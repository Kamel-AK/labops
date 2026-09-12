<?php

namespace App\Services;

use App\Enums\SpotStatus;
use App\Enums\ZoneStatus;
use App\Models\Spot;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AvailabilityService
{
    public function __construct(private readonly ConflictDetector $conflicts) {}

    /**
     * A small, authenticated query for calendar and booking forms.  It exposes no member data.
     */
    public function forInterval(string $startTime, string $endTime, ?int $zoneId = null, ?int $spotId = null): Collection
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        if ($end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages(['end_time' => 'The end time must be after the start time.']);
        }

        return Spot::query()
            ->with('zone:id,name,status,operating_hours_start,operating_hours_end')
            ->where('status', SpotStatus::ACTIVE->value)
            ->whereHas('zone', fn ($query) => $query->where('status', ZoneStatus::OPEN->value))
            ->when($zoneId, fn ($query) => $query->where('zone_id', $zoneId))
            ->when($spotId, fn ($query) => $query->whereKey($spotId))
            ->orderBy('zone_id')
            ->orderBy('name')
            ->get()
            ->map(fn (Spot $spot) => [
                'spot_id' => $spot->id,
                'spot_name' => $spot->name,
                'spot_type' => $spot->type->value,
                'zone_id' => $spot->zone_id,
                'zone_name' => $spot->zone->name,
                'available' => $this->isWithinOperatingHours($spot, $start, $end)
                    && ! $this->conflicts->hasSpotConflict($spot->id, $start, $end),
            ]);
    }

    private function isWithinOperatingHours(Spot $spot, Carbon $start, Carbon $end): bool
    {
        if ($spot->zone->operating_hours_start === null || $spot->zone->operating_hours_end === null) {
            return true;
        }

        $opens = $start->copy()->setTimeFromTimeString($spot->zone->operating_hours_start);
        $closes = $start->copy()->setTimeFromTimeString($spot->zone->operating_hours_end);

        return $start->isSameDay($end) && ! $start->lessThan($opens) && ! $end->greaterThan($closes);
    }
}
