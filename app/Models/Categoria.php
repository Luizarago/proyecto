<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modelo que representa una categor�a de productos.
class Categoria extends Model
{
    use HasFactory;

    /**
     * Relaci�n muchos a muchos: una categor�a puede estar asociada a varios productos.
     */
    public function productos(){
        return $this->belongsToMany(Producto::class)->withTimestamps();
    }

    /**
     * Relaci�n de pertenencia: una categor�a est� vinculada a una caracter�stica.
     */
    public function caracteristica(){
        return $this->belongsTo(Caracteristica::class);
    }

    // Solo se puede guardar o actualizar los campos desde formularios o peticiones.
    protected $fillable = ['caracteristica_id'];
}