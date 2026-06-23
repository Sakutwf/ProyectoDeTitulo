<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Voluntario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoluntarioController extends Controller
{
    private const RELATIONS = [
        'user.roles.permissions',
        'filial',
        'hojasVidaAnuales.titulos',
        'hojasVidaAnuales.cursos',
        'hojasVidaAnuales.sanciones',
        'hojasVidaAnuales.reconocimiento',
    ];

    public function index(Request $request)
    {
        $query = Voluntario::with(self::RELATIONS);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('n_registro', 'like', "%{$search}%")
                    ->orWhere('rut', 'like', "%{$search}%")
                    ->orWhere('nombres', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->orderBy('n_registro')->get(),
            200
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateVoluntario($request);

        $voluntario = Voluntario::create($data);
        $this->ensureVolunteerRole($voluntario);

        return response()->json(
            $voluntario->load(self::RELATIONS),
            201
        );
    }

    public function show(Voluntario $voluntario)
    {
        return response()->json(
            $voluntario->load(self::RELATIONS),
            200
        );
    }

    public function update(Request $request, Voluntario $voluntario)
    {
        $data = $this->validateVoluntario($request, $voluntario->n_registro);

        $voluntario->update($data);
        $this->ensureVolunteerRole($voluntario);

        return response()->json(
            $voluntario->load(self::RELATIONS),
            200
        );
    }

    public function destroy(Voluntario $voluntario)
    {
        $voluntario->delete();

        return response()->json(null, 204);
    }

    private function validateVoluntario(Request $request, ?string $registroActual = null): array
    {
        return $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('voluntarios', 'user_id')->ignore($registroActual, 'n_registro'),
            ],
            'n_registro' => ['required', 'string', 'max:30', Rule::unique('voluntarios', 'n_registro')->ignore($registroActual, 'n_registro')],
            'filial_id' => ['required', 'integer', 'exists:filiales,id'],
            'rut' => ['required', 'string', 'max:20', Rule::unique('voluntarios', 'rut')->ignore($registroActual, 'n_registro')],
            'nombres' => ['required', 'string', 'max:150'],
            'apellidos' => ['required', 'string', 'max:150'],
            'nacionalidad' => ['nullable', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'fecha_incorporacion' => ['nullable', 'date'],
            'celular' => ['nullable', 'string', 'max:30'],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'enfermedades' => ['nullable', 'string'],
            'alergias' => ['nullable', 'string'],
            'contacto_emergencia_nombre' => ['nullable', 'string', 'max:150'],
            'contacto_emergencia_numero' => ['nullable', 'string', 'max:30'],
        ]);
    }

    private function ensureVolunteerRole(Voluntario $voluntario): void
    {
        $roleId = Role::where('clave', 'voluntario')->value('id');

        if ($roleId) {
            $voluntario->user->roles()->syncWithoutDetaching([$roleId]);
        }
    }
}
