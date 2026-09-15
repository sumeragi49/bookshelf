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
use App\Http\Resources\GenreResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\ReviewCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $keyword = $request->input('keyword');
        $genre = $request->input('genre_id');
        $perPage = $request->input('per_page', 20);

        $perPage = min((int)$perPage, 100);

        $books = Book::with('user', 'genres', 'reviews')
              -> withAvg('reviews', 'rating')
              -> withCount('reviews')
              -> genreFilter($genre)
              -> keywordSearch($keyword)
              -> paginate($perPage);

        return (new BookCollection($books))
               ->response()
               ->setStatusCode(200);
    }

    public function show($bookId): JsonResponse
    {
        try{
            $book = Book::with('user', 'genres', 'reviews.user')
                  -> withAvg('reviews', 'rating')
                  -> withCount('reviews')
                  -> findOrFail($bookId);

            $responseData = [
                'book' => new BookResource($book),
                'genres' => GenreResource::collection($book->genres),
                'reviews' => ReviewResource::collection($book->reviews),
            ];

            return response()->json($responseData, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => '書籍が見つかりませんでした。'
            ], 404, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }

    public function store(BookRequest $request)
    {
        $userId = auth()->id();

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

    public function update(BookRequest $request, $bookId)
    {
        $userId = auth()->id();

        try{
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
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => '書籍が見つかりませんでした。'
            ], 404, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }

    public function delete($bookId)
    {
        $userId = auth()->id();

        try{
            $book = Book::findOrFail($bookId);

            $this->authorize('delete', $book);

            $book->delete();

            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => '書籍が見つかりませんでした。'
            ], 404, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }
}
