<template>
  <div class="container py-4">
    <div class="mb-3 d-flex justify-content-between align-items-center">
      <input v-model="search" class="form-control w-50" placeholder="Buscar año, actividad, título o premio...">
    </div>
    <div v-if="paginatedHistoriales.length">
      <div class="accordion" id="historialAccordion">
        <HistorialAnualCard
          v-for="h in paginatedHistoriales"
          :key="h.anio"
          :historial="h"
        />
      </div>
      <nav class="mt-4">
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
    <div v-else class="alert alert-info">No hay historial disponible.</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import HistorialAnualCard from '../components/HistorialAnualCard.vue'

const historiales = ref([])
const search = ref('')
const page = ref(1)
const perPage = 3

const filteredHistoriales = computed(() => {
  if (!search.value) return historiales.value
  const s = search.value.toLowerCase()
  return historiales.value.filter(h =>
    String(h.anio).includes(s) ||
    (h.actividades && h.actividades.some(a =>
      a.nombre.toLowerCase().includes(s) ||
      a.tipo.toLowerCase().includes(s)
    )) ||
    (h.titulos && h.titulos.some(t =>
      t.nombre.toLowerCase().includes(s)
    )) ||
    (h.premios && h.premios.some(p =>
      p.nombre.toLowerCase().includes(s)
    ))
  )
})

const totalPages = computed(() => Math.ceil(filteredHistoriales.value.length / perPage))
const paginatedHistoriales = computed(() =>
  filteredHistoriales.value.slice((page.value - 1) * perPage, page.value * perPage)
)

watch(search, () => { page.value = 1 })

onMounted(async () => {
  const res = await axios.get(`http://localhost:8000/api/users/${window.location.pathname.split('/').pop()}/historial`)
  historiales.value = res.data
})
</script>

<style scoped>
.historial {
  margin-bottom: 1.5rem;
  padding: 1rem;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  background-color: #fff;
}

.historial h2 {
  margin-top: 0;
  font-size: 1.5rem;
  font-weight: 500;
}

.historial h3 {
  font-size: 1.25rem;
  font-weight: 500;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}

.historial ul {
  padding-left: 1.5rem;
  margin-bottom: 0;
}

.historial li {
  margin-bottom: 0.5rem;
}
</style>