<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class OrderCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'appointment_id' => ['nullable','integer','exists:appointments,id'],
            'user_id' => ['nullable','integer','exists:users,id'],
            'currency' => ['nullable','string','size:3'],
            'reference' => ['nullable','string','max:120'],
            'items' => ['required','array','min:1'],
            'items.*.service_id' => ['nullable','integer','exists:services,id'],
            'items.*.name' => ['required','string','max:200'],
            'items.*.qty' => ['required','integer','min:1','max:1000'],
            'items.*.unit_price_cents' => ['required','integer','min:0','max:100000000'],
        ];
    }
}
