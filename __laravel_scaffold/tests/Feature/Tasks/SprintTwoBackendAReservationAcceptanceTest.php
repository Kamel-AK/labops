<?php

use App\Enums\EquipmentStatus;
use App\Models\ActivityLog;
use App\Models\Equipment;
use App\Models\Member;
use App\Models\Project;
use App\Models\Reservation;
use App\Models\Spot;
use App\Models\Zone;
use App\Services\AvailabilityService;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

function backendAReservationLocation(string $name = 'Backend A Zone'): array
{
    $zone = Zone::create([
        'name' => $name,
        'status' => 'open',
        'operating_hours_start' => '08:00:00',
        'operating_hours_end' => '20:00:00',
    ]);
    $spot = Spot::create(['zone_id' => $zone->id, 'name' => "{$name} Spot", 'type' => 'bench', 'status' => 'active']);

    return [$zone, $spot];
}

function backendAReservationData(Member $member, Zone $zone, Spot $spot, array $overrides = []): array
{
    $start = now()->addDay()->setTime(10, 0);

    return array_replace([
        'member_id' => $member->id,
        'zone_id' => $zone->id,
        'spot_id' => $spot->id,
        'start_time' => $start,
        'end_time' => $start->copy()->addHour(),
        'purpose' => 'Reservation acceptance test',
    ], $overrides);
}

test('A6 atomically creates, edits, and cancels reservations while retaining an audit trail', function () {
    [$zone, $spot] = backendAReservationLocation();
    $member = Member::factory()->create();
    $service = app(ReservationService::class);
    $reservation = $service->create($member, backendAReservationData($member, $zone, $spot));

    $edited = $service->update($member, $reservation, backendAReservationData($member, $zone, $spot, ['purpose' => 'Edited purpose']));
    $cancelled = $service->cancel($member, $edited);

    expect($cancelled->status)->toBe('cancelled')
        ->and(Reservation::findOrFail($reservation->id)->purpose)->toBe('Edited purpose')
        ->and(ActivityLog::query()->where('entity_id', $reservation->id)->pluck('action')->all())
        ->toContain('reservation.created', 'reservation.updated', 'reservation.cancelled');
});

test('A6 rejects a conflicting edit and leaves the existing reservation unchanged', function () {
    [$zone, $spot] = backendAReservationLocation();
    $firstMember = Member::factory()->create();
    $secondMember = Member::factory()->create();
    $service = app(ReservationService::class);
    $first = $service->create($firstMember, backendAReservationData($firstMember, $zone, $spot));
    $secondStart = now()->addDay()->setTime(13, 0);
    $second = $service->create($secondMember, backendAReservationData($secondMember, $zone, $spot, [
        'start_time' => $secondStart,
        'end_time' => $secondStart->copy()->addHour(),
    ]));

    expect(fn () => $service->update($secondMember, $second, backendAReservationData($secondMember, $zone, $spot, [
        'start_time' => $first->start_time,
        'end_time' => $first->end_time,
    ])))->toThrow(ValidationException::class);

    expect($second->refresh()->start_time->toDateTimeString())->toBe($secondStart->toDateTimeString())
        ->and($second->status)->toBe('confirmed');
});

test('A7 enforces booking rules and role-aware reservation ownership', function () {
    [$zone, $spot] = backendAReservationLocation();
    $member = Member::factory()->create();
    $pending = Member::factory()->pending()->create();
    $lead = Member::factory()->teamLead()->create();
    $outsider = Member::factory()->create();
    $projectMember = Member::factory()->create();
    $project = Project::factory()->forLead($lead)->create(['status' => 'active']);
    $project->members()->attach($projectMember, ['role_in_project' => 'member', 'joined_at' => now()]);
    $service = app(ReservationService::class);

    expect(fn () => $service->create($pending, backendAReservationData($pending, $zone, $spot)))->toThrow(ValidationException::class);
    expect(fn () => $service->create($member, backendAReservationData($member, $zone, $spot, ['end_time' => now()->addDay()->setTime(15, 0)])))->toThrow(ValidationException::class);
    expect(fn () => $service->create($member, backendAReservationData($member, $zone, $spot, ['start_time' => now()->addDays(15)->setTime(10, 0), 'end_time' => now()->addDays(15)->setTime(11, 0)])))->toThrow(ValidationException::class);
    expect(Gate::forUser($lead)->allows('create-reservation-for', [$projectMember, $project]))->toBeTrue()
        ->and(Gate::forUser($lead)->allows('create-reservation-for', [$outsider, $project]))->toBeFalse();
});

