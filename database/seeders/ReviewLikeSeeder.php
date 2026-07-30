<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Review;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $reviews = Review::all();

        if ($users->isEmpty() || $reviews->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            $likeUserCount = rand(3, 5);
            $randomReviews = $reviews->shuffle()->take($likeUserCount)->pluck('id');

            $user->likedReviews()->syncWithoutDetaching($randomReviews);
        }
    }
}
