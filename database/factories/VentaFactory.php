<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Comprobante;

class VentaFactory extends Factory
{
    public function definition()
    {
        return [
            'fecha_hora' => $this->faker->dateTime(),
            'numero_ticket' => $this->faker->unique()->numerify('TICKET-#####'),
            'impuesto' => $this->faker->randomFloat(2, 0, 20), // Impuesto entre 0% y 20%
            'numero_comprobante' => $this->faker->unique()->numerify('COMP-#####'),
            'total' => $this->faker->randomFloat(2, 100, 1000), // Total entre 100 y 1000
            'estado' => 1,
            'cliente_id' => Cliente::inRandomOrder()->value('id'), // Seleccionar un cliente existente o null
            'user_id' => User::inRandomOrder()->value('id'), // Seleccionar un usuario existente o null
            'comprobante_id' => Comprobante::inRandomOrder()->value('id'), // Seleccionar un comprobante existente o null
        ];
    }
}