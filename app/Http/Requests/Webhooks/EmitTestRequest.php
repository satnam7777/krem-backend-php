<?php

namespace App\Http\Requests\Webhooks;

use Illuminate\Foundation\Http\FormRequest;

class EmitTestRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'event' => ['required','string','max:120'],
            'payload' => ['required','array'],
        ];
    }
}
