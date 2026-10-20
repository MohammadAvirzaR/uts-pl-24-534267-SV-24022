<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create PM user
        $pm = User::create([
            'name' => 'Admin PM',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pm',
        ]);

        // Create member users
        $member1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
            'role' => 'member',
        ]);

        $member2 = User::create([
            'name' => 'Siti Rahayu',
            'email' => 'siti@example.com',
            'password' => Hash::make('password123'),
            'role' => 'member',
        ]);

        // Create projects
        $project1 = Project::create([
            'name' => 'Website Redesign',
            'description' => 'Redesign the company website with modern UI/UX',
            'status' => 'active',
            'deadline' => '2026-12-31',
            'user_id' => $pm->id,
        ]);

        $project2 = Project::create([
            'name' => 'Mobile App Development',
            'description' => 'Build a cross-platform mobile application',
            'status' => 'planning',
            'deadline' => '2027-03-15',
            'user_id' => $pm->id,
        ]);

        // Create tasks for project 1
        Task::create([
            'title' => 'Design wireframes',
            'description' => 'Create wireframes for all pages',
            'status' => 'done',
            'priority' => 'high',
            'deadline' => '2026-11-15',
            'project_id' => $project1->id,
            'assignee_id' => $member1->id,
        ]);

        Task::create([
            'title' => 'Implement homepage',
            'description' => 'Develop the homepage based on wireframes',
            'status' => 'in_progress',
            'priority' => 'high',
            'deadline' => '2026-11-30',
            'project_id' => $project1->id,
            'assignee_id' => $member1->id,
        ]);

        Task::create([
            'title' => 'Setup CI/CD pipeline',
            'description' => 'Configure GitHub Actions for automated deployment',
            'status' => 'todo',
            'priority' => 'medium',
            'deadline' => '2026-12-01',
            'project_id' => $project1->id,
            'assignee_id' => $member2->id,
        ]);

        // Create tasks for project 2
        Task::create([
            'title' => 'Research tech stack',
            'description' => 'Evaluate Flutter vs React Native',
            'status' => 'in_progress',
            'priority' => 'critical',
            'deadline' => '2026-11-01',
            'project_id' => $project2->id,
            'assignee_id' => $member2->id,
        ]);

        Task::create([
            'title' => 'Create project roadmap',
            'description' => 'Define milestones and deliverables',
            'status' => 'todo',
            'priority' => 'high',
            'deadline' => '2026-11-10',
            'project_id' => $project2->id,
            'assignee_id' => $member1->id,
        ]);
    }
}
