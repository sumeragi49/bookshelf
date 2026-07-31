<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class BookTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $seed = true;
    //基本機能のテスト
    public function test_guest_index()
    {
        //login無ならactingAs()使わない
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である')
                 ->assertSee('FACTFULNESS');
    }

    public function test_guest_show()
    {
        $response = $this->get('books/1');

        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である')
                 ->assertSee('夏目漱石')
                 ->assertSee('1905-01-01')
                 ->assertSee('9784101010014')
                 ->assertSee('名前なら既にあるにゃん！');
    }

    public function test_user_index()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->get('/books');

        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である')
                 ->assertSee('FACTFULNESS');
    }

    public function test_user_show()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->get('/books/1');

        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である')
                 ->assertSee('夏目漱石')
                 ->assertSee('9784101010014')
                 ->assertSee('1905-01-01')
                 ->assertSee('名前なら既にあるにゃん！');
    }

    public function test_create_book()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->get('/books/create');

        $response->assertStatus(200);
        $response->assertSee('タイトル')
                 ->assertSee('著者')
                 ->assertSee('ISBN-13')
                 ->assertSee('出版日')
                 ->assertSee('説明')
                 ->assertSee('画像URL')
                 ->assertSee('ジャンル');
    }

    public function test_store_book()
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
            'genres' => [1,6],
        ];

        $response = $this->actingAs($user)->post('/books', $book);

        $response->assertStatus(302);

        $storeBook = Book::latest('id')
                  -> first();

        $this->assertDatabaseHas('books', [
            'isbn' => "9784061330122",
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

    public function test_edit_book()
    {
        $book = Book::with('user', 'genres')
             -> find(1);

        $response = $this->actingAs($book->user)->get('/books/1/edit');

        $response->assertStatus(200);
        $response->assertSee($book->title)
                 ->assertSee($book->author)
                 ->assertSee($book->isbn)
                 ->assertSee($book->published_date->format('Y-m-d'))
                 ->assertSee($book->description)
                 ->assertSee($book->image_url)
                 ->assertSee($book->genres->first()?->name);
    }

    public function test_other_user_edit_book()
    {
        $user = User::factory()->create();

        $book = Book::with('user', 'genres')
             -> find(1);

        $response = $this->actingAs($user)->get("/books/{$book->id}/edit");

        $response->assertStatus(403);
        $response->assertSee('禁止されています');
    }

    public function test_update_book()
    {
        $book = Book::with('user', 'genres')
             -> find(1);

        $updateBook = ([
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '名前が欲しい！',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
            'genres' => [1],
        ]);
        
        $response = $this->actingAs($book->user)->put("/books/{$book->id}", $updateBook);

        $response->assertStatus(302);

        $this->assertDatabaseHas(Book::class, [
            'description' => "名前が欲しい！",
        ]);
    }

    public function test_delete_book()
    {
        $book = Book::with('user', 'genres')
             -> find(1);

        $response  = $this->actingAs($book->user)->delete("/books/{$book->id}");

        $response->assertStatus(302);
        $this->assertModelMissing($book);
    }

    public function test_favorite_book()
    {
        $user = User::find(1);

        $favoriteBooks = $user->favoriteBooks()
                      -> latest()
                      -> take(10)
                      -> get();

        $response = $this->actingAs($user)->get('/favorite');

        $response->assertStatus(200);
        //データの有無の確認
        $this->assertNotEmpty($favoriteBooks, 'データ無');
        //$favoriteBooksがある時,お気に入りの本全てのタイトルがあるかの確認
        foreach ($favoriteBooks as $book) {
            $response->assertSee($book->title);
        }
    }

    public function test_ranking_book()
    {
        $user = User::find(1);

        $books = Book::with('reviews')
               -> withAvg('reviews', 'rating')
               -> withCount('reviews')
               -> orderByDesc('reviews_avg_rating')
               -> take(10)
               -> get();
        //$booksはオブジェクト,それを順番を固定したarray(配列、固定値、文字列)とする
        $rankingBooks = $books->pluck('title')->toArray();

        $response = $this->actingAs($user)->get('/ranking');

        $response->assertStatus(200);
        //指定したarrayが順番も指定どうりかまで検証するメソッド
        $response->assertSeeInOrder($rankingBooks);
    }

    public function test_favorite_toggle_book()
    {
        $userId = \DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::find($userId);

        $bookId = \DB::table('books')->insertGetId([
            'user_id' => $user->id,
            'title' => 'test Book',
            'author' => 'Test Author',
            'isbn' => '978' . rand(1000000000, 9999999999),
            'published_date' => '2000-01-01',
            'description' => 'This is a test book description.',
            'image_url' => 'https://example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('book_genre')->insert([
            ['book_id' => $bookId, 'genre_id' => 1],
            ['book_id' => $bookId, 'genre_id' => 7],
        ]);

        $book = Book::find($bookId);

        $response = $this->actingAs($user)->post("/books/{$book->id}/favorites");

        $response->assertStatus(302);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    //応用機能のテスト
    public function test_index_search_keyword_book()
    {
        $user = User::find(1);

        $keyword = '吾輩';
        $genre = '';
        $sort ='';

        $queryParams = [
            'keyword' => $keyword,
            'genre' => $genre,
            'sort' => $sort,
        ];

        $response = $this->actingAs($user)->get('/books?' . http_build_query($queryParams));

        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である');
    }

    public function test_index_select_genre_book()
    {
        $user = User::find(1);

        $keyword = '';
        $genre = '1';
        $sort ='';

        $queryParams = [
            'keyword' => $keyword,
            'genre' => $genre,
            'sort' => $sort,
        ];

        $response = $this->actingAs($user)->get('/books?' . http_build_query($queryParams));

        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である')
                 ->assertSee('坊っちゃん')
                 ->assertSee('火花');
    }

    public function test_index_sort_oldest_book()
    {
        $user = User::find(1);

        $books = Book::orderBy('created_at', 'desc')
             -> take(10)
             -> pluck('title')
             -> toArray();

        $keyword = '';
        $genre = '';
        $sort ='oldest';

        $queryParams = [
            'keyword' => $keyword,
            'genre' => $genre,
            'sort' => $sort,
        ];

        $response = $this->actingAs($user)->get('/books?' . http_build_query($queryParams));

        $response->assertStatus(200);
        $response->assertSeeInOrder($books);
    }

    public function test_index_sort_title_book()
    {
        $user = User::find(1);

        $books = Book::orderBy('title', 'asc')
             -> take(10)
             -> pluck('title')
             -> toArray();

        $keyword = '';
        $genre = '';
        $sort ='title';

        $queryParams = [
            'keyword' => $keyword,
            'genre' => $genre,
            'sort' => $sort,
        ];

        $response = $this->actingAs($user)->get('/books?' . http_build_query($queryParams));

        $response->assertStatus(200);
        $response->assertSeeInOrder($books);
    }

    public function test_index_sort_rating_book()
    {
        $user = User::find(1);

        $books = Book::query()
             -> withAvg('reviews as rating', 'rating')
             -> sortOrder('rating')
             -> take(10)
             -> pluck('title')
             -> toArray();

        $keyword = '';
        $genre = '';
        $sort ='rating';

        $queryParams = [
            'keyword' => $keyword,
            'genre' => $genre,
            'sort' => $sort,
        ];

        $response = $this->actingAs($user)->get('/books?' . http_build_query($queryParams));

        $response->assertStatus(200);
        $response->assertSeeInOrder($books);
    }

    public function test_create_search_isbn_book()
    {
        $user = User::find(1);
        //*は「ワイルドカード」(com.以下がなんでもそのurlとする。)
        Http::fake([
            'https://www.googleapis.com*' => Http::response([
                'items' => [['volumeInfo' => [
                    'title' => '罪と罰',
                    'authors' => ['北垣信之(訳)'],
                    'isbn' => '9784061330122',
                    'publishedDate' => '1871-01-01',
                    'description' => '因果応報、報いを！',
                    'imageLinks' => [
                        'thumbnail' => 'https://placehold.co',
                    ],
                    'categories' => [1,6],
                ]]]
            ], 200)
        ]);

        $response = $this->actingAs($user)->get("/books/isbn/9784061330122");

        $response->assertStatus(200);
        $response->assertJson([
            'title' => '罪と罰',
                'author' => '北垣信之(訳)',
                'isbn' => '9784061330122',
                'published_date' => '1871-01-01',
                'description' => '因果応報、報いを！',
                'image_url' => 'https://placehold.co',
                'genres' => '1, 6',
        ]);
    }
}
