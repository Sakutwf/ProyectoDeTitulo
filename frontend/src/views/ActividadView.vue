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
                    <td>{{ actividad.planilla }}</td>
                    <td>
                      <span class="badge bg-secondary">{{ actividad.tipo }}</span>
                    </td>
                    <td>{{ actividad.n_beneficiarios }}</td>
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import SidebarMenu from '../components/SidebarMenu.vue'
import ActividadEditView from './ActividadEditView.vue'
import ActividadCreateView from './ActividadCreateView.vue'
import Swal from 'sweetalert2'

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
</style>
