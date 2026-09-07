<?php

namespace App\Policies;

use App\Models\Member;

class MemberPolicy
{
    public function viewAny(Member $user): bool
    {
        return $user->isCoordinator() || $user->isTeamLead();
    }

    public function view(Member $user, Member $member): bool
    {
        return $user->isCoordinator()
            || $user->isTeamLead()
            || (int) $user->id === (int) $member->id;
    }

    public function viewContactDetails(Member $user, ?Member $member = null): bool
    {
        return $user->isCoordinator() || $user->isTeamLead();
    }

    public function create(Member $user): bool
    {
        return $user->isCoordinator();
    }

    public function update(Member $user, Member $member): bool
    {
        return $user->isCoordinator() || (int) $user->id === (int) $member->id;
    }

    public function manageLifecycle(Member $user, Member $member): bool
    {
        return $user->isCoordinator();
    }

    public function delete(Member $user, Member $member): bool
    {
        return $user->isCoordinator() && (int) $user->id !== (int) $member->id;
    }
}
