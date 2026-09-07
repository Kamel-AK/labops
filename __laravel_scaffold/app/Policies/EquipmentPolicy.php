<?php

namespace App\Policies;

use App\Models\Equipment;
use App\Models\Member;

class EquipmentPolicy
{
    public function viewAny(Member $user): bool
    {
        return $user->hasGrantedAccess();
    }

    public function view(Member $user, Equipment $equipment): bool
    {
        return $user->hasGrantedAccess();
    }

    public function create(Member $user): bool
    {
        return $user->isCoordinator();
    }

    public function update(Member $user, Equipment $equipment): bool
    {
        return $user->isCoordinator();
    }

    public function delete(Member $user, Equipment $equipment): bool
    {
        return $user->isCoordinator();
    }

    public function initiateCheckout(Member $user, Equipment $equipment): bool
    {
        return $equipment->type === 'durable'
            && ($user->isCoordinator() || $user->isTeamLead());
    }
}
