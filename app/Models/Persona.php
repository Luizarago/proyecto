<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modelo que representa una persona (cliente o proveedor).
class Persona extends Model
{
    use HasFactory;

    // Campos que se pueden guardar o actualizar desde formularios o peticiones.
    protected $fillable = [
        'razon_social',
        'direccion',
        'tipo_persona',
        'documento_id',
        'numero_documento'
    ];

    // Relación: una persona tiene un tipo de documento.
    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    // Relación: una persona puede ser proveedor.
    public function proveedore()
    {
        return $this->hasOne(Proveedore::class);
    }

    // Relación: una persona puede ser cliente.
    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }

    // Accesorio para mostrar el tipo y número de documento juntos.
    public function getDocumentoCompletoAttribute()
    {
        return $this->documento->tipo_documento . ': ' . $this->numero_documento;
    }
}