<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Reservation;
use App\Models\Spot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function __construct(private readonly ConflictDetector $conflicts, private readonly ActivityLogService $activityLogs) {}

    /** Locks the relevant spot before checking the arbitrary time interval. */
    public function create(Member $actor, array $data): Reservation
    {
        return DB::transaction(function () use ($actor, $data) {
            $spot = Spot::query()->lockForUpdate()->findOrFail($data['spot_id']);
            if ((int) $spot->zone_id !== (int) $data['zone_id']) {
                throw ValidationException::withMessages(['spot_id' => 'The selected spot does not belong to the selected zone.']);
            }
            $start = Carbon::parse($data['start_time']);
            $end = Carbon::parse($data['end_time']);
            if ($end->lessThanOrEqualTo($start)) {
                throw ValidationException::withMessages(['end_time' => 'The end time must be after the start time.']);
            }
            if ($this->conflicts->hasActiveReservationForMember($data['member_id'])) {
                throw ValidationException::withMessages(['member_id' => 'A member may only have one active reservation.']);
            }
            if ($this->conflicts->hasSpotConflict($spot->id, $start, $end)) {
                throw ValidationException::withMessages(['spot_id' => 'The selected spot is already reserved for that time.']);
            }

            $reservation = Reservation::create([...$data, 'created_by' => $actor->id, 'status' => 'confirmed']);
            $this->activityLogs->record($reservation, 'reservation.created', $actor, null, $reservation->only(['member_id', 'spot_id', 'start_time', 'end_time', 'status']));

            return $reservation;
        });
    }
}
