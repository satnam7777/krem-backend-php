<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class OrderMarkPaidRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'paid_at' => ['nullable','date'],
            'note' => ['nullable','string','max:2000'],
        ];
    }
}
