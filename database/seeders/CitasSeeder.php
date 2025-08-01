<?php

namespace Database\Seeders;

use App\Models\Citas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Citas::factory()->count(10)->create();
    }
}
