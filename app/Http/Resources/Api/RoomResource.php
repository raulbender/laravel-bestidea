<?php

// app/Http/Resources/Api/RoomResource.php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        // Recupera o autor do usuario logado nesta sala através da relação
        $roomUser = $user 
            ? $this->roomUsers->where('user_id', $user->id)->first()
            : null;

        return [
            // Atributos diretos da sala para o teste de criacao
            'id'          => $this->id,
            'uuid'        => $this->uuid,
            'title'       => $this->title,
            'description' => $this->description,
            'is_public'   => $this->is_public,
            'expires_at'  => $this->expires_at,
            'created_at'  => $this->created_at,

            // Estrutura aninhada para satisfazer a consulta da sala
            'room' => [
                'id'          => $this->id,
                'uuid'        => $this->uuid,
                'title'       => $this->title,
                'description' => $this->description,
                'is_public'   => $this->is_public,
                'expires_at'  => $this->expires_at,
                'created_at'  => $this->created_at,
            ],

            'is_owner' => $user ? $this->user_id === $user->id : false,

            'my_persona' => $roomUser && $roomUser->author ? [
                'name'   => $roomUser->author->name,
                'avatar' => $roomUser->author->avatar,
                'type'   => $roomUser->author->type,
            ] : null,
        ];
    }
}