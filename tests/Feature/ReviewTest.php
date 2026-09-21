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
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => '5',
            'comment' => '面白く、とても参考になる作品だと感じました。',
        ];

        $response = $this->actingAs($user)->post(route('reviews.store',$book->id), $review);

        $response->assertStatus(302);
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '面白く、とても参考になる作品だと感じました。'
        ]);
    }
    //guestでのログイン
    public function test_guest_login_redirect()
    {
        $book = Book::factory()->create();

        $review = ['rating' => 5, 'comment' => 'ゲスト投稿'];

        $response = $this->post(route('reviews.store',$book->id), $review);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_edit_review()
    {
        $review = Review::factory()->create();

        $user = $review->user;

        $response = $this->actingAs($user)->get(route('reviews.edit', $review->id));

        $response->assertStatus(200);
        $response->assertSee($review->book->title)
                 ->assertSee($review->rating)
                 ->assertSee($review->comment);
    }

    public function test_other_user_access_edit()
    {
        $review = Review::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reviews.edit', $review->id));

        $response->assertStatus(403);
    }

    public function test_reviewer_update_review()
    {
        $review = Review::factory()->create();
        $user = $review->user;

        $updateReview = [
            'rating' => 4,
            'comment' => '新しく修正したコメントです。',
        ];

        $response = $this->actingAs($user)->put(route('reviews.update', $review->id), $updateReview);

        $response->assertStatus(302);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => '新しく修正したコメントです。'
        ]);
    }

    public function test_otherUser_cannot_update_review()
    {
        $review = Review::factory()->create([
            'comment' => '元のコメント'
        ]);
        $user = User::factory()->create();

        $updateReview = [
            'rating' => 4,
            'comment' => '第三者の不正な更新内容です。',
        ];

        $response = $this->actingAs($user)->put(route('reviews.update', $review->id), $updateReview);

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => '元のコメント',
        ]);
    }

    public function test_reviewer_delete_review()
    {
        $review = Review::factory()->create();
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
