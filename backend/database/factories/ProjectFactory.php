<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['planning', 'active', 'completed', 'on_hold']),
            'deadline' => fake()->dateTimeBetween('+1 month', '+1 year'),
            'user_id' => User::factory(),
        ];
    }
}
