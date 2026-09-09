<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\CarbonInterface;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'content'          => $this->content,
            'author'           => new AuthorResource($this->whenLoaded('author', $this->author)),
            'author_name'      => $this->relationLoaded('author') && $this->author 
                                    ? __($this->author->name) 
                                    : __('app.idea.anonymous'),
            'created_at'       => $this->created_at,
            'created_at_human' => $this->created_at?->diffForHumans(),
        ];
    }
}