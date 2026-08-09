<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiChatService;
use App\Support\ApiErrorResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * «المستشار الذكي» — بروكسي المحادثة. المتصفح لا يتصل بالخدمة الخارجية مباشرة.
 */
class AiChatController extends Controller
{
    private const FAILURE_MESSAGE = 'عذراً، تعذر الاتصال بالمستشار الذكي حالياً. يرجى المحاولة مرة أخرى.';

    private const CLASSIFY_FAILURE_MESSAGE = 'عذراً، تعذر تصنيف النشاط حالياً. يرجى المحاولة مرة أخرى.';

    private const HISTORY_FAILURE_MESSAGE = 'عذراً، تعذر تحميل سجل المحادثات حالياً. يرجى المحاولة مرة أخرى.';

    private const KNOWLEDGE_FAILURE_MESSAGE = 'عذراً، تعذر الوصول إلى قاعدة المعرفة حالياً. يرجى المحاولة مرة أخرى.';

    public function __construct(
        private AiChatService $aiChat,
    ) {}

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'department_id' => ['sometimes', 'nullable', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_-]+$/'],
        ], [
            'message.required' => 'اكتب رسالتك أولاً.',
            'message.max' => 'الرسالة طويلة جداً (الحد الأقصى 5000 حرف).',
            'department_id.regex' => 'معرّف القسم غير صالح.',
        ]);

        $message = trim($validated['message']);
        if ($message === '') {
            return response()->json(['message' => 'اكتب رسالتك أولاً.'], 422);
        }

        $ownerKey = (string) $request->user()->id;
        $departmentId = $this->aiChat->resolveDepartmentId(
            $ownerKey,
            $validated['department_id'] ?? null
        );

        try {
            $result = $this->aiChat->chat(
                $message,
                $this->aiChat->currentSessionId($ownerKey),
                $departmentId
            );
        } catch (Throwable $e) {
            return response()->json(
                ApiErrorResponse::payload($e, self::FAILURE_MESSAGE, 'ai_chat.request_failed'),
                502
            );
        }

        $this->aiChat->rememberSessionId($ownerKey, $result['session_id']);

        return response()->json([
            'success' => true,
            'reply' => $result['reply'],
            'truncated' => $result['truncated'],
            'session_id' => $result['session_id'],
            'model' => $result['model'],
            'department_id' => $departmentId,
        ]);
    }

    /** إكمال رد اقتُطع لطوله — يتابع من حيث توقف ضمن نفس الجلسة. */
    public function continueReply(Request $request): JsonResponse
    {
        $ownerKey = (string) $request->user()->id;
        $sessionId = $this->aiChat->currentSessionId($ownerKey);

        if ($sessionId === null) {
            return response()->json(['message' => 'لا توجد محادثة جارية لإكمالها.'], 422);
        }

        $departmentId = $this->aiChat->resolveDepartmentId($ownerKey);

        try {
            $result = $this->aiChat->continueReply($sessionId, $departmentId);
        } catch (Throwable $e) {
            return response()->json(
                ApiErrorResponse::payload($e, self::FAILURE_MESSAGE, 'ai_chat.continue_failed'),
                502
            );
        }

        $this->aiChat->rememberSessionId($ownerKey, $result['session_id']);

        return response()->json([
            'success' => true,
            'reply' => $result['reply'],
            'full_reply' => $result['full_reply'],
            'truncated' => $result['truncated'],
            'session_id' => $result['session_id'],
            'model' => $result['model'],
            'department_id' => $departmentId,
        ]);
    }

    /** يبدأ محادثة جديدة: يمسح جلسة المستشار فقط دون المساس بجلسة تسجيل الدخول. */
    public function reset(Request $request): JsonResponse
    {
        $this->aiChat->forgetSession((string) $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'تم بدء محادثة جديدة.',
        ]);
    }

    /** تصنيف نشاط اقتصادي وفق ISIC4. */
    public function isic4(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:2000'],
        ], [
            'description.required' => 'اكتب وصف النشاط أولاً.',
            'description.max' => 'الوصف طويل جداً (الحد الأقصى 2000 حرف).',
        ]);

        $description = trim($validated['description']);
        if ($description === '') {
            return response()->json(['message' => 'اكتب وصف النشاط أولاً.'], 422);
        }

        try {
            $result = $this->aiChat->classifyIsic4($description);
        } catch (Throwable $e) {
            return response()->json(
                ApiErrorResponse::payload($e, self::CLASSIFY_FAILURE_MESSAGE, 'ai_chat.isic4_failed'),
                502
            );
        }

        return response()->json(['success' => true] + $result);
    }

    /** سجل محادثات المستخدم الحالي وحده. */
    public function history(Request $request): JsonResponse
    {
        try {
            $items = $this->aiChat->historySessions((string) $request->user()->id);
        } catch (Throwable $e) {
            return response()->json(
                ApiErrorResponse::payload($e, self::HISTORY_FAILURE_MESSAGE, 'ai_chat.history_failed'),
                502
            );
        }

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    /** رسائل جلسة سابقة — مسموح فقط لمن أنشأ الجلسة. */
    public function historyMessages(Request $request, string $session): JsonResponse
    {
        $ownerKey = (string) $request->user()->id;

        if (!$this->aiChat->ownsSession($ownerKey, $session)) {
            return response()->json(['message' => 'هذه المحادثة غير متاحة لك.'], 403);
        }

        try {
            $messages = $this->aiChat->historyMessages($session);
        } catch (Throwable $e) {
            return response()->json(
                ApiErrorResponse::payload($e, self::HISTORY_FAILURE_MESSAGE, 'ai_chat.history_messages_failed'),
                502
            );
        }

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /** استئناف محادثة سابقة: يعيد الخدمة إلى سياقها ويجعلها الجلسة الحالية. */
    public function resume(Request $request, string $session): JsonResponse
    {
        $ownerKey = (string) $request->user()->id;

        if (!$this->aiChat->ownsSession($ownerKey, $session)) {
            return response()->json(['message' => 'هذه المحادثة غير متاحة لك.'], 403);
        }

        try {
            $this->aiChat->resumeSession($session);
        } catch (Throwable $e) {
            return response()->json(
                ApiErrorResponse::payload($e, self::HISTORY_FAILURE_MESSAGE, 'ai_chat.resume_failed'),
                502
            );
        }

        $this->aiChat->rememberSessionId($ownerKey, $session);

        return response()->json([
            'success' => true,
            'message' => 'تم استئناف المحادثة.',
        ]);
    }

    /**
     * المزايا المتاحة وعنوان قناة الصوت.
     *
     * الصوت والمكالمة يعملان على WebSocket لا يستطيع PHP تمريره، فيتصل بهما
     * المتصفح مباشرة؛ ويبقى العنوان مضبوطاً في الإعدادات لا في جافاسكربت.
     */
    public function config(Request $request): JsonResponse
    {
        $voiceSocketUrl = null;
        $config = ['features' => [], 'voice' => ['stt_modes' => []]];

        try {
            $config = $this->aiChat->serviceConfig();
            $voiceSocketUrl = $this->aiChat->voiceSocketUrl();
        } catch (Throwable $e) {
            // تعذّر جلب الإعدادات لا يمنع المحادثة النصية؛ نُخفي مزايا الصوت فقط.
            report($e);
        }

        return response()->json([
            'success' => true,
            'features' => $config['features'],
            'voice' => $config['voice'],
            'voice_socket_url' => $voiceSocketUrl,
            'department_id' => $this->aiChat->defaultDepartmentId(),
            'can_manage_knowledge' => $request->user()?->can('manage_ai_knowledge') === true,
        ]);
    }

    public function knowledgeDepartments(): JsonResponse
    {
        try {
            $items = $this->aiChat->knowledgeDepartments();
        } catch (Throwable $e) {
            return response()->json(
                ApiErrorResponse::payload($e, self::KNOWLEDGE_FAILURE_MESSAGE, 'ai_chat.knowledge_departments_failed'),
                502
            );
        }

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    public function knowledgeItems(Request $request, string $department): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:200'],
            'offset' => ['sometimes', 'nullable', 'string', 'max:128'],
        ]);

        try {
            $page = $this->aiChat->knowledgeItems(
                $department,
                (int) ($validated['limit'] ?? 200),
                $validated['offset'] ?? null
            );
        } catch (Throwable $e) {
            return response()->json(
                ApiErrorResponse::payload($e, self::KNOWLEDGE_FAILURE_MESSAGE, 'ai_chat.knowledge_items_failed'),
                502
            );
        }

        return response()->json([
            'success' => true,
            'items' => $page['items'],
            'next_offset' => $page['next_offset'],
        ]);
    }

    public function knowledgeIngest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'text' => ['nullable', 'string', 'max:200000'],
            'files' => ['nullable', 'array', 'max:10'],
            'files.*' => ['file', 'max:20480'],
        ], [
            'department_id.required' => 'حدّد القسم أولاً.',
            'department_id.regex' => 'معرّف القسم غير صالح.',
        ]);

        $files = $request->file('files', []);
        if (!is_array($files)) {
            $files = $files ? [$files] : [];
        }

        $text = isset($validated['text']) ? trim((string) $validated['text']) : '';
        if ($text === '' && $files === []) {
            return response()->json(['message' => 'أرفق ملفاً أو ألصق نصاً للتدريب.'], 422);
        }

        try {
            $result = $this->aiChat->ingestKnowledge(
                $validated['department_id'],
                $text !== '' ? $text : null,
                $files
            );
        } catch (Throwable $e) {
            return response()->json(
                ApiErrorResponse::payload($e, self::KNOWLEDGE_FAILURE_MESSAGE, 'ai_chat.knowledge_ingest_failed'),
                502
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال المحتوى للتدريب.',
            'data' => $result,
        ]);
    }
}
