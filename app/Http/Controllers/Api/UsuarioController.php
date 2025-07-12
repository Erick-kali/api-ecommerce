<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UsuarioController extends Controller
{
    // Listar todos los usuarios
    public function index()
    {
        return response()->json(Usuario::all(), Response::HTTP_OK);
    }

    // Obtener un usuario por ID
    public function show($id)
    {
        $usuario = Usuario::findOrFail($id);
        return response()->json($usuario, Response::HTTP_OK);
    }

    // Crear nuevo usuario
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombres'               => 'required|string|max:150',
            'apellido_paterno'      => 'required|string',
            'apellido_materno'      => 'required|string',
            'tipo_identificacion'   => 'required|string',
            'numero_identificacion' => 'required|string|unique:usuarios',
            'departamento'          => 'required|string',
            'provincia'             => 'required|string',
            'distrito'              => 'required|string',
            'telefono'              => 'required|string',
            'direccion'             => 'required|string',
            'email'                 => 'required|email|unique:usuarios',
            'password'              => 'required|string|min:6',
            'imagen_perfil'         => 'nullable|string',
        ]);

        $usuario = Usuario::create($data);
        return response()->json($usuario, Response::HTTP_CREATED);
    }

    // Actualizar usuario
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        $data = $request->validate([
            'nombres'               => 'sometimes|required|string|max:150',
            'apellido_paterno'      => 'sometimes|required|string',
            'apellido_materno'      => 'sometimes|required|string',
            'tipo_identificacion'   => 'sometimes|required|string',
            'numero_identificacion' => "sometimes|required|string|unique:usuarios,numero_identificacion,$id",
            'departamento'          => 'sometimes|required|string',
            'provincia'             => 'sometimes|required|string',
            'distrito'              => 'sometimes|required|string',
            'telefono'              => 'sometimes|required|string',
            'direccion'             => 'sometimes|required|string',
            'email'                 => "sometimes|required|email|unique:usuarios,email,$id",
            'password'              => 'sometimes|required|string|min:6',
            'imagen_perfil'         => 'nullable|string',
        ]);

        $usuario->update($data);
        return response()->json($usuario, Response::HTTP_OK);
    }

    // Eliminar usuario
    public function destroy($id)
    {
        Usuario::findOrFail($id)->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
