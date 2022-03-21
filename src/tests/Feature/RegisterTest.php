<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function testRegisterValidationWorks()
    {
        $this->postJson('/api/register')
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password'
            ]);
    }

    public function testAUserCanBeRegistered()
    {
        $this->postJson('/api/register', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ])->assertOk()
            ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('users', [
            'email' => 'test@test.com',
        ]);
    }
}
