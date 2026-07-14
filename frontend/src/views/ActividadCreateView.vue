<template>
  <div class="modal fade" id="newActividadModal" tabindex="-1" aria-labelledby="newActividadModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable activity-modal-dialog">
      <div class="modal-content activity-modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="newActividadModalLabel">
            <i class="fa-solid fa-calendar-plus me-2"></i>Nueva actividad
          </h5>
          <button type="button" class="btn-close btn-close-white" aria-label="Cerrar" @click="hide"></button>
        </div>

        <div class="modal-body activity-modal-body">
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

              <div class="col-md-12">
                <label class="form-label">Nombre</label>
                <input v-model.trim="form.nombre" type="text" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Fecha inicio</label>
                <input v-model="form.fecha_inicio" type="date" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Fecha término</label>
                <input v-model="form.fecha_termino" type="date" class="form-control" :min="form.fecha_inicio || null">
              </div>

              <div class="col-md-4">
                <label class="form-label">Hora inicio</label>
                <input v-model="form.hora_inicio" type="time" class="form-control">
              </div>

              <div class="col-md-4">
                <label class="form-label">Hora término</label>
                <input v-model="form.hora_termino" type="time" class="form-control">
              </div>

              <div class="col-md-4">
                <label class="form-label">Horas totales</label>
                <input v-model="form.horas_totales" type="number" min="0" step="0.25" class="form-control">
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

            <ActivityVolunteerSelectors
              ref="volunteerSelectors"
              v-model:selected-volunteers="selectedVolunteers"
              v-model:volunteer-hours="volunteerHours"
              v-model:notify-volunteers="notifyVolunteers"
              v-model:notification-recipients="notificationRecipients"
              :volunteers="volunteers"
              :activity-hours-limit="activityHoursLimit"
              id-prefix="create-activity"
              participant-description="Puedes dejar la nómina vacía y asociarla posteriormente."
              notification-title="Notificar voluntarios a través de correo"
              notification-description="El envío se realizará solamente después de guardar la actividad."
            />
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="hide">Cancelar</button>
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
import { API_BASE } from '../config/api'
import { show_alerta } from '../funciones'
import ActivityVolunteerSelectors from '../components/ActivityVolunteerSelectors.vue'

