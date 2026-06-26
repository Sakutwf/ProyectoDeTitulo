<template>
  <div class="d-flex">
    <SidebarMenu compact collapsible />

    <div class="content-wrapper">
      <div class="content">
        <div class="page-shell">
          <div v-if="isLoading" class="panel-empty">
            Cargando hoja de vida...
          </div>

          <div v-else-if="errorMessage" class="panel-empty panel-empty--error">
            {{ errorMessage }}
          </div>

          <template v-else-if="volunteer">
            <section class="hero-layout">
              <div class="hero-rail">
                <button type="button" class="back-button" @click="goBack" aria-label="Volver">
                  <i class="fa-solid fa-arrow-left"></i>
                </button>

                <div class="year-card">
                  {{ selectedAnnual?.anio || '----' }}
                </div>

                <div class="photo-card">
                  <div class="photo-frame">
                    <img
                      v-if="volunteer.foto_perfil_url"
                      :src="volunteer.foto_perfil_url"
                      :alt="`Foto de ${displayName}`"
                    >
                    <span v-else>Sin foto</span>
                  </div>

                  <button
                    v-if="canUpdatePhoto"
                  type="button"
                  class="photo-action"
                  :disabled="isUploadingPhoto"
                  @click="triggerPhotoInput"
                >
                  {{ isUploadingPhoto ? 'Actualizando...' : photoActionLabel }}
                </button>

                  <input
                    ref="photoInput"
                    type="file"
                    class="d-none"
                    accept=".jpg,.jpeg,.png,.webp"
                    @change="onProfilePhotoSelected"
                  >
                </div>
              </div>

              <article class="hero-panel">
                <div class="hero-top">
                  <div class="hero-copy">
                    <div class="hero-meta">
                      <p class="hero-kicker">
                        N. registro {{ volunteer.registro_filial || 'Sin registro' }}
                      </p>
                      <p class="hero-kicker">
                        Filial {{ volunteer.filial?.nombre || 'Sin filial' }}
                      </p>
                    </div>
                    <h2>{{ displayName }}</h2>

                    <div class="hero-stats">
                      <article
                        v-for="stat in heroSummaryItems"
                        :key="stat.label"
                        class="hero-stat"
                      >
                        <span>{{ stat.label }}</span>
                        <strong>{{ stat.value }}</strong>
                      </article>
                    </div>
                  </div>

                  <div class="hero-brand">
                    <img :src="logoSrc" alt="Cruz Roja Chilena">
                  </div>
                </div>
              </article>

              <section class="action-panel action-panel--hero">
                <button
                  type="button"
                  class="action-button action-button--primary"
                  :disabled="!selectedAnnual"
                  @click="openPdfExport"
                >
                  Exportar PDF
                </button>

                <button
                  v-if="canManageHojaVida"
                  type="button"
                  class="action-button"
                  @click="openCreateEditor"
                >
                  Agregar hoja anual
                </button>

                <button
                  v-if="canManageHojaVida"
                  type="button"
                  class="action-button action-button--ghost"
                  :disabled="!selectedAnnual"
                  @click="openEditEditor"
                >
                  Editar periodo
                </button>
              </section>

              <div class="toolbar-row toolbar-row--hero-outside">
                <label class="search-shell">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  <input
                    v-model.trim="searchTerm"
                    type="text"
                    placeholder="Buscar datos personales, cursos, sanciones o comentarios del año seleccionado"
                  >
                </label>
              </div>
            </section>

            <HistorialAnualEditor
              v-if="isEditorOpen"
              :volunteer-id="volunteer.id"
              :record="editorRecord"
              :current-user-id="currentUser?.id || null"
              :initial-section="editorSection"
              @saved="handleRecordSaved"
              @cancel="closeEditor"
            />

            <section class="content-grid">
              <div class="main-column">
                <div v-if="!selectedAnnual" class="panel-empty panel-empty--soft">
                  Este voluntario todavia no tiene una hoja de vida anual para mostrar.
                </div>

                <div v-else-if="!hasSearchResults" class="panel-empty panel-empty--soft">
                  No encontramos coincidencias en la hoja de vida del periodo {{ selectedAnnual?.anio || 'seleccionado' }}.
                </div>

                <template v-else>

                  <section v-if="showCommissionPanel" class="panel">
                    <div class="panel-header">
                      <div>
                        <p class="panel-kicker">Comision de servicio</p>
                        <h3>Registro del periodo</h3>
                      </div>
                      <span class="counter-chip">
                        {{ commissionInfo.inService ? 'En comision' : 'Sin comision' }}
                      </span>
                    </div>

                    <div v-if="commissionInfo.hasAnyData" class="commission-card">
                      <div class="commission-top">
                        <span class="mini-pill" :class="{ 'mini-pill--active': commissionInfo.inService }">
                          {{ commissionInfo.inService ? 'Si estuvo en comision' : 'No estuvo en comision' }}
                        </span>
                        <span class="mini-pill">{{ commissionInfo.dateRange }}</span>
                      </div>

                      <div class="commission-grid">
                        <article class="commission-item">
                          <span>Lugar</span>
                          <strong>{{ commissionInfo.place }}</strong>
                        </article>
                        <article class="commission-item commission-item--wide">
                          <span>Actividad</span>
                          <strong>{{ commissionInfo.activity }}</strong>
                        </article>
                      </div>
                    </div>

                    <div v-else class="empty-inline">
                      No hay antecedentes de comision de servicio para este periodo.
                    </div>
                  </section>

                  <section v-if="filteredPersonalFacts.length" class="panel">
                    <div class="panel-header">
                      <div>
                        <p class="panel-kicker">Datos personales</p>
                        <h3>Informacion del voluntario</h3>
                      </div>
                    </div>

                    <div class="fact-grid">
                      <article
                        v-for="fact in filteredPersonalFacts"
                        :key="fact.label"
                        class="fact-tile"
                        :class="{ 'fact-tile--wide': fact.wide }"
                      >
                        <span>{{ fact.label }}</span>
                        <strong>{{ fact.value }}</strong>
                      </article>
                    </div>
                  </section>

                  <div class="split-grid">
                    <section class="panel">
                      <div class="panel-header">
                        <div>
                          <p class="panel-kicker">Titulos aprobados</p>
                          <h3>{{ filteredTitles.length }} registro(s)</h3>
                        </div>
                        <button
                          v-if="canUploadAcademicRecords"
                          type="button"
                          class="section-upload-button"
                          :disabled="!selectedAnnual"
                          @click="openSectionEditor('titles')"
                        >
                          <i class="fa-solid fa-file-arrow-up"></i>
                          <span>Subir titulo</span>
                        </button>
                      </div>

                      <div v-if="filteredTitles.length" class="table-responsive">
                        <table class="sheet-table">
                          <thead>
                            <tr>
                              <th>Titulo</th>
                              <th>Entregado por</th>
                              <th>Codigo</th>
                              <th>Respaldo</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="title in filteredTitles" :key="title.id || `${title.titulo}-${title.codigo_titulo}`">
                              <td>{{ title.titulo || 'Sin registro' }}</td>
                              <td>{{ title.entregado_por || 'Sin registro' }}</td>
                              <td>{{ title.codigo_titulo || 'Sin registro' }}</td>
                              <td>
                                <a v-if="title.archivo_url" :href="title.archivo_url" target="_blank" rel="noopener" class="sheet-link">
                                  {{ title.archivo_nombre || 'Ver respaldo' }}
                                </a>
                                <span v-else>Sin respaldo</span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <div v-else class="empty-inline">
                        No hay titulos aprobados registrados para este periodo.
                      </div>
                    </section>

                    <section class="panel">
                      <div class="panel-header">
                        <div>
                          <p class="panel-kicker">Cursos aprobados</p>
                          <h3>{{ filteredCourses.length }} registro(s)</h3>
                        </div>
                        <button
                          v-if="canUploadAcademicRecords"
                          type="button"
                          class="section-upload-button"
                          :disabled="!selectedAnnual"
                          @click="openSectionEditor('courses')"
                        >
                          <i class="fa-solid fa-file-arrow-up"></i>
                          <span>Subir curso</span>
                        </button>
                      </div>

                      <div v-if="filteredCourses.length" class="table-responsive">
                        <table class="sheet-table">
                          <thead>
                            <tr>
                              <th>Curso</th>
                              <th>Entregado por</th>
                              <th>Codigo</th>
                              <th>Respaldo</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="course in filteredCourses" :key="course.id || `${course.nombre_curso}-${course.codigo_curso}`">
                              <td>{{ course.nombre_curso || 'Sin registro' }}</td>
                              <td>{{ course.entregado_por || 'Sin registro' }}</td>
                              <td>{{ course.codigo_curso || 'Sin registro' }}</td>
                              <td>
                                <a v-if="course.archivo_url" :href="course.archivo_url" target="_blank" rel="noopener" class="sheet-link">
                                  {{ course.archivo_nombre || 'Ver respaldo' }}
                                </a>
                                <span v-else>Sin respaldo</span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <div v-else class="empty-inline">
                        No hay cursos aprobados registrados para este periodo.
                      </div>
                    </section>
                  </div>

                  <div class="split-grid">
                    <section class="panel">
                      <div class="panel-header">
                        <div>
                          <p class="panel-kicker">Sanciones</p>
                          <h3>{{ filteredSanctions.length }} registro(s)</h3>
                        </div>
                      </div>

                      <div v-if="filteredSanctions.length" class="stack-list">
                        <article
                          v-for="sanction in filteredSanctions"
                          :key="sanction.id || `${sanction.tipo_sancion}-${sanction.fecha}`"
                          class="stack-card"
                        >
                          <div class="stack-card__row">
                            <span>Tipo</span>
                            <strong>{{ sanction.tipo_sancion || 'Sin registro' }}</strong>
                          </div>
                          <div class="stack-card__row">
                            <span>Fecha</span>
                            <strong>{{ formatDate(sanction.fecha) }}</strong>
                          </div>
                          <div class="stack-card__row stack-card__row--wide">
                            <span>Resumen</span>
                            <strong>{{ sanction.resumen_sancion || 'Sin registro' }}</strong>
                          </div>
                          <div class="stack-card__row stack-card__row--wide">
                            <span>Apelacion</span>
                            <strong>{{ sanction.apelacion || 'Sin registro' }}</strong>
                          </div>
                          <div class="stack-card__row">
                            <span>Fecha apelacion</span>
                            <strong>{{ formatDate(sanction.fecha_apelacion) }}</strong>
                          </div>
                          <div class="stack-card__row stack-card__row--wide">
                            <span>Decision CIG</span>
                            <strong>{{ sanction.decision_cig || 'Sin registro' }}</strong>
                          </div>
                        </article>
                      </div>

                      <div v-else class="empty-inline">
                        No hay sanciones registradas para este periodo.
                      </div>
                    </section>
                  </div>

                  <section v-if="showCommentsPanel" class="panel">
                    <div class="panel-header">
                      <div>
                        <p class="panel-kicker">Comentarios</p>
                        <h3>Observaciones del periodo {{ selectedAnnual?.anio || '' }}</h3>
                      </div>
                    </div>

                    <div class="comments-box">
                      {{ selectedAnnual?.comentarios || 'Sin comentarios registrados para este periodo.' }}
                    </div>
                  </section>
                </template>
              </div>

              <aside class="sidebar-column">
                <section class="panel history-panel">
                  <div class="panel-header panel-header--history">
                    <div>
                      <p class="panel-kicker">Historial</p>
                      <h3>Hojas anuales</h3>
                    </div>

                    <div v-if="annualRecords.length" class="history-controls">
                      <span class="history-counter">
                        {{ annualWindowStart + 1 }}-{{ annualWindowEnd }} de {{ annualRecords.length }}
                      </span>
                      <div class="history-nav">
                        <button
                          type="button"
                          class="history-nav__button"
                          :disabled="!canGoPrevAnnuals"
                          @click="goToPreviousAnnualPage"
                          aria-label="Ver hojas anuales anteriores"
                        >
                          <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button
                          type="button"
                          class="history-nav__button"
                          :disabled="!canGoNextAnnuals"
                          @click="goToNextAnnualPage"
                          aria-label="Ver hojas anuales siguientes"
                        >
                          <i class="fa-solid fa-chevron-right"></i>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div v-if="annualRecords.length" class="annual-list">
                    <HistorialAnualCard
                      v-for="record in visibleAnnualRecords"
                      :key="record.id"
                      :historial="record"
                      :active="record.anio === selectedYear"
                      :to="yearLink(record.anio)"
                    />
                  </div>

                  <div v-else class="empty-inline">
                    Este voluntario todavia no tiene hoja de vida anual registrada.
                  </div>
                </section>

                <section v-if="filteredRecognitionItems.length" class="panel recognition-panel">
                  <div class="panel-header">
                    <div>
                      <p class="panel-kicker">Reconocimiento anual</p>
                      <h3>Estado del periodo</h3>
                    </div>
                  </div>

                  <div class="recognition-grid recognition-grid--sidebar">
                    <article
                      v-for="recognition in filteredRecognitionItems"
                      :key="recognition.label"
                      class="recognition-item"
                      :class="{ active: recognition.value }"
                    >
                      <span>{{ recognition.label }}</span>
                      <strong>{{ recognition.value ? 'Si' : 'No' }}</strong>
                    </article>
                  </div>
                </section>
              </aside>
            </section>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from 'vuex'
