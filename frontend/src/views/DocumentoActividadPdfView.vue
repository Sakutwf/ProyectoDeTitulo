<template>
  <div class="pdf-export-page">
    <div class="pdf-toolbar no-print">
      <button type="button" class="pdf-toolbar__button" @click="goBack">Volver</button>
      <button
        type="button"
        class="pdf-toolbar__button pdf-toolbar__button--primary"
        :disabled="!readyToPrint"
        @click="printNow"
      >
        Guardar como PDF
      </button>
      <span class="pdf-toolbar__hint">Formato sugerido: carta, orientación vertical.</span>
    </div>

    <div v-if="isLoading" class="pdf-state">Cargando documento...</div>
    <div v-else-if="errorMessage" class="pdf-state pdf-state--error">{{ errorMessage }}</div>

    <div v-else-if="documento" class="pdf-document">
      <template v-if="isNarrativeType">
        <article class="print-page">
          <div class="print-page__content">
            <header class="document-cover document-cover--compact">
              <div class="document-cover__brand">
                <img :src="logoSrc" alt="Cruz Roja Chilena" class="document-cover__logo">
              </div>
              <div class="document-cover__titles">
                <h1>INFORME NARRATIVO</h1>
                <h2>{{ narrativeTitleLine }}</h2>
              </div>
            </header>

            <section class="print-section print-section--tight-top narrative-block narrative-block--merged">
              <table class="print-table print-table--two-col print-table--narrative-flow">
                <tbody>
                  <tr class="section-row">
                    <th colspan="2">Información General Operativo</th>
                  </tr>
                  <tr>
                    <td class="label-cell">Fecha del informe</td>
                    <td>{{ documentDateLongLabel }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Fecha de la actividad</td>
                    <td>{{ activityDateLongLabel }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Nombre de la actividad</td>
                    <td>{{ actividad.nombre || 'Sin nombre' }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Hora</td>
                    <td>{{ narrative.horario || activitySchedule }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Lugar</td>
                    <td>{{ narrative.lugar || primaryLocation }}</td>
                  </tr>
                  <tr class="section-row">
                    <th colspan="2">Información del Desarrollo de la Actividad</th>
                  </tr>
                  <tr>
                    <td class="label-cell">Objetivo General</td>
                    <td class="multiline-cell">{{ narrative.objetivo_general || 'Sin información registrada.' }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Objetivo Específico</td>
                    <td class="multiline-cell">{{ narrative.objetivo_especifico || 'Sin información registrada.' }}</td>
                  </tr>
                  <tr class="narrative-summary-row">
                    <td class="label-cell narrative-summary-label">Descripción General (Breve narrativo de la actividad. No más de 10 renglones)</td>
                    <td class="narrative-summary-cell">
                      <div class="multiline-cell narrative-summary-description">{{ narrative.descripcion_general || 'Sin información registrada.' }}</div>
                      <table class="embedded-counts-table" aria-label="Personas asistidas">
                        <tbody>
                          <tr class="section-row">
                            <th colspan="2">
                              Personas asistidas
                              <span class="embedded-counts-table__subtitle">(disgregado por género, tipo de atención, traslado)</span>
                            </th>
                          </tr>
                          <tr v-for="item in narrativeCounts" :key="item.tipo">
                            <td>{{ item.tipo }}</td>
                            <td class="value-cell">{{ item.numero }}</td>
                          </tr>
                          <tr v-if="!narrativeCounts.length">
                            <td colspan="2">Sin registros de personas asistidas.</td>
                          </tr>
                        </tbody>
                      </table>
                      <div class="narrative-summary-meta">
                        <strong>Situaciones de Interés ocurridas:</strong>
                        <span class="multiline-cell">{{ narrative.situaciones_interes || 'Sin información registrada.' }}</span>
                      </div>
                      <div class="narrative-summary-meta">
                        <strong>N° de Puestos</strong>
                        <span class="narrative-summary-meta__hint">(por región/comuna y ubicación de estos)</span>
                        <span class="multiline-cell">{{ narrative.numero_puestos || 'Sin información registrada.' }}</span>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="label-cell">Participantes (Lista de asistencia)</td>
                    <td class="multiline-cell participants-cell">
                      <div v-if="participantRows.length" class="stacked-lines stacked-lines--compact">
                        <div v-for="(row, index) in participantRows" :key="`participant-${index}`">
                          {{ row.tipo || 'Sin tipo' }}: {{ normalizeNumericLabel(row.numero) }}
                        </div>
                      </div>
                      <span v-else>Sin participantes registrados.</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </section>
          </div>
          <footer class="page-footer">{{ narrativePageNumber(1) }}</footer>
        </article>

        <article class="print-page">
          <div class="print-page__content">
            <section class="print-section print-section--tight-top narrative-block narrative-block--merged">
              <table class="print-table print-table--two-col print-table--narrative-flow">
                <tbody>
                  <tr class="section-row">
                    <th colspan="2">Observaciones generales</th>
                  </tr>
                  <tr>
                    <td class="label-cell">Logros</td>
                    <td class="multiline-cell">{{ fallbackText(narrativeObservations.logros) }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Desafíos y dificultades</td>
                    <td class="multiline-cell">{{ fallbackText(narrativeObservations.desafios_dificultades) }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Recomendaciones y acciones por mejorar</td>
                    <td class="multiline-cell">{{ fallbackText(narrativeObservations.recomendaciones_acciones) }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Percepción: Opinión subjetiva del supervisor o encargado de la actividad</td>
                    <td class="multiline-cell">{{ fallbackText(narrativeObservations.percepcion) }}</td>
                  </tr>
                  <tr>
                    <td colspan="2" class="photo-instruction-cell">
                      En el siguiente cuadro adjunte las fotos en formato JPG y al lado agregue la descripción. (3 fotografías mínimo).
                    </td>
                  </tr>
                </tbody>
              </table>
            </section>
          </div>
          <footer class="page-footer">{{ narrativePageNumber(2) }}</footer>
        </article>

        <article v-for="(page, pageIndex) in narrativePhotoPages" :key="`narrative-photo-page-${pageIndex}`" class="print-page">
          <div class="print-page__content">
            <section class="print-section print-section--tight-top">
              <table class="print-table print-table--photo">
                <tbody>
                  <tr class="section-row">
                    <th colspan="2">Registro fotográfico</th>
                  </tr>
                  <tr v-for="(photo, photoIndex) in page" :key="photo.id || `${pageIndex}-${photoIndex}`" class="photo-row">
                    <td class="photo-cell photo-cell--joined">
                      <img :src="photo.imagen_url" :alt="photo.titulo || `Fotografía ${photoIndex + 1}`" class="print-photo">
                    </td>
                    <td class="multiline-cell photo-description-cell photo-description-cell--joined">
                      <strong>{{ photo.titulo || `Fotografía ${pageIndex * narrativePhotosPerPage + photoIndex + 1}` }}</strong>
                      <div>{{ photo.descripcion_informe || 'Sin descripción registrada.' }}</div>
                    </td>
                  </tr>
                  <tr v-if="!page.length">
                    <td colspan="2">Sin fotografías registradas.</td>
                  </tr>
                </tbody>
              </table>
            </section>

            <section v-if="pageIndex === narrativePhotoPages.length - 1" class="signoff-sheet signoff-sheet--attached">
              <table class="print-table print-table--two-col print-table--signoff">
                <tbody>
                  <tr>
                    <td class="label-cell">Elaboración del Informe</td>
                    <td>{{ signoffDateLabel }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Autorización del Informe</td>
                    <td>{{ narrative.autorizacion_informe || 'Sin autorización registrada.' }}</td>
                  </tr>
                </tbody>
              </table>
            </section>
          </div>
          <footer class="page-footer">{{ narrativePageNumber(pageIndex + 3) }}</footer>
        </article>

        <article v-if="!narrativePhotos.length" class="print-page">
          <div class="print-page__content print-page__content--footer-sheet">
            <section class="signoff-sheet">
              <table class="print-table print-table--two-col print-table--signoff">
                <tbody>
                  <tr>
                    <td class="label-cell">Elaboración del Informe</td>
                    <td>{{ signoffDateLabel }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Autorización del Informe</td>
                    <td>{{ narrative.autorizacion_informe || 'Sin autorización registrada.' }}</td>
                  </tr>
                </tbody>
              </table>
            </section>
          </div>
          <footer class="page-footer">{{ narrativePageNumber(narrativeTotalPages) }}</footer>
        </article>
      </template>

      <template v-else-if="isContextAnalysisType">
        <article class="print-page">
          <div class="print-page__content print-page__content--analysis-intro">
            <header class="document-cover document-cover--analysis">
              <div class="document-cover__brand document-cover__brand--analysis">
                <img :src="logoSrc" alt="Cruz Roja Chilena" class="document-cover__logo document-cover__logo--analysis">
              </div>
              <div class="document-cover__titles document-cover__titles--analysis">
                <div class="document-cover__eyebrow">COMUNICADO FILIAL {{ filialName.toUpperCase() }}</div>
                <h1>ANÁLISIS DE CONTEXTO</h1>
                <h2>{{ analysisTitleLine }}</h2>
              </div>
            </header>

            <section class="print-section narrative-block analysis-intro-copy">
              <p>{{ analysis.proposito_documento || 'Sin información registrada.' }}</p>
            </section>

            <section class="print-section narrative-block analysis-section analysis-section--compact">
              <div class="analysis-section__heading">DESCRIPCIÓN DEL EVENTO</div>
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
                    <div class="analysis-climate-card__label">MÍNIMA:</div>
                    <div class="analysis-climate-card__value">{{ formatTemperature(primaryClimate.temperatura_minima) }}</div>
                  </div>
                  <div class="analysis-climate-card__metric">
                    <div class="analysis-climate-card__label">MÁXIMA:</div>
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
          <footer class="page-footer">{{ analysisPageNumber(1) }}</footer>
        </article>

        <article v-for="(page, pageIndex) in analysisClimatePages" :key="`analysis-climate-${pageIndex}`" class="print-page">
          <div class="print-page__content">
            <section class="print-section print-section--tight-top analysis-section">
              <div class="analysis-section__heading">CLIMA ESPERADO</div>
              <div v-for="(item, itemIndex) in page" :key="item.id || `${pageIndex}-${itemIndex}`" class="analysis-climate-card">
                <div class="analysis-climate-card__meta">
                  <div class="analysis-climate-card__metric">
                    <div class="analysis-climate-card__label">MÍNIMA:</div>
                    <div class="analysis-climate-card__value">{{ formatTemperature(item.temperatura_minima) }}</div>
                  </div>
                  <div class="analysis-climate-card__metric">
                    <div class="analysis-climate-card__label">MÁXIMA:</div>
                    <div class="analysis-climate-card__value">{{ formatTemperature(item.temperatura_maxima) }}</div>
                  </div>
                  <div class="analysis-climate-card__metric">
                    <div class="analysis-climate-card__label">TIPO:</div>
                    <div class="analysis-climate-card__value">{{ item.tipo_clima || 'Sin descripción' }}</div>
                  </div>
                </div>
                <div class="analysis-climate-card__image-wrap">
                  <img v-if="item.imagen_url" :src="item.imagen_url" :alt="item.titulo_imagen || `Clima ${itemIndex + 1}`" class="analysis-climate-card__image">
                  <div v-else class="analysis-climate-card__empty">Sin imagen climática registrada.</div>
                </div>
              </div>
              <div v-if="!page.length" class="analysis-empty-state">Sin climas registrados.</div>
            </section>
          </div>
          <footer class="page-footer">{{ analysisPageNumber(pageIndex + 2) }}</footer>
        </article>

        <article v-for="(page, pageIndex) in analysisRiskPages" :key="`analysis-risk-${pageIndex}`" class="print-page">
          <div class="print-page__content">
            <section class="print-section print-section--tight-top analysis-section">
              <div class="analysis-section__heading">IDENTIFICACIÓN DE RIESGOS</div>
              <div v-if="page.length" class="analysis-risk-list">
                <article v-for="(risk, riskIndex) in page" :key="risk.id || `${pageIndex}-${riskIndex}`" class="analysis-risk-card">
                  <div class="analysis-risk-card__header">
                    <div class="analysis-risk-card__title">{{ risk.nombre || 'Sin nombre' }}</div>
                    <div class="analysis-risk-card__badges">
                      <span class="analysis-risk-badge">Probabilidad: {{ joinOptions(risk.probabilidad) || '-' }}</span>
                      <span class="analysis-risk-badge">Impacto: {{ joinOptions(risk.impacto) || '-' }}</span>
                    </div>
                  </div>
                  <div class="analysis-risk-card__body">
                    <p><strong>Descripción:</strong> {{ risk.descripcion || '-' }}</p>
                    <p><strong>Medidas de mitigación:</strong> {{ risk.mitigacion || '-' }}</p>
                  </div>
                </article>
              </div>
              <div v-else class="analysis-empty-state">Sin riesgos registrados.</div>
            </section>
          </div>
          <footer class="page-footer">{{ analysisPageNumber(pageIndex + 2 + analysisClimatePages.length) }}</footer>
        </article>

        <article class="print-page">
          <div class="print-page__content">
            <section class="print-section print-section--tight-top analysis-narrative-section">
              <div class="analysis-section__heading">PROTOCOLO DE TRASLADO</div>
              <div class="analysis-narrative-copy multiline-cell">{{ analysis.protocolo_traslado || 'Sin información registrada.' }}</div>
            </section>

            <section class="print-section analysis-narrative-section">
              <div class="analysis-section__heading">CENTROS DE SALUD CERCANOS</div>
              <div class="analysis-narrative-copy multiline-cell">{{ analysis.centros_salud || 'Sin información registrada.' }}</div>
            </section>

            <section class="print-section analysis-narrative-section" v-if="analysis.plan_traslados">
              <div class="analysis-section__heading">PLAN DE TRASLADOS</div>
              <div class="analysis-narrative-copy multiline-cell">{{ analysis.plan_traslados }}</div>
            </section>

            <section class="print-section analysis-narrative-section">
              <div class="analysis-section__heading">CONCLUSIÓN</div>
              <div class="analysis-narrative-copy multiline-cell">{{ analysis.conclusion || 'Sin información registrada.' }}</div>
            </section>

            <section class="print-section analysis-narrative-section" v-if="analysis.observaciones_finales">
              <div class="analysis-section__footer-note multiline-cell">{{ analysis.observaciones_finales }}</div>
            </section>

            <section class="print-section analysis-narrative-section analysis-narrative-section--meta">
              <div class="analysis-narrative-meta"><strong>Elaboración del documento:</strong> {{ signoffDateLabel }}</div>
            </section>
          </div>
          <footer class="page-footer">{{ analysisPageNumber(analysisTotalPages) }}</footer>
        </article>
      </template>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import logoSrc from '../assets/LogoVertical.svg'
import { buildApiUrl } from '../config/api'

const route = useRoute()
const router = useRouter()

const isLoading = ref(true)
const errorMessage = ref('')
const documento = ref(null)

const narrativePhotosPerPage = 3
const analysisClimatesPerPage = 1
const analysisRisksPerPage = 3

const contexto = computed(() => documento.value?.datos_contexto || {})
const actividad = computed(() => contexto.value?.actividad || documento.value?.actividad || {})
const filial = computed(() => contexto.value?.filial || documento.value?.actividad?.filial || {})
const creator = computed(() => contexto.value?.creador || documento.value?.generador || {})
const contenido = computed(() => documento.value?.contenido || {})
const narrative = computed(() => contenido.value || {})
const analysis = computed(() => contenido.value || {})
const analysisEvent = computed(() => analysis.value?.descripcion_evento || {})
const documentType = computed(() => documento.value?.tipo_documento || '')
const isNarrativeType = computed(() => documentType.value === 'informe_narrativo')
const isContextAnalysisType = computed(() => documentType.value === 'analisis_contexto')
const readyToPrint = computed(() => Boolean(documento.value))

const filialName = computed(() => filial.value?.nombre || 'Filial sin registro')
const creatorName = computed(() => creator.value?.name || creator.value?.username || 'Sin responsable asignado')
const primaryLocation = computed(() => analysisEvent.value?.lugar_evento || narrative.value?.lugar || actividad.value?.lugar || filial.value?.direccion || 'Sin lugar registrado')
const activitySchedule = computed(() => [actividad.value?.hora_inicio, actividad.value?.hora_termino].filter(Boolean).join(' - ') || 'Sin horario registrado')
const documentDateLongLabel = computed(() => formatDateLong(documento.value?.fecha_documento) || 'Sin fecha registrada')
const activityDateLongLabel = computed(() => formatDateLong(actividad.value?.fecha_inicio || documento.value?.fecha_documento) || 'Sin fecha registrada')
const signoffDateLabel = computed(() => formatShortDate(documento.value?.fecha_documento) || formatShortDate(documento.value?.updated_at) || '-')
const analysisTitleLine = computed(() => upperTitleLine(actividad.value?.nombre || documento.value?.titulo || 'ACTIVIDAD'))
const narrativeTitleLine = computed(() => upperTitleLine(actividad.value?.nombre || documento.value?.titulo || 'ACTIVIDAD'))
const narrativeObservations = computed(() => narrative.value?.observaciones_generales || {})
const narrativeCounts = computed(() => compactCounts(narrative.value?.recuentos_personas_asistidas))
const participantRows = computed(() => compactRows(narrative.value?.participantes_asistencia))
const narrativePhotos = computed(() => Array.isArray(narrative.value?.fotos) ? narrative.value.fotos.filter((photo) => photo?.imagen_url) : [])
const climateRows = computed(() => Array.isArray(analysisEvent.value?.climas_esperados) ? analysisEvent.value.climas_esperados.filter(Boolean) : [])
const analysisRisks = computed(() => Array.isArray(analysis.value?.riesgos) ? analysis.value.riesgos.filter(Boolean) : [])
const narrativePhotoPages = computed(() => chunkItems(narrativePhotos.value, narrativePhotosPerPage))
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

onMounted(() => {
  fetchDocument()
})

async function fetchDocument() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await axios.get(buildApiUrl(`documentos-actividad/${route.params.id}`))
    documento.value = response.data
  } catch {
    errorMessage.value = 'No se pudo cargar el documento para exportar.'
  } finally {
    isLoading.value = false
  }
}

function chunkItems(items, size) {
  if (!Array.isArray(items) || !items.length) {
    return []
  }

  const chunks = []
  for (let index = 0; index < items.length; index += size) {
    chunks.push(items.slice(index, index + size))
  }
  return chunks
}

function compactRows(rows) {
  if (!Array.isArray(rows)) {
    return []
  }

  return rows.filter((row) => String(row?.tipo || '').trim() || row?.numero !== null && row?.numero !== undefined && row?.numero !== '')
}

function compactCounts(rows) {
  return compactRows(rows).map((row) => ({
    tipo: row.tipo || 'Sin tipo',
    numero: normalizeNumericLabel(row.numero)
  }))
}

function fallbackText(value) {
  const normalized = String(value || '').trim()
  return normalized || 'Sin información registrada.'
}

function normalizeNumericLabel(value) {
  if (value === null || value === undefined || value === '') {
    return '0'
  }

  const numberValue = Number(value)
  return Number.isFinite(numberValue) ? String(numberValue) : String(value)
}

function formatDateLong(value) {
  if (!value) return ''
  const parsed = new Date(String(value).slice(0, 10) + 'T00:00:00')
  if (Number.isNaN(parsed.getTime())) return String(value)
  return parsed.toLocaleDateString('es-CL', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

function formatShortDate(value) {
  if (!value) return ''
  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) return ''
  const day = String(parsed.getDate()).padStart(2, '0')
  const month = String(parsed.getMonth() + 1).padStart(2, '0')
  const year = String(parsed.getFullYear()).slice(-2)
  return `${day}-${month}-${year}`
}

function upperTitleLine(value) {
  return String(value || '').trim().toUpperCase()
}

function formatTemperature(value) {
  if (value === null || value === undefined || value === '') {
    return '-'
  }

  return `${Number(value)} °C`
}

function joinOptions(value) {
  if (Array.isArray(value)) {
    const labels = value.filter(Boolean)
    return labels.length ? labels.join(', ') : '-'
  }

  return String(value || '-').trim() || '-'
}

function narrativePageNumber(page) {
  return page
}

function analysisPageNumber(page) {
  return page
}

function goBack() {
  router.push({
    name: 'documentos',
    query: {
      tipo: route.query.tipo || undefined,
      actividad: route.query.actividad || undefined
    }
  })
}

function printNow() {
  if (!readyToPrint.value) return
  window.print()
}
</script>

<style scoped>
.pdf-export-page {
  min-height: 100vh;
  padding: 1.5rem;
  background: #eef2f7;
}

.pdf-toolbar {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.pdf-toolbar__button {
  border: none;
  border-radius: 999px;
  padding: 0.75rem 1.15rem;
  background: #d7deea;
  color: #173763;
  font-weight: 700;
}

.pdf-toolbar__button--primary {
  background: #e01e1e;
  color: #fff;
}

.pdf-toolbar__hint {
  color: #5f7085;
  font-size: 0.92rem;
}

.pdf-state {
  display: grid;
  place-items: center;
  min-height: 60vh;
  color: #4d617d;
}

.pdf-state--error {
  color: #a3212b;
}

.pdf-document {
  display: grid;
  gap: 1.2rem;
  justify-content: center;
  width: fit-content;
  margin: 0 auto;
}

.print-page {
  position: relative;
  width: 8.5in;
  min-height: 11in;
  box-sizing: border-box;
  background: #fff;
  color: #111;
  box-shadow: 0 18px 40px rgba(15, 47, 95, 0.15);
  page-break-after: always;
}

.print-page:last-child {
  page-break-after: auto;
}

.print-page__content {
  /*padding: 0.45in 0.45in 0.65in;*/
}

.print-page__content--footer-sheet {
  display: flex;
  align-items: flex-end;
  min-height: calc(11in - 1.1in);
}

.page-footer {
  position: absolute;
  right: 0.4in;
  bottom: 0.2in;
  font-size: 10pt;
  color: #333;
  line-height: 1;
  min-width: 0.18in;
  text-align: right;
}

.document-cover {
  display: flex;
  align-items: center;
  gap: 0.28in;
  margin-bottom: 0.18in;
}

.document-cover--compact {
  margin-bottom: 0.08in;
}

.document-cover__brand {
  width: 1.45in;
  flex: 0 0 1.45in;
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.document-cover__logo {
  display: block;
  width: 1.12in;
  max-width: 100%;
}

.document-cover__titles {
  flex: 1;
}

.document-cover__titles h1,
.document-cover__titles h2 {
  margin: 0;
  font-weight: 700;
  text-align: center;
}

.document-cover__titles h1 {
  font-size: 18pt;
  letter-spacing: 0.02em;
}

.document-cover__titles h2 {
  margin-top: 0.06in;
  font-size: 12pt;
}

.document-cover--analysis {
  align-items: center;
  gap: 0.34in;
  margin-bottom: 0.2in;
}

.document-cover__brand--analysis {
  width: 1.5in;
  flex: 0 0 1.5in;
  min-height: 1.2in;
  align-items: center;
}

.document-cover__logo--analysis {
  width: 1.12in;
}

.document-cover__titles--analysis {
  flex: 1;
  padding-top: 0;
  text-align: left;
}

.document-cover__titles--analysis h1,
.document-cover__titles--analysis h2 {
  text-align: left;
}

.document-cover__titles--analysis h1 {
  font-size: 21pt;
  line-height: 1.05;
}

.document-cover__titles--analysis h2 {
  margin-top: 0.07in;
  font-size: 13pt;
  line-height: 1.15;
}

.document-cover__eyebrow {
  margin-bottom: 0.06in;
  font-size: 10pt;
  font-weight: 700;
  letter-spacing: 0.12em;
  line-height: 1.1;
}

.print-section {
  margin-top: 0.03in;
}

.narrative-block + .narrative-block {
  margin-top: 0.08in;
}

.narrative-block--merged + .narrative-block--merged {
  margin-top: 0.08in;
}

.print-section--tight-top {
  margin-top: 0;
}

.print-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
  font-size: 10.5pt;
}

.print-table th,
.print-table td {
  border: 1px solid #1f1f1f;
  padding: 0.08in 0.1in;
  vertical-align: top;
}

.print-table .section-row th {
  text-align: left;
  background: #f2f2f2;
  font-weight: 700;
}

.print-table .subheader-row th {
  background: #fafafa;
  font-weight: 700;
  text-align: left;
}

.label-cell {
  width: 32%;
  font-weight: 700;
  background-color: #d9d9d9 !important;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}

.value-cell {
  text-align: center;
}

.multiline-cell {
  white-space: pre-line;
  line-height: 1.35;
}

.participants-cell {
  vertical-align: top;
}

.photo-instruction-cell {
  padding-top: 0.14in;
  padding-bottom: 0.14in;
  line-height: 1.35;
}

.narrative-summary-label {
  vertical-align: middle;
}

.narrative-summary-cell {
  padding: 0.08in 0.1in 0.12in;
}

.narrative-summary-description {
  margin-bottom: 0.08in;
}

.embedded-counts-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
  margin-bottom: 0.08in;
  font-size: 10.5pt;
}

.embedded-counts-table th,
.embedded-counts-table td {
  border: 1px solid #1f1f1f;
  padding: 0.06in 0.08in;
  vertical-align: top;
}

.embedded-counts-table .section-row th {
  text-align: left;
}

.embedded-counts-table__subtitle,
.narrative-summary-meta__hint {
  font-style: italic;
  font-weight: 400;
}

.narrative-summary-meta {
  line-height: 1.35;
}

.narrative-summary-meta + .narrative-summary-meta {
  margin-top: 0.05in;
}

.print-table--narrative-flow th,
.print-table--narrative-flow td {
  padding-top: 0.05in;
  padding-bottom: 0.05in;
}

.print-table--narrative-flow .section-row th {
  padding-top: 0.06in;
  padding-bottom: 0.06in;
}

.stacked-lines {
  display: grid;
  gap: 0.05in;
}

.stacked-lines--compact {
  gap: 0.015in;
}

.photo-instruction {
  border: 1px solid #1f1f1f;
  padding: 0.14in;
  font-size: 10.5pt;
  line-height: 1.35;
}

.photo-cell,
.climate-photo-cell {
  width: 42%;
}

.print-table--photo .photo-cell--joined {
  padding: 0;
}

.print-table--photo .photo-description-cell--joined {
  vertical-align: middle;
}

.print-photo,
.print-climate-photo {
  display: block;
  width: 100%;
  object-fit: cover;
  border: 1px solid #8f8f8f;
}

.print-photo {
  aspect-ratio: 4 / 3;
  border: 0;
}

.print-climate-photo {
  aspect-ratio: 3 / 2;
}

.photo-description-cell strong {
  display: block;
  margin-bottom: 0.08in;
}

.signoff-sheet {
  width: 100%;
}

.signoff-sheet--attached {
  margin-top: 0.05in;
}

.print-table--photo .photo-row td {
  padding-top: 0.05in;
  padding-bottom: 0.05in;
}

.print-table--photo .print-photo {
  aspect-ratio: 16 / 10;
}

.print-table--signoff td {
  padding-top: 0.1in;
  padding-bottom: 0.1in;
}

.print-table--signoff td {
  padding-top: 0.12in;
  padding-bottom: 0.12in;
}

.analysis-section__heading {
  margin-bottom: 0.07in;
  padding-bottom: 0.04in;
  border-bottom: 1px solid #1f1f1f;
  font-size: 13pt;
  font-weight: 700;
  letter-spacing: 0.01em;
}

.analysis-intro-copy {
  margin-top: 0.05in;
}

.analysis-intro-copy p {
  margin: 0;
  font-size: 11.7pt;
  line-height: 1.5;
}

.analysis-section--compact {
  margin-top: 0.06in;
}

.analysis-event-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.1in 0.14in;
}

.analysis-event-card {
  border: 1px solid #6d6d6d;
  padding: 0.09in 0.11in;
  min-height: 0.64in;
}

.analysis-event-card__label {
  margin-bottom: 0.05in;
  font-size: 9.4pt;
  font-weight: 700;
}

.analysis-event-card__value {
  font-size: 11.4pt;
  line-height: 1.22;
}

.analysis-event-card__value--strong {
  font-weight: 700;
}

.analysis-climate-card {
  border: 1px solid #c7d2e2;
  border-radius: 0.16in;
  padding: 0.16in;
}

.analysis-climate-card__meta {
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

.analysis-climate-card__label {
  margin-bottom: 0.02in;
  font-size: 10pt;
  font-weight: 700;
}

.analysis-climate-card__value {
  font-size: 11.5pt;
  line-height: 1.25;
}

.analysis-climate-card__image-wrap {
  overflow: hidden;
  border-radius: 0.12in;
}

.analysis-climate-card__image {
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

.analysis-climate-card__empty,
.analysis-empty-state {
  border: 1px solid #bdbdbd;
  padding: 0.2in;
  text-align: center;
  color: #555;
}

.analysis-risk-list {
  display: grid;
  gap: 0.07in;
}

.analysis-risk-card {
  border: 1px solid #6d6d6d;
}

.analysis-risk-card__header {
  padding: 0.09in 0.12in;
  border-bottom: 1px solid #6d6d6d;
}

.analysis-risk-card__title {
  margin-bottom: 0.05in;
  font-size: 13pt;
  font-weight: 500;
}

.analysis-risk-card__badges {
  display: flex;
  gap: 0.08in;
  flex-wrap: wrap;
}

.analysis-risk-badge {
  display: inline-block;
  border: 1px solid #6d6d6d;
  padding: 0.04in 0.08in;
  font-size: 9.5pt;
  font-weight: 700;
}

.analysis-risk-card__body {
  padding: 0.09in 0.12in;
  line-height: 1.33;
}

.analysis-risk-card__body p {
  margin: 0;
}

.analysis-risk-card__body p + p {
  margin-top: 0.06in;
}

.analysis-narrative-section {
  margin-top: 0.08in;
}

.analysis-narrative-copy {
  font-size: 12pt;
  line-height: 1.5;
}

.analysis-narrative-copy.multiline-cell {
  white-space: pre-line;
}

.analysis-section__footer-note {
  margin-top: 0.04in;
  padding-top: 0.08in;
  border-top: 1px solid #7a7a7a;
  font-size: 11.5pt;
  line-height: 1.45;
}

.analysis-narrative-section--meta {
  margin-top: 0.08in;
}

.analysis-narrative-meta {
  font-size: 11pt;
}

@media (max-width: 1100px) {
  .pdf-export-page {
    overflow-x: auto;
  }

  .pdf-document {
    justify-content: start;
    margin: 0;
    padding-bottom: 0.5rem;
  }

  .analysis-event-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .print-page {
    width: 8.5in;
    min-height: 11in;
  }

  .print-page__content--footer-sheet {
    min-height: calc(11in - 1.1in);
  }
}

@media print {
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  @page {
    size: letter portrait;
    margin: 0;
  }

  html,
  body,
  .pdf-export-page {
    margin: 0;
    padding: 0;
    background: #fff;
  }

  .no-print {
    display: none !important;
  }

  .pdf-document {
    display: block;
    margin: 0;
    padding: 0;
  }

  .print-page {
    width: 8.5in;
    min-height: 11in;
    box-shadow: none;
    margin: 0;
    overflow: hidden;
    break-after: page;
    page-break-after: always;
    break-inside: avoid;
    page-break-inside: avoid;
  }

  .print-page:last-child {
    break-after: auto;
    page-break-after: auto;
  }

  .print-page__content {
    padding:0 !important;
  }

  .print-page__content--footer-sheet {
    min-height: calc(11in - 1.1in);
  }

  .print-section,
  .narrative-block,
  .signoff-sheet,
  .photo-instruction,
  .print-table tr,
  .print-table th,
  .print-table td,
  .print-photo,
  .print-climate-photo {
    break-inside: avoid;
    page-break-inside: avoid;
  }
}
</style>
