<?php

use App\Models\Equipment;
use App\Models\EquipmentCheckout;
use App\Models\Member;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

test('coordinator has member account lifecycle control', function () {
    $coordinator = Member::factory()->coordinator()->create();
    $volunteer = Member::factory()->volunteer()->create();

    expect($coordinator->can('create', Member::class))->toBeTrue()
        ->and($coordinator->can('manageLifecycle', $volunteer))->toBeTrue()
        ->and($coordinator->can('delete', $volunteer))->toBeTrue();
});

test('coordinator can create member accounts with hashed passwords', function () {
    $coordinator = Member::factory()->coordinator()->create();

    $response = $this->actingAs($coordinator)->post(route('members.store'), [
        'full_name' => 'New Volunteer',
        'email' => 'new-volunteer@example.com',
        'password' => 'A-secure-password-123',
        'password_confirmation' => 'A-secure-password-123',
        'phone' => '555-0191',
        'role' => 'volunteer',
        'access_status' => 'pending',
        'skills' => ['electronics'],
        'certifications' => [['name' => 'Safety']],
        'emergency_contact' => '555-0110',
        'join_date' => '2026-09-01',
    ]);

    $response->assertSessionHasNoErrors();

    $member = Member::where('email', 'new-volunteer@example.com')->first();

    expect($member)->not->toBeNull()
        ->and(Hash::check('A-secure-password-123', $member->password_hash))->toBeTrue()
        ->and($member->password_hash)->not->toBe('A-secure-password-123')
        ->and($member->skills)->toBe(['electronics']);
});

test('volunteer cannot create member accounts', function () {
    $volunteer = Member::factory()->volunteer()->create();

    $this->actingAs($volunteer)->post(route('members.store'), [
        'full_name' => 'Blocked Member',
        'email' => 'blocked@example.com',
        'password' => 'A-secure-password-123',
        'password_confirmation' => 'A-secure-password-123',
        'role' => 'volunteer',
        'access_status' => 'pending',
    ])->assertForbidden();
});

test('team lead can view members but cannot manage account lifecycle', function () {
    $teamLead = Member::factory()->teamLead()->create();
    $volunteer = Member::factory()->volunteer()->create();

    expect($teamLead->can('viewAny', Member::class))->toBeTrue()
        ->and($teamLead->can('manageLifecycle', $volunteer))->toBeFalse()
        ->and($teamLead->can('delete', $volunteer))->toBeFalse();
});

test('volunteer can view own profile but not member contact details', function () {
    $volunteer = Member::factory()->volunteer()->create();
    $other = Member::factory()->volunteer()->create();

    expect($volunteer->can('view', $volunteer))->toBeTrue()
        ->and($volunteer->can('viewAny', Member::class))->toBeFalse()
        ->and($volunteer->can('view', $other))->toBeFalse()
        ->and(Gate::forUser($volunteer)->allows('view-member-contact', $other))->toBeFalse();
});

test('team lead can manage projects they lead', function () {
    $teamLead = Member::factory()->teamLead()->create();
    $otherLead = Member::factory()->teamLead()->create();
    $project = Project::factory()->forLead($teamLead)->create();

    expect($teamLead->can('update', $project))->toBeTrue()
        ->and($otherLead->can('update', $project))->toBeFalse();
});

test('reservation creation rules allow coordinator, self, and project lead for project members', function () {
    $coordinator = Member::factory()->coordinator()->create();
    $teamLead = Member::factory()->teamLead()->create();
    $volunteer = Member::factory()->volunteer()->create();
    $outsider = Member::factory()->volunteer()->create();
    $project = Project::factory()->forLead($teamLead)->create();

    $project->members()->attach($volunteer, ['role_in_project' => 'member']);

    expect(Gate::forUser($coordinator)->allows('create-reservation-for', [$volunteer, null]))->toBeTrue()
        ->and(Gate::forUser($volunteer)->allows('create-reservation-for', [$volunteer, null]))->toBeTrue()
        ->and(Gate::forUser($teamLead)->allows('create-reservation-for', [$volunteer, $project]))->toBeTrue()
        ->and(Gate::forUser($teamLead)->allows('create-reservation-for', [$outsider, $project]))->toBeFalse();
});

test('checkout rules allow coordinators and team leads to initiate and custodian to check in', function () {
    $coordinator = Member::factory()->coordinator()->create();
    $teamLead = Member::factory()->teamLead()->create();
    $volunteer = Member::factory()->volunteer()->create();
    $equipment = Equipment::factory()->create();
    $checkout = EquipmentCheckout::factory()->forMember($volunteer)->forEquipment($equipment)->create();

    expect(Gate::forUser($coordinator)->allows('initiate-equipment-checkout', $equipment))->toBeTrue()
        ->and(Gate::forUser($teamLead)->allows('initiate-equipment-checkout', $equipment))->toBeTrue()
        ->and(Gate::forUser($volunteer)->allows('initiate-equipment-checkout', $equipment))->toBeFalse()
        ->and(Gate::forUser($volunteer)->allows('check-in-equipment', $checkout))->toBeTrue();
});
