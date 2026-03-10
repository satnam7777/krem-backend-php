<?php

namespace App\Services\Notifications;

use App\Models\NotificationOutbox;
use Illuminate\Support\Arr;

class NotificationDispatcher
{
    public function __construct(
        private TemplateRenderer $renderer,
        private EmailSender $emailSender,
    ) {}

    public function dispatch(NotificationOutbox $row): void
    {
        $templates = config('krema_notifications.templates', []);
        $tpl = $templates[$row->template] ?? null;

        if (!$tpl) {
            throw new \RuntimeException('Unknown template: '.$row->template);
        }

        $payload = $row->payload ?? [];

        if ($row->channel === 'email') {
            $to = $payload['to_email'] ?? null;
            if (!$to) throw new \RuntimeException('Missing payload.to_email');

            $subject = $this->renderer->render($tpl['subject'] ?? '', $payload);
            $body = $this->renderer->render($tpl['body'] ?? '', $payload);

            $this->emailSender->send($to, $subject, $body);
            return;
        }

        if ($row->channel === 'sms') {
            // Stub: integrate provider (Infobip/Twilio) later
            throw new \RuntimeException('SMS channel not configured');
        }

        throw new \RuntimeException('Unknown channel: '.$row->channel);
    }
}
