<?php

namespace App\Http\Requests\Clients;

use Illuminate\Foundation\Http\FormRequest;

class ClientUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes','string','max:80'],
            'last_name' => ['sometimes','nullable','string','max:80'],
            'phone' => ['sometimes','nullable','string','max:40'],
            'email' => ['sometimes','nullable','email','max:160'],
            'gender' => ['sometimes','nullable','string','max:20'],
            'date_of_birth' => ['sometimes','nullable','date'],
            'status' => ['sometimes','in:active,archived'],
        ];
    }
}
