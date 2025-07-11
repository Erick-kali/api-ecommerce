<?php
// app/Http/Resources/UsuarioResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                   => $this->id,
            'imagen_perfil'        => $this->imagen_perfil,
            'nombres'              => $this->nombres,
            'apellido_paterno'     => $this->apellido_paterno,
            'apellido_materno'     => $this->apellido_materno,
            'tipo_identificacion'  => $this->tipo_identificacion,
            'numero_identificacion'=> $this->numero_identificacion,
            'departamento'         => $this->departamento,
            'provincia'            => $this->provincia,
            'distrito'             => $this->distrito,
            'telefono'             => $this->telefono,
            'direccion'            => $this->direccion,
            'email'                => $this->email,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}