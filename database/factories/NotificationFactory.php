<?php

namespace Database\Factories;

use App\Models\user;
use App\Models\InfoNotification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->realText(15);

        return [
            'id' => (string) Str::uuid(),
            'type' => InformationNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'data' => json_encode([
                'reading_plan_id' => $this->faker->numberBetween(1, 20),
                'body' => $title . 'に関するお知らせ',
                'title' => $title,
            ], JSON_UNESCAPED_UNICODE),
            'read_at' => null,
        ];
    }
}
