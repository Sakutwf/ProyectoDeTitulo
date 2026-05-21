<?php

namespace App\Http\Controllers;

use App\Models\HojaDeVida;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HojaDeVidaController extends Controller
{
    public function index()
    {
        return response()->json(
            HojaDeVida::with('voluntario.user', 'hojasAnuales', 'antecedentes')->get(),
            200
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateHojaDeVida($request);
        $hojaDeVida = HojaDeVida::create($data);

        return response()->json(
            $hojaDeVida->load('voluntario.user', 'hojasAnuales', 'antecedentes'),
            201
        );
    }

    public function show(HojaDeVida $hojas_de_vida)
    {
        return response()->json(
            $hojas_de_vida->load('voluntario.user', 'hojasAnuales', 'antecedentes'),
            200
        );
    }

    public function update(Request $request, HojaDeVida $hojas_de_vida)
    {
        $data = $this->validateHojaDeVida($request, $hojas_de_vida->id_libro);
        $hojas_de_vida->update($data);

        return response()->json(
            $hojas_de_vida->load('voluntario.user', 'hojasAnuales', 'antecedentes'),
            200
        );
    }

    public function destroy(HojaDeVida $hojas_de_vida)
    {
        $hojas_de_vida->delete();

        return response()->json(null, 204);
    }

    private function validateHojaDeVida(Request $request, ?int $idLibro = null): array
    {
        return $request->validate([
            'voluntario_id' => [
                'required',
                'integer',
                'exists:voluntarios,id',
                Rule::unique('hojas_de_vida', 'voluntario_id')->ignore($idLibro, 'id_libro'),
            ],
            'fecha_creacion' => ['required', 'date'],
            'estado' => ['required', 'string'],
        ]);
    }
}
