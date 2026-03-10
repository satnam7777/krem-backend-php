<?php

namespace App\Http\Requests\Clients;

use Illuminate\Foundation\Http\FormRequest;

class TreatmentStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'appointment_id' => ['nullable','integer'],
            'service_id' => ['nullable','integer'],
            'performed_at' => ['nullable','date'],
            'notes' => ['nullable','string','max:10000'],
        ];
    }
}
