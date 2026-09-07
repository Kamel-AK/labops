<?php

namespace App\Services;

class NoShowDetector
{
    public function __construct(private readonly OperationalSnapshotProvider $snapshots) {}

    /** Sprint 1 only identifies candidates; state transitions are added with reservation lifecycle work. */
    public function scan(): int
    {
        return $this->snapshots->noShowCandidates(now())->count();
    }
}
