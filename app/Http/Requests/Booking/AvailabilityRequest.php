<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class AvailabilityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'date' => ['required','date'],
            'service_id' => ['required','integer','exists:services,id'],
            'staff_id' => ['nullable','integer','exists:staff,id'],
            'slot_minutes' => ['nullable','integer','min:5','max:60'],
            'timezone' => ['nullable','string','max:64'],
        ];
    }
}
