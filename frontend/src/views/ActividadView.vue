<template>
  <div class="d-flex">
    <SidebarMenu />

    <div class="content-wrapper">
      <div class="content-header">
        <div class="activities-header">
          <div class="activities-header__top">
            <h3 class="m-0"><i class="fa-solid fa-list me-2"></i>Gestion de actividades</h3>
            <button class="btn btn-danger btn-sm activity-header__create" @click="openCreateModal" aria-label="Nueva actividad">
              <i class="fa-solid fa-calendar-plus"></i>
              <span class="activity-header__create-label">Nueva actividad</span>
            </button>
          </div>

          <div class="activities-header__search">
            <input
              v-model="search"
              @input="onSearch"
              type="text"
              class="form-control form-control-sm"
              placeholder="Buscar por nombre, tipo, lugar o colaborador externo..."
            >
          </div>
        </div>
      </div>

      <div class="content">
        <div class="card shadow">
          <div class="card-body">

            <div class="activities-card-list">
              <article v-for="actividad in actividades" :key="`card-${actividad.id}`" class="activity-card">
                <div class="activity-card__header">
                  <div class="activity-card__heading">
                    <h4>{{ actividad.nombre || 'Actividad' }}</h4>
                    <span class="activity-type-badge">{{ actividad.tipo || '-' }}</span>
                  </div>

                  <div class="activity-card__actions">
                    <button @click="editActividad(actividad.id)" class="btn btn-sm activity-action-button" title="Editar" aria-label="Editar actividad">
                      <i class="fa-solid fa-edit"></i>
                    </button>
                    <button
                      v-if="hasAssociatedDocuments(actividad)"
                      type="button"
                      class="btn btn-sm activity-action-button document-icon-button document-icon-button--card-action"
                      title="Ver documentos asociados"
                      aria-label="Ver documentos asociados"
                      @click="openAssociatedDocumentsModal(actividad)"
                    >
                      <img :src="documentsIcon" alt="" class="document-icon-button__icon activity-card__asset-icon--blue">
                    </button>
                    <button @click="deleteActividad(actividad.id)" class="btn btn-sm btn-outline-danger" title="Eliminar" aria-label="Eliminar actividad">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </div>
                </div>

                <div class="activity-card__details">
                  <div class="activity-card__row">
                    <span class="activity-card__label"><i class="fa-regular fa-calendar-days"></i> Fecha</span>
                    <div class="activity-card__value activity-card__value--dates">
                      <span><strong>Inicio:</strong> {{ formatDate(actividad.fecha_inicio) }}</span>
                      <span><strong>Termino:</strong> {{ formatDate(actividad.fecha_termino) }}</span>
                    </div>
                  </div>

                  <div class="activity-card__row">
                    <span class="activity-card__label"><i class="fa-solid fa-location-dot"></i> Lugar</span>
                    <strong class="activity-card__value">{{ actividad.lugar || '-' }}</strong>
                  </div>

                  <div class="activity-card__row">
                    <span class="activity-card__label"><i class="fa-regular fa-clock"></i> Horas</span>
                    <strong class="activity-card__value">{{ formatHours(actividad.horas_totales) }}</strong>
                  </div>

                  <div class="activity-card__row">
                    <span class="activity-card__label">
                      <img :src="volunteersIcon" alt="" class="activity-card__asset-icon activity-card__asset-icon--red">
                      Voluntarios
                    </span>
                    <button class="btn volunteer-pill-button" @click="showVolunteers(actividad)">
                      {{ volunteerButtonLabel(actividad) }}
                    </button>
                  </div>


                </div>
              </article>

              <div v-if="!actividades.length" class="activity-card activity-card--empty">
                No hay actividades disponibles
              </div>
            </div>

            <div class="table-shell table-shell--activities">
              <div v-if="showActivitiesTableNavigation" class="table-scroll-controls" aria-label="Navegacion horizontal de tabla">
                <button
                  type="button"
                  class="table-scroll-button"
                  :disabled="!canScrollActivitiesLeft"
                  @click="scrollActivitiesTable(-1)"
                  aria-label="Desplazar tabla hacia la izquierda"
                >
                  <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button
                  type="button"
                  class="table-scroll-button"
                  :disabled="!canScrollActivitiesRight"
                  @click="scrollActivitiesTable(1)"
                  aria-label="Desplazar tabla hacia la derecha"
                >
                  <i class="fa-solid fa-chevron-right"></i>
                </button>
              </div>

              <div ref="activitiesTableShell" class="table-responsive activities-table-shell" @scroll="updateActivitiesTableScroll">
                <table class="table custom-table custom-table--responsive">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Nombre</th>
                      <th>Tipo</th>
                      <th>Fechas</th>
                      <th>Lugar</th>
                      <th>Horas</th>
                      <th>Voluntarios</th>

                      <th class="text-center">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(actividad, index) in actividades" :key="actividad.id">
                      <td data-label="#">{{ (meta.from || 1) + index }}</td>
                      <td data-label="Nombre">
                        <div class="fw-semibold">{{ actividad.nombre || '-' }}</div>
                        <small class="text-muted">{{ actividad.objetivo || 'Sin objetivo registrado' }}</small>
                      </td>
                      <td data-label="Tipo"><span class="activity-type-badge activity-type-badge--table">{{ actividad.tipo || '-' }}</span></td>
                      <td data-label="Fechas">
                        <div class="activity-date-cell">
                          <span><strong>Inicio:</strong> {{ formatDate(actividad.fecha_inicio) }}</span>
                          <span><strong>Termino:</strong> {{ formatDate(actividad.fecha_termino) }}</span>
                        </div>
                      </td>
                      <td data-label="Lugar">{{ actividad.lugar || '-' }}</td>
                      <td data-label="Horas">{{ formatHours(actividad.horas_totales) }}</td>
                      <td data-label="Voluntarios">
                        <button class="btn volunteer-pill-button volunteer-pill-button--table" @click="showVolunteers(actividad)">
                          {{ volunteerButtonLabel(actividad) }}
                        </button>
                      </td>

                      <td data-label="Acciones">
                        <div class="d-flex justify-content-center actions-cell">
                          <button @click="editActividad(actividad.id)" class="btn btn-sm activity-action-button" title="Editar">
                            <i class="fa-solid fa-edit"></i>
                          </button>
                          <button
                            v-if="hasAssociatedDocuments(actividad)"
                            type="button"
                            class="btn btn-sm activity-action-button document-icon-button document-icon-button--table"
                            title="Ver documentos asociados"
                            aria-label="Ver documentos asociados"
                            @click="openAssociatedDocumentsModal(actividad)"
                          >
                            <img :src="documentsIcon" alt="" class="document-icon-button__icon activity-card__asset-icon--blue">
                          </button>
                          <button @click="deleteActividad(actividad.id)" class="btn btn-sm btn-outline-danger" title="Eliminar">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="!actividades.length" class="no-results-row">
                      <td colspan="8" class="text-center py-3 no-results-cell">No hay actividades disponibles</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <nav v-if="meta.last_page > 1" class="mt-3">
              <ul class="pagination cruz-roja-pagination justify-content-end">
                <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                  <button class="page-link" @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page === 1">
                    <i class="fa-solid fa-chevron-left"></i> Anterior
                  </button>
                </li>
                <li class="page-item" v-for="page in meta.last_page" :key="page" :class="{ active: meta.current_page === page }">
                  <button class="page-link" @click="goToPage(page)">{{ page }}</button>
                </li>
                <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
                  <button class="page-link" @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page">
                    Siguiente <i class="fa-solid fa-chevron-right"></i>
                  </button>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <ActividadEditView
      v-if="selectedActividadId !== null"
      :actividadId="selectedActividadId"
      ref="actividadEditModal"
      @actividad-updated="onActividadUpdated"
    />

    <ActividadCreateView
      ref="actividadCreateModal"
      @actividad-created="onActividadCreated"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'
