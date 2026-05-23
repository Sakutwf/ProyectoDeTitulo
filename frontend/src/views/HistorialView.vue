<template>
  <div class="historial-page">
    <div v-if="hojaDeVida" class="historial-shell">
      <section class="hero-card">
        <div class="hero-rail">
          <button type="button" class="back-button" @click="goBack">
            <span aria-hidden="true">←</span>
          </button>
          <div class="hero-photo-card">
            <div class="hero-photo-frame">
              <img
                v-if="user?.voluntario?.foto_perfil_url"
                :src="user.voluntario.foto_perfil_url"
                :alt="`Foto de ${user?.nombre || 'voluntario'}`"
                class="hero-photo-image"
              >
              <span v-else class="hero-photo-placeholder">Foto</span>
            </div>
            <div class="hero-photo-actions">
              <label class="photo-upload-trigger" :class="{ disabled: isUploadingPhoto }">
                {{ user?.voluntario?.foto_perfil_url ? 'Cambiar foto' : 'Subir foto' }}
                <input
                  type="file"
                  accept=".jpg,.jpeg,.png,.webp"
                  class="d-none"
                  :disabled="isUploadingPhoto"
                  @change="onProfilePhotoSelected"
                >
              </label>
              <div v-if="isUploadingPhoto" class="hero-photo-text">Subiendo foto...</div>
            </div>
          </div>
          <div class="hero-year-card">{{ selectedYear }}</div>
        </div>

        <div class="hero-main">
          <div class="hero-header">
            <div>
              <p class="eyebrow">Nombre</p>
              <h1>{{ user?.nombre || 'Hoja de vida' }}</h1>
            </div>
            <div class="hero-brand-badge" aria-label="Cruz Roja">
              <span class="hero-brand-mark">+</span>
              <span class="hero-brand-text">Cruz Roja</span>
            </div>
          </div>

          <div class="summary-grid">
            <article class="summary-pill compact summary-cargo">
              <span class="summary-label">Cargo</span>
              <strong>{{ selectedHojaAnual?.cargo || 'Sin cargo registrado' }}</strong>
            </article>
            <article class="summary-pill outline compact summary-lista">
              <span class="summary-label dark">Lista</span>
              <strong>{{ selectedListLabel }}</strong>
            </article>
            <article class="summary-pill compact summary-asistencia">
              <span class="summary-label">Asistencia</span>
              <strong>{{ formatAttendance(selectedHojaAnual?.porcentaje_asistencia) }}</strong>
            </article>
            <article class="summary-pill outline compact summary-ingreso">
              <span class="summary-label dark">Ingreso</span>
              <strong>{{ formatDate(user?.voluntario?.fecha_ingreso || hojaDeVida.fecha_creacion) }}</strong>
            </article>
            <article class="summary-pill compact summary-registro">
              <span class="summary-label">N. Registro</span>
              <strong>{{ user?.voluntario?.n_registro || 'Sin registro' }}</strong>
            </article>
          </div>
        </div>
      </section>

      <div class="toolbar">
        <input
          v-model="search"
          class="form-control search-box"
          placeholder="Buscar actividades, cursos, premios u observaciones del ano seleccionado..."
        >
        <span class="status-chip toolbar-status" :class="statusClass">
          {{ formattedStatus }}
        </span>
      </div>

      <div class="content-grid">
        <main class="main-column">
          <section class="panel">
            <div class="panel-header">
              <div>
                <p class="panel-kicker">Ano en curso / periodo seleccionado</p>
                <h2>Participacion en actividades</h2>
              </div>
              <span class="counter-chip">{{ filteredActividades.length }} registros</span>
            </div>

            <div v-if="filteredActividades.length" class="activity-list">
              <article
                v-for="actividad in filteredActividades"
                :key="actividad.id"
                class="activity-item"
              >
                <div class="activity-date">
                  {{ actividad.evento?.fecha_inicio_formateada || '-' }}
                </div>
                <div class="activity-body">
                  <div class="activity-row">
                    <h3>{{ actividad.evento?.nombre || 'Actividad sin evento' }}</h3>
                    <span
                      class="activity-status"
                      :class="actividad.pivot?.asistio ? 'is-present' : 'is-absent'"
                    >
                      {{ actividad.pivot?.asistio ? 'Asistio' : 'Ausente' }}
                    </span>
                  </div>
                  <div class="activity-tags">
                    <span class="mini-tag">{{ actividad.tipo || 'Sin tipo' }}</span>
                    <span class="mini-tag subtle">{{ actividad.evento?.tipo || 'Sin categoria' }}</span>
                  </div>
                  <p v-if="actividad.evento?.descripcion" class="activity-text">
                    {{ actividad.evento.descripcion }}
                  </p>
                </div>
              </article>
            </div>
            <div v-else class="empty-state">
              No hay actividades registradas para {{ selectedYear }}.
            </div>
          </section>

          <div class="detail-grid">
            <section class="panel">
              <div class="panel-header compact">
                <h2>Cursos, talleres y seminarios</h2>
              </div>

              <div class="category-stack">
                <div class="category-card">
                  <h3>Cursos</h3>
                  <ul v-if="groupedAntecedentes.cursos.length" class="bullet-list">
                    <li v-for="item in groupedAntecedentes.cursos" :key="item.id_antecedente">
                      {{ item.nombre }}
                    </li>
                  </ul>
                  <p v-else class="empty-inline">Sin cursos registrados en este ano.</p>
                </div>

                <div class="category-card">
                  <h3>Talleres</h3>
                  <ul v-if="groupedAntecedentes.talleres.length" class="bullet-list">
                    <li v-for="item in groupedAntecedentes.talleres" :key="item.id_antecedente">
                      {{ item.nombre }}
                    </li>
                  </ul>
                  <p v-else class="empty-inline">Sin talleres registrados en este ano.</p>
                </div>

                <div class="category-card">
                  <h3>Seminarios</h3>
                  <ul v-if="groupedAntecedentes.seminarios.length" class="bullet-list">
                    <li v-for="item in groupedAntecedentes.seminarios" :key="item.id_antecedente">
                      {{ item.nombre }}
                    </li>
                  </ul>
                  <p v-else class="empty-inline">Sin seminarios registrados en este ano.</p>
                </div>
              </div>
            </section>

            <section class="panel">
              <div class="panel-header compact">
                <h2>Titulos y premios</h2>
              </div>

              <div class="award-stack" v-if="groupedAntecedentes.logros.length">
                <article
                  v-for="item in groupedAntecedentes.logros"
                  :key="item.id_antecedente"
                  class="award-card"
                >
                  <div class="award-top">
                    <span class="award-type" :class="badgeClassForTipo(item.tipo)">{{ prettyTipo(item.tipo) }}</span>
                    <small>{{ formatDateRange(item.fecha_inicio, item.fecha_termino) }}</small>
                  </div>
                  <h3>{{ item.nombre }}</h3>
                  <p v-if="item.descripcion">{{ item.descripcion }}</p>
                  <small v-if="item.duracion">Duracion: {{ item.duracion }}</small>
                </article>
              </div>
              <div v-else class="empty-state small">
                No hay titulos ni premios cargados para {{ selectedYear }}.
              </div>
            </section>
          </div>

          <section class="panel">
            <div class="panel-header compact">
              <h2>Labor efectuada y observaciones</h2>
            </div>
            <div class="observation-grid">
              <div class="observation-box">
                <span class="observation-label">Labor efectuada y observaciones</span>
                <p>
                  {{ laborYObservacionesTexto }}
                </p>
              </div>
              <div class="observation-box">
                <span class="observation-label">Resumen anual</span>
                <p>
                  {{ yearlyAntecedentesSummary }}
                </p>
              </div>
            </div>
          </section>
        </main>

        <aside class="sidebar-column">
          <section class="panel sidebar-panel sidebar-admin-panel">
            <button type="button" class="action-button sidebar-add-button" @click="openBlankAnnualForm">
              Agregar hoja anual
            </button>
          </section>

          <section class="panel sidebar-panel">
            <div class="panel-header compact">
              <div>
                <p class="panel-kicker">Historial</p>
                <h2>Hojas anuales</h2>
              </div>
            </div>

            <div class="annual-list" v-if="annualNavigationCards.length">
              <HistorialAnualCard
                v-for="historial in annualNavigationCards"
                :key="historial.anio"
                :historial="historial"
                :active="Number(historial.anio) === selectedYear"
                :to="yearLink(historial.anio)"
              />
            </div>
            <div v-else class="empty-state small">
              No hay hojas anuales disponibles.
            </div>
          </section>
        </aside>
      </div>

      <div v-if="showAnnualForm" class="modal-backdrop" @click.self="closeAnnualForm">
        <section class="annual-modal">
          <div class="panel-header annual-modal__header">
            <h2>{{ annualForm.id_hoja ? 'Editar hoja anual' : 'Agregar hoja anual' }}</h2>
            <button
              type="button"
              class="text-button annual-modal__close"
              :disabled="isSavingAnnual"
              @click="closeAnnualForm"
            >
              Cerrar
            </button>
          </div>

          <form class="annual-form" @submit.prevent="saveAnnualRecord">
            <div class="form-grid">
              <label class="form-group">
                <span class="form-label">Ano</span>
                <input
                  v-model="annualForm.anio"
                  type="number"
                  min="1900"
                  max="9999"
                  class="admin-input"
                  required
                >
              </label>

              <label class="form-group">
                <span class="form-label">Cargo</span>
                <input v-model="annualForm.cargo" type="text" class="admin-input" placeholder="Ej: Jefa de juventud">
              </label>

              <label class="form-group">
                <span class="form-label">Lista</span>
                <input v-model="annualForm.lista" type="text" class="admin-input" placeholder="Ej: Lista A">
              </label>

              <label class="form-group">
                <span class="form-label">Porcentaje de asistencia</span>
                <input
                  v-model="annualForm.porcentaje_asistencia"
                  type="number"
                  min="0"
                  max="100"
                  step="0.01"
                  class="admin-input"
                  placeholder="0 - 100"
                >
              </label>

              <label class="form-group form-span-2">
                <span class="form-label">Labor efectuada</span>
                <textarea
                  v-model="annualForm.labor_efectuada"
                  rows="4"
                  class="admin-textarea"
                  placeholder="Describe la labor realizada durante el periodo."
                ></textarea>
              </label>

              <label class="form-group form-span-2">
                <span class="form-label">Observaciones del periodo</span>
                <textarea
                  v-model="annualForm.observaciones_generales"
                  rows="4"
                  class="admin-textarea"
                  placeholder="Observaciones generales del ano."
                ></textarea>
              </label>
            </div>

            <div class="form-grid categories-grid">
              <label class="form-group">
                <span class="form-label">Cursos</span>
                <textarea
                  v-model="annualForm.cursos"
                  rows="5"
                  class="admin-textarea"
                  placeholder="Un curso por linea"
                ></textarea>
              </label>

              <label class="form-group">
                <span class="form-label">Talleres</span>
                <textarea
                  v-model="annualForm.talleres"
                  rows="5"
                  class="admin-textarea"
                  placeholder="Un taller por linea"
                ></textarea>
              </label>

              <label class="form-group">
                <span class="form-label">Seminarios</span>
                <textarea
                  v-model="annualForm.seminarios"
                  rows="5"
                  class="admin-textarea"
                  placeholder="Un seminario por linea"
                ></textarea>
              </label>

              <label class="form-group">
                <span class="form-label">Titulos</span>
                <textarea
                  v-model="annualForm.titulos"
                  rows="5"
                  class="admin-textarea"
                  placeholder="Un titulo por linea"
                ></textarea>
              </label>

              <label class="form-group form-span-2">
                <span class="form-label">Premios</span>
                <textarea
                  v-model="annualForm.premios"
                  rows="5"
                  class="admin-textarea"
                  placeholder="Un premio por linea"
                ></textarea>
              </label>
            </div>

            <div class="form-actions">
              <button type="button" class="action-button secondary" :disabled="isSavingAnnual" @click="closeAnnualForm">
                Cancelar
              </button>
              <button type="submit" class="action-button" :disabled="isSavingAnnual">
                {{ isSavingAnnual ? 'Guardando...' : 'Guardar hoja anual' }}
              </button>
            </div>
          </form>
        </section>
      </div>
    </div>

    <div v-else class="alert alert-info">No existe hoja de vida para este usuario.</div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { show_alerta } from '../funciones'
