<?php

namespace App\Http\Requests\Clients;

use Illuminate\Foundation\Http\FormRequest;

class MedicalUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'allergies' => ['nullable','array'],
            'contraindications' => ['nullable','array'],
            'medications' => ['nullable','array'],
            'conditions' => ['nullable','array'],
            'notes' => ['nullable','string','max:10000'],
        ];
    }
}
