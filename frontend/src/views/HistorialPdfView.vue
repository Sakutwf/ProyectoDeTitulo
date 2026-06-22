<template>
  <div class="pdf-export-page">
    <div class="pdf-toolbar no-print">
      <button type="button" class="pdf-toolbar__button" @click="goBack">
        Volver
      </button>
      <button type="button" class="pdf-toolbar__button pdf-toolbar__button--primary" @click="printNow">
        Guardar como PDF
      </button>
      <span class="pdf-toolbar__hint">Formato sugerido: tamano legal, orientacion vertical.</span>
    </div>

    <div v-if="isLoading" class="pdf-state">
      Preparando hoja de vida...
    </div>

    <div v-else-if="errorMessage" class="pdf-state pdf-state--error">
      {{ errorMessage }}
    </div>

    <div v-else-if="user && hojaDeVida" class="pdf-document">
      <section
        v-for="(rows, pageIndex) in signatureYearChunks"
        :key="`cover-${pageIndex}`"
        class="pdf-sheet"
      >
        <div class="sheet-header">
          <div class="sheet-header__cross" aria-hidden="true">
            <span class="cross cross--vertical"></span>
            <span class="cross cross--horizontal"></span>
          </div>
          <div class="sheet-header__content">
            <h1>CRUZ ROJA CHILENA</h1>
            <p>Hoja de Vida Voluntario/a Activo/a</p>
            <p>Entidad: {{ entityLabel }}</p>
          </div>
        </div>

        <div class="identity-photo identity-photo--cover">
          <img
            v-if="volunteer?.foto_perfil_url"
            :src="volunteer.foto_perfil_url"
            :alt="`Foto de ${user.nombre}`"
          >
        </div>

        <div class="identity-layout">
          <div class="identity-fields">
            <div class="identity-line">
              <span class="identity-label">Nombre:</span>
              <span class="identity-value">{{ user.nombre || '' }}</span>
            </div>
            <div class="identity-line">
              <span class="identity-label">Fecha de Nacimiento:</span>
              <span class="identity-value">{{ formatDate(volunteer?.fecha_nacimiento) }}</span>
            </div>
            <div class="identity-line">
              <span class="identity-label">Cedula de Identidad:</span>
              <span class="identity-value">{{ user.rut || '' }}</span>
            </div>
            <div class="identity-line">
              <span class="identity-label">Estudios Anteriores:</span>
              <span class="identity-value">{{ previousStudies }}</span>
            </div>
            <div class="identity-line">
              <span class="identity-label">Fecha de Ingreso:</span>
              <span class="identity-value">{{ formatDate(volunteer?.fecha_ingreso || hojaDeVida.fecha_creacion) }}</span>
            </div>
            <div class="identity-line">
              <span class="identity-label">N&deg; de Registro:</span>
              <span class="identity-value">{{ volunteer?.n_registro || '' }}</span>
            </div>
            <div class="identity-line identity-line--split">
              <div class="identity-split-field">
                <span class="identity-label">Grupo Sanguineo:</span>
                <span class="identity-value">{{ volunteer?.grupo_sanguineo || '' }}</span>
              </div>
              <div class="identity-split-field">
                <span class="identity-label">Factor Rh:</span>
                <span class="identity-value">{{ volunteer?.factor_rh || '' }}</span>
              </div>
            </div>
          </div>
        </div>

        <table class="signature-table">
          <thead>
            <tr>
              <th>Ano</th>
              <th>Firma del Vicepresidente/a</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, rowIndex) in rows" :key="`signature-${pageIndex}-${rowIndex}`">
              <td>{{ row?.anio || '' }}</td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </section>

      <section
        v-for="(rows, pageIndex) in annualRowChunks"
        :key="`annual-${pageIndex}`"
        class="pdf-sheet pdf-sheet--table"
      >
        <table class="annual-table">
          <colgroup>
            <col class="annual-table__col--year">
            <col class="annual-table__col--cargo">
            <col class="annual-table__col--lista">
            <col class="annual-table__col--attendance">
            <col class="annual-table__col--formation">
            <col class="annual-table__col--titles">
            <col class="annual-table__col--awards">
          </colgroup>
          <thead>
            <tr>
              <th>A&Ntilde;O</th>
              <th>CARGO</th>
              <th>LISTA</th>
              <th>% DE<br>ASISTENCIA</th>
              <th>CURSOS - TALLERES - SEMINARIOS<br>A LOS QUE CONCURRIO</th>
              <th>TITULOS<br>OBTENIDOS EN EL A&Ntilde;O</th>
              <th>PREMIOS<br>OBTENIDOS EN EL A&Ntilde;O</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, rowIndex) in rows" :key="`annual-row-${pageIndex}-${rowIndex}`">
              <td>{{ row?.anio || '' }}</td>
              <td class="cell-preline">{{ row?.cargo || '' }}</td>
              <td class="cell-preline">{{ row?.lista || '' }}</td>
              <td>{{ row?.asistencia || '' }}</td>
              <td class="cell-preline">{{ row?.formacion || '' }}</td>
              <td class="cell-preline">{{ row?.titulos || '' }}</td>
              <td class="cell-preline">{{ row?.premios || '' }}</td>
            </tr>
          </tbody>
        </table>

        <div class="annual-note">
          <strong>NOTA:</strong> La calificacion corresponde a: Lista 1 = Excelente, Lista 2 = Bueno, Lista 3 = Regular
        </div>
      </section>

      <section
        v-for="(rows, pageIndex) in annualRowChunks"
        :key="`observations-${pageIndex}`"
        class="pdf-sheet pdf-sheet--observations"
      >
        <div class="observations-header">Labor Efectuada y Observaciones</div>
        <table class="observations-table">
          <tbody>
            <tr v-for="(row, rowIndex) in rows" :key="`observation-row-${pageIndex}-${rowIndex}`">
              <td class="observations-cell">
                <template v-if="row">
                  <strong>{{ row.anio }}</strong>
                  <span v-if="row.laborObservaciones"> - {{ row.laborObservaciones }}</span>
                </template>
              </td>
            </tr>
          </tbody>
        </table>
      </section>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  FORMATIVE_ACTIVITY_TYPES,
  isFormativeEvent,
  isServiceEvent,
  normalizeCatalogValue
} from '../constants/activityTypes'