import SidebarMenu from '../components/SidebarMenu.vue'
import { API_BASE } from '../config/api'
import ActividadEditView from './ActividadEditView.vue'
import ActividadCreateView from './ActividadCreateView.vue'
import volunteersIcon from '../assets/icons/cruz-roja/voluntary-service.png'
import documentsIcon from '../assets/icons/cruz-roja/documents.png'

const router = useRouter()
const actividades = ref([])
const meta = ref({ current_page: 1, last_page: 1, from: 1 })
const search = ref('')
const selectedActividadId = ref(null)
const actividadEditModal = ref(null)
const actividadCreateModal = ref(null)
const activitiesTableShell = ref(null)
const showActivitiesTableNavigation = ref(false)
const canScrollActivitiesLeft = ref(false)
const canScrollActivitiesRight = ref(false)

async function fetchActividades(page = 1) {
  const params = { page }
  if (search.value) params.search = search.value

  try {
    const response = await axios.get(`${API_BASE}/actividad`, { params })
    actividades.value = response.data.data || []
    meta.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      from: response.data.from
    }
    nextTick(() => updateActivitiesTableScroll())
  } catch (error) {
    actividades.value = []
    meta.value = { current_page: 1, last_page: 1, from: 1 }
  }
}

function updateActivitiesTableScroll() {
  const shell = activitiesTableShell.value

  if (!shell || shell.offsetParent === null) {
    showActivitiesTableNavigation.value = false
    canScrollActivitiesLeft.value = false
    canScrollActivitiesRight.value = false
    return
  }

  const overflowOffset = shell.scrollWidth - shell.clientWidth
  const hasOverflow = overflowOffset > 8

  showActivitiesTableNavigation.value = hasOverflow
  canScrollActivitiesLeft.value = hasOverflow && shell.scrollLeft > 4
  canScrollActivitiesRight.value = hasOverflow && shell.scrollLeft < overflowOffset - 4
}

