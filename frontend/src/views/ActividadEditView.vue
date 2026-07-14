<template>
  <div class="modal fade" id="editActividadModal" tabindex="-1" aria-labelledby="editActividadModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="editActividadModalLabel">
            <i class="fa-solid fa-edit me-2"></i>Editar actividad
          </h5>
          <button type="button" class="btn-close btn-close-white" aria-label="Cerrar" @click="hide"></button>
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

            <ActivityVolunteerSelectors
              ref="volunteerSelectors"
              v-model:selected-volunteers="selectedVolunteers"
              v-model:volunteer-hours="volunteerHours"
              v-model:notify-volunteers="notifyVolunteers"
              v-model:notification-recipients="notificationRecipients"
              :volunteers="volunteers"
              :activity-hours-limit="activityHoursLimit"
              id-prefix="edit-activity"
              participant-description="Actualiza la nómina y las horas asistidas del registro."
              notification-title="Notificar estas modificaciones por correo"
              notification-description="El aviso indicará que la actividad fue modificada."
            />
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="hide">Cancelar</button>
          <button type="button" class="btn btn-danger" :disabled="isSubmitting || !isFormValid" @click="saveActivity">
            <i class="fa-solid fa-save me-1"></i>{{ isSubmitting ? 'Guardando...' : 'Guardar cambios' }}
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
  name: 'ActividadEditView',
  components: { ActivityVolunteerSelectors },
  props: {
    actividadId: {
      type: [Number, String, null],
      required: false,
      default: null
    }
  },
  emits: ['actividad-updated'],
  data() {
    return {
      ACTIVITY_TYPE_OPTIONS,
      modalInstance: null,
      modalElement: null,
      filiales: [],
      volunteers: [],
      selectedVolunteers: [],
      volunteerHours: {},
      notifyVolunteers: false,
      notificationRecipients: [],
      isSubmitting: false,
      emitUpdateAfterHidden: false,
      form: this.defaultForm()
    }
  },
  computed: {
    isFormValid() {
      return Boolean(this.form.id && this.form.filial_id && this.form.tipo && this.form.nombre && this.form.fecha_inicio)
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
  watch: {
    actividadId: {
      immediate: true,
      handler(newValue) {
        if (newValue) {
          this.loadActivity(newValue)
        }
      }
    }
  },
  mounted() {
    this.modalElement = document.getElementById('editActividadModal')
    this.modalInstance = new Modal(this.modalElement, { backdrop: 'static', keyboard: false })
    this.modalElement.addEventListener('hidden.bs.modal', this.handleModalHidden)
    this.loadCatalogs()
  },
  beforeUnmount() {
    this.modalElement?.removeEventListener('hidden.bs.modal', this.handleModalHidden)
    this.modalInstance?.dispose()
    this.handleModalHidden()
  },
  methods: {
    defaultForm() {
      return {
        id: null,
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
      this.modalInstance.show()
    },
    hide() {
      this.$refs.volunteerSelectors?.closeAll()
      this.modalInstance.hide()
    },
    handleModalHidden() {
      this.$refs.volunteerSelectors?.closeAll()

      if (!document.querySelector('.modal.show')) {
        document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove())
        document.body.classList.remove('modal-open')
        document.body.style.removeProperty('overflow')
        document.body.style.removeProperty('padding-right')
      }

      // La vista padre desmonta este componente al recibir el evento. Se emite
      // solo después de que Bootstrap terminó la animación y liberó el modal.
      if (this.emitUpdateAfterHidden) {
        this.emitUpdateAfterHidden = false
        this.$emit('actividad-updated')
      }
    },
    fullVolunteerName(volunteer) {
      return [volunteer.nombres, volunteer.apellidos].filter(Boolean).join(' ') || volunteer.user?.username || 'Voluntario'
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
    async loadActivity(id) {
      try {
        const response = await axios.get(`${API_BASE}/actividad/${id}`)
        const activity = response.data

        this.form = {
          id: activity.id,
          filial_id: activity.filial_id ?? '',
          nombre: activity.nombre || '',
          tipo: activity.tipo || '',
          objetivo: activity.objetivo || '',
          fecha_inicio: activity.fecha_inicio ? String(activity.fecha_inicio).slice(0, 10) : '',
          fecha_termino: activity.fecha_termino ? String(activity.fecha_termino).slice(0, 10) : '',
          hora_inicio: this.normalizeTimeValue(activity.hora_inicio),
          hora_termino: this.normalizeTimeValue(activity.hora_termino),
          lugar: activity.lugar || '',
          horas_totales: activity.horas_totales ?? '',
          colaborador_externo: activity.colaborador_externo || ''
        }

        this.selectedVolunteers = (activity.voluntarios || []).map((volunteer) => volunteer.id)
        this.volunteerHours = Object.fromEntries(
          (activity.voluntarios || []).map((volunteer) => [volunteer.id, Number(volunteer.pivot?.horas_asistidas ?? 0)])
        )
        this.notifyVolunteers = false
        this.notificationRecipients = []

        this.show()
      } catch (error) {
        show_alerta('No se pudo cargar la actividad.', 'error')
      }
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
        const activityId = this.form.id
        const notificationRequested = this.notifyVolunteers
        await axios.put(`${API_BASE}/actividad/${activityId}`, this.buildPayload())
        let notificationFailed = false

        if (notificationRequested && this.notificationRecipients.length) {
          try {
            await axios.post(`${API_BASE}/actividad/${activityId}/notificar-voluntarios`, {
              tipo: 'actividad_modificada',
              voluntario_ids: this.notificationRecipients
            })
          } catch (notificationError) {
            notificationFailed = true
          }
        }

        this.emitUpdateAfterHidden = true
        this.hide()
        show_alerta(
          notificationFailed
            ? 'La actividad se actualizó, pero no fue posible programar los correos.'
            : (notificationRequested ? 'Actividad actualizada y correos programados correctamente.' : 'Actividad actualizada correctamente.'),
          notificationFailed ? 'warning' : 'success'
        )
      } catch (error) {
        const errors = error.response?.data?.errors || {}
        const firstMessage = Object.values(errors).flat()[0] || 'No se pudo actualizar la actividad.'
        show_alerta(firstMessage, 'error')
      } finally {
        this.isSubmitting = false
      }
    }
  }
}
</script>

<style scoped>
#editActividadModal { z-index: 1250; }
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

</style>




