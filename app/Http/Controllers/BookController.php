<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\BookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $genres = Genre::all();

        $keyword = $request->input('keyword');
        $genre = $request->input('genre');
        $sort = $request->input('sort', 'newest');

        $books = Book::query()
              -> select('books.*')
              -> withAvg('reviews as rating', 'rating')
              -> genreFilter($genre)
              -> keywordSearch($keyword)
              -> sortOrder($sort)
              -> paginate(10);

        return view('books.index',compact('books', 'genres', 'keyword', 'genre', 'sort'));
    }

    public function create()
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    public function isbnSearch(Request $request, $isbn)
    {
        $simpleIsbn = str_replace(['-', ' '], '', $isbn);

        if (empty($simpleIsbn)) {
            return response()->json(['error' => '無効なISBNコードです。'], 400);
        }

        $url = 'https://www.googleapis.com/books/v1/volumes?q=isbn:' . $simpleIsbn;

        $apiKey = env('GOOGLE_BOOKS_API_KEY');
        if (!empty($apiKey)) {
            $url .= '&key=' . $apiKey;
        }

        try {
            $response = Http::withoutVerifying()->get($url);
        } catch (\Exception $e) {
            return response()->json(['error' => 'APIサーバーへの接続に失敗しました。'], 500);
        }

        if ($response->failed()) {
            $statusCode = $response->status();

            if ($statusCode === 429) {
                return response()->json(['error' => 'リクエストが多すぎます。しばらく時間を置いてから再度お試しください。'], 429);
            }

            return response()->json(['error' => 'APIの呼び出しに失敗しました。ステータスコード:' . $statusCode], $statusCode);
        }

        $bookData = $response->json();

            if (isset($bookData['items'][0]['volumeInfo'])) {
                $volumeInfo = $bookData['items'][0]['volumeInfo'];

                $book = [
                    'title' => $volumeInfo['title'] ?? 'タイトル不明',
                    'author' => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : '著者不明',
                    'published_date' => $volumeInfo['publishedDate'] ?? '出版日不明',
                    'isbn' => $isbn,
                    //古い作品だとないことがある。
                    'description' => $volumeInfo['description'] ?? '説明なし',
                    'image_url' => isset($volumeInfo['imageLinks']['thumbnail']) ? str_replace('http://', 'https://', $volumeInfo['imageLinks']['thumbnail']): null,
                ];
                //日本の書籍は小説や漫画が空欄になることがあり、出ないことがある
                $genres =isset($volumeInfo['categories']) ? implode(', ', $volumeInfo['categories']) : 'ジャンル不明';

                $book['genres'] = $genres;

                return response()->json($book);
            }
            return response()->json(['error' => '書籍が見つかりませんでした。'], 404);
        
        //$statusCode = $response->status();

        //return response()->json(['error' => "APIの呼び出しに失敗しました。(ステータスコード: {$statusCode})"], 500);
    }

    public function bookCreateStore(BookRequest $request)
    {
        $user = Auth::user();

        DB::transaction(function () use ($user, $request) {

            $book = Book::create([
                'user_id' => $user->id,
                'title' => $request->input('title'),
                'author' => $request->input('author'),
                'isbn' => $request->input('isbn'),
                'published_date' => $request->input('published_date'),
                'description' => $request->input('description'),
                'image_url' => $request->input('image_url'),
            ]);

            $book->genres()->attach($request->input('genres'));
        });

        return redirect()->route('books.index');
    }

    public function show($bookId)
    {
        $book = Book::with('genres','reviews.likedByUsers') 
              -> findOrFail($bookId);

        return view('books.show', compact('book'));
    }

    public function favoriteBook(Request $request, $bookId)
    {
        $request->user()->favoriteBooks()->toggle($bookId);

        return redirect()->back();
    }

    public function bookEdit($bookId)
    {
        $book = Book::with('genres')
             -> findOrFail($bookId);

        $genres = genre::all();

        $this->authorize('edit', $book);

        return view('books.edit', compact('book','genres'));
    }

    public function bookUpdate(BookRequest $request,$bookId)
    {
        $user = Auth::user();

        $book = Book::with('genres')
             -> findOrFail($bookId);

        DB::transaction(function () use ($user,$book, $request) {

            $book->update([
                'user_id' => $user->id,
                'title' => $request->input('title'),
                'author' => $request->input('author'),
                'isbn' => $request->input('isbn'),
                'published_date' => $request->input('published_date'),
                'description' => $request->input('description'),
                'image_url' => $request->input('image_url'),
            ]);

            $book->genres()->sync($request->input('genres'));
        });

        $this->authorize('update', $book);

        return redirect()->route('books.show', $book->id);
    }

    public function bookDelete($bookId)
    {
        $book = Book::findOrFail($bookId);

        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index');
    }

    public function ranking()
    {
        $rankedBooks = Book::with('reviews')
                    -> withAvg('reviews', 'rating')
                    -> withCount('reviews')
                    -> orderByDesc('reviews_avg_rating')
                    -> get();

        return view('ranking.index', compact('rankedBooks'));
    }

    public function favorite()
    {
        $user = Auth::user();

        $books = $user->favoriteBooks()
              -> paginate(10);

        return view('favorites.index', compact('books'));
    }
}
