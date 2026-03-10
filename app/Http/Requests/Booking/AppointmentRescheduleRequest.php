<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentRescheduleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'start_at' => ['required','date'],
            'end_at' => ['required','date','after:start_at'],
            'timezone' => ['nullable','string','max:64'],
            'notes' => ['nullable','string','max:2000'],
        ];
    }
}
