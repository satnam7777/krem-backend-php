<?php

namespace App\Http\Requests\Salon;

use Illuminate\Foundation\Http\FormRequest;

class SwitchSalonRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'salon_id' => ['required','integer','exists:salons,id'],
        ];
    }
}
