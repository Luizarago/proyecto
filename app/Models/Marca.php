<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modelo que representa una marca de productos.
class Marca extends Model
{
    use HasFactory;

    /**
     * Una marca puede tener muchos productos asociados.
     */
    public function productos(){
        return $this->hasMany(Producto::class);
    }

    /**
     * Una marca pertenece a una característica.
     */
    public function caracteristica(){
        return $this->belongsTo(Caracteristica::class);
    }

    // Solo se permite modificar los campos desde formularios o peticiones.
    protected $fillable = ['caracteristica_id'];
}