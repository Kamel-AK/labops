<?php

namespace App\Services;

class LowStockReconciler
{
    public function __construct(private readonly OperationalSnapshotProvider $snapshots) {}

    public function scan(): int
    {
        return $this->snapshots->lowStockEquipment()->count();
    }
}
