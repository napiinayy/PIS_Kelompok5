<?php

namespace Database\Factories;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bill_id'  => Bill::factory(),
            'name'     => fake()->words(2, true),
            'price'    => fake()->randomElement([8000, 15000, 25000, 35000, 50000, 75000]),
            'quantity' => fake()->numberBetween(1, 3),
        ];
    }
}
