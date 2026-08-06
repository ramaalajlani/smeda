<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NewsController extends Controller
{
    private function canManage(?User $user): bool
    {
        return $user && (
            $user->hasPermissionTo('news.manage')
            || $user->hasRole(['media_manager', 'general_director', 'admin', 'super_admin', 'system_admin'])
        );
    }

    public function index(Request $request)
    {
        $canManage = $this->canManage($request->user());

        $q = News::with(['creator:id,name', 'branch:id,name'])
            ->when($request->category,  fn($q) => $q->where('category', $request->category))
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->search,    fn($q) => $q->where(function($q) use ($request) {
                $q->where('title',   'like', "%{$request->search}%")
                  ->orWhere('summary','like', "%{$request->search}%");
            }));

        if ($canManage && $request->filled('status')) {
            $q->where('status', $request->status);
            $q->orderByDesc('is_pinned')->latest();
        } else {
            $q->published()->orderByDesc('is_pinned')->orderByDesc('published_at');
        }

        return response()->json($q->paginate($request->per_page ?? 6));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'summary'    => 'required|string',
            'body'       => 'required|string',
            'image_url'  => 'nullable|url',
            'category'   => 'nullable|string',
            'branch_id'  => 'nullable|exists:branches,id',
            'status'     => 'nullable|in:draft,published,archived',
            'is_pinned'  => 'nullable|boolean',
        ]);

        if (($data['status'] ?? 'draft') === 'published') {
            $data['published_at'] = now();
        }

        $news = News::create($data);
        return response()->json($news->load('creator:id,name'), 201);
    }

    public function show(Request $request, $id)
    {
        $news = News::with(['creator:id,name', 'branch:id,name'])->findOrFail($id);

        if ($news->status !== 'published' && !$this->canManage($request->user())) {
            throw new NotFoundHttpException();
        }

        if ($news->status === 'published') {
            $news->increment('views_count');
        }

        return response()->json($news);
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $data = $request->validate([
            'title'     => 'sometimes|string|max:255',
            'summary'   => 'sometimes|string',
            'body'      => 'sometimes|string',
            'image_url' => 'nullable|url',
            'category'  => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
            'status'    => 'nullable|in:draft,published,archived',
            'is_pinned' => 'nullable|boolean',
        ]);

        if (isset($data['status']) && $data['status'] === 'published' && !$news->published_at) {
            $data['published_at'] = now();
        }

        $news->update($data);
        return response()->json($news->fresh('creator:id,name'));
    }

    public function destroy($id)
    {
        News::findOrFail($id)->delete();
        return response()->json(['message' => 'تم حذف الخبر']);
    }

    public function stats()
    {
        return response()->json([
            'total'     => News::count(),
            'published' => News::published()->count(),
            'draft'     => News::where('status', 'draft')->count(),
            'pinned'    => News::where('is_pinned', true)->count(),
            'views'     => (int) News::published()->sum('views_count'),
        ]);
    }
}
