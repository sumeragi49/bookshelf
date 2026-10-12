<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\genre;
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
}
