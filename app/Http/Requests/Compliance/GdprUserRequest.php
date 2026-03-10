<?php

namespace App\Http\Requests\Compliance;

use Illuminate\Foundation\Http\FormRequest;

class GdprUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id' => ['required','integer','exists:users,id'],
            'reason' => ['nullable','string','max:500'],
        ];
    }
}
