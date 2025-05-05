<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modelo que representa un tipo de documento (por ejemplo, DNI, pasaporte).
class Documento extends Model
{
    use HasFactory;

    /**
     * Un documento puede estar asociado a muchas personas.
     */
    public function persona(){
        return $this->hasMany(Persona::class);
    }
}