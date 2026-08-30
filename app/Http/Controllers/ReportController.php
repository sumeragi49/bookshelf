<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Models\ReadingPlan;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        //ログインユーザーのreviewの総数と平均値を算出する($reviewsStats)
        $reviewStats = DB::table('reviews')
                    -> select([
                            DB::raw('COUNT(*) as total_reviews'),
                            DB::raw('AVG(rating) as average_rating')
                   ])
                   -> where('user_id', $user->id)
                   -> first();
        //ログインユーザーの「読了」した書籍のデータを取得する
        $bookStats = DB::table('reading_plan')
                  -> where('user_id', $user->id)
                  -> where('status', '2')
                  -> selectRaw('
                        COUNT(*) as books_read
                  ')
                  -> first();
        
        $totalCount = $reviewStats->total_reviews ?: 1;

        $distributionData = DB::table('reviews')
                         -> where('user_id', $user->id)
                         -> select('rating', DB::raw('COUNT(*) as count'))
                         -> groupBy('rating')
                         -> orderBy('rating', 'desc')
                         -> get()
                         -> keyBy('rating');

        $ratingDistribution = [];

        for ($rating = 1; $rating <= 5; $rating++) {
            $ratingDistribution[] = isset($distributionData[$rating]) ? (int) $distributionData[$rating]->count : 0;
        }
        //countの最大値をあらかじめ計算する
        //$maxCountValue = max($ratingDistribution) ? : 1;
        //IteratorAggregateを実装してループを可能にする。
        //$ratingDistributionObj = new class($ratingDistribution, $maxCountValue) implements \IteratorAggregate {
            //private $array;
            //private $maxVal;

            //public function _construct($array, $maxVal) {
                //$this->array = $array;
                //$this->maxVal = $maxVal;
            //}

            //public function max() {
                //return $this->maxVal;
            //}
            //Bladeの@foreachでループできるようにする。
            //public function getIterator(): \Traversable {
                //return new \ArrayIterator($this->array);
            //}
        //};

        $ratingDistributionCollection = collect($ratingDistribution);

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
                'total_reviews' => (int) $reviewStats->total_reviews,
                'books_read' => (int) $bookStats->books_read,
                'average_rating' => (float) round($reviewStats->average_rating, 2),
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
