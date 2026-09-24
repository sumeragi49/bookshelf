<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class GenreTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $seed = true;
    //一覧表示のテスト
    public function test_index_genre()
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->has(Book::factory()->count(2))->count(3)->create();

        $response = $this->actingAs($user)->get(route('genres.index'));

        $response->assertStatus(200);
        
        foreach ($genres as $genre) {
            $response->assertSee($genre->name);
        }
    }
    // 登録画面表示のテスト
    public function test_create_genre()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.create'));

        $response->assertStatus(200);
        $response->assertSee('ジャンル名');
    }
    // ジャンル登録のテスト
    public function test_store_genre()
    {
        $user = User::factory()->create();

        $genre = [
            'name' => 'SF',
        ];

        $response = $this->actingAs($user)->post(route('genres.store'), $genre);

        $response->assertStatus(302);
        $this->assertDatabaseHas('genres', [
            'name' => 'SF',
        ]);
    }
    //ジャンル詳細画面表示のテスト
    public function test_show_genre()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => 'SF']);

        $response = $this->actingAs($user)->get(route('genres.show', $genre));

        $response->assertStatus(200);
        $response->assertSee('SF');
    }
    //ジャンル編集画面表示のテスト
    public function test_edit_genre()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => 'SF']);

        $response = $this->actingAs($user)->get(route('genres.edit', $genre));

        $response->assertStatus(200);
        $response->assertSee('SF');
    }

    public function test_update_genre()
    {
        $user = User::find(1);

        $genre = Genre::find(1);

        $updateGenre = [
            'name' => '小説・携帯小説',
        ];

        $response = $this->actingAs($user)->put(route('genres.update', $genre->id), $updateGenre);

        $response->assertStatus(302);
        $this->assertDatabaseHas('genres', [
            'name' => '小説・携帯小説'
        ]);
    }

    public function test_delete_genre()
    {
        $user = User::find(1);

        $genre = Genre::find(1);

        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre->id));

        $response->assertStatus(302);
        $this->assertModelMissing($genre);
    }
}
