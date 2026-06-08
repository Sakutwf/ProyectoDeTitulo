<template>
  <div class="d-flex">
    <SidebarMenu />

    <div class="content-wrapper">
      <div class="content-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <h3 class="m-0"><i class="fa-solid fa-list-check me-2"></i>Actividades</h3>
          <span class="role-chip">Inscripciones activas</span>
        </div>
      </div>

      <div class="content">
        <div class="card shadow volunteer-card">
          <div class="card-body">
            <div class="section-copy">
              <h2>Actividades activas para inscripcion</h2>
              <p>
                Aqui puedes revisar las actividades de servicio vigentes e inscribirte cuando correspondan.
              </p>
            </div>

            <div v-if="isLoading" class="empty-state">
              Cargando actividades activas...
            </div>

            <div v-else-if="activeActivities.length === 0" class="empty-state">
              No hay actividades activas disponibles en este momento.
            </div>

            <div v-else class="activity-grid">
              <article
                v-for="actividad in activeActivities"
                :key="actividad.id"
                class="activity-card"
              >
                <div class="activity-card__top">
                  <span class="activity-badge">{{ actividad.tipo || 'Sin tipo' }}</span>
                  <span class="activity-date">{{ formatDateRange(actividad.evento?.fecha_inicio, actividad.evento?.fecha_termino) }}</span>
                </div>

                <h3>{{ actividad.nombre || 'Actividad sin nombre' }}</h3>
                <p class="activity-event">{{ actividad.evento?.nombre || 'Evento sin nombre' }}</p>
                <p class="activity-description">
                  {{ actividad.evento?.descripcion || 'Sin descripcion disponible.' }}
                </p>

                <div class="activity-meta">
                  <span>{{ formatHours(actividad.horas_participacion) }}</span>
                  <span>{{ actividad.users?.length || 0 }} inscrito(s)</span>
                </div>

                <button
                  type="button"
                  class="btn"
                  :class="isEnrolled(actividad) ? 'btn-outline-danger' : 'btn-danger'"
                  :disabled="loadingActivityId === actividad.id"
                  @click="toggleEnrollment(actividad)"
                >
                  {{ enrollmentButtonLabel(actividad) }}
                </button>
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
import { show_alerta } from '../funciones'
import { useStore } from 'vuex'

const API_BASE = 'http://localhost:8000/api'

const store = useStore()
const currentUser = computed(() => store.getters.authUser)
const activities = ref([])
const isLoading = ref(false)
const loadingActivityId = ref(null)

const todayIso = new Date().toISOString().slice(0, 10)

const activeActivities = computed(() =>
  activities.value
    .filter((actividad) => actividad?.evento?.tipo === 'SERVICIO')
    .filter((actividad) => (actividad?.evento?.fecha_termino || '').slice(0, 10) >= todayIso)
    .sort((left, right) => (left.evento?.fecha_inicio || '').localeCompare(right.evento?.fecha_inicio || ''))
)

function isEnrolled(actividad) {
  return (actividad.users || []).some((user) => Number(user.id) === Number(currentUser.value?.id))
}

function enrollmentButtonLabel(actividad) {
  if (loadingActivityId.value === actividad.id) {
    return isEnrolled(actividad) ? 'Quitando...' : 'Inscribiendo...'
  }

  return isEnrolled(actividad) ? 'Quitar inscripcion' : 'Inscribirme'
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

async function toggleEnrollment(actividad) {
  if (!currentUser.value?.id) {
    show_alerta('No existe un voluntario valido para inscribirse.', 'error')
    return
  }

  loadingActivityId.value = actividad.id

  try {
    if (isEnrolled(actividad)) {
      await axios.delete(`${API_BASE}/actividad/${actividad.id}/voluntarios`, {
        data: { user_id: currentUser.value.id }
      })
      show_alerta('Inscripcion retirada correctamente.', 'success')
    } else {
      await axios.post(`${API_BASE}/actividad/${actividad.id}/voluntarios`, {
        user_id: currentUser.value.id
      })
      show_alerta('Inscripcion realizada correctamente.', 'success')
    }

    await loadActivities()
  } catch (error) {
    show_alerta('No se pudo actualizar la inscripcion en la actividad.', 'error')
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

.btn-danger {
  background-color: #e01e1e;
  border-color: #e01e1e;
}

.btn-danger:hover {
  background-color: #c51b1b;
  border-color: #c51b1b;
}

@media (max-width: 767.98px) {
  .content {
    padding: 0 1rem 1rem;
  }
}
</style>
