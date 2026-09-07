<?php

namespace App\Services;

class OverdueChecker
{
    public function __construct(private readonly OperationalSnapshotProvider $snapshots) {}

    public function scan(): int
    {
        return $this->snapshots->overdueCheckouts(now())->count();
    }
}
