<?php

namespace App\Services\Notifications;

class TemplateRenderer
{
    public function render(string $text, array $payload): string
    {
        // Simple placeholder replacement: {{key}}
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_\.]+)\s*\}\}/', function ($m) use ($payload) {
            $key = $m[1];
            $value = $payload[$key] ?? '';
            if (is_array($value) || is_object($value)) return '';
            return (string)$value;
        }, $text);
    }
}
