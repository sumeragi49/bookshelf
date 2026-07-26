<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'published_date' => $this->published_date
                ? \Carbon\Carbon::parse($this->published_date)->timezone('Asia/Tokyo')->format('Y-m-d H:i:s'):null,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'genres' => $this->genres->pluck('id')->toArray(),
            'average_rating' => $this->reviews_avg_rating
                ? (float) number_format($this->reviews_avg_rating, 1)
                : 0.0,
            'review_count' => $this->reviews_count ?? 0,
        ];
    }
}
