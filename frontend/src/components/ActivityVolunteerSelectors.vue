<template>
  <div class="activity-volunteer-selectors">
    <hr class="my-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <h6 class="mb-1">Voluntarios asociados</h6>
        <small class="text-muted">{{ participantDescription }}</small>
      </div>
    </div>

    <div v-if="volunteers.length" class="participant-launcher">
      <div>
        <strong>{{ selectedVolunteers.length }} voluntario(s) autorizado(s)</strong>
        <span>Abre la nómina para seleccionar participantes e ingresar sus horas.</span>
      </div>
      <button type="button" class="btn btn-outline-danger selector-action-button" @click="openParticipantModal">
        <i class="fa-solid fa-users"></i><span>Seleccionar voluntarios</span>
      </button>
    </div>
    <div v-else class="empty-state">No hay voluntarios disponibles para asociar.</div>

    <hr class="my-4">

    <section class="notification-panel">
      <div class="notification-launcher">
        <div>
          <strong>{{ notificationTitle }}</strong>
          <span>{{ notificationDescription }}</span>
          <small v-if="notifyVolunteers">{{ notificationRecipients.length }} destinatario(s) seleccionado(s)</small>
          <small v-else>No se enviarán correos.</small>
        </div>
        <div class="notification-launcher__actions">
          <button type="button" class="btn btn-sm btn-outline-secondary notification-cancel-button" :disabled="!notifyVolunteers" @click="disableNotifications">No enviar</button>
          <button type="button" class="btn btn-outline-danger selector-action-button" @click="openNotificationModal">
            <i class="fa-solid fa-envelope"></i><span>Seleccionar destinatarios</span>
          </button>
        </div>
      </div>
    </section>

    <div v-if="participantModalOpen" class="participant-modal-backdrop" @click.self="closeParticipantModal">
      <section class="participant-modal" role="dialog" aria-modal="true" :aria-labelledby="`${idPrefix}-participant-title`">
        <header class="participant-modal__header">
          <div>
            <p>Participantes de la actividad</p>
            <h2 :id="`${idPrefix}-participant-title`">Seleccionar voluntarios</h2>
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
              <input :value="volunteerHours[volunteer.id]" type="number" min="0" :max="activityHoursLimit ?? null" step="0.25" class="form-control" placeholder="0" @input="updateVolunteerHours(volunteer.id, $event.target.value)">
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
      <section class="participant-modal" role="dialog" aria-modal="true" :aria-labelledby="`${idPrefix}-notification-title`">
        <header class="participant-modal__header">
          <div>
            <p>Notificación por correo</p>
            <h2 :id="`${idPrefix}-notification-title`">Seleccionar destinatarios</h2>
            <span>Los participantes aparecen marcados inicialmente. Puedes buscar, agregar o quitar destinatarios.</span>
          </div>
          <button type="button" class="participant-modal__close" aria-label="Cerrar" @click="closeNotificationModal"><i class="fa-solid fa-xmark"></i></button>
        </header>
        <div class="participant-modal__toolbar">
          <label><i class="fa-solid fa-magnifying-glass"></i><input v-model.trim="notificationSearch" type="search" class="form-control" placeholder="Buscar voluntario por nombre"></label>
          <div class="notification-toolbar__summary">
            <strong>{{ notificationRecipientDraft.length }} seleccionado(s)</strong>
            <div class="notification-toolbar__bulk-actions">
              <button type="button" class="btn btn-sm btn-outline-danger" :disabled="allNotificationRecipientsSelected" @click="markAllNotificationRecipients">Marcar todos</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="!notificationRecipientDraft.length" @click="unmarkAllNotificationRecipients">Desmarcar todos</button>
            </div>
          </div>
        </div>
        <div class="participant-modal__body">
          <label v-for="volunteer in filteredNotificationVolunteers" :key="`notification-${volunteer.id}`" class="notification-modal-row" :class="{ 'notification-modal-row--selected': draftIncludes(volunteer.id), 'notification-modal-row--disabled': !hasValidVolunteerEmail(volunteer) }">
            <input :checked="draftIncludes(volunteer.id)" class="form-check-input" type="checkbox" :disabled="!hasValidVolunteerEmail(volunteer)" @change="toggleNotificationRecipient(volunteer.id)">
            <span><strong>{{ fullVolunteerName(volunteer) }}</strong><small>{{ hasValidVolunteerEmail(volunteer) ? volunteer.correo_electronico : 'Sin correo electrónico válido' }}</small></span>
          </label>
          <div v-if="!filteredNotificationVolunteers.length" class="empty-state">No se encontraron voluntarios con ese nombre.</div>
        </div>
        <footer class="participant-modal__footer">
          <button type="button" class="btn btn-outline-secondary" @click="closeNotificationModal">Cancelar</button>
          <button type="button" class="btn btn-danger" :disabled="!notificationRecipientDraft.length" @click="confirmNotificationRecipients"><i class="fa-solid fa-check me-2"></i>Usar selección</button>
        </footer>
      </section>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ActivityVolunteerSelectors',
  props: {
    volunteers: { type: Array, default: () => [] },
    selectedVolunteers: { type: Array, default: () => [] },
    volunteerHours: { type: Object, default: () => ({}) },
    activityHoursLimit: { type: Number, default: null },
    notifyVolunteers: { type: Boolean, default: false },
    notificationRecipients: { type: Array, default: () => [] },
    participantDescription: { type: String, default: 'Selecciona participantes e ingresa sus horas asistidas.' },
    notificationTitle: { type: String, required: true },
    notificationDescription: { type: String, required: true },
    idPrefix: { type: String, required: true }
  },
  emits: ['update:selectedVolunteers', 'update:volunteerHours', 'update:notifyVolunteers', 'update:notificationRecipients'],
  data: () => ({
    participantModalOpen: false,
    participantSearch: '',
    notificationModalOpen: false,
    notificationSearch: '',
    notificationRecipientDraft: []
  }),
  computed: {
    activityHoursLabel() {
      if (this.activityHoursLimit === null) return 'Sin definir'
      const formatted = this.activityHoursLimit.toLocaleString('es-CL', { maximumFractionDigits: 2 })
      return `${formatted} ${this.activityHoursLimit === 1 ? 'hora' : 'horas'}`
    },
    notifiableVolunteers() {
      return this.volunteers.filter(this.hasValidVolunteerEmail)
    },
    allNotificationRecipientsSelected() {
      return this.notifiableVolunteers.length > 0 && this.notifiableVolunteers.every((volunteer) => this.draftIncludes(volunteer.id))
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
  methods: {
    sameId(left, right) { return Number(left) === Number(right) },
    fullVolunteerName(volunteer) {
      return [volunteer.nombres, volunteer.apellidos].filter(Boolean).join(' ') || volunteer.user?.username || 'Voluntario'
    },
    hasValidVolunteerEmail(volunteer) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(volunteer?.correo_electronico || '')
    },
    isSelected(id) { return this.selectedVolunteers.some((selectedId) => this.sameId(selectedId, id)) },
    draftIncludes(id) { return this.notificationRecipientDraft.some((selectedId) => this.sameId(selectedId, id)) },
    toggleVolunteer(id) {
      if (this.isSelected(id)) {
        this.$emit('update:selectedVolunteers', this.selectedVolunteers.filter((selectedId) => !this.sameId(selectedId, id)))
        const hours = { ...this.volunteerHours }
        delete hours[id]
        this.$emit('update:volunteerHours', hours)
        return
      }
      this.$emit('update:selectedVolunteers', [...this.selectedVolunteers, id])
      this.$emit('update:volunteerHours', { ...this.volunteerHours, [id]: this.volunteerHours[id] ?? 0 })
    },
    updateVolunteerHours(id, value) {
      this.$emit('update:volunteerHours', { ...this.volunteerHours, [id]: value })
    },
    openParticipantModal() {
      this.participantSearch = ''
      this.participantModalOpen = true
    },
    closeParticipantModal() {
      this.participantModalOpen = false
      this.participantSearch = ''
    },
    openNotificationModal() {
      this.notificationSearch = ''
      // La selección inicial siempre refleja la nómina de participantes actual.
      // Desde aquí el administrador puede agregar, quitar o marcar a todos.
      this.notificationRecipientDraft = this.notifiableVolunteers
        .filter((volunteer) => this.isSelected(volunteer.id))
        .map((volunteer) => volunteer.id)
      this.notificationModalOpen = true
    },
    closeNotificationModal() {
      this.notificationModalOpen = false
      this.notificationSearch = ''
      this.notificationRecipientDraft = []
    },
    toggleNotificationRecipient(id) {
      this.notificationRecipientDraft = this.draftIncludes(id)
        ? this.notificationRecipientDraft.filter((selectedId) => !this.sameId(selectedId, id))
        : [...this.notificationRecipientDraft, id]
    },
    markAllNotificationRecipients() {
      this.notificationRecipientDraft = this.notifiableVolunteers.map((volunteer) => volunteer.id)
    },
    unmarkAllNotificationRecipients() {
      this.notificationRecipientDraft = []
    },
    confirmNotificationRecipients() {
      this.$emit('update:notificationRecipients', [...this.notificationRecipientDraft])
      this.$emit('update:notifyVolunteers', this.notificationRecipientDraft.length > 0)
      this.closeNotificationModal()
    },
    disableNotifications() {
      this.$emit('update:notifyVolunteers', false)
      this.$emit('update:notificationRecipients', [])
    },
    closeAll() {
      this.closeParticipantModal()
      this.closeNotificationModal()
    }
  }
}
</script>

