<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use App\Models\Pais;
use Illuminate\Http\JsonResponse;

class CatalogoController extends Controller
{
    public function paises(): JsonResponse
    {
        return response()->json([
            'data' => Pais::query()->where('activo', true)->orderBy('nombre')->get(['codigo', 'nombre']),
        ]);
    }

    public function niveles(): JsonResponse
    {
        return response()->json([
            'data' => Nivel::query()
                ->where('activo', true)
                ->orderBy('orden')
                ->get(['id', 'nombre', 'codigo', 'orden']),
        ]);
    }
}
