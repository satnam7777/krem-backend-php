<?php

namespace App\Http\Requests\Invite;

use Illuminate\Foundation\Http\FormRequest;

class CreateInviteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email' => ['required','email'],
            'role'  => ['required','in:admin,staff,support'],
        ];
    }
}
