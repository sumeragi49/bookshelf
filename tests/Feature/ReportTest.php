<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\ReadingPlan;
use Illuminate\Support\Facades\DB;

class ReportTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_report_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index'));
        
        $response->assertStatus(200);
    }
    //guestによるレポートページへのアクセス不可の検証
    public function test_guest_cannot_access_report_index()
    {
        $response = $this->get(route('reports.index'));
        
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_access_report_index_empty_data()
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => 1,
            'status' => 'in_progress',
        ]);
        
        $response = $this->actingAs($user)->get(route('reports.index'));
        $response->assertStatus(200);

        /*
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 0
                && $stats['summery']['book_read'] === 0
                && $stats['summery']['average_rating'] === 0
                && $stats['rating_distribution']->toArray() === [0,0,0,0,0]
                && empty($stats['top_rated_books'])
                && empty($stats['genre_rating']);
        });
        */

        $response->assertSee('0');
    }

    public function test_user_can_report_index_correct_aggregated_stats()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $genre = Genre::factory()->create(['name' => '自叙伝']);

        $book1 = Book::factory()->hasAttached($genre)->create(['user_id' => $user->id, 'title' => '評価が5', 'author' => '著者A']);
        $book2 = Book::factory()->hasAttached($genre)->create(['user_id' => $user->id, 'title' => '評価が3', 'author' => '著者B']);

        $review1 = Review::factory()->create(['user_id' => $otherUser, 'book_id' => $book1->id, 'rating' => 5]);
        $review2 = Review::factory()->create(['user_id' => $otherUser,'book_id' => $book2->id, 'rating' => 3]);
        $review3 = Review::factory()->create(['user_id' => $user,'book_id' => $book1->id, 'rating' => 5]);

        ReadingPlan::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'status' => 'completed']);
        ReadingPlan::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'status' => 'completed']);
        ReadingPlan::factory()->create(['user_id' => $otherUser->id, 'book_id' => $book1->id,'status' => 'completed']);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
        
        $response->assertSee('2');
        $response->assertSee('2');
        $response->assertSee('4.0');

        $response->assertSee('評価が5');
        $response->assertSee('著者A');
        $response->assertDontSee('評価が3');

        $response->assertSee('自叙伝');
    }
}
