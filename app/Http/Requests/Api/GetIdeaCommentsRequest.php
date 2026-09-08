<?php

namespace App\Http\Requests\Api;

use App\Models\Idea;
use Illuminate\Foundation\Http\FormRequest;

class GetIdeaCommentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ideaId = $this->route('id');
        $idea = Idea::find($ideaId);

        // Se a ideia não existe, permite que a requisição siga para o Controller retornar 404
        if (! $idea) {
            return true;
        }

        $roomUuid = $this->input('room_uuid') ?? $this->query('room_uuid');

        return $roomUuid && $idea->room && $idea->room->uuid === $roomUuid;
    }

    public function rules(): array
    {
        return [
            'room_uuid' => ['required', 'string', 'uuid'],
        ];
    }
}