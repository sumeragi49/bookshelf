<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
