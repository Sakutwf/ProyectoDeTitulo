<template>
  <div class="pdf-export-page">
    <div class="pdf-toolbar no-print">
      <button type="button" class="pdf-toolbar__button" @click="goBack">
        Volver
      </button>
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

    <div v-if="isLoading" class="pdf-state">
      Cargando hoja de vida...
    </div>

    <div v-else-if="errorMessage" class="pdf-state pdf-state--error">
      {{ errorMessage }}
    </div>

    <div v-else-if="volunteer && selectedAnnual" class="pdf-document">
      <article class="pdf-sheet pdf-sheet--page1">
        <header class="page-header">
          <img :src="logoSrc" alt="Cruz Roja Chilena" class="page-logo">
          <h1>HOJA DE VIDA DEL VOLUNTARIO/A</h1>
        </header>

        <section class="top-grid">
          <table class="sheet-table sheet-table--institutional">
            <tbody>
              <tr class="section-bar">
                <th colspan="2">Datos Institucionales</th>
              </tr>
              <tr>
                <td class="label-cell">Filial</td>
                <td>{{ volunteer.filial?.nombre || '' }}</td>
              </tr>
              <tr>
                <td class="label-cell">Comité regional</td>
                <td>{{ volunteer.filial?.comite_regional || '' }}</td>
              </tr>
              <tr>
                <td class="label-cell">Año</td>
                <td>{{ selectedAnnual.anio || '' }}</td>
              </tr>
              <tr>
                <td class="label-cell">Asistencia Anual</td>
                <td>{{ attendanceLabel }}</td>
              </tr>
            </tbody>
          </table>

          <div class="photo-box">
            <img
              v-if="volunteer.foto_perfil_url"
              :src="volunteer.foto_perfil_url"
              :alt="`Foto de ${displayName}`"
            >
            <span v-else>Foto</span>
          </div>
        </section>

        <table class="sheet-table personal-table">
          <tbody>
            <tr class="section-bar section-bar--short">
              <th colspan="8">Datos Personales</th>
            </tr>
            <tr>
              <td class="label-cell" colspan="2">Nombres</td>
              <td colspan="3">{{ volunteer.nombres || '' }}</td>
              <td class="label-cell">RUT</td>
              <td colspan="2">{{ volunteer.rut || '' }}</td>
            </tr>
            <tr>
              <td class="label-cell" colspan="2">Apellidos</td>
              <td colspan="3">{{ volunteer.apellidos || '' }}</td>
              <td class="label-cell">Celular</td>
              <td colspan="2">{{ volunteer.celular || '' }}</td>
            </tr>
            <tr>
              <td class="label-cell" colspan="2">Nacionalidad</td>
              <td colspan="3">{{ volunteer.nacionalidad || '' }}</td>
              <td class="label-cell">Edad</td>
              <td colspan="2">{{ ageValue }}</td>
            </tr>
            <tr>
              <td class="label-cell" colspan="2">Fecha de nacimiento</td>
              <td colspan="3">{{ formatDate(volunteer.fecha_nacimiento) }}</td>
              <td class="label-cell">Alergia</td>
              <td colspan="2">{{ volunteer.alergias || '' }}</td>
            </tr>
            <tr>
              <td class="label-cell" colspan="2">Fecha incorporación</td>
              <td colspan="6">{{ formatDate(volunteer.fecha_incorporacion) }}</td>
            </tr>
            <tr>
              <td class="label-cell" colspan="2">Enfermedades</td>
              <td colspan="6">{{ volunteer.enfermedades || '' }}</td>
            </tr>
            <tr>
              <td class="label-cell" colspan="2">Correo electrónico</td>
              <td colspan="6">{{ volunteer.correo_electronico || '' }}</td>
            </tr>
            <tr>
              <td class="label-cell" colspan="2">Domicilio</td>
              <td colspan="6">{{ volunteer.domicilio || '' }}</td>
            </tr>
            <tr>
              <td class="label-cell" colspan="3">Estuvo en comisión de servicio</td>
              <td class="choice-cell">Si</td>
              <td class="check-cell">{{ mark(selectedAnnual.estuvo_comision_servicio) }}</td>
              <td class="choice-cell">No</td>
              <td class="check-cell">{{ mark(!selectedAnnual.estuvo_comision_servicio) }}</td>
              <td></td>
            </tr>
            <tr>
              <td class="label-cell" colspan="3">Inicio de la comisión de servicio</td>
              <td colspan="5">{{ formatDate(selectedAnnual.comision_fecha_inicio) }}</td>
            </tr>
            <tr>
              <td class="label-cell" colspan="3">Término de la comisión de servicio</td>
              <td colspan="5">{{ formatDate(selectedAnnual.comision_fecha_termino) }}</td>
            </tr>
            <tr>
              <td class="label-cell" colspan="3">Lugar de Comisión de servicio</td>
              <td colspan="5">{{ selectedAnnual.comision_lugar || '' }}</td>
            </tr>
            <tr>
              <td class="label-cell" colspan="3">Actividad de comisión de servicio</td>
              <td colspan="5">{{ selectedAnnual.comision_actividad || '' }}</td>
            </tr>
            <tr>
              <td class="label-cell" colspan="3">Nombre de contacto en emergencia</td>
              <td colspan="5">{{ volunteer.contacto_emergencia_nombre || '' }}</td>
            </tr>
            <tr>
              <td class="label-cell" colspan="3">Numero de contacto</td>
              <td colspan="5">{{ volunteer.contacto_emergencia_numero || '' }}</td>
            </tr>
          </tbody>
        </table>

        <table class="sheet-table">
          <tbody>
            <tr class="section-bar section-bar--title-block">
              <th>Títulos aprobados</th>
              <th class="filler-cell" colspan="2"></th>
            </tr>
            <tr>
              <td class="label-cell">Título</td>
              <td class="label-cell">Entregado por</td>
              <td class="label-cell">Código del titulo</td>
            </tr>
            <tr v-for="(row, index) in titleRows" :key="`title-${index}`">
              <td>{{ row.titulo }}</td>
              <td>{{ row.entregado_por }}</td>
              <td>{{ row.codigo_titulo }}</td>
            </tr>
          </tbody>
        </table>

        <table class="sheet-table">
          <tbody>
            <tr class="section-bar section-bar--title-block">
              <th>Cursos aprobados</th>
              <th class="filler-cell" colspan="2"></th>
            </tr>
            <tr>
              <td class="label-cell">Nombre del curso</td>
              <td class="label-cell">Entregado por</td>
              <td class="label-cell">Código del curso</td>
            </tr>
            <tr v-for="(row, index) in courseRows" :key="`course-${index}`">
              <td>{{ row.nombre_curso }}</td>
              <td>{{ row.entregado_por }}</td>
              <td>{{ row.codigo_curso }}</td>
            </tr>
          </tbody>
        </table>
      </article>

      <article class="pdf-sheet pdf-sheet--page2">
        <table class="sheet-table">
          <tbody>
            <tr class="section-bar section-bar--title-block section-bar--narrow">
              <th>Sanciones</th>
              <th class="filler-cell" colspan="3"></th>
            </tr>
            <tr>
              <td class="label-cell">Tipo de Sanción</td>
              <td>{{ mainSanction?.tipo_sancion || '' }}</td>
              <td class="label-cell">Firma de voluntario</td>
              <td></td>
            </tr>
            <tr>
              <td class="label-cell">Fecha</td>
              <td>{{ formatDate(mainSanction?.fecha) }}</td>
              <td class="label-cell">Firma de voluntario</td>
              <td></td>
            </tr>
            <tr class="sanction-summary-row">
              <td class="label-cell">Resumen de sanción</td>
              <td colspan="3" class="top-cell">{{ sanctionSummary }}</td>
            </tr>
            <tr>
              <td class="label-cell">Apelación</td>
              <td>{{ mainSanction?.apelacion || '' }}</td>
              <td class="label-cell">Fecha apelación</td>
              <td>{{ formatDate(mainSanction?.fecha_apelacion) }}</td>
            </tr>
            <tr>
              <td class="label-cell">Decisión CIG</td>
              <td colspan="3">{{ mainSanction?.decision_cig || '' }}</td>
            </tr>
          </tbody>
        </table>

        <table class="sheet-table recognition-table">
          <tbody>
            <tr class="section-bar section-bar--title-block section-bar--medium">
              <th>Reconocimiento anual</th>
              <th class="filler-cell" colspan="3"></th>
            </tr>
            <tr>
              <td class="label-cell">Servicios Extraordinario</td>
              <td class="check-cell">{{ mark(recognition.servicio_extraordinario) }}</td>
              <td class="label-cell">Abnegación</td>
              <td class="check-cell">{{ mark(recognition.abnegacion) }}</td>
            </tr>
            <tr>
              <td class="label-cell">3 medalla de honor</td>
              <td class="check-cell">{{ mark(recognition.medalla_honor_3) }}</td>
              <td class="label-cell">2 medalla de honor</td>
              <td class="check-cell">{{ mark(recognition.medalla_honor_2) }}</td>
            </tr>
            <tr>
              <td class="label-cell">1 medalla de honor</td>
              <td class="check-cell">{{ mark(recognition.medalla_honor_1) }}</td>
              <td class="label-cell">Vittorio cucchini</td>
              <td class="check-cell">{{ mark(recognition.vittorio_cucchini) }}</td>
            </tr>
            <tr>
              <td class="label-cell">Promesa</td>
              <td class="check-cell">{{ mark(recognition.promesa) }}</td>
              <td class="label-cell">Juramento</td>
              <td class="check-cell">{{ mark(recognition.juramento) }}</td>
            </tr>
          </tbody>
        </table>

        <table class="sheet-table comments-table">
          <tbody>
            <tr class="section-bar section-bar--title-block section-bar--small">
              <th>Comentarios</th>
              <th class="filler-cell"></th>
            </tr>
            <tr>
              <td colspan="2" class="comments-cell">{{ commentText }}</td>
            </tr>
          </tbody>
        </table>

        <section class="signatures">
          <div class="signature-slot">
            <div class="signature-line"></div>
            <span>Firma voluntario/a</span>
          </div>
          <div class="signature-slot">
            <div class="signature-line"></div>
            <span>Firma secretario/a</span>
          </div>
        </section>

        <ul class="statement-list">
          <li>El/la voluntario/a acepta la información proporcionada en esta hoja de vida anual que elaboro la Directiva de la Filial.</li>
          <li>La Directiva de la Filial, mediante el/la secretario/a da fe que la información proporcionada en la hija de vida del voluntario es fehaciente y recopila el actuar del año mencionado en dicho documento</li>
        </ul>
      </article>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import logoSrc from '../assets/LogoVertical.svg'
