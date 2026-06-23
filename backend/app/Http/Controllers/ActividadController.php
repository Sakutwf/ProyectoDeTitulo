<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Voluntario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActividadController extends Controller
{
    private const RELATIONS = ['filial', 'creador', 'voluntarios.user'];

    /**
     * Display a listing of the resource paginated (8 per page) and searchable.
     */
    public function index(Request $request)
    {
        $query = Actividad::with(self::RELATIONS);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('tipo', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhere('lugar', 'like', "%{$search}%")
                    ->orWhere('colaborador_externo', 'like', "%{$search}%");
            });
        }

        return response()->json($query->orderBy('id')->paginate(8), 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateActividad($request);

        $actividad = Actividad::create($data);
        $this->syncVoluntarios($actividad, $request);

        return response()->json($actividad->load(self::RELATIONS), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return response()->json(Actividad::with(self::RELATIONS)->findOrFail($id), 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Actividad $actividad)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);
        $data = $this->validateActividad($request, true);

        $actividad->update($data);
        $this->syncVoluntarios($actividad, $request);

        return response()->json($actividad->load(self::RELATIONS), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $actividad = Actividad::findOrFail($id);
        $actividad->delete();

        return response()->json(true, 200);
    }

    /**
     * Asociar un voluntario a la actividad.
     */
    public function asociarVoluntario($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);

        $data = $request->validate([
            'voluntario_n_registro' => ['required', 'string', 'exists:voluntarios,n_registro'],
            'horas_asistidas' => ['nullable', 'numeric', 'min:0'],
            'registrado_por' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $actividad->voluntarios()->syncWithoutDetaching([
            $data['voluntario_n_registro'] => [
                'horas_asistidas' => $data['horas_asistidas'] ?? 0,
                'registrado_por' => $data['registrado_por'] ?? null,
            ],
        ]);

        return response()->json($actividad->load(self::RELATIONS), 200);
    }

    /**
     * Desasociar un voluntario de la actividad.
     */
    public function desasociarVoluntario($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);
        $data = $request->validate([
            'voluntario_n_registro' => ['required', 'string', 'exists:voluntarios,n_registro'],
        ]);

        $actividad->voluntarios()->detach($data['voluntario_n_registro']);

        return response()->json($actividad->load(self::RELATIONS), 200);
    }

    private function validateActividad(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'filial_id' => [$required, 'integer', 'exists:filiales,id'],
            'creado_por' => [$required, 'integer', 'exists:users,id'],
            'nombre' => [$required, 'string', 'max:200'],
            'tipo' => [$required, 'string', Rule::in(['Operativa', 'Formación', 'En filial', 'Reunion'])],
            'objetivo' => ['nullable', 'string'],
            'fecha_inicio' => [$required, 'date'],
            'fecha_termino' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_termino' => ['nullable', 'date_format:H:i'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'horas_totales' => ['nullable', 'numeric', 'min:0'],
            'colaborador_externo' => ['nullable', 'string', 'max:200'],
            'voluntarios' => ['sometimes', 'array'],
            'voluntarios.*' => ['string', 'exists:voluntarios,n_registro'],
            'voluntarios_detalle' => ['sometimes', 'array'],
            'voluntarios_detalle.*.voluntario_n_registro' => ['required_with:voluntarios_detalle', 'string', 'exists:voluntarios,n_registro'],
            'voluntarios_detalle.*.horas_asistidas' => ['nullable', 'numeric', 'min:0'],
            'voluntarios_detalle.*.registrado_por' => ['nullable', 'integer', 'exists:users,id'],
        ]);
    }

    private function syncVoluntarios(Actividad $actividad, Request $request): void
    {
        if ($request->has('voluntarios_detalle')) {
            $syncData = collect($request->input('voluntarios_detalle', []))
                ->mapWithKeys(fn (array $detalle) => [
                    $detalle['voluntario_n_registro'] => [
                        'horas_asistidas' => $detalle['horas_asistidas'] ?? 0,
                        'registrado_por' => $detalle['registrado_por'] ?? null,
                    ],
                ])
                ->all();

            $actividad->voluntarios()->sync($syncData);
            return;
        }

        if ($request->has('voluntarios')) {
            $syncData = collect($request->input('voluntarios', []))
                ->mapWithKeys(fn (string $nRegistro) => [
                    $nRegistro => ['horas_asistidas' => 0, 'registrado_por' => null],
                ])
                ->all();

            $actividad->voluntarios()->sync($syncData);
        }
    }
}