import HistorialAnualCard from '../components/HistorialAnualCard.vue'

const API_BASE = 'http://localhost:8000/api'

const route = useRoute()
const router = useRouter()
const currentYear = new Date().getFullYear()

const user = ref(null)
const hojaDeVida = ref(null)
const hojasAnuales = ref([])
const antecedentes = ref([])
const actividades = ref([])
const search = ref('')
const isUploadingPhoto = ref(false)
const showAnnualForm = ref(false)
const isSavingAnnual = ref(false)
const annualForm = ref(createEmptyAnnualForm(currentYear))

const normalizedSearch = computed(() => search.value.trim().toLowerCase())

const availableYears = computed(() => {
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

  years.add(currentYear)

  return [...years].sort((a, b) => b - a)
})

const selectedYear = computed(() => {
  const queryYear = Number(route.query.anio)
  if (Number.isInteger(queryYear) && queryYear > 0) {
    return queryYear
  }

  if (availableYears.value.includes(currentYear)) {
    return currentYear
  }

  return availableYears.value[0] || currentYear
})

const selectedHojaAnual = computed(() =>
  hojasAnuales.value.find((historial) => Number(historial.anio) === selectedYear.value) || null
)

const annualNavigationCards = computed(() => {
  const annualsByYear = new Map(
    hojasAnuales.value.map((historial) => [Number(historial.anio), historial])
  )

  return availableYears.value.map((year) => annualsByYear.get(year) || {
    anio: year,
    cargo: null,
    lista: null,
    porcentaje_asistencia: null,
    labor_efectuada: null,
    observaciones_generales: null
  })
})

