<?php

use App\Models\Member;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('granted members can authenticate using the login screen', function () {
    $member = Member::factory()->create();

    $response = $this->post('/login', [
        'email' => $member->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($member);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('pending members cannot authenticate', function () {
    $member = Member::factory()->pending()->create();

    $this->post('/login', [
        'email' => $member->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('members can not authenticate with invalid password', function () {
    $member = Member::factory()->create();

    $this->post('/login', [
        'email' => $member->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('members can logout', function () {
    $member = Member::factory()->create();

    $response = $this->actingAs($member)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
