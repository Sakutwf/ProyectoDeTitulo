<template>
  <div class="d-flex">
    <SidebarMenu />
    <main class="content-wrapper">
      <header class="content-header">
        <h3 class="m-0"><i class="fa-solid fa-user-check me-2"></i>Solicitudes de hoja de vida</h3>
      </header>
      <div class="content">
        <section class="request-shell">
          <div class="request-toolbar">
            <div><h2>Modificaciones pendientes</h2><p>Revisa la información antes de incorporarla al registro oficial.</p></div>
            <select v-model="statusFilter" class="form-select" @change="loadRequests">
              <option value="pendiente">Pendientes</option>
              <option value="aprobada">Aprobadas</option>
              <option value="rechazada">Rechazadas</option>
              <option value="">Todas</option>
            </select>
          </div>

          <div v-if="loading" class="empty-state">Cargando solicitudes...</div>
          <div v-else-if="!requests.length" class="empty-state">No hay solicitudes para este filtro.</div>
          <div v-else class="request-list">
            <article v-for="item in requests" :key="item.id" class="request-card">
              <div class="request-card__header">
                <div>
                  <span class="status-badge" :class="`status-badge--${item.estado}`">{{ item.estado }}</span>
                  <h3>{{ volunteerName(item) }}</h3>
                  <p>{{ actionLabel(item) }} · {{ item.voluntario?.filial?.nombre || 'Sin filial' }}</p>
                </div>
                <time>{{ formatDate(item.created_at) }}</time>
              </div>

              <dl class="data-grid">
                <template v-for="(value, key) in item.datos" :key="key">
                  <div v-if="value"><dt>{{ fieldLabel(key) }}</dt><dd>{{ value }}</dd></div>
                </template>
              </dl>

              <div v-if="item.archivos?.length" class="attachment-list">
                <a v-for="file in item.archivos" :key="file.id" :href="file.url_publica" target="_blank" rel="noopener">
                  <i class="fa-solid fa-paperclip"></i> {{ file.nombre_original }}
                </a>
              </div>

              <p v-if="item.motivo_revision" class="review-reason"><strong>Motivo:</strong> {{ item.motivo_revision }}</p>

              <div v-if="item.estado === 'pendiente'" class="request-actions">
                <button class="btn btn-outline-danger" :disabled="reviewingId === item.id" @click="review(item, 'rechazar')">Rechazar</button>
                <button class="btn btn-danger" :disabled="reviewingId === item.id" @click="review(item, 'aprobar')">Aprobar</button>
              </div>
            </article>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2'
import SidebarMenu from '../components/SidebarMenu.vue'
import { API_BASE } from '../config/api'

export default {
  name: 'SolicitudesHojaVidaView',
  components: { SidebarMenu },
  data: () => ({ requests: [], loading: false, reviewingId: null, statusFilter: 'pendiente' }),
  mounted() { this.loadRequests() },
  methods: {
    async loadRequests() {
      this.loading = true
      try {
        const response = await axios.get(`${API_BASE}/solicitudes-hoja-vida`, { params: { estado: this.statusFilter || undefined } })
        this.requests = Array.isArray(response.data) ? response.data : []
      } catch (error) {
        Swal.fire('Error', 'No fue posible cargar las solicitudes.', 'error')
      } finally { this.loading = false }
    },
    volunteerName(item) {
      return [item.voluntario?.nombres, item.voluntario?.apellidos].filter(Boolean).join(' ') || 'Voluntario'
    },
    actionLabel(item) {
      const action = { crear: 'Nuevo', actualizar: 'Modificación de', eliminar: 'Eliminación de' }[item.accion] || item.accion
      const type = { titulo: 'título', curso: 'curso', documento: 'documento' }[item.tipo_registro] || item.tipo_registro
      return `${action} ${type}`
    },
    fieldLabel(key) {
      return ({ titulo: 'Título', nombre_curso: 'Curso', nombre_documento: 'Documento', entregado_por: 'Entregado por', codigo_titulo: 'Código', codigo_curso: 'Código', motivo: 'Descripción' })[key] || key
    },
    formatDate(value) { return value ? new Intl.DateTimeFormat('es-CL', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '' },
    async review(item, decision) {
      let motivo = ''
      if (decision === 'rechazar') {
        const result = await Swal.fire({ title: 'Motivo del rechazo', input: 'textarea', inputValidator: value => !value?.trim() ? 'Debes indicar un motivo.' : undefined, showCancelButton: true, confirmButtonText: 'Rechazar', confirmButtonColor: '#e01e1e' })
        if (!result.isConfirmed) return
        motivo = result.value.trim()
      } else {
        const result = await Swal.fire({ title: '¿Aprobar solicitud?', text: 'La información se incorporará a la hoja de vida oficial.', icon: 'question', showCancelButton: true, confirmButtonText: 'Aprobar', confirmButtonColor: '#e01e1e' })
        if (!result.isConfirmed) return
      }

      this.reviewingId = item.id
      try {
        await axios.put(`${API_BASE}/solicitudes-hoja-vida/${item.id}/revisar`, { decision, motivo: motivo || null })
        await this.loadRequests()
        Swal.fire('Solicitud revisada', decision === 'aprobar' ? 'La modificación fue incorporada.' : 'La solicitud fue rechazada.', 'success')
      } catch (error) {
        Swal.fire('Error', error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat()[0] || 'No fue posible revisar la solicitud.', 'error')
      } finally { this.reviewingId = null }
    }
  }
}
</script>

<style scoped>
.content-wrapper{flex:1;min-width:0;padding:2rem;background:#f5f7fa;min-height:100vh}.content-header{margin-bottom:1.25rem}.request-shell{background:#fff;border-radius:18px;padding:1.4rem;box-shadow:0 10px 30px rgba(20,32,50,.08)}.request-toolbar{display:flex;justify-content:space-between;gap:1rem;align-items:center;margin-bottom:1.2rem}.request-toolbar h2{margin:0 0 .25rem}.request-toolbar p{margin:0;color:#667085}.request-toolbar select{max-width:220px}.request-list{display:grid;gap:1rem}.request-card{border:1px solid #e3e8ef;border-radius:15px;padding:1.15rem}.request-card__header{display:flex;justify-content:space-between;gap:1rem}.request-card h3{margin:.45rem 0 .2rem}.request-card p{color:#667085}.status-badge{font-size:.75rem;text-transform:uppercase;font-weight:800;padding:.3rem .55rem;border-radius:999px;background:#fff1c2;color:#805b00}.status-badge--aprobada{background:#dff7e8;color:#176b3a}.status-badge--rechazada{background:#fde2e3;color:#a4212a}.data-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.7rem;margin:1rem 0}.data-grid div{background:#f8fafc;border-radius:10px;padding:.7rem}.data-grid dt{font-size:.75rem;color:#667085;text-transform:uppercase}.data-grid dd{margin:.2rem 0 0;font-weight:700}.attachment-list{display:flex;flex-wrap:wrap;gap:.6rem}.attachment-list a{color:#c51f2b}.request-actions{display:flex;justify-content:flex-end;gap:.6rem;margin-top:1rem}.review-reason{background:#fff5f5;padding:.75rem;border-radius:10px}.empty-state{text-align:center;padding:2rem;color:#667085}@media(max-width:768px){.content-wrapper{padding:6.5rem 1rem 1rem}.request-toolbar{align-items:stretch;flex-direction:column}.request-toolbar select{max-width:none}}
</style>
