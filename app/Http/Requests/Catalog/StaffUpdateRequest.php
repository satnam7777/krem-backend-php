<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class StaffUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['sometimes','string','max:140'],
            'title' => ['sometimes','nullable','string','max:140'],
            'is_active' => ['sometimes','boolean'],
            'sort_order' => ['sometimes','integer','min:-100000','max:100000'],
            'avatar_url' => ['sometimes','nullable','string','max:2048'],
        ];
    }
}
