<template>
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="card-title mb-0">Participación en actividades</h4>
        <span class="badge text-bg-light">{{ actividades.length }} registros</span>
      </div>

      <div v-if="actividades.length">
        <div
          v-for="actividad in actividades"
          :key="actividad.id"
          class="actividad-item"
        >
          <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
              <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <span class="badge text-bg-dark">{{ actividad.evento?.fecha_inicio_formateada || '-' }}</span>
                <span class="badge bg-info-subtle text-info-emphasis">{{ actividad.tipo || 'Sin tipo' }}</span>
                <span
                  class="badge"
                  :class="actividad.pivot?.asistio ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis'"
                >
                  {{ actividad.pivot?.asistio ? 'Asistio' : 'Ausente' }}
                </span>
              </div>
              <h6 class="mb-1">{{ actividad.evento?.nombre || 'Actividad sin evento' }}</h6>
              <p class="mb-0 text-muted" v-if="actividad.evento?.descripcion">
                {{ actividad.evento.descripcion }}
              </p>
            </div>
            <small class="text-muted">{{ actividad.evento?.tipo || '-' }}</small>
          </div>
        </div>
      </div>

      <div v-else class="text-muted">
        No hay actividades registradas para este voluntario.
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  actividades: {
    type: Array,
    default: () => []
  }
})
</script>

<style scoped>
.actividad-item {
  padding: 0.9rem 0;
  border-bottom: 1px solid #ececec;
}

.actividad-item:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}
</style>
