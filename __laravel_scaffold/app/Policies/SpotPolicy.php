<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\Spot;

class SpotPolicy
{
    public function viewAny(Member $user): bool
    {
        return $user->hasGrantedAccess();
    }

    public function view(Member $user, Spot $spot): bool
    {
        return $user->hasGrantedAccess();
    }

    public function create(Member $user): bool
    {
        return $user->isCoordinator();
    }

    public function update(Member $user, Spot $spot): bool
    {
        return $user->isCoordinator();
    }

    public function delete(Member $user, Spot $spot): bool
    {
        return $user->isCoordinator();
    }
}