const API_BASE = 'http://localhost:8000/api'
const ROWS_PER_PAGE = 10
const DEFAULT_ENTITY_LABEL = 'Cruz Roja Filial Curicó'

const route = useRoute()
const router = useRouter()
const currentYear = new Date().getFullYear()

const user = ref(null)
const hojaDeVida = ref(null)
const hojasAnuales = ref([])
const antecedentes = ref([])
const actividades = ref([])
const registrosHorasFilial = ref([])
const isLoading = ref(true)
const errorMessage = ref('')
const hasTriggeredPrint = ref(false)

const volunteer = computed(() => user.value?.voluntario || null)
const entityLabel = computed(() => DEFAULT_ENTITY_LABEL)
const previousStudies = computed(() => '')

const exportYears = computed(() => {
  const years = new Set()

  hojasAnuales.value.forEach((historial) => {
    if (historial?.anio) years.add(Number(historial.anio))
  })

  actividades.value.forEach((actividad) => {
    const year = getYearFromDate(actividad.evento?.fecha_inicio)
    if (year) years.add(year)
  })

  antecedentes.value.forEach((antecedente) => {
    const year = getYearFromAntecedente(antecedente)
    if (year) years.add(year)
  })

  registrosHorasFilial.value.forEach((registro) => {
    const year = getYearFromDate(registro.fecha)
    if (year) years.add(year)
  })

  const orderedYears = [...years].sort((a, b) => a - b)

  return orderedYears.length ? orderedYears : [currentYear]
})

