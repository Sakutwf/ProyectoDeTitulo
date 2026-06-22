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
            <div v-if="canManageHistory" class="hero-photo-actions">
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
              <p class="eyebrow">N. Registro {{ user?.voluntario?.n_registro || 'Sin registro' }}</p>
              <h1>{{ user?.nombre || 'Hoja de vida' }}</h1>
            </div>
            <div class="hero-actions">
              <button type="button" class="session-action" @click="openPdfExport">
                Exportar PDF
              </button>
              <div class="hero-brand-badge" aria-label="Cruz Roja">
                <span class="hero-brand-mark">+</span>
                <span class="hero-brand-text">Cruz Roja</span>
              </div>
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
              No hay actividades de servicio registradas para {{ selectedYear }}.
            </div>
          </section>

          <section class="panel">
            <div class="panel-header compact">
              <div>
                <p class="panel-kicker">Horas en filial</p>
                <h2>Registro manual alternativo</h2>
              </div>
              <div class="panel-header-actions">
                <span class="counter-chip">{{ formatHours(totalFilialHoursForYear) }}</span>
                <button v-if="canManageHistory" type="button" class="action-button" @click="openFilialRecordModal()">
                  Registrar horas
                </button>
              </div>
            </div>

            <div v-if="filteredFilialRecords.length" class="filial-record-list">
              <article
                v-for="record in filteredFilialRecords"
                :key="record.id"
                class="filial-record-card"
              >
                <div class="filial-record-card__top">
                  <div>
                    <strong>{{ formatDate(record.fecha) }}</strong>
                    <p>{{ formatTime(record.hora_entrada) }} - {{ formatTime(record.hora_salida) }}</p>
                  </div>
                  <span class="mini-tag">{{ formatHours(record.horas_totales) }}</span>
                </div>
                <div v-if="canManageHistory" class="filial-record-card__footer">
                  <button type="button" class="text-button award-link" @click="openFilialRecordModal(record)">
                    Editar
                  </button>
                  <button
                    type="button"
                    class="text-button award-delete"
                    :disabled="deletingFilialRecordId === record.id"
                    @click="deleteFilialRecord(record)"
                  >
                    {{ deletingFilialRecordId === record.id ? 'Eliminando...' : 'Eliminar' }}
                  </button>
                </div>
              </article>
            </div>
            <div v-else class="empty-state small">
              No hay horas de filial registradas para {{ selectedYear }}.
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
                  <ul v-if="groupedFormacion.cursos.length" class="bullet-list">
                    <li v-for="item in groupedFormacion.cursos" :key="item.id">
                      {{ item.label }}
                    </li>
                  </ul>
                  <p v-else class="empty-inline">Sin cursos registrados en este ano.</p>
                </div>

                <div class="category-card">
                  <h3>Talleres</h3>
                  <ul v-if="groupedFormacion.talleres.length" class="bullet-list">
                    <li v-for="item in groupedFormacion.talleres" :key="item.id">
                      {{ item.label }}
                    </li>
                  </ul>
                  <p v-else class="empty-inline">Sin talleres registrados en este ano.</p>
                </div>

                <div class="category-card">
                  <h3>Seminarios</h3>
                  <ul v-if="groupedFormacion.seminarios.length" class="bullet-list">
                    <li v-for="item in groupedFormacion.seminarios" :key="item.id">
                      {{ item.label }}
                    </li>
                  </ul>
                  <p v-else class="empty-inline">Sin seminarios registrados en este ano.</p>
                </div>
              </div>
            </section>

            <section class="panel">
              <div class="panel-header compact">
                <h2>Titulos y premios</h2>
                <button type="button" class="action-button award-upload-button" @click="openAchievementModal">
                  Carga aqui tu certificado
                </button>
              </div>

              <div class="award-stack" v-if="groupedLogros.length">
                <article
                  v-for="item in groupedLogros"
                  :key="item.id_antecedente"
                  class="award-card"
                >
                  <div class="award-top">
                    <span class="award-type" :class="badgeClassForTipo(item.tipo)">{{ prettyTipo(item.tipo) }}</span>
                    <small>{{ formatDateRange(item.fecha_inicio, item.fecha_termino) }}</small>
                  </div>
                  <h3>{{ item.nombre }}</h3>
                  <p v-if="item.descripcion">{{ item.descripcion }}</p>
                  <img
                    v-if="hasImageAttachment(item)"
                    :src="item.archivo_url"
                    :alt="`Respaldo de ${item.nombre}`"
                    class="award-image"
                  >
                  <div v-else-if="hasPdfAttachment(item)" class="award-file-chip">
                    PDF adjunto
                  </div>
                  <div v-if="item.archivo_url" class="award-actions">
                    <a :href="item.archivo_url" target="_blank" rel="noopener" class="text-button award-link">
                      {{ hasPdfAttachment(item) ? 'Ver PDF' : 'Ver archivo' }}
                    </a>
                  </div>
                  <small v-if="item.duracion">Duracion: {{ item.duracion }}</small>
                  <div v-if="canManageHistory" class="award-footer">
                    <button type="button" class="text-button award-link" @click="openAchievementModal(item)">
                      Editar
                    </button>
                    <button
                      type="button"
                      class="text-button award-delete"
                      :disabled="deletingAchievementId === item.id_antecedente"
                      @click="deleteAchievement(item)"
                    >
                      {{ deletingAchievementId === item.id_antecedente ? 'Eliminando...' : 'Eliminar' }}
                    </button>
                  </div>
                </article>
              </div>
              <div v-if="manualTitulosPremiosLines.length" class="category-card manual-awards-card">
                <h3>Registro anual</h3>
                <ul class="bullet-list">
                  <li v-for="(item, index) in manualTitulosPremiosLines" :key="`manual-award-${index}`">
                    {{ item }}
                  </li>
                </ul>
              </div>
              <div v-if="!groupedLogros.length && !manualTitulosPremiosLines.length" class="empty-state small">
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
                <span class="observation-label">Labor efectuada</span>
                <textarea
                  v-if="canManageHistory"
                  v-model="observationDraft.labor_efectuada"
                  class="observation-editor"
                  rows="5"
                  placeholder="Escribe la labor efectuada durante este periodo."
                ></textarea>
                <p v-else class="observation-static">
                  {{ selectedHojaAnual?.labor_efectuada || 'Sin labor efectuada registrada para este periodo.' }}
                </p>
              </div>
              <div class="observation-box">
                <span class="observation-label">Observaciones del periodo</span>
                <textarea
                  v-if="canManageHistory"
                  v-model="observationDraft.observaciones_generales"
                  class="observation-editor"
                  rows="5"
                  placeholder="Escribe observaciones relevantes para este periodo."
                ></textarea>
                <p v-else class="observation-static">
                  {{ selectedHojaAnual?.observaciones_generales || 'Sin observaciones registradas para este periodo.' }}
                </p>
              </div>
              <div class="observation-box observation-box--summary">
                <span class="observation-label">Resumen anual</span>
                <p>
                  {{ yearlyAntecedentesSummary }}
                </p>
              </div>
            </div>
            <div v-if="canManageHistory && hasObservationDraftChanges" class="observation-actions">
              <button
                type="button"
                class="action-button secondary"
                :disabled="isSavingObservation"
                @click="resetObservationDraft"
              >
                Cancelar
              </button>
              <button
                type="button"
                class="action-button observation-save-button"
                :disabled="isSavingObservation"
                @click="saveObservationDraft"
              >
                {{ isSavingObservation ? 'Guardando...' : (selectedHojaAnual ? 'Guardar cambios' : 'Guardar') }}
              </button>
            </div>
          </section>
        </main>

        <aside class="sidebar-column">
          <section v-if="canManageHistory" class="panel sidebar-panel sidebar-admin-panel">
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
                  :value="formatAttendance(annualForm.porcentaje_asistencia)"
                  type="text"
                  class="admin-input"
                  readonly
                >
                <small class="hero-photo-text">Se calcula automaticamente solo con actividades de servicio.</small>
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
                  placeholder="Cursos registrados para el ano."
                ></textarea>
              </label>

              <label class="form-group">
                <span class="form-label">Talleres</span>
                <textarea
                  v-model="annualForm.talleres"
                  rows="5"
                  class="admin-textarea"
                  placeholder="Talleres registrados para el ano."
                ></textarea>
              </label>

              <label class="form-group">
                <span class="form-label">Seminarios</span>
                <textarea
                  v-model="annualForm.seminarios"
                  rows="5"
                  class="admin-textarea"
                  placeholder="Seminarios registrados para el ano."
                ></textarea>
              </label>

              <label class="form-group form-span-2">
                <span class="form-label">Titulos y premios</span>
                <textarea
                  v-model="annualForm.titulos_premios"
                  rows="5"
                  class="admin-textarea"
                  placeholder="Titulos y premios registrados para el ano."
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

      <div v-if="showFilialRecordModal" class="modal-backdrop" @click.self="closeFilialRecordModal">
        <section class="annual-modal filial-modal">
          <div class="panel-header annual-modal__header">
            <h2>{{ isEditingFilialRecord ? 'Editar horas en filial' : 'Registrar horas en filial' }}</h2>
            <button
              type="button"
              class="text-button annual-modal__close"
              :disabled="isSavingFilialRecord"
              @click="closeFilialRecordModal"
            >
              Cerrar
            </button>
          </div>

          <form class="filial-record-form" @submit.prevent="saveFilialRecord">
            <label class="form-group">
              <span class="form-label">Fecha</span>
              <input v-model="filialRecordForm.fecha" type="date" class="admin-input" required>
            </label>

            <div class="filial-record-form__times">
              <label class="form-group">
                <span class="form-label">Hora de entrada</span>
                <input v-model="filialRecordForm.hora_entrada" type="time" class="admin-input" required>
              </label>

              <label class="form-group">
                <span class="form-label">Hora de salida</span>
                <input v-model="filialRecordForm.hora_salida" type="time" class="admin-input" required>
              </label>
            </div>

            <p class="form-note">
              Usa este registro cuando haya que corregir o completar manualmente una marcacion de filial.
            </p>

            <div class="form-actions">
              <button
                type="button"
                class="action-button secondary"
                :disabled="isSavingFilialRecord"
                @click="closeFilialRecordModal"
              >
                Cancelar
              </button>
              <button type="submit" class="action-button achievement-submit-button" :disabled="isSavingFilialRecord">
                {{ isSavingFilialRecord ? 'Guardando...' : (isEditingFilialRecord ? 'Guardar cambios' : 'Guardar registro') }}
              </button>
            </div>
          </form>
        </section>
      </div>

      <div v-if="showAchievementModal" class="modal-backdrop" @click.self="closeAchievementModal">
        <section class="annual-modal achievement-modal">
          <div class="panel-header annual-modal__header">
            <h2>{{ isEditingAchievement ? 'Editar titulo o premio' : 'Cargar titulo o premio' }}</h2>
            <button
              type="button"
              class="text-button annual-modal__close"
              :disabled="isUploadingAchievement"
              @click="closeAchievementModal"
            >
              Cerrar
            </button>
          </div>

          <form class="achievement-form" @submit.prevent="saveAchievement">
            <label class="form-group">
              <span class="form-label">Tipo</span>
              <select v-model="achievementForm.tipo" class="admin-input" required>
                <option v-for="option in achievementTypeOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </label>

            <label class="form-group">
              <span class="form-label">Nombre</span>
              <input
                v-model="achievementForm.nombre"
                type="text"
                class="admin-input"
                placeholder="Ej: Monitor comunitario"
                required
              >
            </label>

            <label class="form-group">
              <span class="form-label">Fecha</span>
              <input v-model="achievementForm.fecha_inicio" type="date" class="admin-input" required>
            </label>

            <label class="form-group">
              <span class="form-label">Archivo</span>
              <label class="file-trigger">
                {{ achievementFileButtonLabel }}
                <input
                  type="file"
                  accept=".jpg,.jpeg,.png,.webp,.pdf"
                  class="d-none"
                  @change="onAchievementFileSelected"
                >
              </label>
              <small class="hero-photo-text achievement-file-help">
                {{ achievementAttachmentHelpText }}
              </small>
            </label>

            <div class="form-actions">
              <button type="button" class="action-button secondary" :disabled="isUploadingAchievement" @click="closeAchievementModal">
                Cancelar
              </button>
              <button type="submit" class="action-button achievement-submit-button" :disabled="isUploadingAchievement">
                {{ isUploadingAchievement ? 'Guardando...' : (isEditingAchievement ? 'Guardar cambios' : 'Guardar registro') }}
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
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from 'vuex'
import axios from 'axios'
import { show_alerta } from '../funciones'
import HistorialAnualCard from '../components/HistorialAnualCard.vue'
import { defaultRouteForUser } from '../utils/auth'
import {
  FORMATIVE_ACTIVITY_TYPES,
  isFormativeEvent,
  isServiceEvent,
  normalizeCatalogValue
} from '../constants/activityTypes'

