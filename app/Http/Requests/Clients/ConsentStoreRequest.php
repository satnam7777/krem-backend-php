<?php

namespace App\Http\Requests\Clients;

use Illuminate\Foundation\Http\FormRequest;

class ConsentStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type' => ['required','string','max:60'],
            'version' => ['required','string','max:30'],
            'consent_text' => ['required','string','max:5000'],
            'accepted_at' => ['nullable','date'],
            'source' => ['nullable','string','max:40'],
            'meta' => ['nullable','array'],
        ];
    }
}
