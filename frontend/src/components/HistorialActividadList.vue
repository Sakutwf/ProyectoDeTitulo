<template>
  <div v-if="actividades && actividades.length">
    <h5>Actividades</h5>
    <ul class="list-group">
      <li v-for="a in actividades" :key="a.id" class="list-group-item d-flex justify-content-between align-items-center">
        <span>
          <span class="badge bg-secondary me-2">{{ a.fecha }}</span>
          <span class="badge bg-info text-dark me-2">{{ a.tipo }}</span>
          {{ a.nombre }}
        </span>
        <span class="badge bg-success">{{ a.porcentaje_asistencia }}%</span>
      </li>
    </ul>
  </div>
</template>
<script setup>
defineProps({ actividades: Array })
</script>
