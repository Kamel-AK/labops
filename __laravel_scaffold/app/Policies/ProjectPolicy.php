<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\Project;

class ProjectPolicy
{
    public function viewAny(Member $user): bool
    {
        return $user->hasGrantedAccess();
    }

    public function view(Member $user, Project $project): bool
    {
        return $user->isCoordinator()
            || $project->isLedBy($user)
            || $project->hasMember($user)
            || (int) $project->requested_by === (int) $user->id;
    }

    public function create(Member $user): bool
    {
        return $user->hasGrantedAccess();
    }

    public function update(Member $user, Project $project): bool
    {
        return $user->isCoordinator() || $project->isLedBy($user);
    }

    public function approve(Member $user, Project $project): bool
    {
        return $user->isCoordinator();
    }

    public function delete(Member $user, Project $project): bool
    {
        return $user->isCoordinator();
    }
}
