from pathlib import Path

path = Path(r'frontend/src/views/DocumentoActividadPdfView.vue')
text = path.read_text(encoding='utf-8')

text = text.replace('const analysisRisksPerPage = 2', 'const analysisRisksPerPage = 3', 1)

old_computed = '''const narrativePhotoPages = computed(() => chunkItems(narrativePhotos.value, narrativePhotosPerPage))
const analysisClimatePages = computed(() => {
  const chunks = chunkItems(climateRows.value, analysisClimatesPerPage)
  return chunks.length ? chunks : [[]]
})
const analysisRiskPages = computed(() => {
  const chunks = chunkItems(analysisRisks.value, analysisRisksPerPage)
  return chunks.length ? chunks : [[]]
})
const narrativeTotalPages = computed(() => 2 + Math.max(1, narrativePhotoPages.value.length))
const analysisTotalPages = computed(() => 2 + analysisClimatePages.value.length + analysisRiskPages.value.length)
'''
new_computed = '''const narrativePhotoPages = computed(() => chunkItems(narrativePhotos.value, narrativePhotosPerPage))
const primaryClimate = computed(() => climateRows.value[0] || null)
const remainingClimateRows = computed(() => climateRows.value.slice(1))
const analysisClimatePages = computed(() => {
  const chunks = chunkItems(remainingClimateRows.value, analysisClimatesPerPage)
  return chunks.length ? chunks : []
})
const analysisRiskPages = computed(() => {
  const chunks = chunkItems(analysisRisks.value, analysisRisksPerPage)
  return chunks.length ? chunks : [[]]
})
const narrativeTotalPages = computed(() => 2 + Math.max(1, narrativePhotoPages.value.length))
const analysisTotalPages = computed(() => 2 + analysisClimatePages.value.length + analysisRiskPages.value.length)
'''
if old_computed not in text:
    raise SystemExit('No se encontró el bloque de computed del análisis.')
text = text.replace(old_computed, new_computed, 1)

old_first_page = '''            <section class="print-section narrative-block analysis-section analysis-section--compact">
              <div class="analysis-section__heading">DESCRIPCION DEL EVENTO</div>
              <div class="analysis-event-grid analysis-event-grid--compact">
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">NOMBRE DEL EVENTO</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.nombre_evento || actividad.nombre || 'Sin nombre' }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">FECHA</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.fecha_evento || activityDateLongLabel }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">HORARIO</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.horario_evento || activitySchedule }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">LUGAR</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.lugar_evento || primaryLocation }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">PARTICIPANTES</div>
                  <div class="analysis-event-card__value analysis-event-card__value--strong">{{ analysisEvent.participantes_evento || 'Sin participantes estimados' }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">ORGANIZADOR</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.organizador_evento || 'Sin organizador registrado' }}</div>
                </div>
              </div>
            </section>
          </div>
'''
new_first_page = '''            <section class="print-section narrative-block analysis-section analysis-section--compact">
              <div class="analysis-section__heading">DESCRIPCION DEL EVENTO</div>
              <div class="analysis-event-grid analysis-event-grid--compact">
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">NOMBRE DEL EVENTO</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.nombre_evento || actividad.nombre || 'Sin nombre' }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">FECHA</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.fecha_evento || activityDateLongLabel }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">HORARIO</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.horario_evento || activitySchedule }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">LUGAR</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.lugar_evento || primaryLocation }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">PARTICIPANTES</div>
                  <div class="analysis-event-card__value analysis-event-card__value--strong">{{ analysisEvent.participantes_evento || 'Sin participantes estimados' }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">ORGANIZADOR</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.organizador_evento || 'Sin organizador registrado' }}</div>
                </div>
              </div>
            </section>

            <section v-if="primaryClimate" class="print-section analysis-section analysis-section--paired-climate">
              <div class="analysis-section__heading">CLIMA ESPERADO</div>
              <div class="analysis-climate-card analysis-climate-card--compact">
                <div class="analysis-climate-card__meta analysis-climate-card__meta--inline">
                  <div class="analysis-climate-card__metric">
                    <div class="analysis-climate-card__label">MINIMA:</div>
                    <div class="analysis-climate-card__value">{{ formatTemperature(primaryClimate.temperatura_minima) }}</div>
                  </div>
                  <div class="analysis-climate-card__metric">
                    <div class="analysis-climate-card__label">MAXIMA:</div>
                    <div class="analysis-climate-card__value">{{ formatTemperature(primaryClimate.temperatura_maxima) }}</div>
                  </div>
                  <div class="analysis-climate-card__metric">
                    <div class="analysis-climate-card__label">TIPO:</div>
                    <div class="analysis-climate-card__value">{{ primaryClimate.tipo_clima || 'Sin descripción' }}</div>
                  </div>
                </div>
                <div class="analysis-climate-card__image-wrap analysis-climate-card__image-wrap--compact">
                  <img v-if="primaryClimate.imagen_url" :src="primaryClimate.imagen_url" :alt="primaryClimate.titulo_imagen || 'Clima esperado'" class="analysis-climate-card__image analysis-climate-card__image--compact">
                  <div v-else class="analysis-climate-card__empty">Sin imagen climática registrada.</div>
                </div>
              </div>
            </section>
          </div>
'''
if old_first_page not in text:
    raise SystemExit('No se encontró el bloque inicial para insertar el clima.')
