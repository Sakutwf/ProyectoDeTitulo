<template>
  <div class="d-flex">
    <SidebarMenu />

    <div class="content-wrapper">
      <div class="content-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <h3 class="m-0"><i class="fa-solid fa-receipt me-2"></i>Gestión de boletas</h3>
          <span class="role-chip">{{ filialChipLabel }}</span>
        </div>
      </div>

      <div class="content">
        <div class="card shadow volunteer-card">
          <div class="card-body">
            <div class="section-copy">
              <h2>Boletas de la filial</h2>
              <p>
                Revisa todas las boletas subidas por los voluntarios de tu filial, actualiza su estado y prioriza las pendientes de pago.
              </p>
            </div>

            <section class="boleta-section-panel">
              <div class="boleta-section-panel__header">
                <div>
                  <p class="panel-kicker">Gestión administrativa</p>
                  <h3>Listado de boletas</h3>
                </div>

                <div class="summary-chips">
                  <span class="status-pill status-pill--warning">{{ requestedCount }} solicitado(s)</span>
                  <span class="status-pill status-pill--info">{{ approvedCount }} aprobado(s)</span>
                  <span class="status-pill status-pill--success">{{ paidCount }} pagado(s)</span>
                </div>
              </div>

              <div class="boleta-toolbar">
                <label class="search-field">
                  <span class="search-field__label">Buscar boleta</span>
                  <input
                    v-model.trim="searchTerm"
                    type="text"
                    class="form-control"
                    placeholder="Buscar por voluntario, boleta, actividad o estado"
                  >
                </label>

                <label class="search-field search-field--status">
                  <span class="search-field__label">Filtrar por estado</span>
                  <select v-model="statusFilter" class="form-select">
                    <option value="">Todos</option>
                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </option>
                  </select>
                </label>
              </div>
            </section>

            <div v-if="isLoading" class="empty-state mt-4">
              Cargando boletas registradas...
            </div>

            <template v-else>
              <div class="boletas-card-list">
                <article v-for="item in filteredBoletaItems" :key="`card-${item.id}`" class="boleta-card">
                  <div class="boleta-card__header">
                    <div class="boleta-card__heading">
                      <h4>{{ item.detalle_compra || 'Boleta sin nombre' }}</h4>
                      <p class="boleta-card__volunteer">{{ item.voluntario_nombre }}</p>
                      <span class="status-pill" :class="statusClass(item.estado)">{{ statusLabel(item.estado) }}</span>
                    </div>

                    <div class="boleta-card__actions">
                      <a :href="item.archivo_url" target="_blank" rel="noopener" class="btn btn-sm action-button" title="Ver boleta">
                        <i class="fa-solid fa-eye"></i>
                      </a>
                      <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar boleta" :disabled="deletingBoletaId === item.id" @click="deleteBoleta(item)">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </div>
                  </div>

                  <div class="boleta-card__details">
                    <div class="boleta-card__row">
                      <span class="boleta-card__label">Voluntario</span>
                      <strong>{{ item.voluntario_nombre }}</strong>
                    </div>
                    <div class="boleta-card__row">
                      <span class="boleta-card__label">Monto</span>
                      <strong>{{ formatCurrency(item.monto) }}</strong>
                    </div>
                    <div class="boleta-card__row">
                      <span class="boleta-card__label">Fecha compra</span>
                      <strong>{{ formatDate(item.fecha_compra) }}</strong>
                    </div>
                    <div class="boleta-card__row">
                      <span class="boleta-card__label">Actividad</span>
                      <strong>{{ item.actividad_nombre || 'Sin actividad' }}</strong>
                    </div>
                    <div class="boleta-card__row">
                      <span class="boleta-card__label">Fecha pago</span>
                      <strong>{{ formatDate(item.fecha_pago) }}</strong>
                    </div>
                    <div class="boleta-card__row boleta-card__row--stacked">
                      <span class="boleta-card__label">Estado</span>
                      <select
                        class="form-select status-select"
                        :value="normalizeStatus(item.estado)"
                        :disabled="updatingStatusId === item.id"
                        @change="updateBoletaStatus(item, $event.target.value)"
                      >
                        <option v-for="option in statusOptions" :key="`${item.id}-${option.value}`" :value="option.value">
                          {{ option.label }}
                        </option>
                      </select>
                    </div>
                  </div>
                </article>

                <div v-if="!filteredBoletaItems.length" class="boleta-card boleta-card--empty">
                  {{ emptyBoletasMessage }}
                </div>
              </div>

              <div class="table-shell table-shell--boletas">
                <div class="table-responsive boletas-table-shell">
                  <table class="table custom-table custom-table--responsive">
                    <thead>
                      <tr>
                        <th>Voluntario</th>
                        <th>Nombre</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Actividad</th>
                        <th>Estado</th>
                        <th>Fecha pago</th>
                        <th class="text-center">Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in filteredBoletaItems" :key="item.id">
                        <td data-label="Voluntario"><div class="fw-semibold">{{ item.voluntario_nombre }}</div></td>
                        <td data-label="Nombre"><div class="fw-semibold">{{ item.detalle_compra || '-' }}</div></td>
                        <td data-label="Monto">{{ formatCurrency(item.monto) }}</td>
                        <td data-label="Fecha">{{ formatDate(item.fecha_compra) }}</td>
                        <td data-label="Actividad">{{ item.actividad_nombre || '-' }}</td>
                        <td data-label="Estado">
                          <div class="status-cell">
                            <span class="status-pill" :class="statusClass(item.estado)">{{ statusLabel(item.estado) }}</span>
                            <select
                              class="form-select form-select-sm status-select"
                              :value="normalizeStatus(item.estado)"
                              :disabled="updatingStatusId === item.id"
                              @change="updateBoletaStatus(item, $event.target.value)"
                            >
                              <option v-for="option in statusOptions" :key="`${item.id}-table-${option.value}`" :value="option.value">
                                {{ option.label }}
                              </option>
                            </select>
                          </div>
                        </td>
                        <td data-label="Fecha pago">{{ formatDate(item.fecha_pago) }}</td>
                        <td data-label="Acciones">
                          <div class="d-flex justify-content-center actions-cell">
                            <a :href="item.archivo_url" target="_blank" rel="noopener" class="btn btn-sm action-button" title="Ver boleta">
                              <i class="fa-solid fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar boleta" :disabled="deletingBoletaId === item.id" @click="deleteBoleta(item)">
                              <i class="fa-solid fa-trash"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                      <tr v-if="!filteredBoletaItems.length" class="no-results-row">
                        <td colspan="8" class="text-center py-3 no-results-cell">{{ emptyBoletasMessage }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import SidebarMenu from '../components/SidebarMenu.vue'
import { API_BASE } from '../config/api'
import { show_alerta } from '../funciones'
import { useStore } from 'vuex'

const STATUS_META = {
  solicitado: { label: 'Solicitado', className: 'status-pill--warning' },
  aprobado: { label: 'Aprobado', className: 'status-pill--info' },
  pagado: { label: 'Pagado', className: 'status-pill--success' }
}

const statusOptions = Object.entries(STATUS_META).map(([value, meta]) => ({
  value,
  label: meta.label
}))

const store = useStore()
const currentUser = computed(() => store.getters.authUser)
const filialChipLabel = computed(() => {
  const filialName = currentUser.value?.voluntario?.filial?.nombre
  return filialName ? `Filial ${filialName}` : 'Gestión filial'
})

const boletaItems = ref([])
const isLoading = ref(false)
const updatingStatusId = ref(null)
const deletingBoletaId = ref(null)
const searchTerm = ref('')
const statusFilter = ref('')

const filteredBoletaItems = computed(() => {
  const term = searchTerm.value.trim().toLowerCase()
  const selectedStatus = statusFilter.value ? normalizeStatus(statusFilter.value) : ''

  return boletaItems.value.filter((item) => {
    if (selectedStatus && normalizeStatus(item.estado) !== selectedStatus) {
      return false
    }

    if (!term) {
      return true
    }

    const haystack = [
      item.detalle_compra,
      item.actividad_nombre,
      item.voluntario_nombre,
      item.filial_nombre,
      statusLabel(item.estado),
      formatDate(item.fecha_compra),
      formatDate(item.fecha_pago)
    ].join(' ').toLowerCase()

    return haystack.includes(term)
  })
})

const emptyBoletasMessage = computed(() =>
  searchTerm.value.trim() || statusFilter.value
    ? 'No hay boletas que coincidan con los filtros seleccionados.'
    : 'No hay boletas registradas para esta filial.'
)

const requestedCount = computed(() => boletaItems.value.filter((item) => normalizeStatus(item.estado) === 'solicitado').length)
const approvedCount = computed(() => boletaItems.value.filter((item) => normalizeStatus(item.estado) === 'aprobado').length)
const paidCount = computed(() => boletaItems.value.filter((item) => normalizeStatus(item.estado) === 'pagado').length)

function normalizeStatus(value) {
  const normalized = String(value || '').trim().toLowerCase()

  if (['pendiente', 'solicitado', 'solicitada'].includes(normalized)) {
    return 'solicitado'
  }

  if (['aprobado', 'aprobada'].includes(normalized)) {
    return 'aprobado'
  }

  if (['pagado', 'pagada'].includes(normalized)) {
    return 'pagado'
  }

  return 'solicitado'
}

function normalizeBoleta(item) {
  const normalizedStatus = normalizeStatus(item.estado)
  const volunteer = item.voluntario || {}
  const user = volunteer.user || {}

  return {
    ...item,
    estado: normalizedStatus,
    actividad_nombre: item.actividad?.nombre || item.actividad_nombre || 'Actividad sin nombre',
    voluntario_nombre: [volunteer.nombres, volunteer.apellidos].filter(Boolean).join(' ') || user.name || user.username || 'Voluntario sin nombre',
    filial_nombre: volunteer.filial?.nombre || ''
  }
}

function formatDate(value) {
  if (!value) return '-'
  const parsed = new Date(`${String(value).slice(0, 10)}T00:00:00`)
  if (Number.isNaN(parsed.getTime())) return String(value)
  return parsed.toLocaleDateString('es-CL')
}

function formatCurrency(value) {
  const numericValue = Number(value || 0)
  return numericValue.toLocaleString('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0
  })
}

