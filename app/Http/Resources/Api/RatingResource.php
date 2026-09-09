<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'score'         => $this->score,
            'feedback'      => $this->feedback,
            'total_score'   => $this->idea->total_score,
            'ratings_count' => $this->idea->ratings_count,
            'avg_score'     => number_format($this->idea->avg_score, 2, '.', ''),
            'author_name'      => $this->relationLoaded('user') && $this->user 
                                    ? $this->user->name 
                                    : __('app.idea.anonymous'),
            'created_at'       => $this->created_at,
            'created_at_human' => $this->created_at?->diffForHumans(),
        ];
    }
}