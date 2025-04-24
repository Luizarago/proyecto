<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Caracteristica;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Proveedor;

class PoblarBaseDeDatosSeeder extends Seeder
{
    public function run()
    {
        // Crear características
        Caracteristica::factory(5)->create()->each(function ($caracteristica) {
            // Crear categorías asociadas a cada característica
            Categoria::factory(1)->create(['caracteristica_id' => $caracteristica->id])->each(function ($categoria) {
                // Crear marcas asociadas a cada categoría
                Marca::factory(3)->create(['caracteristica_id' => $categoria->caracteristica_id])->each(function ($marca) {
                    // Crear productos asociados a cada marca
                    Producto::factory(10)->create(['marca_id' => $marca->id]);
                });
            });
        });

        // Crear proveedores
        Proveedor::factory(10)->create();
    }
}