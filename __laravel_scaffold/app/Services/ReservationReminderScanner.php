<?php

namespace App\Services;

class ReservationReminderScanner
{
    public function __construct(private readonly OperationalSnapshotProvider $snapshots) {}

    public function scan(): int
    {
        return $this->snapshots->reminderCandidates(now())->count();
    }
}
