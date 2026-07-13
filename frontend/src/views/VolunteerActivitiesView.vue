<template>
  <div class="d-flex">
    <SidebarMenu />

    <div class="content-wrapper">
      <div class="content-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <h3 class="m-0"><i class="fa-solid fa-list-check me-2"></i>Mis actividades</h3>
          <span class="role-chip">Inscripciones activas</span>
        </div>
      </div>

      <div class="content">
        <div class="card shadow volunteer-card">
          <div class="card-body">
            <div class="section-copy">
              <h2>Actividades disponibles para participar</h2>
              <p>
                Aquí puedes revisar las actividades vigentes del sistema e inscribirte en las que correspondan.
              </p>
            </div>

            <div v-if="isLoading" class="empty-state">
              Cargando actividades activas...
            </div>

            <div v-else-if="activeActivities.length === 0" class="empty-state">
              No hay actividades activas disponibles en este momento.
            </div>

            <div v-else class="activity-grid">
              <article v-for="actividad in activeActivities" :key="actividad.id" class="activity-card">
                <div class="activity-card__top">
                  <span class="activity-badge">{{ actividad.tipo || 'Sin tipo' }}</span>
                  <span class="activity-date">{{ formatDateRange(actividad.fecha_inicio, actividad.fecha_termino) }}</span>
                </div>

                <h3>{{ actividad.nombre || 'Actividad sin nombre' }}</h3>
                <p class="activity-event">{{ actividad.filial?.nombre || 'Sin filial' }}</p>
                <p class="activity-description">
                  {{ actividad.objetivo || 'Sin objetivo registrado.' }}
                </p>

                <div class="activity-meta">
                  <span>{{ formatHours(actividad.horas_totales) }}</span>
                  <span>{{ actividad.voluntarios?.length || 0 }} inscrito(s)</span>
                </div>

                <div class="activity-actions">
                  <button
                    type="button"
                    class="btn"
                    :class="isEnrolled(actividad) ? 'btn-outline-danger' : 'btn-danger'"
                    :disabled="loadingActivityId === actividad.id"
                    @click="toggleEnrollment(actividad)"
                  >
                    {{ enrollmentButtonLabel(actividad) }}
                  </button>

                  <template v-if="isEnrolled(actividad)">
                    <button
                      type="button"
                      class="btn btn-outline-primary"
                      :disabled="galleryLoadingActivityId === actividad.id"
                      @click="toggleGalleryPanel(actividad)"
                    >
                      {{ galleryButtonLabel(actividad) }}
                    </button>

                    <button
                      type="button"
                      class="btn btn-outline-secondary"
                      :disabled="boletasLoadingActivityId === actividad.id"
                      @click="toggleBoletasPanel(actividad)"
                    >
                      {{ boletasButtonLabel(actividad) }}
                    </button>
                  </template>
                </div>

                <section v-if="galleryActivityId === actividad.id" class="asset-panel">
                  <div class="asset-panel__header">
                    <div>
                      <h4>Galería de la actividad</h4>
                      <p>Las imágenes quedan visibles para los voluntarios inscritos en esta actividad.</p>
                    </div>
                  </div>

                  <div class="asset-form">
                    <div class="asset-form__grid">
                      <input
                        v-model.trim="galleryForm.titulo"
                        type="text"
                        class="form-control"
                        placeholder="Título de la imagen"
                      >
                      <input
                        v-model="galleryForm.fecha"
                        type="date"
                        class="form-control"
                      >
                    </div>

                    <textarea
                      v-model.trim="galleryForm.descripcion"
                      class="form-control"
                      rows="2"
                      placeholder="Descripción breve"
                    ></textarea>

                    <input
                      type="file"
                      class="form-control"
                      accept=".jpg,.jpeg,.png,.webp"
                      @change="onGalleryFileSelected"
                    >

                    <div class="asset-form__actions">
                      <button
                        type="button"
                        class="btn btn-danger btn-sm"
                        :disabled="gallerySubmitting || !galleryForm.file"
                        @click="uploadGalleryImage(actividad)"
                      >
                        {{ gallerySubmitting ? 'Subiendo...' : 'Subir imagen' }}
                      </button>
                    </div>
                  </div>

                  <div v-if="galleryLoadingActivityId === actividad.id" class="asset-empty">
                    Cargando galería...
                  </div>

                  <div v-else-if="galleryItems.length" class="gallery-grid">
                    <article v-for="item in galleryItems" :key="item.id" class="gallery-card">
                      <img :src="item.imagen_url" :alt="item.titulo || 'Imagen de actividad'" class="gallery-card__image">
                      <div class="gallery-card__body">
                        <strong>{{ item.titulo || 'Imagen sin título' }}</strong>
                        <small>{{ item.subido_por?.name || item.subido_por?.username || 'Voluntario' }}</small>
                        <small>{{ formatDate(item.fecha) }}</small>
                        <p>{{ item.descripcion || 'Sin descripción.' }}</p>
                      </div>
                    </article>
                  </div>

                  <div v-else class="asset-empty">
                    Todavía no hay imágenes registradas para esta actividad.
                  </div>
                </section>

                <section v-if="boletasActivityId === actividad.id" class="asset-panel">
                  <div class="asset-panel__header">
                    <div>
                      <h4>Boletas de viático</h4>
                      <p>Sube aquí tus respaldos para solicitar reembolso de viático en esta actividad.</p>
                    </div>
                  </div>

                  <div class="asset-form">
                    <div class="asset-form__grid">
                      <input
                        v-model.trim="boletaForm.detalle_compra"
                        type="text"
                        class="form-control"
                        placeholder="Detalle de compra"
                      >
                      <input
                        v-model.number="boletaForm.monto"
                        type="number"
                        min="0"
                        step="0.01"
                        class="form-control"
                        placeholder="Monto"
                      >
                    </div>

                    <input
                      v-model="boletaForm.fecha_compra"
                      type="date"
                      class="form-control"
                    >

                    <input
                      type="file"
                      class="form-control"
                      accept=".jpg,.jpeg,.png,.webp,.pdf"
                      @change="onBoletaFileSelected"
                    >

                    <div class="asset-form__actions">
                      <button
                        type="button"
                        class="btn btn-danger btn-sm"
                        :disabled="boletaSubmitting || !isBoletaFormValid"
                        @click="uploadBoleta(actividad)"
                      >
                        {{ boletaSubmitting ? 'Subiendo...' : 'Registrar boleta' }}
                      </button>
                    </div>
                  </div>

                  <div v-if="boletasLoadingActivityId === actividad.id" class="asset-empty">
                    Cargando boletas...
                  </div>

                  <div v-else-if="boletaItems.length" class="receipt-list">
                    <article v-for="item in boletaItems" :key="item.id" class="receipt-card">
                      <div class="receipt-card__top">
                        <strong>{{ item.detalle_compra }}</strong>
                        <span class="receipt-state">{{ boletaStatusLabel(item.estado) }}</span>
                      </div>
                      <p>{{ formatCurrency(item.monto) }} · {{ formatDate(item.fecha_compra) }}</p>
                      <a :href="item.archivo_url" target="_blank" rel="noopener" class="receipt-link">Ver respaldo</a>
                    </article>
                  </div>

                  <div v-else class="asset-empty">
                    Aun no has subido boletas para esta actividad.
                  </div>
                </section>
              </article>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import SidebarMenu from '../components/SidebarMenu.vue'
