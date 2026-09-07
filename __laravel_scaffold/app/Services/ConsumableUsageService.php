<?php

namespace App\Services;

use App\Enums\EquipmentType;
use App\Models\ConsumableUsage;
use App\Models\Equipment;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsumableUsageService
{
    public function __construct(private readonly ActivityLogService $activityLogs) {}

    public function record(Member $actor, Equipment $equipment, Member $member, float $quantityUsed, ?int $projectId = null, ?string $notes = null): ConsumableUsage
    {
        return DB::transaction(function () use ($actor, $equipment, $member, $quantityUsed, $projectId, $notes) {
            $equipment = Equipment::query()->lockForUpdate()->findOrFail($equipment->id);
            if ($equipment->type !== EquipmentType::CONSUMABLE || $quantityUsed <= 0 || $quantityUsed > $equipment->quantity_available) {
                throw ValidationException::withMessages(['quantity_used' => 'Usage must be positive and cannot exceed stock on hand.']);
            }
            $remaining = $equipment->quantity_available - $quantityUsed;
            $equipment->update(['quantity_available' => $remaining]);
            $usage = ConsumableUsage::create(['equipment_id' => $equipment->id, 'member_id' => $member->id, 'project_id' => $projectId, 'quantity_used' => $quantityUsed, 'quantity_remaining_after' => $remaining, 'notes' => $notes]);
            $this->activityLogs->record($usage, 'consumable.used', $actor, ['quantity_available' => $remaining + $quantityUsed], ['quantity_available' => $remaining]);

            return $usage;
        });
    }
}