const filteredActividades = computed(() => {
  const items = actividades.value
    .filter((actividad) => getYearFromDate(actividad.evento?.fecha_inicio) === selectedYear.value)
    .sort((a, b) => (b.evento?.fecha_inicio || '').localeCompare(a.evento?.fecha_inicio || ''))

  if (!normalizedSearch.value) return items

  return items.filter((actividad) =>
    (actividad.tipo || '').toLowerCase().includes(normalizedSearch.value) ||
    (actividad.evento?.nombre || '').toLowerCase().includes(normalizedSearch.value) ||
    (actividad.evento?.tipo || '').toLowerCase().includes(normalizedSearch.value) ||
    ((actividad.pivot?.asistio ? 'asistio' : 'ausente')).includes(normalizedSearch.value)
  )
})

const filteredAntecedentes = computed(() => {
  const items = antecedentes.value
    .filter((antecedente) => getYearFromAntecedente(antecedente) === selectedYear.value)

  if (!normalizedSearch.value) return items

  return items.filter((antecedente) =>
    (antecedente.tipo || '').toLowerCase().includes(normalizedSearch.value) ||
    (antecedente.nombre || '').toLowerCase().includes(normalizedSearch.value) ||
    (antecedente.descripcion || '').toLowerCase().includes(normalizedSearch.value)
  )
})

