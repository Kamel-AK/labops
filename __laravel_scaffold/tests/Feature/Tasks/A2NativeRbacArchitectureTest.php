<?php

use App\Enums\EquipmentType;
use App\Models\Equipment;
use App\Models\EquipmentCheckout;
use App\Models\Member;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;

test('a2 coordinator has full operational control over core entities', function () {
    $coordinator = Member::factory()->coordinator()->create();
    $member = Member::factory()->volunteer()->create();
    $project = Project::factory()->create();
    $durableEquipment = Equipment::factory()->create([
        'type' => EquipmentType::DURABLE->value,
    ]);
    $checkout = EquipmentCheckout::factory()
        ->forMember($member)
        ->forEquipment($durableEquipment)
        ->create();

    expect($coordinator->can('create', Member::class))->toBeTrue()
        ->and($coordinator->can('manageLifecycle', $member))->toBeTrue()
        ->and($coordinator->can('delete', $member))->toBeTrue()
        ->and($coordinator->can('update', $project))->toBeTrue()
        ->and($coordinator->can('create', Equipment::class))->toBeTrue()
        ->and($coordinator->can('update', $durableEquipment))->toBeTrue()
        ->and($coordinator->can('delete', $durableEquipment))->toBeTrue()
        ->and(Gate::forUser($coordinator)->allows('create-reservation-for', [$member, null]))->toBeTrue()
        ->and(Gate::forUser($coordinator)->allows('initiate-equipment-checkout', $durableEquipment))->toBeTrue()
        ->and(Gate::forUser($coordinator)->allows('check-in-equipment', $checkout))->toBeTrue();
});

test('a2 team lead permissions are scoped to active members and led projects', function () {
    $teamLead = Member::factory()->teamLead()->create();
    $otherLead = Member::factory()->teamLead()->create();
    $projectMember = Member::factory()->volunteer()->create();
    $outsider = Member::factory()->volunteer()->create();
    $inactiveMember = Member::factory()->pending()->create();
    $ledProject = Project::factory()->forLead($teamLead)->create();
    $otherProject = Project::factory()->forLead($otherLead)->create();
    $durableEquipment = Equipment::factory()->create([
        'type' => EquipmentType::DURABLE->value,
    ]);

    $ledProject->members()->attach($projectMember, ['role_in_project' => 'member']);

    expect($teamLead->can('viewAny', Member::class))->toBeTrue()
        ->and($teamLead->can('view', $inactiveMember))->toBeTrue()
        ->and($teamLead->can('manageLifecycle', $projectMember))->toBeFalse()
        ->and($teamLead->can('update', $ledProject))->toBeTrue()
        ->and($teamLead->can('update', $otherProject))->toBeFalse()
        ->and(Gate::forUser($teamLead)->allows('create-reservation-for', [$projectMember, $ledProject]))->toBeTrue()
        ->and(Gate::forUser($teamLead)->allows('create-reservation-for', [$outsider, $ledProject]))->toBeFalse()
        ->and(Gate::forUser($teamLead)->allows('create-reservation-for', [$inactiveMember, $ledProject]))->toBeFalse()
        ->and(Gate::forUser($teamLead)->allows('initiate-equipment-checkout', $durableEquipment))->toBeTrue();
});

test('a2 volunteer permissions are limited to self reservations and assigned equipment check in', function () {
    $volunteer = Member::factory()->volunteer()->create();
    $otherVolunteer = Member::factory()->volunteer()->create();
    $ownEquipment = Equipment::factory()->create([
        'type' => EquipmentType::DURABLE->value,
    ]);
    $otherEquipment = Equipment::factory()->create([
        'type' => EquipmentType::DURABLE->value,
    ]);
    $ownCheckout = EquipmentCheckout::factory()
        ->forMember($volunteer)
        ->forEquipment($ownEquipment)
        ->create();
    $otherCheckout = EquipmentCheckout::factory()
        ->forMember($otherVolunteer)
        ->forEquipment($otherEquipment)
        ->create();

    expect($volunteer->can('view', $volunteer))->toBeTrue()
        ->and($volunteer->can('viewAny', Member::class))->toBeFalse()
        ->and($volunteer->can('view', $otherVolunteer))->toBeFalse()
        ->and(Gate::forUser($volunteer)->allows('view-member-contact', $otherVolunteer))->toBeFalse()
        ->and(Gate::forUser($volunteer)->allows('create-reservation-for', [$volunteer, null]))->toBeTrue()
        ->and(Gate::forUser($volunteer)->allows('create-reservation-for', [$otherVolunteer, null]))->toBeFalse()
        ->and(Gate::forUser($volunteer)->allows('initiate-equipment-checkout', $ownEquipment))->toBeFalse()
        ->and(Gate::forUser($volunteer)->allows('check-in-equipment', $ownCheckout))->toBeTrue()
        ->and(Gate::forUser($volunteer)->allows('check-in-equipment', $otherCheckout))->toBeFalse();
});

test('a2 durable equipment checkout initiation excludes consumables', function () {
    $coordinator = Member::factory()->coordinator()->create();
    $teamLead = Member::factory()->teamLead()->create();
    $durableEquipment = Equipment::factory()->create([
        'type' => EquipmentType::DURABLE->value,
    ]);
    $consumableEquipment = Equipment::factory()->create([
        'type' => EquipmentType::CONSUMABLE->value,
    ]);

    expect(Gate::forUser($coordinator)->allows('initiate-equipment-checkout', $durableEquipment))->toBeTrue()
        ->and(Gate::forUser($teamLead)->allows('initiate-equipment-checkout', $durableEquipment))->toBeTrue()
        ->and(Gate::forUser($coordinator)->allows('initiate-equipment-checkout', $consumableEquipment))->toBeFalse()
        ->and(Gate::forUser($teamLead)->allows('initiate-equipment-checkout', $consumableEquipment))->toBeFalse();
});
