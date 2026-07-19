<template>
  <div class="d-flex">
    <SidebarMenu />

    <div class="content-wrapper">
      <div class="content-header">
        <div class="activities-header">
          <div class="activities-header__top">
            <h3 class="m-0"><i class="fa-solid fa-list me-2"></i>Gestión de actividades</h3>
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
                      type="button"
                      class="btn btn-sm activity-action-button document-icon-button document-icon-button--card-action"
                      :class="{ 'document-icon-button--disabled': !hasAssociatedDocuments(actividad) }"
                      :title="hasAssociatedDocuments(actividad) ? 'Ver documentos asociados' : 'Sin documentos asociados'"
                      :aria-label="hasAssociatedDocuments(actividad) ? 'Ver documentos asociados' : 'Sin documentos asociados'"
                      :disabled="!hasAssociatedDocuments(actividad)"
                      @click="openAssociatedDocumentsModal(actividad)"
                    >
                      <img :src="documentsIcon" alt="" class="document-icon-button__icon" :class="hasAssociatedDocuments(actividad) ? 'activity-card__asset-icon--blue' : 'activity-card__asset-icon--gray'">
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
                      <span><strong>Término:</strong> {{ formatDate(actividad.fecha_termino) }}</span>
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
                    <div class="activity-volunteer-actions">
                      <button class="btn volunteer-pill-button" @click="showVolunteers(actividad)">
                        {{ volunteerButtonLabel(actividad) }}
                      </button>
                      <button
                        v-if="pendingEnrollmentCount(actividad)"
                        type="button"
                        class="btn pending-enrollment-button"
                        :aria-label="pendingEnrollmentButtonLabel(actividad)"
                        :title="pendingEnrollmentButtonLabel(actividad)"
                        @click="showPendingEnrollments(actividad)"
                      >
                        <i class="fa-solid fa-exclamation" aria-hidden="true"></i>
                        <span class="visually-hidden">{{ pendingEnrollmentButtonLabel(actividad) }}</span>
                      </button>
                    </div>
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
                        <div class="activity-volunteer-actions">
                          <button class="btn volunteer-pill-button volunteer-pill-button--table" @click="showVolunteers(actividad)">
                            {{ volunteerButtonLabel(actividad) }}
                          </button>
                          <button
                            v-if="pendingEnrollmentCount(actividad)"
                            type="button"
                            class="btn pending-enrollment-button"
                            :aria-label="pendingEnrollmentButtonLabel(actividad)"
                            :title="pendingEnrollmentButtonLabel(actividad)"
                            @click="showPendingEnrollments(actividad)"
                          >
                            <i class="fa-solid fa-exclamation" aria-hidden="true"></i>
                            <span class="visually-hidden">{{ pendingEnrollmentButtonLabel(actividad) }}</span>
                          </button>
                        </div>
                      </td>

                      <td data-label="Acciones">
                        <div class="d-flex justify-content-center actions-cell">
                          <button @click="editActividad(actividad.id)" class="btn btn-sm activity-action-button" title="Editar">
                            <i class="fa-solid fa-edit"></i>
                          </button>
                          <button
                            type="button"
                            class="btn btn-sm activity-action-button document-icon-button document-icon-button--table"
                            :class="{ 'document-icon-button--disabled': !hasAssociatedDocuments(actividad) }"
                            :title="hasAssociatedDocuments(actividad) ? 'Ver documentos asociados' : 'Sin documentos asociados'"
                            :aria-label="hasAssociatedDocuments(actividad) ? 'Ver documentos asociados' : 'Sin documentos asociados'"
                            :disabled="!hasAssociatedDocuments(actividad)"
                            @click="openAssociatedDocumentsModal(actividad)"
                          >
                            <img :src="documentsIcon" alt="" class="document-icon-button__icon" :class="hasAssociatedDocuments(actividad) ? 'activity-card__asset-icon--blue' : 'activity-card__asset-icon--gray'">
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
import { formatDate } from '../utils/formatters'

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
    title: '¿Esta seguro de que desea eliminar esta actividad?',
    text: 'Esta accion eliminará la actividad y sus asociaciones.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
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
  const modalClasses = {
    popup: 'volunteer-roster-popup',
    title: 'volunteer-roster-title',
    htmlContainer: 'volunteer-roster-content',
    actions: 'volunteer-roster-actions',
    confirmButton: 'volunteer-roster-close'
  }

  if (!volunteers.length) {
    Swal.fire({
      title: 'Voluntarios asociados',
      html: '<div class="volunteer-roster-empty"><i class="fa-solid fa-users"></i><p>No hay voluntarios asociados a esta actividad.</p></div>',
      confirmButtonText: 'Cerrar',
      buttonsStyling: false,
      customClass: modalClasses
    })
    return
  }

  const html = volunteers.map((volunteer) => {
    const firstName = String(volunteer.nombres || '').trim().split(/\s+/)[0] || ''
    const firstSurname = String(volunteer.apellidos || '').trim().split(/\s+/)[0] || ''
    const fullName = [firstName, firstSurname].filter(Boolean).join(' ') || String(volunteer.user?.username || 'Voluntario').trim().split(/\s+/)[0]
    const hours = Number(volunteer.pivot?.horas_asistidas ?? 0)
    const numericHours = Number.isFinite(hours) ? (hours % 1 === 0 ? hours.toFixed(0) : hours.toFixed(2)) : '0'
    const formattedHours = `${numericHours} ${Number(numericHours) === 1 ? 'hora' : 'horas'}`
    const avatar = volunteer.foto_perfil_url
      ? `<button type="button" class="volunteer-roster-avatar volunteer-roster-avatar--photo" data-profile-photo="${escapeHtml(volunteer.foto_perfil_url)}" data-profile-name="${escapeHtml(fullName)}" aria-label="Ampliar foto de ${escapeHtml(fullName)}" title="Ampliar foto"><img src="${escapeHtml(volunteer.foto_perfil_url)}" alt="Foto de ${escapeHtml(fullName)}"></button>`
      : '<span class="volunteer-roster-avatar" aria-hidden="true"><i class="fa-solid fa-user"></i></span>'

    return `
      <article class="volunteer-roster-item">
        ${avatar}
        <div class="volunteer-roster-identity">
          <strong>${escapeHtml(fullName)}</strong>
        </div>
        <span class="volunteer-roster-hours"><i class="fa-regular fa-clock"></i> ${formattedHours}</span>
      </article>
    `
  }).join('')

  Swal.fire({
    title: `Voluntarios asociados (${volunteers.length})`,
    html: `
      <div class="volunteer-roster-list">${html}</div>
      <div class="volunteer-photo-viewer" hidden>
        <section class="volunteer-photo-viewer__dialog" role="dialog" aria-modal="true" aria-label="Foto de perfil ampliada">
          <img class="volunteer-photo-viewer__image" alt="">
        </section>
      </div>
    `,
    confirmButtonText: 'Cerrar',
    buttonsStyling: false,
    customClass: modalClasses,
    didOpen: (popup) => {
      setupVolunteerPhotoViewer(popup)
    }
  })
}

