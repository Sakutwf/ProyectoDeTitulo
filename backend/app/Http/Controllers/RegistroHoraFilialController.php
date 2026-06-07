<?php

namespace App\Http\Controllers;

use App\Models\RegistroHoraFilial;
use App\Services\AttendanceSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class RegistroHoraFilialController extends Controller
{
    public function __construct(private AttendanceSheetService $attendanceSheetService)
    {
    }

    public function index()
    {
        return response()->json(RegistroHoraFilial::with('user')->orderByDesc('fecha')->get(), 200);
    }

    public function store(Request $request)
    {
        $data = $this->validateRegistro($request);
        $data['horas_totales'] = $this->calculateHours($data['fecha'], $data['hora_entrada'], $data['hora_salida']);

        $registro = RegistroHoraFilial::create($data);
        $this->attendanceSheetService->syncForUsers([$registro->user_id], [(int) Carbon::parse($registro->fecha)->year]);

        return response()->json($registro->load('user'), 201);
    }

    public function show(RegistroHoraFilial $registros_horas_filial)
    {
        return response()->json($registros_horas_filial->load('user'), 200);
    }

    public function update(Request $request, RegistroHoraFilial $registros_horas_filial)
    {
        $data = $this->validateRegistro($request);
        $originalYear = (int) Carbon::parse($registros_horas_filial->fecha)->year;
        $data['horas_totales'] = $this->calculateHours($data['fecha'], $data['hora_entrada'], $data['hora_salida']);

        $registros_horas_filial->update($data);

        $this->attendanceSheetService->syncForUsers(
            [$registros_horas_filial->user_id],
            [$originalYear, (int) Carbon::parse($registros_horas_filial->fecha)->year]
        );

        return response()->json($registros_horas_filial->load('user'), 200);
    }

    public function destroy(RegistroHoraFilial $registros_horas_filial)
    {
        $userId = $registros_horas_filial->user_id;
        $year = (int) Carbon::parse($registros_horas_filial->fecha)->year;
        $registros_horas_filial->delete();
        $this->attendanceSheetService->syncForUsers([$userId], [$year]);

        return response()->json(null, 204);
    }

    private function validateRegistro(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'fecha' => ['required', 'date'],
            'hora_entrada' => ['required', 'date_format:H:i'],
            'hora_salida' => ['required', 'date_format:H:i'],
        ]);
    }

    private function calculateHours(string $fecha, string $horaEntrada, string $horaSalida): float
    {
        $entrada = Carbon::parse("{$fecha} {$horaEntrada}");
        $salida = Carbon::parse("{$fecha} {$horaSalida}");

        if ($salida->lessThanOrEqualTo($entrada)) {
            throw ValidationException::withMessages([
                'hora_salida' => ['La hora de salida debe ser posterior a la hora de entrada.'],
            ]);
        }

        return round($entrada->diffInMinutes($salida) / 60, 2);
    }
}
