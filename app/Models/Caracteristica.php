<?php

namespace App\Models;

// Importamos las herramientas necesarias de Laravel
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caracteristica extends Model
{
    // Permite usar las factorías para crear datos de prueba fácilmente.
    use HasFactory;

     /**
     * Relación: una característica pertenece a una categoría.
     *
     */
    public function categoria(){
        // Esta función permite acceder a la categoría relacionada con la característica.
        return $this->hasOne(Categoria::class);
    }
    /**
     * Relación: una característica pertenece a una marca.
     *
     */
    public function marca(){
        // Esta función permite acceder a la marca relacionada con la característica.
        return $this->hasOne(Marca::class);
    }

    
    /**
     * $fillable indica qué campos se pueden rellenar desde un formulario o desde el código.
     * Es como una lista de "campos permitidos" para proteger la información.
     * Si alguien intenta enviar otros datos que no estén aquí, Laravel los ignora.
     * Así evitamos que se cambien datos importantes por accidente o de forma malintencionada.
     */
    protected $fillable = ['nombre','descripcion'];
}