function pendingEnrollments(actividad) {
  return actividad?.solicitudes_pendientes || []
}

function pendingEnrollmentCount(actividad) {
  return pendingEnrollments(actividad).length
}

function pendingEnrollmentButtonLabel(actividad) {
  const count = pendingEnrollmentCount(actividad)
  return `${count} solicitud${count === 1 ? '' : 'es'} pendiente${count === 1 ? '' : 's'}`
}

async function showPendingEnrollments(actividad) {
  const requests = pendingEnrollments(actividad)
  if (!requests.length) return

  const maximumHours = Number(actividad.horas_totales || 0)
  const maximumHoursLabel = maximumHours > 0 ? formatHours(maximumHours) : 'Sin máximo registrado'
  const html = requests.map((volunteer) => {
    const fullName = [volunteer.nombres, volunteer.apellidos].filter(Boolean).join(' ') || volunteer.user?.username || 'Voluntario'

    return `
      <article class="enrollment-review-item" data-enrollment-row="${volunteer.id}">
        <header class="enrollment-review-identity">
          <span aria-hidden="true"><i class="fa-solid fa-user-check"></i></span>
          <strong>${escapeHtml(fullName)}</strong>
        </header>
        <label class="enrollment-review-label" for="enrollment-hours-${volunteer.id}">
          <span>Horas asignadas</span>
          <small>Máximo de la actividad: ${escapeHtml(maximumHoursLabel)}</small>
        </label>
        <input
          id="enrollment-hours-${volunteer.id}"
          class="form-control enrollment-review-hours"
          type="number"
          min="0.25"
          step="0.25"
          ${maximumHours > 0 ? `max="${maximumHours}"` : ''}
          placeholder="Ej. 4"
        >
        <div class="enrollment-review-actions">
          <button type="button" class="btn enrollment-review-action enrollment-review-action--approve" data-enrollment-decision="aprobar" data-volunteer-id="${volunteer.id}">
            Aprobar y asignar horas
          </button>
          <button type="button" class="btn enrollment-review-action enrollment-review-action--reject" data-enrollment-decision="rechazar" data-volunteer-id="${volunteer.id}">
            Rechazar
          </button>
        </div>
      </article>
    `
  }).join('')

  await Swal.fire({
    title: 'Solicitudes de inscripción',
    html: `<div class="enrollment-review-list">${html}</div>`,
    showConfirmButton: false,
    showCloseButton: true,
    buttonsStyling: false,
    customClass: {
      popup: 'volunteer-roster-popup',
      title: 'volunteer-roster-title',
      htmlContainer: 'volunteer-roster-content',
      closeButton: 'enrollment-review-close'
    },
    didOpen: (popup) => {
      popup.querySelectorAll('[data-enrollment-decision]').forEach((button) => {
        button.addEventListener('click', async () => {
          const volunteerId = Number(button.dataset.volunteerId)
          const decision = button.dataset.enrollmentDecision
          const row = popup.querySelector(`[data-enrollment-row="${volunteerId}"]`)
          const hours = Number(row?.querySelector('input')?.value || 0)

          if (decision === 'aprobar' && hours <= 0) {
            Swal.showValidationMessage('Debes asignar una cantidad de horas mayor que cero.')
            return
          }

          if (decision === 'aprobar' && maximumHours > 0 && hours > maximumHours) {
            Swal.showValidationMessage(`No puedes asignar más de ${maximumHoursLabel}.`)
            return
          }

          popup.querySelectorAll('button').forEach((item) => { item.disabled = true })

          try {
            await axios.put(`${API_BASE}/actividad/${actividad.id}/voluntarios/${volunteerId}/solicitud`, {
              decision,
              horas_asistidas: decision === 'aprobar' ? hours : null
            })
            Swal.close()
            await fetchActividades(meta.value.current_page)
            await Swal.fire(
              decision === 'aprobar' ? 'Solicitud aprobada' : 'Solicitud rechazada',
              decision === 'aprobar' ? 'El voluntario quedó inscrito con las horas asignadas.' : 'La solicitud fue rechazada.',
              'success'
            )
          } catch (error) {
            const errors = error.response?.data?.errors || {}
            const message = Object.values(errors).flat()[0] || error.response?.data?.message || 'No se pudo revisar la solicitud.'
            Swal.showValidationMessage(message)
            popup.querySelectorAll('button').forEach((item) => { item.disabled = false })
          }
        })
      })
    }
  })
}