import { API_BASE } from '../config/api'
import { show_alerta } from '../funciones'
import { useStore } from 'vuex'
import { optimizeImage } from '../utils/imageOptimization'

const store = useStore()
const currentUser = computed(() => store.getters.authUser)
const currentVolunteerId = computed(() => currentUser.value?.voluntario?.id || null)
const activities = ref([])
const isLoading = ref(false)
const loadingActivityId = ref(null)
const galleryActivityId = ref(null)
const galleryLoadingActivityId = ref(null)
const gallerySubmitting = ref(false)
const galleryItems = ref([])
const boletasActivityId = ref(null)
const boletasLoadingActivityId = ref(null)
const boletaSubmitting = ref(false)
const boletaItems = ref([])
const galleryForm = ref(createEmptyGalleryForm())
const boletaForm = ref(createEmptyBoletaForm())

const todayIso = new Date().toISOString().slice(0, 10)

const activeActivities = computed(() =>
  activities.value
    .filter((actividad) => {
      const start = (actividad?.fecha_inicio || '').slice(0, 10)
      const end = (actividad?.fecha_termino || '').slice(0, 10)
      return (end || start) >= todayIso
    })
    .sort((left, right) => (left.fecha_inicio || '').localeCompare(right.fecha_inicio || ''))
)

