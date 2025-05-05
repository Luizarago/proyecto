<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modelo que representa un cliente.
class Cliente extends Model
{
    use HasFactory;

    /**
     * Relaci�n de pertenencia: un cliente est� vinculado a una persona.
     */
    public function persona(){
        return $this->belongsTo(Persona::class);
    }

    /**
     * Relaci�n uno a muchos: un cliente puede tener varias ventas.
     */
    public function ventas(){
        return $this->hasMany(Venta::class);
    }

     // Solo se puede guardar o actualizar los campos desde formularios o peticiones.
    protected $fillable = ['persona_id'];
}