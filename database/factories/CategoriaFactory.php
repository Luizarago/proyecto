<?php

namespace Database\Factories;

use App\Models\Caracteristica;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaFactory extends Factory
{
    public function definition()
    {
        return [
            'caracteristica_id' => Caracteristica::factory(), // Relación con caracteristicas
        ];
    }
}