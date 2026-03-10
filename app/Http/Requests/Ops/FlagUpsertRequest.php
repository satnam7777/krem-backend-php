<?php

namespace App\Http\Requests\Ops;

use Illuminate\Foundation\Http\FormRequest;

class FlagUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'key' => ['required','string','max:120'],
            'enabled' => ['required','boolean'],
            'meta' => ['nullable','array'],
        ];
    }
}
