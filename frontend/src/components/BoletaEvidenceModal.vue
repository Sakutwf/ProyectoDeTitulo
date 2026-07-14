<template>
  <Teleport to="body">
    <div class="evidence-backdrop" role="presentation" @click.self="$emit('close')">
      <section class="evidence-modal" role="dialog" aria-modal="true" aria-labelledby="evidence-title">
        <header class="evidence-modal__header">
          <div>
            <small>Evidencia de boleta</small>
            <h2 id="evidence-title">{{ title }}</h2>
          </div>
          <div class="evidence-modal__actions">
            <a :href="url" target="_blank" rel="noopener" title="Abrir archivo en otra pestaña" aria-label="Abrir archivo en otra pestaña">
              <i class="fa-solid fa-arrow-up-right-from-square"></i>
            </a>
            <button type="button" title="Cerrar" aria-label="Cerrar" @click="$emit('close')">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
        </header>

        <div class="evidence-modal__body">
          <img v-if="isImage" :src="url" :alt="`Evidencia de ${title}`">
          <iframe v-else-if="isPdf" :src="url" :title="`Evidencia de ${title}`"></iframe>
          <div v-else class="evidence-modal__unsupported">
            <i class="fa-solid fa-file-arrow-down"></i>
            <p>Este tipo de archivo no se puede previsualizar dentro del sistema.</p>
            <a :href="url" target="_blank" rel="noopener" class="btn btn-danger">Abrir archivo</a>
          </div>
        </div>
      </section>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted } from 'vue'

const props = defineProps({
  boleta: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close'])
const url = computed(() => props.boleta?.archivo_url || '')
const title = computed(() => props.boleta?.detalle_compra || 'Boleta')
const mimeType = computed(() => String(props.boleta?.archivo?.mime_type || '').toLowerCase())
const isImage = computed(() => mimeType.value.startsWith('image/') || /\.(jpg|jpeg|png|webp)(\?|$)/i.test(url.value))
const isPdf = computed(() => mimeType.value === 'application/pdf' || /\.pdf(\?|$)/i.test(url.value))

function handleKeydown(event) {
  if (event.key === 'Escape') emit('close')
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
  document.body.style.overflow = 'hidden'
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})
</script>

<style scoped>
.evidence-backdrop {
  position: fixed;
  inset: 0;
  z-index: 3000;
  display: grid;
  place-items: center;
  padding: 3vh 3vw;
  background: rgba(5, 15, 30, 0.76);
  backdrop-filter: blur(3px);
}

.evidence-modal {
  width: min(82vw, 1100px);
  height: 86vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border-radius: 24px;
  background: var(--cr-white);
  box-shadow: 0 30px 90px rgba(0, 0, 0, 0.38);
}

.evidence-modal__header {
  flex: 0 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #e3e8ef;
}

.evidence-modal__header small {
  color: var(--cr-red);
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.evidence-modal__header h2 {
  margin: 0.15rem 0 0;
  color: var(--cr-navy-ink);
  font-size: 1.25rem;
  font-weight: 800;
}

.evidence-modal__actions {
  display: flex;
  gap: 0.55rem;
}

.evidence-modal__actions a,
.evidence-modal__actions button {
  width: 40px;
  height: 40px;
  display: grid;
  place-items: center;
  padding: 0;
  border: 0;
  border-radius: 50%;
  background: #edf1f6;
  color: var(--cr-navy-soft);
  text-decoration: none;
}

.evidence-modal__actions button {
  background: var(--cr-red);
  color: var(--cr-white);
}

.evidence-modal__body {
  flex: 1;
  min-height: 0;
  display: grid;
  place-items: center;
  overflow: auto;
  padding: 1rem;
  background: #e9edf2;
}

.evidence-modal__body img {
  display: block;
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  border-radius: 10px;
  background: var(--cr-white);
  box-shadow: 0 10px 28px rgba(15, 23, 42, 0.14);
}

.evidence-modal__body iframe {
  width: 100%;
  height: 100%;
  border: 0;
  border-radius: 10px;
  background: var(--cr-white);
}

.evidence-modal__unsupported {
  max-width: 430px;
  padding: 2rem;
  text-align: center;
  color: #526074;
}

.evidence-modal__unsupported > i {
  color: var(--cr-red);
  font-size: 3rem;
}

@media (max-width: 767.98px) {
  .evidence-backdrop { padding: 1rem; }
  .evidence-modal { width: 100%; height: 88vh; border-radius: 18px; }
  .evidence-modal__header { padding: 0.85rem 1rem; }
  .evidence-modal__header h2 { font-size: 1rem; }
}
</style>