function statusLabel(value) {
  return STATUS_META[normalizeStatus(value)]?.label || 'Solicitado'
}

function statusClass(value) {
  return STATUS_META[normalizeStatus(value)]?.className || 'status-pill--warning'
}

async function loadBoletas() {
  isLoading.value = true

  try {
    const response = await axios.get(`${API_BASE}/boletas/gestion`)
    const items = Array.isArray(response.data) ? response.data : []
    boletaItems.value = items.map(normalizeBoleta)
  } catch (error) {
    boletaItems.value = []
    const message = error.response?.data?.message || 'No se pudieron cargar las boletas de la filial.'
    show_alerta(message, 'error')
  } finally {
    isLoading.value = false
  }
}

async function updateBoletaStatus(item, nextStatus) {
  const normalizedStatus = normalizeStatus(nextStatus)

  if (!item?.id || normalizedStatus === normalizeStatus(item.estado)) {
    return
  }

  updatingStatusId.value = item.id

  try {
    await axios.put(`${API_BASE}/boletas/${item.id}/estado`, {
      estado: normalizedStatus
    })

    await loadBoletas()
    show_alerta('Estado de boleta actualizado correctamente.', 'success')
  } catch (error) {
    const message = error.response?.data?.message || 'No se pudo actualizar el estado de la boleta.'
    show_alerta(message, 'error')
  } finally {
    updatingStatusId.value = null
  }
}

