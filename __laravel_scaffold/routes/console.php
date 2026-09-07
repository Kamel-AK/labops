<?php

use App\Services\DailyCoordinatorSummary;
use App\Services\LowStockReconciler;
use App\Services\NoShowDetector;
use App\Services\OverdueChecker;
use App\Services\ReservationReminderScanner;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('queue:prune-batches --hours=48')->daily();

Schedule::call(fn () => app(NoShowDetector::class)->scan())->everyFiveMinutes()->name('labops:no-show-detection');
Schedule::call(fn () => app(ReservationReminderScanner::class)->scan())->everyTenMinutes()->name('labops:reservation-reminders');
Schedule::call(fn () => app(OverdueChecker::class)->scan())->everyFifteenMinutes()->name('labops:overdue-checks');
Schedule::call(fn () => app(LowStockReconciler::class)->scan())->dailyAt('08:00')->name('labops:low-stock-reconciliation');
Schedule::call(fn () => app(DailyCoordinatorSummary::class)->prepare())->dailyAt('08:30')->name('labops:coordinator-summary');
