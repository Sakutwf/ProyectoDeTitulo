<template>
  <div class="d-flex">
    <SidebarMenu />

    <div class="content-wrapper">
      <div class="content-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <h3 class="m-0"><i class="fa-solid fa-receipt me-2"></i>Boletas</h3>
          <span class="role-chip">Perfil de voluntario</span>
        </div>
      </div>

      <div class="content">
        <div class="card shadow volunteer-card">
          <div class="card-body">
            <div class="section-copy">
              <h2>Consulta de boletas</h2>
              <p>
                Esta seccion quedo preparada para la vista de voluntario. Cuando el flujo de boletas este conectado al perfil,
                podras revisarlas desde aqui.
              </p>
            </div>

            <div class="empty-state">
              Aun no hay boletas vinculadas al perfil del voluntario.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import SidebarMenu from '../components/SidebarMenu.vue'
</script>

<style scoped>
.content-wrapper {
  flex: 1;
  background-color: #f5f7fa;
  min-height: 100vh;
}

.content-header {
  padding: 1rem 1.5rem;
  background-color: #fff;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 1.5rem;
}

.content {
  padding: 0 1.5rem 1.5rem;
}

.volunteer-card {
  border: none;
  border-radius: 8px;
}

.section-copy {
  margin-bottom: 1.5rem;
}

.section-copy h2 {
  font-size: 1.65rem;
  font-weight: 700;
  color: #243447;
  margin-bottom: 0.5rem;
}

.section-copy p {
  color: #5c6b7c;
  margin: 0;
  line-height: 1.6;
}

.role-chip {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  background: #eef4fb;
  color: #0f2f5f;
  font-weight: 700;
  padding: 0.5rem 0.9rem;
}

.empty-state {
  border-radius: 18px;
  border: 1px dashed #d6dde7;
  background: #fafbfd;
  padding: 1.2rem;
  color: #65758a;
}

@media (max-width: 767.98px) {
  .content {
    padding: 0 1rem 1rem;
  }
}
</style>
