<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    use AuthorizesRequests;

    public function index(): Response
    {
        $this->authorize('viewAny', Reservation::class);

        return Inertia::render('reservations/pages/Index');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Reservation::class);
    }

    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);
    }

    public function update(Request $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorize('cancel', $reservation);
    }
}
