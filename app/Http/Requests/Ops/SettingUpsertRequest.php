<?php

namespace App\Http\Requests\Ops;

use Illuminate\Foundation\Http\FormRequest;

class SettingUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'key' => ['required','string','max:120'],
            'type' => ['required','in:string,int,bool,json'],
            'value' => ['nullable'],
        ];
    }
}
