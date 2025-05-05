<?php

namespace App\Models;

// Importamos las herramientas necesarias de Laravel
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caracteristica extends Model
{
    // Permite usar las factor�as para crear datos de prueba f�cilmente.
    use HasFactory;

     /**
     * Relaci�n: una caracter�stica pertenece a una categor�a.
     *
     */
    public function categoria(){
        // Esta funci�n permite acceder a la categor�a relacionada con la caracter�stica.
        return $this->hasOne(Categoria::class);
    }
    /**
     * Relaci�n: una caracter�stica pertenece a una marca.
     *
     */
    public function marca(){
        // Esta funci�n permite acceder a la marca relacionada con la caracter�stica.
        return $this->hasOne(Marca::class);
    }

    
    /**
     * $fillable indica qu� campos se pueden rellenar desde un formulario o desde el c�digo.
     * Es como una lista de "campos permitidos" para proteger la informaci�n.
     * Si alguien intenta enviar otros datos que no est�n aqu�, Laravel los ignora.
     * As� evitamos que se cambien datos importantes por accidente o de forma malintencionada.
     */
    protected $fillable = ['nombre','descripcion'];
}
