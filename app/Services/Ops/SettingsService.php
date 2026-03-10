<?php

namespace App\Services\Ops;

use App\Models\SalonSetting;

class SettingsService
{
    public function get(int $salonId, string $key, $default = null)
    {
        $row = SalonSetting::where('salon_id',$salonId)->where('key',$key)->first();
        if (!$row) return $default;

        return $this->castOut($row->type, $row->value);
    }

    public function set(int $salonId, string $key, string $type, $value): SalonSetting
    {
        $payload = $this->castIn($type, $value);

        return SalonSetting::updateOrCreate(
            ['salon_id'=>$salonId,'key'=>$key],
            ['type'=>$type,'value'=>$payload]
        );
    }

    private function castIn(string $type, $value): array
    {
        return match ($type) {
            'string' => ['v'=>(string)$value],
            'int' => ['v'=>(int)$value],
            'bool' => ['v'=>(bool)$value],
            'json' => ['v'=>$value],
            default => ['v'=>$value],
        };
    }

    private function castOut(string $type, ?array $value)
    {
        $v = $value['v'] ?? null;
        return match ($type) {
            'string' => (string)$v,
            'int' => (int)$v,
            'bool' => (bool)$v,
            'json' => $v,
            default => $v,
        };
    }
}