const API_BASE = 'http://localhost:8000/api'

const route = useRoute()
const router = useRouter()
const store = useStore()
const currentYear = new Date().getFullYear()

const user = ref(null)
const hojaDeVida = ref(null)
const hojasAnuales = ref([])
const antecedentes = ref([])
const actividades = ref([])
const registrosHorasFilial = ref([])
const search = ref('')
const isUploadingPhoto = ref(false)
const isUploadingAchievement = ref(false)
const deletingAchievementId = ref(null)
const deletingFilialRecordId = ref(null)
const showAnnualForm = ref(false)
const showAchievementModal = ref(false)
const showFilialRecordModal = ref(false)
const isSavingAnnual = ref(false)
const isSavingObservation = ref(false)
const isSavingFilialRecord = ref(false)
const annualForm = ref(createEmptyAnnualForm(currentYear))
const achievementForm = ref(createEmptyAchievementForm(currentYear))
const filialRecordForm = ref(createEmptyFilialRecordForm(currentYear))
const observationDraft = ref(createObservationDraft())

const achievementTypeOptions = [
  { value: 'TITULO', label: 'Titulo' },
  { value: 'PREMIO', label: 'Premio' }
]

const isEditingAchievement = computed(() => Boolean(achievementForm.value.id_antecedente))
const isEditingFilialRecord = computed(() => Boolean(filialRecordForm.value.id))
const authUser = computed(() => store.getters.authUser)
const canManageHistory = computed(() => store.getters.isAdministratorExperience)

