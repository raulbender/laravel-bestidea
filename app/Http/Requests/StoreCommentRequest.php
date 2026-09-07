<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Idea;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $ideaId = $this->route('id');
        $idea = $ideaId ? Idea::with('room')->find($ideaId) : null;
        $room = $idea?->room;

        // Guest não pode comentar em sala pública
        if ($room && $room->is_public && $user?->is_guest) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:1000'],
        ];
    }
}