import logoSrc from '../assets/LogoVertical.svg'
import SidebarMenu from '../components/SidebarMenu.vue'
import HistorialAnualCard from '../components/HistorialAnualCard.vue'
import HistorialAnualEditor from '../components/HistorialAnualEditor.vue'
import { show_alerta } from '../funciones'

const API_BASE = 'http://127.0.0.1:8000/api'

const route = useRoute()
const router = useRouter()
const store = useStore()

const isLoading = ref(true)
const errorMessage = ref('')
const user = ref(null)
const selectedYear = ref(null)
const isEditorOpen = ref(false)
const editorRecord = ref(null)
const editorSection = ref(null)
const searchTerm = ref('')
const isUploadingPhoto = ref(false)
const photoInput = ref(null)
const annualWindowStart = ref(0)

const currentUser = computed(() => store.getters.authUser)
const canManageHojaVida = computed(() => store.getters.isAdministratorExperience && store.getters.canManagePlatform)
const canUpdatePhoto = computed(() => Boolean(user.value?.id) && (canManageHojaVida.value || currentUser.value?.id === user.value?.id))
const canUploadAcademicRecords = computed(() => Boolean(user.value?.id) && (canManageHojaVida.value || currentUser.value?.id === user.value?.id))
const photoActionLabel = computed(() => volunteer.value?.foto_perfil_url ? 'Cambiar foto' : 'Subir foto')

