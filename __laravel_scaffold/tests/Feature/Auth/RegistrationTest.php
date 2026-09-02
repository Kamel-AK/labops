<?php

test('public registration screen is not available', function () {
    $this->get('/register')->assertNotFound();
});

test('public registration submission is not available', function () {
    $this->post('/register', [
        'full_name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    $this->assertGuest();
});
