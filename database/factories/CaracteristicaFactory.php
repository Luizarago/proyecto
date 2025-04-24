<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CaracteristicaFactory extends Factory
{
    public function definition()
    {
        return [
            'nombre' => $this->faker->unique()->word(), // Ejemplo: "Color"
            'descripcion' => $this->faker->sentence(), // Ejemplo: "Característica relacionada con el color del producto"
            'estado' => 1, // Estado activo
        ];
    }
}