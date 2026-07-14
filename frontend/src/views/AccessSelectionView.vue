<template>
  <div class="access-page">
    <section class="access-card">
      <router-link to="/portada" aria-label="Ir a Inicio y novedades"><img src="@/assets/LogoHorizontal.svg" alt="Cruz Roja" class="access-logo"></router-link>

      <div class="access-copy">
        <p class="access-eyebrow">Acceso disponible</p>
        <h1>Elige como quieres ingresar</h1>
        <p>
          Tu cuenta tiene acceso tanto al perfil de voluntario como a la vista de administración.
        </p>
      </div>

      <div class="access-options">
        <button type="button" class="access-option volunteer" @click="selectAccess('voluntario')">
          <span class="access-option__icon">
            <i class="fa-solid fa-id-card"></i>
          </span>
          <strong>Ingresar a perfil de voluntario</strong>
          <small>Revisa tu hoja de vida, tus periodos anuales y tus actividades vigentes.</small>
        </button>

        <button type="button" class="access-option admin" @click="selectAccess('administrador')">
          <span class="access-option__icon">
            <i class="fa-solid fa-user-shield"></i>
          </span>
          <strong>Ingresar a vista de administrador</strong>
          <small>Gestiona voluntarios, actividades y la información anual del sistema.</small>
        </button>
      </div>
    </section>
  </div>
</template>

<script>
import { defaultRouteForUser } from '../utils/auth'

export default {
  name: 'AccessSelectionView',
  computed: {
    currentUser() {
      return this.$store.getters.authUser
    }
  },
  methods: {
    selectAccess(accessMode) {
      this.$store.dispatch('chooseAccessMode', accessMode)

      const redirect = this.$route.query.redirect
      if (redirect) {
        this.$router.replace(redirect)
        return
      }

      this.$router.replace(defaultRouteForUser(this.currentUser, accessMode))
    }
  }
}
</script>

<style scoped>
.access-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 1.5rem;
  background:
    radial-gradient(circle at top left, rgba(224, 30, 30, 0.12), transparent 24%),
    radial-gradient(circle at bottom right, rgba(15, 47, 95, 0.16), transparent 28%),
    linear-gradient(180deg, #f3efe9 0%, #edf2f8 100%);
}

.access-card {
  width: min(980px, 100%);
  padding: 3rem;
  border-radius: 32px;
  background: rgba(255, 255, 255, 0.97);
  border: 1px solid var(--cr-navy-shadow);
  box-shadow: 0 28px 72px rgba(15, 47, 95, 0.12);
  text-align: center;
}

.access-logo {
  display: block;
  width: min(360px, 100%);
  height: auto;
  margin: 0 auto 2rem;
}

.access-copy h1 {
  margin: 0 0 0.85rem;
  color: var(--cr-navy-dark);
  font-size: clamp(2rem, 4vw, 3.2rem);
  font-weight: 900;
}

.access-copy p {
  margin: 0;
  color: #55657c;
  font-size: 1.05rem;
  line-height: 1.6;
}

.access-eyebrow {
  margin-bottom: 0.65rem !important;
  color: #7d8ba0 !important;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.82rem !important;
  font-weight: 800;
}

.access-options {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
  margin-top: 2rem;
  justify-content: center;
  align-items: stretch;
}

.access-option {
  border: none;
  border-radius: 28px;
  padding: 1.55rem 1.15rem;
  text-align: center;
  display: grid;
  justify-items: center;
  gap: 0.95rem;
  color: var(--cr-white);
  min-height: 220px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.access-option:hover {
  transform: translateY(-2px);
}

.access-option strong {
  font-size: 1.4rem;
  line-height: 1.25;
}

.access-option small {
  max-width: 27ch;
  font-size: 1.06rem;
  line-height: 1.45;
  color: rgba(255, 255, 255, 0.86);
}

.access-option__icon {
  width: 64px;
  height: 64px;
  border-radius: 20px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.16);
  font-size: 1.5rem;
}

.access-option.volunteer {
  background: linear-gradient(160deg, var(--cr-navy-dark) 0%, #214f8f 100%);
  box-shadow: 0 20px 36px rgba(15, 47, 95, 0.2);
}

.access-option.admin {
  background: linear-gradient(160deg, var(--cr-red) 0%, #ff4a4a 100%);
  box-shadow: 0 20px 36px rgba(224, 30, 30, 0.2);
}

@media (max-width: 767.98px) {
  .access-card {
    padding: 2rem 1.4rem;
    border-radius: 24px;
  }

  .access-options {
    grid-template-columns: 1fr;
  }
}
</style>

