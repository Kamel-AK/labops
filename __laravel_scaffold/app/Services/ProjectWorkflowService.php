<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Member;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectWorkflowService
{
    public function __construct(
        private readonly ActivityLogService $activityLogs,
        private readonly NotificationService $notifications,
    ) {}

    public function transition(Project $project, string $action, Member $actor): Project
    {
        $transitions = [
            'submit' => [ProjectStatus::PROPOSED->value, ProjectStatus::PENDING_APPROVAL->value, null],
            'request_changes' => [ProjectStatus::PENDING_APPROVAL->value, ProjectStatus::PROPOSED->value, 'project.changes_requested'],
            'approve' => [ProjectStatus::PENDING_APPROVAL->value, ProjectStatus::ACTIVE->value, 'project.approved'],
            'reject' => [ProjectStatus::PENDING_APPROVAL->value, ProjectStatus::ARCHIVED->value, 'project.rejected'],
            'pause' => [ProjectStatus::ACTIVE->value, ProjectStatus::PAUSED->value, null],
            'resume' => [ProjectStatus::PAUSED->value, ProjectStatus::ACTIVE->value, null],
            'complete' => [ProjectStatus::ACTIVE->value, ProjectStatus::COMPLETED->value, null],
            'archive' => [null, ProjectStatus::ARCHIVED->value, null],
        ];

        if (! isset($transitions[$action])) {
            throw ValidationException::withMessages(['action' => 'Unsupported project lifecycle action.']);
        }

        [$from, $to, $notificationType] = $transitions[$action];

        return DB::transaction(function () use ($project, $actor, $action, $from, $to, $notificationType) {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $current = $project->status instanceof ProjectStatus ? $project->status->value : $project->status;

            $isValidArchive = $action === 'archive' && in_array($current, [ProjectStatus::ACTIVE->value, ProjectStatus::COMPLETED->value], true);
            if (($from !== null && $current !== $from) || ($from === null && ! $isValidArchive)) {
                throw ValidationException::withMessages(['status' => "The project cannot be {$action}d from its current status."]);
            }

            $oldValues = ['status' => $current];
            $updates = ['status' => $to];
            if ($action === 'approve') {
                $updates += ['approved_by' => $actor->id, 'approved_at' => now()];
            }

            $project->update($updates);
            $this->activityLogs->record($project, "project.{$action}", $actor, $oldValues, $updates);

            if ($notificationType !== null) {
                $project->loadMissing('requester', 'lead');
                collect([$project->requester, $project->lead])
                    ->filter()
                    ->unique('id')
                    ->each(fn (Member $recipient) => $this->notifications->queue($recipient, $notificationType, [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'status' => $to,
                    ]));
            }

            return $project->refresh();
        });
    }
}
