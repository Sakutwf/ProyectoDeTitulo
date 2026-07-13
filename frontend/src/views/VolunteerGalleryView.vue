<template>
  <div class="d-flex">
    <SidebarMenu />

    <div class="content-wrapper">
      <div class="content-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <h3 class="m-0"><i class="fa-solid fa-images me-2"></i>Mi galería</h3>
          <span class="role-chip">Actividades inscritas</span>
        </div>
      </div>

      <div class="content">
        <div class="card shadow volunteer-card">
          <div class="card-body">
            <div class="section-copy">
              <h2>Galería de actividades</h2>
              <p>
                Sube fotografías a las actividades en las que estás inscrito y revisa las imágenes ya compartidas.
              </p>
            </div>

            <div v-if="isLoading" class="empty-state">
              Cargando actividades inscritas...
            </div>

            <div v-else-if="enrolledActivities.length === 0" class="empty-state">
              Aún no estás inscrito en actividades con galería disponible.
            </div>

            <div v-else class="activity-grid">
              <article v-for="actividad in enrolledActivities" :key="actividad.id" class="activity-card">
                <div class="activity-card__top">
                  <div>
                    <span class="activity-badge">{{ actividad.tipo || 'Sin tipo' }}</span>
                    <h3>{{ actividad.nombre || 'Actividad sin nombre' }}</h3>
                    <p>{{ formatDateRange(actividad.fecha_inicio, actividad.fecha_termino) }}</p>
                  </div>

                  <button
                    type="button"
                    class="btn btn-outline-primary activity-toggle"
                    :disabled="galleryLoadingActivityId === actividad.id"
                    @click="toggleGalleryPanel(actividad)"
                  >
                    {{ galleryButtonLabel(actividad) }}
                  </button>
                </div>

                <section v-if="galleryActivityId === actividad.id" class="gallery-panel">
                  <div class="gallery-panel__copy">
                    <h4>Subir fotografía</h4>
                    <p>Las imágenes quedarán asociadas a esta actividad para los participantes y la gestión interna.</p>
                  </div>

                  <div class="gallery-form">
                    <div class="gallery-form__grid">
                      <input
                        v-model.trim="galleryForm.titulo"
                        type="text"
                        class="form-control"
                        placeholder="Nombre de la foto"
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
                      rows="3"
                      placeholder="Descripción breve"
                    ></textarea>

                    <input
                      type="file"
                      class="form-control"
                      accept=".jpg,.jpeg,.png,.webp"
                      @change="onGalleryFileSelected"
                    >

                    <div class="gallery-form__actions">
                      <button
                        type="button"
                        class="btn btn-danger"
                        :disabled="gallerySubmitting || !galleryForm.file"
                        @click="uploadGalleryImage(actividad)"
                      >
                        {{ gallerySubmitting ? 'Subiendo...' : 'Subir foto' }}
                      </button>
                    </div>
                  </div>

                  <div v-if="galleryLoadingActivityId === actividad.id" class="empty-state empty-state--nested">
                    Cargando galería...
                  </div>

                  <div v-else-if="galleryItems.length" class="gallery-grid-inner">
                    <article v-for="item in galleryItems" :key="item.id" class="gallery-item-card">
                      <img :src="item.imagen_url" :alt="item.titulo || 'Imagen de actividad'" class="gallery-item-card__image">
                      <div class="gallery-item-card__body">
                        <strong>{{ item.titulo || 'Imagen sin nombre' }}</strong>
                        <small>{{ formatDate(item.fecha) }}</small>
                        <p>{{ item.descripcion || 'Sin descripción.' }}</p>
                      </div>
                    </article>
                  </div>

                  <div v-else class="empty-state empty-state--nested">
                    Todavía no hay fotografías registradas para esta actividad.
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
const galleryActivityId = ref(null)
const galleryLoadingActivityId = ref(null)
const gallerySubmitting = ref(false)
const galleryItems = ref([])
const galleryForm = ref(createEmptyGalleryForm())

const enrolledActivities = computed(() =>
  activities.value
    .filter((actividad) => (actividad.voluntarios || []).some((volunteer) => Number(volunteer.id) === Number(currentVolunteerId.value)))
    .sort((left, right) => String(right.fecha_inicio || '').localeCompare(String(left.fecha_inicio || '')))
)

