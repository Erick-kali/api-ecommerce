<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    // Obtener todos los productos
    public function index()
    {
        $productos = Producto::with(['categoria', 'imagenes', 'promociones'])->get();
        return response()->json($productos);
    }

    // Crear un nuevo producto
    public function store(Request $request)
    {
        // Validación de los campos requeridos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            'categoria_id' => 'required|integer|exists:categorias,id',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validación de imagen
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Subir la imagen si se ha enviado
        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $imagenPath = $imagen->store('productos', 'public'); // Guarda la imagen en el directorio 'productos'
        }

        // Crear el producto, sin necesidad de enviar 'estado', ya que por defecto será 'activo'
        $producto = Producto::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'categoria_id' => $request->categoria_id,
            'imagen' => $imagenPath,  // Guardamos la ruta de la imagen
            // 'estado' no es necesario incluirlo, ya que la base de datos lo pone por defecto en 'activo'
        ]);

        return response()->json($producto, 201);
    }

    // Obtener un producto por ID
    public function show($id)
    {
        $producto = Producto::with(['categoria', 'imagenes', 'promociones'])->find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        return response()->json($producto);
    }

    // Actualizar un producto
    public function update(Request $request, $id)
    {
        // Validación de datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'nullable|numeric',
            'categoria_id' => 'nullable|integer|exists:categorias,id',
            'estado' => 'nullable|boolean',
            'fecha_creacion' => 'nullable|date',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validación para la imagen
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Obtener el producto
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // Subir imagen si se ha enviado
        if ($request->hasFile('imagen')) {
            // Borrar la imagen anterior si existe
            if ($producto->imagen && Storage::exists('public/' . $producto->imagen)) {
                Storage::delete('public/' . $producto->imagen);
            }

            // Subir la nueva imagen
            $imagen = $request->file('imagen');
            $producto->imagen = $imagen->store('productos', 'public');
        }

        // Actualizar el producto
        $producto->update($request->all());

        return response()->json($producto);
    }

    // Eliminar un producto
    public function destroy($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // Borrar la imagen asociada si existe
        if ($producto->imagen && Storage::exists('public/' . $producto->imagen)) {
            Storage::delete('public/' . $producto->imagen);
        }

        $producto->delete();

        return response()->json(['message' => 'Producto eliminado']);
    }
}