async function deleteBoleta(item) {
  if (!item?.id || deletingBoletaId.value === item.id) {
    return
  }

  const confirm = await Swal.fire({
    title: 'Eliminar boleta',
    text: 'Esta acción quitará el respaldo registrado para toda la filial.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  })

  if (!confirm.isConfirmed) {
    return
  }

  deletingBoletaId.value = item.id

  try {
    await axios.delete(`${API_BASE}/boletas/${item.id}`)
    await loadBoletas()
    show_alerta('Boleta eliminada correctamente.', 'success')
  } catch (error) {
    const message = error.response?.data?.message || 'No se pudo eliminar la boleta.'
    show_alerta(message, 'error')
  } finally {
    deletingBoletaId.value = null
  }
}
onMounted(async () => {
  await loadBoletas()
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

.panel-kicker {
  margin: 0 0 0.3rem;
  color: #7d8ba0;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.78rem;
  font-weight: 800;
}

.boleta-section-panel {
  border: 1px solid #e2e8f0;
  border-radius: 22px;
  padding: 1.15rem;
  background: linear-gradient(180deg, #ffffff, #fbfcfe);
}

.boleta-section-panel__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.boleta-section-panel__header h3 {
  margin: 0;
  color: #163a69;
  font-size: 1.15rem;
  font-weight: 800;
}

.summary-chips {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 0.55rem;
}

.boleta-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-top: 1rem;
}

.search-field {
  display: grid;
  gap: 0.45rem;
  width: min(100%, 24rem);
}

.search-field--status {
  width: min(100%, 16rem);
}

.search-field__label {
  color: #5b6c80;
  font-size: 0.9rem;
  font-weight: 700;
}

.empty-state {
  border-radius: 18px;
  border: 1px dashed #d6dde7;
  background: #fafbfd;
  padding: 1.2rem;
  color: #65758a;
}

.table-shell {
  margin-top: 1.5rem;
}

.boletas-card-list {
  display: none;
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
  line-height: 1.25;
}

.custom-table td {
  padding: 8px 12px;
  vertical-align: middle;
  border-bottom: 1px solid #e0e0e0;
  white-space: normal;
  line-height: 1.35;
}

.actions-cell {
  flex-wrap: nowrap;
  gap: 0.55rem;
}

.actions-cell .btn,
.boleta-card__actions .btn,
.boleta-card__actions a {
  width: 44px;
  height: 44px;
  padding: 0;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.actions-cell .btn i,
.boleta-card__actions .btn i,
.boleta-card__actions a i {
  font-size: 1.05rem;
}

.action-button {
  border-color: #0f4c81;
  color: #0f4c81;
  background: #fff;
}

.action-button:hover,
.action-button:focus,
.action-button:active {
  border-color: #0c416d;
  color: #0c416d;
  background: #edf5fb;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 36px;
  padding: 0.35rem 0.8rem;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 800;
}

.status-pill--warning {
  background: #fff3cd;
  color: #7a5a00;
}

.status-pill--info {
  background: #dbeafe;
  color: #1d4ed8;
}

.status-pill--success {
  background: #dcfce7;
  color: #166534;
}

.status-cell {
  display: grid;
  gap: 0.45rem;
}

.status-select {
  min-width: 10rem;
}

.boleta-card__volunteer {
  margin: 0 0 0.55rem;
  color: #4a5c73;
  font-weight: 700;
}

@media (max-width: 1199.98px) {
  .table-shell--boletas {
    display: none;
  }

  .boletas-card-list {
    display: grid;
    gap: 1rem;
    margin-top: 1.5rem;
  }

  .boleta-card {
    border: 1px solid #e5eaf1;
    border-radius: 22px;
    padding: 1.1rem 1rem 1rem;
    background:
      linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 252, 255, 0.98)),
      radial-gradient(circle at top right, rgba(224, 30, 30, 0.08), transparent 38%);
    box-shadow: 0 16px 30px rgba(15, 47, 95, 0.08);
  }

  .boleta-card--empty {
    text-align: center;
    color: #64748b;
    font-weight: 600;
  }

  .boleta-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.8rem;
    margin-bottom: 0.95rem;
  }

  .boleta-card__heading h4 {
    margin: 0 0 0.35rem;
    color: #12284c;
    font-size: 1.45rem;
    line-height: 1.1;
    font-weight: 800;
  }

  .boleta-card__actions {
    display: flex;
    gap: 0.45rem;
    flex-shrink: 0;
  }

  .boleta-card__details {
    display: grid;
  }

  .boleta-card__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.8rem;
    padding: 0.92rem 0;
    border-top: 1px solid #e6edf5;
  }

  .boleta-card__row--stacked {
    display: grid;
    justify-content: stretch;
  }

  .boleta-card__row:first-child {
    border-top: none;
    padding-top: 0;
  }

  .boleta-card__label {
    color: #334155;
    font-size: 1rem;
  }

  .status-select {
    width: 100%;
    min-width: 0;
  }
}

@media (max-width: 767.98px) {
  .content-header {
    padding: 1rem 1rem 0.9rem;
    margin-bottom: 1rem;
  }

  .content {
    padding: 0 1rem 1rem;
  }

  .boleta-section-panel__header,
  .boleta-toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .search-field,
  .search-field--status {
    width: 100%;
  }

  .summary-chips {
    justify-content: flex-start;
  }
}
</style>



