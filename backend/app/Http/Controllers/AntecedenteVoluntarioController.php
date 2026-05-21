<?php

namespace App\Http\Controllers;

use App\Models\AntecedenteVoluntario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AntecedenteVoluntarioController extends Controller
{
    public function index()
    {
        return response()->json(
            AntecedenteVoluntario::with('hojaDeVida.voluntario.user')->get(),
            200
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateAntecedente($request);
        $antecedente = AntecedenteVoluntario::create($data);

        return response()->json($antecedente->load('hojaDeVida.voluntario.user'), 201);
    }

    public function show(AntecedenteVoluntario $antecedentes_voluntario)
    {
        return response()->json($antecedentes_voluntario->load('hojaDeVida.voluntario.user'), 200);
    }

    public function update(Request $request, AntecedenteVoluntario $antecedentes_voluntario)
    {
        $data = $this->validateAntecedente($request);
        $antecedentes_voluntario->update($data);

        return response()->json($antecedentes_voluntario->load('hojaDeVida.voluntario.user'), 200);
    }

    public function destroy(AntecedenteVoluntario $antecedentes_voluntario)
    {
        $antecedentes_voluntario->delete();

        return response()->json(null, 204);
    }

    private function validateAntecedente(Request $request): array
    {
        return $request->validate([
            'hoja_de_vida_id' => ['required', 'integer', 'exists:hojas_de_vida,id_libro'],
            'tipo' => ['required', 'string', Rule::in(AntecedenteVoluntario::TIPOS)],
            'nombre' => ['required', 'string'],
            'descripcion' => ['nullable', 'string'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_termino' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'duracion' => ['nullable', 'string'],
        ]);
    }
}
