<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class ServiceStaffUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'staff_id' => ['required','integer','exists:staff,id'],
            'is_active' => ['nullable','boolean'],
            'price_cents_override' => ['nullable','integer','min:0','max:100000000'],
            'duration_min_override' => ['nullable','integer','min:5','max:1440'],
        ];
    }
}
