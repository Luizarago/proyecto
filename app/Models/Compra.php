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
     * Relación de pertenencia: la compra está asociada a un proveedor.
     */
    public function proveedore(){
        return $this->belongsTo(Proveedore::class);
    }

    /**
     * Relación de pertenencia: la compra está asociada a un comprobante.
     */
    public function comprobante(){
        return $this->belongsTo(Comprobante::class);
    }

    /**
     * Relación muchos a muchos: una compra puede incluir varios productos.
     * Se guarda información adicional en la tabla intermedia (cantidad, precio_compra, precio_venta).
     */
    public function productos(){
        return $this->belongsToMany(Producto::class)->withTimestamps()
        ->withPivot('cantidad','precio_compra','precio_venta');
    }
}