<?php

namespace Database\Factories;

use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SalonFactory extends Factory
{
    protected $model = Salon::class;

    public function definition(): array
    {
        $name = $this->faker->company();
        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'currency' => 'EUR',
            'timezone' => 'Europe/Sarajevo',
            'status' => 'active',
        ];
    }
}
