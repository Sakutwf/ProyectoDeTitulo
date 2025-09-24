<template>
  <div class="d-flex">
    <!-- Sidebar -->
    <SidebarMenu />

    <!-- Contenido principal -->
    <div class="content-wrapper">
      <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
          <h3 class="m-0"><i class="fa-solid fa-list me-2"></i>Gestión de Actividades</h3>
          <button class="btn btn-danger btn-sm" @click="abrirModalNuevaActividad">
            <i class="fa-solid fa-calendar-plus me-1"></i> Nueva Actividad
          </button>
        </div>
      </div>
      <div class="content">
        <div class="card shadow">
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-12 col-md-6">
                <input v-model="search" @input="onSearch" type="text" class="form-control" placeholder="Buscar por evento, tipo de evento o tipo de actividad...">
              </div>
            </div>
            <div class="table-responsive">
              <table class="table custom-table">
                <thead>
                  <tr>
                    <th class="fw-semibold">#</th>
                    <th class="fw-semibold">Nombre Evento</th>
                    <th class="fw-semibold">Fecha Inicio</th>
                    <th class="fw-semibold">Fecha Término</th>
                    <th class="fw-semibold">Descripción</th>
                    <th class="fw-semibold">Tipo Evento</th>
                    <th class="fw-semibold">Planilla</th>
                    <th class="fw-semibold">Tipo Actividad</th>
                    <th class="fw-semibold">N° Beneficiarios</th>
                    <th class="fw-semibold text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(actividad, i) in actividades" :key="actividad.id">
                    <td>{{ (meta.from || 1) + i }}</td>
                    <td>{{ actividad.evento?.nombre || '-' }}</td>
                    <td>{{ formatDate(actividad.evento?.fecha_inicio) }}</td>
                    <td>{{ formatDate(actividad.evento?.fecha_termino) }}</td>
                    <td>{{ actividad.evento?.descripcion || '-' }}</td>
                    <td>
                      <span class="badge bg-cruz-roja">{{ actividad.evento?.tipo || '-' }}</span>
                    </td>
                    <td>
                      <template v-if="actividad.users && actividad.users.length > 0">
                        <button class="btn btn-link p-0" @click="mostrarVoluntarios(actividad)" title="Ver voluntarios">
                          <i class="fa-solid fa-users text-primary"></i>
                        </button>
                      </template>
                      <template v-else>
                        <span
                          class="badge bg-cruz-roja"
                          style="cursor:pointer"
                          @click="abrirAgregarVoluntarios(actividad)"
                          title="Agregar voluntarios"
                        >
                          Agregar voluntarios
                        </span>
                      </template>
                    </td>
                    <td>
                      <span class="badge bg-secondary">{{ actividad.tipo }}</span>
                    </td>
                    <td>{{ actividad.N_beneficiarios }}</td>
                    <td>
                      <div class="d-flex justify-content-center">
                        <button @click="editActividad(actividad.id)" class="btn btn-sm btn-outline-primary me-2" title="Editar">
                          <i class="fa-solid fa-edit"></i>
                        </button>
                        <button @click="eliminarActividad(actividad.id)" class="btn btn-sm btn-outline-danger" title="Eliminar">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!actividades || actividades.length === 0">
                    <td colspan="10" class="text-center py-3">No hay actividades disponibles</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- Paginación alineada a la derecha -->
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
    <!-- Modal de edición de actividad -->
    <ActividadEditView
      v-if="selectedActividadId !== null"
      :actividadId="selectedActividadId"
      ref="actividadEditModal"
      @actividad-updated="onActividadUpdated"
    />
    <!-- Modal de creación de actividad -->
    <ActividadCreateView
      ref="actividadCreateModal"
      @actividad-created="onActividadCreated"
    />
    <!-- Modal para agregar voluntarios a la planilla -->
    <div class="modal fade" id="agregarVoluntariosModal" tabindex="-1" aria-labelledby="agregarVoluntariosModalLabel" aria-hidden="true" ref="agregarVoluntariosModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="agregarVoluntariosModalLabel">
              <i class="fa-solid fa-user-plus me-2"></i>Agregar voluntarios a la actividad
            </h5>
            <button type="button" class="btn btn-danger btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar">
              <i class="fa-solid fa-times"></i>
            </button>
          </div>
          <div class="modal-body">
            <div v-if="voluntariosDisponibles.length === 0">
              No hay voluntarios disponibles.
            </div>
            <div v-else>
              <div class="mb-2">Seleccione voluntarios:</div>
              <ul class="list-group">
                <li v-for="user in voluntariosDisponibles" :key="user.id" class="list-group-item d-flex align-items-center">
                  <input
                    class="form-check-input me-2"
                    type="checkbox"
                    :id="'voluntario-' + user.id"
                    :value="user.id"
                    v-model="voluntariosSeleccionados"
                  >
                  <label class="form-check-label" :for="'voluntario-' + user.id">
                    {{ user.nombre || user.name || user.email }}
                  </label>
                </li>
              </ul>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-danger" @click="guardarVoluntarios">
              <i class="fa-solid fa-save me-1"></i>Asociar Voluntarios
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import SidebarMenu from '../components/SidebarMenu.vue'
import ActividadEditView from './ActividadEditView.vue'
import ActividadCreateView from './ActividadCreateView.vue'
import Swal from 'sweetalert2'
import * as bootstrap from 'bootstrap'

