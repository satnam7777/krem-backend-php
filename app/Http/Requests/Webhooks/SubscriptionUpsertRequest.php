<?php

namespace App\Http\Requests\Webhooks;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['nullable','string','max:120'],
            'target_url' => ['required','url','max:500'],
            'enabled' => ['required','boolean'],
            'secret' => ['required','string','min:16','max:255'],
            'events' => ['nullable','array'],
            'events.*' => ['string','max:120'],
        ];
    }
}
