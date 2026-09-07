<?php

namespace Database\Factories;

use App\Enums\EquipmentStatus;
use App\Enums\EquipmentType;
use App\Models\EquipmentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipment>
 */
class EquipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'category_id' => EquipmentCategory::factory(),
            'subcategory' => fake()->word(),
            'asset_tag' => fake()->unique()->bothify('LAB-####'),
            'type' => EquipmentType::DURABLE->value,
            'status' => EquipmentStatus::AVAILABLE->value,
            'quantity_total' => 1,
            'quantity_available' => 1,
            'min_stock_threshold' => 0,
            'allow_borrow' => false,
            'max_borrow_days' => 0,
        ];
    }
}