const volunteer = computed(() => user.value?.voluntario || null)

const annualWindowSize = 3

const annualRecords = computed(() =>
  [...(volunteer.value?.hoja_vida_anual || [])].sort((left, right) => Number(right.anio) - Number(left.anio))
)

const visibleAnnualRecords = computed(() =>
  annualRecords.value.slice(annualWindowStart.value, annualWindowStart.value + annualWindowSize)
)

const annualWindowEnd = computed(() =>
  Math.min(annualWindowStart.value + annualWindowSize, annualRecords.value.length)
)

const canGoPrevAnnuals = computed(() => annualWindowStart.value > 0)
const canGoNextAnnuals = computed(() => annualWindowEnd.value < annualRecords.value.length)

const selectedAnnual = computed(() => {
  if (!selectedYear.value) {
    return annualRecords.value[0] || null
  }

  return annualRecords.value.find((record) => Number(record.anio) === Number(selectedYear.value)) || null
})

const normalizedSearch = computed(() => normalizeSearch(searchTerm.value))

const displayName = computed(() => {
  if (volunteer.value) {
    return [volunteer.value.nombres, volunteer.value.apellidos].filter(Boolean).join(' ') || user.value?.username || 'Voluntario'
  }

  return user.value?.username || 'Hoja de vida'
})

