<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Project;
use App\Models\Reservation;
use App\Services\AvailabilityService;
use App\Services\ReservationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ReservationService $reservations,
        private readonly AvailabilityService $availability,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Reservation::class);

        return Inertia::render('reservations/pages/Index', [
            'reservations' => Reservation::query()
                ->with(['spot:id,name', 'zone:id,name', 'project:id,name'])
                ->where(fn ($query) => $query->where('member_id', $request->user()->id)->orWhere('created_by', $request->user()->id))
                ->latest('start_time')
                ->get(),
        ]);
    }

    public function availability(Request $request)
    {
        $this->authorize('viewAny', Reservation::class);
        $data = $request->validate([
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date'],
            'zone_id' => ['nullable', 'integer', 'exists:zones,id'],
            'spot_id' => ['nullable', 'integer', 'exists:spots,id'],
        ]);

        return response()->json($this->availability->forInterval(
            $data['start_time'],
            $data['end_time'],
            $data['zone_id'] ?? null,
            $data['spot_id'] ?? null,
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Reservation::class);
        $data = $this->validateReservation($request);
        $this->authorizeForTarget($data);
        $this->reservations->create($request->user(), $data);

        return back()->with('success', 'Reservation created.');
    }

    public function show(Reservation $reservation): Response
    {
        $this->authorize('view', $reservation);

        return Inertia::render('reservations/pages/Index', [
            'reservation' => $reservation->load(['member', 'zone', 'spot', 'project', 'equipment']),
        ]);
    }

    public function update(Request $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);
        $data = array_replace(
            $reservation->only(['member_id', 'project_id', 'zone_id', 'spot_id', 'start_time', 'end_time', 'purpose']),
            $this->validateReservation($request, true),
        );
        $data['equipment_ids'] = $request->has('equipment_ids')
            ? $request->input('equipment_ids')
            : $reservation->equipment()->pluck('equipment.id')->all();
        $this->authorizeForTarget($data);
        $this->reservations->update($request->user(), $reservation, $data);

        return back()->with('success', 'Reservation updated.');
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        $this->authorize('cancel', $reservation);
        $this->reservations->cancel($request->user(), $reservation);

        return back()->with('success', 'Reservation cancelled.');
    }

    public function checkIn(Request $request, Reservation $reservation)
    {
        $this->authorize('checkIn', $reservation);
        $this->reservations->checkIn($request->user(), $reservation);

        return back()->with('success', 'Reservation checked in.');
    }

    public function complete(Request $request, Reservation $reservation)
    {
        $this->authorize('complete', $reservation);
        $this->reservations->complete($request->user(), $reservation);

        return back()->with('success', 'Reservation completed.');
    }

    public function extend(Request $request, Reservation $reservation)
    {
        $this->authorize('extend', $reservation);
        $data = $request->validate(['end_time' => ['required', 'date']]);
        $this->reservations->extend($request->user(), $reservation, $data['end_time']);

        return back()->with('success', 'Reservation extended.');
    }

    private function validateReservation(Request $request, bool $partial = false): array
    {
        $rule = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'member_id' => [$rule, 'integer', 'exists:members,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'zone_id' => [$rule, 'integer', 'exists:zones,id'],
            'spot_id' => [$rule, 'integer', 'exists:spots,id'],
            'start_time' => [$rule, 'date'],
            'end_time' => [$rule, 'date'],
            'purpose' => [$rule, 'string', 'max:255'],
            'equipment_ids' => ['sometimes', 'array'],
            'equipment_ids.*' => ['integer', 'distinct', 'exists:equipment,id'],
        ]);
    }

    private function authorizeForTarget(array $data): void
    {
        $target = Member::findOrFail($data['member_id']);
        $project = ($data['project_id'] ?? null) === null ? null : Project::findOrFail($data['project_id']);
        $this->authorize('create-reservation-for', [$target, $project]);
    }
}
