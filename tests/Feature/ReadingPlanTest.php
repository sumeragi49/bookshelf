<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;

class ReadingPlanTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_index_reading_plan()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->get(route('reading-plans.index'));

        $response->assertStatus(200);
        $response->assertSee('未読')
                 ->assertSee('読了');
    }

    public function test_create_reading_plan()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->get(route('reading-plans.create'));

        $response->assertStatus(200);
        $response->assertSee('新規読書計画作成')
                 ->assertSee('書籍')
                 ->assertSee('期日');
    }

    public function test_store_reading_plan()
    {
        $user = User::find(1);

        $book = Book::find(4);
        
        $readingPlan = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-08-06 00:00:00',
            'status' => 0
        ];

        $response = $this->actingAs($user)->post(route('reading-plans.store'), $readingPlan);

        $response->assertStatus(302);
        $this->assertDatabaseHas('reading_plan', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            //00:00:00まで入れないとエラーになる。
            'target_date' => '2026-08-06 00:00:00',
            'status' => '0',
        ]);
    }

    public function test_edit_reading_plan()
    {
        $user = User::find(1);

        $readingPlan = ReadingPlan::find(1);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $readingPlan->id));

        $response->assertStatus(200);
        $response->assertSee('読書計画編集')
                 ->assertSee('吾輩は猫である')
                 ->assertSee('未読')
                 ->assertSee('2026-08-06');
    }

    public function test_complete_reading_plan()
    {
        $this->freezeTime();
        $nowStr = now()->format('Y-m-d H:i:s');

        $user = User::find(1);

        $readingPlan = ReadingPlan::find(1);

        $completeStatus = [
            'completed_at' => $nowStr,
            'status' => 2
        ];

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $readingPlan->id), $completeStatus);

        $response->assertStatus(302);

        $this->assertDatabaseHas('reading_plan', [
            'user_id' => 1,
            'book_id' => 1,
            'target_date' => '2026-08-06',
            'completed_at' => $nowStr,
            'status' => 2,
        ]);
    }

    public function test_update_reading_plan()
    {
        $user = User::find(1);

        $readingPlan = ReadingPlan::find(1);

        $updateReadingPlan = [
            'user_id' => $user->id,
            'book_id' => $readingPlan->book->id,
            'target_date' => '2026-08-15',
        ];

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan->id), $updateReadingPlan);

        $response->assertStatus(302);
        $this->assertDatabaseHas('reading_plan', [
            'target_date' => '2026-08-15 00:00:00',
            'status' => 0,
        ]);
    }

    public function test_delete_reading_plan()
    {
        $user = User::find(1);

        $readingPlan = ReadingPlan::find(1);

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertStatus(302);
        $this->assertModelMissing($readingPlan);
    }
}
