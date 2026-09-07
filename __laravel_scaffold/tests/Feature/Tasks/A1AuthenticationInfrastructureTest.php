<?php

use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

test('a1 members table contains required account lifecycle fields only', function () {
    expect(Schema::hasColumns('members', [
        'full_name',
        'email',
        'phone',
        'password_hash',
        'access_status',
        'skills',
        'certifications',
        'emergency_contact',
        'join_date',
        'deleted_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumn('members', 'telegram_chat_id'))->toBeFalse();
});

test('a1 member accounts authenticate through password hash and hash plain assignments', function () {
    $member = Member::factory()->create([
        'password_hash' => 'Plain-password-123!',
        'skills' => ['microscopy', 'inventory'],
        'certifications' => [['name' => 'Lab Safety']],
    ]);

    expect($member->getAuthPasswordName())->toBe('password_hash')
        ->and(Hash::check('Plain-password-123!', $member->password_hash))->toBeTrue()
        ->and($member->password_hash)->not->toBe('Plain-password-123!')
        ->and($member->skills)->toBe(['microscopy', 'inventory'])
        ->and($member->certifications)->toBe([['name' => 'Lab Safety']]);
});

test('a1 only granted members can log in and sessions are cleared on logout', function () {
    $granted = Member::factory()->create();
    $pending = Member::factory()->pending()->create();

    $this->post('/login', [
        'email' => $granted->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($granted);

    $this->post('/logout')->assertRedirect('/');
    $this->assertGuest();

    $this->post('/login', [
        'email' => $pending->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('a1 public registration is disabled and coordinators create member accounts', function () {
    $coordinator = Member::factory()->coordinator()->create();

    $this->get('/register')->assertNotFound();
    $this->post('/register', [])->assertNotFound();

    $this->actingAs($coordinator)->post(route('members.store'), [
        'full_name' => 'Created Volunteer',
        'email' => 'created-volunteer@example.com',
        'password' => 'A-secure-password-123',
        'password_confirmation' => 'A-secure-password-123',
        'phone' => '555-0191',
        'role' => 'volunteer',
        'access_status' => 'pending',
        'skills' => ['electronics'],
        'certifications' => [['name' => 'Safety Orientation']],
        'emergency_contact' => '555-0100',
        'join_date' => '2026-09-01',
    ])->assertSessionHasNoErrors();

    $member = Member::where('email', 'created-volunteer@example.com')->firstOrFail();

    expect(Hash::check('A-secure-password-123', $member->password_hash))->toBeTrue()
        ->and($member->access_status)->toBe('pending')
        ->and($member->skills)->toBe(['electronics'])
        ->and($member->certifications)->toBe([['name' => 'Safety Orientation']]);
});

test('a1 inactive authenticated accounts are removed from the session', function () {
    $member = Member::factory()->suspended()->create();

    $this->actingAs($member)->get(route('dashboard'))
        ->assertRedirect(route('login', absolute: false));

    $this->assertGuest();
});
