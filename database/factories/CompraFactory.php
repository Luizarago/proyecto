<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompraFactory extends Factory
{
    protected $model = \App\Models\Compra::class;

    public function definition()
    {
        return [
            'fecha_hora' => $this->faker->dateTime(),
            'impuesto' => $this->faker->randomFloat(2, 0, 20), // Impuesto entre 0% y 20%
            'numero_comprobante' => $this->faker->unique()->numerify('COMP-####'),
            'total' => $this->faker->randomFloat(2, 100, 1000), // Total entre 100 y 1000
            'estado' => 1, // Estado por defecto
            'comprobante_id' => null, // Puedes ajustar esto si necesitas relaciones
            'proveedore_id' => \App\Models\Proveedore::factory(), // Relación con proveedor
        ];
    }
}