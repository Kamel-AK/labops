<?php

use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberStatusController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SpotController;
use App\Http\Controllers\ZoneController;
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
        Route::get('/', [MemberController::class, 'index'])->name('index');
        Route::post('/', [MemberController::class, 'store'])->name('store');
        Route::get('/{member}', [MemberController::class, 'show'])->name('show');
        Route::patch('/{member}', [MemberController::class, 'update'])->name('update');
        Route::delete('/{member}', [MemberController::class, 'destroy'])->name('destroy');
        Route::patch('/{member}/grant-access', [MemberStatusController::class, 'grantAccess'])->name('grant-access');
        Route::patch('/{member}/suspend-access', [MemberStatusController::class, 'suspendAccess'])->name('suspend-access');
        Route::patch('/{member}/revoke-access', [MemberStatusController::class, 'revokeAccess'])->name('revoke-access');
        Route::patch('/{member}/role', [MemberStatusController::class, 'updateRole'])->name('update-role');
    });

    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::patch('/{project}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');
        Route::post('/{project}/transition', [ProjectController::class, 'transition'])->name('transition');
        Route::put('/{project}/members', [ProjectController::class, 'syncMembers'])->name('members.sync');
        Route::put('/{project}/equipment-needs', [ProjectController::class, 'syncEquipmentNeeds'])->name('equipment-needs.sync');
    });

    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/', [ReservationController::class, 'index'])->name('index');
        Route::get('/availability', [ReservationController::class, 'availability'])->name('availability');
        Route::post('/', [ReservationController::class, 'store'])->name('store');
        Route::get('/{reservation}', [ReservationController::class, 'show'])->name('show');
        Route::patch('/{reservation}', [ReservationController::class, 'update'])->name('update');
        Route::post('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel');
        Route::post('/{reservation}/check-in', [ReservationController::class, 'checkIn'])->name('check-in');
        Route::post('/{reservation}/complete', [ReservationController::class, 'complete'])->name('complete');
        Route::post('/{reservation}/extend', [ReservationController::class, 'extend'])->name('extend');
    });

    Route::prefix('equipment')->name('equipment.')->group(function () {
        Route::get('/', [EquipmentController::class, 'index'])->name('index');
        Route::post('/import/preview', [EquipmentController::class, 'previewImport'])->name('import.preview');
        Route::post('/import', [EquipmentController::class, 'import'])->name('import');
    });

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', fn () => Inertia::render('inventory/pages/Index'))->name('index');
    });

    Route::prefix('zones')->name('zones.')->group(function () {
        Route::get('/', [ZoneController::class, 'index'])->name('index');
    });

    Route::prefix('spots')->name('spots.')->group(function () {
        Route::get('/', [SpotController::class, 'index'])->name('index');
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
