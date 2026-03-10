<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class StaffStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:140'],
            'title' => ['nullable','string','max:140'],
            'is_active' => ['nullable','boolean'],
            'sort_order' => ['nullable','integer','min:-100000','max:100000'],
            'avatar_url' => ['nullable','string','max:2048'],
        ];
    }
}
