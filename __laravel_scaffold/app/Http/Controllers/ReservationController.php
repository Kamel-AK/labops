<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Project;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ReservationService $reservations) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Reservation::class);

        return Inertia::render('reservations/pages/Index');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Reservation::class);

        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'zone_id' => ['required', 'exists:zones,id'],
            'spot_id' => ['required', 'exists:spots,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date'],
            'purpose' => ['required', 'string', 'max:255'],
        ]);
        $target = Member::findOrFail($data['member_id']);
        $project = isset($data['project_id']) ? Project::findOrFail($data['project_id']) : null;
        $this->authorize('create-reservation-for', [$target, $project]);
        $this->reservations->create($request->user(), $data);

        return back()->with('success', 'Reservation created.');
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