const groupedAntecedentes = computed(() => ({
  cursos: filteredAntecedentes.value.filter((item) => normalizeTipo(item.tipo) === 'CURSO'),
  talleres: filteredAntecedentes.value.filter((item) => normalizeTipo(item.tipo) === 'TALLER'),
  seminarios: filteredAntecedentes.value.filter((item) => ['SEMINARIO', 'CAPACITACION'].includes(normalizeTipo(item.tipo))),
  logros: filteredAntecedentes.value.filter((item) => ['TITULO', 'PREMIO'].includes(normalizeTipo(item.tipo)))
}))

const selectedListLabel = computed(() => {
  if (selectedHojaAnual.value?.lista) {
    return selectedHojaAnual.value.lista
  }

  return findLegacyListForYear(selectedYear.value) || 'No registra'
})

const formattedStatus = computed(() => {
  const raw = hojaDeVida.value?.estado || user.value?.estado || 'Sin estado'
  const normalized = String(raw).toLowerCase()
  return normalized.charAt(0).toUpperCase() + normalized.slice(1)
})

const statusClass = computed(() => {
  const raw = String(hojaDeVida.value?.estado || user.value?.estado || '').toUpperCase()
  return raw === 'ACTIVO' ? 'is-active' : 'is-inactive'
})

const yearlyAntecedentesSummary = computed(() => {
  const totalAntecedentes = filteredAntecedentes.value.length
  const totalActividades = filteredActividades.value.length

  if (!totalAntecedentes && !totalActividades) {
    return 'Todavia no hay actividades ni antecedentes asociados a este periodo.'
  }

  return `Este periodo registra ${totalActividades} actividad(es) y ${totalAntecedentes} antecedente(s) vinculados al voluntario.`
})

const laborYObservacionesTexto = computed(() => {
  const parts = [
    selectedHojaAnual.value?.labor_efectuada?.trim(),
    selectedHojaAnual.value?.observaciones_generales?.trim()
  ].filter(Boolean)

  if (!parts.length) {
    return 'Sin informacion registrada para este ano.'
  }

  return parts.join(' ')
})

function yearLink(year) {
  return {
    name: 'HistorialView',
    params: { id: route.params.id },
    query: { anio: year }
  }
}

function createEmptyAnnualForm(year) {
  return {
    id_hoja: null,
    anio: year,
    cargo: '',
    lista: '',
    porcentaje_asistencia: '',
    labor_efectuada: '',
    observaciones_generales: '',
    cursos: '',
    talleres: '',
    seminarios: '',
    titulos: '',
    premios: ''
  }
}

function formatDate(dateString) {
  if (!dateString) return '-'
  return dateString.slice(0, 10).split('-').reverse().join('-')
}

function formatDateRange(start, end) {
  if (!start && !end) return '-'
  if (start && end) return `${formatDate(start)} - ${formatDate(end)}`
  return formatDate(start || end)
}

function formatAttendance(value) {
  if (value === null || value === undefined || value === '') return 'Sin porcentaje'
  return `${value}%`
}

function prettyTipo(tipo) {
  const normalized = normalizeTipo(tipo)
  if (normalized === 'TITULO') return 'Titulo'
  if (normalized === 'PREMIO') return 'Premio'
  return normalized || 'Antecedente'
}

function badgeClassForTipo(tipo) {
  return normalizeTipo(tipo) === 'PREMIO' ? 'is-premio' : 'is-titulo'
}

function normalizeTipo(tipo) {
  return String(tipo || '').trim().toUpperCase()
}

function getYearFromDate(dateString) {
  if (!dateString) return null
  return Number(dateString.slice(0, 4))
}

