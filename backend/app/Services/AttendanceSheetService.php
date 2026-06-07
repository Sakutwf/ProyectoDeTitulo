<?php

namespace App\Services;

use App\Enums\EventoTipo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceSheetService
{
    public function syncForUsers(iterable $userIds, ?iterable $years = null): void
    {
        $normalizedYears = $years === null
            ? null
            : collect($years)
                ->filter()
                ->map(fn ($year) => (int) $year)
                ->unique()
                ->values();

        collect($userIds)
            ->filter()
            ->map(fn ($userId) => (int) $userId)
            ->unique()
            ->each(fn (int $userId) => $this->syncForUserId($userId, $normalizedYears));
    }

    public function syncForUserId(int $userId, ?Collection $years = null): void
    {
        $user = User::with([
            'voluntario.hojaDeVida.hojasAnuales',
            'actividades' => fn ($query) => $query->with('evento'),
            'registrosHorasFilial',
        ])->find($userId);

        if (! $user || ! $user->voluntario?->hojaDeVida) {
            return;
        }

        $hojaDeVida = $user->voluntario->hojaDeVida;
        $percentagesByYear = $this->calculatePercentagesByYear($user);

        foreach ($percentagesByYear as $anio => $porcentaje) {
            $hojaDeVida->hojasAnuales()->updateOrCreate(
                ['anio' => $anio],
                ['porcentaje_asistencia' => $porcentaje],
            );
        }

        if ($years !== null) {
            foreach ($years as $anio) {
                if ($percentagesByYear->has($anio)) {
                    continue;
                }

                $hojaDeVida->hojasAnuales()
                    ->where('anio', $anio)
                    ->update(['porcentaje_asistencia' => null]);
            }

            return;
        }

        $query = $hojaDeVida->hojasAnuales();

        if ($percentagesByYear->isEmpty()) {
            $query->update(['porcentaje_asistencia' => null]);

            return;
        }

        $query
            ->whereNotIn('anio', $percentagesByYear->keys()->all())
            ->update(['porcentaje_asistencia' => null]);
    }

    private function calculatePercentagesByYear(User $user): Collection
    {
        $activityHoursByYear = $user->actividades
            ->filter(fn ($actividad) => filled($actividad->evento?->fecha_inicio))
            ->filter(fn ($actividad) => EventoTipo::tryFromMixed($actividad->evento?->tipo) === EventoTipo::SERVICIO)
            ->groupBy(fn ($actividad) => Carbon::parse($actividad->evento->fecha_inicio)->year)
            ->map(function (Collection $actividades) {
                return [
                    'horas_programadas' => (float) $actividades->sum(fn ($actividad) => (float) ($actividad->horas_participacion ?? 0)),
                    'horas_cumplidas' => (float) $actividades
                        ->filter(fn ($actividad) => (bool) ($actividad->pivot->asistio ?? false))
                        ->sum(fn ($actividad) => (float) ($actividad->horas_participacion ?? 0)),
                ];
            })
            ->sortKeys();

        $filialHoursByYear = $user->registrosHorasFilial
            ->filter(fn ($registro) => filled($registro->fecha))
            ->groupBy(fn ($registro) => Carbon::parse($registro->fecha)->year)
            ->map(fn (Collection $registros) => (float) $registros->sum(fn ($registro) => (float) ($registro->horas_totales ?? 0)));

        return $activityHoursByYear
            ->keys()
            ->merge($filialHoursByYear->keys())
            ->unique()
            ->sort()
            ->values()
            ->mapWithKeys(function ($year) use ($activityHoursByYear, $filialHoursByYear) {
                $activityHours = $activityHoursByYear->get($year, [
                    'horas_programadas' => 0,
                    'horas_cumplidas' => 0,
                ]);
                $horasFilial = (float) ($filialHoursByYear->get($year, 0));
                $horasEsperadas = (float) $activityHours['horas_programadas'] + $horasFilial;
                $horasCumplidas = (float) $activityHours['horas_cumplidas'] + $horasFilial;

                if ($horasEsperadas <= 0) {
                    return [$year => null];
                }

                return [$year => round(($horasCumplidas / $horasEsperadas) * 100, 2)];
            })
            ->filter(fn ($porcentaje) => $porcentaje !== null);
    }
}
