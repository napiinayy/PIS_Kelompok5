<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group_id'        => Group::factory(),
            'created_by'      => User::factory(),
            'name'            => fake()->words(3, true),
            'restaurant_name' => fake()->company(),
            'date'            => fake()->dateTimeThisMonth()->format('Y-m-d'),
            'tax_percent'     => fake()->randomElement([0, 10, 11]),
            'service_percent' => fake()->randomElement([0, 5, 10]),
            'status'          => 'draft',
        ];
    }
}
