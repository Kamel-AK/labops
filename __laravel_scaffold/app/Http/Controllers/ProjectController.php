<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function index(): Response
    {
        $this->authorize('viewAny', Project::class);

        return Inertia::render('projects/pages/Index');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
    }
}
