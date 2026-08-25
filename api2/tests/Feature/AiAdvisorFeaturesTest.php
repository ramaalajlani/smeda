<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * مزايا «المستشار الذكي» ما عدا المحادثة النصية: تصنيف ISIC4، سجل المحادثات،
 * الاستئناف، وإعدادات المزايا وقناة الصوت.
 */
class AiAdvisorFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = 'https://ai.example.test';

    private const SESSION_ID = 'c25d7ec8-b2f0-4938-9bab-aaa740014c31';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.ai_service.url' => self::BASE_URL,
            'services.ai_service.ws_url' => null,
            'services.ai_service.voice_department' => 'advisor',
            'services.ai_service.department_id' => 'advisor',
            'services.ai_service.api_key' => null,
        ]);
    }

    private function actingAsUser(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        return $user;
    }

    /** @param array<string, mixed> $overrides */
    private function chatPayload(array $overrides = []): array
    {
        return [
            'data' => array_merge([
                'reply' => 'مرحباً بك.',
                'session_id' => self::SESSION_ID,
                'model' => 'gemini-2.5-flash',
            ], $overrides),
            'status_code' => 1,
        ];
    }

    private function isic4Payload(): array
    {
        return [
            'data' => [
                'status' => 'classified',
                'query' => 'عندي مزرعة أزرع فيها القمح والشعير',
                'best_match' => [
                    'code' => '0150',
                    'description' => 'الزراعة المختلطة',
                    'reason' => 'الوصف يشير إلى زراعة نوعين من الحبوب.',
                ],
                'alternatives' => [
                    ['code' => '0119', 'description' => 'زراعة الذرة الصفراء للعلف', 'reason' => 'زراعة الحبوب فقط.'],
                ],
                'candidates' => [
                    ['code' => 'isic_file.xlsx', 'text' => 'محتوى خام طويل', 'relevance' => 0.71],
                ],
            ],
            'status_code' => 1,
        ];
    }

    /* ── تصنيف ISIC4 ─────────────────────────────────────────────────────── */

    public function test_guest_cannot_classify(): void
    {
        Http::fake();

        $this->postJson('/api/ai/isic4/classify', ['description' => 'مزرعة'])->assertUnauthorized();

        Http::assertNothingSent();
    }

    public function test_classify_returns_a_clean_result_without_raw_candidates(): void
    {
        $this->actingAsUser();
        Http::fake([self::BASE_URL.'/api/isic4/classify' => Http::response($this->isic4Payload())]);

        $response = $this->postJson('/api/ai/isic4/classify', [
            'description' => 'عندي مزرعة أزرع فيها القمح والشعير',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'classified')
            ->assertJsonPath('data.best_match.code', '0150')
            ->assertJsonPath('data.best_match.description', 'الزراعة المختلطة')
            ->assertJsonCount(1, 'data.alternatives')
            ->assertJsonPath('data.alternatives.0.code', '0119')
            ->assertJsonMissingPath('data.candidates');

        Http::assertSent(function (Request $request) {
            return $request->url() === self::BASE_URL.'/api/isic4/classify'
                && $request['description'] === 'عندي مزرعة أزرع فيها القمح والشعير'
                && ($request['answers'] ?? []) === [
                    'section' => '',
                    'division' => '',
                    'group' => '',
                    'class' => '',
                ];
        });
    }

    public function test_classify_wizard_returns_need_input_with_options(): void
    {
        $this->actingAsUser();
        Http::fake([self::BASE_URL.'/api/isic4/classify' => Http::response([
            'data' => [
                'status' => 'need_input',
                'step' => 'section',
                'step_index' => 1,
                'step_count' => 4,
                'step_title' => 'الباب الأقرب',
                'question' => 'ما الباب الأقرب لنشاطك؟',
                'options' => [
                    ['id' => 'G', 'code' => 'G', 'label' => 'تجارة الجملة والتجزئة', 'recommended' => true],
                ],
                'answers' => ['section' => '', 'division' => '', 'group' => '', 'class' => ''],
                'selected' => [],
                'steps' => [
                    ['key' => 'section', 'title' => 'الباب الأقرب'],
                    ['key' => 'division', 'title' => 'التصنيف الفرعي'],
                ],
                'query' => 'عندي محل تجزئة',
            ],
            'status_code' => 1,
        ])]);

        $this->postJson('/api/ai/isic4/classify', [
            'description' => 'عندي محل تجزئة',
            'answers' => [],
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'need_input')
            ->assertJsonPath('data.step', 'section')
            ->assertJsonCount(1, 'data.options')
            ->assertJsonPath('data.options.0.code', 'G');
    }

    public function test_classify_wizard_forwards_partial_answers(): void
    {
        $this->actingAsUser();
        Http::fake([self::BASE_URL.'/api/isic4/classify' => Http::response([
            'data' => [
                'status' => 'classified',
                'query' => 'عندي محل تجزئة',
                'answers' => ['section' => 'G', 'division' => '47', 'group' => '471', 'class' => '4719'],
                'selected' => [],
                'best_match' => [
                    'code' => '471901',
                    'description' => 'البيع بالتجزئة لمجموعة واسعة من السلع',
                    'reason' => 'أنسب خيار لمحل تجزئة عام.',
                    'breadcrumb' => 'تجارة > تجزئة > ...',
                ],
                'alternatives' => [],
                'candidates' => [['code' => '471901', 'text' => 'raw']],
            ],
            'status_code' => 1,
        ])]);

        $this->postJson('/api/ai/isic4/classify', [
            'description' => 'عندي محل تجزئة',
            'answers' => ['section' => 'G', 'division' => '47', 'group' => '471', 'class' => '4719'],
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'classified')
            ->assertJsonPath('data.best_match.code', '471901')
            ->assertJsonPath('data.best_match.description', 'البيع بالتجزئة لمجموعة واسعة من السلع')
            ->assertJsonMissingPath('data.candidates');

        Http::assertSent(fn (Request $request) => ($request['answers']['class'] ?? null) === '4719'
            && ($request['answers']['section'] ?? null) === 'G');
    }

    public function test_classify_requires_a_description(): void
    {
        $this->actingAsUser();
        Http::fake();

        $this->postJson('/api/ai/isic4/classify', ['description' => '   '])
            ->assertStatus(422);

        Http::assertNothingSent();
    }

    public function test_classify_failure_becomes_a_friendly_502(): void
    {
        $this->actingAsUser();
        Http::fake(fn () => throw new ConnectionException('timed out'));

        $this->postJson('/api/ai/isic4/classify', ['description' => 'مزرعة قمح'])
            ->assertStatus(502)
            ->assertJsonPath('message', 'عذراً، تعذر تصنيف النشاط حالياً. يرجى المحاولة مرة أخرى.');
    }

    /* ── سجل المحادثات ───────────────────────────────────────────────────── */

    public function test_history_is_empty_before_any_conversation(): void
    {
        $this->actingAsUser();
        Http::fake();

        $this->getJson('/api/ai/chat/history')
            ->assertOk()
            ->assertJsonPath('items', []);

        // لا جلسات مملوكة ⇒ لا نتصل بالخدمة إطلاقاً.
        Http::assertNothingSent();
    }

    public function test_history_lists_only_sessions_the_user_created(): void
    {
        $this->actingAsUser();
        Http::fake([
            self::BASE_URL.'/api/chat' => Http::response($this->chatPayload()),
            self::BASE_URL.'/api/chat/history*' => Http::response([
                'data' => [
                    'items' => [
                        ['session_id' => self::SESSION_ID, 'messages_count' => 4, 'updated_at' => '2026-08-08T10:00:00Z'],
                        ['session_id' => 'someone-else-session', 'messages_count' => 99, 'updated_at' => '2026-08-08T11:00:00Z'],
                    ],
                ],
            ]),
        ]);

        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])->assertOk();

        $this->getJson('/api/ai/chat/history')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.session_id', self::SESSION_ID)
            ->assertJsonPath('items.0.messages_count', 4);
    }

    public function test_history_messages_are_denied_for_a_session_the_user_does_not_own(): void
    {
        $this->actingAsUser();
        Http::fake();

        $this->getJson('/api/ai/chat/history/someone-else-session/messages')
            ->assertStatus(403);

        Http::assertNothingSent();
    }

    public function test_history_messages_are_returned_for_an_owned_session(): void
    {
        $this->actingAsUser();
        Http::fake([
            self::BASE_URL.'/api/chat' => Http::response($this->chatPayload()),
            self::BASE_URL.'/api/chat/history/*/messages' => Http::response([
                'data' => [
                    'messages' => [
                        ['role' => 'user', 'content' => 'مرحبا'],
                        ['role' => 'assistant', 'content' => 'أهلاً بك.'],
                        ['role' => 'weird', 'content' => 'دور غير معروف'],
                        ['role' => 'user', 'content' => ''],
                    ],
                ],
            ]),
        ]);

        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])->assertOk();

        $this->getJson('/api/ai/chat/history/'.self::SESSION_ID.'/messages')
            ->assertOk()
            ->assertJsonCount(3, 'messages')
            ->assertJsonPath('messages.0.role', 'user')
            ->assertJsonPath('messages.2.role', 'assistant');
    }

    public function test_sessions_are_isolated_between_users(): void
    {
        $this->actingAsUser();
        Http::fake([
            self::BASE_URL.'/api/chat' => Http::response($this->chatPayload()),
            self::BASE_URL.'/api/chat/history*' => Http::response(['data' => ['items' => []]]),
        ]);
        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])->assertOk();

        // مستخدم آخر لا يرى جلسة الأول ولا يستطيع فتحها.
        $this->actingAsUser();
        $this->getJson('/api/ai/chat/history')->assertOk()->assertJsonPath('items', []);
        $this->getJson('/api/ai/chat/history/'.self::SESSION_ID.'/messages')->assertStatus(403);
        $this->postJson('/api/ai/chat/history/'.self::SESSION_ID.'/resume')->assertStatus(403);
    }

    public function test_resume_makes_the_session_current_again(): void
    {
        $this->actingAsUser();
        Http::fake([
            self::BASE_URL.'/api/chat' => Http::response($this->chatPayload()),
            self::BASE_URL.'/api/chat/history/*/resume' => Http::response(['data' => [], 'status_code' => 1]),
        ]);

        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])->assertOk();
        $this->postJson('/api/ai/chat/reset')->assertOk();

        $this->postJson('/api/ai/chat/history/'.self::SESSION_ID.'/resume')
            ->assertOk()
            ->assertJsonPath('success', true);

        // بعد الاستئناف تعود الرسائل التالية إلى نفس الجلسة بدل بدء واحدة جديدة.
        $this->postJson('/api/ai/chat', ['message' => 'وبعد؟'])->assertOk();

        $chatCalls = collect(Http::recorded())
            ->filter(fn ($pair): bool => $pair[0]->url() === self::BASE_URL.'/api/chat')
            ->values();

        $this->assertSame(self::SESSION_ID, $chatCalls[1][0]['session_id']);
    }

    /* ── الإعدادات وقناة الصوت ───────────────────────────────────────────── */

    public function test_config_exposes_features_and_the_derived_voice_socket_url(): void
    {
        $this->actingAsUser();
        Http::fake([
            self::BASE_URL.'/api/advisor/ui-config' => Http::response([
                'data' => [
                    'features' => ['textChat' => true, 'voiceChat' => true, 'voiceCall' => true, 'isic4' => true, 'imageGeneration' => false],
                ],
            ]),
            self::BASE_URL.'/api/voice/config' => Http::response([
                'data' => ['stt_modes' => ['record', 'live'], 'tts_sample_rate' => 24000, 'live_sample_rate' => 16000],
            ]),
        ]);

        $this->getJson('/api/ai/config')
            ->assertOk()
            ->assertJsonPath('features.voiceCall', true)
            ->assertJsonPath('features.imageGeneration', false)
            ->assertJsonPath('voice.stt_modes', ['record', 'live'])
            ->assertJsonPath('voice.tts_sample_rate', 24000)
            ->assertJsonPath('voice_socket_url', 'wss://ai.example.test/ws/advisor')
            ->assertJsonPath('department_id', 'advisor')
            ->assertJsonPath('can_manage_knowledge', false);
    }

    public function test_config_reports_knowledge_permission_when_granted(): void
    {
        $user = $this->actingAsUser();
        \Spatie\Permission\Models\Permission::findOrCreate('manage_ai_knowledge', 'sanctum');
        $user->givePermissionTo('manage_ai_knowledge');

        Http::fake([
            self::BASE_URL.'/api/advisor/ui-config' => Http::response([
                'data' => ['features' => ['textChat' => true]],
            ]),
            self::BASE_URL.'/api/voice/config' => Http::response([
                'data' => ['stt_modes' => [], 'tts_sample_rate' => 24000, 'live_sample_rate' => 16000],
            ]),
        ]);

        $this->getJson('/api/ai/config')
            ->assertOk()
            ->assertJsonPath('can_manage_knowledge', true);
    }

    public function test_knowledge_ingest_is_forbidden_without_permission(): void
    {
        $this->actingAsUser();
        \Spatie\Permission\Models\Permission::findOrCreate('manage_ai_knowledge', 'sanctum');
        Http::fake();

        $this->postJson('/api/ai/knowledge/ingest', [
            'department_id' => 'advisor',
            'text' => 'محتوى تدريبي',
        ])->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_knowledge_ingest_is_allowed_with_permission(): void
    {
        $user = $this->actingAsUser();
        \Spatie\Permission\Models\Permission::findOrCreate('manage_ai_knowledge', 'sanctum');
        $user->givePermissionTo('manage_ai_knowledge');

        Http::fake([
            self::BASE_URL.'/api/rag/ingest' => Http::response([
                'data' => [
                    'status' => 'ok',
                    'chunks_ingested' => 3,
                    'department_id' => 'advisor',
                ],
            ]),
        ]);

        $this->postJson('/api/ai/knowledge/ingest', [
            'department_id' => 'advisor',
            'text' => 'محتوى تدريبي',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.chunks_ingested', 3);

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/api/rag/ingest'));
    }

    public function test_config_degrades_without_exposing_an_error_when_the_service_is_down(): void
    {
        $this->actingAsUser();
        Http::fake(fn () => throw new ConnectionException('timed out'));

        $this->getJson('/api/ai/config')
            ->assertOk()
            ->assertJsonPath('features', [])
            ->assertJsonPath('voice.stt_modes', [])
            ->assertJsonPath('voice_socket_url', null)
            ->assertJsonPath('can_manage_knowledge', false);
    }
}
