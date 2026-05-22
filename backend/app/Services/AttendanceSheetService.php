<?php

namespace App\Services;

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
        return $user->actividades
            ->filter(fn ($actividad) => filled($actividad->evento?->fecha_inicio))
            ->groupBy(fn ($actividad) => Carbon::parse($actividad->evento->fecha_inicio)->year)
            ->map(function (Collection $actividades) {
                $totalActividades = $actividades->count();

                if ($totalActividades === 0) {
                    return null;
                }

                $asistencias = $actividades
                    ->filter(fn ($actividad) => (bool) ($actividad->pivot->asistio ?? false))
                    ->count();

                return round(($asistencias / $totalActividades) * 100, 2);
            })
            ->filter(fn ($porcentaje) => $porcentaje !== null)
            ->sortKeys();
    }
}
