<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Http\Requests\ReviewRequest;

class ReviewController extends Controller
{
    public function reviewStore(ReviewRequest $request, $bookId)
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

    public function reviewEdit($reviewId)
    {
        $review = Review::with('book')
               -> findOrFail($reviewId);

        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    public function reviewUpdate(ReviewRequest $request,$reviewId)
    {
        $review = Review::findOrFail($reviewId);

        $bookId = $review->book_id;

        $review->update([
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        return redirect()->route('books.show',['book' => $bookId]);
    }

    public function ReviewDelete($reviewId)
    {
        $review = Review::findOrFail($reviewId);

        $this->authorize('delete', $review);

        $review->delete();

        return redirect()->route('books.index');
    }

    public function reviewLike(Request $request, $reviewId)
    {
        $request->user()->likedReviews()->toggle($reviewId);

        $review = Review::findOrFail($reviewId);

        return redirect()->route('books.show', $review->book_id);
    }
}