function getYearFromAntecedente(antecedente) {
  return getYearFromDate(antecedente?.fecha_inicio) || getYearFromDate(antecedente?.fecha_termino)
}

function getAntecedentesForYear(year) {
  return antecedentes.value.filter((antecedente) => getYearFromAntecedente(antecedente) === Number(year))
}

function joinAntecedentesByTipo(year, tipos) {
  return getAntecedentesForYear(year)
    .filter((item) => tipos.includes(normalizeTipo(item.tipo)))
    .map((item) => item.nombre)
    .filter(Boolean)
    .join('\n')
}

function findLegacyListForYear(year) {
  return getAntecedentesForYear(year)
    .find((item) => normalizeTipo(item.tipo) === 'CARGO')
    ?.nombre || ''
}

function buildAnnualForm(year, annual = null) {
  const targetYear = Number(year) || currentYear

  return {
    id_hoja: annual?.id_hoja || null,
    anio: targetYear,
    cargo: annual?.cargo || '',
    lista: annual?.lista || findLegacyListForYear(targetYear),
    porcentaje_asistencia: annual?.porcentaje_asistencia ?? '',
    labor_efectuada: annual?.labor_efectuada || '',
    observaciones_generales: annual?.observaciones_generales || '',
    cursos: joinAntecedentesByTipo(targetYear, ['CURSO']),
    talleres: joinAntecedentesByTipo(targetYear, ['TALLER']),
    seminarios: joinAntecedentesByTipo(targetYear, ['SEMINARIO', 'CAPACITACION']),
    titulos: joinAntecedentesByTipo(targetYear, ['TITULO']),
    premios: joinAntecedentesByTipo(targetYear, ['PREMIO'])
  }
}

function parseLineItems(text) {
  return String(text || '')
    .split(/\r?\n/)
    .map((item) => item.trim())
    .filter(Boolean)
}

function getSuggestedNewYear() {
  const existingYears = new Set(hojasAnuales.value.map((item) => Number(item.anio)).filter(Boolean))
  let year = currentYear

  while (existingYears.has(year)) {
    year += 1
  }

  return year
}

function openBlankAnnualForm() {
  annualForm.value = createEmptyAnnualForm(getSuggestedNewYear())
  showAnnualForm.value = true
}

function closeAnnualForm() {
  showAnnualForm.value = false
  annualForm.value = createEmptyAnnualForm(selectedYear.value)
}

function goBack() {
  router.back()
}

function getValidationMessage(error) {
  const responseErrors = error?.response?.data?.errors
  if (!responseErrors) {
    return 'No se pudo guardar la hoja anual.'
  }

  return Object.values(responseErrors)
    .flat()
    .join(' ')
}

async function loadUser() {
  const userId = route.params.id
  const res = await axios.get(`${API_BASE}/user/${userId}`)

  user.value = res.data
  hojaDeVida.value = res.data?.voluntario?.hoja_de_vida || null
  hojasAnuales.value = [...(hojaDeVida.value?.hojas_anuales || [])]
    .sort((a, b) => Number(b.anio) - Number(a.anio))
  antecedentes.value = hojaDeVida.value?.antecedentes || []
  actividades.value = (res.data?.actividades || []).map((actividad) => ({
    ...actividad,
    evento: actividad.evento
      ? {
          ...actividad.evento,
          fecha_inicio_formateada: formatDate(actividad.evento.fecha_inicio)
        }
      : null
  }))
}

async function saveAnnualRecord() {
  if (!hojaDeVida.value?.id_libro) {
    show_alerta('No existe una hoja de vida asociada al voluntario.', 'error')
    return
  }

  const year = Number(annualForm.value.anio)
  if (!Number.isInteger(year) || year < 1900 || year > 9999) {
    show_alerta('Debes indicar un ano valido de cuatro digitos.', 'error')
    return
  }

  const payload = {
    hoja_de_vida_id: hojaDeVida.value.id_libro,
    anio: year,
    cargo: annualForm.value.cargo.trim() || null,
    lista: annualForm.value.lista.trim() || null,
    porcentaje_asistencia: annualForm.value.porcentaje_asistencia === '' ? null : Number(annualForm.value.porcentaje_asistencia),
    labor_efectuada: annualForm.value.labor_efectuada.trim() || null,
    observaciones_generales: annualForm.value.observaciones_generales.trim() || null,
    antecedentes: {
      cursos: parseLineItems(annualForm.value.cursos),
      talleres: parseLineItems(annualForm.value.talleres),
      seminarios: parseLineItems(annualForm.value.seminarios),
      titulos: parseLineItems(annualForm.value.titulos),
      premios: parseLineItems(annualForm.value.premios)
    }
  }

  const request = annualForm.value.id_hoja
    ? axios.put(`${API_BASE}/hojas-anuales/${annualForm.value.id_hoja}`, payload)
    : axios.post(`${API_BASE}/hojas-anuales`, payload)

  isSavingAnnual.value = true

  try {
    await request
    await router.replace(yearLink(year))
    await loadUser()
    showAnnualForm.value = false
    annualForm.value = buildAnnualForm(year, selectedHojaAnual.value)
    show_alerta('Hoja anual guardada correctamente.', 'success')
  } catch (error) {
    show_alerta(getValidationMessage(error), 'error')
  } finally {
    isSavingAnnual.value = false
  }
}

