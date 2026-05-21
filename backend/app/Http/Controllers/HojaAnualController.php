<?php

namespace App\Http\Controllers;

use App\Models\HojaAnual;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HojaAnualController extends Controller
{
    public function index()
    {
        return response()->json(
            HojaAnual::with('hojaDeVida.voluntario.user')->get(), 200);
    }

    public function store(Request $request)
    {
        $data = $this->validateHojaAnual($request);
        $hojaAnual = HojaAnual::create($data);

        return response()->json($hojaAnual->load('hojaDeVida.voluntario.user'), 201);
    }

    public function show(HojaAnual $hojas_anuale)
    {
        return response()->json($hojas_anuale->load('hojaDeVida.voluntario.user'), 200);
    }

    public function update(Request $request, HojaAnual $hojas_anuale)
    {
        $data = $this->validateHojaAnual($request, $hojas_anuale->id_hoja);
        $hojas_anuale->update($data);
        return response()->json($hojas_anuale->load('hojaDeVida.voluntario.user'), 200);
    }

    public function destroy(HojaAnual $hojas_anuale)
    {
        $hojas_anuale->delete();
        return response()->json(null, 204);
    }

    private function validateHojaAnual(Request $request, ?int $idHoja = null): array
    {
        return $request->validate([
            'hoja_de_vida_id' => ['required', 'integer', 'exists:hojas_de_vida,id_libro'],
            'anio' => [
                'required',
                'integer',
                'digits:4',
                Rule::unique('hojas_anuales')
                    ->ignore($idHoja, 'id_hoja')
                    ->where(fn ($query) => $query->where('hoja_de_vida_id', $request->hoja_de_vida_id)),
            ],
            'porcentaje_asistencia' => ['nullable', 'numeric', 'between:0,100'],
            'cargo' => ['nullable', 'string'],
            'observaciones_generales' => ['nullable', 'string'],
        ]);
    }
}