function scrollActivitiesTable(direction) {
  const shell = activitiesTableShell.value
  if (!shell) {
    return
  }

  shell.scrollBy({
    left: direction * Math.max(shell.clientWidth * 0.72, 220),
    behavior: 'smooth'
  })

  window.setTimeout(() => updateActivitiesTableScroll(), 260)
}

function onSearch() {
  fetchActividades(1)
}

function goToPage(page) {
  if (page >= 1 && page <= meta.value.last_page) {
    fetchActividades(page)
  }
}

function openCreateModal() {
  actividadCreateModal.value?.show()
}

function editActividad(id) {
  selectedActividadId.value = id
  window.setTimeout(() => {
    actividadEditModal.value?.show()
  }, 100)
}

function onActividadCreated() {
  fetchActividades(meta.value.current_page)
}

function onActividadUpdated() {
  selectedActividadId.value = null
  fetchActividades(meta.value.current_page)
}

async function deleteActividad(id) {
  const confirm = await Swal.fire({
    title: 'Esta seguro de que desea eliminar esta actividad?',
    text: 'Esta accion eliminara la actividad y sus asociaciones.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Si, eliminar',
    cancelButtonText: 'Cancelar'
  })

  if (!confirm.isConfirmed) {
    return
  }

  try {
    await axios.delete(`${API_BASE}/actividad/${id}`)
    fetchActividades(meta.value.current_page)
    Swal.fire('Eliminada', 'La actividad ha sido eliminada.', 'success')
  } catch (error) {
    Swal.fire('Error', 'No se pudo eliminar la actividad.', 'error')
  }
}

function showVolunteers(actividad) {
  const volunteers = actividad.voluntarios || []

  if (!volunteers.length) {
    Swal.fire('Voluntarios asociados', 'No hay voluntarios asociados a esta actividad.', 'info')
    return
  }

  const html = volunteers.map((volunteer) => {
    const fullName = [volunteer.nombres, volunteer.apellidos].filter(Boolean).join(' ') || volunteer.user?.username || volunteer.registro_filial || 'Voluntario'
    const hours = Number(volunteer.pivot?.horas_asistidas ?? 0)
    return `<li><strong>${fullName}</strong> · Reg. ${volunteer.registro_filial || '-'} · ${hours} h</li>`
  }).join('')

  Swal.fire({
    title: `Voluntarios asociados (${volunteers.length})`,
    html: `<ul style="text-align:left;padding-left:1.25rem;margin:0">${html}</ul>`,
    confirmButtonText: 'Cerrar'
  })
}

