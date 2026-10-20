<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with('user')
            ->withCount('tasks')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:planning,active,completed,on_hold',
            'deadline' => 'nullable|date',
        ]);

        $project = $request->user()->projects()->create($validated);

        return response()->json([
            'message' => 'Project created successfully',
            'data' => $project->load('user'),
        ], 201);
    }

    public function show(Project $project)
    {
        return response()->json([
            'data' => $project->load(['user', 'tasks.assignee']),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:planning,active,completed,on_hold',
            'deadline' => 'nullable|date',
        ]);

        $project->update($validated);

        return response()->json([
            'message' => 'Project updated successfully',
            'data' => $project->load('user'),
        ]);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully',
        ]);
    }
}
