<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enfermedade>
 */
class EnfermedadeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
            'enfermedades' => $this->faker->randomElement([
                'Diabetes',
                'Hipertensión',
                'Asma',
                'Epilepsia',
                'Gastritis',
                'Artritis',
                'COVID-19',
                'Migraña',
                'Anemia',
                'Colesterol alto',
                 'created_by' => 1,
            ]),
             // Usuario por defecto (ajústalo según tu app)
        ];
    }
}
