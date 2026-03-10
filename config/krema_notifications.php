<?php

return [
    'templates' => [
        // Payload must include: to_email (for email channel)
        'invite_created' => [
            'subject' => 'You have been invited',
            'body' => "Hello,\n\nYou have been invited to join {{salon_name}}.\nOpen: {{invite_link}}\n\nThanks,\nKrema.ba",
        ],
        'appointment_created' => [
            'subject' => 'Appointment created',
            'body' => "Hi {{customer_name}},\n\nYour appointment is created for {{start_at}} with {{staff_name}} ({{service_name}}).\n\nKrema.ba",
        ],
        'appointment_confirmed' => [
            'subject' => 'Appointment confirmed',
            'body' => "Hi {{customer_name}},\n\nYour appointment at {{start_at}} is confirmed.\n\nKrema.ba",
        ],
    ],
];
