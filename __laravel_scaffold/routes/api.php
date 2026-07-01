<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('dashboard')->name('api.dashboard.')->group(function () {
        //
    });

    Route::prefix('members')->name('api.members.')->group(function () {
        //
    });

    Route::prefix('projects')->name('api.projects.')->group(function () {
        //
    });

    Route::prefix('reservations')->name('api.reservations.')->group(function () {
        //
    });

    Route::prefix('equipment')->name('api.equipment.')->group(function () {
        //
    });

    Route::prefix('inventory')->name('api.inventory.')->group(function () {
        //
    });

    Route::prefix('zones')->name('api.zones.')->group(function () {
        //
    });

    Route::prefix('spots')->name('api.spots.')->group(function () {
        //
    });

    Route::prefix('notifications')->name('api.notifications.')->group(function () {
        //
    });

    Route::prefix('activity-log')->name('api.activity-log.')->group(function () {
        //
    });

    Route::prefix('settings')->name('api.settings.')->group(function () {
        //
    });
});
