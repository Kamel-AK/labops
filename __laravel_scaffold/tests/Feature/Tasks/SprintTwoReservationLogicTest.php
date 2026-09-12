<?php

use App\Enums\EquipmentStatus;
use App\Models\ActivityLog;
use App\Models\Equipment;
use App\Models\Member;
use App\Models\Project;
use App\Models\Spot;
use App\Models\Zone;
use App\Services\AvailabilityService;
use App\Services\ReservationService;
use Illuminate\Validation\ValidationException;

function reservableLocation(string $name = 'Reservation Zone'): array
{
    $zone = Zone::create([
        'name' => $name,
        'status' => 'open',
        'operating_hours_start' => '08:00:00',
        'operating_hours_end' => '20:00:00',
    ]);
    $spot = Spot::create(['zone_id' => $zone->id, 'name' => "{$name} Bench", 'type' => 'bench', 'status' => 'active']);

    return [$zone, $spot];
}

function reservationPayload(Member $member, Zone $zone, Spot $spot, array $overrides = []): array
{
    $start = now()->addDay()->setTime(10, 0);

    return array_merge([
        'member_id' => $member->id,
        'zone_id' => $zone->id,
        'spot_id' => $spot->id,
        'start_time' => $start,
        'end_time' => $start->copy()->addHour(),
        'purpose' => 'Prototype work',
    ], $overrides);
}

test('reservation rules enforce access, operating hours, duration, advance window, and location state', function () {
    [$zone, $spot] = reservableLocation();
    $member = Member::factory()->create();
    $pending = Member::factory()->pending()->create();
    $service = app(ReservationService::class);

    expect(fn () => $service->create($pending, reservationPayload($pending, $zone, $spot)))->toThrow(ValidationException::class);
    expect(fn () => $service->create($member, reservationPayload($member, $zone, $spot, ['start_time' => now()->addDay()->setTime(7, 0), 'end_time' => now()->addDay()->setTime(8, 30)])))->toThrow(ValidationException::class);
    expect(fn () => $service->create($member, reservationPayload($member, $zone, $spot, ['end_time' => now()->addDay()->setTime(15, 0)])))->toThrow(ValidationException::class);
    expect(fn () => $service->create($member, reservationPayload($member, $zone, $spot, ['start_time' => now()->addDays(15)->setTime(10, 0), 'end_time' => now()->addDays(15)->setTime(11, 0)])))->toThrow(ValidationException::class);

    $spot->update(['status' => 'maintenance']);
    expect(fn () => $service->create($member, reservationPayload($member, $zone, $spot)))->toThrow(ValidationException::class);
});

test('reservation lifecycle is atomic, auditable, and supports valid extension and completion', function () {
    [$zone, $spot] = reservableLocation();
    $member = Member::factory()->create();
    $lead = Member::factory()->teamLead()->create();
    $project = Project::factory()->forLead($lead)->create(['status' => 'active']);
    $project->members()->attach($member, ['role_in_project' => 'member', 'joined_at' => now()]);
    $equipment = Equipment::factory()->create();
    $service = app(ReservationService::class);
    $reservation = $service->create($member, reservationPayload($member, $zone, $spot, [
        'project_id' => $project->id,
        'equipment_ids' => [$equipment->id],
    ]));

    expect($reservation->status)->toBe('confirmed')
        ->and($reservation->equipment()->pluck('equipment.id')->all())->toBe([$equipment->id]);

    $service->checkIn($member, $reservation);
    $extended = $service->extend($member, $reservation->refresh(), $reservation->start_time->copy()->addHours(2)->toDateTimeString());
    $completed = $service->complete($member, $extended);

    expect($completed->status)->toBe('completed')
        ->and($completed->checked_in_at)->not->toBeNull()
        ->and($completed->checked_out_at)->not->toBeNull()
        ->and(ActivityLog::query()->where('entity_id', $reservation->id)->pluck('action')->all())
        ->toContain('reservation.created', 'reservation.checked_in', 'reservation.extended', 'reservation.completed');
});

test('retired equipment and non-member project links are rejected without changing equipment state', function () {
    [$zone, $spot] = reservableLocation();
    $member = Member::factory()->create();
    $lead = Member::factory()->teamLead()->create();
    $project = Project::factory()->forLead($lead)->create(['status' => 'active']);
    $retired = Equipment::factory()->create(['status' => EquipmentStatus::RETIRED->value]);
    $service = app(ReservationService::class);

    expect(fn () => $service->create($member, reservationPayload($member, $zone, $spot, ['project_id' => $project->id])))->toThrow(ValidationException::class);
    expect(fn () => $service->create($member, reservationPayload($member, $zone, $spot, ['equipment_ids' => [$retired->id]])))->toThrow(ValidationException::class);
    expect($retired->refresh()->status)->toBe(EquipmentStatus::RETIRED);
});

test('availability only returns active spots in open zones and marks overlapping intervals unavailable', function () {
    [$zone, $spot] = reservableLocation();
    $otherSpot = Spot::create(['zone_id' => $zone->id, 'name' => 'Available Bench', 'type' => 'bench', 'status' => 'active']);
    $inactiveSpot = Spot::create(['zone_id' => $zone->id, 'name' => 'Hidden Bench', 'type' => 'bench', 'status' => 'inactive']);
    $member = Member::factory()->create();
    $payload = reservationPayload($member, $zone, $spot);
    app(ReservationService::class)->create($member, $payload);

    $availability = app(AvailabilityService::class)->forInterval($payload['start_time'], $payload['end_time'], $zone->id);

    expect($availability->pluck('spot_id')->all())->toContain($spot->id, $otherSpot->id)
        ->not->toContain($inactiveSpot->id)
        ->and($availability->firstWhere('spot_id', $spot->id)['available'])->toBeFalse()
        ->and($availability->firstWhere('spot_id', $otherSpot->id)['available'])->toBeTrue();

    $outsideHours = app(AvailabilityService::class)->forInterval(
        now()->addDay()->setTime(7, 0),
        now()->addDay()->setTime(8, 30),
        $zone->id,
        $otherSpot->id,
    );
    expect($outsideHours->first()['available'])->toBeFalse();
});

test('confirmed reservations can be edited and cancelled with an audit trail', function () {
    [$zone, $spot] = reservableLocation();
    $member = Member::factory()->create();
    $service = app(ReservationService::class);
    $reservation = $service->create($member, reservationPayload($member, $zone, $spot));

    $updated = $service->update($member, $reservation, reservationPayload($member, $zone, $spot, ['purpose' => 'Updated prototype work']));
    $cancelled = $service->cancel($member, $updated);

    expect($updated->purpose)->toBe('Updated prototype work')
        ->and($cancelled->status)->toBe('cancelled')
        ->and(ActivityLog::query()->where('entity_id', $reservation->id)->pluck('action')->all())
        ->toContain('reservation.updated', 'reservation.cancelled');
});

test('reservation routes enforce self-only volunteer booking and expose the authenticated availability query', function () {
    [$zone, $spot] = reservableLocation();
    $volunteer = Member::factory()->create();
    $other = Member::factory()->create();
    $payload = reservationPayload($other, $zone, $spot);

    $this->actingAs($volunteer)->post(route('reservations.store'), $payload)->assertForbidden();
    $this->actingAs($volunteer)->getJson(route('reservations.availability', [
        'start_time' => $payload['start_time']->toDateTimeString(),
        'end_time' => $payload['end_time']->toDateTimeString(),
        'zone_id' => $zone->id,
    ]))->assertOk()->assertJsonPath('0.spot_id', $spot->id)->assertJsonPath('0.available', true);
});