test('A7 blocks overlap and a second active reservation for a member', function () {
    [$zone, $spot] = backendAReservationLocation();
    $member = Member::factory()->create();
    $other = Member::factory()->create();
    $service = app(ReservationService::class);
    $first = $service->create($member, backendAReservationData($member, $zone, $spot));

    expect(fn () => $service->create($other, backendAReservationData($other, $zone, $spot, [
        'start_time' => $first->start_time->copy()->addMinutes(15),
        'end_time' => $first->end_time->copy()->addMinutes(15),
    ])))->toThrow(ValidationException::class);
    expect(fn () => $service->create($member, backendAReservationData($member, $zone, $spot, [
        'start_time' => $first->end_time->copy()->addHour(),
        'end_time' => $first->end_time->copy()->addHours(2),
    ])))->toThrow(ValidationException::class);
});

test('A8 permits only confirmed to checked-in to completed transitions and keeps extension checked in', function () {
    [$zone, $spot] = backendAReservationLocation();
    $member = Member::factory()->create();
    $service = app(ReservationService::class);
    $reservation = $service->create($member, backendAReservationData($member, $zone, $spot));

    expect(fn () => $service->complete($member, $reservation))->toThrow(ValidationException::class);
    $checkedIn = $service->checkIn($member, $reservation);
    $extended = $service->extend($member, $checkedIn, $checkedIn->start_time->copy()->addHours(2)->toDateTimeString());

    expect($extended->status)->toBe('checked_in')
        ->and($extended->checked_in_at)->not->toBeNull()
        ->and($extended->end_time->equalTo($checkedIn->start_time->copy()->addHours(2)))->toBeTrue();

    $completed = $service->complete($member, $extended);
    expect($completed->status)->toBe('completed')->and($completed->checked_out_at)->not->toBeNull();
});

test('A9 links only active project members and non-retired equipment without reserving equipment inventory', function () {
    [$zone, $spot] = backendAReservationLocation();
    $lead = Member::factory()->teamLead()->create();
    $member = Member::factory()->create();
    $project = Project::factory()->forLead($lead)->create(['status' => 'active']);
    $project->members()->attach($member, ['role_in_project' => 'member', 'joined_at' => now()]);
    $neededEquipment = Equipment::factory()->create();
    $project->equipmentNeeds()->attach($neededEquipment, ['quantity_needed' => 1]);
    $reservation = app(ReservationService::class)->create($member, backendAReservationData($member, $zone, $spot, [
        'project_id' => $project->id,
        'equipment_ids' => [$neededEquipment->id],
    ]));

    expect($reservation->project_id)->toBe($project->id)
        ->and($reservation->equipment()->pluck('equipment.id')->all())->toBe([$neededEquipment->id])
        ->and($neededEquipment->refresh()->status)->toBe(EquipmentStatus::AVAILABLE)
        ->and($project->equipmentNeeds()->whereKey($neededEquipment->id)->exists())->toBeTrue();
});

test('A10 reports availability by zone, spot, and requested date range without exposing inactive capacity', function () {
    [$zone, $bookedSpot] = backendAReservationLocation();
    $availableSpot = Spot::create(['zone_id' => $zone->id, 'name' => 'A10 available', 'type' => 'bench', 'status' => 'active']);
    $inactiveSpot = Spot::create(['zone_id' => $zone->id, 'name' => 'A10 inactive', 'type' => 'bench', 'status' => 'inactive']);
    $member = Member::factory()->create();
    $payload = backendAReservationData($member, $zone, $bookedSpot);
    app(ReservationService::class)->create($member, $payload);

    $availability = app(AvailabilityService::class)->forInterval($payload['start_time'], $payload['end_time'], $zone->id);

    expect($availability->firstWhere('spot_id', $bookedSpot->id)['available'])->toBeFalse()
        ->and($availability->firstWhere('spot_id', $availableSpot->id)['available'])->toBeTrue()
        ->and($availability->pluck('spot_id')->all())->not->toContain($inactiveSpot->id);
});