function createEmptyGalleryForm() {
  return {
    titulo: '',
    descripcion: '',
    fecha: '',
    file: null
  }
}

function formatDate(value) {
  if (!value) return '-'
  const parsed = new Date(`${String(value).slice(0, 10)}T00:00:00`)
  if (Number.isNaN(parsed.getTime())) return String(value)
  return parsed.toLocaleDateString('es-CL')
}

function formatDateRange(start, end) {
  if (!start && !end) return '-'
  if (start && end) return `${formatDate(start)} - ${formatDate(end)}`
  return formatDate(start || end)
}

async function loadActivities() {
  if (!currentVolunteerId.value) {
    activities.value = []
    return
  }

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
    show_alerta('No se pudieron cargar tus actividades inscritas.', 'error')
  } finally {
    isLoading.value = false
  }
}

function galleryButtonLabel(actividad) {
  if (galleryLoadingActivityId.value === actividad.id) {
    return 'Cargando...'
  }

  return galleryActivityId.value === actividad.id ? 'Ocultar galeria' : 'Abrir galeria'
}

function onGalleryFileSelected(event) {
  galleryForm.value.file = event.target.files?.[0] || null
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

async function toggleGalleryPanel(actividad) {
  if (galleryActivityId.value === actividad.id) {
    galleryActivityId.value = null
    galleryItems.value = []
    galleryForm.value = createEmptyGalleryForm()
    return
  }

  galleryActivityId.value = actividad.id
  galleryForm.value = createEmptyGalleryForm()
  await loadGallery(actividad.id)
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
    show_alerta('Fotografía subida correctamente.', 'success')
  } catch (error) {
    show_alerta('No se pudo subir la fotografía.', 'error')
  } finally {
    gallerySubmitting.value = false
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
  margin: 0 0 0.5rem;
  color: #243447;
  font-size: 1.65rem;
  font-weight: 700;
}

.section-copy p {
  margin: 0;
  color: #5c6b7c;
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

.empty-state--nested {
  padding: 1rem;
}

.activity-grid {
  display: grid;
  gap: 1rem;
}

.activity-card {
  border: 1px solid #e1e6ef;
  border-radius: 20px;
  padding: 1rem;
  background: #fff;
  display: grid;
  gap: 1rem;
}

.activity-card__top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.activity-card__top h3 {
  margin: 0.65rem 0 0.3rem;
  color: #243447;
  font-size: 1.25rem;
  font-weight: 800;
}

.activity-card__top p {
  margin: 0;
  color: #66788c;
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

.activity-toggle {
  border-radius: 14px;
  font-weight: 700;
  white-space: nowrap;
}

.gallery-panel {
  border-top: 1px solid #e7edf4;
  padding-top: 1rem;
  display: grid;
  gap: 1rem;
}

.gallery-panel__copy h4 {
  margin: 0 0 0.3rem;
  color: #163a69;
  font-size: 1.05rem;
  font-weight: 800;
}

.gallery-panel__copy p {
  margin: 0;
  color: #66788c;
}

.gallery-form {
  display: grid;
  gap: 0.8rem;
  padding: 1rem;
  border-radius: 18px;
  background: #f8fafc;
  border: 1px solid #e5ebf2;
}

.gallery-form__grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.8rem;
}

.gallery-form__actions {
  display: flex;
  justify-content: flex-end;
}

.gallery-grid-inner {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 0.9rem;
}

.gallery-item-card {
  border: 1px solid #e1e6ef;
  border-radius: 18px;
  overflow: hidden;
  background: #fff;
}

.gallery-item-card__image {
  width: 100%;
  height: 190px;
  object-fit: cover;
  display: block;
}

.gallery-item-card__body {
  padding: 0.9rem;
  display: grid;
  gap: 0.25rem;
}

.gallery-item-card__body strong {
  color: #22344d;
}

.gallery-item-card__body small,
.gallery-item-card__body p {
  margin: 0;
  color: #66788c;
}

@media (max-width: 767.98px) {
  .content-header {
    padding: 1rem 1rem 0.9rem;
    margin-bottom: 1rem;
  }

  .content {
    padding: 0 1rem 1rem;
  }

  .gallery-form__grid {
    grid-template-columns: 1fr;
  }

  .activity-card__top {
    flex-direction: column;
  }

  .activity-toggle {
    width: 100%;
  }
}
</style>
