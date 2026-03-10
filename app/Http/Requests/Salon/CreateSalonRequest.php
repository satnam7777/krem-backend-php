<?php

namespace App\Http\Requests\Salon;

use Illuminate\Foundation\Http\FormRequest;

class CreateSalonRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:120'],
            'currency' => ['nullable','string','size:3'],
            'timezone' => ['nullable','string','max:64'],
        ];
    }
}
