from pathlib import Path
path = Path(r'D:/Escritorio/Miscy/U/Proyecto de titulo/ProyectoDeTitulo/backend/app/Http/Controllers/DocumentoActividadController.php')
text = path.read_text(encoding='utf-8')
old = """    private function buildInitialContent(string $type): array
    {
        $base = [
            'resumen' => '',
            'desarrollo' => '',
            'resultados' => '',
            'dificultades' => '',
            'conclusiones' => '',
            'recomendaciones' => '',
            'observaciones' => '',
        ];

        if ($type === 'analisis_contexto') {
            return array_merge($base, [
                'analisis_contextual' => '',
                'diagnostico' => '',
                'oportunidades' => '',
                'riesgos' => '',
            ]);
        }

        return array_merge($base, [
            'introduccion' => '',
            'metodologia' => '',
            'participacion_comunitaria' => '',
        ]);
    }
"""
new = """    private function buildInitialContent(string $type): array
    {
        if ($type === 'analisis_contexto') {
            return [
                'proposito_documento' => '',
                'descripcion_evento' => [
                    'nombre_evento' => '',
                    'fecha_evento' => '',
                    'horario_evento' => '',
                    'lugar_evento' => '',
                    'participantes_evento' => '',
                    'organizador_evento' => '',
                    'clima_esperado' => '',
                ],
                'riesgos' => [],
                'plan_traslados' => [
                    'coordinacion_samu' => '',
                    'punto_encuentro' => '',
                    'comunicacion_interna' => '',
                    'documentacion_medica' => '',
                ],
                'protocolo_traslado' => [
                    ['titulo' => 'Evaluacion inicial', 'detalle' => ''],
                    ['titulo' => 'Activacion del SAMU', 'detalle' => ''],
                    ['titulo' => 'Estabilizacion', 'detalle' => ''],
                    ['titulo' => 'Comunicacion con familiares', 'detalle' => ''],
                ],
                'centros_salud' => [],
                'conclusion' => '',
                'observaciones_finales' => '',
            ];
        }

        $base = [
            'resumen' => '',
            'desarrollo' => '',
            'resultados' => '',
            'dificultades' => '',
            'conclusiones' => '',
            'recomendaciones' => '',
            'observaciones' => '',
        ];

        return array_merge($base, [
            'introduccion' => '',
            'metodologia' => '',
            'participacion_comunitaria' => '',
        ]);
    }
"""
if old not in text:
    raise SystemExit('target snippet not found in DocumentoActividadController.php')
path.write_text(text.replace(old, new), encoding='utf-8')
