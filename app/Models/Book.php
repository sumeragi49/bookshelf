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
        'published_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookFollowers()
    {
        return $this->belongsToMany(User::class, 'favorites', 'user_id', 'book_id');
    }

    public function isFavoriteBy(?User $user): bool 
    {
        if (!$user) {
            return false;
        }
        return $this->bookFollowers()->where('user_id', $user->id)->exists();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function reviewFollowers()
    {
        return $this->belongsToMany(User::class, 'likes', 'user_id', 'review_id');
    }

    public function readingPlans()
    {
        return $this->hasMany(ReadingPlan::class);
    }

    public function scopeKeywordSearch($query, $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($keyword) {
            $subQuery->where('books.title', 'like', '%' . $keyword . '%')
                     ->orWhere('books.author', 'like', '%' . $keyword . '%');
        });
    }

    public function scopeGenreFilter(Builder $query, ?string $genre): Builder
    {
        if (empty($genre)) {
            return $query;
        }

        return $query->whereHas('genres', function (Builder $subQuery) use ($genre) {
            $subQuery->where('genre_id', $genre);
        });
    }

    public function scopeSortOrder(Builder $query, ?string $sortKey): Builder
    {
        return match ((string)$sortKey) {
            'oldest' => $query->orderBy('created_at','asc'),
            'title' => $query->orderBy('title', 'asc'),
            'rating' => $query->orderByRaw('
            (SELECT AVG(rating) FROM reviews WHERE reviews.book_id = books.id) IS NULL ASC,
            (SELECT AVG(rating) FROM reviews WHERE reviews.book_id = books.id) DESC
            '),
            'newest' => $query->orderBy('created_at', 'desc'),
            'default' => $query->orderBy('created_at', 'desc'),
        };
    }
}