const actividades = ref([])
const meta = ref({
  current_page: 1,
  last_page: 1,
  from: 1
})
const search = ref('')
const selectedActividadId = ref(null)

function formatDate(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString()
}

async function fetchActividades(page = 1) {
  let params = { page }
  if (search.value) params.search = search.value
  try {
    const res = await axios.get('http://localhost:8000/api/actividad', { params })
    actividades.value = res.data.data
    meta.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      from: res.data.from
    }
  } catch (e) {
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

function editActividad(id) {
  selectedActividadId.value = id
  setTimeout(() => {
    if (actividadEditModal.value && typeof actividadEditModal.value.show === 'function') {
      actividadEditModal.value.show()
    }
  }, 100)
}

function onActividadUpdated() {
  selectedActividadId.value = null
  fetchActividades(meta.value.current_page)
}

async function eliminarActividad(id) {
  const confirm = await Swal.fire({
    title: '¿Está seguro de que desea eliminar esta actividad?',
    text: "Esta acción no eliminará el evento asociado.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  })
  if (confirm.isConfirmed) {
    try {
      await axios.delete(`http://localhost:8000/api/actividad/${id}`)
      fetchActividades(meta.value.current_page)
      Swal.fire('Eliminado', 'La actividad ha sido eliminada.', 'success')
    } catch (e) {
      Swal.fire('Error', 'No se pudo eliminar la actividad.', 'error')
    }
  }
}

const actividadEditModal = ref(null)

function abrirModalNuevaActividad() {
  actividadCreateModal.value.show()
}

function onActividadCreated() {
  fetchActividades(meta.value.current_page)
}

const actividadCreateModal = ref(null)

function mostrarVoluntarios(actividad) {
  if (actividad.users && actividad.users.length) {
    const lista = actividad.users.map(u => {
      const nombre = u.nombre || u.name || u.email || `ID: ${u.id}`;
      return `<li>
        <a href="/usuarios/${u.id}/historial" target="_blank" style="text-decoration:underline;color:#222;">
          ${nombre}
        </a>
      </li>`;
    }).join('');
    Swal.fire({
      title: `Voluntarios asignados a la actividad (${actividad.users.length})`,
      html: `<label><ul style="list-style:none;padding-left:0">${lista}</ul></label>`,
      showConfirmButton: true,
      confirmButtonText: 'Cerrar',
      customClass: {
        confirmButton: 'btn btn-danger'
      },
      buttonsStyling: false
    });
  } else {
    Swal.fire({
      title: 'Voluntarios asignados a la actividad (0)',
      html: '<label>No hay voluntarios asignados.</label>',
      showConfirmButton: true,
      confirmButtonText: 'Cerrar',
      customClass: {
        confirmButton: 'btn btn-danger'
      },
      buttonsStyling: false
    });
  }
}

const agregarVoluntariosModal = ref(null)
const actividadSeleccionada = ref(null)
const voluntariosDisponibles = ref([])
const voluntariosSeleccionados = ref([])

function abrirAgregarVoluntarios(actividad) {
  // Guarda la actividad seleccionada y los usuarios ya asociados (si existen)
  actividadSeleccionada.value = actividad
  voluntariosSeleccionados.value = actividad.users ? actividad.users.map(u => u.id) : []
  // Cargar usuarios con rol voluntario
  axios.get('http://localhost:8000/api/users?role=voluntario')
    .then(res => {
      const users = Array.isArray(res.data)
        ? res.data
        : (res.data.data || []);
      voluntariosDisponibles.value = users
    })
    .catch(() => {
      voluntariosDisponibles.value = []
    })
    .finally(() => {
      const modal = new bootstrap.Modal(agregarVoluntariosModal.value)
      modal.show()
    })
}

async function guardarVoluntarios() {
  if (!actividadSeleccionada.value || voluntariosSeleccionados.value.length === 0) {
    Swal.fire('Seleccione al menos un voluntario', '', 'warning')
    return
  }
  try {
    // Asocia la lista de usuarios seleccionados a la actividad seleccionada
    await axios.put(`http://localhost:8000/api/actividad/${actividadSeleccionada.value.id}`, {
      planilla: voluntariosSeleccionados.value
    })
    const modal = bootstrap.Modal.getInstance(agregarVoluntariosModal.value)
    modal.hide()
    fetchActividades(meta.value.current_page)
    Swal.fire({
      icon: 'success',
      title: 'Voluntarios asociados correctamente',
      showConfirmButton: true,
      confirmButtonText: 'Cerrar'
    })
  } catch (e) {
    Swal.fire('Error', 'No se pudo agregar voluntarios', 'error')
  }
}

onMounted(() => fetchActividades())
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
  padding: 6px 12px;
  vertical-align: middle;
  border-bottom: 1px solid #e0e0e0;
  transition: background-color 0.2s;
  height: 36px;
}

.custom-table tbody tr:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

.card {
  border: none;
  border-radius: 8px;
  overflow: hidden;
}

.card-header {
  padding: 15px 20px;
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

.d-flex {
  display: flex;
}

.btn-outline-primary, .btn-outline-danger {
  border-radius: 4px;
  padding: 4px 8px;
}

.btn-outline-primary:hover {
  background-color: #e01e1e;
  border-color: #e01e1e;
  color: white;
}

.btn-outline-danger:hover {
  background-color: #f44336;
  color: white;
}

.badge.bg-cruz-roja {
  background-color: #e01e1e !important;
  color: #fff !important;
}

.cruz-roja-pagination .page-link {
  color: #e01e1e;
  font-weight: 600;
  border: 1px solid #e01e1e;
  background: #fff;
  border-radius: 6px;
  margin: 0 2px;
  transition: background 0.2s, color 0.2s;
}

.cruz-roja-pagination .page-link:focus {
  box-shadow: 0 0 0 0.15rem #e01e1e33;
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
  cursor: not-allowed;
}

.cruz-roja-pagination .page-link i {
  font-size: 0.95em;
  vertical-align: middle;
}

.btn-danger.btn-close-white {
  background-color: #e01e1e !important;
  border: none;
  color: #fff !important;
  border-radius: 0.25rem;
  padding: 0.375rem 0.75rem;
  font-size: 1.25rem;
  line-height: 1;
  opacity: 1;
  box-shadow: none;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-danger.btn-close-white:hover {
  background-color: #b71c1c !important;
  color: #fff !important;
}
</style>