// Abre la foto sin cerrar la lista y devuelve el foco a la miniatura al salir.
function setupVolunteerPhotoViewer(popup) {
  const viewer = popup.querySelector('.volunteer-photo-viewer')
  const image = viewer?.querySelector('.volunteer-photo-viewer__image')
  const actions = popup.querySelector('.volunteer-roster-actions')

  if (!viewer || !image) {
    return
  }

  let lastTrigger = null

  const closeViewer = () => {
    viewer.hidden = true
    popup.classList.remove('volunteer-photo-open')
    actions?.removeAttribute('hidden')
    image.removeAttribute('src')
    lastTrigger?.focus()
  }

  popup.querySelectorAll('[data-profile-photo]').forEach((button) => {
    button.addEventListener('click', () => {
      lastTrigger = button
      image.src = button.dataset.profilePhoto
      image.alt = `Foto ampliada de ${button.dataset.profileName}`
      popup.classList.add('volunteer-photo-open')
      actions?.setAttribute('hidden', '')
      viewer.hidden = false
    })
  })

  viewer.addEventListener('click', (event) => {
    if (event.target === viewer) {
      closeViewer()
    }
  })
}

function escapeHtml(value) {
  return String(value).replace(/[&<>'"]/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    "'": '&#39;',
    '"': '&quot;'
  }[character]))
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
    return 'Análisis de contexto'
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
  background: var(--cr-white);
  color: #274062;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 18px var(--cr-navy-shadow);
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
  border-bottom: 1px solid var(--cr-gray-300);
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
  color: var(--cr-red);
  font-weight: 600;
  border: 1px solid var(--cr-red);
  background: var(--cr-white);
  border-radius: 6px;
  margin: 0 2px;
}

