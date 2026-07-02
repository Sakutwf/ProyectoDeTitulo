from pathlib import Path
path = Path(r'D:/Escritorio/Miscy/U/Proyecto de titulo/ProyectoDeTitulo/frontend/src/views/DocumentosView.vue')
text = path.read_text(encoding='utf-8')
old = """function createEmptyNarrativeParticipantRow(tipo = '', numero = null, source = 'manual') {
  return {
    id: nextParticipantId(),
    tipo,
    numero,
    source
  }
}
"""
new = """function createEmptyAnalysisRiskRow(nombre = '', descripcion = '', probabilidad = '', impacto = '', mitigacion = '') {
  return {
    id: nextParticipantId(),
    nombre,
    descripcion,
    probabilidad,
    impacto,
    mitigacion
  }
}

function createEmptyAnalysisCenterRow(nombre = '', detalle = '') {
  return {
    id: nextParticipantId(),
    nombre,
    detalle
  }
}

function createEmptyAnalysisProtocolStep(titulo = '', detalle = '') {
  return {
    id: nextParticipantId(),
    titulo,
    detalle
  }
}

function createAnalysisContent(context = null) {
  const activity = context?.actividad || {}
  const participantTotal = Number(context?.resumen?.total_participantes ?? 0)

  return {
    proposito_documento: '',
    descripcion_evento: {
      nombre_evento: activity.nombre || '',
      fecha_evento: formatDateRange(activity.fecha_inicio, activity.fecha_termino),
      horario_evento: formatActivitySchedule(activity),
      lugar_evento: activity.lugar || '',
      participantes_evento: participantTotal ? String(participantTotal) : '',
      organizador_evento: activity.colaborador_externo || context?.filial?.nombre || '',
      clima_esperado: ''
    },
    riesgos: [createEmptyAnalysisRiskRow()],
    plan_traslados: {
      coordinacion_samu: '',
      punto_encuentro: '',
      comunicacion_interna: '',
      documentacion_medica: ''
    },
    protocolo_traslado: [
      createEmptyAnalysisProtocolStep('Evaluacion inicial'),
      createEmptyAnalysisProtocolStep('Activacion del SAMU'),
      createEmptyAnalysisProtocolStep('Estabilizacion'),
      createEmptyAnalysisProtocolStep('Comunicacion con familiares')
    ],
    centros_salud: [createEmptyAnalysisCenterRow()],
    conclusion: '',
    observaciones_finales: ''
  }
}

function normalizeAnalysisRiskRows(rows, legacyRisk = '') {
  if (Array.isArray(rows) && rows.length) {
    return rows.map((row) => ({
      id: row?.id || nextParticipantId(),
      nombre: row?.nombre || row?.titulo || '',
      descripcion: row?.descripcion || '',
      probabilidad: row?.probabilidad || '',
      impacto: row?.impacto || '',
      mitigacion: row?.mitigacion || row?.medidas_mitigacion || ''
    }))
  }

  if (String(legacyRisk || '').trim()) {
    return [createEmptyAnalysisRiskRow('Riesgo identificado', String(legacyRisk).trim())]
  }

  return [createEmptyAnalysisRiskRow()]
}

function normalizeAnalysisCenterRows(rows) {
  if (!Array.isArray(rows) || !rows.length) {
    return [createEmptyAnalysisCenterRow()]
  }

  return rows.map((row) => ({
    id: row?.id || nextParticipantId(),
    nombre: row?.nombre || '',
    detalle: row?.detalle || ''
  }))
}

function normalizeAnalysisProtocolSteps(rows, fallbackRows = []) {
  const source = Array.isArray(rows) && rows.length ? rows : fallbackRows

  return source.map((row) => ({
    id: row?.id || nextParticipantId(),
    titulo: row?.titulo || '',
    detalle: row?.detalle || ''
  }))
}

function joinAnalysisLegacyNotes(incoming) {
  const blocks = [
    ['Diagnostico', incoming?.diagnostico],
    ['Oportunidades', incoming?.oportunidades],
    ['Desarrollo', incoming?.desarrollo],
    ['Resultados', incoming?.resultados],
    ['Recomendaciones', incoming?.recomendaciones],
    ['Observaciones', incoming?.observaciones]
  ]

  return blocks
    .filter(([, value]) => String(value || '').trim())
    .map(([label, value]) => label + ':\n' + String(value).trim())
    .join('\n\n')
}

function normalizeAnalysisContent(content, context = null) {
  const defaults = createAnalysisContent(context)
  const incoming = content && typeof content === 'object' ? content : {}
  const mergedDescription = {
    ...defaults.descripcion_evento,
    ...(incoming.descripcion_evento && typeof incoming.descripcion_evento === 'object' ? incoming.descripcion_evento : {})
  }
  const mergedPlan = {
    ...defaults.plan_traslados,
    ...(incoming.plan_traslados && typeof incoming.plan_traslados === 'object' ? incoming.plan_traslados : {})
  }
  const legacyIntro = [incoming.resumen, incoming.analisis_contextual]
    .filter((value) => String(value || '').trim())
    .map((value) => String(value).trim())
    .join('\n\n')
  const legacyNotes = joinAnalysisLegacyNotes(incoming)

  return {
    proposito_documento: typeof incoming.proposito_documento === 'string' && incoming.proposito_documento.trim()
      ? incoming.proposito_documento
      : (legacyIntro || defaults.proposito_documento),
    descripcion_evento: {
      nombre_evento: String(mergedDescription.nombre_evento || defaults.descripcion_evento.nombre_evento),
      fecha_evento: String(mergedDescription.fecha_evento || defaults.descripcion_evento.fecha_evento),
      horario_evento: String(mergedDescription.horario_evento || defaults.descripcion_evento.horario_evento),
      lugar_evento: String(mergedDescription.lugar_evento || defaults.descripcion_evento.lugar_evento),
      participantes_evento: String(mergedDescription.participantes_evento || defaults.descripcion_evento.participantes_evento),
      organizador_evento: String(mergedDescription.organizador_evento || defaults.descripcion_evento.organizador_evento),
      clima_esperado: String(mergedDescription.clima_esperado || defaults.descripcion_evento.clima_esperado)
    },
    riesgos: normalizeAnalysisRiskRows(incoming.riesgos, typeof incoming.riesgos === 'string' ? incoming.riesgos : ''),
    plan_traslados: {
      coordinacion_samu: String(mergedPlan.coordinacion_samu || defaults.plan_traslados.coordinacion_samu),
      punto_encuentro: String(mergedPlan.punto_encuentro || defaults.plan_traslados.punto_encuentro),
      comunicacion_interna: String(mergedPlan.comunicacion_interna || defaults.plan_traslados.comunicacion_interna),
      documentacion_medica: String(mergedPlan.documentacion_medica || defaults.plan_traslados.documentacion_medica)
    },
    protocolo_traslado: normalizeAnalysisProtocolSteps(incoming.protocolo_traslado, defaults.protocolo_traslado),
    centros_salud: normalizeAnalysisCenterRows(incoming.centros_salud),
    conclusion: typeof incoming.conclusion === 'string' && incoming.conclusion.trim()
      ? incoming.conclusion
      : (typeof incoming.conclusiones === 'string' ? incoming.conclusiones : defaults.conclusion),
    observaciones_finales: typeof incoming.observaciones_finales === 'string' && incoming.observaciones_finales.trim()
      ? incoming.observaciones_finales
      : legacyNotes
  }
}

function ensureAnalysisContent() {
  if (!isContextAnalysisType.value) {
    return null
  }

  const normalized = normalizeAnalysisContent(form.contenido, form.datos_contexto)
  form.contenido = normalized
  return normalized
}

function createEmptyNarrativeParticipantRow(tipo = '', numero = null, source = 'manual') {
  return {
    id: nextParticipantId(),
    tipo,
    numero,
    source
  }
}
"""
if old not in text:
    raise SystemExit('No se encontro el punto de insercion de helpers de analisis')
path.write_text(text.replace(old, new), encoding='utf-8')
