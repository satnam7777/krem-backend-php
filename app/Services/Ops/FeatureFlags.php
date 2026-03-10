<?php

namespace App\Services\Ops;

use App\Models\FeatureFlag;

class FeatureFlags
{
    public function enabled(int $salonId, string $key, bool $default = false): bool
    {
        $row = FeatureFlag::where('salon_id',$salonId)->where('key',$key)->first();
        return $row ? (bool)$row->enabled : $default;
    }

    public function set(int $salonId, string $key, bool $enabled, array $meta = []): FeatureFlag
    {
        return FeatureFlag::updateOrCreate(
            ['salon_id'=>$salonId,'key'=>$key],
            ['enabled'=>$enabled,'meta'=>$meta]
        );
    }
}