function isEnrolled(actividad) {
  return (actividad.voluntarios || []).some((volunteer) => Number(volunteer.id) === Number(currentVolunteerId.value))
}

function enrollmentButtonLabel(actividad) {
  if (loadingActivityId.value === actividad.id) {
    return isEnrolled(actividad) ? 'Quitando...' : 'Inscribiendo...'
  }

  return isEnrolled(actividad) ? 'Quitar inscripción' : 'Inscribirme'
}

function formatDate(dateString) {
  if (!dateString) return '-'
  return String(dateString).slice(0, 10).split('-').reverse().join('-')
}

function formatDateRange(start, end) {
  if (!start && !end) return '-'
  if (start && end) return `${formatDate(start)} - ${formatDate(end)}`
  return formatDate(start || end)
}

function formatHours(value) {
  const numericValue = Number(value || 0)
  return `${numericValue % 1 === 0 ? numericValue.toFixed(0) : numericValue.toFixed(2)} h`
}

function boletaStatusLabel(value) {
  const normalized = String(value || '').trim().toLowerCase()

  if (['pagado', 'pagada'].includes(normalized)) {
    return 'Pagado'
  }

  if (['aprobado', 'aprobada'].includes(normalized)) {
    return 'Aprobado'
  }

  return 'Solicitado'
}

function formatCurrency(value) {
  const numericValue = Number(value || 0)
  return numericValue.toLocaleString('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0
  })
}

function createEmptyGalleryForm() {
  return {
    titulo: '',
    descripcion: '',
    fecha: '',
    file: null
  }
}

function createEmptyBoletaForm() {
  return {
    detalle_compra: '',
    monto: '',
    fecha_compra: '',
    file: null
  }
}

const isBoletaFormValid = computed(() =>
  Boolean(boletaForm.value.file && boletaForm.value.detalle_compra.trim() && Number(boletaForm.value.monto) > 0)
)

async function loadActivities() {
  isLoading.value = true

  try {
    const firstPage = await axios.get(`${API_BASE}/actividad`, { params: { page: 1 } })
    const totalPages = Number(firstPage.data?.last_page || 1)
    const pages = [firstPage.data]

    if (totalPages > 1) {
      const responses = await Promise.all(
        Array.from({ length: totalPages - 1 }, (_, index) =>
          axios.get(`${API_BASE}/actividad`, { params: { page: index + 2 } })
        )
      )

      pages.push(...responses.map((response) => response.data))
    }

    activities.value = pages.flatMap((page) => page.data || [])
  } catch (error) {
    activities.value = []
    show_alerta('No se pudieron cargar las actividades activas.', 'error')
  } finally {
    isLoading.value = false
  }
}

function galleryButtonLabel(actividad) {
  if (galleryLoadingActivityId.value === actividad.id) {
    return 'Cargando galería...'
  }

  return galleryActivityId.value === actividad.id ? 'Ocultar galería' : 'Galería'
}

function boletasButtonLabel(actividad) {
  if (boletasLoadingActivityId.value === actividad.id) {
    return 'Cargando boletas...'
  }

  return boletasActivityId.value === actividad.id ? 'Ocultar boletas' : 'Boletas'
}

function onGalleryFileSelected(event) {
  galleryForm.value.file = event.target.files?.[0] || null
}

function onBoletaFileSelected(event) {
  boletaForm.value.file = event.target.files?.[0] || null
}

async function loadGallery(actividadId) {
  galleryLoadingActivityId.value = actividadId

  try {
    const response = await axios.get(`${API_BASE}/actividad/${actividadId}/galeria`)
    galleryItems.value = Array.isArray(response.data) ? response.data : []
  } catch (error) {
    galleryItems.value = []
    show_alerta('No se pudo cargar la galería de la actividad.', 'error')
  } finally {
    galleryLoadingActivityId.value = null
  }
}

async function loadBoletas(actividadId) {
  boletasLoadingActivityId.value = actividadId

  try {
    const response = await axios.get(`${API_BASE}/actividad/${actividadId}/boletas`, {
      params: { voluntario_id: currentVolunteerId.value }
    })
    boletaItems.value = Array.isArray(response.data) ? response.data : []
  } catch (error) {
    boletaItems.value = []
    show_alerta('No se pudieron cargar tus boletas.', 'error')
  } finally {
    boletasLoadingActivityId.value = null
  }
}

