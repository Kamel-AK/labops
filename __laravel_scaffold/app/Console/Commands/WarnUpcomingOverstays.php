<?php

namespace App\Console\Commands;

use App\Services\OverstayService;
use Illuminate\Console\Command;


class WarnUpcomingOverstays extends Command
{
    protected $signature = 'reservations:warn-overstays';

    protected $description = 'Create in-app warnings for checked-in reservations ending within 15 minutes.';

    public function handle(OverstayService $overstayService): int
    {
        $count = $overstayService->warnUpcomingOverstays();

        $this->info("WarnUpcomingOverstays: warned {$count} reservation(s).");

        return self::SUCCESS;
    }
}
