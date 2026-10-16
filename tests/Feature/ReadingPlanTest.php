<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;
use Carbon\Carbon;

class ReadingPlanTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_user_can_index_reading_plan()
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.index'));

        $response->assertStatus(200);
        $response->assertSee('未読');
    }

    public function test_guest_cannot_access_index_reading_plan()
    {
        $response = $this->get(route('reading-plans.index'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_create_reading_plan()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reading-plans.create'));

        $response->assertStatus(200);
        $response->assertSee('新規読書計画作成')
                 ->assertSee('書籍')
                 ->assertSee('期日');
    }

    public function test_store_reading_plan()
    {
        $this->withoutExceptionHandling(); 

        $user = User::factory()->create();
        $book = Book::factory()->create();

        $targetDate = now()->addMonth(1)->format('Y-m-d');
        
        $readingPlan = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
        ];

        $response = $this->actingAs($user)->post(route('reading-plans.store'), $readingPlan);

        //dd(session()->get('error')?->getBag('default')->getMessages());

        $response->assertStatus(302);

        //dump(\DB::table('reading_plan')->get());

        $this->assertDatabaseHas('reading_plan', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            //00:00:00まで入れないとエラーになる。
            'target_date' => now()->addMonth(1)->startOfDay()->toDateTimeString(),
            'status' => 'in_progress',
        ]);
    }

    public function test_user_can_edit_reading_plan()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'title' => '罪と罰'
        ]);
        $targetDate = now()->addMonth(1)->format('Y-m-d');
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => $targetDate,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $readingPlan->id));

        $response->assertStatus(200);
        $response->assertSee('読書計画編集')
                 ->assertSee($readingPlan->title)
                 ->assertSee('未読')
                 ->assertSee($targetDate);
    }

    public function test_other_user_cannot_edit_reading_plan()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create([
            'title' => '罪と罰'
        ]);
        $targetDate = now()->addMonth(1)->format('Y-m-d');
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => $targetDate,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($otherUser)->get(route('reading-plans.edit', $readingPlan->id));

        $response->assertStatus(403);
    }

    public function test_complete_reading_plan()
    {
        $this->freezeTime();
        $nowStr = now()->format('Y-m-d H:i:s');

        $readingPlan = ReadingPlan::with('user')
                    -> find(1);

        $completeStatus = [
            'completed_at' => $nowStr,
            'status' => 2
        ];

        $response = $this->actingAs($readingPlan->user)->post(route('reading-plans.complete', $readingPlan->id), $completeStatus);

        $response->assertStatus(302);

        $this->assertDatabaseHas('reading_plan', [
            'user_id' => 1,
            'book_id' => 1,
            'target_date' => '2026-08-03',
            'completed_at' => $nowStr,
            'status' => 2,
        ]);
    }

    public function test_user_can_update_reading_plan()
    {
        //$this->withoutExceptionHandling();

        $user = User::factory()->create();
        $book = Book::factory()->create();

        $targetDate = now()->addMonth(1)->format('Y-m-d');

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
        ]);

        $updateTargetDate = now()->addMonth(2)->format('Y-m-d');
        $updateTargetDateTime = now()->addMonth(2)->format('Y-m-d 00:00:00');

        $updateReadingPlan = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $updateTargetDate,
        ];

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan->id), $updateReadingPlan);

        $response->assertStatus(302);

        //dump(\DB::table('reading_plan')->get());

        $this->assertDatabaseHas('reading_plan', [
            'target_date' => $updateTargetDateTime,
        ]);
    }

    public function test_other_user_cannot_update_reading_plan()
    {
        //$this->withoutExceptionHandling();

        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $targetDate = now()->addMonth(1)->format('Y-m-d');

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
        ]);

        $updateTargetDate = now()->addMonth(2)->format('Y-m-d');
        $updateTargetDateTime = now()->addMonth(2)->format('Y-m-d 00:00:00');

        $updateReadingPlan = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $updateTargetDate,
        ];

        $response = $this->actingAs($otherUser)->put(route('reading-plans.update', $readingPlan->id), $updateReadingPlan);

        $response->assertStatus(403);

        //dump(\DB::table('reading_plan')->get());

        $this->assertDatabaseMissing('reading_plan', [
            'target_date' => $updateTargetDateTime,
        ]);
    }

    public function test_user_can_delete_reading_plan()
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertStatus(302);
        $this->assertModelMissing($readingPlan);
    }

    public function test_other_user_cannot_delete_reading_plan()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($otherUser)->delete(route('reading-plans.destroy', $readingPlan->id));

        $response->assertStatus(403);

        $this->assertDatabaseHas('reading_plan', [
            'user_id' => $user->id,
        ]);
    }
}