const achievementAttachmentHelpText = computed(() => {
  if (achievementForm.value.archivo) {
    return achievementForm.value.archivo.name
  }

  if (achievementForm.value.archivo_url) {
    return 'Deja este campo vacio si quieres conservar el archivo actual.'
  }

  return 'Selecciona una imagen o un PDF del certificado, reconocimiento o premio.'
})

const achievementFileButtonLabel = computed(() =>
  isEditingAchievement.value ? 'Subir otro archivo' : 'Seleccionar archivo'
)

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

  registrosHorasFilial.value.forEach((registro) => {
    const year = getYearFromDate(registro.fecha)
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
    .filter((actividad) => isServiceEvent(actividad.evento?.tipo))
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

const filteredFormativeActivities = computed(() => {
  const items = actividades.value
    .filter((actividad) => isFormativeEvent(actividad.evento?.tipo))
    .filter((actividad) => FORMATIVE_ACTIVITY_TYPES.includes(normalizeTipo(actividad.tipo)))
    .filter((actividad) => getYearFromDate(actividad.evento?.fecha_inicio) === selectedYear.value)
    .filter((actividad) => actividad.pivot?.asistio !== false)
    .sort((a, b) => (b.evento?.fecha_inicio || '').localeCompare(a.evento?.fecha_inicio || ''))

  if (!normalizedSearch.value) return items

  return items.filter((actividad) =>
    (actividad.tipo || '').toLowerCase().includes(normalizedSearch.value) ||
    (actividad.evento?.nombre || '').toLowerCase().includes(normalizedSearch.value) ||
    (actividad.evento?.descripcion || '').toLowerCase().includes(normalizedSearch.value)
  )
})

const filteredAntecedentes = computed(() => {
  const items = antecedentes.value
    .filter((antecedente) => ['TITULO', 'PREMIO', 'CARGO'].includes(normalizeTipo(antecedente.tipo)))
    .filter((antecedente) => getYearFromAntecedente(antecedente) === selectedYear.value)

  if (!normalizedSearch.value) return items

  return items.filter((antecedente) =>
    (antecedente.tipo || '').toLowerCase().includes(normalizedSearch.value) ||
    (antecedente.nombre || '').toLowerCase().includes(normalizedSearch.value) ||
    (antecedente.descripcion || '').toLowerCase().includes(normalizedSearch.value)
  )
})

const groupedFormacion = computed(() => {
  if (selectedHojaAnual.value) {
    return {
      cursos: buildSectionItemsWithFallback(
        selectedHojaAnual.value.cursos,
        filteredFormativeActivities.value.filter((item) => normalizeTipo(item.tipo) === 'CURSO')
      ),
      talleres: buildSectionItemsWithFallback(
        selectedHojaAnual.value.talleres,
        filteredFormativeActivities.value.filter((item) => normalizeTipo(item.tipo) === 'TALLER')
      ),
      seminarios: buildSectionItemsWithFallback(
        selectedHojaAnual.value.seminarios,
        filteredFormativeActivities.value.filter((item) => normalizeTipo(item.tipo) === 'SEMINARIO')
      )
    }
  }

  return {
    cursos: filteredFormativeActivities.value
      .filter((item) => normalizeTipo(item.tipo) === 'CURSO')
      .map(mapActivityToSectionItem),
    talleres: filteredFormativeActivities.value
      .filter((item) => normalizeTipo(item.tipo) === 'TALLER')
      .map(mapActivityToSectionItem),
    seminarios: filteredFormativeActivities.value
      .filter((item) => normalizeTipo(item.tipo) === 'SEMINARIO')
      .map(mapActivityToSectionItem)
  }
})

const groupedLogros = computed(() =>
  filteredAntecedentes.value.filter((item) => ['TITULO', 'PREMIO'].includes(normalizeTipo(item.tipo)))
)

const manualTitulosPremiosLines = computed(() =>
  selectedHojaAnual.value ? parseMultilineField(selectedHojaAnual.value.titulos_premios) : []
)

const filialRecordsForYear = computed(() =>
  registrosHorasFilial.value
    .filter((record) => getYearFromDate(record.fecha) === selectedYear.value)
    .sort((a, b) => {
      const left = `${b.fecha || ''} ${b.hora_entrada || ''}`
      const right = `${a.fecha || ''} ${a.hora_entrada || ''}`
      return left.localeCompare(right)
    })
)

const filteredFilialRecords = computed(() => {
  if (!normalizedSearch.value) {
    return filialRecordsForYear.value
  }

  return filialRecordsForYear.value.filter((record) => {
    const fields = [
      formatDate(record.fecha),
      formatTime(record.hora_entrada),
      formatTime(record.hora_salida),
      formatHours(record.horas_totales)
    ]

    return fields.some((field) => field.toLowerCase().includes(normalizedSearch.value))
  })
})

const totalFilialHoursForYear = computed(() =>
  filialRecordsForYear.value.reduce((total, record) => total + Number(record.horas_totales || 0), 0)
)

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
  const totalLogros = groupedLogros.value.length
  const totalFormacion = filteredFormativeActivities.value.length
  const totalActividades = filteredActividades.value.length
  const totalFilialHours = totalFilialHoursForYear.value

  if (!totalLogros && !totalFormacion && !totalActividades && totalFilialHours <= 0) {
    return 'Todavia no hay actividades, horas en filial ni antecedentes asociados a este periodo.'
  }

  return `Este periodo registra ${totalActividades} actividad(es) de servicio, ${formatHours(totalFilialHours)} en filial, ${totalFormacion} instancia(s) formativa(s) aprobada(s) y ${totalLogros} logro(s) cargado(s).`
})