.cruz-roja-pagination .page-item.active .page-link,
.cruz-roja-pagination .page-link:hover {
  background: var(--cr-red);
  color: var(--cr-white);
  border-color: var(--cr-red);
}

.cruz-roja-pagination .page-item.disabled .page-link {
  color: #aaa;
  background: #f5f5f5;
  border-color: var(--cr-gray-300);
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
  border-color: var(--cr-blue);
  color: var(--cr-blue);
  border-radius: 12px;
}

.activity-action-button:hover,
.activity-action-button:focus,
.activity-action-button:active {
  border-color: var(--cr-blue-dark);
  color: var(--cr-blue-dark);
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
  border: 1.5px solid var(--cr-blue);
  border-radius: 0.9rem;
  color: var(--cr-blue);
  background: var(--cr-white);
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

.activity-card__asset-icon--gray {
  filter: grayscale(1) brightness(0.7);
  opacity: 0.75;
}

.volunteer-pill-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  min-height: 42px;
  padding: 0.62rem 1rem;
  border: 1.5px solid var(--cr-navy-medium);
  border-radius: 999px;
  color: var(--cr-white);
  background: var(--cr-navy-medium);
  font-weight: 700;
  line-height: 1.1;
  white-space: nowrap;
}

.volunteer-pill-button:hover,
.volunteer-pill-button:focus,
.volunteer-pill-button:active {
  border-color: var(--cr-navy-dark);
  color: var(--cr-white);
  background: var(--cr-navy-dark);
}

.activity-volunteer-actions {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  flex-wrap: nowrap;
}

.pending-enrollment-button {
  width: 38px;
  height: 38px;
  padding: 0;
  border: 0;
  border-radius: 50%;
  justify-self: center;
  color: var(--cr-white);
  background: var(--cr-red);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.05rem;
  box-shadow: none;
}

.pending-enrollment-button:hover,
.pending-enrollment-button:focus,
.pending-enrollment-button:active {
  color: var(--cr-white);
  background: var(--cr-red-dark);
}


