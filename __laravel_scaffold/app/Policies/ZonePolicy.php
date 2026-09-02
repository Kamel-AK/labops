<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\Zone;

class ZonePolicy
{
    public function viewAny(Member $user): bool
    {
        return $user->hasGrantedAccess();
    }

    public function view(Member $user, Zone $zone): bool
    {
        return $user->hasGrantedAccess();
    }

    public function create(Member $user): bool
    {
        return $user->isCoordinator();
    }

    public function update(Member $user, Zone $zone): bool
    {
        return $user->isCoordinator();
    }

    public function delete(Member $user, Zone $zone): bool
    {
        return $user->isCoordinator();
    }
}