const annualRowMap = computed(() =>
  new Map(hojasAnuales.value.map((historial) => [Number(historial.anio), historial]))
)

const annualRows = computed(() =>
  exportYears.value.map((year) => {
    const annual = annualRowMap.value.get(year) || null

    return {
      anio: year,
      cargo: annual?.cargo || '',
      lista: annual?.lista || findLegacyListForYear(year),
      asistencia: buildAttendanceLabel(year, annual),
      formacion: buildFormationSummary(year, annual),
      titulos: buildAchievementColumn(year, annual, 'TITULO'),
      premios: buildAchievementColumn(year, annual, 'PREMIO'),
      laborObservaciones: buildLaborObservaciones(year, annual)
    }
  })
)

const signatureYearChunks = computed(() =>
  chunkWithBlanks(exportYears.value.map((anio) => ({ anio })), ROWS_PER_PAGE)
)

const annualRowChunks = computed(() =>
  chunkWithBlanks(annualRows.value, ROWS_PER_PAGE)
)

const readyToPrint = computed(() => Boolean(user.value && hojaDeVida.value && !isLoading.value))

watch(readyToPrint, async (ready) => {
  if (!ready || hasTriggeredPrint.value || route.query.autoprint === '0') {
    return
  }

  hasTriggeredPrint.value = true
  await nextTick()
  await waitForImages()
  window.print()
})

watch(user, (currentUser) => {
  if (!currentUser?.nombre) {
    return
  }

  document.title = `Hoja de Vida - ${currentUser.nombre}`
})

onMounted(async () => {
  await loadUser()
})

async function loadUser() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const userId = route.params.id
    const response = await axios.get(`${API_BASE}/user/${userId}`)

    user.value = response.data
    hojaDeVida.value = response.data?.voluntario?.hoja_de_vida || null
    hojasAnuales.value = [...(hojaDeVida.value?.hojas_anuales || [])]
      .sort((a, b) => Number(a.anio) - Number(b.anio))
    antecedentes.value = hojaDeVida.value?.antecedentes || []
    registrosHorasFilial.value = [...(response.data?.registros_horas_filial || [])]
      .sort((a, b) => `${a.fecha || ''} ${a.hora_entrada || ''}`.localeCompare(`${b.fecha || ''} ${b.hora_entrada || ''}`))
    actividades.value = response.data?.actividades || []

    if (!hojaDeVida.value) {
      errorMessage.value = 'No existe una hoja de vida asociada al voluntario.'
    }
  } catch (error) {
    errorMessage.value = 'No se pudo cargar la hoja de vida para exportar.'
  } finally {
    isLoading.value = false
  }
}

function goBack() {
  router.push({
    name: 'HistorialView',
    params: { id: route.params.id }
  })
}

function printNow() {
  window.print()
}

function chunkWithBlanks(items, size) {
  const source = items.length ? [...items] : [null]
  const chunks = []

  for (let index = 0; index < source.length; index += size) {
    chunks.push(source.slice(index, index + size))
  }

  if (!chunks.length) {
    chunks.push([])
  }

  return chunks.map((chunk) => {
    const missingItems = Math.max(0, size - chunk.length)
    return [...chunk, ...Array(missingItems).fill(null)]
  })
}

function normalizeTipo(tipo) {
  return normalizeCatalogValue(tipo)
}

function formatDate(dateString) {
  if (!dateString) return ''
  return String(dateString).slice(0, 10).split('-').reverse().join('-')
}

function formatAttendanceValue(value) {
  const numericValue = Number(value)

  if (!Number.isFinite(numericValue)) {
    return ''
  }

  return new Intl.NumberFormat('es-CL', {
    minimumFractionDigits: numericValue % 1 === 0 ? 0 : 2,
    maximumFractionDigits: 2
  }).format(numericValue)
}

function formatHours(value) {
  const numericValue = Number(value || 0)

  if (!numericValue) {
    return ''
  }

  return `${new Intl.NumberFormat('es-CL', {
    minimumFractionDigits: numericValue % 1 === 0 ? 0 : 2,
    maximumFractionDigits: 2
  }).format(numericValue)} h`
}

