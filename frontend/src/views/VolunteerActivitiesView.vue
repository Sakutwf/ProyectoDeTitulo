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
const currentVolunteerRegister = computed(() => currentUser.value?.voluntario?.n_registro || null)
const activities = ref([])
const isLoading = ref(false)
const loadingActivityId = ref(null)

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
  return (actividad.voluntarios || []).some((volunteer) => volunteer.n_registro === currentVolunteerRegister.value)
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
  if (!currentVolunteerRegister.value) {
    show_alerta('No existe un voluntario válido para inscribirse.', 'error')
    return
  }

  loadingActivityId.value = actividad.id

  try {
    if (isEnrolled(actividad)) {
      await axios.delete(`${API_BASE}/actividad/${actividad.id}/voluntarios`, {
        data: { voluntario_n_registro: currentVolunteerRegister.value }
      })
      show_alerta('Inscripción retirada correctamente.', 'success')
    } else {
      await axios.post(`${API_BASE}/actividad/${actividad.id}/voluntarios`, {
        voluntario_n_registro: currentVolunteerRegister.value,
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

@media (max-width: 767.98px) {
  .content {
    padding: 0 1rem 1rem;
  }
}
</style>