.document-icon-button {
  padding: 0;
  background: var(--cr-white);
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

.document-icon-button--disabled,
.document-icon-button:disabled {
  border-color: #c3ceda;
  color: #8a97a6;
  background: #f4f6f8;
  cursor: not-allowed;
}

.document-icon-button--disabled:hover,
.document-icon-button--disabled:focus,
.document-icon-button--disabled:active,
.document-icon-button:disabled:hover,
.document-icon-button:disabled:focus,
.document-icon-button:disabled:active {
  background: #f4f6f8;
  border-color: #c3ceda;
  color: #8a97a6;
}

:global(.volunteer-roster-popup) {
  width: min(92vw, 650px);
  padding: 0;
  overflow: hidden;
  border-radius: 22px;
  border-top: 6px solid var(--cr-red);
  background: var(--cr-white);
  box-shadow: 0 24px 70px rgba(11, 43, 75, 0.28);
}

:global(.volunteer-roster-title) {
  margin: 0;
  padding: 1.35rem 1.5rem 0.9rem;
  color: var(--cr-navy-medium);
  font-size: clamp(1.35rem, 3vw, 1.75rem);
  font-weight: 800;
  text-align: left;
}

:global(.volunteer-roster-content) {
  margin: 0;
  padding: 0 1.5rem;
  color: var(--cr-navy-medium);
}

:global(.volunteer-roster-list) {
  display: grid;
  max-height: min(52vh, 430px);
  gap: 0.7rem;
  overflow-y: auto;
  padding: 0.15rem 0.2rem 0.2rem 0;
  text-align: left;
}

:global(.volunteer-roster-item) {
  display: grid;
  grid-template-columns: 44px minmax(0, 1fr) auto;
  align-items: center;
  gap: 0.8rem;
  padding: 0.85rem 1rem;
  border: 1px solid #dce5ef;
  border-left: 4px solid var(--cr-red);
  border-radius: 15px;
  background: #fbfcfe;
}

:global(.volunteer-roster-avatar) {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  color: var(--cr-white);
  background: var(--cr-red);
  font-size: 1rem;
}

:global(.volunteer-roster-avatar--photo) {
  overflow: hidden;
  box-sizing: border-box;
  flex: 0 0 auto;
  padding: 0;
  cursor: zoom-in;
  border: 3px solid var(--cr-red);
  background: var(--cr-white);
}

:global(.volunteer-roster-avatar--photo img) {
  width: 100%;
  height: 100%;
  display: block;
  border-radius: 50%;
  object-fit: cover;
}

:global(.volunteer-roster-avatar--photo:hover),
:global(.volunteer-roster-avatar--photo:focus-visible) {
  transform: scale(1.06);
  box-shadow: 0 0 0 3px rgba(224, 30, 30, 0.16);
  outline: none;
}

:global(.volunteer-photo-viewer) {
  position: fixed;
  inset: 0;
  z-index: 12000;
  display: grid;
  place-items: center;
  padding: 1rem;
  background: rgba(10, 24, 43, 0.97);
  backdrop-filter: none;
}

:global(.volunteer-photo-viewer[hidden]) {
  display: none;
}

:global(.volunteer-photo-viewer__dialog) {
  width: min(72vw, 300px);
  aspect-ratio: 1;
  overflow: hidden;
  padding: 0;
  border: 3px solid var(--cr-red);
  border-radius: 50%;
  background: transparent;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
}

:global(.volunteer-photo-viewer__image) {
  display: block;
  width: 100%;
  height: 100%;
  max-width: none;
  max-height: none;
  border-radius: 50%;
  object-fit: cover;
  object-position: center;
}

:global(.volunteer-roster-identity) {
  display: grid;
  min-width: 0;
  gap: 0.2rem;
}

:global(.volunteer-roster-identity strong) {
  overflow-wrap: anywhere;
  color: var(--cr-navy-medium);
  font-size: 1rem;
  font-weight: 800;
}

:global(.volunteer-roster-hours) {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.42rem 0.65rem;
  border-radius: 999px;
  color: var(--cr-navy-medium);
  background: #eaf1f8;
  font-size: 0.88rem;
  font-weight: 800;
  white-space: nowrap;
}

:global(.volunteer-roster-hours i) {
  color: var(--cr-red);
}

:global(.volunteer-roster-actions) {
  position: static;
  z-index: auto;
  width: 100%;
  justify-content: flex-end;
  margin: 0;
  padding: 1rem 1.5rem 1.3rem;
}

:global(.volunteer-roster-popup.volunteer-photo-open .volunteer-roster-actions),
:global(.volunteer-roster-actions[hidden]) {
  display: none !important;
}

:global(.volunteer-roster-close) {
  min-width: 110px;
  padding: 0.65rem 1.1rem;
  border: 1px solid var(--cr-red);
  border-radius: 10px;
  color: var(--cr-white);
  background: var(--cr-red);
  font-weight: 700;
}

:global(.volunteer-roster-close:hover),
:global(.volunteer-roster-close:focus) {
  border-color: var(--cr-red-dark);
  background: var(--cr-red-dark);
  box-shadow: 0 0 0 3px rgba(224, 30, 30, 0.16);
}

:global(.enrollment-review-close) {
  width: 40px;
  height: 40px;
  margin: 0.75rem 0.75rem 0 0;
  border-radius: 50%;
  color: var(--cr-navy-soft);
  background: #eef1f5;
}

:global(.enrollment-review-list) {
  display: grid;
  gap: 0.8rem;
}

:global(.enrollment-review-item) {
  display: grid;
  gap: 0.9rem;
  padding: 1rem;
  border: 1px solid #dce5ef;
  border-left: 4px solid var(--cr-red);
  border-radius: 16px;
  background: #fbfcfe;
  text-align: left;
}

:global(.enrollment-review-identity) {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  color: var(--cr-navy-medium);
}

:global(.enrollment-review-identity > span) {
  width: 38px;
  height: 38px;
  flex: 0 0 auto;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  color: var(--cr-white);
  background: var(--cr-red);
}

:global(.enrollment-review-label) {
  display: grid;
  gap: 0.2rem;
  margin: 0;
  color: var(--cr-navy-medium);
  font-weight: 800;
}

:global(.enrollment-review-label small) {
  color: var(--cr-gray-600);
  font-size: 0.82rem;
  font-weight: 600;
}

:global(.enrollment-review-hours) {
  margin: 0;
  border-radius: 12px;
}

:global(.enrollment-review-hours:focus) {
  border-color: var(--cr-red);
  box-shadow: 0 0 0 3px rgba(224, 30, 30, 0.14);
}

:global(.enrollment-review-actions) {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem;
}

:global(.enrollment-review-action) {
  padding: 0.65rem 1rem;
  border: 0;
  border-radius: 999px;
  color: var(--cr-white);
  font-weight: 700;
}

:global(.enrollment-review-action--approve) {
  background: var(--cr-navy-medium);
}

:global(.enrollment-review-action--approve:hover),
:global(.enrollment-review-action--approve:focus) {
  color: var(--cr-white);
  background: var(--cr-navy-dark);
}

:global(.enrollment-review-action--reject) {
  background: var(--cr-red);
}

:global(.enrollment-review-action--reject:hover),
:global(.enrollment-review-action--reject:focus) {
  color: var(--cr-white);
  background: var(--cr-red-dark);
}

:global(.volunteer-roster-empty) {
  display: grid;
  justify-items: center;
  gap: 0.75rem;
  padding: 1rem 0;
  color: var(--cr-gray-600);
  text-align: center;
}

:global(.volunteer-roster-empty i) {
  color: var(--cr-red);
  font-size: 2rem;
}

:global(.volunteer-roster-empty p) {
  margin: 0;
}

@media (max-width: 480px) {
  :global(.volunteer-roster-title) {
    padding: 1.1rem 1rem 0.8rem;
  }

  :global(.volunteer-roster-content) {
    padding: 0 1rem;
  }

  :global(.volunteer-roster-item) {
    grid-template-columns: 38px minmax(0, 1fr);
    padding: 0.75rem;
  }

  :global(.volunteer-roster-avatar) {
    width: 38px;
    height: 38px;
  }

  :global(.volunteer-roster-hours) {
    grid-column: 2;
    justify-self: start;
  }

  :global(.volunteer-roster-actions) {
    padding: 0.9rem 1rem 1.1rem;
  }
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
  border: 1.5px solid var(--cr-blue);
  background: var(--cr-white);
  color: var(--cr-blue);
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
    box-shadow: 0 16px 30px var(--cr-navy-shadow);
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
    color: var(--cr-navy-ink);
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

  .activity-card__label i {
    width: 1.2rem;
    color: var(--cr-red);
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
    color: var(--cr-navy-ink);
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















