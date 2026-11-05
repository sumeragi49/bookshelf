<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
    ];

    protected $casts = [
        'published_date' => 'datetime:Y-m-d\TH:i:s.uP',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    //第1引数の $query は Laravel が自動で渡すクエリビルダで、呼び出し側は第2引数の $keyword だけを渡します。
    public function scopeKeywordSearch($query, $keyword)
    {
        //キーワードが空なら何もしない
        /*
        if (empty($keyword)) {
            return $query;
        }
        //
        return $query->where(function (Builder $subQuery) use ($keyword) {
            $subQuery->where('books.title', 'like', '%' . $keyword . '%')
                     ->orWhere('books.author', 'like', '%' . $keyword . '%');
        });
        */
        //$keywordが空でない場合
        if (!empty($keyword)) {
            //今回はorのみの条件だが,後々、「いいねをしている書籍のみ」などの確定された条件が付く場合,andとorの条件が混在する可能性があるため,クロージャー形式で囲む(条件分岐がorのみであれば$queryからいきなりwhereで条件付けを始めてもよい)
            //$queryは以下のEloquentの条件で行うこととする
            $query->where(function (Builder $subQuery) use ($keyword) {
                //books tableのtitleカラムのデータに部分一致するもの
                $subQuery->where('books.title', 'like', '%' . $keyword . '%')
                         //books tableのauthorカラムのデータに部分一致するもの
                         ->orWhere('books.author', 'like', '%' . $keyword . '%');
            });
        }
    }
    /*
    //ジャンルによるフィルターのscope設定
    public function scopeGenreFilter(Builder $query, ?string $genre): Builder
    {
        //$genreが空であればそのままにする
        if (empty($genre)) {
            return $query;
        }
        //$genreが空でない場合
        if (!empty($genre)) {
            //$queryはgenres tableからデータを取得し、取得条件は以下($subQuery)とする
            return $query->whereHas('genres', function (Builder $subQuery) use ($genre) {
                //$subQueryは'genre_id'で取得したものを$genreとして格納する。
                $subQuery->where('genre_id', $genre);
            });
        }
    }
    */

    //この書き方の場合は「戻り値の型をBuilderと宣言しているためreturnが必要」
    /*
    : Builder と書くと、PHP はそのメソッドが必ず Builder を返すかをチェックしている。今のコードは if の中で $query を変更しているが、最後に何も返していない。そのため戻り値が null になり、型エラーになる。$genre が空で if を通らない場合も同じ
    */
    /*
    public function scopeGenreFilter(Builder $query, ?string $genre): Builder
    {
        if (!empty($genre)) {
            $query->whereHas('genres', function (Builder $subQuery) use ($genre) {
                $subQuery->where('genre_id', $genre);
            });
        }

        return $query;
    }
    */

    //今回はジャンルによる単純な一つの場合分けのためwhenの方が好ましい
    //条件を足す可能性があるのならifの方が好ましい。
    
    public function scopeGenreFilter(Builder $query, ?string $genre): Builder
    {
        return $query->when($genre, function (Builder $q, $genre) {
            $q->whereHas('genres', fn (Builder $sub) => $sub->where('genre_id', $genre));
        });
    }
    

    public function scopeSortOrder(Builder $query, ?string $sortKey): Builder
    {
        return match ((string)$sortKey) {
            'oldest' => $query->orderBy('created_at','asc'),
            'title' => $query->orderBy('title', 'asc'),
            'rating' => $query->orderByRaw('rating IS NULL ASC')->orderBy('rating', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }
}