import { API_BASE } from '../config/api'

const route = useRoute()
const router = useRouter()

const isLoading = ref(true)
const errorMessage = ref('')
const user = ref(null)

const volunteer = computed(() => user.value?.voluntario || null)

const annualRecords = computed(() =>
  [...(volunteer.value?.hoja_vida_anual || [])].sort((left, right) => Number(right.anio) - Number(left.anio))
)

const selectedAnnual = computed(() => {
  const requestedYear = Number(route.query.anio)
  if (requestedYear) {
    return annualRecords.value.find((record) => Number(record.anio) === requestedYear) || null
  }
  return annualRecords.value[0] || null
})

const displayName = computed(() => [volunteer.value?.nombres, volunteer.value?.apellidos].filter(Boolean).join(' ') || user.value?.username || 'Voluntario')

const ageValue = computed(() => {
  const dateValue = volunteer.value?.fecha_nacimiento
  if (!dateValue) return ''
  const birthDate = new Date(`${dateValue}T00:00:00`)
  if (Number.isNaN(birthDate.getTime())) return ''
  const today = new Date()
  let age = today.getFullYear() - birthDate.getFullYear()
  const monthDelta = today.getMonth() - birthDate.getMonth()
  if (monthDelta < 0 || (monthDelta === 0 && today.getDate() < birthDate.getDate())) age -= 1
  return String(age)
})

