<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\AiModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = $this->faker->randomElement(['user', 'ai']);

        return [
            'conversation_id' => Conversation::factory(),
            'ai_model_id' => $role === 'ai' ? AiModel::inRandomOrder()->first()?->id : null, 
            'role' => $role,
            'content' => $this->faker->text(120),
            'tokens_used' => $role === 'ai' ? $this->faker->numberBetween(50, 2000) : null,
            'is_error' => $this->faker->boolean(5),
        ];
    }
}
