<?php

namespace App\Http\Controllers;

use App\Models\Voluntario;
use Illuminate\Http\Request;

class VoluntarioController extends Controller
{
    private const RELATIONS = [
        'user.roles.permissions',
        'filial',
        'hojaVidaAnual.titulos',
        'hojaVidaAnual.cursos',
        'hojaVidaAnual.sanciones',
        'hojaVidaAnual.reconocimiento',
    ];

    public function index(Request $request)
    {
        $query = Voluntario::with(self::RELATIONS);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('registro_filial', 'like', "%{$search}%")
                    ->orWhere('rut', 'like', "%{$search}%")
                    ->orWhere('nombres', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->orderBy('registro_filial')->get(),
            200
        );
    }
}
