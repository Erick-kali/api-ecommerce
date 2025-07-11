<?php

namespace App\Http\Controllers\Api;

use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoriaApiController extends Controller
{
    /**
     * Obtener todas las categorías.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Retorna todas las categorías
        return Categoria::all();
    }

    /**
     * Almacenar una nueva categoría.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $request->validate([
            'nombre' => 'required|string',   // 'nombre' es requerido y debe ser una cadena
            'descripcion' => 'nullable|string',  // 'descripcion' es opcional
        ]);

        // Crear y devolver la nueva categoría
        return Categoria::create($request->all());
    }

    /**
     * Mostrar una categoría específica.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Buscar una categoría por su ID y devolverla
        return Categoria::findOrFail($id);
    }

    /**
     * Actualizar una categoría específica.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Buscar la categoría por su ID
        $categoria = Categoria::findOrFail($id);

        // Actualizar los datos de la categoría
        $categoria->update($request->all());

        // Devolver la categoría actualizada
        return $categoria;
    }

    /**
     * Eliminar una categoría específica.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Buscar la categoría por su ID
        $categoria = Categoria::findOrFail($id);

        // Eliminar la categoría
        $categoria->delete();

        // Devolver una respuesta vacía con código 204
        return response()->json(null, 204);
    }
}
