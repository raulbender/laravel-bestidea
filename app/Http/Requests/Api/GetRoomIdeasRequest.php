<?php

namespace App\Http\Requests\Api;

use App\Models\Room;
use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class GetRoomIdeasRequest extends FormRequest {
    /**
     * Valida as permissões de acesso do usuário à sala solicitada.
     */
    public function authorize(): bool {
        $room = $this->getRoom();

        if (! $room) {
            return true; // Deixa passar para o sistema de validação tratar o 'exists'
        }

        // Se a sala for privada (is_public == false), checa autorização do usuário
        if (! $room->is_public) {
            return $this->user()?->can('view', $room) ?? false;
        }

        return true;
    }

    /**
     * Regras de validação da query string.
     */
    public function rules(): array {
        return [
            'room_uuid' => ['required', 'string', 'uuid', 'exists:rooms,uuid'],
            'sort'      => ['nullable', 'string', 'in:recent,hot,top_rated'],
            'filter'    => ['nullable', 'string', 'in:mine'],
        ];
    }

    /**
     * Prepara a query string para validação do FormRequest.
     */
    protected function prepareForValidation(): void {
        $this->merge([
            'room_uuid' => $this->query('room_uuid'),
        ]);
    }

    /**
     * Instância da Room a partir do UUID fornecido na requisição.
     */
    public function getRoom(): ?Room {
        $uuid = $this->query('room_uuid');

        // Se o UUID for nulo ou não tiver o formato UUID estrito, 
        // retorna null para evitar a consulta no PostgreSQL.
        if (! $uuid || ! Str::isUuid($uuid)) {
            return null;
        }

        return Room::where('uuid', $uuid)->first();
    }
}
