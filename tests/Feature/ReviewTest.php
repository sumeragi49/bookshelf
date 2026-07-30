<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\models\review;
use Illuminate\Support\Facades\Hash;

class ReviewTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_store_review()
    {
        $user = User::find(1);

        $book = Book::find(1);

        $review = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => '5',
            'comment' => '面白く、とても参考になる作品だと感じました。',
        ];

        $response = $this->actingAs($user)->post(route('reviews.store',$book->id), $review);

        $response->assertStatus(302);
        $this->assertDatabaseHas('reviews', [
            'comment' => '面白く、とても参考になる作品だと感じました。'
        ]);
    }

    public function test_edit_review()
    {
        $review = Review::with('book', 'user')
               -> find(1);

        $user = $review->user;

        $response = $this->actingAs($user)->get(route('reviews.edit', $review->id));

        $response->assertStatus(200);
        $response->assertSee($review->book->title)
                 ->assertSee($review->rating)
                 ->assertSee($review->comment);
    }

    public function test_update_review()
    {
        $review = Review::with('book', 'user')
               -> find(1);

        $user = $review->user;

        $updateReview = [
            'user_id' => $user->id,
            'book_id' => $review->book->id,
            'rating' => '5',
            'comment' => '面白く、とても参考になる作品だと感じました。',
        ];

        $response = $this->actingAs($user)->put(route('reviews.update', $review->id), $updateReview);

        $response->assertStatus(302);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => '面白く、とても参考になる作品だと感じました。'
        ]);
    }

    public function test_delete_review()
    {
        $review = Review::with('book', 'user')
               -> find(1);

        $user = $review->user;

        $response = $this->actingAs($user)->delete(route('reviews.destroy', $review->id));

        $response->assertStatus(302);
        $this->assertModelMissing($review);
    }

    public function test_like_toggle_book()
    {
        $userId = \DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::find($userId);

        $book = Book::find(1);

        $reviewId = \DB::table('reviews')->insertGetId([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => '5',
            'comment' => '面白く、とても参考になる作品だと感じました。',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $review = Review::find($reviewId);

        $response = $this->actingAs($user)->post(route('reviews.like', $review->id));

        $response->assertStatus(302);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }
}
