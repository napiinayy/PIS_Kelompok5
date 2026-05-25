<?php

namespace Database\Factories;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bill_id' => Bill::factory(),
            'user_id' => null,
            'name'    => fake()->firstName(),
            'is_paid' => false,
        ];
    }
}
