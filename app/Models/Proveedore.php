<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modelo que representa un proveedor.
class Proveedore extends Model
{
    use HasFactory;

    // Solo se permite modificar el campo persona_id desde formularios o peticiones.
    protected $fillable = ['persona_id'];

    // Un proveedor pertenece a una persona.
    public function persona(){
        return $this->belongsTo(Persona::class);
    }

    // Un proveedor puede tener muchas compras.
    public function compras(){
        return $this->hasMany(Compra::class);
    }
}