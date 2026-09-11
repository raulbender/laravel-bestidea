<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IdeaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'content'          => $this->content,
            'avg_score'        => (float) ($this->avg_score ?? 0),
            'ratings_count'    => (int) ($this->ratings_count ?? 0),
            'comments_count'   => (int) ($this->comments_count ?? 0),
            'author_name'      => __($this->author?->name) ?? __('app.idea.anonymous'),
            'author_avatar'    => $this->author?->avatar ?? '👤',
            'created_at_human' => $this->created_at?->diffForHumans() ?? '',
            'my_rating'        => $this->when(!is_null($this->my_rating), (int) $this->my_rating),
        ];
    }
}