function associatedDocuments(actividad) {
  return (actividad?.documentos || []).filter((documento) => {
    return ['analisis_contexto', 'informe_narrativo'].includes(documento?.tipo_documento) && ['borrador', 'final'].includes(documento?.estado)
  })
}

function hasAssociatedDocuments(actividad) {
  return associatedDocuments(actividad).length > 0
}

function documentTypeLabel(type) {
  if (type === 'analisis_contexto') {
    return 'Analisis de contexto'
  }

  if (type === 'informe_narrativo') {
    return 'Informe narrativo'
  }

  return 'Documento'
}

function openAssociatedDocuments(actividad, type) {
  router.push({
    name: 'documentos',
    query: {
      actividad: String(actividad.id),
      tipo: type
    }
  })
}

async function openAssociatedDocumentsModal(actividad) {
  const documents = associatedDocuments(actividad)

  if (!documents.length) {
    return
  }

  const uniqueDocuments = documents.filter((documento, index, array) => {
    return array.findIndex((item) => item.tipo_documento === documento.tipo_documento) === index
  })

  const buttonsMarkup = uniqueDocuments.map((documento) => `
    <button
      type="button"
      class="swal2-styled associated-document-option"
      data-document-type="${documento.tipo_documento}"
    >
      ${documentTypeLabel(documento.tipo_documento)}
      <span>${documento.estado === 'final' ? 'Version final' : 'Borrador disponible'}</span>
    </button>
  `).join('')

  await Swal.fire({
    title: 'Documentos asociados',
    html: `
      <div class="associated-document-modal">
        <p>Selecciona el documento que quieres abrir para esta actividad.</p>
        <div class="associated-document-options">${buttonsMarkup}</div>
      </div>
    `,
    showConfirmButton: false,
    showCloseButton: true,
    didOpen: () => {
      const popup = Swal.getPopup()
      if (!popup) {
        return
      }

      popup.querySelectorAll('[data-document-type]').forEach((button) => {
        button.addEventListener('click', () => {
          const selectedType = button.getAttribute('data-document-type')
          Swal.close()
          if (selectedType) {
            openAssociatedDocuments(actividad, selectedType)
          }
        })
      })
    }
  })
}

function volunteerButtonLabel(actividad) {
  const count = actividad?.voluntarios?.length || 0
  return `${count} voluntario${count === 1 ? '' : 's'}`
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

function formatHours(value) {
  if (value === null || value === undefined || value === '') return '-'
  const numericValue = Number(value)
  if (!Number.isFinite(numericValue)) return '-'
  return `${numericValue % 1 === 0 ? numericValue.toFixed(0) : numericValue.toFixed(2)} horas`
}

onMounted(() => {
  fetchActividades()
  window.addEventListener('resize', updateActivitiesTableScroll)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateActivitiesTableScroll)
})
</script>

<style scoped>
.table-shell {
  display: grid;
  gap: 0.7rem;
}

.activities-card-list {
  display: none;
}

.table-scroll-controls {
  display: none;
  justify-content: flex-end;
  gap: 0.55rem;
}

.table-scroll-button {
  width: 2.3rem;
  height: 2.3rem;
  border: 1px solid #d5deea;
  border-radius: 999px;
  background: #fff;
  color: #274062;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 18px rgba(15, 47, 95, 0.08);
}

.table-scroll-button:disabled {
  opacity: 0.45;
  cursor: default;
  box-shadow: none;
}

.custom-table {
  width: 100%;
  min-width: 78rem;
  border-collapse: separate;
  border-spacing: 0;
  table-layout: auto;
}

.custom-table th {
  font-size: 0.9rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  padding: 10px 12px;
  color: #333;
  background-color: #f5f5f5;
  white-space: nowrap;
  overflow-wrap: normal;
  word-break: normal;
  hyphens: none;
  line-height: 1.25;
}

