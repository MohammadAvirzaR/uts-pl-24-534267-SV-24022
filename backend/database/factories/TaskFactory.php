<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['todo', 'in_progress', 'review', 'done']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'deadline' => fake()->dateTimeBetween('+1 week', '+6 months'),
            'project_id' => Project::factory(),
            'assignee_id' => User::factory(),
        ];
    }
}
