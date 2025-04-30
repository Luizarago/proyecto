<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Caracteristica;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\Proveedore;
use App\Models\Documento;
use App\Models\Venta;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Comprobante;

class PoblarBaseDeDatosSeeder extends Seeder
{
    public function run()
    {
        // Crear características
        Caracteristica::factory(5)->create()->each(function ($caracteristica) {
            // Crear categorías asociadas a cada característica
            Categoria::factory(2)->create(['caracteristica_id' => $caracteristica->id])->each(function ($categoria) {
                // Crear marcas asociadas a cada categoría
                Marca::factory(3)->create(['caracteristica_id' => $categoria->caracteristica_id])->each(function ($marca) {
                    // Crear productos asociados a cada marca
                    Producto::factory(10)->create(['marca_id' => $marca->id]);
                });
            });
        });

        // Crear personas y asociar un proveedor a cada persona
        Persona::factory(10)->create()->each(function ($persona) {
            // Asegurar que cada persona tenga un documento válido
            $persona->update([
                'documento_id' => Documento::inRandomOrder()->value('id'), // Seleccionar un documento existente
            ]);

            // Asociar proveedor a la persona
            Proveedore::factory()->create([
                'persona_id' => $persona->id, // Asociar proveedor a esta persona
            ]);
        });

        // Crear ventas de ejemplo
        Venta::factory(10)->create([
            'cliente_id' => Cliente::inRandomOrder()->value('id'), // Seleccionar un cliente existente o null
            'user_id' => User::inRandomOrder()->value('id'), // Seleccionar un usuario existente o null
            'comprobante_id' => Comprobante::inRandomOrder()->value('id'), // Seleccionar un comprobante existente o null
        ]);
    }
}