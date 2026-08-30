<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [BookController::class, 'index'])->name('books.index');

Route::middleware('auth')->group(function () {
    Route::get('/books',[BookController::class, 'index'])->name('books.index');

    Route::get('/books/create',[BookController::class, 'create'])->name('books.create');

    Route::get('/books/isbn/{isbn}', [BookController::class, 'isbnSearch']);

    Route::post('/books', [BookController::class, 'store'])->name('books.store');

    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');

    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');

    Route::delete('/books/{book}', [BookController::class, 'delete'])->name('books.destroy');

    Route::post('/books/{book}/favorites', [BookController::class, 'favoriteBook'])->name('favorites.toggle');

    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])->name('reviews.like');

    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');

    Route::put('/review/{review}', [ReviewController::class, 'update'])->name('reviews.update');

    Route::delete('/reviews/{review}', [ReviewController::class, 'delete'])->name('reviews.destroy');

    Route::get('/favorites', [BookController::class, 'favorite'])->name('favorites.index');

    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');

    Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');

    Route::get('/genres/{genre}', [GenreController::class, 'show'])->name('genres.show');

    Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');

    Route::put('/genre/{genre}', [GenreController::class, 'update'])->name('genres.update');

    Route::delete('/genres/{genres}', [GenreController::class, 'delete'])->name('genres.destroy');

    Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('/reading-plans', [ReadingPlanController::class, 'index'])->name('reading-plans.index');

    Route::get('/reading-plan/create', [ReadingPlanController::class, 'create'])->name('reading-plans.create');

    Route::post('/reading-plans', [ReadingPlanController::class, 'store'])->name('reading-plans.store');

    Route::post('/reading-plans/{plan}/complete', [ReadingPlanController::class, 'complete'])->name('reading-plans.complete');

    Route::get('/reading-plans/{plan}/edit', [ReadingPlanController::class, 'edit'])->name('reading-plans.edit');

    Route::put('/reading-plans/{plan}', [ReadingPlanController::class, 'update'])->name('reading-plans.update');

    Route::delete('/reading-plans/{plan}', [ReadingPlanController::class, 'delete'])->name('reading-plans.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'store'])->name('notifications.read');
});

Route::get('/ranking', [BookController::class, 'ranking'])->name('ranking.index');

Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