function getYearFromDate(dateString) {
  if (!dateString) return null
  return Number(String(dateString).slice(0, 4))
}

function getYearFromAntecedente(antecedente) {
  return getYearFromDate(antecedente?.fecha_inicio) || getYearFromDate(antecedente?.fecha_termino)
}

function hasStoredAnnualValue(value) {
  return value !== null && value !== undefined
}

function parseMultilineField(value) {
  return String(value || '')
    .split(/\r?\n/)
    .map((item) => item.trim())
    .filter(Boolean)
}

function getAntecedentesForYear(year) {
  return antecedentes.value.filter((antecedente) => getYearFromAntecedente(antecedente) === Number(year))
}

function getActivitiesForYear(year) {
  return actividades.value.filter((actividad) => getYearFromDate(actividad.evento?.fecha_inicio) === Number(year))
}

function getFormativeActivityLines(year, tipo) {
  return getActivitiesForYear(year)
    .filter((actividad) => isFormativeEvent(actividad.evento?.tipo))
    .filter((actividad) => FORMATIVE_ACTIVITY_TYPES.includes(normalizeTipo(actividad.tipo)))
    .filter((actividad) => normalizeTipo(actividad.tipo) === tipo)
    .filter((actividad) => actividad.pivot?.asistio !== false)
    .map((actividad) =>
      `${actividad.evento?.nombre || actividad.tipo}${actividad.evento?.fecha_inicio ? ` (${formatDate(actividad.evento.fecha_inicio)})` : ''}`
    )
    .filter(Boolean)
}

function getStoredOrDerivedLines(annual, fieldName, fallbackFactory) {
  if (annual && hasStoredAnnualValue(annual[fieldName])) {
    return parseMultilineField(annual[fieldName])
  }

  return fallbackFactory()
}

function splitAchievementLines(value) {
  const grouped = {
    TITULO: [],
    PREMIO: [],
  }

  parseMultilineField(value).forEach((line) => {
    const normalizedLine = normalizeCatalogValue(line)

    if (normalizedLine.startsWith('TITULO:') || normalizedLine.startsWith('TITULOS:')) {
      grouped.TITULO.push(line.replace(/^titulos?:\s*/i, '').trim())
      return
    }

    if (normalizedLine.startsWith('PREMIO:') || normalizedLine.startsWith('PREMIOS:')) {
      grouped.PREMIO.push(line.replace(/^premios?:\s*/i, '').trim())
      return
    }

    grouped.TITULO.push(line)
  })

  return grouped
}

function buildAchievementColumn(year, annual, tipo) {
  const lines = getAntecedentesForYear(year)
    .filter((antecedente) => normalizeTipo(antecedente.tipo) === tipo)
    .map((antecedente) => antecedente.nombre?.trim())
    .filter(Boolean)

  if (lines.length) {
    return lines.join('\n')
  }

  if (annual && hasStoredAnnualValue(annual.titulos_premios)) {
    return splitAchievementLines(annual.titulos_premios)[tipo].join('\n')
  }

  return ''
}

function buildFormationSummary(year, annual) {
  const cursos = getStoredOrDerivedLines(annual, 'cursos', () => getFormativeActivityLines(year, 'CURSO'))
  const talleres = getStoredOrDerivedLines(annual, 'talleres', () => getFormativeActivityLines(year, 'TALLER'))
  const seminarios = getStoredOrDerivedLines(annual, 'seminarios', () => getFormativeActivityLines(year, 'SEMINARIO'))

  return [
    cursos.length ? `Cursos: ${cursos.join('; ')}` : '',
    talleres.length ? `Talleres: ${talleres.join('; ')}` : '',
    seminarios.length ? `Seminarios: ${seminarios.join('; ')}` : ''
  ]
    .filter(Boolean)
    .join('\n')
}

