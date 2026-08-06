<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InboxMessage;
use App\Models\InboxMessageRead;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InboxController extends Controller
{
    /* ── GET /inbox  (صندوق الوارد للمستخدم الحالي) ── */
    public function inbox(Request $request): JsonResponse
    {
        $user = $request->user();
        $uid  = $user->id;

        // رسائل فردية للمستخدم + رسائل بث عام + رسائل بث لدوره
        $msgs = InboxMessage::with(['sender:id,name', 'replies' => fn ($q) => $q->select('id','parent_id','sender_id','subject','created_at')->limit(1)])
            ->where(function ($q) use ($uid, $user) {
                $q->where('recipient_id', $uid)
                  ->orWhere(fn ($q2) => $q2->where('is_broadcast', true)->where(fn ($q3) =>
                        $q3->whereNull('broadcast_role')->orWhere('broadcast_role', $user->roles->pluck('name')->first())
                  ));
            })
            ->whereNull('parent_id') // رسائل رئيسية فقط
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->orderByDesc('id')
            ->paginate(max(1, min($request->integer('per_page', 20), 100)));

        // تضمين حالة القراءة لكل رسالة بث
        $broadcastIds = $msgs->getCollection()
            ->filter(fn (InboxMessage $m) => $m->is_broadcast)
            ->pluck('id');

        $readBroadcastIds = $broadcastIds->isEmpty()
            ? collect()
            : InboxMessageRead::where('user_id', $uid)
                ->whereIn('message_id', $broadcastIds)
                ->pluck('message_id')
                ->flip();

        $msgs->getCollection()->transform(function (InboxMessage $m) use ($readBroadcastIds) {
            if ($m->is_broadcast) {
                $m->is_read = $readBroadcastIds->has($m->id);
            }
            return $m;
        });

        return response()->json($msgs);
    }

    /* ── GET /inbox/sent  (ما أرسله المستخدم الحالي) ── */
    public function sent(Request $request): JsonResponse
    {
        $rows = InboxMessage::with(['recipient:id,name'])
            ->where('sender_id', $request->user()->id)
            ->whereNull('parent_id')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($rows);
    }

    /* ── GET /inbox/{id} ── */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $uid  = $user->id;

        $msg = InboxMessage::with([
            'sender:id,name',
            'recipient:id,name',
            'replies.sender:id,name',
        ])->findOrFail($id);

        // تحقق من الصلاحية
        $canRead = $msg->sender_id === $uid
            || $msg->recipient_id === $uid
            || ($msg->is_broadcast && $this->userCanReadBroadcast($user, $msg))
            || $user->hasRole(['admin', 'super_admin']);

        if (!$canRead) abort(403);

        // تعليم مقروء
        if ($msg->is_broadcast) {
            InboxMessageRead::firstOrCreate(['message_id' => $id, 'user_id' => $uid], ['read_at' => now()]);
        } elseif ($msg->recipient_id === $uid && !$msg->is_read) {
            $msg->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json(['data' => $msg]);
    }

    /* ── POST /inbox  (إرسال رسالة) ── */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'subject'        => ['required', 'string', 'max:255'],
            'body'           => ['required', 'string'],
            'recipient_id'   => ['nullable', 'integer', 'exists:users,id'],
            'is_broadcast'   => ['sometimes', 'boolean'],
            'broadcast_role' => ['nullable', 'string', 'max:60'],
            'requires_reply' => ['sometimes', 'boolean'],
            'priority'       => ['sometimes', 'in:normal,high,urgent'],
            'parent_id'      => ['nullable', 'integer', 'exists:inbox_messages,id'],
        ]);

        // الرد لا يحتاج broadcast
        $isBroadcast = !empty($validated['is_broadcast']) && empty($validated['parent_id']);

        // البث يتطلب صلاحية
        if ($isBroadcast && !$user->hasRole(['admin', 'super_admin', 'branch_manager'])) {
            abort(403, 'لا يمكنك إرسال رسائل جماعية.');
        }

        $msg = InboxMessage::create(array_merge($validated, [
            'sender_id'   => $user->id,
            'sender_role' => $user->roles->pluck('name')->first() ?? 'user',
            'is_broadcast'=> $isBroadcast,
        ]));

        // إرسال إشعار
        $notifData = [
            'icon'          => 'bi-envelope-fill',
            'color'         => 'info',
            'action_url'    => '/front/inbox/inbox-view.php?id=' . $msg->id,
            'action_label'  => 'عرض الرسالة',
            'reference_type'=> 'inbox_message',
            'reference_id'  => $msg->id,
        ];

        if ($isBroadcast) {
            $roleFilter = $validated['broadcast_role'] ?? null;
            $query = User::query();
            if ($roleFilter) $query->role($roleFilter);
            $query->where('id', '!=', $user->id)->select('id')->each(function (User $u) use ($msg, $notifData) {
                Notification::send($u->id, 'inbox_message', 'رسالة جديدة: ' . $msg->subject, $notifData);
            });
        } elseif (!empty($validated['recipient_id'])) {
            Notification::send($validated['recipient_id'], 'inbox_message', 'رسالة جديدة: ' . $msg->subject, $notifData);
        }

        return response()->json(['message' => 'تم الإرسال.', 'data' => $msg], 201);
    }

    /* ── POST /inbox/{id}/reply ── */
    public function reply(Request $request, int $id): JsonResponse
    {
        $parent = InboxMessage::findOrFail($id);
        $user   = $request->user();

        if (!$this->userCanReplyToMessage($user, $parent)) {
            abort(403);
        }

        $threadRoot = $parent->parent_id
            ? (InboxMessage::find($parent->parent_id) ?? $parent)
            : $parent;

        $recipientId = $threadRoot->sender_id === $user->id
            ? $threadRoot->recipient_id
            : $threadRoot->sender_id;

        $validated = $request->validate([
            'body'     => ['required', 'string', 'max:10000'],
            'subject'  => ['sometimes', 'string', 'max:255'],
        ]);

        $reply = InboxMessage::create([
            'sender_id'    => $user->id,
            'sender_role'  => $user->roles->pluck('name')->first() ?? 'user',
            'recipient_id' => $recipientId,
            'subject'      => $validated['subject'] ?? 'رد: ' . $threadRoot->subject,
            'body'         => $validated['body'],
            'parent_id'    => $threadRoot->id,
            'is_broadcast' => false,
        ]);

        if ($recipientId) {
            Notification::send($recipientId, 'inbox_reply', 'رد جديد على: ' . $threadRoot->subject, [
                'icon'          => 'bi-reply-fill',
                'color'         => 'primary',
                'action_url'    => '/front/inbox/inbox-view.php?id=' . $threadRoot->id,
                'action_label'  => 'عرض المحادثة',
                'reference_type'=> 'inbox_message',
                'reference_id'  => $threadRoot->id,
            ]);
        }

        return response()->json(['message' => 'تم إرسال الرد.', 'data' => $reply], 201);
    }

    private function userCanReadBroadcast(User $user, InboxMessage $message): bool
    {
        if (!$message->is_broadcast) {
            return false;
        }

        $role = $user->roles->pluck('name')->first();

        return $message->broadcast_role === null
            || $message->broadcast_role === $role;
    }

    private function userCanReplyToMessage($user, InboxMessage $message): bool
    {
        if ($message->is_broadcast) {
            return false;
        }

        $root = $message->parent_id
            ? (InboxMessage::find($message->parent_id) ?? $message)
            : $message;

        if ($root->is_broadcast) {
            return false;
        }

        return (int) $root->sender_id === (int) $user->id
            || (int) $root->recipient_id === (int) $user->id;
    }

    /* ── DELETE /inbox/{id} ── */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $msg = InboxMessage::findOrFail($id);
        if ($msg->sender_id !== $request->user()->id && !$request->user()->hasRole(['admin', 'super_admin'])) {
            abort(403);
        }
        $msg->delete();
        return response()->json(['message' => 'تم حذف الرسالة.']);
    }

    /* ── GET /inbox/unread-count ── */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $uid  = $user->id;
        $role = $user->roles->pluck('name')->first();

        $individual = InboxMessage::where('recipient_id', $uid)->where('is_read', false)->whereNull('parent_id')->count();

        $broadcastIds = InboxMessage::where('is_broadcast', true)
            ->where(fn ($q) => $q->whereNull('broadcast_role')->orWhere('broadcast_role', $role))
            ->pluck('id');

        $readBroadcast = InboxMessageRead::where('user_id', $uid)->whereIn('message_id', $broadcastIds)->count();
        $unreadBroadcast = $broadcastIds->count() - $readBroadcast;

        return response()->json(['count' => $individual + max(0, $unreadBroadcast)]);
    }

    /* ── GET /inbox/users-list  (قائمة المستخدمين للبحث عند الإرسال) ── */
    public function usersList(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));

        if ($search === '' || mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->where('is_active', true)
            ->where('id', '!=', $currentUser->id)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->select('id', 'name', 'email')
            ->limit(20)
            ->get();

        return response()->json($users);
    }
}
