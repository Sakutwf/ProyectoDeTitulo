<template>
  <div class="card mb-3 shadow-sm">
    <div class="card-header bg-white">
      <h4 class="mb-0">
        <button class="btn btn-link text-decoration-none text-dark fw-semibold px-0" type="button" data-bs-toggle="collapse" :data-bs-target="'#collapse-' + historial.id_hoja">
          Hoja anual {{ historial.anio }}
        </button>
      </h4>
    </div>
    <div :id="'collapse-' + historial.id_hoja" class="collapse show">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="info-chip">
              <span class="label">Cargo</span>
              <strong>{{ historial.cargo || 'Sin cargo registrado' }}</strong>
            </div>
          </div>
          <div class="col-md-4">
            <div class="info-chip">
              <span class="label">Asistencia</span>
              <strong>{{ formatAttendance(historial.porcentaje_asistencia) }}</strong>
            </div>
          </div>
          <div class="col-md-4">
            <div class="info-chip">
              <span class="label">Año</span>
              <strong>{{ historial.anio }}</strong>
            </div>
          </div>
          <div class="col-12">
            <h6 class="text-uppercase text-muted small mb-2">Observaciones generales</h6>
            <p class="mb-0">{{ historial.observaciones_generales || 'Sin observaciones registradas.' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({ historial: Object })

function formatAttendance(value) {
  if (value === null || value === undefined || value === '') return 'Sin porcentaje'
  return `${value}%`
}
</script>

<style scoped>
.info-chip {
  background: #f8f9fa;
  border: 1px solid #ececec;
  border-radius: 12px;
  padding: 0.85rem 1rem;
}

.label {
  display: block;
  color: #6c757d;
  font-size: 0.75rem;
  text-transform: uppercase;
  margin-bottom: 0.3rem;
}
</style>
