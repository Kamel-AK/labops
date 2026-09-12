<?php

namespace App\Services;

use App\Enums\SpotStatus;
use App\Enums\ZoneStatus;
use App\Models\Member;
use App\Models\Spot;
use App\Models\Zone;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class ReservationRules
{
    public function validate(Member $member, Zone $zone, Spot $spot, CarbonInterface $start, CarbonInterface $end, bool $enforceAdvanceWindow = true): void
    {
        $errors = [];
        if (! $member->hasGrantedAccess()) {
            $errors['member_id'] = 'Reservations require a granted member account.';
        }
        if ($zone->status !== ZoneStatus::OPEN) {
            $errors['zone_id'] = 'The selected zone is not open for reservations.';
        }
        if ($spot->status !== SpotStatus::ACTIVE) {
            $errors['spot_id'] = 'The selected spot is not active for reservations.';
        }
        if ($end->lessThanOrEqualTo($start)) {
            $errors['end_time'] = 'The end time must be after the start time.';
        }

        $duration = $start->diffInMinutes($end, false);
        if ($duration > config('reservations.max_duration_minutes')) {
            $errors['end_time'] = 'Reservations may not exceed '.config('reservations.max_duration_minutes').' minutes.';
        }
        if (! $start->isSameDay($end)) {
            $errors['end_time'] = 'Reservations must start and end on the same day.';
        }

        if ($enforceAdvanceWindow) {
            if ($start->lessThan(now())) {
                $errors['start_time'] = 'Reservations cannot start in the past.';
            }
            if ($start->greaterThan(now()->addDays(config('reservations.advance_booking_days')))) {
                $errors['start_time'] = 'Reservations may only be booked '.config('reservations.advance_booking_days').' days in advance.';
            }
        }

        if ($zone->operating_hours_start !== null && $zone->operating_hours_end !== null) {
            $opens = $start->copy()->setTimeFromTimeString($zone->operating_hours_start);
            $closes = $start->copy()->setTimeFromTimeString($zone->operating_hours_end);
            if ($start->lessThan($opens) || $end->greaterThan($closes)) {
                $errors['start_time'] = 'The reservation must fall within the zone operating hours.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
