<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class ServiceStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:140'],
            'description' => ['nullable','string','max:5000'],
            'duration_min' => ['required','integer','min:5','max:1440'],
            'buffer_min' => ['nullable','integer','min:0','max:240'],
            'price_cents' => ['required','integer','min:0','max:100000000'],
            'currency' => ['nullable','string','size:3'],
            'is_active' => ['nullable','boolean'],
            'sort_order' => ['nullable','integer','min:-100000','max:100000'],
            'image_url' => ['nullable','string','max:2048'],
        ];
    }
}