async function onProfilePhotoSelected(event) {
  const file = event.target.files?.[0]
  event.target.value = ''

  if (!file || !user.value?.id) {
    return
  }

  const formData = new FormData()
  formData.append('foto_perfil', file)
  isUploadingPhoto.value = true

  try {
    const response = await axios.post(`${API_BASE}/user/${user.value.id}/foto-perfil`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    user.value = response.data
    hojaDeVida.value = response.data?.voluntario?.hoja_de_vida || hojaDeVida.value
  } finally {
    isUploadingPhoto.value = false
  }
}

onMounted(async () => {
  await loadUser()
})
</script>

<style scoped>
.historial-page {
  padding: 1.5rem;
  background:
    radial-gradient(circle at top left, rgba(255, 49, 61, 0.1), transparent 24%),
    linear-gradient(180deg, #f2efe9 0%, #f8f5ef 100%);
  min-height: 100vh;
}

.historial-shell {
  max-width: 1380px;
  margin: 0 auto;
}

.hero-card {
  display: grid;
  grid-template-columns: 228px 1fr;
  gap: 1.2rem;
  align-items: stretch;
  margin-bottom: 1rem;
}

.hero-rail {
  display: grid;
  grid-template-columns: 58px minmax(0, 1fr);
  grid-template-areas:
    "back year"
    "photo photo";
  gap: 0.5rem;
}

.back-button {
  grid-area: back;
  border: none;
  border-radius: 18px;
  background: #0f2f5f;
  color: #fff;
  min-height: 58px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
  font-size: 1rem;
  box-shadow: 0 12px 24px rgba(15, 47, 95, 0.16);
  padding: 0.15rem;
}

.back-button::before {
  content: '\2190';
  font-size: 1.7rem;
  line-height: 1;
  font-weight: 900;
}

.back-button span[aria-hidden='true'] {
  font-size: 0;
  line-height: 0;
}

.hero-photo-card,
.hero-year-card {
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 18px;
}

.hero-photo-card {
  grid-area: photo;
  min-height: 236px;
  flex-direction: column;
  justify-content: flex-start;
  background: #ffffff;
  border: 1px solid rgba(15, 47, 95, 0.08);
  color: #163a69;
  box-shadow: 0 16px 30px rgba(15, 47, 95, 0.08);
  padding: 0.65rem 0.65rem 0.55rem;
}

.hero-photo-frame {
  width: 100%;
  flex: 1;
  min-height: 168px;
  border-radius: 16px;
  background: linear-gradient(180deg, #ffffff 0%, #f5f7fa 100%);
  border: 1px dashed #d4dbe5;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.hero-photo-placeholder {
  color: #7f8da3;
  font-weight: 700;
  font-size: 0.95rem;
}

.hero-photo-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-photo-text {
  text-align: center;
  color: #7a8799;
  font-size: 0.72rem;
  line-height: 1.25;
}

.hero-photo-actions {
  width: 100%;
  margin-top: auto;
  padding-top: 0.5rem;
  display: grid;
  gap: 0.3rem;
}

.photo-upload-trigger {
  display: inline-flex;
  width: 100%;
  justify-content: center;
  align-items: center;
  border-radius: 999px;
  background: #0f2f5f;
  color: #fff;
  font-size: 0.74rem;
  font-weight: 700;
  padding: 0.45rem 0.75rem;
  cursor: pointer;
}

.photo-upload-trigger.disabled {
  opacity: 0.6;
  cursor: wait;
}

.hero-year-card {
  grid-area: year;
  min-height: 58px;
  background: #ff313d;
  color: #fff;
  font-size: 2rem;
  font-weight: 800;
  box-shadow: 0 16px 28px rgba(255, 49, 61, 0.18);
}

.hero-main {
  background: rgba(255, 255, 255, 1);
  border: 1px solid rgba(15, 47, 95, 0.08);
  border-radius: 22px;
  padding: 2rem 2rem 1rem;
  box-shadow: 0 22px 50px rgba(15, 47, 95, 0.08);
  backdrop-filter: blur(10px);
}

.hero-header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
  margin-bottom: 0.5rem;
  flex-wrap: wrap;
}

.eyebrow {
  margin: 0 0 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.76rem;
  color: #73839a;
}

.hero-header h1 {
  margin: 0;
  color: #0f2f5f;
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 800;
  line-height: 1;
}

.hero-brand-badge {
  min-width: 110px;
  min-height: 88px;
  border-radius: 18px;
  background: #ffffff;
  border: 1px solid rgba(15, 47, 95, 0.08);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 0;
  box-shadow: 0 10px 24px rgba(15, 47, 95, 0.06);
}

.hero-brand-mark {
  color: #ff313d;
  font-size: 5rem;
  line-height: 0.8;
  font-weight: 800;
}

.hero-brand-text {
  color: #163a69;
  font-size: 1rem;
  font-weight: 700;
}

.status-chip,
.summary-pill,
.counter-chip {
  border-radius: 999px;
  padding: 0.62rem 0.95rem;
}

.status-chip {
  display: inline-flex;
  align-items: center;
  font-weight: 700;
  font-size: 1rem;
}

.status-chip.is-active {
  background: #d9d9d9;
  color: #0f2f5f;
}

.status-chip.is-inactive {
  background: #f0d9dc;
  color: #8b1e2c;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(12, minmax(0, 1fr));
  grid-template-areas:
    "cargo cargo cargo cargo cargo cargo lista lista lista lista lista lista"
    "asistencia asistencia asistencia asistencia ingreso ingreso ingreso ingreso registro registro registro registro";
  gap: 0.85rem;
  align-items: stretch;
  margin-top: 0.45rem;
}

.summary-pill {
  background: #0f2f5f;
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: 62px;
}

.summary-cargo {
  grid-area: cargo;
}

.summary-lista {
  grid-area: lista;
}

.summary-asistencia {
  grid-area: asistencia;
}

.summary-ingreso {
  grid-area: ingreso;
}

.summary-registro {
  grid-area: registro;
}

.summary-pill.outline {
  background: #fff;
  color: #0f2f5f;
  border: 3px solid #0f2f5f;
}

.summary-pill.compact {
  align-items: center;
  text-align: center;
}

.summary-label {
  display: block;
  margin-bottom: 0.12rem;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  opacity: 0.8;
}

.summary-label.dark {
  color: #5f6f82;
  opacity: 1;
}

.summary-pill strong {
  font-size: 0.96rem;
  line-height: 1.15;
}

.toolbar {
  margin: 1rem 0 1.2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.search-box {
  max-width: 580px;
  border-radius: 999px;
  border: 1px solid #dfe5ec;
  padding: 0.85rem 1rem;
  box-shadow: 0 8px 24px rgba(15, 47, 95, 0.05);
}

.toolbar-status {
  margin-left: auto;
}

.action-button,
.text-button {
  border: none;
  border-radius: 999px;
  font-weight: 700;
  transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
}

.action-button {
  background: #ff313d;
  color: #fff;
  padding: 0.8rem 1.1rem;
  box-shadow: 0 14px 28px rgba(255, 49, 61, 0.16);
}

.action-button.secondary {
  background: #ffffff;
  color: #0f2f5f;
  border: 1px solid #d4dbe5;
  box-shadow: none;
}

.action-button:disabled,
.text-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.action-button:not(:disabled):hover,
.text-button:not(:disabled):hover {
  transform: translateY(-1px);
}

.text-button {
  background: transparent;
  color: #0f2f5f;
  padding: 0.45rem 0.25rem;
}

.annual-form {
  display: grid;
  gap: 1rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.form-group {
  display: grid;
  gap: 0.45rem;
}

.form-span-2 {
  grid-column: span 2;
}

.form-label {
  font-size: 0.82rem;
  font-weight: 700;
  color: #3f526f;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.admin-input,
.admin-textarea {
  width: 100%;
  border-radius: 18px;
  border: 1px solid #d8e0ea;
  background: #fff;
  color: #163a69;
  padding: 0.9rem 1rem;
  outline: none;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
}

.admin-input:focus,
.admin-textarea:focus {
  border-color: #0f2f5f;
  box-shadow: 0 0 0 3px rgba(15, 47, 95, 0.08);
}

.admin-textarea {
  resize: vertical;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1000;
  background: rgba(15, 47, 95, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

.annual-modal {
  width: min(960px, 100%);
  max-height: calc(100vh - 3rem);
  overflow-y: auto;
  background: rgba(255, 255, 255, 0.98);
  border: 1px solid rgba(15, 47, 95, 0.1);
  border-radius: 28px;
  padding: 1.4rem;
  box-shadow: 0 28px 60px rgba(15, 47, 95, 0.2);
}

.annual-modal__header {
  margin-bottom: 1rem;
}

.annual-modal__close {
  padding-right: 0;
}

.content-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.9fr);
  gap: 1.2rem;
  align-items: start;
}

.main-column,
.sidebar-column {
  display: grid;
  gap: 1.2rem;
}

.panel {
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(15, 47, 95, 0.08);
  border-radius: 28px;
  padding: 1.2rem;
  box-shadow: 0 18px 40px rgba(15, 47, 95, 0.07);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1rem;
}

.panel-header.compact {
  margin-bottom: 0.8rem;
}

.panel-kicker {
  margin: 0 0 0.2rem;
  font-size: 0.76rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #7a8799;
}

.panel-header h2 {
  margin: 0;
  color: #0f2f5f;
  font-size: 1.45rem;
  font-weight: 800;
}

.counter-chip {
  background: #eef4fb;
  color: #0f2f5f;
  font-weight: 700;
}

.activity-list {
  display: grid;
  gap: 0.85rem;
}

.activity-item {
  display: grid;
  grid-template-columns: 104px 1fr;
  gap: 1rem;
  padding: 1rem;
  border-radius: 22px;
  background: #fbfbfc;
  border: 1px solid #edf0f4;
}

.activity-date {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 86px;
  border-radius: 20px;
  background: #0f2f5f;
  color: #fff;
  font-weight: 700;
  text-align: center;
}

.activity-row {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  align-items: center;
  margin-bottom: 0.45rem;
  flex-wrap: wrap;
}

.activity-row h3,
.category-card h3,
.award-card h3 {
  margin: 0;
  color: #102d56;
  font-size: 1.05rem;
  font-weight: 800;
}

.activity-tags {
  display: flex;
  gap: 0.45rem;
  flex-wrap: wrap;
  margin-bottom: 0.55rem;
}

.mini-tag,
.activity-status,
.award-type {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 0.28rem 0.65rem;
  font-size: 0.8rem;
  font-weight: 700;
}

.mini-tag {
  background: #e9eef6;
  color: #163a69;
}

.mini-tag.subtle {
  background: #f4f0ea;
  color: #755f44;
}

.activity-status.is-present {
  background: #e7f8ed;
  color: #18794e;
}

.activity-status.is-absent {
  background: #fff4e5;
  color: #9a6700;
}

.activity-text,
.award-card p,
.observation-box p,
.empty-inline {
  margin: 0;
  color: #5f6f82;
  line-height: 1.55;
}

.detail-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.95fr);
  gap: 1.2rem;
}

.category-stack,
.award-stack,
.annual-list {
  display: grid;
  gap: 0.85rem;
}

.category-card,
.award-card,
.observation-box {
  border-radius: 22px;
  background: #fbfbfc;
  border: 1px solid #edf0f4;
  padding: 1rem;
}

.bullet-list {
  margin: 0;
  padding-left: 1.1rem;
  color: #163a69;
}

.bullet-list li + li {
  margin-top: 0.35rem;
}

.award-top {
  display: flex;
  justify-content: space-between;
  gap: 0.7rem;
  align-items: center;
  margin-bottom: 0.4rem;
  flex-wrap: wrap;
  color: #7a8799;
}

.award-type.is-premio {
  background: #fee4e2;
  color: #d92d20;
}

.award-type.is-titulo {
  background: #ece9ff;
  color: #6941c6;
}

.observation-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.observation-label {
  display: block;
  margin-bottom: 0.45rem;
  color: #73839a;
  text-transform: uppercase;
  font-size: 0.78rem;
  letter-spacing: 0.06em;
}

.empty-state {
  border-radius: 22px;
  background: #faf7f3;
  border: 1px dashed #d8d0c6;
  color: #7a6f5d;
  padding: 1rem;
}

.empty-state.small {
  font-size: 0.95rem;
}

.sidebar-panel {
  position: sticky;
  top: 1rem;
}

.sidebar-admin-panel {
  position: static;
  padding: 1rem;
}

.sidebar-add-button {
  width: 100%;
  justify-content: center;
  background: #0f2f5f;
  box-shadow: 0 14px 28px rgba(15, 47, 95, 0.16);
}

@media (max-width: 1199.98px) {
  .content-grid,
  .detail-grid,
  .observation-grid,
  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-span-2 {
    grid-column: auto;
  }

  .sidebar-panel {
    position: static;
  }
}

@media (max-width: 767.98px) {
  .historial-page {
    padding: 1rem;
  }

  .hero-card {
    grid-template-columns: 1fr;
  }

  .hero-rail {
    grid-template-columns: 54px minmax(0, 1fr);
    grid-template-areas:
      "back year"
      "photo photo";
  }

  .hero-photo-card {
    min-height: 190px;
  }

  .back-button {
    min-height: 54px;
  }

  .hero-year-card {
    min-height: 54px;
    font-size: 1.25rem;
  }

  .summary-grid {
    grid-template-columns: 1fr;
    grid-template-areas: none;
  }

  .form-actions {
    width: 100%;
    justify-content: stretch;
  }

  .action-button {
    width: 100%;
  }

  .activity-item {
    grid-template-columns: 1fr;
  }

  .activity-date {
    min-height: 64px;
  }
}
</style>
