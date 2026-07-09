<?php

namespace Database\Seeders;

use App\Models\Zone;
use App\Models\Spot;
use App\Enums\SpotType;
use App\Enums\SpotStatus;
use Illuminate\Database\Seeder;

class ZoneAndSpotSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Electronics',
                'description' => 'منطقة مخصصة لهندسة الإلكترونيات، اللحام، وأجهزة القياس والـ Oscilloscopes.',
                'color_code' => '#3b82f6', // أزرق
                'operating_hour_start' => '08:00:00',
                'operating_hour_end' => '20:00:00',
                'spots' => [
                    ['name' => 'Electronics Bench A', 'type' => SpotType::BENCH->value],
                    ['name' => 'Electronics Bench B', 'type' => SpotType::BENCH->value],
                    ['name' => 'Oscilloscope Station', 'type' => SpotType::MACHINE_STATION->value],
                ]
            ],
            [
                'name' => 'Digital Fabrication',
                'description' => 'منطقة التصنيع الرقمي والآلات، طابعات ثلاثية الأبعاد، وقواطع الليزر.',
                'color_code' => '#ef4444', // أحمر
                'operating_hour_start' => '10:00:00',
                'operating_hour_end' => '18:00:00',
                'spots' => [
                    ['name' => 'Prusa 3D Printer 1', 'type' => SpotType::MACHINE_STATION->value],
                    ['name' => 'Laser Cutter Workstation', 'type' => SpotType::MACHINE_STATION->value],
                ]
            ],
            [
                'name' => 'Programming',
                'description' => 'بيئة هادئة مخصصة لتطوير البرمجيات، الأكواد، ومراجعة الخوارزميات.',
                'color_code' => '#10b981', // أخضر
                'operating_hour_start' => '08:00:00',
                'operating_hour_end' => '22:00:00',
                'spots' => [
                    ['name' => 'Coding Desk 1', 'type' => SpotType::DESK->value],
                    ['name' => 'Coding Desk 2', 'type' => SpotType::DESK->value],
                    ['name' => 'Coding Desk 3', 'type' => SpotType::DESK->value],
                ]
            ],
            [
                'name' => 'Storage',
                'description' => 'مستودع الأدوات، الخزائن، والمواد المستهلكة المخصصة للمشاريع.',
                'color_code' => '#f59e0b', // برتقالي
                'operating_hour_start' => '08:00:00',
                'operating_hour_end' => '20:00:00',
                'spots' => [
                    ['name' => 'Inventory Management Desk', 'type' => SpotType::DESK->value],
                ]
            ],
            [
                'name' => 'Collaboration',
                'description' => 'منطقة العصف الذهني، الاجتماعات، والعمل الجماعي بين الفرق.',
                'color_code' => '#8b5cf6', // بنفسجي
                'operating_hour_start' => '08:00:00',
                'operating_hour_end' => '22:00:00',
                'spots' => [
                    ['name' => 'Meeting Table Alpha', 'type' => SpotType::DESK->value],
                    ['name' => 'Brainstorming Round Bench', 'type' => SpotType::BENCH->value],
                ]
            ],
        ];

        foreach ($zones as $zoneData) {
            $zone = Zone::create([
                'name' => $zoneData['name'],
                'description' => $zoneData['description'],
                'color_code' => $zoneData['color_code'],
                'operating_hour_start' => $zoneData['operating_hour_start'],
                'operating_hour_end' => $zoneData['operating_hour_end'],
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