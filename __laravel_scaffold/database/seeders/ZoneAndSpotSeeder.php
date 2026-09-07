<?php

namespace Database\Seeders;

use App\Enums\SpotStatus;
use App\Enums\SpotType;
use App\Models\Spot;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneAndSpotSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Electronics',
                'description' => 'Electronics engineering, soldering, and measurement benches.',
                'color_code' => '#3b82f6',
                'operating_hours_start' => '08:00:00',
                'operating_hours_end' => '20:00:00',
                'spots' => [
                    ['name' => 'Electronics Bench A', 'type' => SpotType::BENCH->value],
                    ['name' => 'Electronics Bench B', 'type' => SpotType::BENCH->value],
                    ['name' => 'Oscilloscope Station', 'type' => SpotType::MACHINE->value],
                ],
            ],
            [
                'name' => 'Digital Fabrication',
                'description' => 'Digital fabrication machines, 3D printers, and laser cutters.',
                'color_code' => '#ef4444',
                'operating_hours_start' => '10:00:00',
                'operating_hours_end' => '18:00:00',
                'spots' => [
                    ['name' => 'Prusa 3D Printer 1', 'type' => SpotType::MACHINE->value],
                    ['name' => 'Laser Cutter Workstation', 'type' => SpotType::MACHINE->value],
                ],
            ],
            [
                'name' => 'Programming',
                'description' => 'Quiet area for software development and code review.',
                'color_code' => '#10b981',
                'operating_hours_start' => '08:00:00',
                'operating_hours_end' => '22:00:00',
                'spots' => [
                    ['name' => 'Coding Desk 1', 'type' => SpotType::DESK->value],
                    ['name' => 'Coding Desk 2', 'type' => SpotType::DESK->value],
                    ['name' => 'Coding Desk 3', 'type' => SpotType::DESK->value],
                ],
            ],
            [
                'name' => 'Storage',
                'description' => 'Tool, cabinet, and consumable storage for projects.',
                'color_code' => '#f59e0b',
                'operating_hours_start' => '08:00:00',
                'operating_hours_end' => '20:00:00',
                'spots' => [
                    ['name' => 'Inventory Management Desk', 'type' => SpotType::DESK->value],
                ],
            ],
            [
                'name' => 'Collaboration',
                'description' => 'Meeting, brainstorming, and team collaboration area.',
                'color_code' => '#8b5cf6',
                'operating_hours_start' => '08:00:00',
                'operating_hours_end' => '22:00:00',
                'spots' => [
                    ['name' => 'Meeting Table Alpha', 'type' => SpotType::SHARED_TABLE->value],
                    ['name' => 'Brainstorming Bench', 'type' => SpotType::BENCH->value],
                ],
            ],
        ];

        foreach ($zones as $zoneData) {
            $zone = Zone::create([
                'name' => $zoneData['name'],
                'description' => $zoneData['description'],
                'color_code' => $zoneData['color_code'],
                'operating_hours_start' => $zoneData['operating_hours_start'],
                'operating_hours_end' => $zoneData['operating_hours_end'],
            ]);

            foreach ($zoneData['spots'] as $spotData) {
                Spot::create([
                    'zone_id' => $zone->id,
                    'name' => $spotData['name'],
                    'type' => $spotData['type'],
                    'status' => SpotStatus::ACTIVE->value,
                ]);
            }
        }
    }
}