.custom-table td {
  padding: 8px 12px;
  vertical-align: middle;
  border-bottom: 1px solid #e0e0e0;
  white-space: normal;
  overflow-wrap: normal;
  word-break: normal;
  hyphens: none;
  line-height: 1.35;
}

.card {
  border: none;
  border-radius: 8px;
  overflow: hidden;
}

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

.activities-header {
  display: grid;
  gap: 0.9rem;
}

.activities-header__top {
  display: flex;
  align-items: stretch;
  justify-content: space-between;
  gap: 0.75rem;
}

.activities-header__top h3 {
  display: flex;
  align-items: center;
  min-height: 56px;
  font-size: 2rem;
  line-height: 1.05;
}

.activities-header__search {
  display: flex;
  width: min(100%, 28rem);
}

.activity-header__create {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.55rem;
  min-width: 44px;
  min-height: 56px;
  padding: 0.85rem 1.2rem;
  border-radius: 16px;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.1;
  white-space: nowrap;
}

.cruz-roja-pagination .page-link {
  color: #e01e1e;
  font-weight: 600;
  border: 1px solid #e01e1e;
  background: #fff;
  border-radius: 6px;
  margin: 0 2px;
}

.cruz-roja-pagination .page-item.active .page-link,
.cruz-roja-pagination .page-link:hover {
  background: #e01e1e;
  color: #fff;
  border-color: #e01e1e;
}

.cruz-roja-pagination .page-item.disabled .page-link {
  color: #aaa;
  background: #f5f5f5;
  border-color: #e0e0e0;
}

.actions-cell {
  flex-wrap: nowrap;
  gap: 0.55rem;
}

.actions-cell .btn {
  width: 44px;
  height: 44px;
  padding: 0;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.actions-cell .btn i {
  font-size: 1.1rem;
}

.activity-action-button {
  border-color: #0f4c81;
  color: #0f4c81;
  border-radius: 12px;
}

.activity-action-button:hover,
.activity-action-button:focus,
.activity-action-button:active {
  border-color: #0c416d;
  color: #0c416d;
  background: #edf5fb;
}

.activity-date-cell {
  display: grid;
  gap: 0.2rem;
  white-space: nowrap;
}

.activity-date-cell span {
  display: block;
}

.activity-type-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 38px;
  padding: 0.45rem 0.95rem;
  border: 1.5px solid #0f4c81;
  border-radius: 0.9rem;
  color: #0f4c81;
  background: #fff;
  font-size: 1rem;
  font-weight: 800;
  line-height: 1;
  white-space: nowrap;
}

.activity-card__asset-icon--red {
  filter: brightness(0) saturate(100%) invert(28%) sepia(86%) saturate(2111%) hue-rotate(338deg) brightness(96%) contrast(93%);
}

.activity-card__asset-icon--blue {
  filter: brightness(0) saturate(100%) invert(18%) sepia(30%) saturate(1812%) hue-rotate(174deg) brightness(91%) contrast(93%);
}

.volunteer-pill-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  min-height: 42px;
  padding: 0.62rem 1rem;
  border: 1.5px solid #df3342;
  border-radius: 999px;
  color: #fff;
  background: #df3342;
  font-weight: 700;
  line-height: 1.1;
  white-space: nowrap;
}

.volunteer-pill-button:hover,
.volunteer-pill-button:focus,
.volunteer-pill-button:active {
  border-color: #c92b39;
  color: #fff;
  background: #c92b39;
}


