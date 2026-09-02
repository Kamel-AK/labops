<?php

namespace Database\Factories;

use App\Enums\CheckoutStatus;
use App\Enums\CheckoutType;
use App\Models\Equipment;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EquipmentCheckout>
 */
class EquipmentCheckoutFactory extends Factory
{
    public function definition(): array
    {
        return [
            'equipment_id' => Equipment::factory(),
            'member_id' => Member::factory(),
            'checked_out_at' => now(),
            'expected_return_at' => now()->addDay(),
            'actual_return_at' => null,
            'status' => CheckoutStatus::ACTIVE->value,
            'checkout_type' => CheckoutType::IN_LAB->value,
            'return_condition' => null,
        ];
    }

    public function forMember(Member $member): static
    {
        return $this->state(fn () => ['member_id' => $member->id]);
    }

    public function forEquipment(Equipment $equipment): static
    {
        return $this->state(fn () => ['equipment_id' => $equipment->id]);
    }
}
