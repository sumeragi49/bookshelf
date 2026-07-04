<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        if ($users->isEmpty() || $books->isEmpty()) {
            return;
        }

        $comments = [
            '的確なコメントだと思いました！',
            '自分でも今度読みたいと思います！',
            '自分でも読んでみてたいへん面白いと思いました！',
            '続編が気になります',
            '難しかったですが読みごたえがありました！',
        ];

        $totalReviewsCreated = 0;
        $targetCount = 32;

        while ($totalReviewsCreated < $targetCount) {
            foreach ($books as $book) {
                if ($totalReviewsCreated >= $targetCount) {
                    break 2;
                }

                $maxRemaining = $targetCount - $totalReviewsCreated;
                $reviewerCount = min(rand(2,4), $maxRemaining);

                $reviewers = $users->shuffle()->take($reviewerCount);

                foreach ($reviewers as $user) {
                    Review::create([
                        'user_id' => $user->id,
                        'book_id' => $book->id,
                        'rating' => rand(1, 5),
                        'comment' => $comments[array_rand($comments)],
                    ]);

                    $totalReviewsCreated++;
                }
            }
        }
    }
}
