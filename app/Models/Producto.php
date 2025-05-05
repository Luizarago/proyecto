<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

// Modelo que representa un producto.
class Producto extends Model
{
    use HasFactory;

    // Campos que se pueden guardar o actualizar desde formularios o peticiones.
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'fecha_vencimiento',
        'marca_id',
        'img_path',
        'stock' 
    ];

    // Relaci�n muchos a muchos: un producto puede estar en varias compras.
    public function compras()
    {
        return $this->belongsToMany(Compra::class)->withTimestamps()
            ->withPivot('cantidad', 'precio_compra', 'precio_venta');
    }

    // Relaci�n muchos a muchos: un producto puede estar en varias ventas.
    public function ventas()
    {
        return $this->belongsToMany(Venta::class)->withTimestamps()
            ->withPivot('cantidad', 'precio_venta', 'descuento');
    }

    // Relaci�n muchos a muchos: un producto puede pertenecer a varias categor�as.
    public function categorias()
    {
        return $this->belongsToMany(Categoria::class)->withTimestamps();
    }

    // Relaci�n: un producto pertenece a una marca.
    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

        // M�todo que guarda la imagen subida en la carpeta de productos y le pone un nombre �nico.
        public function handleUploadImage($image)
        {
            $file = $image;
            $name = time() . $file->getClientOriginalName();
            Storage::putFileAs('/public/productos/', $file, $name, 'public');
    
            return $name;
        }
}