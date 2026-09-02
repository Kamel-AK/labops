<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password_hash' => static::$password ??= Hash::make('password'),
            'phone' => fake()->phoneNumber(),
            'role' => 'volunteer',
            'access_status' => 'granted',
            'skills' => [],
            'certifications' => [],
            'emergency_contact' => fake()->phoneNumber(),
            'join_date' => now()->toDateString(),
            'remember_token' => Str::random(10),
        ];
    }

    public function coordinator(): static
    {
        return $this->state(fn () => ['role' => 'coordinator']);
    }

    public function teamLead(): static
    {
        return $this->state(fn () => ['role' => 'team_lead']);
    }

    public function volunteer(): static
    {
        return $this->state(fn () => ['role' => 'volunteer']);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['access_status' => 'pending']);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['access_status' => 'suspended']);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
