<?php

namespace App\Services\Ai;

use App\Models\AiChatOwnedSession;
use App\Models\AiChatUserState;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * بروكسي «المستشار الذكي» — الباك إند وحده يتصل بالخدمة الخارجية عبر HTTP.
 *
 * يحتفظ أيضاً بـ session_id الخاص بالمحادثة على السيرفر مربوطاً بالمستخدم،
 * لأن واجهة الـ API هنا stateless (Sanctum token بلا session middleware)،
 * فلا يمكن الاعتماد على session() بين الطلبات.
 *
 * ولأن سجل المحادثات في الخدمة الخارجية غير مقسّم على المستخدمين، نحتفظ محلياً
 * بقائمة الجلسات التي أنشأها كل مستخدم في قاعدة البيانات (لا كاش الملفات)
 * ولا نكشف له غيرها.
 */
class AiChatService
{
    private const CONFIG_CACHE_KEY = 'ai_chat_service_config';

    /**
     * يرسل رسالة إلى خدمة المحادثة ويعيد الحقول المطلوبة للواجهة فقط.
     *
     * @return array{reply:string,full_reply:?string,truncated:bool,session_id:?string,model:?string}
     */
    public function chat(string $message, ?string $sessionId = null, ?string $departmentId = null): array
    {
        return $this->shapeReply($this->send('post', '/api/chat', [
            'message' => $message,
            'session_id' => $sessionId ?: null,
            'department_id' => $this->normalizeDepartmentId($departmentId),
        ]));
    }