const cargoLabel = computed(() =>
  selectedAnnual.value?.cargo ||
  volunteer.value?.cargo ||
  'Sin Cargo registrado'
)

const periodCargoInfo = computed(() => {
  const annual = selectedAnnual.value

  if (!annual) {
    return {
      hasCargo: false,
      label: 'Sin cargo registrado para este periodo'
    }
  }

  return {
    hasCargo: Boolean(annual.cargo),
    label: annual.cargo || 'Sin cargo registrado para este periodo'
  }
})

const attendanceLabel = computed(() => {
  const percentage = selectedAnnual.value?.asistencia_anual_porcentaje

  if (percentage === null || percentage === undefined || percentage === '') {
    return 'Sin registro'
  }

  return `${Number(percentage)}%`
})

const heroSummaryItems = computed(() => {
  return [
    { label: 'Cargo', value: cargoLabel.value },
    { label: 'Asistencia en el periodo', value: attendanceLabel.value },
  ]
})

const personalFacts = computed(() => {
  if (!volunteer.value) {
    return []
  }

  return [
    { label: 'Nombres', value: volunteer.value.nombres || 'Sin registro' },
    { label: 'Apellidos', value: volunteer.value.apellidos || 'Sin registro' },
    { label: 'RUT', value: volunteer.value.rut || 'Sin registro' },
    { label: 'Celular', value: volunteer.value.celular || 'Sin registro' },
    { label: 'Nacionalidad', value: volunteer.value.nacionalidad || 'Sin registro' },
    { label: 'Edad', value: ageLabel.value },
    { label: 'Fecha de nacimiento', value: formatDate(volunteer.value.fecha_nacimiento) },
    { label: 'Alergias', value: volunteer.value.alergias || 'Sin registro' },
    { label: 'Fecha incorporacion', value: formatDate(volunteer.value.fecha_incorporacion) },
    { label: 'Enfermedades', value: volunteer.value.enfermedades || 'Sin registro' },
    { label: 'Correo electronico', value: volunteer.value.correo_electronico || 'Sin correo', wide: true },
    { label: 'Domicilio', value: volunteer.value.domicilio || 'Sin registro', wide: true },
    { label: 'Contacto de emergencia', value: volunteer.value.contacto_emergencia_nombre || 'Sin registro', wide: true },
    { label: 'Numero de contacto', value: volunteer.value.contacto_emergencia_numero || 'Sin registro' },
  ]
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

  return `${age} anos`
})

const commissionInfo = computed(() => {
  const annual = selectedAnnual.value

  if (!annual) {
    return {
      inService: false,
      hasAnyData: false,
      dateRange: 'Sin fechas registradas',
      place: 'Sin registro',
      activity: 'Sin registro',
    }
  }

  const start = formatDate(annual.comision_fecha_inicio)
  const end = formatDate(annual.comision_fecha_termino)
  const place = annual.comision_lugar || 'Sin registro'
  const activity = annual.comision_actividad || 'Sin registro'
  const hasAnyData = Boolean(
    annual.estuvo_comision_servicio ||
    annual.comision_fecha_inicio ||
    annual.comision_fecha_termino ||
    annual.comision_lugar ||
    annual.comision_actividad
  )

  return {
    inService: Boolean(annual.estuvo_comision_servicio),
    hasAnyData,
    dateRange: [start, end].filter((value) => value !== 'Sin registro').join(' - ') || 'Sin fechas registradas',
    place,
    activity,
  }
})