function buildAttendanceLabel(year, annual) {
  if (annual?.porcentaje_asistencia !== null && annual?.porcentaje_asistencia !== undefined && annual?.porcentaje_asistencia !== '') {
    return `${formatAttendanceValue(annual.porcentaje_asistencia)}%`
  }

  const serviceActivities = getActivitiesForYear(year)
    .filter((actividad) => isServiceEvent(actividad.evento?.tipo))

  if (!serviceActivities.length) {
    return ''
  }

  const attendedActivities = serviceActivities.filter((actividad) => actividad.pivot?.asistio !== false).length
  return `${formatAttendanceValue((attendedActivities / serviceActivities.length) * 100)}%`
}

function getFilialHoursForYear(year) {
  return registrosHorasFilial.value
    .filter((registro) => getYearFromDate(registro.fecha) === Number(year))
    .reduce((total, registro) => total + Number(registro.horas_totales || 0), 0)
}

function findLegacyListForYear(year) {
  return getAntecedentesForYear(year)
    .find((antecedente) => normalizeTipo(antecedente.tipo) === 'CARGO')
    ?.nombre || ''
}

function buildLaborObservaciones(year, annual) {
  const lines = []

  if (annual?.labor_efectuada) {
    lines.push(`Labor: ${annual.labor_efectuada}`)
  }

  if (annual?.observaciones_generales) {
    lines.push(`Observaciones: ${annual.observaciones_generales}`)
  }

  const filialHours = formatHours(getFilialHoursForYear(year))
  if (filialHours) {
    lines.push(`Horas en filial: ${filialHours}`)
  }

  return lines.join('\n')
}

async function waitForImages() {
  const pendingImages = [...document.images].filter((image) => !image.complete)

  if (!pendingImages.length) {
    return
  }

  await Promise.all(
    pendingImages.map((image) =>
      new Promise((resolve) => {
        image.addEventListener('load', resolve, { once: true })
        image.addEventListener('error', resolve, { once: true })
      })
    )
  )
}
</script>

<style scoped>
.pdf-export-page {
  min-height: 100vh;
  background: #eef1f5;
  padding: 1.25rem;
  color: #111827;
  font-family: Arial, Helvetica, sans-serif;
}

.pdf-toolbar {
  position: sticky;
  top: 0;
  z-index: 20;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin: 0 auto 1rem;
  width: min(8.5in, 100%);
}

.pdf-toolbar__button {
  border: 1px solid #cbd5e1;
  border-radius: 999px;
  background: #ffffff;
  color: #0f172a;
  padding: 0.7rem 1rem;
  font-size: 0.95rem;
  font-weight: 700;
}

.pdf-toolbar__button--primary {
  background: #d72638;
  border-color: #d72638;
  color: #ffffff;
}

.pdf-toolbar__hint {
  color: #475569;
  font-size: 0.9rem;
}

.pdf-state {
  width: min(8.5in, 100%);
  margin: 2rem auto 0;
  border-radius: 18px;
  background: #ffffff;
  padding: 2rem;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
}

.pdf-state--error {
  color: #991b1b;
}

.pdf-document {
  display: grid;
  gap: 1.25rem;
}

.pdf-sheet {
  width: 8.5in;
  min-height: 14in;
  margin: 0 auto;
  background: #ffffff;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
  padding: 0.32in 0.42in 0.38in;
  box-sizing: border-box;
  break-after: page;
  page-break-after: always;
}

.sheet-header {
  display: grid;
  justify-items: center;
  gap: 0.08in;
  margin-bottom: 0.14in;
}

.sheet-header__cross {
  position: relative;
  width: 0.62in;
  height: 0.62in;
  margin: 0 auto;
}

.cross {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  background: #e11d2e;
}

.cross--vertical {
  width: 0.18in;
  height: 0.62in;
}

.cross--horizontal {
  width: 0.62in;
  height: 0.18in;
}

.sheet-header__content {
  text-align: center;
}

