<?php

namespace App\Console\Commands;

use App\Services\OverstayService;
use Illuminate\Console\Command;


class ReleaseOverstayedReservations extends Command
{
    protected $signature = 'reservations:release-overstayed';

    protected $description = 'Force-complete and release spots for reservations that overstayed past the grace period.';

    public function handle(OverstayService $overstayService): int
    {
        $count = $overstayService->releaseOverstayed();

        $this->info("ReleaseOverstayedReservations: released {$count} reservation(s).");

        return self::SUCCESS;
    }
}
