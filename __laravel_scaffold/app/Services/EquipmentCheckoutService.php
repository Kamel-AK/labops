<?php

namespace App\Services;

use App\Enums\CheckoutStatus;
use App\Enums\EquipmentStatus;
use App\Enums\EquipmentType;
use App\Models\Equipment;
use App\Models\EquipmentCheckout;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EquipmentCheckoutService
{
    public function __construct(private readonly ActivityLogService $activityLogs) {}

    /** Transaction-safe boundary for the Sprint 3 checkout controller. */
    public function checkout(Member $actor, Equipment $equipment, Member $member, array $data): EquipmentCheckout
    {
        return DB::transaction(function () use ($actor, $equipment, $member, $data) {
            $equipment = Equipment::query()->lockForUpdate()->findOrFail($equipment->id);
            if ($equipment->type !== EquipmentType::DURABLE || $equipment->status !== EquipmentStatus::AVAILABLE) {
                throw ValidationException::withMessages(['equipment_id' => 'Equipment is not available for checkout.']);
            }
            if (! $member->hasGrantedAccess()) {
                throw ValidationException::withMessages(['member_id' => 'Equipment can only be assigned to a granted member.']);
            }
            $isBorrowed = ($data['checkout_type'] ?? 'in_lab') === 'borrowed';
            if ($isBorrowed && ! $equipment->allow_borrow) {
                throw ValidationException::withMessages(['checkout_type' => 'This equipment may not leave the lab.']);
            }
            $checkout = EquipmentCheckout::create([
                ...$data, 'equipment_id' => $equipment->id, 'member_id' => $member->id,
                'checked_out_at' => $data['checked_out_at'] ?? now(), 'status' => CheckoutStatus::ACTIVE->value,
            ]);
            $equipment->update(['status' => $isBorrowed ? EquipmentStatus::BORROWED : EquipmentStatus::IN_USE, 'current_custodian_id' => $member->id]);
            $this->activityLogs->record($checkout, 'equipment.checked_out', $actor, null, $checkout->only(['equipment_id', 'member_id', 'status']));

            return $checkout;
        });
    }

    public function checkIn(Member $actor, EquipmentCheckout $checkout, ?string $returnCondition = null, bool $conditionIssue = false): EquipmentCheckout
    {
        return DB::transaction(function () use ($actor, $checkout, $returnCondition, $conditionIssue) {
            $checkout = EquipmentCheckout::query()->lockForUpdate()->findOrFail($checkout->id);
            if (! in_array($checkout->status, [CheckoutStatus::ACTIVE->value, CheckoutStatus::OVERDUE->value], true)) {
                throw ValidationException::withMessages(['checkout' => 'This checkout has already been returned.']);
            }
            $equipment = Equipment::query()->lockForUpdate()->findOrFail($checkout->equipment_id);
            $checkout->update(['actual_return_at' => now(), 'status' => CheckoutStatus::RETURNED->value, 'return_condition' => $returnCondition]);
            $equipment->update(['current_custodian_id' => null, 'status' => $conditionIssue ? EquipmentStatus::MAINTENANCE : EquipmentStatus::AVAILABLE]);
            $this->activityLogs->record($checkout, 'equipment.checked_in', $actor, ['status' => $checkout->getOriginal('status')], ['status' => CheckoutStatus::RETURNED->value]);

            return $checkout->refresh();
        });
    }
}
