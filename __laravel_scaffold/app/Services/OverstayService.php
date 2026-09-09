<?php

namespace App\Services;

use App\Jobs\CreateInAppNotification;
use App\Models\ActivityLog;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class OverstayService
{
    private const WARNING_WINDOW_MINUTES = 15;
    private const GRACE_PERIOD_MINUTES = 15;


    public function warnUpcomingOverstays(): int
    {
        $now = Carbon::now();
        $warnBefore = $now->copy()->addMinutes(self::WARNING_WINDOW_MINUTES);

        $candidates = Reservation::query()
            ->where('status', 'checked_in')
            ->whereBetween('end_time', [$now, $warnBefore])
            ->whereDoesntHave('activityLogs', function ($query) {
                $query->where('action', 'overstay_warning');
            })
            ->get();

        foreach ($candidates as $reservation) {
            DB::transaction(function () use ($reservation) {
                ActivityLog::create([
                    'entity_type' => 'reservation',
                    'entity_id' => $reservation->id,
                    'action' => 'overstay_warning',
                    'actor_type' => 'system',
                    'performed_by' => null,
                    'description' => 'Reservation ending within 15 minutes; overstay warning issued.',
                ]);
            });

            CreateInAppNotification::dispatch(
                $reservation->member_id,
                'reservation_overstay_warning',
                [
                    'reservation_id' => $reservation->id,
                    'end_time' => $reservation->end_time->toIso8601String(),
                ],
            )->afterCommit();
        }

        return $candidates->count();
    }


    public function releaseOverstayed(): int
    {
        $graceDeadline = Carbon::now()->subMinutes(self::GRACE_PERIOD_MINUTES);

        $candidateIds = Reservation::query()
            ->where('status', 'checked_in')
            ->where('end_time', '<', $graceDeadline)
            ->pluck('id');

        $releasedCount = 0;

        foreach ($candidateIds as $id) {
            $released = DB::transaction(function () use ($id) {

                $reservation = Reservation::whereKey($id)->lockForUpdate()->first();

                if (! $reservation || $reservation->status !== 'checked_in') {
                    return null;
                }

                if ($reservation->end_time >= Carbon::now()->subMinutes(self::GRACE_PERIOD_MINUTES)) {
                    return null;
                }

                $reservation->update([
                    'status' => 'completed',
                    'checked_out_at' => Carbon::now(),
                ]);

                ActivityLog::create([
                    'entity_type' => 'reservation',
                    'entity_id' => $reservation->id,
                    'action' => 'overstay_release',
                    'actor_type' => 'system',
                    'performed_by' => null,
                    'description' => 'Spot force-released after overstay grace period expired.',
                ]);

                return $reservation;
            });

            if ($released) {
                $releasedCount++;

                CreateInAppNotification::dispatch(
                    $released->member_id,
                    'reservation_overstay_release',
                    ['reservation_id' => $released->id],
                )->afterCommit();
            }
        }

        return $releasedCount;
    }
}