.document-icon-button {
  padding: 0;
  background: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.document-icon-button:hover,
.document-icon-button:focus,
.document-icon-button:active {
  background: #eff6ff;
}

.document-icon-button__icon {
  width: 1.45rem;
  height: 1.45rem;
  object-fit: contain;
}

:deep(.associated-document-modal) {
  text-align: left;
}

:deep(.associated-document-modal p) {
  margin: 0 0 0.9rem;
  color: #475569;
}

:deep(.associated-document-options) {
  display: grid;
  gap: 0.75rem;
}

:deep(.associated-document-option) {
  width: 100%;
  margin: 0;
  border-radius: 16px;
  border: 1.5px solid #0f4c81;
  background: #fff;
  color: #0f4c81;
  display: grid;
  gap: 0.2rem;
  justify-items: start;
  text-align: left;
  padding: 0.9rem 1rem;
  box-shadow: none;
}

:deep(.associated-document-option span) {
  font-size: 0.86rem;
  font-weight: 600;
  color: #64748b;
}

:deep(.associated-document-option:hover) {
  background: #fff7f8;
}

@media (min-width: 1200px) {
  .volunteer-pill-button--table {
    width: auto;
    min-width: 0;
    max-width: none;
    padding-inline: 0.75rem;
  }

  .activity-type-badge--table {
    min-height: 42px;
    padding: 0.5rem 1rem;
    font-size: 0.95rem;
  }

  .document-icon-button--table {
    width: 44px;
    height: 44px;
    border-radius: 12px;
  }
}

@media (max-width: 1199.98px) {
  .table-shell--activities {
    display: none;
  }

  .activities-card-list {
    display: grid;
    gap: 1rem;
  }

  .activity-card {
    border: 1px solid #e5eaf1;
    border-radius: 22px;
    padding: 1.1rem 1rem 1rem;
    background:
      linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 252, 255, 0.98)),
      radial-gradient(circle at top right, rgba(224, 30, 30, 0.08), transparent 38%);
    box-shadow: 0 16px 30px rgba(15, 47, 95, 0.08);
  }

  .activity-card--empty {
    text-align: center;
    color: #64748b;
    font-weight: 600;
  }

  .activity-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.85rem;
    margin-bottom: 0.95rem;
  }

  .activity-card__heading {
    min-width: 0;
  }

  .activity-card__heading h4 {
    margin: 0 0 0.7rem;
    color: #12284c;
    font-size: 1.55rem;
    line-height: 1.1;
    font-weight: 800;
  }



  .activity-card__actions {
    display: flex;
    gap: 0.45rem;
    flex-shrink: 0;
  }

  .activity-card__actions .btn {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
  }

  .activity-card__actions .btn i {
    font-size: 1.1rem;
  }

  .activity-card__details {
    display: grid;
  }

  .activity-card__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 0;
    border-top: 1px solid #e6edf5;
  }

  .activity-card__row:first-child {
    border-top: none;
    padding-top: 0;
  }

  .activity-card__label {
    display: inline-flex;
    align-items: center;
    gap: 0.7rem;
    color: #334155;
    font-size: 1rem;
    line-height: 1.2;
    min-width: 8rem;
  }

  .activity-card__label--stacked {
    align-items: center;
  }

  .activity-card__label i {
    width: 1.2rem;
    color: #e01e1e;
    font-size: 1.15rem;
    text-align: center;
  }

  .activity-card__asset-icon {
    width: 1.5rem;
    height: 1.5rem;
    object-fit: contain;
    flex-shrink: 0;
  }

  .activity-card__value {
    color: #12284c;
    font-size: 1.05rem;
    line-height: 1.3;
    text-align: right;
  }

  .activity-card__value--dates {
    display: grid;
    gap: 0.3rem;
    text-align: right;
  }
}

@media (max-width: 767.98px) {
  .content {
    padding: 0 1rem 1rem;
  }

  .content-header {
    padding: 1rem 1rem 0.9rem;
    margin-bottom: 1rem;
  }

  .activities-header__top {
    align-items: center;
  }

  .activities-header__top h3 {
    min-height: 44px;
    font-size: 1.75rem;
  }

  .activities-header__search {
    width: 100%;
  }

  .activities-header__search .form-control {
    min-height: 46px;
    border-radius: 14px;
    font-size: 0.98rem;
  }

  .activity-header__create {
    width: 44px;
    min-width: 44px;
    height: 44px;
    min-height: 44px;
    padding: 0;
    border-radius: 12px;
    flex-shrink: 0;
  }

  .activity-header__create-label {
    display: none;
  }

  .activity-card__heading h4 {
    font-size: 1.35rem;
  }

  .activity-card__row {
    gap: 0.8rem;
  }

  .activity-card__label {
    min-width: 7rem;
  }
}
</style>



















