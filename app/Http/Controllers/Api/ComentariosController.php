<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use Illuminate\Http\Request;

class ComentariosController extends Controller
{
    // Obtener todos los comentarios
    public function index()
    {
        $comentarios = Comentario::all();
        return response()->json($comentarios);
    }

    // Obtener un comentario por su ID
    public function show($id)
    {
        $comentario = Comentario::find($id);
        if ($comentario) {
            return response()->json($comentario);
        } else {
            return response()->json(['message' => 'Comentario no encontrado'], 404);
        }
    }

    // Crear un nuevo comentario
    public function store(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'producto_id' => 'required|exists:productos,id',
            'comentario' => 'required|string|max:500',
            'calificacion' => 'nullable|integer|min:0|max:5',
        ]);

        $comentario = Comentario::create([
            'usuario_id' => $request->usuario_id,
            'producto_id' => $request->producto_id,
            'comentario' => $request->comentario,
            'calificacion' => $request->calificacion ?? 0,
        ]);

        return response()->json($comentario, 201);
    }

    // Actualizar un comentario existente
    public function update(Request $request, $id)
    {
        $comentario = Comentario::find($id);
        if ($comentario) {
            $request->validate([
                'usuario_id' => 'sometimes|exists:usuarios,id',
                'producto_id' => 'sometimes|exists:productos,id',
                'comentario' => 'sometimes|string|max:500',
                'calificacion' => 'nullable|integer|min:0|max:5',
            ]);

            $comentario->update($request->only(['usuario_id', 'producto_id', 'comentario', 'calificacion']));
            return response()->json($comentario);
        } else {
            return response()->json(['message' => 'Comentario no encontrado'], 404);
        }
    }

    // Eliminar un comentario
    public function destroy($id)
    {
        $comentario = Comentario::find($id);
        if ($comentario) {
            $comentario->delete();
            return response()->json(['message' => 'Comentario eliminado']);
        } else {
            return response()->json(['message' => 'Comentario no encontrado'], 404);
        }
    }
}
