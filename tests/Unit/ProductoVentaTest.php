<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Producto;
use App\Models\Venta;

class ProductoVentaTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrar_producto_venta()
    {
        // Ejecutar el método para poblar la base de datos
        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->artisan('db:seed', ['--class' => 'PoblarBaseDeDatosSeeder']);

        // Obtener un producto y una venta existentes
        $producto = Producto::first(); // Obtiene el primer producto de la base de datos
        $venta = Venta::first(); // Obtiene la primera venta de la base de datos

        // Registrar la relación en la tabla producto_venta
        $venta->productos()->attach($producto->id, [
            'cantidad' => 5,
            'precio_venta' => 75.00,
            'descuento' => 5.00,
        ]);

        // Verificar que la relación se haya registrado correctamente
        $this->assertDatabaseHas('producto_venta', [
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 5,
            'precio_venta' => 75.00,
            'descuento' => 5.00,
        ]);
    }
}