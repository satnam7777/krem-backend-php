<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'service_id' => ['required','integer','exists:services,id'],
            'staff_id' => ['nullable','integer','exists:staff,id'],
            'start_at' => ['required','date'], // ISO8601 recommended
            'end_at' => ['required','date','after:start_at'],
            'timezone' => ['nullable','string','max:64'],

            // customer fields (guest or user)
            'user_id' => ['nullable','integer','exists:users,id'],
            'customer_name' => ['nullable','string','max:120'],
            'customer_phone' => ['nullable','string','max:40'],
            'customer_email' => ['nullable','email','max:120'],

            'notes' => ['nullable','string','max:2000'],
        ];
    }
}