const attendanceLabel = computed(() => {
  if (!selectedAnnual.value) return ''
  const percentage = selectedAnnual.value.asistencia_anual_porcentaje
  if (percentage) return `${Number(percentage)}%`
  return ''
})

const titleRows = computed(() => padRows(selectedAnnual.value?.titulos || [], 3, () => ({ titulo: '', entregado_por: '', codigo_titulo: '' })))
const courseRows = computed(() => padRows(selectedAnnual.value?.cursos || [], 5, () => ({ nombre_curso: '', entregado_por: '', codigo_curso: '' })))
const mainSanction = computed(() => selectedAnnual.value?.sanciones?.[0] || null)
const recognition = computed(() => selectedAnnual.value?.reconocimiento || {})

const sanctionSummary = computed(() => {
  if (!mainSanction.value) return ''
  const base = mainSanction.value.resumen_sancion || ''
  const extraCount = Math.max((selectedAnnual.value?.sanciones?.length || 0) - 1, 0)
  if (!extraCount) return base
  return [base, `Además existen ${extraCount} sanción(es) adicional(es) registradas en este periodo.`].filter(Boolean).join(' ')
})

const commentText = computed(() => selectedAnnual.value?.comentarios || '')
const readyToPrint = computed(() => Boolean(volunteer.value && selectedAnnual.value))