    /**
     * إكمال رد سابق اقتُطع لبلوغه حد الطول. الخدمة تكمل من حيث توقفت في نفس الجلسة.
     *
     * @return array{reply:string,full_reply:?string,truncated:bool,session_id:?string,model:?string}
     */
    public function continueReply(string $sessionId, ?string $departmentId = null): array
    {
        return $this->shapeReply($this->send('post', '/api/chat', [
            'continue' => true,
            'session_id' => $sessionId,
            'department_id' => $this->normalizeDepartmentId($departmentId),
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{reply:string,full_reply:?string,truncated:bool,session_id:?string,model:?string}
     */
    private function shapeReply(array $data): array
    {
        $reply = $data['reply'] ?? null;
        if (!is_string($reply) || trim($reply) === '') {
            throw new RuntimeException('لم تتضمن استجابة المستشار الذكي أي رد.');
        }

        return [
            'reply' => trim($reply),
            'full_reply' => $this->stringOrNull($data['full_reply'] ?? null),
            'truncated' => (bool) ($data['truncated'] ?? false),
            'session_id' => $this->stringOrNull($data['session_id'] ?? null),
            'model' => $this->stringOrNull($data['model'] ?? null),
        ];
    }

    /**
     * تصنيف نشاط اقتصادي وفق ISIC4. يُستبعد candidates لأنه مقاطع فهرسة خام.
     *
     * @return array{best_match:?array{code:?string,label:?string,raw:string,reason:string},alternatives:list<array{code:?string,label:?string,raw:string,reason:string}>,clarifying_question:?string}
     */
    public function classifyIsic4(string $description): array
    {
        $data = $this->send('post', '/api/isic4/classify', ['description' => $description]);

        $best = null;
        if (isset($data['best_match']) && is_array($data['best_match'])) {
            $best = $this->isicMatch($data['best_match']);
        }

        $alternatives = [];
        foreach ((array) ($data['alternatives'] ?? []) as $alternative) {
            if (!is_array($alternative)) {
                continue;
            }
            $match = $this->isicMatch($alternative);
            if ($match !== null) {
                $alternatives[] = $match;
            }
        }

        $clarifying = $this->stringOrNull($data['clarifying_question'] ?? null);

        if ($best === null && $alternatives === [] && $clarifying === null) {
            throw new RuntimeException('لم تتضمن استجابة التصنيف أي نتيجة.');
        }

        return [
            'best_match' => $best,
            'alternatives' => $alternatives,
            'clarifying_question' => $clarifying,
        ];
    }

    /**
     * الخدمة تعيد الكود كسطر خام من ملف الفهرسة، مثل «136,,,,0150,,,الزراعة المختلطة,»
     * أو نصاً يتذيّله الكود. نفصل رقم التصنيف عن مسمّاه ليصلح للعرض.
     *
     * @param  array<string, mixed>  $match
     * @return ?array{code:?string,label:?string,raw:string,reason:string}
     */
    private function isicMatch(array $match): ?array
    {
        $raw = $this->stringOrNull($match['code'] ?? null);
        if ($raw === null) {
            return null;
        }

        $text = trim(preg_replace('/\s+/u', ' ', str_replace(',', ' ', $raw)));
        $text = trim($text, " \t\n\r-\"'…");

        preg_match_all('/\d{3,6}/u', $text, $numbers);
        $codes = $numbers[0] ?? [];

        $code = null;
        foreach ($codes as $candidate) {
            // أكواد ISIC4 تبدأ بصفر وطولها 4–6 خانات؛ ما قبلها أرقام تسلسل في الملف.
            if (strlen($candidate) >= 4 && str_starts_with($candidate, '0')) {
                $code = $candidate;
                break;
            }
        }
        if ($code === null && $codes !== []) {
            $code = end($codes);
        }

        $label = trim(preg_replace('/\s+/u', ' ', preg_replace('/\d{3,6}/u', ' ', $text)));
        $label = trim($label, " \t\n\r-\"'…");

        return [
            'code' => $code,
            'label' => $label !== '' ? $label : null,
            'raw' => $raw,
            'reason' => (string) ($this->stringOrNull($match['reason'] ?? null) ?? ''),
        ];
    }

    /**
     * سجل محادثات هذا المستخدم فقط: نبني القائمة من الجلسات المملوكة محلياً
     * ونُثريها بالبيانات الوصفية من الخدمة عند توفّرها.
     *
     * @return list<array{session_id:string,messages_count:?int,updated_at:?string}>
     */
    public function historySessions(int|string $ownerKey): array
    {
        $owned = $this->ownedSessions($ownerKey);
        if ($owned === []) {
            return [];
        }

        $upstream = [];
        try {
            $data = $this->send('get', '/api/chat/history', [], [
                'limit' => max(count($owned), 25),
                'offset' => 0,
            ]);
            foreach ((array) ($data['items'] ?? []) as $item) {
                $id = is_array($item) ? $this->stringOrNull($item['session_id'] ?? null) : null;
                if ($id !== null) {
                    $upstream[$id] = $item;
                }
            }
        } catch (RuntimeException) {
            // السجل الوصفي إضافة تحسينية؛ نتابع بما نعرفه محلياً عند تعذّره.
        }

        $items = [];
        foreach ($owned as $session) {
            $id = $session['id'];
            $meta = $upstream[$id] ?? null;

            $count = null;
            if (is_array($meta) && isset($meta['messages_count']) && is_numeric($meta['messages_count'])) {
                $count = (int) $meta['messages_count'];
            }

            $updatedAt = is_array($meta) ? $this->stringOrNull($meta['updated_at'] ?? null) : null;

            $items[] = [
                'session_id' => $id,
                'messages_count' => $count,
                'updated_at' => $updatedAt ?? $session['at'],
            ];
        }

        return $items;
    }

    /**
     * رسائل جلسة سابقة. على المستدعي التحقق من الملكية قبل النداء.
     *
     * @return list<array{role:string,content:string}>
     */
    public function historyMessages(string $sessionId): array
    {
        $data = $this->send('get', '/api/chat/history/'.rawurlencode($sessionId).'/messages');

        $messages = [];
        foreach ((array) ($data['messages'] ?? []) as $message) {
            if (!is_array($message)) {
                continue;
            }
            $content = $this->stringOrNull($message['content'] ?? null);
            if ($content === null) {
                continue;
            }
            $role = $this->stringOrNull($message['role'] ?? null);
            $messages[] = [
                'role' => in_array($role, ['user', 'assistant'], true) ? $role : 'assistant',
                'content' => $content,
            ];
        }

        return $messages;
    }

    /** يعيد الخدمة إلى سياق جلسة سابقة. على المستدعي التحقق من الملكية. */
    public function resumeSession(string $sessionId): void
    {
        $this->send('post', '/api/chat/history/'.rawurlencode($sessionId).'/resume');
    }

    /**
     * المزايا المتاحة وإعداد الصوت — مُخزَّنة مؤقتاً لتفادي طلب لكل فتح للنافذة.
     *
     * @return array{features:array<string,bool>,voice:array<string,mixed>}
     */
    public function serviceConfig(): array
    {
        $ttl = max(1, (int) config('services.ai_service.config_ttl', 10));

        return Cache::remember(self::CONFIG_CACHE_KEY, now()->addMinutes($ttl), function (): array {
            $features = [];
            foreach ((array) ($this->send('get', '/api/advisor/ui-config')['features'] ?? []) as $key => $enabled) {
                if (is_string($key)) {
                    $features[$key] = (bool) $enabled;
                }
            }

            $voice = $this->send('get', '/api/voice/config');

            $modes = [];
            foreach ((array) ($voice['stt_modes'] ?? []) as $mode) {
                if (in_array($mode, ['record', 'live'], true)) {
                    $modes[] = $mode;
                }
            }

            return [
                'features' => $features,
                'voice' => [
                    'stt_modes' => $modes,
                    'tts_sample_rate' => (int) ($voice['tts_sample_rate'] ?? 24000),
                    'live_sample_rate' => (int) ($voice['live_sample_rate'] ?? 16000),
                ],
            ];
        });
    }

    /** عنوان WebSocket الذي يتصل به المتصفح للصوت (الاستثناء الوحيد للاتصال المباشر). */
    public function voiceSocketUrl(): string
    {
        $configured = trim((string) config('services.ai_service.ws_url', ''));
        $base = $configured !== ''
            ? $configured
            : preg_replace('#^http#i', 'ws', $this->baseUrl());

        $department = trim((string) config('services.ai_service.voice_department', 'advisor')) ?: 'advisor';

        return rtrim((string) $base, '/').'/ws/'.rawurlencode($department);
    }

    /**
     * أقسام قاعدة المعرفة المعتمدة لدى خدمة المستشار.
     *
     * @return list<array{department_id:string,count:int}>
     */
    public function knowledgeDepartments(): array
    {
        $data = $this->send('get', '/api/knowledge/departments');
        $items = [];
        foreach ((array) ($data['items'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $id = $this->stringOrNull($item['department_id'] ?? null);
            if ($id === null) {
                continue;
            }
            $items[] = [
                'department_id' => $id,
                'count' => max(0, (int) ($item['count'] ?? 0)),
            ];
        }

        return $items;
    }

    /**
     * قطع معرفة مدرَّبة لقسم معيّن (صفحات محدودة).
     *
     * @return array{items:list<array<string,mixed>>,next_offset:?string}
     */
    public function knowledgeItems(string $departmentId, int $limit = 200, ?string $offset = null): array
    {
        $departmentId = trim($departmentId);
        if ($departmentId === '' || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $departmentId)) {
            throw new RuntimeException('معرّف القسم غير صالح.');
        }

        $query = ['limit' => max(1, min(200, $limit))];
        if (is_string($offset) && $offset !== '') {
            $query['offset'] = $offset;
        }

        $data = $this->send('get', '/api/knowledge/'.rawurlencode($departmentId), [], $query);

        $items = [];
        foreach ((array) ($data['items'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $items[] = [
                'id' => $this->stringOrNull($item['id'] ?? null),
                'text' => is_string($item['text'] ?? null) ? $item['text'] : '',
                'source_name' => $this->stringOrNull($item['source_name'] ?? null),
                'source_type' => $this->stringOrNull($item['source_type'] ?? null),
                'department_id' => $this->stringOrNull($item['department_id'] ?? null) ?: $departmentId,
                'ingested_at' => $this->stringOrNull($item['ingested_at'] ?? null),
            ];
        }

        return [
            'items' => $items,
            'next_offset' => $this->stringOrNull($data['next_offset'] ?? null),
        ];
    }

    /**
     * رفع ملفات/نص لتدريب قاعدة معرفة قسم عبر الخدمة الخارجية.
     *
     * @param  list<\Illuminate\Http\UploadedFile>  $files
     * @return array<string, mixed>
     */
    public function ingestKnowledge(string $departmentId, ?string $text, array $files): array
    {
        $departmentId = trim($departmentId);
        if ($departmentId === '' || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $departmentId)) {
            throw new RuntimeException('معرّف القسم غير صالح.');
        }

        $url = $this->baseUrl().'/api/rag/ingest';

        try {
            $request = $this->httpClient()->timeout(max(60, $this->timeout() * 3));

            foreach ($files as $file) {
                $request = $request->attach(
                    'files',
                    fopen($file->getRealPath(), 'r'),
                    $file->getClientOriginalName()
                );
            }

            $payload = ['department_id' => $departmentId];
            if (is_string($text) && trim($text) !== '') {
                $payload['text'] = trim($text);
            }

            $response = $request->post($url, $payload);
        } catch (ConnectionException $e) {
            throw new RuntimeException(
                'تعذر الوصول إلى خدمة المستشار الذكي: '.$e->getMessage(),
                0,
                $e
            );
        }

        if (!$response->successful()) {
            throw new RuntimeException(
                'استجابة غير ناجحة من خدمة المستشار الذكي (HTTP '.$response->status().').'
            );
        }

        $body = $response->json();
        if (!is_array($body)) {
            throw new RuntimeException('استجابة خدمة المستشار الذكي غير صالحة.');
        }

        return isset($body['data']) && is_array($body['data']) ? $body['data'] : $body;
    }

    public function currentSessionId(int|string $ownerKey): ?string
    {
        $state = AiChatUserState::query()->find($this->userId($ownerKey));

        return $this->stringOrNull($state?->current_session_id);
    }

    public function rememberSessionId(int|string $ownerKey, ?string $sessionId): void
    {
        if (!is_string($sessionId) || $sessionId === '') {
            return;
        }

        $userId = $this->userId($ownerKey);

        AiChatUserState::query()->updateOrCreate(
            ['user_id' => $userId],
            ['current_session_id' => $sessionId]
        );

        $this->trackOwnedSession($userId, $sessionId);
    }

    public function forgetSession(int|string $ownerKey): void
    {
        $userId = $this->userId($ownerKey);

        AiChatUserState::query()->updateOrCreate(
            ['user_id' => $userId],
            ['current_session_id' => null]
        );
    }

    /**
     * يختار قسم المعرفة: طلب صريح إن وُجد، وإلا القسم المحفوظ للجلسة، وإلا الافتراضي من الإعدادات.
     */
    public function resolveDepartmentId(int|string $ownerKey, ?string $requested = null): ?string
    {
        $requested = $this->normalizeDepartmentId($requested);
        if ($requested !== null) {
            $this->rememberDepartmentId($ownerKey, $requested);

            return $requested;
        }

        $stored = $this->currentDepartmentId($ownerKey);
        if ($stored !== null) {
            return $stored;
        }

        $fallback = $this->normalizeDepartmentId(config('services.ai_service.department_id'));
        if ($fallback !== null) {
            $this->rememberDepartmentId($ownerKey, $fallback);
        }

        return $fallback;
    }

    public function currentDepartmentId(int|string $ownerKey): ?string
    {
        $state = AiChatUserState::query()->find($this->userId($ownerKey));

        return $this->normalizeDepartmentId($state?->department_id);
    }

    public function rememberDepartmentId(int|string $ownerKey, ?string $departmentId): void
    {
        $departmentId = $this->normalizeDepartmentId($departmentId);
        if ($departmentId === null) {
            return;
        }

        AiChatUserState::query()->updateOrCreate(
            ['user_id' => $this->userId($ownerKey)],
            ['department_id' => $departmentId]
        );
    }

    public function defaultDepartmentId(): ?string
    {
        return $this->normalizeDepartmentId(config('services.ai_service.department_id'));
    }

    public function ownsSession(int|string $ownerKey, string $sessionId): bool
    {
        return AiChatOwnedSession::query()
            ->where('user_id', $this->userId($ownerKey))
            ->where('session_id', $sessionId)
            ->exists();
    }

    /**
     * الجلسات التي أنشأها المستخدم، الأحدث أولاً.
     *
     * @return list<array{id:string,at:string}>
     */
    public function ownedSessions(int|string $ownerKey): array
    {
        $userId = $this->userId($ownerKey);
        $max = max(1, (int) config('services.ai_service.history_max', 40));
        $ttlMinutes = max(60, (int) config('services.ai_service.history_ttl', 43200));

        // حذف المنتهية عند القراءة حتى لا تتراكم بلا مهمة مجدولة.
        AiChatOwnedSession::query()
            ->where('user_id', $userId)
            ->where('last_used_at', '<', now()->subMinutes($ttlMinutes))
            ->delete();

        return AiChatOwnedSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('last_used_at')
            ->limit($max)
            ->get(['session_id', 'last_used_at'])
            ->map(static fn (AiChatOwnedSession $row): array => [
                'id' => $row->session_id,
                'at' => optional($row->last_used_at)?->toIso8601String() ?? '',
            ])
            ->all();
    }

    private function trackOwnedSession(int $userId, string $sessionId): void
    {
        AiChatOwnedSession::query()->updateOrCreate(
            ['user_id' => $userId, 'session_id' => $sessionId],
            ['last_used_at' => now()]
        );

        $max = max(1, (int) config('services.ai_service.history_max', 40));
        $excess = AiChatOwnedSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('last_used_at')
            ->skip($max)
            ->take(1000)
            ->pluck('id');

        if ($excess->isNotEmpty()) {
            AiChatOwnedSession::query()->whereIn('id', $excess)->delete();
        }
    }

    private function userId(int|string $ownerKey): int
    {
        return (int) $ownerKey;
    }

    /**
     * نداء موحّد للخدمة الخارجية: يعيد جسم data ويرفع RuntimeException بنص عربي
     * موجّه للمستخدم، والسبب التقني يبقى في اللوج.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function send(string $method, string $path, array $payload = [], array $query = []): array
    {
        $url = $this->baseUrl().$path;

        try {
            $request = $this->httpClient()->asJson();

            $response = $method === 'get'
                ? $request->get($url, $query)
                : $request->post($url, $payload);
        } catch (ConnectionException $e) {
            // السبب الفعلي (مهلة/DNS/TLS) يُسجَّل في اللوج فقط، ولا يصل للمستخدم.
            throw new RuntimeException(
                'تعذر الوصول إلى خدمة المستشار الذكي: '.$e->getMessage(),
                0,
                $e
            );
        }

        if (!$response->successful()) {
            throw new RuntimeException(
                'استجابة غير ناجحة من خدمة المستشار الذكي (HTTP '.$response->status().').'
            );
        }

        $body = $response->json();
        if (!is_array($body)) {
            throw new RuntimeException('استجابة خدمة المستشار الذكي غير صالحة.');
        }

        return isset($body['data']) && is_array($body['data']) ? $body['data'] : $body;
    }

    private function httpClient(): \Illuminate\Http\Client\PendingRequest
    {
        $request = Http::acceptJson()->timeout($this->timeout());
        $apiKey = trim((string) config('services.ai_service.api_key', ''));
        if ($apiKey !== '') {
            $request = $request->withToken($apiKey);
        }

        return $request;
    }

    private function normalizeDepartmentId(mixed $value): ?string
    {
        if (!is_string($value) && !is_int($value)) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '' || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $value)) {
            return null;
        }

        return $value;
    }

    private function baseUrl(): string
    {
        $base = trim((string) config('services.ai_service.url', ''));
        if ($base === '') {
            throw new RuntimeException(
                'خدمة المستشار الذكي غير مضبوطة. عيّن AI_SERVICE_URL في ملف .env.'
            );
        }

        return rtrim($base, '/');
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function timeout(): int
    {
        return max(5, (int) config('services.ai_service.timeout', 60));
    }
}
