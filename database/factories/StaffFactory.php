<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        return [
            'salon_id' => Salon::factory(),
            'name' => $this->faker->name(),
            'title' => null,
            'is_active' => true,
            'sort_order' => 0,
            'avatar_url' => null,
        ];
    }
}
