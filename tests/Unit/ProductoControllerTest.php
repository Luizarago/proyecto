<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\User;

class ProductoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutar el seeder principal para datos esenciales
        $this->artisan('db:seed --class=DatabaseSeeder');

        // Ejecutar el seeder adicional para poblar datos de prueba
        $this->artisan('db:seed --class=PoblarBaseDeDatosSeeder');
    }

    public function test_crear_producto()
    {
        // Autenticar el primer usuario existente
        $user = User::first(); // Buscar el primer usuario en la base de datos
        $this->actingAs($user); // Autenticar al usuario

        // Verificar que existen categorías y marcas en la base de datos
        $this->assertTrue(Categoria::exists(), 'No hay categorías en la base de datos.');
        $this->assertTrue(Marca::exists(), 'No hay marcas en la base de datos.');

        // Seleccionar una categoría y una marca existentes
        $categoria = Categoria::first();
        $marca = Marca::first();

        // Datos del producto que se enviarán
        $datosProducto = [
            'codigo' => 'COD123',
            'nombre' => 'Producto de Prueba',
            'descripcion' => 'Una descripción de prueba',
            'fecha_vencimiento' => now()->addDays(30)->toDateString(),
            'marca_id' => $marca->id,
            'categorias' => [$categoria->id], // Asignar categorías
        ];

        // Simular una solicitud POST al método store del controlador
        $response = $this->post(route('productos.store'), $datosProducto);

        // Verificar que la respuesta sea un redireccionamiento exitoso
        $response->assertStatus(302);

        // Verificar que el producto se haya creado en la base de datos
        $this->assertDatabaseHas('productos', [
            'codigo' => 'COD123',
            'nombre' => 'Producto de Prueba',
            'descripcion' => 'Una descripción de prueba',
            'fecha_vencimiento' => now()->addDays(30)->toDateString(),
            'marca_id' => $marca->id,
        ]);

        // Verificar que la relación con las categorías se haya registrado correctamente
        $producto = Producto::where('codigo', 'COD123')->first();

        $this->assertNotNull($producto, 'Ha fallado el test al crear el producto.');
        $this->assertTrue($producto->categorias->contains($categoria), 'La categoría no está asociada al producto.');
    }
}