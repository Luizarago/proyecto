<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Documento; // Asegúrate de importar el modelo Documento

class PersonaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'razon_social' => $this->faker->company,
            'direccion' => $this->faker->address,
            'tipo_persona' => $this->faker->randomElement(['Jurídica', 'Natural']),
            'estado' => $this->faker->boolean,
            'documento_id' => Documento::inRandomOrder()->value('id'),
            'numero_documento' => $this->faker->unique()->numerify('##########'), 
        ];
    }
}