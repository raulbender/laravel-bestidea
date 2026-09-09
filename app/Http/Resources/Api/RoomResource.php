<?php

// app/Http/Resources/Api/RoomResource.php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\CarbonInterface;

class RoomResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $user = $request->user();
        $roomUser = $user ? $this->roomUsers->where('user_id', $user->id)->first() : null;

        return [
            'id'                 => $this->id,
            'uuid'               => $this->uuid,
            'title'              => $this->title,
            'description'        => $this->description,
            'is_public'          => $this->is_public,
            'expires_at'         => $this->expires_at,
            'expires_at_human'   => $this->expires_at?->diffForHumans(['syntax' => CarbonInterface::DIFF_ABSOLUTE]),
            'is_expiring_soon'   => $this->expires_at ? $this->expires_at->isFuture() && now()->diffInHours($this->expires_at) <= 24  : false,
            'ideas_count'        => $this->ideas_count ?? $this->ideas()->count(),
            'comments_count'     => $this->comments_count ?? ($this->relationLoaded('comments') ? $this->comments->count() : $this->comments()->count()),
            'participants_count' => $this->room_users_count ?? $this->roomUsers()->count(),
            'created_at'         => $this->created_at,
            'created_at_human'   => $this->created_at?->diffForHumans(),

            // Metadados inseridos diretamente na raiz do objeto
            'owner_name'         => $this->user?->name ?? __('app.idea.anonymous'),
            'is_owner'           => $this->when($user, fn() => $this->user_id === $user->id),

            'my_persona'         => $this->when($roomUser && $roomUser->author, fn() => [
                'name'   => __($roomUser->author->name),
                'avatar' => $roomUser->author->avatar,
                'type'   => $roomUser->author->type,
            ]),
        ];
    }
}