async function toggleGalleryPanel(actividad) {
  if (galleryActivityId.value === actividad.id) {
    galleryActivityId.value = null
    galleryItems.value = []
    galleryForm.value = createEmptyGalleryForm()
    return
  }

  galleryActivityId.value = actividad.id
  boletasActivityId.value = boletasActivityId.value === actividad.id ? null : boletasActivityId.value
  galleryForm.value = createEmptyGalleryForm()
  await loadGallery(actividad.id)
}

async function toggleBoletasPanel(actividad) {
  if (boletasActivityId.value === actividad.id) {
    boletasActivityId.value = null
    boletaItems.value = []
    boletaForm.value = createEmptyBoletaForm()
    return
  }

  boletasActivityId.value = actividad.id
  galleryActivityId.value = galleryActivityId.value === actividad.id ? null : galleryActivityId.value
  boletaForm.value = createEmptyBoletaForm()
  await loadBoletas(actividad.id)
}

async function uploadGalleryImage(actividad) {
  if (!galleryForm.value.file) {
    show_alerta('Selecciona una imagen para subir.', 'warning')
    return
  }

  gallerySubmitting.value = true

  try {
    const optimizedFile = await optimizeImage(galleryForm.value.file)
    const formData = new FormData()
    formData.append('archivo', optimizedFile)
    formData.append('subido_por', currentUser.value?.id || '')

    if (galleryForm.value.titulo.trim()) {
      formData.append('titulo', galleryForm.value.titulo.trim())
    }

    if (galleryForm.value.descripcion.trim()) {
      formData.append('descripcion', galleryForm.value.descripcion.trim())
    }

    if (galleryForm.value.fecha) {
      formData.append('fecha', galleryForm.value.fecha)
    }

    await axios.post(`${API_BASE}/actividad/${actividad.id}/galeria`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    galleryForm.value = createEmptyGalleryForm()
    await loadGallery(actividad.id)
    show_alerta('Imagen subida correctamente.', 'success')
  } catch (error) {
    show_alerta('No se pudo subir la imagen de la actividad.', 'error')
  } finally {
    gallerySubmitting.value = false
  }
}

async function uploadBoleta(actividad) {
  if (!isBoletaFormValid.value) {
    show_alerta('Completa detalle, monto y archivo de la boleta.', 'warning')
    return
  }

  boletaSubmitting.value = true

  try {
    const formData = new FormData()
    formData.append('voluntario_id', String(currentVolunteerId.value))
    formData.append('archivo', boletaForm.value.file)
    formData.append('detalle_compra', boletaForm.value.detalle_compra.trim())
    formData.append('monto', String(boletaForm.value.monto))

    if (boletaForm.value.fecha_compra) {
      formData.append('fecha_compra', boletaForm.value.fecha_compra)
    }

    await axios.post(`${API_BASE}/actividad/${actividad.id}/boletas`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    boletaForm.value = createEmptyBoletaForm()
    await loadBoletas(actividad.id)
    show_alerta('Boleta registrada correctamente.', 'success')
  } catch (error) {
    const message = error.response?.data?.message || 'No se pudo registrar la boleta.'
    show_alerta(message, 'error')
  } finally {
    boletaSubmitting.value = false
  }
}

async function toggleEnrollment(actividad) {
  if (!currentVolunteerId.value) {
    show_alerta('No existe un voluntario válido para inscribirse.', 'error')
    return
  }

  loadingActivityId.value = actividad.id

  try {
    if (isEnrolled(actividad)) {
      await axios.delete(`${API_BASE}/actividad/${actividad.id}/voluntarios`, {
        data: { voluntario_id: currentVolunteerId.value }
      })
      show_alerta('Inscripción retirada correctamente.', 'success')
    } else {
      await axios.post(`${API_BASE}/actividad/${actividad.id}/voluntarios`, {
        voluntario_id: currentVolunteerId.value,
        registrado_por: currentUser.value?.id || null
      })
      show_alerta('Inscripción realizada correctamente.', 'success')
    }

    await loadActivities()
  } catch (error) {
    show_alerta('No se pudo actualizar la inscripción en la actividad.', 'error')
  } finally {
    loadingActivityId.value = null
  }
}

onMounted(async () => {
  await loadActivities()
})
</script>

<style scoped>
.content-wrapper {
  flex: 1;
  background-color: #f5f7fa;
  min-height: 100vh;
}

.content-header {
  padding: 1rem 1.5rem;
  background-color: #fff;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 1.5rem;
}

.content {
  padding: 0 1.5rem 1.5rem;
}

.volunteer-card {
  border: none;
  border-radius: 8px;
}

.section-copy {
  margin-bottom: 1.5rem;
}

.section-copy h2 {
  font-size: 1.65rem;
  font-weight: 700;
  color: #243447;
  margin-bottom: 0.5rem;
}

.section-copy p {
  color: #5c6b7c;
  margin: 0;
}

.role-chip {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  background: #eef4fb;
  color: #0f2f5f;
  font-weight: 700;
  padding: 0.5rem 0.9rem;
}

.empty-state {
  border-radius: 18px;
  border: 1px dashed #d6dde7;
  background: #fafbfd;
  padding: 1.2rem;
  color: #65758a;
}

.activity-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.activity-card {
  border: 1px solid #e1e6ef;
  border-radius: 18px;
  padding: 1rem;
  background: #fff;
  display: grid;
  gap: 0.85rem;
}

.activity-card__top {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
}

.activity-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.35rem 0.7rem;
  border-radius: 999px;
  background: #e01e1e;
  color: #fff;
  font-size: 0.8rem;
  font-weight: 700;
}

.activity-date {
  color: #6a7b90;
  font-size: 0.88rem;
  font-weight: 600;
}

.activity-card h3 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: #233448;
}