const recognitionItems = computed(() => {
  const recognition = selectedAnnual.value?.reconocimiento || {}

  return [
    { label: 'Servicio extraordinario', value: Boolean(recognition.servicio_extraordinario) },
    { label: 'Abnegacion', value: Boolean(recognition.abnegacion) },
    { label: '3a medalla de honor', value: Boolean(recognition.medalla_honor_3) },
    { label: '2a medalla de honor', value: Boolean(recognition.medalla_honor_2) },
    { label: '1a medalla de honor', value: Boolean(recognition.medalla_honor_1) },
    { label: 'Vittorio Cucchini', value: Boolean(recognition.vittorio_cucchini) },
    { label: 'Promesa', value: Boolean(recognition.promesa) },
    { label: 'Juramento', value: Boolean(recognition.juramento) },
  ]
})

const filteredPersonalFacts = computed(() => personalFacts.value.filter((item) => matchesSearch(`${item.label} ${item.value}`)))
const filteredTitles = computed(() => (selectedAnnual.value?.titulos || []).filter((item) => matchesSearch(`${item.titulo} ${item.entregado_por} ${item.codigo_titulo} ${item.archivo_nombre || ''}`)))
const filteredCourses = computed(() => (selectedAnnual.value?.cursos || []).filter((item) => matchesSearch(`${item.nombre_curso} ${item.entregado_por} ${item.codigo_curso} ${item.archivo_nombre || ''}`)))
const filteredSanctions = computed(() => (selectedAnnual.value?.sanciones || []).filter((item) => matchesSearch(`${item.tipo_sancion} ${item.fecha} ${item.resumen_sancion} ${item.apelacion} ${item.fecha_apelacion} ${item.decision_cig}`)))
const filteredRecognitionItems = computed(() => recognitionItems.value.filter((item) => matchesSearch(`${item.label} ${item.value ? 'si' : 'no'}`)))

const showCommissionPanel = computed(() =>
  Boolean(selectedAnnual.value) &&
  (!normalizedSearch.value || matchesSearch(`comision ${commissionInfo.value.place} ${commissionInfo.value.activity} ${commissionInfo.value.dateRange}`))
)
const showCommentsPanel = computed(() =>
  Boolean(selectedAnnual.value) &&
  (!normalizedSearch.value || matchesSearch(selectedAnnual.value?.comentarios || ''))
)

const hasSearchResults = computed(() => {
  if (!selectedAnnual.value) {
    return false
  }

  if (!normalizedSearch.value) {
    return true
  }

  return Boolean(
    filteredPersonalFacts.value.length ||
    filteredTitles.value.length ||
    filteredCourses.value.length ||
    filteredSanctions.value.length ||
    filteredRecognitionItems.value.length ||
    showCommissionPanel.value ||
    showCommentsPanel.value
  )
})

watch(
  () => route.query.anio,
  () => syncSelectedYear(),
  { immediate: true }
)

watch(annualRecords, () => {
  syncSelectedYear()
  syncAnnualWindow()
})

watch(selectedYear, () => syncAnnualWindow())

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

function syncAnnualWindow() {
  if (!annualRecords.value.length) {
    annualWindowStart.value = 0
    return
  }

  const maxStart = Math.max(annualRecords.value.length - annualWindowSize, 0)
  const selectedIndex = annualRecords.value.findIndex((record) => Number(record.anio) === Number(selectedYear.value))
  const targetIndex = selectedIndex >= 0 ? selectedIndex : 0

  if (targetIndex < annualWindowStart.value) {
    annualWindowStart.value = targetIndex
  } else if (targetIndex >= annualWindowStart.value + annualWindowSize) {
    annualWindowStart.value = targetIndex - annualWindowSize + 1
  }

  annualWindowStart.value = Math.min(Math.max(annualWindowStart.value, 0), maxStart)
}

function goToPreviousAnnualPage() {
  annualWindowStart.value = Math.max(annualWindowStart.value - annualWindowSize, 0)
}

function goToNextAnnualPage() {
  const maxStart = Math.max(annualRecords.value.length - annualWindowSize, 0)
  annualWindowStart.value = Math.min(annualWindowStart.value + annualWindowSize, maxStart)
}

function yearLink(year) {
  return {
    name: 'HistorialView',
    params: { id: route.params.id },
    query: { anio: year }
  }
}

