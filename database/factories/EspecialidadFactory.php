<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Especialidad>
 */
class EspecialidadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'especialidad' => $this->faker->jobTitle(),
        'created_at'   => now(),
        'updated_at'   => now(),
        'deleted_at'   => null,
        'created_by'   => 1,
        'updated_by'   => null,
        'deleted_by'   => null,
            
        ];
    }
}
