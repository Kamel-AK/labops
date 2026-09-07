<?php

namespace App\Services;

class DailyCoordinatorSummary
{
    public function __construct(private readonly OperationalSnapshotProvider $snapshots) {}

    public function prepare(): array
    {
        return $this->snapshots->coordinatorSummary();
    }
}