const hasObservationDraftChanges = computed(() =>
  observationDraft.value.labor_efectuada !== (selectedHojaAnual.value?.labor_efectuada || '') ||
  observationDraft.value.observaciones_generales !== (selectedHojaAnual.value?.observaciones_generales || '')
)

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
    titulos_premios: ''
  }
}

function createEmptyAchievementForm(year) {
  return {
    id_antecedente: null,
    tipo: 'TITULO',
    nombre: '',
    fecha_inicio: getTodayIsoDate(),
    archivo: null,
    archivo_url: ''
  }
}

function createObservationDraft(annual = null) {
  return {
    labor_efectuada: annual?.labor_efectuada || '',
    observaciones_generales: annual?.observaciones_generales || ''
  }
}

function createEmptyFilialRecordForm(year) {
  return {
    id: null,
    fecha: getSuggestedDateForYear(year),
    hora_entrada: '09:00',
    hora_salida: '13:00'
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

function formatHours(value) {
  const numericValue = Number(value || 0)
  return `${new Intl.NumberFormat('es-CL', {
    minimumFractionDigits: numericValue % 1 === 0 ? 0 : 2,
    maximumFractionDigits: 2
  }).format(numericValue)} h`
}

function formatTime(value) {
  if (!value) return '--:--'
  return String(value).slice(0, 5)
}

function getTodayIsoDate() {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

function getSuggestedDateForYear(year) {
  const today = getTodayIsoDate()
  const selected = Number(year)

  if (!Number.isInteger(selected) || selected === currentYear) {
    return today
  }

  return `${selected}-01-01`
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
  return normalizeCatalogValue(tipo)
}

function getAttachmentReference(item) {
  return String(item?.archivo_url || item?.archivo || '').toLowerCase()
}

function hasImageAttachment(item) {
  return /\.(jpg|jpeg|png|webp)$/i.test(getAttachmentReference(item))
}

function hasPdfAttachment(item) {
  return /\.pdf$/i.test(getAttachmentReference(item))
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

function parseMultilineField(value) {
  return String(value || '')
    .split(/\r?\n/)
    .map((item) => item.trim())
    .filter(Boolean)
}

function buildManualSectionItems(value) {
  return parseMultilineField(value).map((label, index) => ({
    id: `manual-${index}-${label}`,
    label
  }))
}

function hasStoredAnnualSectionValue(value) {
  return value !== null && value !== undefined
}

function buildSectionItemsWithFallback(value, fallbackItems) {
  if (hasStoredAnnualSectionValue(value)) {
    return buildManualSectionItems(value)
  }

  return fallbackItems.map(mapActivityToSectionItem)
}

function mapActivityToSectionItem(item) {
  return {
    id: item.id,
    label: `${item.evento?.nombre || item.tipo}${item.evento?.fecha_inicio ? ` - ${formatDate(item.evento?.fecha_inicio)}` : ''}`
  }
}

function getActivitiesForYear(year) {
  return actividades.value.filter((actividad) => getYearFromDate(actividad.evento?.fecha_inicio) === Number(year))
}

function joinActivitiesByTipo(year, tipo) {
  return getActivitiesForYear(year)
    .filter((item) => isFormativeEvent(item.evento?.tipo))
    .filter((item) => normalizeTipo(item.tipo) === tipo)
    .filter((item) => item.pivot?.asistio !== false)
    .map((item) => `${item.evento?.nombre || item.tipo} (${formatDate(item.evento?.fecha_inicio)})`)
    .filter(Boolean)
    .join('\n')
}

function joinAchievementsByYear(year) {
  return getAntecedentesForYear(year)
    .filter((item) => ['TITULO', 'PREMIO'].includes(normalizeTipo(item.tipo)))
    .map((item) => `${prettyTipo(item.tipo)}: ${item.nombre}`)
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
    cursos: annual ? (annual.cursos ?? joinActivitiesByTipo(targetYear, 'CURSO')) : joinActivitiesByTipo(targetYear, 'CURSO'),
    talleres: annual ? (annual.talleres ?? joinActivitiesByTipo(targetYear, 'TALLER')) : joinActivitiesByTipo(targetYear, 'TALLER'),
    seminarios: annual ? (annual.seminarios ?? joinActivitiesByTipo(targetYear, 'SEMINARIO')) : joinActivitiesByTipo(targetYear, 'SEMINARIO'),
    titulos_premios: annual ? (annual.titulos_premios ?? joinAchievementsByYear(targetYear)) : joinAchievementsByYear(targetYear)
  }
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

function openAchievementModal(achievement = null) {
  if (achievement) {
    achievementForm.value = {
      id_antecedente: achievement.id_antecedente,
      tipo: normalizeTipo(achievement.tipo) || 'TITULO',
      nombre: achievement.nombre || '',
      fecha_inicio: achievement.fecha_inicio ? achievement.fecha_inicio.slice(0, 10) : getTodayIsoDate(),
      archivo: null,
      archivo_url: achievement.archivo_url || ''
    }
  } else {
    achievementForm.value = createEmptyAchievementForm(selectedYear.value)
  }

  showAchievementModal.value = true
}

function closeAnnualForm() {
  showAnnualForm.value = false
  annualForm.value = createEmptyAnnualForm(selectedYear.value)
}

function closeAchievementModal() {
  showAchievementModal.value = false
  achievementForm.value = createEmptyAchievementForm(selectedYear.value)
}

function openFilialRecordModal(record = null) {
  if (record) {
    filialRecordForm.value = {
      id: record.id,
      fecha: record.fecha ? String(record.fecha).slice(0, 10) : getSuggestedDateForYear(selectedYear.value),
      hora_entrada: formatTime(record.hora_entrada),
      hora_salida: formatTime(record.hora_salida)
    }
  } else {
    filialRecordForm.value = createEmptyFilialRecordForm(selectedYear.value)
  }

  showFilialRecordModal.value = true
}

function closeFilialRecordModal() {
  showFilialRecordModal.value = false
  filialRecordForm.value = createEmptyFilialRecordForm(selectedYear.value)
}

function resetObservationDraft() {
  observationDraft.value = createObservationDraft(selectedHojaAnual.value)
}

function goBack() {
  router.push('/inicio')
}

function openPdfExport() {
  const targetUserId = user.value?.id || route.params.id

  if (!targetUserId) {
    show_alerta('No existe un usuario valido para exportar.', 'error')
    return
  }

  const targetRoute = router.resolve({
    name: 'HistorialPdfView',
    params: { id: targetUserId },
    query: { autoprint: 1 }
  })

  const openedWindow = window.open(targetRoute.href, '_blank', 'noopener')

  if (!openedWindow) {
    router.push({
      name: 'HistorialPdfView',
      params: { id: targetUserId },
      query: { autoprint: 1 }
    })
  }
}

function goHome() {
  if (!authUser.value) {
    router.push('/login')
    return
  }

  router.push(defaultRouteForUser(authUser.value, store.getters.accessMode))
}

function getValidationMessage(error, fallbackMessage = 'No se pudo guardar la informacion.') {
  const responseErrors = error?.response?.data?.errors
  if (!responseErrors) {
    return fallbackMessage
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
  registrosHorasFilial.value = [...(res.data?.registros_horas_filial || [])]
    .sort((a, b) => `${b.fecha || ''} ${b.hora_entrada || ''}`.localeCompare(`${a.fecha || ''} ${a.hora_entrada || ''}`))
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
    labor_efectuada: annualForm.value.labor_efectuada.trim() || null,
    observaciones_generales: annualForm.value.observaciones_generales.trim() || null,
    cursos: annualForm.value.cursos.trim(),
    talleres: annualForm.value.talleres.trim(),
    seminarios: annualForm.value.seminarios.trim(),
    titulos_premios: annualForm.value.titulos_premios.trim()
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
    show_alerta(getValidationMessage(error, 'No se pudo guardar la hoja anual.'), 'error')
  } finally {
    isSavingAnnual.value = false
  }
}

async function saveObservationDraft() {
  if (!hojaDeVida.value?.id_libro) {
    show_alerta('No existe una hoja de vida asociada al voluntario.', 'error')
    return
  }

  const payload = {
    hoja_de_vida_id: hojaDeVida.value.id_libro,
    anio: selectedYear.value,
    cargo: selectedHojaAnual.value?.cargo || null,
    lista: selectedHojaAnual.value?.lista || null,
    labor_efectuada: observationDraft.value.labor_efectuada.trim() || null,
    observaciones_generales: observationDraft.value.observaciones_generales.trim() || null
  }

  const request = selectedHojaAnual.value
    ? axios.put(`${API_BASE}/hojas-anuales/${selectedHojaAnual.value.id_hoja}`, payload)
    : axios.post(`${API_BASE}/hojas-anuales`, payload)
  const wasEditing = Boolean(selectedHojaAnual.value)

  isSavingObservation.value = true

  try {
    await request
    await loadUser()
    resetObservationDraft()
    show_alerta(
      wasEditing
        ? 'Labor efectuada y observaciones actualizadas correctamente.'
        : 'Labor efectuada y observaciones guardadas correctamente.',
      'success'
    )
  } catch (error) {
    show_alerta(getValidationMessage(error, 'No se pudo guardar la informacion del periodo.'), 'error')
  } finally {
    isSavingObservation.value = false
  }
}

async function saveAchievement() {
  if (!hojaDeVida.value?.id_libro) {
    show_alerta('No existe una hoja de vida asociada al voluntario.', 'error')
    return
  }

  if (!achievementForm.value.nombre.trim()) {
    show_alerta('Debes indicar el nombre del titulo o premio.', 'warning')
    return
  }

  if (!achievementForm.value.fecha_inicio) {
    show_alerta('Debes indicar la fecha del titulo o premio.', 'warning')
    return
  }

  const formData = new FormData()
  formData.append('hoja_de_vida_id', hojaDeVida.value.id_libro)
  formData.append('tipo', achievementForm.value.tipo)
  formData.append('nombre', achievementForm.value.nombre.trim())
  formData.append('fecha_inicio', achievementForm.value.fecha_inicio)

  if (achievementForm.value.archivo) {
    formData.append('archivo', achievementForm.value.archivo)
  }

  const isEditing = Boolean(achievementForm.value.id_antecedente)

  const request = isEditing
    ? axios.post(`${API_BASE}/antecedentes-voluntarios/${achievementForm.value.id_antecedente}`, (() => {
        formData.append('_method', 'PUT')
        return formData
      })(), {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
    : axios.post(`${API_BASE}/antecedentes-voluntarios`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })

  isUploadingAchievement.value = true

  try {
    await request

    closeAchievementModal()
    await loadUser()
    show_alerta(
      isEditing
        ? 'Titulo o premio actualizado correctamente.'
        : 'Titulo o premio guardado correctamente.',
      'success'
    )
  } catch (error) {
    show_alerta(getValidationMessage(error, 'No se pudo guardar el titulo o premio.'), 'error')
  } finally {
    isUploadingAchievement.value = false
  }
}

async function saveFilialRecord() {
  if (!user.value?.id) {
    show_alerta('No existe un voluntario valido para registrar horas en filial.', 'error')
    return
  }

  if (!filialRecordForm.value.fecha || !filialRecordForm.value.hora_entrada || !filialRecordForm.value.hora_salida) {
    show_alerta('Debes completar la fecha, la hora de entrada y la hora de salida.', 'warning')
    return
  }

  const payload = {
    user_id: user.value.id,
    fecha: filialRecordForm.value.fecha,
    hora_entrada: filialRecordForm.value.hora_entrada,
    hora_salida: filialRecordForm.value.hora_salida
  }

  const isEditing = Boolean(filialRecordForm.value.id)
  const request = isEditing
    ? axios.put(`${API_BASE}/registros-horas-filial/${filialRecordForm.value.id}`, payload)
    : axios.post(`${API_BASE}/registros-horas-filial`, payload)

  isSavingFilialRecord.value = true

  try {
    await request
    closeFilialRecordModal()
    await loadUser()
    show_alerta(
      isEditing
        ? 'Horas en filial actualizadas correctamente.'
        : 'Horas en filial registradas correctamente.',
      'success'
    )
  } catch (error) {
    show_alerta(getValidationMessage(error, 'No se pudieron guardar las horas en filial.'), 'error')
  } finally {
    isSavingFilialRecord.value = false
  }
}

async function deleteFilialRecord(record) {
  deletingFilialRecordId.value = record.id

  try {
    await axios.delete(`${API_BASE}/registros-horas-filial/${record.id}`)
    await loadUser()
    show_alerta('Registro de horas en filial eliminado correctamente.', 'success')
  } catch (error) {
    show_alerta(getValidationMessage(error, 'No se pudo eliminar el registro de horas en filial.'), 'error')
  } finally {
    deletingFilialRecordId.value = null
  }
}

async function deleteAchievement(antecedente) {
  deletingAchievementId.value = antecedente.id_antecedente

  try {
    await axios.delete(`${API_BASE}/antecedentes-voluntarios/${antecedente.id_antecedente}`)
    await loadUser()
    show_alerta('Titulo o premio eliminado correctamente.', 'success')
  } catch {
    show_alerta('No se pudo eliminar el titulo o premio.', 'error')
  } finally {
    deletingAchievementId.value = null
  }
}

function onAchievementFileSelected(event) {
  const file = event.target.files?.[0] || null
  event.target.value = ''
  achievementForm.value = {
    ...achievementForm.value,
    archivo: file
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
  annualForm.value = buildAnnualForm(selectedYear.value, selectedHojaAnual.value)
  achievementForm.value = createEmptyAchievementForm(selectedYear.value)
  resetObservationDraft()
})

watch(selectedYear, (year) => {
  if (!showAnnualForm.value) {
    annualForm.value = buildAnnualForm(year, selectedHojaAnual.value)
  }

  if (!showAchievementModal.value && !isUploadingAchievement.value) {
    achievementForm.value = createEmptyAchievementForm(year)
  }

  if (!showFilialRecordModal.value && !isSavingFilialRecord.value) {
    filialRecordForm.value = createEmptyFilialRecordForm(year)
  }

  if (!isSavingObservation.value) {
    resetObservationDraft()
  }
})
</script>

<style scoped>
.historial-page {
  --historial-fluid-gap: clamp(1rem, 1.35vw, 1.6rem);
  --historial-fluid-panel-padding: clamp(1rem, 1.45vw, 1.55rem);
  --historial-fluid-body: clamp(0.98rem, 0.3vw + 0.92rem, 1.12rem);
  --historial-fluid-label: clamp(0.76rem, 0.18vw + 0.73rem, 0.9rem);
  --historial-fluid-title: clamp(1.2rem, 0.95vw + 0.98rem, 1.75rem);
  padding: 1.5rem;
  background:
    radial-gradient(circle at top left, rgba(255, 49, 61, 0.1), transparent 24%),
    linear-gradient(180deg, #f2efe9 0%, #f8f5ef 100%);
  min-height: 100vh;
  padding: clamp(1rem, 1.6vw, 1.75rem) clamp(1rem, 5vw, 10vw) clamp(1.5rem, 2vw, 2rem);
}

.historial-shell {
  width: 100%;
  max-width: none;
  margin: 0 auto;
  font-size: var(--historial-fluid-body);
}

.hero-card {
  display: grid;
  grid-template-columns: minmax(240px, 17vw) minmax(0, 1fr);
  gap: var(--historial-fluid-gap);
  align-items: stretch;
  margin-bottom: clamp(1rem, 1.4vw, 1.4rem);
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
  min-height: clamp(236px, 22vw, 320px);
  flex-direction: column;
  justify-content: flex-start;
  background: #ffffff;
  border: 1px solid rgba(15, 47, 95, 0.08);
  color: #163a69;
  box-shadow: 0 16px 30px rgba(15, 47, 95, 0.08);
  padding: clamp(0.65rem, 0.9vw, 0.95rem) clamp(0.65rem, 0.9vw, 0.95rem) clamp(0.55rem, 0.8vw, 0.8rem);
}

.hero-photo-frame {
  width: 100%;
  flex: 1;
  min-height: clamp(168px, 15vw, 248px);
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
  font-size: clamp(0.95rem, 0.35vw + 0.88rem, 1.12rem);
}

.hero-photo-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-photo-text {
  text-align: center;
  color: #7a8799;
  font-size: clamp(0.78rem, 0.2vw + 0.74rem, 0.9rem);
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
  font-size: clamp(0.82rem, 0.22vw + 0.78rem, 0.96rem);
  font-weight: 700;
  padding: clamp(0.5rem, 0.65vw, 0.7rem) clamp(0.75rem, 1vw, 1rem);
  cursor: pointer;
}

.photo-upload-trigger.disabled {
  opacity: 0.6;
  cursor: wait;
}

.hero-year-card {
  grid-area: year;
  min-height: clamp(58px, 5.4vw, 90px);
  background: #ff313d;
  color: #fff;
  font-size: clamp(2rem, 1.5vw + 1.55rem, 3rem);
  font-weight: 800;
  box-shadow: 0 16px 28px rgba(255, 49, 61, 0.18);
}

.hero-main {
  background: rgba(255, 255, 255, 1);
  border: 1px solid rgba(15, 47, 95, 0.08);
  border-radius: 22px;
  padding: clamp(1.45rem, 1.9vw, 2.4rem) clamp(1.45rem, 1.9vw, 2.4rem) clamp(1rem, 1.25vw, 1.4rem);
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

.hero-actions {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.eyebrow {
  margin: 0 0 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: clamp(0.8rem, 0.2vw + 0.76rem, 0.94rem);
  color: #73839a;
}

.hero-header h1 {
  margin: 0;
  color: #0f2f5f;
  font-size: clamp(2.2rem, 2.2vw + 1.45rem, 4rem);
  font-weight: 800;
  line-height: 1;
}

.hero-brand-badge {
  min-width: clamp(110px, 9vw, 148px);
  min-height: clamp(88px, 7vw, 118px);
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
  font-size: clamp(4rem, 2.5vw + 3.2rem, 6rem);
  line-height: 0.8;
  font-weight: 800;
}

.hero-brand-text {
  color: #163a69;
  font-size: clamp(1rem, 0.35vw + 0.92rem, 1.18rem);
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
  font-size: clamp(0.94rem, 0.35vw + 0.88rem, 1.08rem);
}

.status-chip.is-active {
  background: #dff3e5;
  color: #1f7a3d;
}

.status-chip.is-inactive {
  background: #e3e6ea;
  color: #5e6670;
}

.summary-grid {
  display: grid;
  grid-template-columns:
    minmax(220px, 1.7fr)
    minmax(190px, 1.25fr)
    minmax(175px, 1fr)
    minmax(175px, 1fr);
  grid-template-areas: "cargo lista asistencia ingreso";
  gap: clamp(0.7rem, 0.85vw, 1rem);
  align-items: stretch;
  margin-top: 0.7rem;
}

.summary-pill {
  background: #0f2f5f;
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: clamp(52px, 4vw, 70px);
  padding: clamp(0.55rem, 0.75vw, 0.8rem) clamp(0.85rem, 1vw, 1.1rem);
  overflow: hidden;
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

.summary-pill.outline {
  background: #fff;
  color: #0f2f5f;
  border: 2px solid #0f2f5f;
}

.summary-pill.compact {
  align-items: center;
  text-align: center;
}

.summary-label {
  display: block;
  margin-bottom: 0.08rem;
  font-size: clamp(0.68rem, 0.16vw + 0.65rem, 0.82rem);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  opacity: 0.8;
  line-height: 1.1;
}

.summary-label.dark {
  color: #5f6f82;
  opacity: 1;
}

.summary-pill strong {
  font-size: clamp(0.95rem, 0.4vw + 0.87rem, 1.12rem);
  line-height: 1.1;
  max-width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.toolbar {
  margin: clamp(1rem, 1.2vw, 1.3rem) 0 clamp(1.1rem, 1.4vw, 1.45rem);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--historial-fluid-gap);
  flex-wrap: wrap;
}

.search-box {
  flex: 1 1 min(720px, 100%);
  max-width: none;
  border-radius: 999px;
  border: 1px solid #dfe5ec;
  padding: clamp(0.9rem, 0.9vw, 1.05rem) clamp(1rem, 1.2vw, 1.25rem);
  font-size: var(--historial-fluid-body);
  box-shadow: 0 8px 24px rgba(15, 47, 95, 0.05);
}

.toolbar-status {
  margin-left: auto;
}

.action-button,
.text-button {
  border: none;
  border-radius: 999px;
  font-size: clamp(0.92rem, 0.24vw + 0.87rem, 1.02rem);
  font-weight: 700;
  transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
}

.session-action {
  border: none;
  border-radius: 999px;
  background: #eef4fb;
  color: #0f2f5f;
  padding: 0.72rem 1rem;
  font-size: 0.82rem;
  font-weight: 700;
}

.session-action--danger {
  background: #fee4e2;
  color: #b42318;
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

.achievement-form {
  display: grid;
  gap: 0.85rem;
}

.achievement-modal {
  width: min(640px, 100%);
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
  font-size: clamp(0.82rem, 0.18vw + 0.79rem, 0.94rem);
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
  font-size: var(--historial-fluid-body);
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

.form-note {
  border-radius: 18px;
  border: 1px dashed #d8d0c6;
  background: #faf7f3;
  color: #7a6f5d;
  padding: 1rem;
  line-height: 1.5;
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
  grid-template-columns: minmax(0, 2.15fr) minmax(320px, 0.92fr);
  gap: var(--historial-fluid-gap);
  align-items: start;
}

.main-column,
.sidebar-column {
  display: grid;
  gap: var(--historial-fluid-gap);
}

.panel {
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(15, 47, 95, 0.08);
  border-radius: 28px;
  padding: var(--historial-fluid-panel-padding);
  box-shadow: 0 18px 40px rgba(15, 47, 95, 0.07);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  gap: var(--historial-fluid-gap);
  align-items: center;
  margin-bottom: 1rem;
}

.panel-header.compact {
  margin-bottom: 0.8rem;
}

.panel-kicker {
  margin: 0 0 0.2rem;
  font-size: var(--historial-fluid-label);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #7a8799;
}

.panel-header h2 {
  margin: 0;
  color: #0f2f5f;
  font-size: var(--historial-fluid-title);
  font-weight: 800;
}

.counter-chip {
  background: #eef4fb;
  color: #0f2f5f;
  font-size: clamp(0.88rem, 0.2vw + 0.84rem, 1rem);
  font-weight: 700;
}

.award-upload-button {
  padding: 0.72rem 1rem;
  box-shadow: 0 12px 24px rgba(255, 49, 61, 0.14);
}

.activity-list {
  display: grid;
  gap: 0.85rem;
}

.activity-item {
  display: grid;
  grid-template-columns: minmax(116px, 10.5vw) 1fr;
  gap: clamp(1rem, 1vw, 1.2rem);
  padding: clamp(1rem, 1vw, 1.2rem);
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
  font-size: clamp(0.94rem, 0.28vw + 0.9rem, 1.06rem);
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
  font-size: clamp(1.05rem, 0.45vw + 0.96rem, 1.3rem);
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
  font-size: clamp(0.8rem, 0.2vw + 0.76rem, 0.92rem);
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
  font-size: var(--historial-fluid-body);
  line-height: 1.55;
}

.detail-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.95fr);
  gap: var(--historial-fluid-gap);
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
  padding: clamp(1rem, 1vw, 1.2rem);
}

.bullet-list {
  margin: 0;
  padding-left: 1.1rem;
  color: #163a69;
  font-size: var(--historial-fluid-body);
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

.award-image {
  width: 100%;
  border-radius: 16px;
  margin-top: 0.85rem;
  border: 1px solid #edf0f4;
  object-fit: cover;
}

.award-file-chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  margin-top: 0.85rem;
  padding: 1rem;
  border-radius: 16px;
  border: 1px solid #dbe4f0;
  background: #eef4fb;
  color: #0f2f5f;
  font-weight: 700;
}

.award-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 0.6rem;
}

.award-link,
.award-delete {
  font-size: clamp(0.84rem, 0.16vw + 0.81rem, 0.95rem);
}

.award-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.45rem;
}

.award-delete {
  color: #b42318;
}

.panel-header-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.filial-record-list {
  display: grid;
  gap: 0.85rem;
}

.filial-record-card {
  border-radius: 22px;
  border: 1px solid #d8e0ea;
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  padding: 1rem 1.05rem 0.9rem;
}

.filial-record-card__top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.filial-record-card__top p {
  margin: 0.25rem 0 0;
  color: #6b7b91;
}

.filial-record-card__footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.75rem;
}

.file-trigger {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 44px;
  padding: 0.7rem 1rem;
  border-radius: 14px;
  background: #ff313d;
  color: #fff;
  font-size: clamp(0.92rem, 0.2vw + 0.88rem, 1.02rem);
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 12px 24px rgba(255, 49, 61, 0.18);
  transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.file-trigger:hover {
  background: #e02834;
  transform: translateY(-1px);
}

.achievement-file-help {
  display: block;
  margin-top: 0.7rem;
  font-size: var(--historial-fluid-body);
  line-height: 1.45;
}

.achievement-submit-button {
  background: #0f2f5f;
  box-shadow: 0 14px 28px rgba(15, 47, 95, 0.18);
}

.achievement-submit-button:not(:disabled):hover {
  background: #163a69;
}

.filial-modal {
  width: min(580px, 100%);
}

.filial-record-form {
  display: grid;
  gap: 1rem;
}

.filial-record-form__times {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.observation-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.observation-box--summary {
  grid-column: 1 / -1;
}

.observation-label {
  display: block;
  margin-bottom: 0.45rem;
  color: #73839a;
  text-transform: uppercase;
  font-size: var(--historial-fluid-label);
  letter-spacing: 0.06em;
}

.observation-editor {
  width: 100%;
  min-height: 148px;
  border: 1px solid #d8e0ea;
  border-radius: 18px;
  background: #fff;
  color: #163a69;
  padding: 0.95rem 1rem;
  font-size: var(--historial-fluid-body);
  resize: vertical;
  line-height: 1.55;
  outline: none;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
}

.observation-editor:focus {
  border-color: #0f2f5f;
  box-shadow: 0 0 0 3px rgba(15, 47, 95, 0.08);
}

.observation-static {
  min-height: 148px;
  margin: 0;
  border: 1px solid #d8e0ea;
  border-radius: 18px;
  background: #f9fbfd;
  color: #163a69;
  padding: 0.95rem 1rem;
  font-size: var(--historial-fluid-body);
  line-height: 1.6;
  white-space: pre-wrap;
}

.observation-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1rem;
  flex-wrap: wrap;
}

.observation-save-button {
  background: #0f2f5f;
  box-shadow: 0 14px 28px rgba(15, 47, 95, 0.18);
}

.observation-save-button:not(:disabled):hover {
  background: #163a69;
}

.empty-state {
  border-radius: 22px;
  background: #faf7f3;
  border: 1px dashed #d8d0c6;
  color: #7a6f5d;
  padding: 1rem;
}

.empty-state.small {
  font-size: clamp(0.95rem, 0.18vw + 0.92rem, 1.02rem);
}

.sidebar-panel {
  position: sticky;
  top: clamp(1rem, 1.2vw, 1.35rem);
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
  .form-grid,
  .filial-record-form__times {
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

  .summary-cargo,
  .summary-lista,
  .summary-asistencia,
  .summary-ingreso {
    grid-area: auto;
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
