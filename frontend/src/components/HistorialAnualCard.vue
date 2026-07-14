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
  const documents = props.historial?.otros_documentos?.length || 0
  const sanctions = props.historial?.sanciones?.length || 0

  return `${titles} titulo(s) · ${courses} curso(s) · ${documents} documento(s) · ${sanctions} sancion(es)`
})
</script>

<style scoped>
.annual-card {
  display: grid;
  grid-template-columns: clamp(72px, 4.8vw, 92px) 1fr;
  gap: clamp(0.7rem, 0.65vw, 0.9rem);
  align-items: stretch;
  background: var(--cr-white);
  border: 1px solid #ece8e2;
  border-radius: 18px;
  padding: clamp(0.72rem, 0.7vw, 0.9rem);
  color: var(--cr-navy-dark);
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.annual-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 24px var(--cr-navy-shadow);
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
  min-height: clamp(72px, 4.6vw, 92px);
  border-radius: 20px;
  background: #ff313d;
  color: var(--cr-white);
  font-size: clamp(1.24rem, 0.7vw + 1.1rem, 1.62rem);
  font-weight: 800;
  letter-spacing: 0.04em;
}

.annual-card__body {
  min-width: 0;
}

.annual-card__header {
  display: flex;
  justify-content: space-between;
  gap: 0.6rem;
  align-items: center;
  margin-bottom: 0.32rem;
}

.annual-card__header strong {
  font-size: clamp(0.92rem, 0.2vw + 0.88rem, 1rem);
}

.annual-card__badge {
  white-space: nowrap;
  padding: 0.18rem 0.5rem;
  border-radius: 999px;
  background: var(--cr-blue-pale);
  color: var(--cr-navy-dark);
  font-size: clamp(0.78rem, 0.14vw + 0.76rem, 0.88rem);
  font-weight: 700;
}

.annual-card__text {
  margin: 0;
  color: #5f6f82;
  font-size: clamp(0.9rem, 0.16vw + 0.87rem, 0.98rem);
  line-height: 1.32;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 6;
  overflow: hidden;
}
</style>


