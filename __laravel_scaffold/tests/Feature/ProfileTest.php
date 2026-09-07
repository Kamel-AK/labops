<?php

use App\Models\Member;

test('profile page is displayed', function () {
    $member = Member::factory()->create();

    $response = $this
        ->actingAs($member)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $member = Member::factory()->create();

    $response = $this
        ->actingAs($member)
        ->patch('/profile', [
            'full_name' => 'Test Member',
            'email' => 'test@example.com',
            'phone' => '555-0101',
            'skills' => ['soldering'],
            'certifications' => [['name' => 'Safety']],
            'emergency_contact' => '555-0199',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $member->refresh();

    $this->assertSame('Test Member', $member->full_name);
    $this->assertSame('test@example.com', $member->email);
    $this->assertSame('555-0101', $member->phone);
    $this->assertSame(['soldering'], $member->skills);
});

test('member can soft delete their account', function () {
    $member = Member::factory()->create();

    $response = $this
        ->actingAs($member)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNotNull($member->fresh()->deleted_at);
});

test('correct password must be provided to delete account', function () {
    $member = Member::factory()->create();

    $response = $this
        ->actingAs($member)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect('/profile');

    $this->assertNull($member->fresh()->deleted_at);
});
