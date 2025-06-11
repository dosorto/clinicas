<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'root',
            'email' => 'root@example.com',
        ]);
        $this->call([
            RolesAndPermissionsSeeder::class
        ]);
        $user = User::find(1);
        $user->assignRole('root');
        
        $this->call([
            NacionalidadSeeder::class,
        ]);
    }
}
