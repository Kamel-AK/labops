<?php

namespace App\Services;

use App\Enums\CheckoutStatus;
use App\Enums\EquipmentType;
use App\Models\Equipment;
use App\Models\EquipmentCheckout;
use App\Models\Member;
use App\Models\Reservation;
use Carbon\CarbonInterface;

class OperationalSnapshotProvider
{
    public function noShowCandidates(CarbonInterface $now)
    {
        return Reservation::query()->where('status', 'confirmed')->whereNull('checked_in_at')->where('start_time', '<=', $now->copy()->subMinutes(15));
    }

    public function overdueCheckouts(CarbonInterface $now)
    {
        return EquipmentCheckout::query()->where('status', CheckoutStatus::ACTIVE->value)->where('expected_return_at', '<', $now);
    }

    public function reminderCandidates(CarbonInterface $now)
    {
        return Reservation::query()->where('status', 'confirmed')->whereBetween('start_time', [$now->copy()->addMinutes(25), $now->copy()->addMinutes(35)]);
    }

    public function lowStockEquipment()
    {
        return Equipment::query()->where('type', EquipmentType::CONSUMABLE->value)->whereColumn('quantity_available', '<=', 'min_stock_threshold');
    }

    public function coordinatorSummary(): array
    {
        return [
            'active_reservations' => Reservation::query()->whereIn('status', ['confirmed', 'checked_in'])->count(),
            'overdue_checkouts' => $this->overdueCheckouts(now())->count(),
            'low_stock_items' => $this->lowStockEquipment()->count(),
            'coordinator_count' => Member::query()->where('role', 'coordinator')->where('access_status', 'granted')->count(),
        ];
    }
}
