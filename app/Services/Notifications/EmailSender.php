<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Mail;

class EmailSender
{
    public function send(string $to, string $subject, string $body): void
    {
        // Minimal dependency: use raw text mail
        Mail::raw($body, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }
}
