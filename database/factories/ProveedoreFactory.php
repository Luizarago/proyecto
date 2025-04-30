<?php

namespace Database\Factories;

use App\Models\Proveedore;
use App\Models\Persona; // Importar el modelo Persona
use Illuminate\Database\Eloquent\Factories\Factory;

class ProveedoreFactory extends Factory
{
    protected $model = Proveedore::class;

    public function definition()
    {
        return [
            'persona_id' => Persona::factory(), // Crear una persona asociada automáticamente
        ];
    }
}