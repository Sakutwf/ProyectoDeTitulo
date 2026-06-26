<template>
  <RouterLink
    class="annual-card text-decoration-none"
    :class="{ active }"
    :to="to"
  >
    <div class="annual-card__year">{{ yearLabel }}</div>
    <div class="annual-card__body">
      <div class="annual-card__header">
        <strong>{{ attendanceLabel }}</strong>
        <span class="annual-card__badge">{{ secondaryLabel }}</span>
      </div>
      <p class="annual-card__text">
        {{ summaryText }}
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

const yearLabel = computed(() => String(props.historial?.anio || '----'))

const attendanceLabel = computed(() => {
  const percentage = props.historial?.asistencia_anual_porcentaje

  if (percentage === null || percentage === undefined || percentage === '') {
    return 'Asistencia sin registrar'
  }

  return `${Number(percentage)}% de asistencia`
})

const secondaryLabel = computed(() => {
  return `${props.historial?.titulos?.length || 0} titulo(s)`
})

const summaryText = computed(() => {
  const comments = props.historial?.comentarios?.trim()

  if (comments) {
    return comments
  }

  const titles = props.historial?.titulos?.length || 0
  const courses = props.historial?.cursos?.length || 0
  const sanctions = props.historial?.sanciones?.length || 0

  return `${titles} titulo(s) · ${courses} curso(s) · ${sanctions} sancion(es)`
})
</script>

<style scoped>
.annual-card {
  display: grid;
  grid-template-columns: clamp(80px, 5.4vw, 106px) 1fr;
  gap: clamp(0.85rem, 0.75vw, 1rem);
  align-items: stretch;
  background: #ffffff;
  border: 1px solid #ece8e2;
  border-radius: 18px;
  padding: clamp(0.8rem, 0.8vw, 1rem);
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
  min-height: clamp(80px, 5vw, 106px);
  border-radius: 22px;
  background: #ff313d;
  color: #fff;
  font-size: clamp(1.35rem, 0.8vw + 1.18rem, 1.8rem);
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
  margin-bottom: 0.45rem;
}

.annual-card__header strong {
  font-size: clamp(0.95rem, 0.25vw + 0.9rem, 1.05rem);
}

.annual-card__badge {
  white-space: nowrap;
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  background: #eef4fb;
  color: #0f2f5f;
  font-size: clamp(0.82rem, 0.18vw + 0.79rem, 0.92rem);
  font-weight: 700;
}

.annual-card__text {
  margin: 0;
  color: #5f6f82;
  font-size: clamp(0.94rem, 0.22vw + 0.89rem, 1.02rem);
  line-height: 1.4;
}
</style>
