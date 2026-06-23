<template>
  <div class="d-flex">
    <SidebarMenu />

    <div class="content-wrapper">
      <div class="content-header">
        <div class="header-main">
          <div>
            <p class="header-kicker">Hoja de vida anual</p>
            <h1>{{ displayName }}</h1>
            <p class="header-subtitle">
              Formato alineado con la nueva hoja de vida institucional del voluntariado.
            </p>
          </div>

          <button
            type="button"
            class="btn btn-danger header-action"
            :disabled="!selectedAnnual"
            @click="openPdfExport"
          >
            <i class="fa-solid fa-file-pdf me-2"></i>
            Exportar PDF
          </button>
        </div>
      </div>

      <div class="content">
        <div v-if="isLoading" class="panel-empty">
          Cargando hoja de vida...
        </div>

        <div v-else-if="errorMessage" class="panel-empty panel-empty--error">
          {{ errorMessage }}
        </div>

        <template v-else-if="volunteer">
          <section class="hero-card">
            <div class="hero-card__photo">
              <img
                v-if="volunteer.foto_perfil_url"
                :src="volunteer.foto_perfil_url"
                :alt="`Foto de ${displayName}`"
              >
              <span v-else>Sin foto</span>
            </div>

            <div class="hero-card__body">
              <div class="hero-card__identity">
                <div>
                  <p class="eyebrow">N. Registro {{ volunteer.n_registro }}</p>
                  <h2>{{ displayName }}</h2>
                </div>

                <span class="status-chip">
                  {{ user?.estado ? 'Perfil activo' : 'Perfil inactivo' }}
                </span>
              </div>

              <div class="hero-card__meta">
                <div>
                  <span>Filial</span>
                  <strong>{{ volunteer.filial?.nombre || 'Sin filial' }}</strong>
                </div>
                <div>
                  <span>Comité regional</span>
                  <strong>{{ volunteer.filial?.comite_regional || 'Sin registro' }}</strong>
                </div>
                <div>
                  <span>Correo</span>
                  <strong>{{ user?.email || 'Sin correo' }}</strong>
                </div>
                <div>
                  <span>Celular</span>
                  <strong>{{ volunteer.celular || 'Sin celular' }}</strong>
                </div>
              </div>
            </div>
          </section>

          <section class="panel">
            <div class="panel__header">
              <div>
                <p class="panel-kicker">Periodo anual</p>
                <h3>Selecciona el año que quieres revisar</h3>
              </div>
            </div>

            <div v-if="annualRecords.length" class="annual-grid">
              <HistorialAnualCard
                v-for="record in annualRecords"
                :key="record.id"
                :historial="record"
                :active="record.anio === selectedYear"
                :to="yearLink(record.anio)"
              />
            </div>

            <div v-else class="panel-empty panel-empty--soft">
              Este voluntario todavía no tiene hojas de vida anuales registradas.
            </div>
          </section>

          <template v-if="selectedAnnual">
            <section class="detail-grid">
              <article class="panel">
                <div class="panel__header">
                  <div>
                    <p class="panel-kicker">Datos institucionales</p>
                    <h3>Periodo {{ selectedAnnual.anio }}</h3>
                  </div>
                </div>

                <div class="info-table">
                  <div class="info-row">
                    <span>Filial</span>
                    <strong>{{ volunteer.filial?.nombre || 'Sin filial' }}</strong>
                  </div>
                  <div class="info-row">
                    <span>Comité regional</span>
                    <strong>{{ volunteer.filial?.comite_regional || 'Sin registro' }}</strong>
                  </div>
                  <div class="info-row">
                    <span>Año</span>
                    <strong>{{ selectedAnnual.anio }}</strong>
                  </div>
                  <div class="info-row">
                    <span>Asistencia anual</span>
                    <strong>{{ attendanceLabel(selectedAnnual) }}</strong>
                  </div>
                </div>
              </article>

              <article class="panel">
                <div class="panel__header">
                  <div>
                    <p class="panel-kicker">Comisión de servicio</p>
                    <h3>Registro del periodo</h3>
                  </div>
                </div>

                <div class="info-table">
                  <div class="info-row">
                    <span>¿Estuvo en comisión?</span>
                    <strong>{{ yesNo(selectedAnnual.estuvo_comision_servicio) }}</strong>
                  </div>
                  <div class="info-row">
                    <span>Inicio</span>
                    <strong>{{ formatDate(selectedAnnual.comision_fecha_inicio) }}</strong>
                  </div>
                  <div class="info-row">
                    <span>Término</span>
                    <strong>{{ formatDate(selectedAnnual.comision_fecha_termino) }}</strong>
                  </div>
                  <div class="info-row">
                    <span>Lugar</span>
                    <strong>{{ selectedAnnual.comision_lugar || 'Sin registro' }}</strong>
                  </div>
                  <div class="info-row info-row--stacked">
                    <span>Actividad</span>
                    <strong>{{ selectedAnnual.comision_actividad || 'Sin registro' }}</strong>
                  </div>
                </div>
              </article>
            </section>

            <section class="panel">
              <div class="panel__header">
                <div>
                  <p class="panel-kicker">Datos personales</p>
                  <h3>Información consistente con el formato nuevo</h3>
                </div>
              </div>

              <div class="personal-grid">
                <div class="data-chip"><span>Nombres</span><strong>{{ volunteer.nombres || 'Sin registro' }}</strong></div>
                <div class="data-chip"><span>Apellidos</span><strong>{{ volunteer.apellidos || 'Sin registro' }}</strong></div>
                <div class="data-chip"><span>RUT</span><strong>{{ volunteer.rut || 'Sin registro' }}</strong></div>
                <div class="data-chip"><span>Celular</span><strong>{{ volunteer.celular || 'Sin registro' }}</strong></div>
                <div class="data-chip"><span>Nacionalidad</span><strong>{{ volunteer.nacionalidad || 'Sin registro' }}</strong></div>
                <div class="data-chip"><span>Edad</span><strong>{{ ageLabel }}</strong></div>
                <div class="data-chip"><span>Fecha de nacimiento</span><strong>{{ formatDate(volunteer.fecha_nacimiento) }}</strong></div>
                <div class="data-chip"><span>Alergias</span><strong>{{ volunteer.alergias || 'Sin registro' }}</strong></div>
                <div class="data-chip"><span>Fecha incorporación</span><strong>{{ formatDate(volunteer.fecha_incorporacion) }}</strong></div>
                <div class="data-chip"><span>Enfermedades</span><strong>{{ volunteer.enfermedades || 'Sin registro' }}</strong></div>
                <div class="data-chip"><span>Correo electrónico</span><strong>{{ user?.email || 'Sin correo' }}</strong></div>
                <div class="data-chip data-chip--wide"><span>Domicilio</span><strong>{{ volunteer.domicilio || 'Sin registro' }}</strong></div>
                <div class="data-chip data-chip--wide"><span>Contacto de emergencia</span><strong>{{ volunteer.contacto_emergencia_nombre || 'Sin registro' }}</strong></div>
                <div class="data-chip"><span>Número de contacto</span><strong>{{ volunteer.contacto_emergencia_numero || 'Sin registro' }}</strong></div>
              </div>
            </section>

            <section class="detail-grid">
              <article class="panel">
                <div class="panel__header">
                  <div>
                    <p class="panel-kicker">Títulos aprobados</p>
                    <h3>{{ selectedAnnual.titulos?.length || 0 }} registro(s)</h3>
                  </div>
                </div>

                <div v-if="selectedAnnual.titulos?.length" class="table-responsive">
                  <table class="sheet-table">
                    <thead>
                      <tr>
                        <th>Título</th>
                        <th>Entregado por</th>
                        <th>Código</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="title in selectedAnnual.titulos" :key="title.id">
                        <td>{{ title.titulo || 'Sin registro' }}</td>
                        <td>{{ title.entregado_por || 'Sin registro' }}</td>
                        <td>{{ title.codigo_titulo || 'Sin registro' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div v-else class="panel-empty panel-empty--soft">Sin títulos registrados.</div>
              </article>

              <article class="panel">
                <div class="panel__header">
                  <div>
                    <p class="panel-kicker">Cursos aprobados</p>
                    <h3>{{ selectedAnnual.cursos?.length || 0 }} registro(s)</h3>
                  </div>
                </div>

                <div v-if="selectedAnnual.cursos?.length" class="table-responsive">
                  <table class="sheet-table">
                    <thead>
                      <tr>
                        <th>Curso</th>
                        <th>Entregado por</th>
                        <th>Código</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="course in selectedAnnual.cursos" :key="course.id">
                        <td>{{ course.nombre_curso || 'Sin registro' }}</td>
                        <td>{{ course.entregado_por || 'Sin registro' }}</td>
                        <td>{{ course.codigo_curso || 'Sin registro' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div v-else class="panel-empty panel-empty--soft">Sin cursos registrados.</div>
              </article>
            </section>

            <section class="detail-grid">
              <article class="panel">
                <div class="panel__header">
                  <div>
                    <p class="panel-kicker">Sanciones</p>
                    <h3>{{ selectedAnnual.sanciones?.length || 0 }} registro(s)</h3>
                  </div>
                </div>

                <div v-if="selectedAnnual.sanciones?.length" class="sanction-list">
                  <article
                    v-for="sanction in selectedAnnual.sanciones"
                    :key="sanction.id"
                    class="sanction-card"
                  >
                    <div class="sanction-card__row">
                      <span>Tipo</span>
                      <strong>{{ sanction.tipo_sancion || 'Sin registro' }}</strong>
                    </div>
                    <div class="sanction-card__row">
                      <span>Fecha</span>
                      <strong>{{ formatDate(sanction.fecha) }}</strong>
                    </div>
                    <div class="sanction-card__row sanction-card__row--stacked">
                      <span>Resumen</span>
                      <strong>{{ sanction.resumen_sancion || 'Sin registro' }}</strong>
                    </div>
                    <div class="sanction-card__row sanction-card__row--stacked">
                      <span>Apelación</span>
                      <strong>{{ sanction.apelacion || 'Sin registro' }}</strong>
                    </div>
                    <div class="sanction-card__row">
                      <span>Fecha apelación</span>
                      <strong>{{ formatDate(sanction.fecha_apelacion) }}</strong>
                    </div>
                    <div class="sanction-card__row sanction-card__row--stacked">
                      <span>Decisión CIG</span>
                      <strong>{{ sanction.decision_cig || 'Sin registro' }}</strong>
                    </div>
                  </article>
                </div>
                <div v-else class="panel-empty panel-empty--soft">Sin sanciones registradas.</div>
              </article>

              <article class="panel">
                <div class="panel__header">
                  <div>
                    <p class="panel-kicker">Reconocimiento anual</p>
                    <h3>Estado del periodo seleccionado</h3>
                  </div>
                </div>

                <div class="recognition-grid">
                  <div
                    v-for="recognition in recognitionItems"
                    :key="recognition.label"
                    class="recognition-item"
                    :class="{ active: recognition.value }"
                  >
                    <span>{{ recognition.label }}</span>
                    <strong>{{ recognition.value ? 'Sí' : 'No' }}</strong>
                  </div>
                </div>
              </article>
            </section>

            <section class="panel">
              <div class="panel__header">
                <div>
                  <p class="panel-kicker">Comentarios</p>
                  <h3>Observaciones del año {{ selectedAnnual.anio }}</h3>
                </div>
              </div>

              <div class="comments-box">
                {{ selectedAnnual.comentarios || 'Sin comentarios registrados para este periodo.' }}
              </div>
            </section>
          </template>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SidebarMenu from '../components/SidebarMenu.vue'
import HistorialAnualCard from '../components/HistorialAnualCard.vue'

const API_BASE = 'http://127.0.0.1:8000/api'

const route = useRoute()
const router = useRouter()

const isLoading = ref(true)
const errorMessage = ref('')
const user = ref(null)
const selectedYear = ref(null)

const volunteer = computed(() => user.value?.voluntario || null)

const annualRecords = computed(() =>
  [...(volunteer.value?.hojas_vida_anuales || [])].sort((left, right) => Number(right.anio) - Number(left.anio))
)

const selectedAnnual = computed(() => {
  if (!selectedYear.value) {
    return annualRecords.value[0] || null
  }

  return annualRecords.value.find((record) => Number(record.anio) === Number(selectedYear.value)) || null
})

const displayName = computed(() => {
  if (volunteer.value) {
    return [volunteer.value.nombres, volunteer.value.apellidos].filter(Boolean).join(' ') || user.value?.name || 'Voluntario'
  }

  return user.value?.name || 'Hoja de vida'
})

const ageLabel = computed(() => {
  const dateValue = volunteer.value?.fecha_nacimiento

  if (!dateValue) {
    return 'Sin registro'
  }

  const birthDate = new Date(`${dateValue}T00:00:00`)

  if (Number.isNaN(birthDate.getTime())) {
    return 'Sin registro'
  }

  const today = new Date()
  let age = today.getFullYear() - birthDate.getFullYear()
  const monthDelta = today.getMonth() - birthDate.getMonth()

  if (monthDelta < 0 || (monthDelta === 0 && today.getDate() < birthDate.getDate())) {
    age -= 1
  }

  return `${age} años`
})

const recognitionItems = computed(() => {
  const recognition = selectedAnnual.value?.reconocimiento || {}

  return [
    { label: 'Servicio extraordinario', value: Boolean(recognition.servicio_extraordinario) },
    { label: 'Abnegación', value: Boolean(recognition.abnegacion) },
    { label: '3ª medalla de honor', value: Boolean(recognition.medalla_honor_3) },
    { label: '2ª medalla de honor', value: Boolean(recognition.medalla_honor_2) },
    { label: '1ª medalla de honor', value: Boolean(recognition.medalla_honor_1) },
    { label: 'Vittorio Cucchini', value: Boolean(recognition.vittorio_cucchini) },
    { label: 'Promesa', value: Boolean(recognition.promesa) },
    { label: 'Juramento', value: Boolean(recognition.juramento) }
  ]
})

watch(
  () => route.query.anio,
  () => syncSelectedYear(),
  { immediate: true }
)

watch(annualRecords, () => syncSelectedYear())

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

    syncSelectedYear()
  } catch (error) {
    errorMessage.value = 'No se pudo cargar la hoja de vida del voluntario.'
  } finally {
    isLoading.value = false
  }
}

function syncSelectedYear() {
  const requestedYear = Number(route.query.anio)
  const years = annualRecords.value.map((record) => Number(record.anio))

  if (requestedYear && years.includes(requestedYear)) {
    selectedYear.value = requestedYear
    return
  }

  selectedYear.value = years[0] || null
}

function yearLink(year) {
  return {
    name: 'HistorialView',
    params: { id: route.params.id },
    query: { anio: year }
  }
}

function openPdfExport() {
  if (!selectedAnnual.value) {
    return
  }

  router.push({
    name: 'HistorialPdfView',
    params: { id: route.params.id },
    query: { anio: selectedAnnual.value.anio }
  })
}

function formatDate(value) {
  if (!value) {
    return 'Sin registro'
  }

  const parsed = new Date(`${value}T00:00:00`)

  if (Number.isNaN(parsed.getTime())) {
    return value
  }

  return parsed.toLocaleDateString('es-CL')
}

function yesNo(value) {
  return value ? 'Sí' : 'No'
}

function attendanceLabel(record) {
  if (!record) {
    return 'Sin registro'
  }

  const hours = record.asistencia_anual_horas
  const percentage = record.asistencia_anual_porcentaje

  if ((hours === null || hours === undefined || hours === '') && (percentage === null || percentage === undefined || percentage === '')) {
    return 'Sin registro'
  }

  if (hours !== null && hours !== undefined && hours !== '' && percentage !== null && percentage !== undefined && percentage !== '') {
    return `${Number(hours)} horas · ${Number(percentage)}%`
  }

  if (percentage !== null && percentage !== undefined && percentage !== '') {
    return `${Number(percentage)}%`
  }

  return `${Number(hours)} horas`
}
</script>

<style scoped>
.content-wrapper {
  flex: 1;
  background: #f5f7fa;
  min-height: 100vh;
}

.content-header {
  padding: 1rem 1.5rem;
  background: #fff;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 1.5rem;
}

.content {
  padding: 0 1.5rem 1.5rem;
}

.header-main {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
}

.header-kicker,
.panel-kicker,
.eyebrow {
  margin: 0 0 0.35rem;
  color: #d3272d;
  font-size: 0.8rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.header-main h1,
.panel__header h3,
.hero-card__identity h2 {
  margin: 0;
  color: #0f2f5f;
}

.header-subtitle {
  margin: 0.4rem 0 0;
  color: #607086;
}

.header-action {
  white-space: nowrap;
}

.hero-card,
.panel {
  background: #fff;
  border: 1px solid #e3e9f1;
  border-radius: 24px;
  box-shadow: 0 14px 32px rgba(15, 47, 95, 0.08);
}

.hero-card {
  display: grid;
  grid-template-columns: 180px 1fr;
  gap: 1.5rem;
  padding: 1.4rem;
  margin-bottom: 1.5rem;
}

.hero-card__photo {
  height: 220px;
  border-radius: 24px;
  background: linear-gradient(180deg, #f0f4f9 0%, #e4ebf4 100%);
  border: 1px dashed #bfd0e3;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #678;
  font-weight: 700;
}

.hero-card__photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-card__body {
  display: grid;
  gap: 1.1rem;
}

.hero-card__identity {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
}

.status-chip {
  padding: 0.5rem 0.85rem;
  border-radius: 999px;
  background: #eff5fb;
  color: #163a69;
  font-weight: 700;
}

.hero-card__meta,
.personal-grid,
.recognition-grid,
.detail-grid,
.annual-grid {
  display: grid;
}

.hero-card__meta {
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.9rem;
}

.hero-card__meta div,
.data-chip,
.recognition-item {
  padding: 0.95rem 1rem;
  border-radius: 18px;
  background: #f7f9fc;
  border: 1px solid #e7edf5;
}

.hero-card__meta span,
.data-chip span,
.recognition-item span,
.info-row span,
.sanction-card__row span {
  display: block;
  color: #6a7a8f;
  font-size: 0.82rem;
  margin-bottom: 0.3rem;
}

.hero-card__meta strong,
.data-chip strong,
.recognition-item strong,
.info-row strong,
.sanction-card__row strong {
  color: #183a69;
}

.panel {
  padding: 1.25rem;
  margin-bottom: 1.5rem;
}

.panel__header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1rem;
}

.panel-empty {
  display: grid;
  place-items: center;
  min-height: 220px;
  background: #fff;
  border: 1px solid #e3e9f1;
  border-radius: 24px;
  color: #617389;
  text-align: center;
  padding: 1.5rem;
}

.panel-empty--error {
  color: #a61f2b;
  border-color: #efc7cc;
  background: #fff8f8;
}

.panel-empty--soft {
  min-height: 120px;
  background: #f8fafc;
  box-shadow: none;
}

.annual-grid {
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.detail-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.5rem;
}

.info-table {
  display: grid;
  gap: 0.85rem;
}

.info-row,
.sanction-card__row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
  padding: 0.9rem 1rem;
  border-radius: 16px;
  background: #f8fafc;
  border: 1px solid #e7edf5;
}

.info-row--stacked,
.sanction-card__row--stacked {
  display: grid;
}

.personal-grid {
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.9rem;
}

.data-chip--wide {
  grid-column: span 2;
}

.table-responsive {
  overflow-x: auto;
}

.sheet-table {
  width: 100%;
  border-collapse: collapse;
}

.sheet-table th,
.sheet-table td {
  padding: 0.8rem 0.85rem;
  border-bottom: 1px solid #e8edf4;
  text-align: left;
  vertical-align: top;
}

.sheet-table th {
  color: #183a69;
  font-size: 0.82rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  background: #f7f9fc;
}

.sanction-list {
  display: grid;
  gap: 0.9rem;
}

.sanction-card {
  border: 1px solid #e8edf4;
  border-radius: 18px;
  padding: 0.9rem;
  background: #fbfcfe;
}

.recognition-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.9rem;
}

.recognition-item.active {
  border-color: #ffcbcf;
  background: #fff5f6;
}

.comments-box {
  min-height: 120px;
  padding: 1rem 1.05rem;
  border-radius: 18px;
  background: #f8fafc;
  border: 1px solid #e7edf5;
  color: #183a69;
  white-space: pre-line;
  line-height: 1.5;
}

@media (max-width: 1199px) {
  .detail-grid,
  .personal-grid,
  .hero-card__meta {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 767.98px) {
  .d-flex {
    display: block !important;
  }

  .header-main,
  .hero-card,
  .hero-card__identity,
  .detail-grid,
  .personal-grid,
  .hero-card__meta,
  .recognition-grid {
    grid-template-columns: 1fr;
    display: grid;
  }

  .header-action {
    width: 100%;
  }

  .hero-card__photo {
    height: 260px;
  }

  .data-chip--wide {
    grid-column: auto;
  }
}
</style>
