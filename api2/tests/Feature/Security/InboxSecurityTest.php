<?php

namespace Tests\Feature\Security;

use App\Models\InboxMessage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InboxSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Role::findOrCreate('trainer_user', 'sanctum');
    }

    private function createThread(User $sender, User $recipient): InboxMessage
    {
        return InboxMessage::query()->create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'موضوع',
            'body' => 'محتوى',
            'is_broadcast' => false,
        ]);
    }

    public function test_sender_can_reply_to_message(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $message = $this->createThread($sender, $recipient);

        Sanctum::actingAs($sender);

        $this->postJson("/api/inbox/{$message->id}/reply", ['body' => 'رد المرسل'])
            ->assertCreated()
            ->assertJsonPath('data.sender_id', $sender->id)
            ->assertJsonPath('data.recipient_id', $recipient->id);
    }

    public function test_recipient_can_reply_to_message(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $message = $this->createThread($sender, $recipient);

        Sanctum::actingAs($recipient);

        $this->postJson("/api/inbox/{$message->id}/reply", ['body' => 'رد المستقبل'])
            ->assertCreated()
            ->assertJsonPath('data.sender_id', $recipient->id)
            ->assertJsonPath('data.recipient_id', $sender->id);
    }

    public function test_third_party_cannot_reply(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $intruder = User::factory()->create();
        $message = $this->createThread($sender, $recipient);

        Sanctum::actingAs($intruder);

        $this->postJson("/api/inbox/{$message->id}/reply", ['body' => 'رد غير مصرح'])
            ->assertForbidden();
    }

    public function test_reply_ignores_spoofed_sender_and_recipient_ids(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $other = User::factory()->create();
        $message = $this->createThread($sender, $recipient);

        Sanctum::actingAs($recipient);

        $this->postJson("/api/inbox/{$message->id}/reply", [
            'body' => 'رد',
            'sender_id' => $other->id,
            'recipient_id' => $other->id,
        ])->assertCreated()
            ->assertJsonPath('data.sender_id', $recipient->id)
            ->assertJsonPath('data.recipient_id', $sender->id);
    }

    public function test_user_cannot_read_role_targeted_broadcast_for_other_role(): void
    {
        $trainer = User::factory()->create();
        $trainer->assignRole('trainer_user');

        $broadcast = InboxMessage::query()->create([
            'sender_id' => User::factory()->create()->id,
            'subject' => 'بث للمدربين فقط',
            'body' => 'محتوى',
            'is_broadcast' => true,
            'broadcast_role' => 'branch_manager',
        ]);

        Sanctum::actingAs($trainer);

        $this->getJson("/api/inbox/{$broadcast->id}")->assertForbidden();
    }
}
