<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Provincia;
use App\Models\Distrito;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    // Obtener todos los departamentos
    public function getDepartamentos()
    {
        $departamentos = Departamento::all();
        return response()->json($departamentos);
    }

    // Obtener provincias por departamento
    public function getProvincias($departamentoId)
    {
        $provincias = Provincia::where('departamento_id', $departamentoId)->get();
        return response()->json($provincias);
    }

    // Obtener distritos por provincia
    public function getDistritos($provinciaId)
    {
        $distritos = Distrito::where('provincia_id', $provinciaId)->get();
        return response()->json($distritos);
    }
}