export default {
  name: 'ActividadCreateView',
  components: { ActivityVolunteerSelectors },
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
      notifyVolunteers: false,
      notificationRecipients: [],
      form: this.defaultForm()
    }
  },
  watch: {
    'form.hora_inicio'() {
      this.syncHorasTotalesWithSchedule()
    },
    'form.hora_termino'() {
      this.syncHorasTotalesWithSchedule()
    }
  },
  computed: {
    isFormValid() {
      return Boolean(this.form.filial_id && this.form.tipo && this.form.nombre && this.form.fecha_inicio)
    },
    activityHoursLimit() {
      if (this.form.horas_totales !== '' && this.form.horas_totales !== null && this.form.horas_totales !== undefined) {
        const totalHours = Number(this.form.horas_totales)
        return Number.isFinite(totalHours) && totalHours >= 0 ? totalHours : null
      }

      if (!this.form.hora_inicio || !this.form.hora_termino) {
        return null
      }

      const [startHour, startMinute] = this.form.hora_inicio.split(':').map(Number)
      const [endHour, endMinute] = this.form.hora_termino.split(':').map(Number)

      if (![startHour, startMinute, endHour, endMinute].every(Number.isFinite)) {
        return null
      }

      const startMinutes = (startHour * 60) + startMinute
      const endMinutes = (endHour * 60) + endMinute

      if (endMinutes <= startMinutes) {
        return null
      }

      return Number(((endMinutes - startMinutes) / 60).toFixed(2))
    }
  },
  mounted() {
    this.modalInstance = new Modal(document.getElementById('newActividadModal'), { backdrop: 'static', keyboard: false })
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
    normalizeFilialName(value) {
      return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase()
    },
    findCuricoFilialId() {
      const filiales = Array.isArray(this.filiales) ? this.filiales : []
      const filial = filiales.find((item) => this.normalizeFilialName(item?.nombre) === 'curico')
      return filial?.id ?? ''
    },
    async loadCatalogs() {
      try {
        const [filialesResponse, volunteersResponse] = await Promise.all([
          axios.get(`${API_BASE}/filiales`),
          axios.get(`${API_BASE}/voluntarios`)
        ])

        this.filiales = Array.isArray(filialesResponse.data) ? filialesResponse.data : []
        this.volunteers = Array.isArray(volunteersResponse.data) ? volunteersResponse.data : []
        this.notificationRecipients = []

        if (!this.form.filial_id) {
          this.form.filial_id = this.findCuricoFilialId()
        }
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
      this.$refs.volunteerSelectors?.closeAll()
      this.modalInstance.hide()
    },
    resetForm() {
      this.form = this.defaultForm()
      this.form.filial_id = this.findCuricoFilialId()
      this.selectedVolunteers = []
      this.volunteerHours = {}
      this.notifyVolunteers = false
      this.notificationRecipients = []
      this.isSubmitting = false
    },
    fullVolunteerName(volunteer) {
      return [volunteer.nombres, volunteer.apellidos].filter(Boolean).join(' ') || volunteer.user?.username || 'Voluntario'
    },
    calculateScheduledHours() {
      const startValue = this.normalizeTimeValue(this.form.hora_inicio)
      const endValue = this.normalizeTimeValue(this.form.hora_termino)

      if (!startValue || !endValue) {
        return null
      }

      const [startHour, startMinute] = startValue.split(':').map(Number)
      const [endHour, endMinute] = endValue.split(':').map(Number)

      if (![startHour, startMinute, endHour, endMinute].every(Number.isFinite)) {
        return null
      }

      const startMinutes = (startHour * 60) + startMinute
      const endMinutes = (endHour * 60) + endMinute

      if (endMinutes <= startMinutes) {
        return null
      }

      return Number(((endMinutes - startMinutes) / 60).toFixed(2))
    },
    normalizeTimeValue(value) {
      if (typeof value !== 'string') {
        return ''
      }

      const trimmedValue = value.trim()

      if (!trimmedValue) {
        return ''
      }

      const [hours, minutes] = trimmedValue.split(':')

      if (hours === undefined || minutes === undefined) {
        return ''
      }

      return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`
    },
    syncHorasTotalesWithSchedule() {
      this.form.hora_inicio = this.normalizeTimeValue(this.form.hora_inicio)
      this.form.hora_termino = this.normalizeTimeValue(this.form.hora_termino)

      const scheduledHours = this.calculateScheduledHours()

      if (scheduledHours !== null) {
        this.form.horas_totales = scheduledHours
      }
    },
    validateVolunteerHours() {
      const assignedVolunteers = this.selectedVolunteers
        .map((voluntarioId) => {
          const volunteer = this.volunteers.find((item) => Number(item.id) === Number(voluntarioId))
          return {
            name: this.fullVolunteerName(volunteer || {}),
            hours: Number(this.volunteerHours[voluntarioId] ?? 0)
          }
        })
        .filter((item) => item.hours > 0)

      if (!assignedVolunteers.length) {
        return true
      }

      if (this.activityHoursLimit === null) {
        show_alerta('Debes ingresar las horas totales de la actividad antes de asignar horas a voluntarios.', 'warning')
        return false
      }

      const invalidVolunteer = assignedVolunteers.find((item) => item.hours > this.activityHoursLimit)

      if (invalidVolunteer) {
        show_alerta('Las horas asignadas a ' + invalidVolunteer.name + ' no pueden superar las horas totales de la actividad.', 'warning')
        return false
      }

      return true
    },
    buildPayload() {
      const horaInicio = this.normalizeTimeValue(this.form.hora_inicio)
      const horaTermino = this.normalizeTimeValue(this.form.hora_termino)

      return {
        filial_id: Number(this.form.filial_id),
        nombre: this.form.nombre.trim(),
        tipo: this.form.tipo,
        objetivo: this.form.objetivo || null,
        fecha_inicio: this.form.fecha_inicio,
        fecha_termino: this.form.fecha_termino || null,
        hora_inicio: horaInicio || null,
        hora_termino: horaTermino || null,
        lugar: this.form.lugar || null,
        horas_totales: this.activityHoursLimit,
        colaborador_externo: this.form.colaborador_externo || null,
        voluntarios_detalle: this.selectedVolunteers.map((voluntarioId) => ({
          voluntario_id: voluntarioId,
          horas_asistidas: Number(this.volunteerHours[voluntarioId] ?? 0),
        }))
      }
    },
    async saveActivity() {
      if (!this.isFormValid) {
        show_alerta('Completa los datos obligatorios de la actividad.', 'warning')
        return
      }

      if (!this.validateVolunteerHours()) {
        return
      }

      if (this.notifyVolunteers && !this.notificationRecipients.length) {
        show_alerta('Selecciona al menos un voluntario para enviar el aviso.', 'warning')
        return
      }

      this.isSubmitting = true

      try {
        const notificationRequested = this.notifyVolunteers
        const response = await axios.post(`${API_BASE}/actividad`, this.buildPayload())
        let notificationFailed = false

        if (this.notifyVolunteers && this.notificationRecipients.length) {
          try {
            await axios.post(`${API_BASE}/actividad/${response.data.id}/notificar-voluntarios`, {
              tipo: 'nueva_actividad',
              voluntario_ids: this.notificationRecipients
            })
          } catch (notificationError) {
            notificationFailed = true
          }
        }

        this.hide()
        this.$emit('actividad-created')
        this.resetForm()
        show_alerta(
          notificationFailed
            ? 'La actividad fue creada, pero no fue posible programar los correos.'
            : (notificationRequested ? 'Actividad creada y correos programados correctamente.' : 'Actividad creada correctamente.'),
          notificationFailed ? 'warning' : 'success'
        )
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
#newActividadModal {
  padding-top: 4.75rem;
  padding-bottom: 4.75rem;
}

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

.activity-modal-content {
  position: relative;
  max-height: calc(100vh - 9.5rem);
}

.modal-body {
  padding: 20px 30px;
}

.activity-modal-dialog {
  width: min(1140px, calc(100vw - 2rem));
  max-width: min(1140px, calc(100vw - 2rem));
  margin: 0 auto;
}

.activity-modal-body {
  overflow-y: auto;
}

@media (max-width: 991.98px) {
  #newActividadModal {
    padding: 4.25rem 0.5rem 1rem;
  }

  #newActividadModal.show {
    display: flex !important;
    align-items: center;
    justify-content: center;
  }

  .activity-modal-dialog {
    width: calc(100vw - 1rem);
    max-width: calc(100vw - 1rem);
    margin: auto;
  }

  .activity-modal-content {
    max-height: calc(100vh - 5.25rem);
  }

  .activity-modal-body {
    padding: 18px 18px 24px;
  }
}

@media (max-width: 575.98px) {
  #newActividadModal {
    padding: 5.25rem 0.5rem;
  }

  #newActividadModal.show {
    display: flex !important;
    align-items: center;
    justify-content: center;
  }

  .activity-modal-dialog {
    width: calc(100vw - 1rem);
    max-width: calc(100vw - 1rem);
    margin: auto;
  }

  .activity-modal-content {
    max-height: calc(100vh - 10.5rem);
  }

  .activity-modal-body {
    padding: 14px 14px 18px;
  }

  .modal-header,
  .modal-footer {
    padding: 0.75rem 0.9rem;
  }

  .modal-title {
    font-size: 1.05rem;
  }
}
</style>











