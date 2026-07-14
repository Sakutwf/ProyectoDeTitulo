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

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
              <div>
                <h6 class="mb-1">Voluntarios asociados</h6>
                <small class="text-muted">Actualiza la nómina y las horas asistidas del registro.</small>
              </div>
            </div>

            <div v-if="volunteers.length" class="participant-launcher">
              <div>
                <strong>{{ selectedVolunteers.length }} voluntario(s) autorizado(s)</strong>
                <span>Abre la nómina para seleccionar participantes e ingresar sus horas.</span>
              </div>
              <button type="button" class="btn btn-outline-danger" @click="openParticipantModal">
                <i class="fa-solid fa-users me-2"></i>Seleccionar voluntarios
              </button>
            </div>

            <div v-else class="empty-state">
              No hay voluntarios disponibles para asociar.
            </div>

            <hr class="my-4">

            <section class="notification-panel">
              <div class="notification-launcher">
                <div><strong>Notificar estas modificaciones por correo</strong><span>El aviso indicará que la actividad fue modificada.</span><small v-if="notifyVolunteers">{{ notificationRecipients.length }} destinatario(s) seleccionado(s)</small><small v-else>No se enviarán correos.</small></div>
                <div class="notification-launcher__actions">
                  <button v-if="notifyVolunteers" type="button" class="btn btn-sm btn-outline-secondary" @click="disableNotifications">No enviar</button>
                  <button type="button" class="btn btn-outline-danger" @click="openNotificationModal"><i class="fa-solid fa-envelope me-2"></i>Seleccionar destinatarios</button>
                </div>
              </div>
            </section>
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

    <div v-if="participantModalOpen" class="participant-modal-backdrop" @click.self="closeParticipantModal">
      <section class="participant-modal" role="dialog" aria-modal="true" aria-labelledby="participant-modal-title">
        <header class="participant-modal__header">
          <div>
            <p>Participantes de la actividad</p>
            <h2 id="participant-modal-title">Seleccionar voluntarios</h2>
            <span>Marca a quienes participarán y registra sus horas asistidas.</span>
          </div>
          <div class="participant-modal__header-actions">
            <div class="participant-modal__total-hours">
              <span>Horas de la actividad</span>
              <strong>{{ activityHoursLabel }}</strong>
            </div>
            <button type="button" class="participant-modal__close" aria-label="Cerrar" @click="closeParticipantModal"><i class="fa-solid fa-xmark"></i></button>
          </div>
        </header>

        <div class="participant-modal__toolbar">
          <label><i class="fa-solid fa-magnifying-glass"></i><input v-model.trim="participantSearch" type="search" class="form-control" placeholder="Buscar voluntario"></label>
          <strong>{{ selectedVolunteers.length }} seleccionado(s)</strong>
        </div>

        <div class="participant-modal__body">
          <article v-for="volunteer in filteredParticipantVolunteers" :key="`participant-${volunteer.id}`" class="participant-row" :class="{ 'participant-row--selected': isSelected(volunteer.id) }">
            <label class="participant-row__identity">
              <input :checked="isSelected(volunteer.id)" class="form-check-input" type="checkbox" @change="toggleVolunteer(volunteer.id)">
              <span><strong>{{ fullVolunteerName(volunteer) }}</strong><small>Registro filial {{ volunteer.registro_filial || 'sin información' }}</small></span>
            </label>
            <label v-if="isSelected(volunteer.id)" class="participant-row__hours">
              <span>Horas asistidas</span>
              <input v-model="volunteerHours[volunteer.id]" type="number" min="0" :max="activityHoursLimit ?? null" step="0.25" class="form-control" placeholder="0">
            </label>
          </article>
          <div v-if="!filteredParticipantVolunteers.length" class="empty-state">No se encontraron voluntarios.</div>
        </div>

        <footer class="participant-modal__footer">
          <button type="button" class="btn btn-danger" @click="closeParticipantModal"><i class="fa-solid fa-check me-2"></i>Listo</button>
        </footer>
      </section>
    </div>

    <div v-if="notificationModalOpen" class="participant-modal-backdrop" @click.self="closeNotificationModal">
      <section class="participant-modal" role="dialog" aria-modal="true" aria-labelledby="notification-modal-title">
        <header class="participant-modal__header">
          <div><p>Notificación por correo</p><h2 id="notification-modal-title">Seleccionar destinatarios</h2><span>Busca por nombre y marca a los voluntarios que recibirán el aviso.</span></div>
          <button type="button" class="participant-modal__close" aria-label="Cerrar" @click="closeNotificationModal"><i class="fa-solid fa-xmark"></i></button>
        </header>
        <div class="participant-modal__toolbar">
          <label><i class="fa-solid fa-magnifying-glass"></i><input v-model.trim="notificationSearch" type="search" class="form-control" placeholder="Buscar voluntario por nombre"></label>
          <div class="notification-toolbar__summary"><strong>{{ notificationRecipientDraft.length }} seleccionado(s)</strong><button type="button" class="btn btn-sm btn-outline-danger" @click="toggleAllNotificationRecipients">{{ allNotificationRecipientsSelected ? 'Desmarcar todos' : 'Seleccionar todos' }}</button></div>
        </div>
        <div class="participant-modal__body">
          <label v-for="volunteer in filteredNotificationVolunteers" :key="`notification-${volunteer.id}`" class="notification-modal-row" :class="{ 'notification-modal-row--selected': notificationRecipientDraft.includes(volunteer.id), 'notification-modal-row--disabled': !hasValidVolunteerEmail(volunteer) }">
            <input v-model="notificationRecipientDraft" class="form-check-input" type="checkbox" :value="volunteer.id" :disabled="!hasValidVolunteerEmail(volunteer)">
            <span><strong>{{ fullVolunteerName(volunteer) }}</strong><small>{{ hasValidVolunteerEmail(volunteer) ? volunteer.correo_electronico : 'Sin correo electrónico válido' }}</small></span>
          </label>
          <div v-if="!filteredNotificationVolunteers.length" class="empty-state">No se encontraron voluntarios con ese nombre.</div>
        </div>
        <footer class="participant-modal__footer"><button type="button" class="btn btn-outline-secondary" @click="closeNotificationModal">Cancelar</button><button type="button" class="btn btn-danger" :disabled="!notificationRecipientDraft.length" @click="confirmNotificationRecipients"><i class="fa-solid fa-check me-2"></i>Usar selección</button></footer>
      </section>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { Modal } from 'bootstrap'
