<?php

namespace App\Console\Commands;

use App\Jobs\CreateInAppNotification;
use App\Models\ActivityLog;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class DetectNoShows extends Command
{
    protected $signature = 'reservations:detect-no-shows';

    protected $description = 'Mark confirmed reservations as no_show after the 15-minute grace period.';

    public function handle(): int
    {
        $cutoff = Carbon::now()->subMinutes(15);

        $candidateIds = Reservation::query()
            ->where('status', 'confirmed')
            ->whereNull('checked_in_at')
            ->where('start_time', '<=', $cutoff)
            ->pluck('id');

        $markedCount = 0;

        foreach ($candidateIds as $id) {
            $reservation = DB::transaction(function () use ($id) {
                
                $reservation = Reservation::whereKey($id)->lockForUpdate()->first();

                if (! $reservation
                    || $reservation->status !== 'confirmed'
                    || $reservation->checked_in_at !== null
                ) {
                    return null;
                }

                $reservation->update(['status' => 'no_show']);

                ActivityLog::create([
                    'entity_type' => 'reservation',
                    'entity_id' => $reservation->id,
                    'action' => 'no_show_detected',
                    'actor_type' => 'system',
                    'performed_by' => null,
                    'description' => 'Reservation automatically marked no_show after 15-minute grace period.',
                ]);

                return $reservation;
            });

            if ($reservation) {
                $markedCount++;

                CreateInAppNotification::dispatch(
                    $reservation->member_id,
                    'reservation_no_show',
                    ['reservation_id' => $reservation->id],
                )->afterCommit();
            }
        }

        $this->info("DetectNoShows: marked {$markedCount} reservation(s) as no_show.");

        return self::SUCCESS;
    }
}
