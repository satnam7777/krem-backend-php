<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class ServiceUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['sometimes','string','max:140'],
            'description' => ['sometimes','nullable','string','max:5000'],
            'duration_min' => ['sometimes','integer','min:5','max:1440'],
            'buffer_min' => ['sometimes','integer','min:0','max:240'],
            'price_cents' => ['sometimes','integer','min:0','max:100000000'],
            'currency' => ['sometimes','string','size:3'],
            'is_active' => ['sometimes','boolean'],
            'sort_order' => ['sometimes','integer','min:-100000','max:100000'],
            'image_url' => ['sometimes','nullable','string','max:2048'],
        ];
    }
}
