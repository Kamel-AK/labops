<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\Project;
use App\Models\Reservation;

class ReservationPolicy
{
    public function viewAny(Member $user): bool
    {
        return $user->hasGrantedAccess();
    }

    public function view(Member $user, Reservation $reservation): bool
    {
        return $user->isCoordinator()
            || (int) $reservation->member_id === (int) $user->id
            || (int) $reservation->created_by === (int) $user->id
            || ($reservation->project && $reservation->project->isLedBy($user));
    }

    public function create(Member $user): bool
    {
        return $user->hasGrantedAccess();
    }

    public function createFor(Member $user, Member $targetMember, ?Project $project = null): bool
    {
        if (! $targetMember->hasGrantedAccess()) {
            return false;
        }

        if ($user->isCoordinator()) {
            return true;
        }

        if ((int) $user->id === (int) $targetMember->id) {
            return true;
        }

        return $user->isTeamLead()
            && $project !== null
            && $project->isLedBy($user)
            && $project->hasMember($targetMember);
    }

    public function update(Member $user, Reservation $reservation): bool
    {
        return $user->isCoordinator() || (int) $reservation->created_by === (int) $user->id;
    }

    public function cancel(Member $user, Reservation $reservation): bool
    {
        return $this->update($user, $reservation);
    }

    public function checkIn(Member $user, Reservation $reservation): bool
    {
        return $user->isCoordinator()
            || (int) $reservation->member_id === (int) $user->id
            || ($user->isTeamLead() && $reservation->project && $reservation->project->isLedBy($user));
    }

    public function complete(Member $user, Reservation $reservation): bool
    {
        return $this->checkIn($user, $reservation);
    }

    public function extend(Member $user, Reservation $reservation): bool
    {
        return $this->checkIn($user, $reservation);
    }
}
