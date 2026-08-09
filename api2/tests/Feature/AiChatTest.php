<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AiChatTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = 'https://ai.example.test';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.ai_service.url' => self::BASE_URL,
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
    private function upstreamPayload(array $overrides = []): array
    {
        return [
            'data' => array_merge([
                'reply' => 'مرحباً بك. كيف يمكنني مساعدتك اليوم؟',
                'truncated' => false,
                'session_id' => 'c25d7ec8-b2f0-4938-9bab-aaa740014c31',
                'usage' => ['prompt_tokens' => 895, 'completion_tokens' => 12, 'total_tokens' => 907],
                'model' => 'gemini-2.5-flash',
                'provider' => 'gemini',
                'credits_charged' => 0,
                'wallet' => ['billing_mode' => 'unmetered'],
            ], $overrides),
            'message' => 'Chat completed successfully',
            'status_code' => 1,
        ];
    }

    public function test_guest_cannot_use_ai_chat(): void
    {
        Http::fake();

        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])->assertUnauthorized();

        Http::assertNothingSent();
    }

    public function test_chat_returns_only_the_fields_the_frontend_needs(): void
    {
        $this->actingAsUser();
        Http::fake([self::BASE_URL.'/*' => Http::response($this->upstreamPayload())]);

        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'reply' => 'مرحباً بك. كيف يمكنني مساعدتك اليوم؟',
                'truncated' => false,
                'session_id' => 'c25d7ec8-b2f0-4938-9bab-aaa740014c31',
                'model' => 'gemini-2.5-flash',
                'department_id' => 'advisor',
            ]);

        Http::assertSent(function (Request $request) {
            return $request->url() === self::BASE_URL.'/api/chat'
                && $request->method() === 'POST'
                && $request['message'] === 'مرحبا'
                && $request['session_id'] === null
                && $request['department_id'] === 'advisor';
        });
    }

    public function test_chat_forwards_an_explicit_department_id(): void
    {
        $this->actingAsUser();
        Http::fake([self::BASE_URL.'/*' => Http::response($this->upstreamPayload())]);

        $this->postJson('/api/ai/chat', [
            'message' => 'صنّف نشاطي',
            'department_id' => 'isic4',
        ])->assertOk()->assertJsonPath('department_id', 'isic4');

        Http::assertSent(fn (Request $request) => $request['department_id'] === 'isic4');
    }

    public function test_continue_reuses_the_department_bound_to_the_session(): void
    {
        $this->actingAsUser();
        Http::fake([
            self::BASE_URL.'/api/chat' => Http::sequence()
                ->push($this->upstreamPayload(['truncated' => true, 'reply' => 'جزء أول']))
                ->push($this->upstreamPayload([
                    'reply' => 'البقية',
                    'full_reply' => 'جزء أول البقية',
                    'truncated' => false,
                ])),
        ]);

        $this->postJson('/api/ai/chat', [
            'message' => 'اشرح',
            'department_id' => 'isic4',
        ])->assertOk();

        $this->postJson('/api/ai/chat/continue')
            ->assertOk()
            ->assertJsonPath('department_id', 'isic4');

        $calls = collect(Http::recorded())->filter(
            fn ($pair) => $pair[0]->url() === self::BASE_URL.'/api/chat'
        )->values();

        $this->assertSame('isic4', $calls[0][0]['department_id']);
        $this->assertSame('isic4', $calls[1][0]['department_id']);
        $this->assertTrue((bool) $calls[1][0]['continue']);
    }

    public function test_outbound_requests_include_the_service_api_key_when_configured(): void
    {
        config(['services.ai_service.api_key' => 'secret-ai-key']);
        $this->actingAsUser();
        Http::fake([self::BASE_URL.'/*' => Http::response($this->upstreamPayload())]);

        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])->assertOk();

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer secret-ai-key');
        });
    }

    public function test_second_message_reuses_the_stored_session_id(): void
    {
        $this->actingAsUser();
        Http::fake([self::BASE_URL.'/*' => Http::response($this->upstreamPayload())]);

        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])->assertOk();
        $this->postJson('/api/ai/chat', ['message' => 'ما هي شروط التمويل؟'])->assertOk();

        $sent = Http::recorded();
        $this->assertCount(2, $sent);
        $this->assertNull($sent[0][0]['session_id']);
        $this->assertSame('c25d7ec8-b2f0-4938-9bab-aaa740014c31', $sent[1][0]['session_id']);
    }

    public function test_session_id_is_isolated_per_user(): void
    {
        $this->actingAsUser();
        Http::fake([self::BASE_URL.'/*' => Http::response($this->upstreamPayload())]);
        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])->assertOk();

        $this->actingAsUser();
        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])->assertOk();

        $sent = Http::recorded();
        $this->assertNull($sent[1][0]['session_id']);
    }

    public function test_reset_starts_a_new_ai_session(): void
    {
        $this->actingAsUser();
        Http::fake([self::BASE_URL.'/*' => Http::response($this->upstreamPayload())]);

        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])->assertOk();
        $this->postJson('/api/ai/chat/reset')->assertOk()->assertJsonPath('success', true);
        $this->postJson('/api/ai/chat', ['message' => 'مرحبا من جديد'])->assertOk();

        $sent = Http::recorded();
        $this->assertNull($sent[1][0]['session_id']);
    }

    public function test_message_is_required(): void
    {
        $this->actingAsUser();
        Http::fake();

        $this->postJson('/api/ai/chat', ['message' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors('message');

        Http::assertNothingSent();
    }

    public function test_message_length_is_capped(): void
    {
        $this->actingAsUser();
        Http::fake();

        $this->postJson('/api/ai/chat', ['message' => str_repeat('ا', 5001)])
            ->assertStatus(422)
            ->assertJsonValidationErrors('message');

        Http::assertNothingSent();
    }

    public function test_upstream_error_status_becomes_a_friendly_502(): void
    {
        $this->actingAsUser();
        Http::fake([self::BASE_URL.'/*' => Http::response('boom', 500)]);

        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])
            ->assertStatus(502)
            ->assertJsonPath('message', 'عذراً، تعذر الاتصال بالمستشار الذكي حالياً. يرجى المحاولة مرة أخرى.');
    }

    public function test_connection_timeout_becomes_a_friendly_502(): void
    {
        $this->actingAsUser();
        Http::fake(fn () => throw new ConnectionException('Connection timed out'));

        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])
            ->assertStatus(502)
            ->assertJsonPath('message', 'عذراً، تعذر الاتصال بالمستشار الذكي حالياً. يرجى المحاولة مرة أخرى.');
    }

    public function test_missing_reply_in_upstream_payload_becomes_a_friendly_502(): void
    {
        $this->actingAsUser();
        Http::fake([self::BASE_URL.'/*' => Http::response(['data' => ['session_id' => 'abc']])]);

        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])->assertStatus(502);
    }

    public function test_invalid_upstream_body_becomes_a_friendly_502(): void
    {
        $this->actingAsUser();
        Http::fake([self::BASE_URL.'/*' => Http::response('<html>not json</html>', 200)]);

        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])->assertStatus(502);
    }

    public function test_a_truncated_reply_is_flagged_so_the_user_can_continue_it(): void
    {
        $this->actingAsUser();
        Http::fake([self::BASE_URL.'/*' => Http::response($this->upstreamPayload([
            'reply' => 'الجزء الأول من الرد',
            'truncated' => true,
        ]))]);

        $this->postJson('/api/ai/chat', ['message' => 'اشرح لي بالتفصيل'])
            ->assertOk()
            ->assertJsonPath('truncated', true);
    }

    public function test_continue_resumes_the_current_session_and_returns_the_full_reply(): void
    {
        $this->actingAsUser();
        Http::fake([
            self::BASE_URL.'/*' => Http::sequence()
                ->push($this->upstreamPayload(['reply' => 'الجزء الأول', 'truncated' => true]), 200)
                ->push($this->upstreamPayload([
                    'reply' => ' والجزء الثاني',
                    'full_reply' => 'الجزء الأول والجزء الثاني',
                    'truncated' => false,
                ]), 200),
        ]);

        $this->postJson('/api/ai/chat', ['message' => 'اشرح لي بالتفصيل'])->assertOk();

        $this->postJson('/api/ai/chat/continue')
            ->assertOk()
            ->assertJsonPath('full_reply', 'الجزء الأول والجزء الثاني')
            ->assertJsonPath('truncated', false);

        $sent = Http::recorded();
        $this->assertTrue($sent[1][0]['continue']);
        $this->assertSame('c25d7ec8-b2f0-4938-9bab-aaa740014c31', $sent[1][0]['session_id']);
        $this->assertSame('advisor', $sent[1][0]['department_id']);
    }

    public function test_continue_without_a_conversation_is_rejected(): void
    {
        $this->actingAsUser();
        Http::fake();

        $this->postJson('/api/ai/chat/continue')
            ->assertStatus(422)
            ->assertJsonPath('message', 'لا توجد محادثة جارية لإكمالها.');

        Http::assertNothingSent();
    }

    public function test_a_failed_call_does_not_lose_the_conversation_context(): void
    {
        $this->actingAsUser();
        Http::fake([
            self::BASE_URL.'/*' => Http::sequence()
                ->push($this->upstreamPayload(), 200)
                ->push('boom', 503)
                ->push($this->upstreamPayload(), 200),
        ]);

        $this->postJson('/api/ai/chat', ['message' => 'مرحبا'])->assertOk();
        $this->postJson('/api/ai/chat', ['message' => 'ثانية'])->assertStatus(502);
        $this->postJson('/api/ai/chat', ['message' => 'ثالثة'])->assertOk();

        $sent = Http::recorded();
        $this->assertCount(3, $sent);
        $this->assertSame('c25d7ec8-b2f0-4938-9bab-aaa740014c31', $sent[2][0]['session_id']);
    }
}