import { ACTIVITY_TYPE_OPTIONS } from '../constants/activityTypes'
import { API_BASE } from '../config/api'
import { show_alerta } from '../funciones'

export default {
  name: 'ActividadEditView',
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
      participantModalOpen: false,
      participantSearch: '',
      notifyVolunteers: false,
      notificationRecipients: [],
      notificationRecipientDraft: [],
      notificationModalOpen: false,
      notificationSearch: '',
      isSubmitting: false,
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
    },
    activityHoursLabel() {
      if (this.activityHoursLimit === null) return 'Sin definir'

      const formatted = Number(this.activityHoursLimit).toLocaleString('es-CL', { maximumFractionDigits: 2 })
      return `${formatted} ${Number(this.activityHoursLimit) === 1 ? 'hora' : 'horas'}`
    },
    notifiableVolunteers() {
      return this.volunteers.filter((volunteer) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(volunteer.correo_electronico || ''))
    },
    allNotificationRecipientsSelected() {
      return this.notifiableVolunteers.length > 0 && this.notifiableVolunteers.every((volunteer) => this.notificationRecipientDraft.includes(volunteer.id))
    },
    filteredParticipantVolunteers() {
      const query = this.participantSearch.toLocaleLowerCase('es').trim()
      if (!query) return this.volunteers
      return this.volunteers.filter((volunteer) => `${this.fullVolunteerName(volunteer)} ${volunteer.registro_filial || ''}`.toLocaleLowerCase('es').includes(query))
    },
    filteredNotificationVolunteers() {
      const query = this.notificationSearch.toLocaleLowerCase('es').trim()
      if (!query) return this.volunteers
      return this.volunteers.filter((volunteer) => `${this.fullVolunteerName(volunteer)} ${volunteer.correo_electronico || ''} ${volunteer.registro_filial || ''}`.toLocaleLowerCase('es').includes(query))
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
      this.closeParticipantModal()
      this.closeNotificationModal()
      this.modalInstance.hide()
    },
    handleModalHidden() {
      this.closeParticipantModal()
      this.closeNotificationModal()

      if (document.querySelector('.modal.show')) return

      document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove())
      document.body.classList.remove('modal-open')
      document.body.style.removeProperty('overflow')
      document.body.style.removeProperty('padding-right')
    },
    openParticipantModal() {
      this.participantSearch = ''
      this.participantModalOpen = true
    },
    closeParticipantModal() {
      this.participantModalOpen = false
      this.participantSearch = ''
    },
    hasValidVolunteerEmail(volunteer) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(volunteer?.correo_electronico || '')
    },
    openNotificationModal() {
      this.notificationSearch = ''
      this.notificationRecipientDraft = this.notifyVolunteers
        ? [...this.notificationRecipients]
        : this.notifiableVolunteers
          .filter((volunteer) => this.selectedVolunteers.some((id) => Number(id) === Number(volunteer.id)))
          .map((volunteer) => volunteer.id)
      this.notificationModalOpen = true
    },
    closeNotificationModal() {
      this.notificationModalOpen = false
      this.notificationSearch = ''
      this.notificationRecipientDraft = []
    },
    confirmNotificationRecipients() {
      this.notificationRecipients = [...this.notificationRecipientDraft]
      this.notifyVolunteers = this.notificationRecipients.length > 0
      this.closeNotificationModal()
    },
    disableNotifications() {
      this.notifyVolunteers = false
      this.notificationRecipients = []
    },
    fullVolunteerName(volunteer) {
      return [volunteer.nombres, volunteer.apellidos].filter(Boolean).join(' ') || volunteer.user?.username || 'Voluntario'
    },
    toggleAllNotificationRecipients() {
      this.notificationRecipientDraft = this.allNotificationRecipientsSelected
        ? []
        : this.notifiableVolunteers.map((volunteer) => volunteer.id)
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

        this.hide()
        this.$emit('actividad-updated')
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

.participant-launcher { display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.1rem;border:1px solid #e4e8ef;border-radius:16px;background:#fbfcfe; }
.participant-launcher>div { display:grid;gap:.2rem; }
.participant-launcher strong { color:#163a69; }
.participant-launcher span { color:#65758a;font-size:.9rem; }
.participant-modal-backdrop { position:fixed;inset:0;z-index:1260;display:grid;place-items:center;padding:1rem;background:rgba(15,23,42,.7);backdrop-filter:blur(3px); }
.participant-modal { width:min(900px,96vw);max-height:88vh;display:flex;flex-direction:column;overflow:hidden;border-radius:24px;background:#fff;box-shadow:0 28px 80px rgba(0,0,0,.35); }
.participant-modal__header { display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.2rem 1.4rem;border-bottom:1px solid #e4e8ef; }
.participant-modal__header p { margin:0;color:#e01e1e;font-size:.72rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase; }
.participant-modal__header h2 { margin:.2rem 0;color:#163a69;font-size:1.45rem;font-weight:800; }
.participant-modal__header span { color:#65758a; }
.participant-modal__header-actions { display:flex;align-items:flex-start;gap:.75rem; }
.participant-modal__total-hours { min-width:145px;padding:.55rem .8rem;border:1px solid #cbd9e8;border-radius:12px;background:#f7f9fc;text-align:right; }
.participant-modal__total-hours span { display:block;color:#65758a;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em; }
.participant-modal__total-hours strong { display:block;margin-top:.1rem;color:#163a69;font-size:1rem; }
.participant-modal__close { width:40px;height:40px;flex:0 0 auto;border:0;border-radius:50%;background:#eef1f5;color:#173352; }
.participant-modal__toolbar { display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.4rem;background:#f8fafc;border-bottom:1px solid #e4e8ef;color:#163a69; }
.participant-modal__toolbar label { position:relative;width:min(100%,420px); }
.participant-modal__toolbar label i { position:absolute;left:.9rem;top:50%;z-index:1;transform:translateY(-50%);color:#7b8798; }
.participant-modal__toolbar input { margin:0;padding-left:2.5rem; }
.participant-modal__body { min-height:0;overflow-y:auto;display:grid;gap:.75rem;padding:1rem 1.4rem; }
.participant-row { display:grid;grid-template-columns:minmax(0,1fr) minmax(190px,240px);align-items:center;gap:1rem;padding:.9rem 1rem;border:1px solid #e3e8ef;border-radius:15px;background:#fff;transition:.2s; }
.participant-row--selected { border-color:#e01e1e;background:#fff8f8;box-shadow:0 0 0 2px rgba(224,30,30,.08); }
.participant-row__identity { display:flex;align-items:flex-start;gap:.75rem;min-width:0;cursor:pointer; }
.participant-row__identity .form-check-input { flex:0 0 auto;margin-top:.25rem; }
.participant-row__identity span { display:grid;min-width:0; }
.participant-row__identity strong { color:#163a69; }
.participant-row__identity small { color:#65758a; }
.participant-row__hours { display:grid;gap:.35rem;color:#344054;font-size:.8rem;font-weight:800; }
.participant-row__hours input { margin:0; }
.participant-modal__footer { display:flex;justify-content:flex-end;padding:1rem 1.4rem;border-top:1px solid #e4e8ef;background:#fff; }

.empty-state {
  border-radius: 18px;
  border: 1px dashed #d6dde7;
  background: #fafbfd;
  padding: 1.2rem;
  color: #65758a;
}

.notification-panel { border:1px solid #f1c7ca;border-radius:16px;padding:1rem;background:#fff8f8; }
.notification-launcher { display:flex;align-items:center;justify-content:space-between;gap:1rem; }
.notification-launcher>div:first-child { display:grid;gap:.2rem; }
.notification-launcher span,.notification-launcher small { color:#65758a; }
.notification-launcher__actions { display:flex;align-items:center;gap:.6rem;flex:0 0 auto; }
.notification-toolbar__summary { display:flex;align-items:center;justify-content:flex-end;gap:.7rem; }
.notification-modal-row { display:flex;align-items:flex-start;gap:.75rem;padding:.9rem 1rem;border:1px solid #e3e8ef;border-radius:15px;background:#fff;cursor:pointer;transition:.2s; }
.notification-modal-row--selected { border-color:#e01e1e;background:#fff8f8;box-shadow:0 0 0 2px rgba(224,30,30,.08); }
.notification-modal-row--disabled { background:#f5f6f8;cursor:not-allowed;opacity:.68; }
.notification-modal-row .form-check-input { flex:0 0 auto;margin-top:.25rem; }
.notification-modal-row span { display:grid;min-width:0; }
.notification-modal-row strong { color:#163a69; }
.notification-modal-row small { color:#65758a; }
.participant-modal__footer { gap:.65rem; }
@media(max-width:700px){.participant-launcher,.notification-launcher{align-items:stretch;flex-direction:column}.participant-launcher .btn,.notification-launcher__actions,.notification-launcher__actions .btn{width:100%}.notification-launcher__actions{display:grid}.participant-modal{max-height:92vh;border-radius:18px}.participant-modal__toolbar{align-items:stretch;flex-direction:column}.participant-modal__toolbar label{width:100%}.notification-toolbar__summary{align-items:stretch;flex-direction:column}.participant-row{grid-template-columns:1fr}.participant-row__hours{padding-left:1.8rem}.participant-modal__footer{display:grid;grid-template-columns:1fr}.participant-modal__footer .btn{width:100%}}
</style>