<style scoped>
.activity-volunteer-selectors{container-type:inline-size}.participant-launcher{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;padding:1rem 1.1rem;border:1px solid #e4e8ef;border-radius:16px;background:#fbfcfe}.participant-launcher>div{display:grid;min-width:0;gap:.2rem}.participant-launcher strong{color:var(--cr-navy-medium)}.participant-launcher span{color:var(--cr-gray-600);font-size:.9rem}.selector-action-button{display:inline-flex;align-items:center;justify-content:center;gap:.3rem;min-width:0;padding:.45rem .7rem;white-space:nowrap}.selector-action-button>span,.notification-launcher .selector-action-button>span{display:inline;color:var(--cr-red);font-size:inherit}.participant-modal-backdrop{position:fixed;inset:0;z-index:1260;display:grid;place-items:center;padding:1rem;background:rgba(15,23,42,.7);backdrop-filter:blur(3px)}.participant-modal{width:min(900px,96vw);max-height:88vh;display:flex;flex-direction:column;overflow:hidden;border-radius:24px;background:var(--cr-white);box-shadow:0 28px 80px rgba(0,0,0,.35)}.participant-modal__header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.2rem 1.4rem;border-bottom:1px solid #e4e8ef}.participant-modal__header p{margin:0;color:var(--cr-red);font-size:.72rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.participant-modal__header h2{margin:.2rem 0;color:var(--cr-navy-medium);font-size:1.45rem;font-weight:800}.participant-modal__header span{color:var(--cr-gray-600)}.participant-modal__header-actions{display:flex;align-items:flex-start;gap:.75rem}.participant-modal__total-hours{min-width:145px;padding:.55rem .8rem;border:1px solid #cbd9e8;border-radius:12px;background:#f7f9fc;text-align:right}.participant-modal__total-hours span{display:block;color:var(--cr-gray-600);font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}.participant-modal__total-hours strong{display:block;margin-top:.1rem;color:var(--cr-navy-medium);font-size:1rem}.participant-modal__close{width:40px;height:40px;flex:0 0 auto;border:0;border-radius:50%;background:#eef1f5;color:var(--cr-navy-soft)}.participant-modal__toolbar{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.4rem;background:var(--cr-gray-50);border-bottom:1px solid #e4e8ef;color:var(--cr-navy-medium)}.participant-modal__toolbar label{position:relative;width:min(100%,420px)}.participant-modal__toolbar label i{position:absolute;left:.9rem;top:50%;z-index:1;transform:translateY(-50%);color:#7b8798}.participant-modal__toolbar input{margin:0;padding-left:2.5rem}.participant-modal__body{min-height:0;overflow-y:auto;display:grid;gap:.75rem;padding:1rem 1.4rem}.participant-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(190px,240px);align-items:center;gap:1rem;padding:.9rem 1rem;border:1px solid #e3e8ef;border-radius:15px;background:var(--cr-white);transition:.2s}.participant-row--selected,.notification-modal-row--selected{border-color:var(--cr-red);background:#fff8f8;box-shadow:0 0 0 2px rgba(224,30,30,.08)}.participant-row__identity{display:flex;align-items:flex-start;gap:.75rem;min-width:0;cursor:pointer}.participant-row__identity .form-check-input,.notification-modal-row .form-check-input{flex:0 0 auto;margin-top:.25rem}.participant-row__identity span,.notification-modal-row span{display:grid;min-width:0}.participant-row__identity strong,.notification-modal-row strong{color:var(--cr-navy-medium)}.participant-row__identity small,.notification-modal-row small{color:var(--cr-gray-600)}.participant-row__hours{display:grid;gap:.35rem;color:var(--cr-slate-dark);font-size:.8rem;font-weight:800}.participant-row__hours input{margin:0}.participant-modal__footer{display:flex;justify-content:flex-end;gap:.65rem;padding:1rem 1.4rem;border-top:1px solid #e4e8ef;background:var(--cr-white)}.empty-state{border-radius:18px;border:1px dashed #d6dde7;background:var(--cr-surface);padding:1.2rem;color:var(--cr-gray-600)}.notification-panel{max-width:100%;overflow:hidden;border:1px solid #f1c7ca;border-radius:16px;padding:1rem;background:#fff8f8}.notification-launcher{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;min-width:0;gap:1rem}.notification-launcher>div:first-child{display:grid;flex:1 1 220px;min-width:0;gap:.2rem;overflow-wrap:anywhere}.notification-launcher span,.notification-launcher small{color:var(--cr-gray-600)}.notification-launcher__actions{display:flex;align-items:center;justify-content:flex-end;flex:1 1 300px;flex-wrap:nowrap;min-width:0;max-width:100%;gap:clamp(.2rem,1.5cqw,.6rem)}.notification-launcher__actions .btn{min-width:0;max-width:100%}.notification-cancel-button{flex:0 1 auto;padding:.45rem .7rem;white-space:nowrap}.notification-cancel-button:disabled{border-color:#7b8490;background:#f1f3f5;color:#626b76;opacity:1}.notification-toolbar__summary,.notification-toolbar__bulk-actions{display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;min-width:0;gap:.7rem}.notification-modal-row{display:flex;align-items:flex-start;gap:.75rem;padding:.9rem 1rem;border:1px solid #e3e8ef;border-radius:15px;background:var(--cr-white);cursor:pointer;transition:.2s}.notification-modal-row--disabled{background:#f5f6f8;cursor:not-allowed;opacity:.68}
@container (max-width:620px){.notification-launcher__actions{flex-basis:100%;justify-content:flex-end}.participant-launcher>div{flex:1 1 100%}.participant-launcher>.selector-action-button{margin-left:auto}}
@container (max-width:390px){.notification-cancel-button,.selector-action-button{flex:0 1 auto;padding:.38rem .55rem;font-size:clamp(.88rem,3.8cqw,.95rem);line-height:1.15}.selector-action-button{gap:.22rem}.selector-action-button i{margin:0}}
@media(max-width:700px){.participant-launcher,.notification-launcher{align-items:stretch;justify-content:flex-start;flex-direction:column}.participant-launcher>div,.notification-launcher>div:first-child,.notification-launcher__actions{flex:none}.notification-launcher__actions{width:100%}.participant-modal{max-height:92vh;border-radius:18px}.participant-modal__header{align-items:stretch;flex-direction:column}.participant-modal__header-actions{justify-content:space-between}.participant-modal__toolbar{align-items:stretch;flex-direction:column}.participant-modal__toolbar label{width:100%}.notification-toolbar__summary{align-items:stretch;flex-direction:column}.participant-row{grid-template-columns:1fr}.participant-row__hours{padding-left:1.8rem}.participant-modal__footer{display:grid;grid-template-columns:1fr}.participant-modal__footer .btn{width:100%}}
</style>
