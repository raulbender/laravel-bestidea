<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IdeaResource extends JsonResource
{
     /**
      * Transform the resource into an array.
      *
      * @return array<string, mixed>
      */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'content'        => $this->content,
            'avg_score'      => $this->avg_score,
            'ratings_count'  => $this->ratings_count,
            'comments_count' => $this->comments_count,
            'author'         => $this->whenLoaded('author', fn () => new AuthorResource($this->author)),
            'created_at'     => $this->created_at,
        ];
    }

}