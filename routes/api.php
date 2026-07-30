<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\BookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//用途によってはgetからuser()まで消す。
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/v1/books', [BookController::class, 'store']);

    Route::put('/v1/books/{book}', [BookController::class, 'update']);

    Route::delete('/v1/books/{book}', [BookController::class, 'delete']);
});

Route::get('/v1/books', [BookController::class, 'index']);

Route::get('/v1/books/{book}', [BookController::class, 'show']);

