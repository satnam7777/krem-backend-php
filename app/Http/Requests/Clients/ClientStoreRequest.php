<?php

namespace App\Http\Requests\Clients;

use Illuminate\Foundation\Http\FormRequest;

class ClientStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'first_name' => ['required','string','max:80'],
            'last_name' => ['nullable','string','max:80'],
            'phone' => ['nullable','string','max:40'],
            'email' => ['nullable','email','max:160'],
            'gender' => ['nullable','string','max:20'],
            'date_of_birth' => ['nullable','date'],
        ];
    }
}
