from pathlib import Path
path = Path(r'D:/Escritorio/Miscy/U/Proyecto de titulo/ProyectoDeTitulo/frontend/src/views/DocumentosView.vue')
text = path.read_text(encoding='utf-8')
old = """function handleActividadClickOutside(event) {
  if (actividadComboboxRef.value && !actividadComboboxRef.value.contains(event.target)) {
    closeActividadMenu()
  }
}
"""
new = old + """

function addAnalysisRiskRow() {
  const analysis = ensureAnalysisContent()
  analysis.riesgos.push(createEmptyAnalysisRiskRow())
}

function removeAnalysisRiskRow(index) {
  const analysis = ensureAnalysisContent()
  analysis.riesgos.splice(index, 1)

  if (!analysis.riesgos.length) {
    analysis.riesgos.push(createEmptyAnalysisRiskRow())
  }
}

function addAnalysisProtocolStep() {
  const analysis = ensureAnalysisContent()
  analysis.protocolo_traslado.push(createEmptyAnalysisProtocolStep())
}

function removeAnalysisProtocolStep(index) {
  const analysis = ensureAnalysisContent()
  analysis.protocolo_traslado.splice(index, 1)

  if (!analysis.protocolo_traslado.length) {
    analysis.protocolo_traslado.push(createEmptyAnalysisProtocolStep())
  }
}

function addAnalysisCenterRow() {
  const analysis = ensureAnalysisContent()
  analysis.centros_salud.push(createEmptyAnalysisCenterRow())
}

function removeAnalysisCenterRow(index) {
  const analysis = ensureAnalysisContent()
  analysis.centros_salud.splice(index, 1)

  if (!analysis.centros_salud.length) {
    analysis.centros_salud.push(createEmptyAnalysisCenterRow())
  }
}
"""
if old not in text:
    raise SystemExit('No se encontro el punto de insercion de handlers de analisis')
text = text.replace(old, new)
old = """async function saveDocument(targetState) {
  if (!canSubmit.value) {
    Swal.fire('Faltan datos', 'Selecciona una actividad y completa al menos el titulo del documento.', 'warning')
    return
  }

  if (isNarrativeType.value && targetState === 'final' && !validateNarrativeFinalContent()) {
    return
  }

  await persistDocument(targetState)
}

async function exportNarrativePdf() {
  if (!canSubmit.value) {
    Swal.fire('Faltan datos', 'Selecciona una actividad y completa al menos el titulo del documento.', 'warning')
    return
  }

  const saved = await persistDocument('final', {
    showSuccess: false
  })

  if (!saved?.id) {
    return
  }

  await router.push({
    name: 'DocumentosPdfView',
    params: { id: saved.id }
  })
}

async function handlePrimaryDocumentAction() {
  if (isNarrativeType.value) {
    await exportNarrativePdf()
    return
  }

  await saveDocument('final')
}
"""
new = """async function saveDocument(targetState) {
  if (!canSubmit.value) {
    Swal.fire('Faltan datos', 'Selecciona una actividad y completa al menos el titulo del documento.', 'warning')
    return
  }

  if (targetState === 'final') {
    const validatedContent = isNarrativeType.value
      ? validateNarrativeFinalContent()
      : isContextAnalysisType.value
        ? ensureAnalysisContent()
        : form.contenido

    if (!validatedContent) {
      return
    }

    form.contenido = validatedContent
  }

  await persistDocument(targetState)
}

function validateDocumentForPdfExport() {
  if (!canSubmit.value) {
    Swal.fire('Faltan datos', 'Selecciona una actividad y completa al menos el titulo del documento.', 'warning')
    return null
  }

  if (isNarrativeType.value) {
    return validateNarrativeFinalContent()
  }

  if (isContextAnalysisType.value) {
    return ensureAnalysisContent()
  }

  return form.contenido
}

async function exportDocumentPdf() {
  const validatedContent = validateDocumentForPdfExport()

  if (!validatedContent) {
    return
  }

  form.contenido = validatedContent

  const saved = await persistDocument('final', {
    showSuccess: false
  })

  if (!saved?.id) {
    return
  }

  await router.push({
    name: 'DocumentosPdfView',
    params: { id: saved.id }
  })
}

async function handlePrimaryDocumentAction() {
  await exportDocumentPdf()
}
"""
if old not in text:
    raise SystemExit('No se encontro el bloque de exportacion a reemplazar')
text = text.replace(old, new)
path.write_text(text, encoding='utf-8')
