<?php

namespace App\Services\Clients;

class ConsentHasher
{
    public function sha256(string $text): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($text));
        return hash('sha256', $normalized);
    }
}
