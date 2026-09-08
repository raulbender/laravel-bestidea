<?php

namespace App\Http\Requests;

use App\Models\Idea;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ideaId = $this->route('id');
        $idea = Idea::find($ideaId);

        if (! $idea) {
            return true;
        }

        $roomUuid = $this->input('room_uuid') ?? $this->query('room_uuid');

        $hasValidKey = $roomUuid && $idea->room && $idea->room->uuid === $roomUuid;

        if (! $hasValidKey) {
            return false;
        }

        // Convidados não podem comentar em salas públicas
        if ($idea->room->is_public && $this->user()?->is_guest) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'content'   => ['required', 'string', 'max:1000'],
            'room_uuid' => ['nullable', 'string', 'uuid'],
        ];
    }
}