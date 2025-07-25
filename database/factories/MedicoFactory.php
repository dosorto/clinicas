<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medico>
 */
class MedicoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'persona_id'         => $this->faker->numberBetween(1, 20),
        'numero_colegiacion' => $this->faker->unique()->numerify('COL-#####'),
        
        ];
    }
}
