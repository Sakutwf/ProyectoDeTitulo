<template>
  <div class="d-flex">
    <SidebarMenu />

    <div class="content-wrapper">
      <div class="content-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <h3 class="m-0"><i class="fa-solid fa-list me-2"></i>Gestion de actividades</h3>
          <button class="btn btn-danger btn-sm" @click="openCreateModal">
            <i class="fa-solid fa-calendar-plus me-1"></i>Nueva actividad
          </button>
        </div>
      </div>

      <div class="content">
        <div class="card shadow">
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-12 col-md-6">
                <input
                  v-model="search"
                  @input="onSearch"
                  type="text"
                  class="form-control"
                  placeholder="Buscar por nombre, tipo, lugar o colaborador externo..."
                >
              </div>
            </div>

            <div class="table-responsive">
              <table class="table custom-table custom-table--responsive">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Filial</th>
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
                    <td>
                      <div class="fw-semibold">{{ actividad.nombre || '-' }}</div>
                      <small class="text-muted">{{ actividad.objetivo || 'Sin objetivo registrado' }}</small>
                    </td>
                    <td data-label="Tipo"><span class="badge bg-secondary">{{ actividad.tipo || '-' }}</span></td>
                    <td data-label="Filial">{{ actividad.filial?.nombre || '-' }}</td>
                    <td data-label="Fechas">{{ formatDateRange(actividad.fecha_inicio, actividad.fecha_termino) }}</td>
                    <td data-label="Lugar">{{ actividad.lugar || '-' }}</td>
                    <td data-label="Horas">{{ formatHours(actividad.horas_totales) }}</td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary" @click="showVolunteers(actividad)">
                        {{ actividad.voluntarios?.length || 0 }} voluntario(s)
                      </button>
                    </td>
                    <td>
                      <div class="d-flex justify-content-center">
                        <button @click="editActividad(actividad.id)" class="btn btn-sm btn-outline-primary me-2" title="Editar">
                          <i class="fa-solid fa-edit"></i>
                        </button>
                        <button @click="deleteActividad(actividad.id)" class="btn btn-sm btn-outline-danger" title="Eliminar">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!actividades.length" class="no-results-row">
                    <td colspan="9" class="text-center py-3 no-results-cell">No hay actividades disponibles</td>
                  </tr>
                </tbody>
              </table>
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
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import SidebarMenu from '../components/SidebarMenu.vue'
import { API_BASE } from '../config/api'
import ActividadEditView from './ActividadEditView.vue'
import ActividadCreateView from './ActividadCreateView.vue'

const actividades = ref([])
const meta = ref({ current_page: 1, last_page: 1, from: 1 })
const search = ref('')
const selectedActividadId = ref(null)
const actividadEditModal = ref(null)
const actividadCreateModal = ref(null)

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
  } catch (error) {
    actividades.value = []
    meta.value = { current_page: 1, last_page: 1, from: 1 }
  }
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
  setTimeout(() => {
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
  return `${numericValue % 1 === 0 ? numericValue.toFixed(0) : numericValue.toFixed(2)} h`
}

onMounted(() => {
  fetchActividades()
})
</script>

<style scoped>
.custom-table {
  border-collapse: separate;
  border-spacing: 0;
}

.custom-table th {
  font-size: 0.9rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  padding: 10px 12px;
  color: #333;
  background-color: #f5f5f5;
}

.custom-table td {
  padding: 8px 12px;
  vertical-align: middle;
  border-bottom: 1px solid #e0e0e0;
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

@media (max-width: 767.98px) {
  .table-responsive {
    overflow: visible;
  }

  .custom-table--responsive,
  .custom-table--responsive tbody,
  .custom-table--responsive tr,
  .custom-table--responsive td {
    display: block;
    width: 100% !important;
  }

  .custom-table--responsive thead {
    display: none;
  }

  .custom-table--responsive tbody {
    display: grid;
    gap: 0.85rem;
  }

  .custom-table--responsive tr {
    padding: 0.95rem;
    border: 1px solid #dfe7f1;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 10px 24px rgba(15, 47, 95, 0.06);
  }

  .custom-table--responsive td {
    display: grid;
    grid-template-columns: minmax(6.8rem, 8.5rem) minmax(0, 1fr);
    gap: 0.7rem;
    padding: 0.3rem 0;
    border-bottom: none;
    text-align: left !important;
  }

  .custom-table--responsive td::before {
    content: attr(data-label);
    color: #71829a;
    font-size: 0.77rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  .custom-table--responsive td[data-label="Voluntarios"],
  .custom-table--responsive td[data-label="Acciones"] {
    grid-template-columns: 1fr;
    gap: 0.45rem;
  }

  .actions-cell {
    justify-content: flex-start !important;
  }

  .custom-table--responsive tr.no-results-row {
    padding: 0;
    border: none;
    background: transparent;
    box-shadow: none;
  }

  .custom-table--responsive .no-results-cell {
    display: block;
    padding: 1rem 0;
  }

  .custom-table--responsive .no-results-cell::before {
    content: none;
  }
}
</style>


