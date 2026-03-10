<?php

namespace App\Http\Requests\Invite;

use Illuminate\Foundation\Http\FormRequest;

class AcceptInviteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'token' => ['required','string'],
            'password' => ['nullable','string','min:8'],
        ];
    }
}
