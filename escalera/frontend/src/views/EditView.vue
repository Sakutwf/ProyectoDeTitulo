<template>
  <div class="legacy-view">
    <div class="legacy-card">
      <h1>Vista reemplazada</h1>
      <p>
        Esta edicion pertenecia al modelo antiguo de usuarios.
        La edicion actual se realiza desde el modulo de perfiles.
      </p>
      <button type="button" class="btn btn-danger" @click="goToProfiles">
        Ir a gestion de perfiles
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EditView',
  methods: {
    goToProfiles() {
      this.$router.push({ name: 'voluntarios' })
    }
  }
}
</script>

<style scoped>
.legacy-view {
  min-height: 100vh;
  display: grid;
  place-items: center;
  background: #f5f7fa;
  padding: 24px;
}

.legacy-card {
  max-width: 560px;
  background: #fff;
  border: 1px solid #e4e9f1;
  border-radius: 18px;
  box-shadow: 0 18px 40px rgba(15, 47, 95, 0.08);
  padding: 32px;
  text-align: center;
}

.legacy-card h1 {
  margin-bottom: 12px;
  color: #0f2f5f;
}

.legacy-card p {
  margin-bottom: 20px;
  color: #607086;
}
</style>
