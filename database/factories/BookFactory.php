<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
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
            'title' => $this->faker->realText(20) . 'の本',
            'author' => $this->faker->name(),

            'isbn' => $this->faker->isbn13(),
            'published_date' => $this->faker->date(),
            'description' => $this->faker->realText(100),
            'image_url' => $this->faker->imageUrl(400, 600, 'books'),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