onMounted(() => {
  fetchUser()
})

async function fetchUser() {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await axios.get(`${API_BASE}/user/${route.params.id}`)
    user.value = response.data
    if (!response.data?.voluntario) {
      errorMessage.value = 'El usuario no tiene una ficha de voluntario asociada.'
      return
    }
    if (!selectedAnnual.value) {
      errorMessage.value = 'No existe una hoja de vida anual para exportar.'
    }
  } catch {
    errorMessage.value = 'No se pudo cargar la hoja de vida para exportar.'
  } finally {
    isLoading.value = false
  }
}

function formatDate(value) {
  if (!value) return ''
  const parsed = new Date(`${value}T00:00:00`)
  if (Number.isNaN(parsed.getTime())) return value
  return parsed.toLocaleDateString('es-CL')
}

function padRows(items, minimumRows, createEmptyRow) {
  const rows = [...items]
  while (rows.length < minimumRows) rows.push(createEmptyRow())
  return rows.slice(0, minimumRows)
}

function mark(value) {
  return value ? 'X' : ''
}

function goBack() {
  router.push({
    name: 'HistorialView',
    params: { id: route.params.id },
    query: route.query
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

.pdf-toolbar__button:disabled {
  opacity: 0.7;
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
  gap: 1.25rem;
  justify-content: center;
}

.pdf-sheet {
  width: 8.5in;
  min-height: 11in;
  padding: 0.72in 0.55in 0.58in;
  background: #fff;
  color: #111;
  box-shadow: 0 18px 40px rgba(15, 47, 95, 0.15);
}

.page-header {
  display: grid;
  grid-template-columns: 1.1in 1fr;
  align-items: center;
  margin-bottom: 0.18in;
}

.page-logo {
  width: 0.72in;
}

.page-header h1 {
  margin: 0;
  text-align: center;
  font-size: 0.26in;
  font-weight: 800;
}

.top-grid {
  display: grid;
  grid-template-columns: 1fr 1.55in;
  gap: 0.24in;
  align-items: start;
}

.photo-box {
  height: 1.55in;
  border: 1px solid #1a1a1a;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding-top: 0.04in;
  font-size: 0.16in;
  overflow: hidden;
}

.photo-box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.sheet-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
  margin-top: 0.18in;
}

.sheet-table th,
.sheet-table td {
  border: 1px solid #1a1a1a;
  padding: 0.035in 0.06in;
  font-size: 0.135in;
  line-height: 1.15;
  vertical-align: middle;
  word-break: break-word;
}

.sheet-table--institutional {
  margin-top: 0;
}

.section-bar th {
  background: #f10808;
  color: #fff;
  font-weight: 800;
  text-align: center;
  font-size: 0.16in;
  padding: 0.035in 0.05in;
}

.section-bar--short th {
  width: 2.15in;
}

.section-bar--title-block th:first-child {
  width: 2.7in;
}

.section-bar--narrow th:first-child {
  width: 1.55in;
}

.section-bar--medium th:first-child {
  width: 2.45in;
}

.section-bar--small th:first-child {
  width: 2.4in;
}

.filler-cell {
  background: transparent !important;
  color: transparent !important;
}

.label-cell {
  font-weight: 400;
}

.choice-cell,
.check-cell {
  text-align: center;
}

.check-cell {
  font-weight: 800;
}

.top-cell {
  vertical-align: top !important;
}

.sanction-summary-row td {
  height: 0.75in;
}

.comments-cell {
  height: 2.25in;
  vertical-align: top !important;
  white-space: pre-line;
}

.signatures {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.4in;
  margin: 0.85in 0 0.6in;
}

.signature-slot {
  text-align: center;
  font-size: 0.14in;
}

.signature-line {
  border-top: 1px solid #111;
  margin-bottom: 0.06in;
}

.statement-list {
  margin: 0;
  padding-left: 0.34in;
  font-size: 0.13in;
  line-height: 1.35;
}

.statement-list li + li {
  margin-top: 0.28in;
}

@media print {
  @page {
    size: letter;
    margin: 0;
  }

  .pdf-export-page {
    padding: 0;
    background: #fff;
  }

  .no-print {
    display: none !important;
  }

  .pdf-document {
    gap: 0;
  }

  .pdf-sheet {
    box-shadow: none;
    margin: 0;
    break-after: page;
    page-break-after: always;
  }

  .pdf-sheet:last-child {
    break-after: auto;
    page-break-after: auto;
  }
}
</style>
