<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Spot;
use App\Models\Zone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;


class AvailabilityService
{

    private const BLOCKING_STATUSES = ['confirmed', 'checked_in'];

    private const CALENDAR_VISIBLE_STATUSES = ['confirmed', 'checked_in', 'completed', 'no_show'];


    public function getCalendarData(?int $zoneId, Carbon $rangeStart, Carbon $rangeEnd): Collection
    {
        return Reservation::query()
            ->with([
                'member:id,full_name',
                'project:id,name',
                'spot:id,name,zone_id,status',
            ])
            ->whereIn('status', self::CALENDAR_VISIBLE_STATUSES)
            ->where('start_time', '<', $rangeEnd)
            ->where('end_time', '>', $rangeStart)
            ->when($zoneId, fn ($query) => $query->where('zone_id', $zoneId))
            ->orderBy('start_time')
            ->get();
    }


    public function availableSpots(int $zoneId, Carbon $start, Carbon $end): Collection
    {
        $zone = Zone::findOrFail($zoneId);

        if ($zone->status !== 'open') {
            return collect();
        }

        return Spot::query()
            ->where('zone_id', $zoneId)
            ->where('status', 'active')
            ->whereDoesntHave('reservations', function ($query) use ($start, $end) {
                $query->whereIn('status', self::BLOCKING_STATUSES)
                    ->where('start_time', '<', $end)
                    ->where('end_time', '>', $start);
            })
            ->orderBy('name')
            ->get();
    }

    
    public function getSpotAvailability(int $spotId, Carbon $date): array
    {
        $spot = Spot::findOrFail($spotId);

        if ($spot->status !== 'active') {
            return [
                'spot_id' => $spot->id,
                'is_bookable' => false,
                'reason' => "spot_status_{$spot->status}",
                'busy_slots' => [],
            ];
        }

        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();

        $busySlots = Reservation::query()
            ->where('spot_id', $spotId)
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->where('start_time', '<', $dayEnd)
            ->where('end_time', '>', $dayStart)
            ->orderBy('start_time')
            ->get(['id', 'start_time', 'end_time', 'member_id'])
            ->map(fn (Reservation $r) => [
                'reservation_id' => $r->id,
                'start_time' => $r->start_time,
                'end_time' => $r->end_time,
            ]);

        return [
            'spot_id' => $spot->id,
            'is_bookable' => true,
            'busy_slots' => $busySlots,
        ];
    }
}
