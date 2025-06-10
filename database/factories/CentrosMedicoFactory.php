<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Centros_Medico>
 */
class CentrosMedicoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_centro' => $this->faker->name(),
            'direccion' => $this->faker->name(),
            'telefono' => $this->faker->name(),
            'fotografia' => $this->faker->name(),
            'created_by' =>1,
        ];
    }
}
