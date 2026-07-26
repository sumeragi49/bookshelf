<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Genre;

class GenreTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_index_genre()
    {
        $user = User::find(1);

        $genres = Genre::with('books')
               -> withCount('books')
               -> get();

        $response = $this->actingAs($user)->get(route('genres.index'));

        $response->assertStatus(200);
        
        foreach ($genres as $genre) {
            $response->assertSee($genre->name);
        }
    }

    public function test_create_genre()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->get(route('genres.create'));

        $response->assertStatus(200);
        $response->assertSee('ジャンル名');
    }

    public function test_store_genre()
    {
        $user = User::find(1);

        $genre = [
            'name' => 'SF',
        ];

        $response = $this->actingAs($user)->post(route('genres.store'), $genre);

        $response->assertStatus(302);
        $this->assertDatabaseHas('genres', [
            'name' => 'SF',
        ]);
    }

    public function test_show_genre()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->get("/genres/1");

        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である')
                 ->assertSee('坊っちゃん')
                 ->assertSee('火花');
    }

    public function test_edit_genre()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->get("/genres/1/edit");

        $response->assertStatus(200);
        $response->assertSee('小説');
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
