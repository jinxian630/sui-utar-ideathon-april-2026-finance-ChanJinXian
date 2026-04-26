<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Saving>
 */
class SavingFactory extends Factory
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
            'amount' => $this->faker->randomFloat(2, 5, 100),
            'category' => $this->faker->word,
            'description' => $this->faker->sentence,
            'note' => $this->faker->paragraph,
            'round_up_amount' => 0.00,
            'synced_on_chain' => false,
            'sui_digest' => null,
        ];
    }
}
