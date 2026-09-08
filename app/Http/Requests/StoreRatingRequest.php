<?php

namespace App\Http\Requests;

use App\Models\Idea;
use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
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

        // Bloqueia feedback em texto feito por visitantes (guests) em salas públicas
        if ($this->filled('feedback') && $idea->room->is_public && $this->user()?->is_guest) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'score'     => ['required', 'integer', 'between:1,5'],
            'feedback'  => ['nullable', 'string', 'max:1000'],
            'room_uuid' => ['nullable', 'string', 'uuid'],
        ];
    }
}