text = text.replace(old_first_page, new_first_page, 1)

text = text.replace("<footer class=\"page-footer\">{{ analysisPageNumber(pageIndex + 2 + analysisClimatePages.length) }}</footer>", "<footer class=\"page-footer\">{{ analysisPageNumber(pageIndex + 2 + analysisClimatePages.length) }}</footer>", 1)

style_anchor = '''.analysis-climate-card__meta {
  display: grid;
  gap: 0.05in;
  margin-bottom: 0.08in;
}
'''
style_replace = '''.analysis-climate-card__meta {
  display: grid;
  gap: 0.05in;
  margin-bottom: 0.08in;
}

.analysis-section--paired-climate {
  margin-top: 0.05in;
}

.analysis-climate-card--compact {
  padding: 0.1in 0.14in 0.12in;
}

.analysis-climate-card__meta--inline {
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.08in;
  margin-bottom: 0.06in;
}
'''
if style_anchor not in text:
    raise SystemExit('No se encontró el ancla de estilos de clima.')
text = text.replace(style_anchor, style_replace, 1)

style_anchor2 = '''.analysis-climate-card__image {
  display: block;
  width: 100%;
  max-height: 7.1in;
  object-fit: cover;
}
'''
style_replace2 = '''.analysis-climate-card__image {
  display: block;
  width: 100%;
  max-height: 7.1in;
  object-fit: cover;
}

.analysis-climate-card__image-wrap--compact {
  max-height: 2.55in;
}

.analysis-climate-card__image--compact {
  max-height: 2.55in;
  object-fit: cover;
}
'''
if style_anchor2 not in text:
    raise SystemExit('No se encontró el estilo de imagen de clima.')
text = text.replace(style_anchor2, style_replace2, 1)

style_anchor3 = '''.analysis-risk-list {
  display: grid;
  gap: 0.09in;
}
'''
style_replace3 = '''.analysis-risk-list {
  display: grid;
  gap: 0.07in;
}
'''
if style_anchor3 not in text:
    raise SystemExit('No se encontró el estilo de lista de riesgos.')
text = text.replace(style_anchor3, style_replace3, 1)

style_anchor4 = '''.analysis-risk-card__body {
  padding: 0.12in;
  line-height: 1.4;
}
'''
style_replace4 = '''.analysis-risk-card__body {
  padding: 0.09in 0.12in;
  line-height: 1.33;
}
'''
if style_anchor4 not in text:
    raise SystemExit('No se encontró el estilo del cuerpo de riesgos.')
text = text.replace(style_anchor4, style_replace4, 1)

path.write_text(text, encoding='utf-8')
