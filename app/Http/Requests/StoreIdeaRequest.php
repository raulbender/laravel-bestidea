<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIdeaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $room = $this->route('uuid') ? \App\Models\Room::where('uuid', $this->route('uuid'))->first() : null;

        // Se a sala for pública e o usuário for guest, proíbe a criação de ideia (Retorna 403)
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