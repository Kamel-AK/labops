<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EquipmentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('dashboard/pages/Index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('members')->name('members.')->group(function () {
        Route::get('/', fn () => Inertia::render('members/pages/Index'))->name('index');
    });

    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', fn () => Inertia::render('projects/pages/Index'))->name('index');
    });

    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/', fn () => Inertia::render('reservations/pages/Index'))->name('index');
    });

    Route::prefix('equipment')->name('equipment.')->group(function () {
        Route::get('/', fn () => Inertia::render('equipment/pages/Index'))->name('index');
        Route::post('/import', [EquipmentController::class, 'import'])->name('import');
    });

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', fn () => Inertia::render('inventory/pages/Index'))->name('index');
    });

    Route::prefix('zones')->name('zones.')->group(function () {
        Route::get('/', fn () => Inertia::render('zones/pages/Index'))->name('index');
    });

    Route::prefix('spots')->name('spots.')->group(function () {
        Route::get('/', fn () => Inertia::render('spots/pages/Index'))->name('index');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', fn () => Inertia::render('notifications/pages/Index'))->name('index');
    });

    Route::prefix('activity-log')->name('activity-log.')->group(function () {
        Route::get('/', fn () => Inertia::render('activity-log/pages/Index'))->name('index');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', fn () => Inertia::render('settings/pages/Index'))->name('index');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
