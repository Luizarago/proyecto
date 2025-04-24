<?php

namespace Database\Factories;

use App\Models\Marca;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    public function definition()
    {
        return [
            'codigo' => $this->faker->unique()->ean13(), // Código de barras
            'nombre' => $this->faker->word(), // Ejemplo: "Televisor"
            'stock' => $this->faker->numberBetween(10, 100),
            'descripcion' => $this->faker->sentence(),
            'fecha_vencimiento' => $this->faker->optional()->date(),
            'img_path' => $this->faker->imageUrl(640, 480, 'products', true),
            'estado' => 1,
            'marca_id' => Marca::factory(), // Relación con marca
        ];
    }
}