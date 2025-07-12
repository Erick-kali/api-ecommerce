<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens; // Asegúrate de que esto esté aquí
use Illuminate\Support\Facades\Hash;

class Usuario extends Model
{
    use HasFactory, HasApiTokens; // Esto es necesario para usar Sanctum

    protected $table = 'usuarios';

    protected $fillable = [
        'imagen_perfil',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'tipo_identificacion',
        'numero_identificacion',
        'departamento',
        'provincia',
        'distrito',
        'telefono',
        'direccion',
        'email',
        'password',
    ];

    // Encriptar la contraseña automáticamente
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    protected $hidden = [
        'password',
    ];
}
