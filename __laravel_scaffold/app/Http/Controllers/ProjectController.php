<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Project;
use App\Services\ActivityLogService;
use App\Services\ProjectWorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ProjectWorkflowService $workflow, private readonly ActivityLogService $activityLogs) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Project::class);

        return Inertia::render('projects/pages/Index', [
            'projects' => Project::query()->with(['lead', 'requester'])->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'lead_id' => ['required', 'exists:members,id'],
            'start_date' => ['nullable', 'date'],
            'target_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);
        $lead = Member::findOrFail($data['lead_id']);
        abort_unless($lead->hasGrantedAccess() && ($lead->isCoordinator() || $lead->isTeamLead()), 422, 'Project lead must be a granted Coordinator or Team Lead.');

        $project = Project::create([...$data, 'requested_by' => $request->user()->id, 'status' => 'proposed']);
        $project->members()->syncWithoutDetaching([$lead->id => ['role_in_project' => 'lead', 'joined_at' => now()]]);
        $this->activityLogs->record($project, 'project.created', $request->user(), null, $project->only(['name', 'status', 'lead_id']));

        return back()->with('success', 'Project proposal created.');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return Inertia::render('projects/pages/Index', ['project' => $project->load(['lead', 'requester', 'members', 'equipmentNeeds'])]);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $project->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'target_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]));

        return back()->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();
        $this->activityLogs->record($project, 'project.deleted', request()->user(), null, ['deleted_at' => now()->toIso8601String()]);

        return back()->with('success', 'Project archived from the registry.');
    }

    public function transition(Request $request, Project $project)
    {
        $action = $request->validate(['action' => ['required', 'in:submit,request_changes,approve,reject,pause,resume,complete,archive']])['action'];
        $this->authorize(in_array($action, ['approve', 'request_changes', 'reject'], true) ? 'approve' : 'update', $project);
        $this->workflow->transition($project, $action, $request->user());

        return back()->with('success', 'Project status updated.');
    }

    public function syncMembers(Request $request, Project $project)
    {
        $this->authorize('manageTeam', $project);
        $data = $request->validate(['members' => ['required', 'array'], 'members.*.id' => ['required', 'exists:members,id'], 'members.*.role_in_project' => ['required', 'in:lead,member,advisor']]);
        $members = Member::whereIn('id', collect($data['members'])->pluck('id'))->get();
        abort_if($members->contains(fn (Member $member) => ! $member->hasGrantedAccess()), 422, 'Only granted members can join a project.');
        $project->members()->sync(collect($data['members'])->mapWithKeys(fn ($member) => [$member['id'] => ['role_in_project' => $member['role_in_project'], 'joined_at' => now()]])->all());

        return back()->with('success', 'Project team updated.');
    }

    public function syncEquipmentNeeds(Request $request, Project $project)
    {
        $this->authorize('manageTeam', $project);
        $data = $request->validate(['needs' => ['required', 'array'], 'needs.*.equipment_id' => ['required', 'exists:equipment,id'], 'needs.*.quantity_needed' => ['required', 'integer', 'min:1'], 'needs.*.notes' => ['nullable', 'string']]);
        $project->equipmentNeeds()->sync(collect($data['needs'])->mapWithKeys(fn ($need) => [$need['equipment_id'] => ['quantity_needed' => $need['quantity_needed'], 'notes' => $need['notes'] ?? null]])->all());

        return back()->with('success', 'Project equipment needs updated.');
    }
}
