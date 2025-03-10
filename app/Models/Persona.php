<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $fillable = [
        'razon_social',
        'direccion',
        'tipo_persona',
        'documento_id',
        'numero_documento'
    ];

    // Relación con el modelo Documento
    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    public function proveedore()
    {
        return $this->hasOne(Proveedore::class);
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }

    // Accesor para obtener el tipo de documento completo
    public function getDocumentoCompletoAttribute()
    {
        return $this->documento->tipo_documento . ': ' . $this->numero_documento;
    }
}