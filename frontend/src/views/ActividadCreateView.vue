<template>
  <div class="modal fade" id="newActividadModal" tabindex="-1" aria-labelledby="newActividadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="newActividadModalLabel">
            <i class="fa-solid fa-calendar-plus me-2"></i>Nueva actividad
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <form @submit.prevent="saveActivity">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Filial</label>
                <select v-model="form.filial_id" class="form-select" required>
                  <option value="">Seleccione una filial</option>
                  <option v-for="filial in filiales" :key="filial.id" :value="filial.id">
                    {{ filial.nombre }}
                  </option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Tipo</label>
                <select v-model="form.tipo" class="form-select" required>
                  <option value="">Seleccione un tipo</option>
                  <option v-for="option in ACTIVITY_TYPE_OPTIONS" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>

              <div class="col-md-8">
                <label class="form-label">Nombre</label>
                <input v-model.trim="form.nombre" type="text" class="form-control" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Horas totales</label>
                <input v-model="form.horas_totales" type="number" min="0" step="0.25" class="form-control">
              </div>

              <div class="col-md-6">
                <label class="form-label">Fecha inicio</label>
                <input v-model="form.fecha_inicio" type="date" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Fecha término</label>
                <input v-model="form.fecha_termino" type="date" class="form-control" :min="form.fecha_inicio || null">
              </div>

              <div class="col-md-6">
                <label class="form-label">Hora inicio</label>
                <input v-model="form.hora_inicio" type="time" class="form-control">
              </div>

              <div class="col-md-6">
                <label class="form-label">Hora término</label>
                <input v-model="form.hora_termino" type="time" class="form-control">
              </div>

              <div class="col-md-6">
                <label class="form-label">Lugar</label>
                <input v-model.trim="form.lugar" type="text" class="form-control">
              </div>

              <div class="col-md-6">
                <label class="form-label">Colaborador externo</label>
                <input v-model.trim="form.colaborador_externo" type="text" class="form-control">
              </div>

              <div class="col-12">
                <label class="form-label">Objetivo</label>
                <textarea v-model.trim="form.objetivo" class="form-control" rows="3"></textarea>
              </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
              <div>
                <h6 class="mb-1">Voluntarios asociados</h6>
                <small class="text-muted">Puedes dejarlo vacío y asociarlos después.</small>
              </div>
            </div>

            <div v-if="volunteers.length" class="volunteer-grid">
              <article v-for="volunteer in volunteers" :key="volunteer.id" class="volunteer-card">
                <label class="form-check d-flex align-items-start gap-2">
                  <input
                    :checked="isSelected(volunteer.id)"
                    class="form-check-input mt-1"
                    type="checkbox"
                    @change="toggleVolunteer(volunteer.id)"
                  >
                  <span>
                    <strong>{{ fullVolunteerName(volunteer) }}</strong>
                    <small class="d-block text-muted">Registro filial {{ volunteer.registro_filial }}</small>
                  </span>
                </label>

                <div v-if="isSelected(volunteer.id)" class="mt-3">
                  <label class="form-label">Horas asistidas</label>
                  <input
                    v-model="volunteerHours[volunteer.id]"
                    type="number"
                    min="0"
                    step="0.25"
                    class="form-control"
                  >
                </div>
              </article>
            </div>

            <div v-else class="empty-state">
              No hay voluntarios disponibles para asociar.
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" :disabled="isSubmitting || !isFormValid" @click="saveActivity">
            <i class="fa-solid fa-save me-1"></i>{{ isSubmitting ? 'Guardando...' : 'Guardar actividad' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { Modal } from 'bootstrap'
import { ACTIVITY_TYPE_OPTIONS } from '../constants/activityTypes'
import { show_alerta } from '../funciones'

const API_BASE = 'http://localhost:8000/api'

export default {
  name: 'ActividadCreateView',
  emits: ['actividad-created'],
  data() {
    return {
      ACTIVITY_TYPE_OPTIONS,
      modalInstance: null,
      isSubmitting: false,
      filiales: [],
      volunteers: [],
      selectedVolunteers: [],
      volunteerHours: {},
      form: this.defaultForm()
    }
  },
  computed: {
    isFormValid() {
      return Boolean(this.form.filial_id && this.form.tipo && this.form.nombre && this.form.fecha_inicio)
    }
  },
  mounted() {
    this.modalInstance = new Modal(document.getElementById('newActividadModal'))
    this.loadCatalogs()
  },
  methods: {
    defaultForm() {
      return {
        filial_id: '',
        nombre: '',
        tipo: '',
        objetivo: '',
        fecha_inicio: '',
        fecha_termino: '',
        hora_inicio: '',
        hora_termino: '',
        lugar: '',
        horas_totales: '',
        colaborador_externo: ''
      }
    },
    async loadCatalogs() {
      try {
        const [filialesResponse, volunteersResponse] = await Promise.all([
          axios.get(`${API_BASE}/filiales`),
          axios.get(`${API_BASE}/voluntarios`)
        ])

        this.filiales = Array.isArray(filialesResponse.data) ? filialesResponse.data : []
        this.volunteers = Array.isArray(volunteersResponse.data) ? volunteersResponse.data : []
      } catch (error) {
        this.filiales = []
        this.volunteers = []
      }
    },
    show() {
      this.resetForm()
      this.modalInstance.show()
    },
    hide() {
      this.modalInstance.hide()
    },
    resetForm() {
      this.form = this.defaultForm()
      this.selectedVolunteers = []
      this.volunteerHours = {}
      this.isSubmitting = false
    },
    fullVolunteerName(volunteer) {
      return [volunteer.nombres, volunteer.apellidos].filter(Boolean).join(' ') || volunteer.user?.username || 'Voluntario'
    },
    isSelected(voluntarioId) {
      return this.selectedVolunteers.includes(voluntarioId)
    },
    toggleVolunteer(voluntarioId) {
      if (this.isSelected(voluntarioId)) {
        this.selectedVolunteers = this.selectedVolunteers.filter((value) => value !== voluntarioId)
        const nextHours = { ...this.volunteerHours }
        delete nextHours[voluntarioId]
        this.volunteerHours = nextHours
        return
      }

      this.selectedVolunteers = [...this.selectedVolunteers, voluntarioId]
      this.volunteerHours = {
        ...this.volunteerHours,
        [voluntarioId]: this.volunteerHours[voluntarioId] ?? 0
      }
    },
    buildPayload() {
      return {
        filial_id: Number(this.form.filial_id),
        creado_por: this.$store.getters.authUser?.id,
        nombre: this.form.nombre.trim(),
        tipo: this.form.tipo,
        objetivo: this.form.objetivo || null,
        fecha_inicio: this.form.fecha_inicio,
        fecha_termino: this.form.fecha_termino || null,
        hora_inicio: this.form.hora_inicio || null,
        hora_termino: this.form.hora_termino || null,
        lugar: this.form.lugar || null,
        horas_totales: this.form.horas_totales === '' ? null : Number(this.form.horas_totales),
        colaborador_externo: this.form.colaborador_externo || null,
        voluntarios_detalle: this.selectedVolunteers.map((voluntarioId) => ({
          voluntario_id: voluntarioId,
          horas_asistidas: Number(this.volunteerHours[voluntarioId] ?? 0),
          registrado_por: this.$store.getters.authUser?.id || null
        }))
      }
    },
    async saveActivity() {
      if (!this.isFormValid) {
        show_alerta('Completa los datos obligatorios de la actividad.', 'warning')
        return
      }

      this.isSubmitting = true

      try {
        await axios.post(`${API_BASE}/actividad`, this.buildPayload())
        this.hide()
        this.$emit('actividad-created')
        this.resetForm()
        show_alerta('Actividad creada correctamente.', 'success')
      } catch (error) {
        const errors = error.response?.data?.errors || {}
        const firstMessage = Object.values(errors).flat()[0] || 'No se pudo crear la actividad.'
        show_alerta(firstMessage, 'error')
      } finally {
        this.isSubmitting = false
      }
    }
  }
}
</script>

<style scoped>
.modal-header {
  border-bottom: 0;
}

.modal-footer {
  border-top: 0;
}

.modal-content {
  border-radius: 12px;
  border: none;
}

.modal-body {
  padding: 20px 30px;
}

.volunteer-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 0.9rem;
}

.volunteer-card {
  border: 1px solid #e4e8ef;
  border-radius: 16px;
  padding: 0.95rem;
  background: #fbfcfe;
}

.empty-state {
  border-radius: 18px;
  border: 1px dashed #d6dde7;
  background: #fafbfd;
  padding: 1.2rem;
  color: #65758a;
}
</style>
