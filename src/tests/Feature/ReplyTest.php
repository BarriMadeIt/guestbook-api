<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReplyTest extends TestCase
{
    use RefreshDatabase;

    private $message;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->message = Message::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Sanctum::actingAs($this->user, ['*']);
    }

    public function testRequireMessageMiddlewareWorks()
    {
        $this->getJson('api/replies/list')
            ->assertStatus(417);
    }

    public function testMessageIsOwnedByUser()
    {
        $message = Message::factory()->create();

        $this->json('GET', '/api/replies/list', [
            'message_id' => $message->id,
        ])->assertStatus(404);
    }

    public function testRepliesCanBeFetched()
    {    
        Reply::factory(4)->create([
            'message_id' => $this->message->id,
            'user_id' => $this->user->id,
        ]);

        $this->json('GET', '/api/replies/list', [
            'message_id' => $this->message->id,
        ])->assertJson(fn (AssertableJson $json) => $json->has('replies', 4));
    }

    public function testOtherMessageRepliesArentFetched()
    {
        $message = Message::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Reply::factory(4)->create([
            'message_id' => $message->id,
        ]);

        Reply::factory()->create([
            'message_id' => $this->message->id,
            'user_id' => $this->user->id,
        ]);

        $this->json('GET', '/api/replies/list', [
            'message_id' => $this->message->id,
        ])->assertJson(fn (AssertableJson $json) => $json->has('replies', 1));
    }

    public function testOtherUserRepliesArentFetched()
    {
        $user = User::factory()->create();
        $message = Message::factory()->create([
            'user_id' => $user->id,
        ]);

        Reply::factory(4)->create([
            'message_id' => $message->id,
        ]);

        Reply::factory()->create([
            'message_id' => $this->message->id,
            'user_id' => $this->user->id,
        ]);

        $this->json('GET', '/api/replies/list', [
            'message_id' => $this->message->id,
        ])->assertJson(fn (AssertableJson $json) => $json->has('replies', 1));
    }

    public function testAddReplyValidationWorks()
    {
        $this->postJson('/api/replies/add', [
            'message_id' => $this->message->id,
        ])->assertStatus(422)
            ->assertJsonValidationErrors([
                'reply',
            ]);
    }

    public function testAReplyCanBeAdded()
    {
        $this->postJson('/api/replies/add', [
            'message_id' => $this->message->id,
            'reply' => 'Test reply',
        ])->assertOk()
            ->assertJsonStructure(['reply_id']);

        $this->assertDatabaseHas('replies', [
            'message_id' => $this->message->id,
            'reply' => 'Test reply',
        ]);
    }

    public function testAdminCanAddReply()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        Sanctum::actingAs($admin, ['*']);

        $this->postJson('/api/replies/add', [
            'message_id' => $this->message->id,
            'reply' => 'Test reply from admin',
        ])->assertOk()
            ->assertJsonStructure(['reply_id']);

        $this->assertDatabaseHas('replies', [
            'message_id' => $this->message->id,
            'reply' => 'Test reply from admin',
        ]);
    }

    public function testUpdateReplyValidationWorks()
    {
        $reply = Reply::factory()->create([
            'message_id' => $this->message->id,
        ]);

        $this->putJson('/api/replies/update/' . $reply->id, [
            'message_id' => $this->message->id,
        ])->assertStatus(422)
            ->assertJsonValidationErrors([
                'reply',
            ]);
    }

    public function testAReplyCanBeUpdated()
    {
        $reply = Reply::factory()->create([
            'message_id' => $this->message->id,
            'reply' => 'Test reply',
        ]);

        $this->putJson('/api/replies/update/' . $reply->id, [
            'message_id' => $this->message->id,
            'reply' => 'Test reply is updated',
        ])->assertOk();

        $this->assertDatabaseHas('replies', [
            'id' => $reply->id,
            'message_id' => $this->message->id,
            'reply' => 'Test reply is updated',
        ]);
    }

    public function testOtherMessageRepliesCannotBeUpdated()
    {
        $message = Message::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $reply = Reply::factory()->create([
            'message_id' => $message->id,
            'reply' => 'Test reply',
        ]);

        $this->putJson('/api/replies/update/' . $reply->id, [
            'message_id' => $this->message->id,
            'reply' => 'Test reply updated',
        ])->assertStatus(404);

        $this->assertDatabaseHas('replies', [
            'id' => $reply->id,
            'message_id' => $message->id,
            'reply' => $reply->reply,
        ]);
    }

    public function testAReplyCanBeArchived()
    {
        $reply = Reply::factory()->create([
            'message_id' => $this->message->id,
            'reply' => 'Test reply',
        ]);

        $this->deleteJson('/api/replies/archive/' . $reply->id, [
            'message_id' => $this->message->id,
        ])->assertOk();

        $this->assertSoftDeleted($reply);
    }

    public function testOtherMessageRepliesCannotBeArchived()
    {
        $message = Message::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $reply = Reply::factory()->create([
            'message_id' => $message->id,
            'reply' => 'Test reply',
        ]);

        $this->deleteJson('/api/replies/archive/' . $reply->id, [
            'message_id' => $this->message->id,
        ])->assertStatus(404);

        $this->assertDatabaseHas('replies', [
            'id' => $reply->id,
            'message_id' => $message->id,
            'deleted_at' => null,
        ]);
    }

    public function testAdminCanArchiveReplies()
    {
        $reply = Reply::factory()->create([
            'message_id' => $this->message->id,
            'reply' => 'Test reply',
        ]);

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        Sanctum::actingAs($admin, ['*']);

        $this->deleteJson('/api/replies/archive/' . $reply->id, [
            'message_id' => $this->message->id,
        ])->assertOk();

        $this->assertSoftDeleted($reply);
    }

    public function testRepliesAreCached()
    {
        Reply::factory(4)->create([
            'message_id' => $this->message->id,
        ]);

        $this->json('GET', '/api/replies/list', [
            'message_id' => $this->message->id,
        ])->assertOk();
        
        $this->assertTrue(Cache::has('replies_of_' . $this->message->id));

        $this->postJson('/api/replies/add', [
            'message_id' => $this->message->id,
            'reply' => 'Test reply',
        ])->assertOk();
        
        $this->assertTrue(Cache::missing('replies_of_' . $this->message->id));
    }
}
