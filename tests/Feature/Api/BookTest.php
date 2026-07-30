<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\review;
use Laravel\Sanctum\Sanctum;

class BookTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_api_index_book()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->getJson('/api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'description',
                    'image_url',
                    'genres',
                    'average_rating',
                    'review_count',
                ]
            ]
        ]);
    }

    public function test_api_show_book()
    {
        $user = User::find(1);

        $book = Book::find(1);

        $reviews = Review::where('book_id', $book->id)
                -> get();

        $response = $this->actingAs($user)->getJson('/api/v1/books/1');

        $response->assertStatus(200);

        $response->assertJsonPath('book.title', '吾輩は猫である');
        $response->assertJsonPath('book.author', '夏目漱石');
        $response->assertJsonPath('book.published_date', '1905-01-01 00:00:00');

        $response->assertJsonStructure([
            'book' => [
                'id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'genres',
            ],
            'reviews' => [
                '*' => [
                    'id',
                    'user_id',
                    'book_id',
                    'rating',
                    'comment',
                ]
            ]
        ]);
    }

    public function test_api_store_book()
    {
        $user = User::find(1);

        $book = [
            'user_id' => $user->id,
            'title' => '罪と罰',
            'author' => '北垣信之(訳)',
            'isbn' => '9784061330122',
            'published_date' => '1871-01-01',
            'description' => '因果応報、報いを！',
            'image_url' => 'https://placehold.co',
            'genres' => array(1, 6),
        ];

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', $book);

        $response->assertStatus(201);

        $storeBook = Book::latest('id')
                  -> first();

        $this->assertDatabaseHas('books', [
            'user_id' => 1,
            'title' => '罪と罰',
            'author' => '北垣信之(訳)',
            'isbn' => '9784061330122',
            'published_date' => '1871-01-01',
            'description' => '因果応報、報いを！',
            'image_url' => 'https://placehold.co',
        ]);
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $storeBook->id,
            'genre_id' => 1,
        ]);
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $storeBook->id,
            'genre_id' => 6,
        ]);
    }

    public function test_api_update_book()
    {
        $user = User::find(1);

        $book = Book::find(1);

        $updateBook = ([
            'title' => '吾輩は猫である(訳)',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '名前が欲しい！',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
            'genres' => [1,5],
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/books/{$book->id}", $updateBook);

        $response->assertStatus(200);

        $storeBook = Book::latest('updated_at')
                  -> first();

        $this->assertDatabaseHas('books', [
            'title' => '吾輩は猫である(訳)',
        ]);
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $storeBook->id,
            'genre_id' => 1,
        ]);
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $storeBook->id,
            'genre_id' => 5,
        ]);
    }

    public function test_api_delete_book()
    {
        $user = User::find(1);

        $book = Book::find(1);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);

        $this->assertModelMissing($book);
    }
}
