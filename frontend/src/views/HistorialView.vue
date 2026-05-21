<template>
  <div class="container py-4">
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h2 class="mb-1">{{ user?.nombre || 'Hoja de vida' }}</h2>
        <p class="text-muted mb-0" v-if="hojaDeVida">
          Libro #{{ hojaDeVida.id_libro }} | Creada el {{ formatDate(hojaDeVida.fecha_creacion) }} | Estado: {{ hojaDeVida.estado }}
        </p>
      </div>
      <input v-model="search" class="form-control search-box" placeholder="Buscar por año, cargo, observación o antecedente...">
    </div>

    <div v-if="hojaDeVida" class="row g-4">
      <div class="col-12 col-xl-8">
        <div v-if="paginatedHistoriales.length">
          <HistorialAnualCard
            v-for="h in paginatedHistoriales"
            :key="h.id_hoja"
            :historial="h"
          />
          <nav class="mt-4" v-if="totalPages > 1">
            <ul class="pagination justify-content-center">
              <li class="page-item" :class="{disabled: page === 1}">
                <button class="page-link" @click="page--" :disabled="page === 1">Anterior</button>
              </li>
              <li class="page-item" v-for="p in totalPages" :key="p" :class="{active: page === p}">
                <button class="page-link" @click="page = p">{{ p }}</button>
              </li>
              <li class="page-item" :class="{disabled: page === totalPages}">
                <button class="page-link" @click="page++" :disabled="page === totalPages">Siguiente</button>
              </li>
            </ul>
          </nav>
        </div>
        <div v-else class="alert alert-info">No hay hojas anuales disponibles.</div>
      </div>

      <div class="col-12 col-xl-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h4 class="card-title mb-3">Antecedentes del voluntario</h4>
            <div v-if="filteredAntecedentes.length">
              <div v-for="antecedente in filteredAntecedentes" :key="antecedente.id_antecedente" class="antecedente-item">
                <div class="d-flex justify-content-between align-items-start gap-2">
                  <div>
                    <span class="badge bg-danger-subtle text-danger mb-2">{{ antecedente.tipo }}</span>
                    <h6 class="mb-1">{{ antecedente.nombre }}</h6>
                  </div>
                  <small class="text-muted">{{ formatDateRange(antecedente.fecha_inicio, antecedente.fecha_termino) }}</small>
                </div>
                <p class="mb-1" v-if="antecedente.descripcion">{{ antecedente.descripcion }}</p>
                <small class="text-muted" v-if="antecedente.duracion">Duración: {{ antecedente.duracion }}</small>
              </div>
            </div>
            <div v-else class="text-muted">No hay antecedentes registrados.</div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="alert alert-info">No existe hoja de vida para este usuario.</div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import axios from 'axios'
import HistorialAnualCard from '../components/HistorialAnualCard.vue'

const user = ref(null)
const hojaDeVida = ref(null)
const hojasAnuales = ref([])
const antecedentes = ref([])
const search = ref('')
const page = ref(1)
const perPage = 3

const normalizedSearch = computed(() => search.value.trim().toLowerCase())

const filteredHistoriales = computed(() => {
  if (!normalizedSearch.value) return hojasAnuales.value

  return hojasAnuales.value.filter((historial) =>
    String(historial.anio).includes(normalizedSearch.value) ||
    (historial.cargo || '').toLowerCase().includes(normalizedSearch.value) ||
    (historial.observaciones_generales || '').toLowerCase().includes(normalizedSearch.value)
  )
})

const filteredAntecedentes = computed(() => {
  if (!normalizedSearch.value) return antecedentes.value

  return antecedentes.value.filter((antecedente) =>
    (antecedente.tipo || '').toLowerCase().includes(normalizedSearch.value) ||
    (antecedente.nombre || '').toLowerCase().includes(normalizedSearch.value) ||
    (antecedente.descripcion || '').toLowerCase().includes(normalizedSearch.value)
  )
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredHistoriales.value.length / perPage)))
const paginatedHistoriales = computed(() =>
  filteredHistoriales.value.slice((page.value - 1) * perPage, page.value * perPage)
)

watch(search, () => {
  page.value = 1
})

function formatDate(dateString) {
  if (!dateString) return '-'
  return dateString.slice(0, 10).split('-').reverse().join('-')
}

function formatDateRange(start, end) {
  if (!start && !end) return '-'
  if (start && end) return `${formatDate(start)} - ${formatDate(end)}`
  return formatDate(start || end)
}

onMounted(async () => {
  const userId = window.location.pathname.split('/').pop()
  const res = await axios.get(`http://localhost:8000/api/user/${userId}`)
  user.value = res.data
  hojaDeVida.value = res.data?.voluntario?.hoja_de_vida || null
  hojasAnuales.value = hojaDeVida.value?.hojas_anuales || []
  antecedentes.value = hojaDeVida.value?.antecedentes || []
})
</script>

<style scoped>
.search-box {
  max-width: 420px;
}

.antecedente-item {
  padding: 0.9rem 0;
  border-bottom: 1px solid #ececec;
}

.antecedente-item:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}
</style>
