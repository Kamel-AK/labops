<?php

namespace App\Policies;

use App\Models\EquipmentCheckout;
use App\Models\Member;

class EquipmentCheckoutPolicy
{
    public function create(Member $user): bool
    {
        return $user->isCoordinator() || $user->isTeamLead();
    }

    public function checkIn(Member $user, EquipmentCheckout $checkout): bool
    {
        return $user->isCoordinator()
            || $user->isTeamLead()
            || (int) $checkout->member_id === (int) $user->id;
    }
}
