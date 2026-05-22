<template>
  <RouterLink
    class="annual-card text-decoration-none"
    :class="{ active: active }"
    :to="to"
  >
    <div class="annual-card__year">{{ yearLabel }}</div>
    <div class="annual-card__body">
      <div class="annual-card__header">
        <strong>{{ historial.cargo || 'Sin cargo' }}</strong>
        <span class="annual-card__attendance">{{ formatAttendance(historial.porcentaje_asistencia) }}</span>
      </div>
      <p class="annual-card__text">
        {{ historial.observaciones_generales || 'Ver detalle del periodo anual.' }}
      </p>
    </div>
  </RouterLink>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  historial: {
    type: Object,
    required: true
  },
  active: {
    type: Boolean,
    default: false
  },
  to: {
    type: Object,
    required: true
  }
})

const yearLabel = computed(() => String(props.historial.anio || '----'))

function formatAttendance(value) {
  if (value === null || value === undefined || value === '') return 'Sin %'
  return `${value}%`
}
</script>

<style scoped>
.annual-card {
  display: grid;
  grid-template-columns: 76px 1fr;
  gap: 0.85rem;
  align-items: stretch;
  background: #ffffff;
  border: 1px solid #ece8e2;
  border-radius: 18px;
  padding: 0.8rem;
  color: #0f2f5f;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.annual-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 24px rgba(15, 47, 95, 0.08);
  border-color: #d6dfe9;
}

.annual-card.active {
  border-color: #ff313d;
  box-shadow: 0 14px 30px rgba(255, 49, 61, 0.16);
}

.annual-card__year {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 76px;
  border-radius: 22px;
  background: #ff313d;
  color: #fff;
  font-size: 1.35rem;
  font-weight: 800;
  letter-spacing: 0.04em;
}

.annual-card__body {
  min-width: 0;
}

.annual-card__header {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  align-items: center;
  margin-bottom: 0.4rem;
}

.annual-card__attendance {
  white-space: nowrap;
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  background: #eef4fb;
  color: #0f2f5f;
  font-size: 0.82rem;
  font-weight: 700;
}

.annual-card__text {
  margin: 0;
  color: #5f6f82;
  font-size: 0.92rem;
  line-height: 1.4;
}
</style>
