<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Registrar nuevo usuario y devolver token
     */
    public function register(Request $request)
    {
        // Validación de datos
        $validator = Validator::make($request->all(), [
            'imagen_perfil'        => 'nullable|file|mimes:jpg,jpeg,png,gif', // Aceptar imágenes en varios formatos
            'nombres'              => 'required|string|max:255',
            'apellido_paterno'     => 'required|string|max:255',
            'apellido_materno'     => 'required|string|max:255',
            'tipo_identificacion'  => 'required|string|max:50',
            'numero_identificacion'=> 'required|string|max:100|unique:usuarios,numero_identificacion',
            'departamento'         => 'required|string|max:100',
            'provincia'            => 'required|string|max:100',
            'distrito'             => 'required|string|max:100',
            'telefono'             => 'required|string|max:50',
            'direccion'            => 'required|string|max:255',
            'email'                => 'required|email|unique:usuarios,email',
            'password'             => 'required|string|min:6|confirmed', // Asegúrate de enviar 'password_confirmation'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Si imagen viene como archivo, subirla
        if ($request->hasFile('imagen_perfil')) {
            $image = $request->file('imagen_perfil');
            $filename = 'perfil/' . uniqid() . '.' . $image->getClientOriginalExtension();  // Obtener extensión del archivo
            $image->storeAs('public', $filename);  // Guarda la imagen en el almacenamiento público
            $request->merge(['imagen_perfil' => $filename]);  // Asigna la ruta de la imagen al campo imagen_perfil
        }

        // Crear usuario
        try {
            $user = Usuario::create($request->all());
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Registro exitoso',
                'user'    => new UsuarioResource($user),
                'token'   => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar el usuario',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login de usuario existente y devolver token
     */
    public function login(Request $request)
    {
        // Validación de datos
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:usuarios,email', // Verificar que el email exista
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar al usuario por su correo electrónico
        $user = Usuario::where('email', $request->email)->first();

        // Si el usuario no existe
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        // Verificar que la contraseña proporcionada coincide con el hash almacenado
        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas',
            ], 401);
        }

        // Si todo está bien, generamos el token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'user'    => [
                'id' => $user->id,
                'nombres' => $user->nombres,
                'email' => $user->email,
                'rol' => $user->rol // Agregamos el campo 'rol'
            ],
            'token'   => $token,
        ], 200);
    }
    public function updateRole(Request $request, $id)
    {
        // Validación de datos
        $validator = Validator::make($request->all(), [
            'rol' => 'required|string|in:admin,cliente', // Aceptar solo 'admin' o 'cliente'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar al usuario por su ID
        $user = Usuario::find($id);

        // Si el usuario no existe, retorna un error
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        // Actualizar el rol del usuario
        $user->rol = $request->rol;
        $user->save(); // Guardar los cambios

        // Retornar la respuesta con los datos actualizados
        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'user' => $user, // Puedes agregar los datos del usuario actualizados si deseas
        ], 200);
    }

}
