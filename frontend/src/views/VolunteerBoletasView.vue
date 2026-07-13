<template>
  <div class="d-flex">
    <SidebarMenu />

    <div class="content-wrapper">
      <div class="content-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <h3 class="m-0"><i class="fa-solid fa-receipt me-2"></i>Mis boletas</h3>
          <span class="role-chip">Rendición personal</span>
        </div>
      </div>

      <div class="content">
        <div class="card shadow volunteer-card">
          <div class="card-body">
            <div class="section-copy">
              <h2>Boletas/Viático</h2>
              <p>
                Registra y administra tus boletas asociándolas a cualquiera de las actividades en las que participas.
              </p>
            </div>

            <section class="boleta-section-panel">
              <div class="boleta-section-panel__header">
                <div>
                  <p class="panel-kicker">Mis registros</p>
                  <h3>Listado de boletas</h3>
                </div>

                <button
                  v-if="enrolledActivities.length"
                  type="button"
                  class="btn btn-danger"
                  @click="openCreateModal"
                >
                  <i class="fa-solid fa-plus me-2"></i>Añadir boleta
                </button>
              </div>

              <div v-if="isLoading" class="empty-state empty-state--nested">
                Cargando actividades disponibles...
              </div>

              <div v-else-if="enrolledActivities.length === 0" class="empty-state empty-state--nested">
                Necesitas estar inscrito en una actividad para registrar boletas.
              </div>

              <div v-else class="boleta-toolbar">
                <label class="search-field">
                  <span class="search-field__label">Buscar boleta</span>
                  <input
                    v-model.trim="searchTerm"
                    type="text"
                    class="form-control"
                    placeholder="Buscar por nombre, actividad o estado"
                  >
                </label>
              </div>
            </section>

            <div v-if="isLoadingBoletas" class="empty-state mt-4">
              Cargando boletas registradas...
            </div>

            <template v-else>
              <div class="boletas-card-list">
                <article v-for="item in filteredBoletaItems" :key="`card-${item.id}`" class="boleta-card">
                  <div class="boleta-card__header">
                    <div class="boleta-card__heading">
                      <h4>{{ item.detalle_compra || 'Boleta sin nombre' }}</h4>
                      <span class="state-label" :class="statusClass(item.estado)">{{ statusLabel(item.estado) }}</span>
                    </div>

                    <div class="boleta-card__actions">
                      <button type="button" class="btn btn-sm action-button" title="Editar" @click="openEditModal(item)">
                        <i class="fa-solid fa-edit"></i>
                      </button>
                      <button type="button" class="btn btn-sm action-button" title="Ver boleta" @click="openEvidenceModal(item)">
                        <i class="fa-solid fa-eye"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" @click="deleteBoleta(item)">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </div>
                  </div>

                  <div class="boleta-card__details">
                    <div class="boleta-card__row">
                      <span class="boleta-card__label">Monto</span>
                      <strong>{{ formatCurrency(item.monto) }}</strong>
                    </div>
                    <div class="boleta-card__row">
                      <span class="boleta-card__label">Fecha</span>
                      <strong>{{ formatDate(item.fecha_compra) }}</strong>
                    </div>
                    <div class="boleta-card__row">
                      <span class="boleta-card__label">Actividad</span>
                      <strong>{{ item.actividad_nombre || 'Sin actividad' }}</strong>
                    </div>
                    <div v-if="item.motivo_revision" class="boleta-card__row">
                      <span class="boleta-card__label">Motivo de revisión</span>
                      <strong>{{ item.motivo_revision }}</strong>
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
                        <th>Nombre</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Actividad</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in filteredBoletaItems" :key="item.id">
                        <td data-label="Nombre"><div class="fw-semibold">{{ item.detalle_compra || '-' }}</div></td>
                        <td data-label="Monto">{{ formatCurrency(item.monto) }}</td>
                        <td data-label="Fecha">{{ formatDate(item.fecha_compra) }}</td>
                        <td data-label="Actividad">{{ item.actividad_nombre || '-' }}</td>
                        <td data-label="Estado">
                          <span class="state-label" :class="statusClass(item.estado)">{{ statusLabel(item.estado) }}</span>
                          <small v-if="item.motivo_revision" class="d-block mt-1">{{ item.motivo_revision }}</small>
                        </td>
                        <td data-label="Acciones">
                          <div class="d-flex justify-content-center actions-cell">
                            <button type="button" class="btn btn-sm action-button" title="Editar" @click="openEditModal(item)">
                              <i class="fa-solid fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm action-button" title="Ver boleta" @click="openEvidenceModal(item)">
                              <i class="fa-solid fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" @click="deleteBoleta(item)">
                              <i class="fa-solid fa-trash"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                      <tr v-if="!filteredBoletaItems.length" class="no-results-row">
                        <td colspan="6" class="text-center py-3 no-results-cell">{{ emptyBoletasMessage }}</td>
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

    <div v-if="isCreateModalOpen" class="edit-modal-backdrop" @click.self="closeCreateModal">
      <div class="edit-modal-card">
        <div class="edit-modal-card__header">
          <div>
            <p class="panel-kicker">Crear boleta</p>
            <h4>Nueva boleta</h4>
          </div>
          <button type="button" class="btn-close" aria-label="Cerrar" @click="closeCreateModal"></button>
        </div>

        <div class="edit-modal-card__body">
          <select v-model="createForm.actividad_id" class="form-select">
            <option value="">Selecciona una actividad</option>
            <option v-for="actividad in enrolledActivities" :key="actividad.id" :value="String(actividad.id)">
              {{ actividad.nombre || 'Actividad sin nombre' }}
            </option>
          </select>

          <input
            v-model.trim="createForm.detalle_compra"
            type="text"
            class="form-control"
            placeholder="Nombre o detalle"
          >

          <input
            v-model.number="createForm.monto"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            placeholder="Monto"
          >

          <input
            v-model="createForm.fecha_compra"
            type="date"
            class="form-control"
          >

          <div class="file-field">
            <label class="file-field__label">Seleccionar archivo</label>
            <input
              type="file"
              class="form-control"
              accept=".jpg,.jpeg,.png,.webp,.pdf"
              @change="onCreateFileSelected"
            >
          </div>
        </div>

        <div class="edit-modal-card__actions">
          <button type="button" class="btn btn-outline-secondary" @click="closeCreateModal">Cancelar</button>
          <button type="button" class="btn btn-danger" :disabled="createSubmitting || !isCreateFormValid" @click="createBoleta">
            {{ createSubmitting ? 'Guardando...' : 'Crear boleta' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="isEditModalOpen" class="edit-modal-backdrop" @click.self="closeEditModal">
      <div class="edit-modal-card">
        <div class="edit-modal-card__header">
          <div>
            <p class="panel-kicker">Editar boleta</p>
            <h4>{{ editForm.detalle_compra || 'Boleta' }}</h4>
          </div>
          <button type="button" class="btn-close" aria-label="Cerrar" @click="closeEditModal"></button>
        </div>

        <div class="edit-modal-card__body">
          <select v-model="editForm.actividad_id" class="form-select">
            <option value="">Selecciona una actividad</option>
            <option v-for="actividad in enrolledActivities" :key="`edit-${actividad.id}`" :value="String(actividad.id)">
              {{ actividad.nombre || 'Actividad sin nombre' }}
            </option>
          </select>

          <input
            v-model.trim="editForm.detalle_compra"
            type="text"
            class="form-control"
            placeholder="Nombre o detalle"
          >

          <input
            v-model.number="editForm.monto"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            placeholder="Monto"
          >

          <input
            v-model="editForm.fecha_compra"
            type="date"
            class="form-control"
          >

          <div v-if="editCurrentFileUrl" class="current-file-panel">
            <span class="current-file-panel__label">Archivo actual</span>
            <img v-if="editCurrentFileIsImage" :src="editCurrentFileUrl" alt="Boleta actual" class="current-file-panel__image">
            <a v-else :href="editCurrentFileUrl" target="_blank" rel="noopener" class="current-file-panel__link">
              Ver archivo actual
            </a>
          </div>

          <div class="file-field">
            <label class="file-field__label">Modificar imagen</label>
            <input
              type="file"
              class="form-control"
              accept=".jpg,.jpeg,.png,.webp,.pdf"
              @change="onEditFileSelected"
            >
          </div>
        </div>

        <div class="edit-modal-card__actions">
          <button type="button" class="btn btn-outline-secondary" @click="closeEditModal">Cancelar</button>
          <button type="button" class="btn btn-danger" :disabled="editSubmitting || !isEditFormValid" @click="updateBoleta">
            {{ editSubmitting ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </div>
    </div>
    <BoletaEvidenceModal v-if="evidenceBoleta" :boleta="evidenceBoleta" @close="closeEvidenceModal" />
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import SidebarMenu from '../components/SidebarMenu.vue'
import BoletaEvidenceModal from '../components/BoletaEvidenceModal.vue'
import { API_BASE } from '../config/api'
import { show_alerta } from '../funciones'
import { useStore } from 'vuex'

const store = useStore()
const currentUser = computed(() => store.getters.authUser)
const currentVolunteerId = computed(() => currentUser.value?.voluntario?.id || null)

const activities = ref([])
const boletaItems = ref([])
const isLoading = ref(false)
const isLoadingBoletas = ref(false)
const createSubmitting = ref(false)
const editSubmitting = ref(false)
const isCreateModalOpen = ref(false)
const isEditModalOpen = ref(false)
const selectedBoletaId = ref(null)
const selectedBoletaItem = ref(null)
const searchTerm = ref('')
const editPreviewUrl = ref('')
const evidenceBoleta = ref(null)

function openEvidenceModal(item) {
  if (item?.archivo_url) evidenceBoleta.value = item
}

function closeEvidenceModal() {
  evidenceBoleta.value = null
}

const createForm = ref(createEmptyCreateForm())
const editForm = ref(createEmptyEditForm())

const enrolledActivities = computed(() =>
  activities.value
    .filter((actividad) => (actividad.voluntarios || []).some((volunteer) => Number(volunteer.id) === Number(currentVolunteerId.value)))
    .sort((left, right) => String(right.fecha_inicio || '').localeCompare(String(left.fecha_inicio || '')))
)

const filteredBoletaItems = computed(() => {
  const term = searchTerm.value.trim().toLowerCase()

  if (!term) {
    return boletaItems.value
  }

  return boletaItems.value.filter((item) => {
    const haystack = [
      item.detalle_compra,
      item.actividad_nombre,
      statusLabel(item.estado),
      formatDate(item.fecha_compra)
    ].join(' ').toLowerCase()

    return haystack.includes(term)
  })
})

const emptyBoletasMessage = computed(() =>
  searchTerm.value.trim()
    ? 'No hay boletas que coincidan con la búsqueda.'
    : 'Aún no has registrado boletas.'
)

const isCreateFormValid = computed(() =>
  Boolean(
    createForm.value.actividad_id &&
    createForm.value.file &&
    createForm.value.detalle_compra.trim() &&
    Number(createForm.value.monto) > 0
  )
)

const isEditFormValid = computed(() =>
  Boolean(
    selectedBoletaId.value &&
    editForm.value.actividad_id &&
    editForm.value.detalle_compra.trim() &&
    Number(editForm.value.monto) > 0
  )
)

const editCurrentFileUrl = computed(() => editPreviewUrl.value || selectedBoletaItem.value?.archivo_url || '')
const editCurrentFileIsImage = computed(() => isImageBoleta(selectedBoletaItem.value, editCurrentFileUrl.value))

function createEmptyCreateForm() {
  return {
    actividad_id: '',
    detalle_compra: '',
    monto: '',
    fecha_compra: '',
    file: null
  }
}

function createEmptyEditForm() {
  return {
    actividad_id: '',
    detalle_compra: '',
    monto: '',
    fecha_compra: '',
    file: null
  }
}

function normalizeBoleta(item, actividad) {
  return {
    ...item,
    actividad_id: item.actividad_id || actividad.id,
    actividad_nombre: item.actividad?.nombre || actividad.nombre || 'Actividad sin nombre'
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

function normalizeBoletaStatus(value) {
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

  if (['rechazado', 'rechazada'].includes(normalized)) {
    return 'rechazado'
  }

  return 'solicitado'
}

function statusLabel(value) {
  const normalized = normalizeBoletaStatus(value)

  if (normalized === 'pagado') {
    return 'Pagado'
  }

  if (normalized === 'aprobado') {
    return 'Aprobado'
  }

  if (normalized === 'rechazado') {
    return 'Rechazado'
  }

  return 'Solicitado'
}

function statusClass(value) {
  const normalized = normalizeBoletaStatus(value)

  if (normalized === 'pagado') {
    return 'state-label--neutral'
  }

  if (normalized === 'aprobado') {
    return 'state-label--approved'
  }

  if (normalized === 'rechazado') {
    return 'state-label--neutral'
  }

  return 'state-label--requested'
}

function isImageBoleta(item, url) {
  const mimeType = String(item?.archivo?.mime_type || '').toLowerCase()
  if (mimeType.startsWith('image/')) {
    return true
  }

  return /\.(jpg|jpeg|png|webp)(\?|$)/i.test(String(url || ''))
}

function revokeEditPreview() {
  if (editPreviewUrl.value?.startsWith('blob:')) {
    URL.revokeObjectURL(editPreviewUrl.value)
  }
  editPreviewUrl.value = ''
}

async function loadActivities() {
  if (!currentVolunteerId.value) {
    activities.value = []
    boletaItems.value = []
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

async function loadBoletas() {
  if (!currentVolunteerId.value) {
    boletaItems.value = []
    return
  }

  isLoadingBoletas.value = true

  try {
    const responses = await Promise.all(
      enrolledActivities.value.map((actividad) =>
        axios.get(`${API_BASE}/actividad/${actividad.id}/boletas`, {
          params: { voluntario_id: currentVolunteerId.value }
        })
      )
    )

    boletaItems.value = responses
      .flatMap((response, index) => {
        const actividad = enrolledActivities.value[index]
        const items = Array.isArray(response.data) ? response.data : []
        return items.map((item) => normalizeBoleta(item, actividad))
      })
      .sort((left, right) => {
        const dateCompare = String(right.fecha_compra || '').localeCompare(String(left.fecha_compra || ''))
        if (dateCompare !== 0) {
          return dateCompare
        }

        return Number(right.id) - Number(left.id)
      })
  } catch (error) {
    boletaItems.value = []
    show_alerta('No se pudieron cargar tus boletas.', 'error')
  } finally {
    isLoadingBoletas.value = false
  }
}

function openCreateModal() {
  createForm.value = createEmptyCreateForm()
  isCreateModalOpen.value = true
}

function closeCreateModal() {
  isCreateModalOpen.value = false
  createForm.value = createEmptyCreateForm()
}

function onCreateFileSelected(event) {
  createForm.value.file = event.target.files?.[0] || null
}

function onEditFileSelected(event) {
  const nextFile = event.target.files?.[0] || null
  editForm.value.file = nextFile
  revokeEditPreview()

  if (nextFile) {
    editPreviewUrl.value = URL.createObjectURL(nextFile)
  }
}

async function createBoleta() {
  if (!isCreateFormValid.value) {
    show_alerta('Completa la actividad, nombre, monto y archivo de la boleta.', 'warning')
    return
  }

  createSubmitting.value = true

  try {
    const formData = new FormData()
    formData.append('voluntario_id', String(currentVolunteerId.value))
    formData.append('archivo', createForm.value.file)
    formData.append('detalle_compra', createForm.value.detalle_compra.trim())
    formData.append('monto', String(createForm.value.monto))

    if (createForm.value.fecha_compra) {
      formData.append('fecha_compra', createForm.value.fecha_compra)
    }

    await axios.post(`${API_BASE}/actividad/${createForm.value.actividad_id}/boletas`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    closeCreateModal()
    await loadBoletas()
    show_alerta('Boleta registrada correctamente.', 'success')
  } catch (error) {
    const message = error.response?.data?.message || 'No se pudo registrar la boleta.'
    show_alerta(message, 'error')
  } finally {
    createSubmitting.value = false
  }
}

function openEditModal(item) {
  revokeEditPreview()
  selectedBoletaId.value = item.id
  selectedBoletaItem.value = item
  editForm.value = {
    actividad_id: String(item.actividad_id || ''),
    detalle_compra: item.detalle_compra || '',
    monto: item.monto || '',
    fecha_compra: item.fecha_compra ? String(item.fecha_compra).slice(0, 10) : '',
    file: null
  }
  isEditModalOpen.value = true
}

function closeEditModal() {
  isEditModalOpen.value = false
  selectedBoletaId.value = null
  selectedBoletaItem.value = null
  editForm.value = createEmptyEditForm()
  revokeEditPreview()
}

async function updateBoleta() {
  if (!isEditFormValid.value || !selectedBoletaId.value) {
    show_alerta('Completa los datos requeridos de la boleta.', 'warning')
    return
  }

  editSubmitting.value = true

  try {
    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('actividad_id', editForm.value.actividad_id)
    formData.append('detalle_compra', editForm.value.detalle_compra.trim())
    formData.append('monto', String(editForm.value.monto))

    if (editForm.value.fecha_compra) {
      formData.append('fecha_compra', editForm.value.fecha_compra)
    }

    if (editForm.value.file) {
      formData.append('archivo', editForm.value.file)
    }

    await axios.post(`${API_BASE}/boletas/${selectedBoletaId.value}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    closeEditModal()
    await loadBoletas()
    show_alerta('Boleta actualizada correctamente.', 'success')
  } catch (error) {
    const message = error.response?.data?.message || 'No se pudo actualizar la boleta.'
    show_alerta(message, 'error')
  } finally {
    editSubmitting.value = false
  }
}

async function deleteBoleta(item) {
  const confirm = await Swal.fire({
    title: 'Eliminar boleta',
    text: 'Esta acción quitará el respaldo registrado.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  })

  if (!confirm.isConfirmed) {
    return
  }

  try {
    await axios.delete(`${API_BASE}/boletas/${item.id}`)
    await loadBoletas()
    show_alerta('Boleta eliminada correctamente.', 'success')
  } catch (error) {
    const message = error.response?.data?.message || 'No se pudo eliminar la boleta.'
    show_alerta(message, 'error')
  }
}

onMounted(async () => {
  await loadActivities()
  await loadBoletas()
})

onBeforeUnmount(() => {
  revokeEditPreview()
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

.boleta-toolbar {
  margin-top: 1rem;
}

.search-field {
  display: grid;
  gap: 0.45rem;
  width: min(100%, 24rem);
}

.search-field__label,
.file-field__label,
.current-file-panel__label {
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

.empty-state--nested {
  margin-top: 0.75rem;
}

.table-shell {
  margin-top: 1.5rem;
}

.boletas-card-list {
  display: none;
}

.custom-table {
  width: 100%;
  min-width: 68rem;
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

.state-label {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  min-height: 38px;
  padding: 0.45rem 0.95rem;
  border: 1.5px solid currentColor;
  border-radius: 0.9rem;
  background: #fff;
  font-size: 0.85rem;
  font-weight: 800;
  line-height: 1;
  white-space: nowrap;
}

.state-label::before {
  width: 0.48rem;
  height: 0.48rem;
  flex: 0 0 auto;
  border-radius: 50%;
  background: currentColor;
  content: '';
}

.state-label--requested {
  color: #e01e1e;
}

.state-label--approved {
  color: #0f2f5f;
}

.state-label--neutral {
  color: #667085;
}

.edit-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.48);
  display: grid;
  place-items: center;
  padding: 1rem;
  z-index: 1600;
}

.edit-modal-card {
  width: min(560px, 100%);
  border-radius: 24px;
  background: #fff;
  box-shadow: 0 24px 64px rgba(15, 23, 42, 0.24);
  overflow: hidden;
}

.edit-modal-card__header,
.edit-modal-card__actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.2rem;
}

.edit-modal-card__header {
  border-bottom: 1px solid #e5ebf2;
}

.edit-modal-card__header h4 {
  margin: 0;
  color: #163a69;
  font-weight: 800;
}

.edit-modal-card__body {
  padding: 1.1rem 1.2rem;
  display: grid;
  gap: 0.85rem;
}

.edit-modal-card__actions {
  border-top: 1px solid #e5ebf2;
}

.file-field,
.current-file-panel {
  display: grid;
  gap: 0.5rem;
}

.current-file-panel {
  padding: 0.85rem;
  border: 1px solid #e5ebf2;
  border-radius: 16px;
  background: #f8fafc;
}

.current-file-panel__image {
  width: 100%;
  max-height: 220px;
  object-fit: contain;
  border-radius: 12px;
  background: #fff;
}

.current-file-panel__link {
  color: #0f4c81;
  font-weight: 700;
  text-decoration: none;
}

.current-file-panel__link:hover {
  text-decoration: underline;
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
    margin: 0 0 0.55rem;
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

  .boleta-card__row:first-child {
    border-top: none;
    padding-top: 0;
  }

  .boleta-card__label {
    color: #334155;
    font-size: 1rem;
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
  .edit-modal-card__actions {
    flex-direction: column;
    align-items: stretch;
  }

  .search-field {
    width: 100%;
  }

  .edit-modal-card__header,
  .edit-modal-card__body,
  .edit-modal-card__actions {
    padding-inline: 1rem;
  }
}
</style>

