<?php

use App\Models\Member;
use Illuminate\Support\Facades\Hash;

test('password can be updated', function () {
    $member = Member::factory()->create();

    $response = $this
        ->actingAs($member)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'New-password-123!',
            'password_confirmation' => 'New-password-123!',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertTrue(Hash::check('New-password-123!', $member->refresh()->password_hash));
});

test('correct password must be provided to update password', function () {
    $member = Member::factory()->create();

    $response = $this
        ->actingAs($member)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect('/profile');
});
