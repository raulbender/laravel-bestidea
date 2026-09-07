<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Idea;

class StoreRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $ideaId = $this->route('id');
        $idea = $ideaId ? Idea::with('room')->find($ideaId) : null;
        $room = $idea?->room;

        // Guest não pode enviar feedback em sala pública (mas pode apenas dar o score)
        if ($room && $room->is_public && $user?->is_guest && $this->filled('feedback')) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'score'    => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ];
    }
}