<?php

namespace App\Services;

use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use App\Models\Member;
use App\Models\Project;
use App\Models\Reservation;
use App\Models\Spot;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function __construct(
        private readonly ConflictDetector $conflicts,
        private readonly ReservationRules $rules,
        private readonly ActivityLogService $activityLogs,
        private readonly NotificationService $notifications,
    ) {}

    /** Lock the relevant spot before checking arbitrary time intervals and writing the booking. */
    public function create(Member $actor, array $data): Reservation
    {
        return DB::transaction(function () use ($actor, $data) {
            $reservation = $this->persist($actor, null, $data);
            $this->activityLogs->record($reservation, 'reservation.created', $actor, null, $this->auditValues($reservation));
            $this->notify($reservation, 'reservation.confirmed');

            return $reservation;
        });
    }

    public function update(Member $actor, Reservation $reservation, array $data): Reservation
    {
        return DB::transaction(function () use ($actor, $reservation, $data) {
            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);
            if ($reservation->status !== 'confirmed') {
                throw ValidationException::withMessages(['status' => 'Only confirmed reservations may be edited.']);
            }
            $oldValues = $this->auditValues($reservation);
            $reservation = $this->persist($actor, $reservation, $data);
            $this->activityLogs->record($reservation, 'reservation.updated', $actor, $oldValues, $this->auditValues($reservation));
            $this->notify($reservation, 'reservation.updated');

            return $reservation;
        });
    }

    public function cancel(Member $actor, Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($actor, $reservation) {
            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);
            if ($reservation->status !== 'confirmed') {
                throw ValidationException::withMessages(['status' => 'Only confirmed reservations may be cancelled.']);
            }
            $reservation->update(['status' => 'cancelled']);
            $this->activityLogs->record($reservation, 'reservation.cancelled', $actor, ['status' => 'confirmed'], ['status' => 'cancelled']);
            $this->notify($reservation, 'reservation.cancelled');

            return $reservation->refresh();
        });
    }

    public function checkIn(Member $actor, Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($actor, $reservation) {
            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);
            if ($reservation->status !== 'confirmed') {
                throw ValidationException::withMessages(['status' => 'Only confirmed reservations may be checked in.']);
            }
            $reservation->update(['status' => 'checked_in', 'checked_in_at' => now()]);
            $this->activityLogs->record($reservation, 'reservation.checked_in', $actor, ['status' => 'confirmed'], ['status' => 'checked_in', 'checked_in_at' => $reservation->checked_in_at]);

            return $reservation->refresh();
        });
    }

    public function complete(Member $actor, Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($actor, $reservation) {
            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);
            if ($reservation->status !== 'checked_in') {
                throw ValidationException::withMessages(['status' => 'Only checked-in reservations may be completed.']);
            }
            $reservation->update(['status' => 'completed', 'checked_out_at' => now()]);
            $this->activityLogs->record($reservation, 'reservation.completed', $actor, ['status' => 'checked_in'], ['status' => 'completed', 'checked_out_at' => $reservation->checked_out_at]);

            return $reservation->refresh();
        });
    }

    public function extend(Member $actor, Reservation $reservation, string $endTime): Reservation
    {
        return DB::transaction(function () use ($actor, $reservation, $endTime) {
            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);
            if ($reservation->status !== 'checked_in') {
                throw ValidationException::withMessages(['status' => 'Only checked-in reservations may be extended.']);
            }
            $spot = Spot::query()->lockForUpdate()->findOrFail($reservation->spot_id);
            $zone = Zone::findOrFail($reservation->zone_id);
            $end = Carbon::parse($endTime);
            $this->rules->validate($reservation->member, $zone, $spot, $reservation->start_time, $end, false);
            if ($this->conflicts->hasSpotConflict($spot->id, $reservation->start_time, $end, $reservation->id)) {
                throw ValidationException::withMessages(['end_time' => 'The spot is not available for the requested extension.']);
            }
            $oldValues = ['end_time' => $reservation->end_time->toIso8601String()];
            $reservation->update(['end_time' => $end]);
            $this->activityLogs->record($reservation, 'reservation.extended', $actor, $oldValues, ['end_time' => $end->toIso8601String()]);

            return $reservation->refresh();
        });
    }

    private function persist(Member $actor, ?Reservation $reservation, array $data): Reservation
    {
        $member = Member::findOrFail($data['member_id']);
        $zone = Zone::findOrFail($data['zone_id']);
        $spot = Spot::query()->lockForUpdate()->findOrFail($data['spot_id']);
        if ((int) $spot->zone_id !== (int) $zone->id) {
            throw ValidationException::withMessages(['spot_id' => 'The selected spot does not belong to the selected zone.']);
        }

        $start = Carbon::parse($data['start_time']);
        $end = Carbon::parse($data['end_time']);
        $this->rules->validate($member, $zone, $spot, $start, $end);
        $this->validateProjectLink($member, $data['project_id'] ?? null);
        $this->validateEquipmentLinks($data['equipment_ids'] ?? []);

        if ($this->conflicts->hasActiveReservationForMember($member->id, $reservation?->id)) {
            throw ValidationException::withMessages(['member_id' => 'A member may only have one active reservation.']);
        }
        if ($this->conflicts->hasSpotConflict($spot->id, $start, $end, $reservation?->id)) {
            throw ValidationException::withMessages(['spot_id' => 'The selected spot is already reserved for that time.']);
        }

        $attributes = collect($data)->except('equipment_ids')->all();
        if ($reservation === null) {
            $reservation = Reservation::create([...$attributes, 'created_by' => $actor->id, 'status' => 'confirmed']);
        } else {
            $reservation->update($attributes);
            $reservation->refresh();
        }
        $reservation->equipment()->sync($data['equipment_ids'] ?? []);

        return $reservation->refresh();
    }

    private function validateProjectLink(Member $member, ?int $projectId): void
    {
        if ($projectId === null) {
            return;
        }
        $project = Project::findOrFail($projectId);
        if ($project->status !== 'active') {
            throw ValidationException::withMessages(['project_id' => 'Reservations may only be linked to active projects.']);
        }
        if (! $project->hasMember($member) && (int) $project->lead_id !== (int) $member->id) {
            throw ValidationException::withMessages(['project_id' => 'The reservation member must belong to the selected project.']);
        }
    }

    private function validateEquipmentLinks(array $equipmentIds): void
    {
        $equipmentIds = array_values(array_unique($equipmentIds));
        $availableCount = Equipment::query()->whereIn('id', $equipmentIds)->where('status', '!=', EquipmentStatus::RETIRED->value)->count();
        if ($availableCount !== count($equipmentIds)) {
            throw ValidationException::withMessages(['equipment_ids' => 'Linked equipment must exist and cannot be retired.']);
        }
    }

    private function notify(Reservation $reservation, string $type): void
    {
        $reservation->loadMissing('member');
        $this->notifications->queue($reservation->member, $type, [
            'reservation_id' => $reservation->id,
            'start_time' => $reservation->start_time->toIso8601String(),
            'end_time' => $reservation->end_time->toIso8601String(),
            'status' => $reservation->status,
        ]);
    }

    private function auditValues(Reservation $reservation): array
    {
        return [
            'member_id' => $reservation->member_id,
            'project_id' => $reservation->project_id,
            'zone_id' => $reservation->zone_id,
            'spot_id' => $reservation->spot_id,
            'start_time' => $reservation->start_time->toIso8601String(),
            'end_time' => $reservation->end_time->toIso8601String(),
            'purpose' => $reservation->purpose,
            'status' => $reservation->status,
        ];
    }
}
