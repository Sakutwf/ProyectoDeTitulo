<template>
  <div class="login-page">
    <form class="login-card" @submit.prevent="submitLogin">
      <div class="login-brand">
        <img src="@/assets/LogoHorizontal.svg" alt="Cruz Roja" class="login-logo">
      </div>

      <label class="login-field">
        <span>ID de acceso</span>
        <input
          v-model.trim="form.user"
          type="text"
          autocomplete="username"
          placeholder="Ingresa tu N° de registro"
          required
        >
        <small class="login-help">Los voluntarios ingresan con su N° de registro.</small>
      </label>

      <label class="login-field">
        <span>Contrasena</span>
        <input
          v-model="form.id"
          type="password"
          autocomplete="current-password"
          placeholder="Ingresa tu contrasena"
          required
        >
      </label>

      <button type="submit" class="login-submit" :disabled="isSubmitting">
        {{ isSubmitting ? 'Ingresando...' : 'Iniciar sesion' }}
      </button>

      <a href="#" class="login-recovery-link" @click.prevent>
        Recuperar Contraseña
      </a>
    </form>
  </div>
</template>

<script>
import axios from 'axios'
import { show_alerta } from '../funciones'
import { API_BASE } from '../config/api'
import { defaultRouteForUser, requiresAccessSelection } from '../utils/auth'

export default {
  name: 'LoginView',
  data() {
    return {
      form: {
        user: '',
        id: ''
      },
      isSubmitting: false
    }
  },
  methods: {
    async submitLogin() {
      this.isSubmitting = true

      try {
        const response = await axios.post(`${API_BASE}/login`, this.form)
        this.$store.dispatch('login', response.data)

        if (requiresAccessSelection(response.data.user)) {
          this.$router.replace({
            name: 'access-selection',
            query: this.$route.query.redirect ? { redirect: this.$route.query.redirect } : {}
          })
          return
        }

        if (this.$route.query.redirect) {
          this.$router.replace(this.$route.query.redirect)
          return
        }

        this.$router.replace(defaultRouteForUser(response.data.user))
      } catch (error) {
        const message = error.response?.data?.errors?.user?.[0] || 'No se pudo iniciar sesion.'
        show_alerta(message, 'error')
      } finally {
        this.isSubmitting = false
      }
    }
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 1.5rem;
  background:
    radial-gradient(circle at top left, rgba(224, 30, 30, 0.12), transparent 24%),
    radial-gradient(circle at bottom right, rgba(15, 47, 95, 0.14), transparent 26%),
    linear-gradient(180deg, #f3efe9 0%, #edf2f8 100%);
}

.login-card {
  width: min(560px, 100%);
  min-height: 620px;
  display: grid;
  align-content: center;
  gap: 1.2rem;
  padding: 3.75rem 2.5rem;
  border-radius: 30px;
  background: rgba(255, 255, 255, 0.97);
  border: 1px solid rgba(15, 47, 95, 0.08);
  box-shadow: 0 30px 70px rgba(15, 47, 95, 0.12);
}

.login-brand {
  display: flex;
  justify-content: center;
  margin-bottom: 1.25rem;
}

.login-logo {
  width: min(250px, 100%);
  height: auto;
}

.login-field {
  display: grid;
  gap: 0.55rem;
}

.login-field span {
  color: #0f2f5f;
  font-size: 0.82rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.login-field input {
  width: 100%;
  min-height: 58px;
  border-radius: 18px;
  border: 1px solid #ccd7e4;
  background: #fff;
  color: #163a69;
  padding: 1rem 1.1rem;
  outline: none;
  font-size: 1rem;
}

.login-field input:focus {
  border-color: #0f2f5f;
  box-shadow: 0 0 0 3px rgba(15, 47, 95, 0.08);
}

.login-help {
  color: #5d6d84;
  font-size: 0.82rem;
}

.login-submit {
  margin-top: 0.4rem;
  min-height: 58px;
  border: none;
  border-radius: 999px;
  background: #e01e1e;
  color: #fff;
  padding: 1rem 1.2rem;
  font-size: 1rem;
  font-weight: 800;
  box-shadow: 0 16px 30px rgba(224, 30, 30, 0.18);
}

.login-submit:disabled {
  opacity: 0.7;
  cursor: wait;
}

.login-recovery-link {
  justify-self: center;
  color: #0f2f5f;
  font-size: 0.96rem;
  font-weight: 700;
  text-decoration: none;
}

.login-recovery-link:hover {
  text-decoration: underline;
}

@media (max-width: 575.98px) {
  .login-card {
    min-height: auto;
    padding: 2.5rem 1.4rem;
    border-radius: 24px;
  }
}
</style>
