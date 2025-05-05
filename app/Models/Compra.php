<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modelo que representa una compra realizada a un proveedor.
class Compra extends Model
{
    use HasFactory;

 // Solo se puede guardar o actualizar los campos desde formularios o peticiones.
    protected $fillable = [
        'fecha_hora',
        'impuesto',
        'numero_comprobante',
        'total',
        'comprobante_id',
        'proveedore_id'
    ];

    /**
     * Relaci�n de pertenencia: la compra est� asociada a un proveedor.
     */
    public function proveedore(){
        return $this->belongsTo(Proveedore::class);
    }

    /**
     * Relaci�n de pertenencia: la compra est� asociada a un comprobante.
     */
    public function comprobante(){
        return $this->belongsTo(Comprobante::class);
    }

    /**
     * Relaci�n muchos a muchos: una compra puede incluir varios productos.
     * Se guarda informaci�n adicional en la tabla intermedia (cantidad, precio_compra, precio_venta).
     */
    public function productos(){
        return $this->belongsToMany(Producto::class)->withTimestamps()
        ->withPivot('cantidad','precio_compra','precio_venta');
    }
}