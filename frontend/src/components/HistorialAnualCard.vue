<template>
  <div class="card mb-3">
    <div class="card-header">
      <h4 class="mb-0">
        <button class="btn btn-link text-decoration-none" type="button" data-bs-toggle="collapse" :data-bs-target="'#collapse-' + historial.anio">
          {{ historial.anio }}
        </button>
      </h4>
    </div>
    <div :id="'collapse-' + historial.anio" class="collapse show">
      <div class="card-body">
        <HistorialActividadList :actividades="historial.actividades" />
        <div v-if="historial.titulos && historial.titulos.length">
          <h5 class="mt-4">Títulos</h5>
          <ul class="list-group mb-3">
            <li v-for="t in historial.titulos" :key="t.id" class="list-group-item d-flex justify-content-between align-items-center">
              <span>
                <span class="badge bg-primary me-2">{{ t.fecha_obtenido }}</span>
                {{ t.nombre }}
              </span>
            </li>
          </ul>
        </div>
        <div v-if="historial.premios && historial.premios.length">
          <h5 class="mt-4">Premios</h5>
          <ul class="list-group">
            <li v-for="p in historial.premios" :key="p.id" class="list-group-item d-flex justify-content-between align-items-center">
              <span>
                <span class="badge bg-warning text-dark me-2">{{ p.fecha_obtenido }}</span>
                {{ p.nombre }}
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import HistorialActividadList from './HistorialActividadList.vue'
defineProps({ historial: Object })
</script>
