<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Member::factory()->coordinator()->create([
            'full_name' => 'Test Coordinator',
            'email' => 'test@example.com',
            'access_status' => 'granted',
        ]);

        $this->call([
            ZoneAndSpotSeeder::class,
        ]);
    }
}
