<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReplyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'message_id' => Message::factory()->create(),
            'user_id' => User::factory()->create(),
            'reply' => $this->faker->sentence(),
        ];
    }
}
