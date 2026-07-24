<template>
  <div class="login-page">
    <form class="login-card" @submit.prevent="submitLogin">
      <router-link to="/portada" class="login-back-link"><i class="fa-solid fa-arrow-left"></i> Volver</router-link>
      <div class="login-brand">
        <router-link to="/portada" aria-label="Ir a Inicio y novedades"><img src="@/assets/LogoHorizontal.svg" alt="Cruz Roja" class="login-logo"></router-link>
      </div>

      <label class="login-field">
        <span>ID de acceso</span>
        <input
          v-model="form.user"
          type="text"
          autocomplete="username"
          placeholder="Ingresa tu RUT"
          required
          @input="onIdentifierInput"
        >
        <small class="login-help">Ingresa tu RUT como ID de acceso. Los administradores sin perfil de voluntario pueden usar su nombre de usuario.</small>
      </label>

      <label class="login-field">
        <span>Contraseña</span>
        <div class="password-field">
          <input
            v-model="form.id"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="current-password"
            placeholder="Ingresa tu contraseña"
            required
          >
          <button
            type="button"
            class="password-toggle"
            :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
            :title="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
            @click="showPassword = !showPassword"
          >
            <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
          </button>
        </div>
      </label>

      <button type="submit" class="login-submit" :disabled="isSubmitting">
        {{ isSubmitting ? 'Ingresando...' : 'Iniciar sesión' }}
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
      showPassword: false,
      isSubmitting: false
    }
  },
  methods: {
    normalizeRut(value) {
      return String(value || '')
        .replace(/[^0-9kK]/g, '')
        .toUpperCase()
    },
    formatRut(value) {
      const cleaned = this.normalizeRut(value)
      if (!cleaned) return ''

      const body = cleaned.slice(0, -1)
      const verifier = cleaned.slice(-1)
      const formattedBody = body.replace(/\B(?=(\d{3})+(?!\d))/g, '.')

      return body ? `${formattedBody}-${verifier}` : verifier
    },
    onIdentifierInput(event) {
      const value = event.target.value

      if (/^[0-9kK.\-]*$/.test(value)) {
        const formatted = this.formatRut(value)
        this.form.user = formatted
        event.target.value = formatted
        return
      }

      this.form.user = value.trimStart()
    },
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
        const message = error.response?.data?.errors?.user?.[0] || 'No se pudo iniciar sesión.'
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
  position: relative;
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 1.5rem;
  background:
    radial-gradient(circle at top left, rgba(224, 30, 30, 0.12), transparent 24%),
    radial-gradient(circle at bottom right, rgba(15, 47, 95, 0.14), transparent 26%),
    linear-gradient(180deg, #f3efe9 0%, #edf2f8 100%);
}

.login-back-link {
  position: absolute;
  top: 1.25rem;
  left: 1.25rem;
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0.75rem 1rem;
  border: 1px solid rgba(15, 47, 95, 0.14);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.94);
  color: var(--cr-navy-dark);
  font-weight: 800;
  text-decoration: none;
  box-shadow: 0 10px 24px var(--cr-navy-shadow);
}

.login-back-link:hover {
  color: var(--cr-red);
  border-color: rgba(224, 30, 30, 0.35);
}

.login-card {
  position: relative;
  width: min(560px, 100%);
  min-height: 620px;
  display: grid;
  align-content: center;
  gap: 1.2rem;
  padding: 3.75rem 2.5rem;
  border-radius: 30px;
  background: rgba(255, 255, 255, 0.97);
  border: 1px solid var(--cr-navy-shadow);
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
  color: var(--cr-navy-dark);
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
  background: var(--cr-white);
  color: var(--cr-navy-medium);
  padding: 1rem 1.1rem;
  outline: none;
  font-size: 1rem;
}

.login-field input:focus {
  border-color: var(--cr-navy-dark);
  box-shadow: 0 0 0 3px var(--cr-navy-shadow);
}

.password-field {
  position: relative;
}

.password-field input {
  padding-right: 3.5rem;
}

.password-toggle {
  position: absolute;
  top: 50%;
  right: 0.65rem;
  display: grid;
  width: 2.5rem;
  height: 2.5rem;
  padding: 0;
  border: 0;
  border-radius: 50%;
  background: transparent;
  color: var(--cr-navy-dark);
  place-items: center;
  transform: translateY(-50%);
}

.password-toggle:hover,
.password-toggle:focus-visible {
  background: var(--cr-navy-shadow);
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
  background: var(--cr-red);
  color: var(--cr-white);
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
  color: var(--cr-navy-dark);
  font-size: 0.96rem;
  font-weight: 700;
  text-decoration: none;
}

.login-recovery-link:hover {
  text-decoration: underline;
}

@media (max-width: 575.98px) {
  .login-page {
    padding-top: 1.5rem;
  }

  .login-card {
    min-height: auto;
    padding: 2.5rem 1.4rem;
    border-radius: 24px;
  }
}
</style>
