<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create();
        Shipment::factory(10)->create();
    }
}