.activity-event {
  margin: 0;
  color: #0f2f5f;
  font-weight: 600;
}

.activity-description {
  margin: 0;
  color: #5c6b7c;
  line-height: 1.55;
}

.activity-meta {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  color: #6a7b90;
  font-size: 0.9rem;
  font-weight: 600;
  flex-wrap: wrap;
}

.activity-actions {
  display: flex;
  gap: 0.6rem;
  flex-wrap: wrap;
}

.asset-panel {
  border-top: 1px solid #e5ebf2;
  padding-top: 1rem;
  display: grid;
  gap: 0.9rem;
}

.asset-panel__header h4 {
  margin: 0 0 0.2rem;
  font-size: 1rem;
  font-weight: 700;
  color: #1f3554;
}

.asset-panel__header p {
  margin: 0;
  color: #627488;
  font-size: 0.9rem;
}

.asset-form {
  display: grid;
  gap: 0.75rem;
  padding: 0.9rem;
  border-radius: 14px;
  background: #f8fafc;
  border: 1px solid #e5ebf2;
}

.asset-form__grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
}

.asset-form__actions {
  display: flex;
  justify-content: flex-end;
}

.asset-empty {
  border-radius: 14px;
  border: 1px dashed #d7e0ea;
  padding: 0.9rem;
  color: #66788c;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 0.85rem;
}

.gallery-card {
  border: 1px solid #e1e6ef;
  border-radius: 16px;
  overflow: hidden;
  background: #fff;
}

.gallery-card__image {
  width: 100%;
  height: 180px;
  object-fit: cover;
  display: block;
}

.gallery-card__body {
  padding: 0.8rem;
  display: grid;
  gap: 0.25rem;
}

.gallery-card__body strong {
  color: #22344d;
}

.gallery-card__body small,
.gallery-card__body p {
  margin: 0;
  color: #66788c;
}

.receipt-list {
  display: grid;
  gap: 0.75rem;
}

.receipt-card {
  border: 1px solid #e1e6ef;
  border-radius: 14px;
  padding: 0.9rem;
  background: #fff;
}

.receipt-card__top {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  align-items: center;
  margin-bottom: 0.35rem;
}

.receipt-card p {
  margin: 0 0 0.45rem;
  color: #66788c;
}

.receipt-state {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 0.22rem 0.65rem;
  background: #fff3cd;
  color: #7a5a00;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: capitalize;
}

.receipt-link {
  color: #0f2f5f;
  font-weight: 700;
  text-decoration: none;
}

.receipt-link:hover {
  text-decoration: underline;
}

@media (max-width: 767.98px) {
  .content {
    padding: 0 1rem 1rem;
  }

  .asset-form__grid {
    grid-template-columns: 1fr;
  }
}
</style>

