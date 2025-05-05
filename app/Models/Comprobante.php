<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modelo que representa un comprobante (por ejemplo, factura o ticket).
class Comprobante extends Model
{
    use HasFactory;

    /**
     * Un comprobante puede estar asociado a muchas compras.
     */
    public function compras(){
        return $this->hasMany(Compra::class);
    }

    /**
     * Un comprobante puede estar asociado a muchas ventas.
     */
    public function ventas(){
        return $this->hasMany(Venta::class);
    }
}