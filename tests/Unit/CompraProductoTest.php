<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Producto; 
use App\Models\Compra;   

class CompraProductoTest extends TestCase
{
    
    public function test_registrar_compra_producto()
    {
        // Crear un producto y una compra
        $producto = Producto::factory()->create();
        $compra = Compra::factory()->create();
    
        // Registrar la relación en la tabla compra_producto
        $compra->productos()->attach($producto->id, [
            'cantidad' => 10,
            'precio_compra' => 50.00,
            'precio_venta' => 75.00,
        ]);
    
        // Verificar que la relación se haya registrado correctamente
        $this->assertDatabaseHas('compra_producto', [
            'compra_id' => $compra->id,
            'producto_id' => $producto->id,
            'cantidad' => 10,
            'precio_compra' => 50.00,
            'precio_venta' => 75.00,
        ]);
    }
}