function goBack() {
  router.push({ name: canManageHojaVida.value ? 'voluntarios' : 'inicio' })
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

function openCreateEditor() {
  editorRecord.value = null
  editorSection.value = null
  isEditorOpen.value = true
}

function openEditEditor() {
  if (!selectedAnnual.value) {
    return
  }

  editorRecord.value = selectedAnnual.value
  editorSection.value = null
  isEditorOpen.value = true
}

function openSectionEditor(section) {
  if (!selectedAnnual.value) {
    return
  }

  editorRecord.value = selectedAnnual.value
  editorSection.value = section
  isEditorOpen.value = true
}

function closeEditor() {
  isEditorOpen.value = false
  editorRecord.value = null
  editorSection.value = null
}

async function handleRecordSaved(record) {
  closeEditor()
  await fetchUser()
  selectedYear.value = Number(record?.anio || selectedYear.value)
  router.replace({
    name: 'HistorialView',
    params: { id: route.params.id },
    query: { anio: selectedYear.value }
  })
}

function triggerPhotoInput() {
  if (!canUpdatePhoto.value || isUploadingPhoto.value) {
    return
  }

  photoInput.value?.click()
}

async function onProfilePhotoSelected(event) {
  const file = event.target.files?.[0]

  if (!file || !user.value?.id) {
    return
  }

  isUploadingPhoto.value = true

  try {
    const formData = new FormData()
    formData.append('foto_perfil', file)

    const response = await axios.post(`${API_BASE}/user/${user.value.id}/foto-perfil`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    user.value = response.data
    show_alerta('Foto de perfil actualizada correctamente.', 'success')
  } catch (error) {
    show_alerta('No se pudo actualizar la foto de perfil.', 'error')
  } finally {
    isUploadingPhoto.value = false
    event.target.value = ''
  }
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

function formatAttendance(record) {
  if (!record) {
    return 'Sin registro'
  }

  const hours = record.asistencia_anual_horas
  const percentage = record.asistencia_anual_porcentaje

  if ((hours === null || hours === undefined || hours === '') && (percentage === null || percentage === undefined || percentage === '')) {
    return 'Sin registro'
  }

  if (hours !== null && hours !== undefined && hours !== '' && percentage !== null && percentage !== undefined && percentage !== '') {
    return `${Number(percentage)}% · ${Number(hours)} h`
  }

  if (percentage !== null && percentage !== undefined && percentage !== '') {
    return `${Number(percentage)}%`
  }

  return `${Number(hours)} h`
}

function normalizeSearch(value) {
  return String(value || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .trim()
}

function matchesSearch(value) {
  if (!normalizedSearch.value) {
    return true
  }

  return normalizeSearch(value).includes(normalizedSearch.value)
}
</script>

<style scoped>
.content-wrapper {
  flex: 1;
  background: #f4f6f8;
  min-height: 100vh;
}

.content {
  padding: clamp(1.25rem, 1rem + 1vw, 2rem);
}

.page-shell {
  width: min(100%, 1840px);
  margin: 0 auto;
}

.hero-kicker,
.panel-kicker {
  margin: 0 0 0.3rem;
  color: #8a96a8;
  font-size: 0.92rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.hero-panel h2,
.panel-header h3 {
  margin: 0;
  color: #163a69;
}

.panel-header h3 {
  font-size: clamp(1.35rem, 1.1rem + 0.55vw, 1.72rem);
  line-height: 1.2;
}

.hero-layout,
.content-grid,
.hero-stats,
.fact-grid,
.split-grid,
.recognition-grid,
.commission-grid {
  display: grid;
}

.hero-layout {
  grid-template-columns: 170px minmax(0, 1fr) 320px;
  grid-template-areas:
    "rail panel actions"
    "rail toolbar toolbar";
  gap: 1.2rem;
  margin-bottom: 1rem;
  align-items: stretch;
}

.hero-rail {
  grid-area: rail;
  display: grid;
  grid-template-columns: 48px minmax(0, 1fr);
  grid-template-areas:
    "back year"
    "photo photo";
  gap: 0.7rem;
  align-items: start;
}

.back-button,
.year-card,
.photo-card,
.hero-panel,
.panel,
.action-panel,
.search-shell,
.status-pill,
.panel-empty {
  border: 1px solid #e4e8ee;
  background: #fff;
}

.back-button,
.year-card {
  min-height: 44px;
  border-radius: 14px;
}

.back-button {
  grid-area: back;
  color: #fff;
  background: #173b70;
  border-color: #173b70;
  font-size: 0.96rem;
}

.year-card {
  grid-area: year;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ff3743;
  border-color: #ff3743;
  color: #fff;
  font-size: 1.2rem;
  font-weight: 800;
}

.photo-card,
.hero-panel,
.panel,
.action-panel,
.panel-empty {
  border-radius: 18px;
  box-shadow: 0 16px 36px rgba(15, 47, 95, 0.08);
}

.photo-card {
  grid-area: photo;
  padding: 0.4rem;
  display: grid;
  gap: 0.4rem;
  align-content: start;
}

.photo-frame {
  width: 100%;
  aspect-ratio: 1 / 1;
  border-radius: 14px;
  background: #f3f6fb;
  border: 1px dashed #c8d3e1;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #71829a;
  font-weight: 600;
}

.photo-frame img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-action {
  border: none;
  border-radius: 999px;
  min-height: 38px;
  background: #173b70;
  color: #fff;
  font-size: 0.9rem;
  font-weight: 700;
}

.photo-action:disabled {
  opacity: 0.65;
}

.hero-panel {
  grid-area: panel;
  padding: 1.2rem 1.35rem;
  min-height: 216px;
}

.hero-panel h2 {
  font-size: clamp(2.55rem, 2rem + 1vw, 3.35rem);
  line-height: 1.05;
  font-weight: 800;
}

.hero-top,
.toolbar-row,
.panel-header,
.commission-top,
.stack-card__row {
  display: flex;
  justify-content: space-between;
  gap: 0.8rem;
  align-items: center;
  flex-wrap: wrap;
}

.hero-top {
  align-items: center;
  justify-content: flex-start;
  flex-wrap: nowrap;
  gap: 1.2rem;
}

.hero-copy {
  min-width: 0;
  flex: 1;
}

.hero-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1.2rem;
  margin-bottom: 0.4rem;
}

.hero-brand {
  width: 112px;
  min-width: 112px;
  height: 90px;
  border-radius: 16px;
  border: 1px solid #e4e8ee;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
}

.hero-brand img {
  width: 72px;
  height: auto;
}

.hero-stats {
  grid-template-columns: repeat(2, minmax(0, 260px));
  gap: 1rem;
  margin-top: 0.9rem;
}

.fact-tile,
.recognition-item,
.commission-item,
.stack-card,
.counter-chip,
.mini-pill,
.status-pill {
  border-radius: 14px;
}

.hero-stat span,
.fact-tile span,
.recognition-item span,
.commission-item span,
.stack-card__row span {
  display: block;
  font-size: 0.83rem;
  color: #708198;
  margin-bottom: 0.28rem;
  text-transform: uppercase;
}

.hero-stat span {
  margin-bottom: 0.2rem;
  color: #173b70;
  font-size: 1.05rem;
  line-height: 1.2;
  text-transform: none;
}

.hero-stat strong,
.fact-tile strong,
.recognition-item strong,
.commission-item strong,
.stack-card__row strong {
  display: block;
  font-size: 1.22rem;
  line-height: 1.35;
}

.hero-stat strong {
  color: #0f2f5f;
  font-size: 1.22rem;
  line-height: 1.3;
}

.toolbar-row {
  margin-bottom: 1rem;
}

.toolbar-row--hero-outside {
  grid-area: toolbar;
  margin-bottom: 0;
}

.action-panel--hero {
  grid-area: actions;
  align-self: stretch;
  padding: 0.8rem;
  min-height: 216px;
  align-content: start;
}

.search-shell {
  width: 100%;
  min-height: 60px;
  border-radius: 999px;
  padding: 0 1.2rem;
  display: flex;
  align-items: center;
  gap: 0.7rem;
  box-shadow: 0 10px 26px rgba(15, 47, 95, 0.06);
}

.search-shell i {
  color: #8a96a8;
}

.search-shell input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  color: #173b70;
  font-size: 1.08rem;
}

.status-pill,
.counter-chip,
.mini-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.6rem 0.95rem;
  font-weight: 700;
  font-size: 0.96rem;
}

.status-pill--success {
  background: #ebf8ee;
  border-color: #d6eddc;
  color: #1f7a3f;
}

.status-pill--warning {
  background: #fff4db;
  border-color: #f2dfad;
  color: #936d00;
}

.content-grid {
  grid-template-columns: minmax(0, 1.22fr) minmax(380px, 0.9fr);
  gap: 1.2rem;
  align-items: start;
}

.main-column,
.sidebar-column {
  display: grid;
  gap: 1.2rem;
}

.history-panel {
  min-width: 0;
}

.panel-header--history {
  align-items: flex-start;
}

.history-controls {
  display: grid;
  gap: 0.65rem;
  justify-items: end;
}

.history-counter {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
  padding: 0.45rem 0.8rem;
  border-radius: 999px;
  background: #eef4fb;
  color: #173b70;
  font-size: 0.95rem;
  font-weight: 700;
}

.history-nav {
  display: flex;
  gap: 0.55rem;
}

.history-nav__button {
  width: 42px;
  height: 42px;
  border: 1px solid #d6dfeb;
  border-radius: 999px;
  background: #fff;
  color: #173b70;
}

.history-nav__button:disabled {
  opacity: 0.45;
}

.recognition-panel {
  min-width: 0;
}

.panel,
.action-panel {
  padding: 1.05rem 1.15rem;
}

.counter-chip {
  background: #eef4fb;
  color: #173b70;
}

.fact-grid {
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 0.85rem;
}




.fact-tile {
  padding: 0.95rem 1rem;
  background: #f8fafc;
  border: 1px solid #e7edf4;
}

.fact-tile--wide {
  grid-column: span 2;
}

.commission-card {
  border: 1px solid #e7edf4;
  border-radius: 16px;
  background: #f8fafc;
  padding: 0.85rem 0.95rem;
}

.commission-grid {
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 0.85rem;
  margin-top: 0.7rem;
}

.commission-item {
  padding: 0.75rem 0.85rem;
  background: #fff;
  border: 1px solid #e7edf4;
}

.commission-item--wide {
  grid-column: span 2;
}

.mini-pill {
  background: #edf3fb;
  color: #173b70;
}

.mini-pill--active {
  background: #e7f8ed;
  color: #1f7a3f;
}

.split-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.2rem;
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
  padding: 0.72rem 0.76rem;
  border-bottom: 1px solid #e8edf4;
  text-align: left;
  vertical-align: top;
  font-size: 0.97rem;
}

.sheet-table th {
  background: #f7f9fc;
  color: #173b70;
  font-size: 0.82rem;
  text-transform: uppercase;
}

.sheet-link {
  color: #173b70;
  font-weight: 700;
  text-decoration: none;
}

.sheet-link:hover {
  text-decoration: underline;
}

.stack-list {
  display: grid;
  gap: 0.85rem;
}

.stack-card {
  background: #f8fafc;
  border: 1px solid #e7edf4;
  padding: 0.8rem 0.88rem;
}

.stack-card__row--wide {
  display: grid;
}

.recognition-grid {
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 0.85rem;
}

.recognition-grid--sidebar {
  grid-template-columns: 1fr;
}

.recognition-item {
  background: #f8fafc;
  border: 1px solid #e7edf4;
  padding: 0.75rem 0.85rem;
}

.recognition-item.active {
  background: #fff4f5;
  border-color: #ffd2d7;
}

.comments-box {
  min-height: 120px;
  padding: 1.05rem 1.15rem;
  border-radius: 16px;
  background: #f8fafc;
  border: 1px solid #e7edf4;
  color: #173b70;
  white-space: pre-line;
  line-height: 1.65;
  font-size: 1.02rem;
}

.action-panel {
  display: grid;
  gap: 0.85rem;
}

.action-button {
  width: 100%;
  min-height: 54px;
  border-radius: 999px;
  border: 1px solid #173b70;
  background: #173b70;
  color: #fff;
  font-weight: 700;
  font-size: 1.02rem;
}

.action-button--primary {
  background: #ff3743;
  border-color: #ff3743;
}

.action-button--ghost {
  background: #fff;
  color: #173b70;
}

.action-button:disabled {
  opacity: 0.6;
}

.section-upload-button {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  min-height: 38px;
  padding: 0.45rem 0.9rem;
  border: 1px solid #ff3743;
  border-radius: 999px;
  background: #fff5f5;
  color: #cf2530;
  font-size: 0.92rem;
  font-weight: 700;
}

.section-upload-button:disabled {
  opacity: 0.55;
}

.annual-list {
  display: grid;
  gap: 0.85rem;
}

.panel-empty {
  display: grid;
  place-items: center;
  min-height: 190px;
  text-align: center;
  padding: 1.2rem;
  color: #617389;
  font-size: 1.05rem;
}

.panel-empty--error {
  color: #a61f2b;
  background: #fff8f8;
  border-color: #efc7cc;
}

.panel-empty--soft,
.empty-inline {
  background: #fafcfe;
  border: 1px dashed #d7e1ec;
  border-radius: 16px;
  padding: 1.05rem 1.15rem;
  color: #667a93;
  font-size: 1.08rem;
}

@media (min-width: 1600px) {
  .hero-layout {
    grid-template-columns: 182px minmax(0, 1fr) 340px;
  }

  .content-grid {
    grid-template-columns: minmax(0, 1.28fr) minmax(420px, 0.92fr);
  }

  .hero-panel h2 {
    font-size: 3.45rem;
  }
}

@media (max-width: 1199.98px) {
  .content-grid,
  .hero-layout,
  .split-grid,
  .fact-grid,
  .hero-stats,
  .commission-grid {
    grid-template-columns: 1fr;
  }

  .content-grid {
    grid-template-columns: 1fr;
  }

  .hero-layout {
    grid-template-areas:
      "rail"
      "panel"
      "toolbar"
      "actions";
    grid-template-columns: 1fr;
  }

  .fact-tile--wide,
  .commission-item--wide {
    grid-column: auto;
  }
}

@media (max-width: 767.98px) {
  .d-flex {
    display: block !important;
  }

  .content {
    padding: 0 1rem 1rem;
  }

  .hero-layout {
    gap: 0.85rem;
  }

  .hero-rail {
    grid-template-columns: 54px minmax(0, 1fr);
    grid-template-areas:
      "back year"
      "photo photo";
    align-items: start;
  }

  .photo-card {
    grid-column: 1 / -1;
  }

  .toolbar-row,
  .hero-top,
  .panel-header {
    align-items: stretch;
  }

  .hero-top {
    flex-wrap: wrap;
  }

  .hero-brand {
    width: 104px;
    min-width: 104px;
    height: 84px;
  }

  .hero-brand img {
    width: 66px;
  }

  .panel-header--history,
  .history-controls {
    justify-items: stretch;
  }

  .history-nav {
    justify-content: space-between;
  }

  .search-shell,
  .status-pill,
  .action-button,
  .history-counter,
  .section-upload-button {
    width: 100%;
    justify-content: center;
  }

  .recognition-grid {
    grid-template-columns: 1fr;
  }
}
</style>
























