<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        //ログインユーザーのreviewの総数と平均値を算出する($reviewsStats)
        //平均値はreviews_avg_rating,総数はcount_reviewとして呼び出す
        $reviewStats = User::withCount('reviews')
                    -> withAvg('reviews', 'rating')
                    -> findOrFail(Auth::id());
        //ログインユーザーの「読了」した書籍のデータを取得する
        $bookStats = ReadingPlan::where('user_id', $user->id)
                  -> where('status', 'completed')
                  -> count();
        
        $totalCount = $reviewStats->total_reviews ?: 1;

        //userのreviewの1～5の評価の分布を
        //データの中身 [5 => 3, 4 => 1, 3 => 1] // 評価 => 件数
        $distributionData = $user->reviews()
                         -> pluck('rating')
                         -> countBy()
                         -> sortKeysDesc();
        //上で数えた結果から ★1〜★5 を順番に取り出し、ない評価は 0 で埋めて、5つの要素を持つ配列に並べ直しています。
        //[0, 0, 1, 1, 3]  // ★1, ★2, ★3, ★4, ★5 の件数
        $ratingDistribution = [];

        for ($rating = 1; $rating <= 5; $rating++) {
            $ratingDistribution[] = $distributionData[$rating] ?? 0;
        }

        $ratingDistributionCollection = collect($ratingDistribution);

        /*
        $topRatedBooks = DB::table('reviews')
                      -> join('books', 'reviews.book_id', '=', 'books.id')
                      -> select(
                        'books.id',
                        'books.title',
                        'books.author',
                        DB::raw('AVG(reviews.rating) as avg_rating'),
                        DB::raw('COUNT(reviews.id) as review_count')
                      )
                      -> where('reviews.user_id', $user->id)
                      -> where('reviews.rating', '>=', 4)
                      -> groupBy('books.id', 'books.title', 'books.author')
                      -> orderBy('avg_rating', 'desc')
                      -> orderBy('review_count', 'desc')
                      -> limit(5)
                      -> get()
                      -> map(function ($book) {
                        return [
                            'id'           => $book->id,
                            'title'        => $book->title,
                            'author'       => $book->author,
                            'avg_rating'   => (float) round($book->avg_rating, 2),
                            'rating'       => (float) round($book->avg_rating, 2),
                            'review_count' => (int) $book->review_count,
                        ];
                      })
                      ->toArray();
        */
        /*
        「このユーザーが評価 4 以上のレビューを書いた本」だけに絞り込む条件
        実際の whereHas（EXISTS 型）

        視点は books 側にあり、本を1冊ずつ見て「この本に、条件を満たすレビューはある？」と確認する形です。
        */
        $topRatedBooks = Book::query()
                      -> whereHas('reviews', function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                              ->where('rating', '>=', 4);
                      })
                      -> withAvg('reviews', 'rating')
                      -> withCount('reviews')
                      -> orderBy('reviews_avg_rating', 'desc')
                      -> orderBy('reviews_count', 'desc')
                      -> limit(5)
                      -> get()
                      -> map(function ($book) {
                        return [
                            'id'           => $book->id,
                            'title'        => $book->title,
                            'author'       => $book->author,
                            'avg_rating'   => (float) round($book->reviews_avg_rating, 2),
                            'rating'       => (float) round($book->reviews_average_rating, 2),
                            'review_count' => (int) $book->review_count,
                        ];
                      })
                      ->toArray();


        $topRatedGenres = DB::table('genres')
                       -> join('book_genre', 'genres.id', '=', 'book_genre.genre_id')
                       -> join('books', 'book_genre.book_id', '=', 'books.id')
                       -> join('reviews', 'books.id', '=', 'reviews.book_id')
                       ->select(
                            'genres.id',
                            'genres.name',
                            DB::raw('AVG(reviews.rating) as avg_rating'),
                            DB::raw('COUNT(reviews.id) as review_count')
                       )
                       -> where('reviews.user_id', $user->id)
                       -> groupBy('genres.id', 'genres.name')
                       -> orderBy('avg_rating', 'desc')
                       -> orderBy('review_count', 'desc')
                       -> limit(5)
                       -> get()
                       -> map(function ($genre) {
                            return [
                                'id' => $genre->id,
                                'name' => $genre->name,
                                'average_rating' => (float) round($genre->avg_rating, 2),
                                'rating' => (float) round($genre->avg_rating, 2),
                                'count' => (int) $genre->review_count,
                           ];
                       })
                       ->toArray();

        $stats = [
            'summary' => [
                'total_reviews' => (int) $reviewStats->reviews_count,
                'books_read' => (int) $bookStats,
                'average_rating' => (float) round($reviewStats->reviews_avg_rating, 2),
            ],
            'rating_distribution' => $ratingDistributionCollection,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $topRatedGenres
        ];

        //return view('reports.index', compact('stats', 'reviewStats', 'bookStats'));

        //$statsForRender = $stats;
        //$statsForRender['rating_distribution'] = new class($maxCountValue) {
            //private $m;
            //public function _construct($m) { $this->m = $m; }
            //public function max() { return $this->m; }
        //};

        $viewContent = view('reports.index', [
            'stats' => $stats,
            'reviewStats' => $reviewStats,
            'bookStats' => $bookStats
        ])->render();

        return response($viewContent);
    }
}
