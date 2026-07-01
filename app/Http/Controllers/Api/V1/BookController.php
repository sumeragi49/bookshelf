<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Review;
use App\Http\Requests\BookRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\BookCollection;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\ReviewCollection;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();

        $books = Book::with('user', 'genres', 'reviews')
              -> withAvg('reviews', 'rating')
              -> withCount('reviews')
              -> paginate(10);

        return (new BookCollection($books))
               ->response()
               ->setStatusCode(200);
    }

    public function show($bookId): JsonResponse
    {
        $book = Book::with('user', 'genres', 'reviews.user')
             -> findOrFail($bookId);

        $reviews = Review::where('book_id', $bookId)
               -> get();

        $responseData = [
            'book' => new BookResource($book),
            'reviews' => ReviewResource::collection($reviews),
        ];

        return response()->json($responseData, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function bookCreateStore(BookRequest $request)
    {
        $userId = 1;

        $book = DB::transaction(function () use ($userId, $request) {

            $createBook = Book::create([
                'user_id' => $userId,
                'title' => $request->input('title'),
                'author' => $request->input('author'),
                'isbn' => $request->input('isbn'),
                'published_date' => $request->input('published_date'),
                'description' => $request->input('description'),
                'image_url' => $request->input('image_url'),
            ]);

            $createBook->genres()->attach($request->input('genres'));

            return $createBook;
        });

        return response()->json([
            'status' => 'success',
            'data' => $book,
        ], 201, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function bookUpdate(BookRequest $request)
    {
        $userId = 1;
        //更新する書籍データは登録で作成した書籍データを使用。(各自,自由に更新する書籍のbookIdを選択)
        $bookId = 12;

        $book = Book::with('genres')
             -> findOrFail($bookId);

        DB::transaction(function () use ($userId,$book, $request) {

            $book->update([
                'user_id' => $userId,
                'title' => $request->input('title'),
                'author' => $request->input('author'),
                'isbn' => $request->input('isbn'),
                'published_date' => $request->input('published_date'),
                'description' => $request->input('description'),
                'image_url' => $request->input('image_url'),
            ]);

            $book->genres()->sync($request->input('genres'));
        });

        return response()->json([
            'status' => 'success',
            'data' => $book,
        ], 201, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function bookDelete()
    {
        //削除する書籍データは登録で作成した書籍データを使用。(各自,自由に削除する書籍のbookIdを選択)
        $bookId = 12;

        $book = Book::findOrFail($bookId);

        //$this->authorize('delete', $book);
        //「現在ログインしているユーザー」に権限があるかどうかをチェックするcode
        //使用する場合はTinker上で「auth()->loginUsingId(1);」と入力してログイン状態を作る。

        $book->delete();

        return response()->json([
            'status' => 'success',
            'data' => $book,
        ], 201, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
