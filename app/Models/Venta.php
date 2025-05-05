<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modelo que representa una venta realizada a un cliente.
class Venta extends Model
{
    use HasFactory;

    // Campos que se pueden guardar o actualizar desde formularios o peticiones.
    protected $fillable = [
        'numero_ticket',
        'fecha_hora',
        'impuesto',
        'numero_comprobante',
        'total',
        'estado',
        'cliente_id',
        'user_id',
        'comprobante_id',
    ];

    // Relación: una venta pertenece a un cliente.
    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    // Relación: una venta pertenece a un usuario (quien la realizó).
    public function user(){
        return $this->belongsTo(User::class);
    }

    // Relación: una venta pertenece a un comprobante.
    public function comprobante(){
        return $this->belongsTo(Comprobante::class);
    }

    // Relación muchos a muchos: una venta puede incluir varios productos.
    // Se guarda información extra en la tabla intermedia (cantidad, precio_venta, descuento).
    public function productos(){
        return $this->belongsToMany(Producto::class)->withTimestamps()
            ->withPivot('cantidad','precio_venta','descuento');
    }
}