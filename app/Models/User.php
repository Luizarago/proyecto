<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

// Modelo que representa a un usuario del sistema (por defecto en Laravel).
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    // Campos que se pueden guardar o actualizar desde formularios o peticiones.
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // Estos campos no se muestran cuando se convierte el usuario a JSON.
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Convierte automáticamente el campo email_verified_at a tipo fecha.
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Un usuario puede tener muchas ventas asociadas.
    public function ventas(){
        return $this->hasMany(Venta::class);
    }
}