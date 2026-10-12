<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Book;
use App\Models\User;
use App\Models\ReadingPlan;
use Carbon\Carbon
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReadingPlan>
 */
class ReadingPlanFactory extends Factory
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
            'book_id' => Book::factory(),
            'target_date' => now()->addMonth(1),
            'completed_at' => now()->addMonth(1),
            'status' => $this->faker->randomElement(['in_progress', 'completed', 'expired']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
