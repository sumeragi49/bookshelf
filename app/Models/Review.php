<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'comment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'likes', 'review_id', 'user_id');
    }

    public function isLikedBy(?User $user): bool 
    {
        if (!$user) {
            return false;
        }
        return $this->likedByUsers()->where('user_id', $user->id)->exists();
    }
}
