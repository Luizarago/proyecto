<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modelo que representa una categoría de productos.
class Categoria extends Model
{
    use HasFactory;

    /**
     * Relación muchos a muchos: una categoría puede estar asociada a varios productos.
     */
    public function productos(){
        return $this->belongsToMany(Producto::class)->withTimestamps();
    }

    /**
     * Relación de pertenencia: una categoría está vinculada a una característica.
     */
    public function caracteristica(){
        return $this->belongsTo(Caracteristica::class);
    }

    // Solo se puede guardar o actualizar los campos desde formularios o peticiones.
    protected $fillable = ['caracteristica_id'];
}