<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'salon_id' => Salon::factory(),
            'name' => $this->faker->words(3, true),
            'description' => null,
            'duration_min' => 30,
            'buffer_min' => 0,
            'price_cents' => 2500,
            'currency' => 'EUR',
            'is_active' => true,
            'sort_order' => 0,
            'image_url' => null,
        ];
    }
}
