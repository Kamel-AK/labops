<?php

namespace App\Services;

use App\Enums\EquipmentStatus;
use App\Models\ActivityLog;
use App\Models\Equipment;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;


class ReservationEquipmentService
{

    public function attach(Reservation $reservation, array $equipmentIds): void
    {
        $equipmentIds = array_values(array_unique($equipmentIds));

        if (empty($equipmentIds)) {
            return;
        }

        DB::transaction(function () use ($reservation, $equipmentIds) {
            $validEquipment = Equipment::query()
                ->whereIn('id', $equipmentIds)
                ->where('status', '!=', EquipmentStatus::RETIRED)
                ->lockForUpdate()
                ->get(['id']);

            if ($validEquipment->count() !== count($equipmentIds)) {
                $missingOrRetired = array_diff($equipmentIds, $validEquipment->pluck('id')->all());

                throw new InvalidArgumentException(
                    'The following equipment is unavailable or does not exist: '
                    . implode(', ', $missingOrRetired)
                );
            }

            $reservation->equipment()->syncWithoutDetaching($equipmentIds);

            ActivityLog::create([
                'entity_type' => 'reservation',
                'entity_id' => $reservation->id,
                'action' => 'equipment_linked',
                'actor_type' => 'user',
                'performed_by' => Auth::id(),
                'description' => 'Equipment linked to reservation for planning purposes.',
                'new_values' => ['equipment_ids' => $equipmentIds],
            ]);
        });
    }


    public function detach(Reservation $reservation, array $equipmentIds): void
    {
        DB::transaction(function () use ($reservation, $equipmentIds) {
            $reservation->equipment()->detach($equipmentIds);

            ActivityLog::create([
                'entity_type' => 'reservation',
                'entity_id' => $reservation->id,
                'action' => 'equipment_unlinked',
                'actor_type' => 'user',
                'performed_by' => Auth::id(),
                'description' => 'Equipment unlinked from reservation.',
                'new_values' => ['equipment_ids' => $equipmentIds],
            ]);
        });
    }


    public function linkedEquipment(Reservation $reservation)
    {
        return $reservation->equipment()
            ->select(['equipment.id', 'equipment.name', 'equipment.asset_tag', 'equipment.status'])
            ->get();
    }
}