.sheet-header__content h1 {
  margin: 0;
  font-size: 0.25in;
  font-weight: 800;
  letter-spacing: 0.01in;
}

.sheet-header__content p {
  margin: 0.03in 0 0;
  font-size: 0.14in;
}

.identity-layout {
  margin-bottom: 0.18in;
}

.identity-photo {
  aspect-ratio: 1 / 1.14;
  border: 2px solid #111827;
  background: #f8fafc;
  overflow: hidden;
}

.identity-photo--cover {
  width: 1.35in;
  margin: 0 auto 0.18in;
}

.identity-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.identity-fields {
  display: grid;
  gap: 0.12in;
}

.identity-line {
  min-height: 0.27in;
  border-bottom: 1px dotted #111827;
  display: flex;
  align-items: flex-end;
  gap: 0.12in;
  font-size: 0.13in;
  padding-bottom: 0.03in;
}

.identity-line--split {
  border-bottom: none;
  gap: 0.2in;
}

.identity-split-field {
  flex: 1;
  min-height: 0.27in;
  border-bottom: 1px dotted #111827;
  display: flex;
  align-items: flex-end;
  gap: 0.12in;
  padding-bottom: 0.03in;
}

.identity-label {
  white-space: nowrap;
}

.identity-value {
  display: inline-flex;
  align-items: flex-end;
  min-height: 100%;
}

.signature-table,
.annual-table,
.observations-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
  border: 2px solid #111827;
}

.signature-table th,
.signature-table td,
.annual-table th,
.annual-table td,
.observations-table td {
  border: 1px solid #111827;
  vertical-align: top;
}

.signature-table th,
.annual-table th {
  text-align: center;
  font-size: 0.13in;
  font-weight: 700;
}

.signature-table th {
  height: 0.34in;
}

.signature-table td {
  height: 0.39in;
  font-size: 0.13in;
  padding: 0.08in 0.1in;
}

.signature-table th:first-child,
.signature-table td:first-child {
  width: 34%;
  text-align: center;
}

.annual-table thead th {
  height: 0.52in;
  padding: 0.06in;
  line-height: 1.15;
}

.annual-table tbody td {
  height: 0.93in;
  padding: 0.06in 0.07in;
  font-size: 0.105in;
  text-align: center;
}

.annual-table__col--year {
  width: 7.5%;
}

.annual-table__col--cargo {
  width: 13%;
}

.annual-table__col--lista {
  width: 8%;
}

.annual-table__col--attendance {
  width: 13%;
}

.annual-table__col--formation {
  width: 23%;
}

.annual-table__col--titles {
  width: 17%;
}

.annual-table__col--awards {
  width: 18.5%;
}

.cell-preline {
  text-align: left !important;
  white-space: pre-line;
  line-height: 1.22;
}

.annual-note {
  margin-top: 0.1in;
  font-size: 0.12in;
  line-height: 1.35;
}

.pdf-sheet--observations {
  padding-top: 0.5in;
}

.observations-header {
  border: 2px solid #111827;
  border-bottom: none;
  text-align: center;
  font-size: 0.16in;
  font-weight: 700;
  padding: 0.12in 0.16in;
}

.observations-table td {
  height: 0.93in;
  padding: 0.08in 0.12in;
  font-size: 0.12in;
}

.observations-cell {
  white-space: pre-line;
  line-height: 1.3;
}

@media (max-width: 900px) {
  .pdf-export-page {
    padding: 0.75rem;
  }

  .pdf-sheet {
    width: 100%;
    min-height: auto;
    padding: 1rem;
  }

  .sheet-header {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }

  .identity-layout {
    grid-template-columns: 1fr;
  }
}

@media print {
  :global(body) {
    background: #ffffff;
  }

  .pdf-export-page {
    background: #ffffff;
    padding: 0;
  }

  .no-print {
    display: none !important;
  }

  .pdf-document {
    gap: 0;
  }

  .pdf-sheet {
    margin: 0;
    box-shadow: none;
  }
}
</style>
