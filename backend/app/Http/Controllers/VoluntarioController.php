<?php

namespace App\Http\Controllers;

use App\Models\Voluntario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoluntarioController extends Controller
{
    public function index()
    {
        return response()->json(
            Voluntario::with('user', 'hojaDeVida.hojasAnuales', 'hojaDeVida.antecedentes')
                ->whereHas('user.roles', fn ($query) => $query->where('slug', 'voluntario'))
                ->get(),
            200
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateVoluntario($request);

        $voluntario = Voluntario::create($data);

        if ($request->filled('fecha_creacion') || $request->filled('estado_hoja')) {
            $voluntario->hojaDeVida()->create([
                'fecha_creacion' => $request->input('fecha_creacion', now()->toDateString()),
                'estado' => $request->input('estado_hoja', $voluntario->user->estado),
            ]);
        }

        return response()->json(
            $voluntario->load('user', 'hojaDeVida.hojasAnuales', 'hojaDeVida.antecedentes'),
            201
        );
    }

    public function show(Voluntario $voluntario)
    {
        return response()->json(
            $voluntario->load('user', 'hojaDeVida.hojasAnuales', 'hojaDeVida.antecedentes'),
            200
        );
    }

    public function update(Request $request, Voluntario $voluntario)
    {
        $data = $this->validateVoluntario($request, $voluntario->id);

        $voluntario->update($data);

        if ($request->filled('fecha_creacion') || $request->filled('estado_hoja')) {
            $voluntario->hojaDeVida()->updateOrCreate(
                [],
                [
                    'fecha_creacion' => $request->input(
                        'fecha_creacion',
                        optional($voluntario->hojaDeVida)->fecha_creacion ?? now()->toDateString()
                    ),
                    'estado' => $request->input('estado_hoja', $voluntario->user->estado),
                ]
            );
        }

        return response()->json(
            $voluntario->load('user', 'hojaDeVida.hojasAnuales', 'hojaDeVida.antecedentes'),
            200
        );
    }

    public function destroy(Voluntario $voluntario)
    {
        $voluntario->delete();

        return response()->json(null, 204);
    }

    private function validateVoluntario(Request $request, ?int $voluntarioId = null): array
    {
        return $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('voluntarios', 'user_id')->ignore($voluntarioId),
            ],
            'fecha_ingreso' => ['required', 'date'],
            'n_registro' => ['required', 'string', Rule::unique('voluntarios', 'n_registro')->ignore($voluntarioId)],
            'factor_rh' => ['required', 'string'],
            'grupo_sanguineo' => ['required', 'string'],
            'fecha_nacimiento' => ['required', 'date'],
        ]);
    }
}
