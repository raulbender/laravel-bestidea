<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        // Bloqueia a criação de salas públicas por convidados (Retorna 403)
        if ($this->boolean('is_public', false) && $user?->is_guest) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'is_public'   => ['sometimes', 'boolean'],
            'expires_at'  => [
                'nullable',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
                    if ($value && $this->user()?->is_guest) {
                        $hours = now()->diffInHours(Carbon::parse($value), false);
                        if ($hours > 24) {
                            $fail('Salas criadas por convidados não podem ter expiração superior a 24 horas.');
                        }
                    }
                },
            ],
        ];
    }
}