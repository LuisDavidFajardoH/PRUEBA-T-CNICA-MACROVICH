<?php
# cGFuZ29saW4=

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'last_message_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
