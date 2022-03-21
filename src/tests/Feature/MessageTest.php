<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        Sanctum::actingAs($this->user, ['*']);

    }

    public function testAddMessageValidationWorks()
    {
        $this->postJson('/api/messages/add')
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'message',
            ]);
    }

    public function testAMessageCanBeAdded()
    {
        Message::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->postJson('/api/messages/add', [
            'message' => 'Hello world',
        ])->assertOk();

        $this->assertDatabaseHas('messages', [
            'message' => 'Hello world',
        ]);
    }

    public function testMessagesCanBeFetched()
    {
        Message::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $this->getJson('/api/messages/list')
            ->assertJson(fn (AssertableJson $json) => $json->has('messages', 3));
    }

    public function testOtherUserMessagesArentFetched()
    {
        Message::factory()->create([
            'user_id' => $this->user->id,
        ]);
        Message::factory()->create();

        $this->getJson('/api/messages/list')
            ->assertJson(fn (AssertableJson $json) => $json->has('messages', 1));
    }

    public function testOnlyAdminCanFetchAllMessages()
    {
        Message::factory()->create([
            'user_id' => $this->user->id,
        ]);
        Message::factory()->create();

        $this->getJson('/api/messages/list-all')
            ->assertStatus(401);
    }

    public function testAdminCanFetchAllMessages()
    {
        $user = User::factory()->create([
            'is_admin' => true,
        ]);

        Sanctum::actingAs($user, ['*']);

        Message::factory()->create([
            'user_id' => $user->id,
        ]);

        Message::factory(3)->create();

        $this->getJson('/api/messages/list-all')
            ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json->has('messages', 4));
    }

    public function testUpdateMessageValidationWorks()
    {
        $message = Message::factory()->create();

        $this->putJson('/api/messages/update/' . $message->id)
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'message',
            ]);
    }

    public function testAMessageCanBeUpdated()
    {
        $message = Message::factory()->create([
            'user_id' => $this->user->id,
            'message' => 'Hello to a healthy lifestyle',
        ]);

        $this->putJson('/api/messages/update/' . $message->id, [
            'message' => 'Hello, living healthy',
        ])->assertOk();

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'message' => 'Hello, living healthy',
        ]);
    }

    public function testOtherUserCannotUpdateAMessage()
    {
        $message = Message::factory()->create();

        $this->putJson('api/messages/update' . $message->id, [
            'message' => 'Hello, living healthy',
        ])->assertStatus(404);
        
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'message' => $message->message,
        ]);
    }

    public function testAMessageCanBeArchived()
    {
        $message = Message::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->delete('/api/messages/archive/' . $message->id)
            ->assertOk();
        
        $this->assertSoftDeleted($message);
    }

    public function testOtherUserCannotArchiveAMessage()
    {
        $message = Message::factory()->create();

        $this->delete('/api/messages/archive/' . $message->id)
            ->assertStatus(404);
        
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            $message->getDeletedAtColumn() => null,
        ]);
    }
}
