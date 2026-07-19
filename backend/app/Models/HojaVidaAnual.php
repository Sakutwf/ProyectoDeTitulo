<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HojaVidaAnual extends Model
{
    public const REQUIRED_ANNUAL_HOURS = 288.0;

    private const ACTIVITY_TYPE_REUNION = 'Reunion';
    private const ACTIVITY_TYPE_FORMACION = 'Formación';
    private const ACTIVITY_TYPE_OPERATIVA = 'Operativa';
    private const ACTIVITY_TYPE_EN_FILIAL = 'En filial';

    protected $table = 'hoja_vida_anual';

    protected $fillable = [
        'voluntario_id',
        'anio',
        'asistencia_anual_horas',
        'asistencia_anual_porcentaje',
        'asistencia_anual_ajuste_horas',
        'asistencia_reuniones_filial_ajuste_horas',
        'asistencia_actividades_voluntariado_ajuste_horas',
        'asistencia_horas_filial_ajuste_horas',
        'asistencia_horas_formativas_ajuste_horas',
        'cargo_clave',
        'cargo_nombre',
        'cargo_grupo',
        'cargo_direccion',
        'estuvo_comision_servicio',
        'comision_fecha_inicio',
        'comision_fecha_termino',
        'comision_lugar',
        'comision_actividad',
        'comentarios',
        'generada_por',
        'fecha_generacion',
        'ruta_pdf',
    ];

    protected $appends = [
        'asistencia_reuniones_filial_horas',
        'asistencia_reuniones_filial_horas_base',
        'asistencia_actividades_voluntariado_horas',
        'asistencia_actividades_voluntariado_horas_base',
        'asistencia_horas_formativas_horas',
        'asistencia_horas_formativas_horas_base',
        'asistencia_en_filial_horas',
        'asistencia_en_filial_horas_base',
        'asistencia_actividades_formativas_operativas_horas',
        'asistencia_total_periodo_horas',
        'asistencia_total_ajustes_horas',
        'asistencia_anual_horas_base',
        'asistencia_anual_horas_requeridas',
        'generada_por_nombre',
    ];

    private ?array $attendanceMetricsCache = null;

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'asistencia_anual_horas' => 'decimal:2',
            'asistencia_anual_porcentaje' => 'decimal:2',
            'asistencia_anual_ajuste_horas' => 'decimal:2',
            'asistencia_reuniones_filial_ajuste_horas' => 'decimal:2',
            'asistencia_actividades_voluntariado_ajuste_horas' => 'decimal:2',
            'asistencia_horas_filial_ajuste_horas' => 'decimal:2',
            'asistencia_horas_formativas_ajuste_horas' => 'decimal:2',
            'estuvo_comision_servicio' => 'boolean',
            'comision_fecha_inicio' => 'date',
            'comision_fecha_termino' => 'date',
            'fecha_generacion' => 'date',
        ];
    }

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class);
    }

    public function titulos()
    {
        return $this->hasMany(TituloVoluntario::class, 'hoja_vida_anual_id');
    }

    public function cursos()
    {
        return $this->hasMany(CursoVoluntario::class, 'hoja_vida_anual_id');
    }

    public function solicitudes()
    {
        return $this->hasMany(SolicitudHojaVida::class, 'hoja_vida_anual_id');
    }

    public function otrosDocumentos()
    {
        return $this->hasMany(OtroDocumentoVoluntario::class, 'hoja_vida_anual_id');
    }

    public function sanciones()
    {
        return $this->hasMany(SancionVoluntario::class, 'hoja_vida_anual_id');
    }

    public function reconocimiento()
    {
        return $this->hasOne(ReconocimientoVoluntario::class, 'hoja_vida_anual_id');
    }

    public function generador()
    {
        return $this->belongsTo(User::class, 'generada_por');
    }

    public static function calculateAttendanceMetricsForVoluntario(
        ?int $voluntarioId,
        ?int $anio,
        array $adjustments = [],
        ?float $unclassifiedHours = null
    ): array {
        $normalizedAdjustments = self::normalizeAttendanceAdjustments($adjustments);
        $normalizedUnclassifiedHours = self::normalizeUnclassifiedHours($unclassifiedHours);

        if (! $voluntarioId || ! $anio) {
            return self::composeAttendanceMetrics([
                HojaVidaAnualAjusteHora::TYPE_REUNIONES_FILIAL => 0.0,
                HojaVidaAnualAjusteHora::TYPE_ACTIVIDADES_VOLUNTARIADO => 0.0,
                HojaVidaAnualAjusteHora::TYPE_HORAS_FILIAL => 0.0,
                HojaVidaAnualAjusteHora::TYPE_HORAS_FORMATIVAS => 0.0,
            ], $normalizedAdjustments, $normalizedUnclassifiedHours);
        }

        $totals = DB::table('actividad_voluntario as actividad_voluntario')
            ->join('actividades as actividades', 'actividades.id', '=', 'actividad_voluntario.actividad_id')
            ->where('actividad_voluntario.voluntario_id', $voluntarioId)
            ->where('actividad_voluntario.estado', Actividad::INSCRIPCION_APROBADA)
            ->whereYear('actividades.fecha_inicio', $anio)
            ->whereIn('actividades.tipo', [
                self::ACTIVITY_TYPE_REUNION,
                self::ACTIVITY_TYPE_FORMACION,
                self::ACTIVITY_TYPE_OPERATIVA,
                self::ACTIVITY_TYPE_EN_FILIAL,
            ])
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN actividades.tipo = ? THEN actividad_voluntario.horas_asistidas ELSE 0 END), 0) as reuniones',
                [self::ACTIVITY_TYPE_REUNION]
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN actividades.tipo = ? THEN actividad_voluntario.horas_asistidas ELSE 0 END), 0) as voluntariado',
                [self::ACTIVITY_TYPE_OPERATIVA]
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN actividades.tipo = ? THEN actividad_voluntario.horas_asistidas ELSE 0 END), 0) as filial',
                [self::ACTIVITY_TYPE_EN_FILIAL]
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN actividades.tipo = ? THEN actividad_voluntario.horas_asistidas ELSE 0 END), 0) as formativas',
                [self::ACTIVITY_TYPE_FORMACION]
            )
            ->first();

        return self::composeAttendanceMetrics([
            HojaVidaAnualAjusteHora::TYPE_REUNIONES_FILIAL => (float) ($totals->reuniones ?? 0),
            HojaVidaAnualAjusteHora::TYPE_ACTIVIDADES_VOLUNTARIADO => (float) ($totals->voluntariado ?? 0),
            HojaVidaAnualAjusteHora::TYPE_HORAS_FILIAL => (float) ($totals->filial ?? 0),
            HojaVidaAnualAjusteHora::TYPE_HORAS_FORMATIVAS => (float) ($totals->formativas ?? 0),
        ], $normalizedAdjustments, $normalizedUnclassifiedHours);
    }

    public function getAsistenciaAnualHorasAttribute($value): float
    {
        return $this->attendanceMetrics()['total'];
    }

    public function getAsistenciaAnualPorcentajeAttribute($value): float
    {
        return $this->attendanceMetrics()['porcentaje'];
    }

    public function getAsistenciaReunionesFilialHorasAttribute(): float
    {
        return $this->attendanceMetrics()['reuniones'];
    }

    public function getAsistenciaReunionesFilialHorasBaseAttribute(): float
    {
        return $this->attendanceMetrics()['reuniones_base'];
    }

    public function getAsistenciaActividadesVoluntariadoHorasAttribute(): float
    {
        return $this->attendanceMetrics()['voluntariado'];
    }

    public function getAsistenciaActividadesVoluntariadoHorasBaseAttribute(): float
    {
        return $this->attendanceMetrics()['voluntariado_base'];
    }

    public function getAsistenciaHorasFormativasHorasAttribute(): float
    {
        return $this->attendanceMetrics()['formativas'];
    }

    public function getAsistenciaHorasFormativasHorasBaseAttribute(): float
    {
        return $this->attendanceMetrics()['formativas_base'];
    }

    public function getAsistenciaEnFilialHorasAttribute(): float
    {
        return $this->attendanceMetrics()['filial'];
    }

    public function getAsistenciaEnFilialHorasBaseAttribute(): float
    {
        return $this->attendanceMetrics()['filial_base'];
    }

    public function getAsistenciaActividadesFormativasOperativasHorasAttribute(): float
    {
        return round($this->attendanceMetrics()['voluntariado'] + $this->attendanceMetrics()['formativas'], 2);
    }

    public function getAsistenciaTotalPeriodoHorasAttribute(): float
    {
        return $this->attendanceMetrics()['base'];
    }

    public function getAsistenciaTotalAjustesHorasAttribute(): float
    {
        return $this->attendanceMetrics()['ajustes'];
    }

    public function getAsistenciaAnualHorasBaseAttribute(): float
    {
        return $this->attendanceMetrics()['base'];
    }

    public function getAsistenciaAnualHorasRequeridasAttribute(): float
    {
        return $this->attendanceMetrics()['requeridas'];
    }

    public function getGeneradaPorNombreAttribute(): string
    {
        $generador = $this->relationLoaded('generador')
            ? $this->getRelation('generador')
            : $this->generador()->first();

        if (! $generador) {
            return 'Administrador';
        }

        $nombre = trim(collect([
            $generador->nombre ?? null,
            $generador->segundo_nombre ?? null,
            $generador->apellido_paterno ?? null,
            $generador->apellido_materno ?? null,
        ])->filter()->implode(' '));

        if ($nombre !== '') {
            return $nombre;
        }

        return $generador->username ?: 'Administrador';
    }

    private function attendanceMetrics(): array
    {
        if ($this->attendanceMetricsCache === null) {
            $this->attendanceMetricsCache = self::calculateAttendanceMetricsForVoluntario(
                $this->voluntario_id ? (int) $this->voluntario_id : null,
                $this->anio ? (int) $this->anio : null,
                $this->attendanceAdjustments(),
                (float) ($this->getAttribute('asistencia_anual_ajuste_horas') ?? 0)
            );
        }

        return $this->attendanceMetricsCache;
    }

    private function attendanceAdjustments(): array
    {
        return self::normalizeAttendanceAdjustments([
            HojaVidaAnualAjusteHora::TYPE_REUNIONES_FILIAL => (float) ($this->getAttribute('asistencia_reuniones_filial_ajuste_horas') ?? 0),
            HojaVidaAnualAjusteHora::TYPE_ACTIVIDADES_VOLUNTARIADO => (float) ($this->getAttribute('asistencia_actividades_voluntariado_ajuste_horas') ?? 0),
            HojaVidaAnualAjusteHora::TYPE_HORAS_FILIAL => (float) ($this->getAttribute('asistencia_horas_filial_ajuste_horas') ?? 0),
            HojaVidaAnualAjusteHora::TYPE_HORAS_FORMATIVAS => (float) ($this->getAttribute('asistencia_horas_formativas_ajuste_horas') ?? 0),
        ]);
    }

    private static function normalizeAttendanceAdjustments(array $adjustments): array
    {
        return [
            HojaVidaAnualAjusteHora::TYPE_REUNIONES_FILIAL => round((float) ($adjustments[HojaVidaAnualAjusteHora::TYPE_REUNIONES_FILIAL] ?? 0), 2),
            HojaVidaAnualAjusteHora::TYPE_ACTIVIDADES_VOLUNTARIADO => round((float) ($adjustments[HojaVidaAnualAjusteHora::TYPE_ACTIVIDADES_VOLUNTARIADO] ?? 0), 2),
            HojaVidaAnualAjusteHora::TYPE_HORAS_FILIAL => round((float) ($adjustments[HojaVidaAnualAjusteHora::TYPE_HORAS_FILIAL] ?? 0), 2),
            HojaVidaAnualAjusteHora::TYPE_HORAS_FORMATIVAS => round((float) ($adjustments[HojaVidaAnualAjusteHora::TYPE_HORAS_FORMATIVAS] ?? 0), 2),
        ];
    }

    private static function normalizeUnclassifiedHours(?float $hours): float
    {
        return round(max((float) ($hours ?? 0), 0), 2);
    }

    private static function composeAttendanceMetrics(array $baseHours, array $adjustments, float $unclassifiedHours = 0.0): array
    {
        $reunionesBase = round((float) ($baseHours[HojaVidaAnualAjusteHora::TYPE_REUNIONES_FILIAL] ?? 0), 2);
        $voluntariadoBase = round((float) ($baseHours[HojaVidaAnualAjusteHora::TYPE_ACTIVIDADES_VOLUNTARIADO] ?? 0), 2);
        $filialBase = round((float) ($baseHours[HojaVidaAnualAjusteHora::TYPE_HORAS_FILIAL] ?? 0), 2);
        $formativasBase = round((float) ($baseHours[HojaVidaAnualAjusteHora::TYPE_HORAS_FORMATIVAS] ?? 0), 2);

        $reuniones = round(max($reunionesBase + $adjustments[HojaVidaAnualAjusteHora::TYPE_REUNIONES_FILIAL], 0), 2);
        $voluntariado = round(max($voluntariadoBase + $adjustments[HojaVidaAnualAjusteHora::TYPE_ACTIVIDADES_VOLUNTARIADO], 0), 2);
        $filial = round(max($filialBase + $adjustments[HojaVidaAnualAjusteHora::TYPE_HORAS_FILIAL], 0), 2);
        $formativas = round(max($formativasBase + $adjustments[HojaVidaAnualAjusteHora::TYPE_HORAS_FORMATIVAS], 0), 2);

        $base = round($reunionesBase + $voluntariadoBase + $filialBase + $formativasBase, 2);
        $totalClasificadas = round($reuniones + $voluntariado + $filial + $formativas, 2);
        $total = round($totalClasificadas + $unclassifiedHours, 2);
        $ajustes = round(
            $adjustments[HojaVidaAnualAjusteHora::TYPE_REUNIONES_FILIAL]
            + $adjustments[HojaVidaAnualAjusteHora::TYPE_ACTIVIDADES_VOLUNTARIADO]
            + $adjustments[HojaVidaAnualAjusteHora::TYPE_HORAS_FILIAL]
            + $adjustments[HojaVidaAnualAjusteHora::TYPE_HORAS_FORMATIVAS],
            2
        );
        $porcentaje = self::REQUIRED_ANNUAL_HOURS > 0
            ? round(min(($total / self::REQUIRED_ANNUAL_HOURS) * 100, 100), 2)
            : 0.0;

        return [
            'reuniones_base' => $reunionesBase,
            'voluntariado_base' => $voluntariadoBase,
            'filial_base' => $filialBase,
            'formativas_base' => $formativasBase,
            'reuniones' => $reuniones,
            'voluntariado' => $voluntariado,
            'filial' => $filial,
            'formativas' => $formativas,
            'ajustes' => $ajustes,
            'base' => $base,
            'unclassified' => $unclassifiedHours,
            'total' => $total,
            'porcentaje' => $porcentaje,
            'requeridas' => self::REQUIRED_ANNUAL_HOURS,
        ];
    }
}
