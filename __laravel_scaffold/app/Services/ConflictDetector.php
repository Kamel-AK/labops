<?php

namespace App\Services;

use App\Models\Reservation;
use Carbon\CarbonInterface;

class ConflictDetector
{
    public function hasSpotConflict(int $spotId, CarbonInterface $start, CarbonInterface $end, ?int $ignoreReservationId = null): bool
    {
        return Reservation::query()
            ->where('spot_id', $spotId)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->when($ignoreReservationId, fn ($query) => $query->whereKeyNot($ignoreReservationId))
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();
    }

    public function hasActiveReservationForMember(int $memberId, ?int $ignoreReservationId = null): bool
    {
        return Reservation::query()
            ->where('member_id', $memberId)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->when($ignoreReservationId, fn ($query) => $query->whereKeyNot($ignoreReservationId))
            ->exists();
    }
}
