<?php

namespace App\Http\Requests\Notifications;

use Illuminate\Foundation\Http\FormRequest;

class EnqueueNotificationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'channel' => ['required','in:email,sms'],
            'template' => ['required','string','max:120'],
            'payload' => ['required','array'],
            'send_after' => ['nullable','date'],
            'user_id' => ['nullable','integer','exists:users,id'],
        ];
    }
}
