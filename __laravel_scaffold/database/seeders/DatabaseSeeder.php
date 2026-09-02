<?php

namespace Database\Seeders;

use App\Models\Member;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Member::factory(10)->create();

        Member::factory()->coordinator()->create([
            'full_name' => 'Test Coordinator',
            'email' => 'test@example.com',
        ]);
    }
}
