<?php

namespace Database\Seeders;

use App\Models\Enfermedade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnfermedadeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Enfermedade::factory()->count(10)->create();
    }
}
