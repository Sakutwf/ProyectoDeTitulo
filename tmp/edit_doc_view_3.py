from pathlib import Path
path = Path(r'D:/Escritorio/Miscy/U/Proyecto de titulo/ProyectoDeTitulo/frontend/src/views/DocumentosView.vue')
text = path.read_text(encoding='utf-8')
repls = [
("""  if (selectedActividad.value && normalizeActividadSearch(activitySearchTerm.value) !== normalizeActividadSearch(actividadLabel(selectedActividad.value))) {
    selectedActividadId.value = ''
    currentSavedDocumentId.value = null
    form.datos_contexto = null
    documentosGuardados.value = []
    if (isNarrativeType.value) {
      form.contenido = createNarrativeContent()
    }
  }
""", """  if (selectedActividad.value && normalizeActividadSearch(activitySearchTerm.value) !== normalizeActividadSearch(actividadLabel(selectedActividad.value))) {
    selectedActividadId.value = ''
    currentSavedDocumentId.value = null
    form.datos_contexto = null
    documentosGuardados.value = []
    form.contenido = emptyContent(selectedType.value)
  }
"""),
("""function emptyContent(type) {
  if (type === 'informe_narrativo') {
    return createNarrativeContent(form.datos_contexto)
  }

  const meta = documentTypes.find((item) => item.key === type)
  return (meta?.sections || []).reduce((accumulator, section) => {
    accumulator[section.key] = ''
    return accumulator
  }, {})
}
""", """function emptyContent(type) {
  if (type === 'informe_narrativo') {
    return createNarrativeContent(form.datos_contexto)
  }

  if (type === 'analisis_contexto') {
    return createAnalysisContent(form.datos_contexto)
  }

  const meta = documentTypes.find((item) => item.key === type)
  return (meta?.sections || []).reduce((accumulator, section) => {
    accumulator[section.key] = ''
    return accumulator
  }, {})
}
"""),
("""  form.contenido = type === 'informe_narrativo'
    ? normalizeNarrativeContent(emptyContent(type))
    : emptyContent(type)
""", """  form.contenido = type === 'informe_narrativo'
    ? normalizeNarrativeContent(emptyContent(type), form.datos_contexto)
    : type === 'analisis_contexto'
      ? normalizeAnalysisContent(emptyContent(type), form.datos_contexto)
      : emptyContent(type)
"""),
("""  form.contenido = isNarrativeType.value
    ? normalizeNarrativeContent(documento.contenido || {}, form.datos_contexto)
    : {
        ...emptyContent(selectedType.value),
        ...(documento.contenido || {})
      }
""", """  form.contenido = isNarrativeType.value
    ? normalizeNarrativeContent(documento.contenido || {}, form.datos_contexto)
    : isContextAnalysisType.value
      ? normalizeAnalysisContent(documento.contenido || {}, form.datos_contexto)
      : {
          ...emptyContent(selectedType.value),
          ...(documento.contenido || {})
        }
"""),
("""    form.contenido = selectedType.value === 'informe_narrativo'
      ? normalizeNarrativeContent(response.data.contenido_inicial || {}, response.data.prefill || null)
      : {
          ...emptyContent(selectedType.value),
          ...(response.data.contenido_inicial || {})
        }
""", """    form.contenido = selectedType.value === 'informe_narrativo'
      ? normalizeNarrativeContent(response.data.contenido_inicial || {}, response.data.prefill || null)
      : selectedType.value === 'analisis_contexto'
        ? normalizeAnalysisContent(response.data.contenido_inicial || {}, response.data.prefill || null)
        : {
            ...emptyContent(selectedType.value),
            ...(response.data.contenido_inicial || {})
          }
"""),
("""    const payload = {
      tipo_documento: selectedType.value,
      titulo: form.titulo.trim(),
      estado: targetState,
      fecha_documento: form.fecha_documento || null,
      datos_contexto: form.datos_contexto,
      contenido: form.contenido
    }
""", """    const normalizedContent = isNarrativeType.value
      ? ensureNarrativeContent()
      : isContextAnalysisType.value
        ? ensureAnalysisContent()
        : form.contenido

    const payload = {
      tipo_documento: selectedType.value,
      titulo: form.titulo.trim(),
      estado: targetState,
      fecha_documento: form.fecha_documento || null,
      datos_contexto: form.datos_contexto,
      contenido: normalizedContent
    }
""")
]
for old, new in repls:
    if old not in text:
        raise SystemExit('No se encontro un bloque esperado para reemplazar')
    text = text.replace(old, new)
path.write_text(text, encoding='utf-8')
