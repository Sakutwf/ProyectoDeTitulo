<?php

namespace App\Http\Controllers;

use App\Models\AntecedenteVoluntario;
use App\Models\HojaAnual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HojaAnualController extends Controller
{
    private const ANTECEDENTE_KEYS = [
        'cursos' => 'CURSO',
        'talleres' => 'TALLER',
        'seminarios' => 'SEMINARIO',
        'titulos' => 'TITULO',
        'premios' => 'PREMIO',
    ];

    private const REMOVABLE_TYPES = [
        'CURSO',
        'TALLER',
        'SEMINARIO',
        'CAPACITACION',
        'TITULO',
        'PREMIO',
    ];

    public function index()
    {
        return response()->json(HojaAnual::with('hojaDeVida.voluntario.user')->get(), 200);
    }

    public function store(Request $request)
    {
        $data = $this->validateHojaAnual($request);
        $hojaAnual = DB::transaction(function () use ($request, $data) {
            $hojaAnual = HojaAnual::create($data);
            $this->syncYearlyAntecedentes($hojaAnual, $request->input('antecedentes', []));

            return $hojaAnual;
        });

        return response()->json($hojaAnual->load('hojaDeVida.voluntario.user'), 201);
    }

    public function show(HojaAnual $hojas_anuale)
    {
        return response()->json($hojas_anuale->load('hojaDeVida.voluntario.user'), 200);
    }

    public function update(Request $request, HojaAnual $hojas_anuale)
    {
        $data = $this->validateHojaAnual($request, $hojas_anuale->id_hoja);
        $originalYear = (int) $hojas_anuale->anio;

        DB::transaction(function () use ($request, $hojas_anuale, $data, $originalYear) {
            $hojas_anuale->update($data);
            $this->syncYearlyAntecedentes($hojas_anuale, $request->input('antecedentes', []), [$originalYear]);
        });

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
            'lista' => ['nullable', 'string'],
            'labor_efectuada' => ['nullable', 'string'],
            'observaciones_generales' => ['nullable', 'string'],
            'antecedentes' => ['sometimes', 'array'],
            'antecedentes.cursos' => ['sometimes', 'array'],
            'antecedentes.cursos.*' => ['string'],
            'antecedentes.talleres' => ['sometimes', 'array'],
            'antecedentes.talleres.*' => ['string'],
            'antecedentes.seminarios' => ['sometimes', 'array'],
            'antecedentes.seminarios.*' => ['string'],
            'antecedentes.titulos' => ['sometimes', 'array'],
            'antecedentes.titulos.*' => ['string'],
            'antecedentes.premios' => ['sometimes', 'array'],
            'antecedentes.premios.*' => ['string'],
        ]);
    }

    private function syncYearlyAntecedentes(HojaAnual $hojaAnual, array $antecedentes, array $yearsToClear = []): void
    {
        $year = (int) $hojaAnual->anio;
        $startOfYear = sprintf('%04d-01-01', $year);
        $years = collect([$year, ...$yearsToClear])
            ->filter()
            ->unique()
            ->values()
            ->all();

        AntecedenteVoluntario::query()
            ->where('hoja_de_vida_id', $hojaAnual->hoja_de_vida_id)
            ->whereIn('tipo', self::REMOVABLE_TYPES)
            ->where(function ($query) use ($years) {
                foreach ($years as $clearYear) {
                    $start = sprintf('%04d-01-01', $clearYear);
                    $end = sprintf('%04d-12-31', $clearYear);

                    $query->orWhere(function ($yearQuery) use ($start, $end) {
                        $yearQuery->whereBetween('fecha_inicio', [$start, $end])
                            ->orWhereBetween('fecha_termino', [$start, $end]);
                    });
                }
            })
            ->delete();

        foreach (self::ANTECEDENTE_KEYS as $key => $tipo) {
            foreach ($this->sanitizeAntecedenteList($antecedentes[$key] ?? []) as $nombre) {
                AntecedenteVoluntario::create([
                    'hoja_de_vida_id' => $hojaAnual->hoja_de_vida_id,
                    'tipo' => $tipo,
                    'nombre' => $nombre,
                    'fecha_inicio' => $startOfYear,
                ]);
            }
        }
    }

    private function sanitizeAntecedenteList(array $items): array
    {
        return collect($items)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
