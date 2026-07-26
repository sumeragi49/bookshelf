<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Http\Requests\ReviewRequest;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request, $bookId)
    {
        $user = auth()->user();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $bookId,
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        return redirect()->back();
    }

    public function edit($reviewId)
    {
        $review = Review::with('book')
               -> findOrFail($reviewId);

        $this->authorize('edit', $review);

        return view('reviews.edit', compact('review'));
    }

    public function update(ReviewRequest $request,$reviewId)
    {
        $review = Review::findOrFail($reviewId);

        $bookId = $review->book_id;

        $review->update([
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        $this->authorize('update', $review);

        return redirect()->route('books.show',['book' => $bookId]);
    }

    public function delete($reviewId)
    {
        $review = Review::findOrFail($reviewId);

        $this->authorize('delete', $review);

        $review->delete();

        return redirect()->route('books.index');
    }

    public function like(Request $request, $reviewId)
    {
        $request->user()->likedReviews()->toggle($reviewId);

        $review = Review::findOrFail($reviewId);

        return redirect()->route('books.show', $review->book_id);
    }
}
