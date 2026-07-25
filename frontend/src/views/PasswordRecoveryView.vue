<template>
  <div class="recovery-page">
    <form class="recovery-card" @submit.prevent="isReset ? resetPassword() : requestResetLink()">
      <router-link :to="{ name: 'login' }" class="recovery-back-link">
        <i class="fa-solid fa-arrow-left"></i> Volver
      </router-link>

      <div class="recovery-brand">
        <img src="@/assets/LogoHorizontal.svg" alt="Cruz Roja" class="recovery-logo">
      </div>

      <template v-if="!isReset">
        <div>
          <h1>Recuperar contraseña</h1>
          <p>Ingresa tu RUT. Enviaremos un enlace de recuperación al correo registrado en tu perfil.</p>
        </div>

        <label class="recovery-field">
          <span>RUT</span>
          <input
            v-model="rut"
            type="text"
            autocomplete="username"
            placeholder="Ej: 12.345.678-9"
            required
            @input="onRutInput"
          >
        </label>

        <button type="submit" class="recovery-submit" :disabled="isSubmitting">
          {{ isSubmitting ? 'Enviando...' : 'Enviar enlace de recuperación' }}
        </button>

        <div v-if="requestSent" class="recovery-status" role="status">
          Si el RUT está registrado y tiene un correo asociado, recibirás un enlace de recuperación.
        </div>

        <div class="admin-help">
          <strong>¿No tienes acceso al correo?</strong>
          <span>Contacta a tu administrador para que envíe un nuevo enlace o actualice el correo registrado.</span>
        </div>
      </template>

      <template v-else>
        <div>
          <h1>Nueva contraseña</h1>
          <p>Ingresa y confirma la nueva contraseña para tu cuenta.</p>
        </div>

        <label class="recovery-field">
          <span>Nueva contraseña</span>
          <div class="password-field">
            <input v-model="password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" minlength="8" required>
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

        <label class="recovery-field">
          <span>Confirmar contraseña</span>
          <div class="password-field">
            <input v-model="passwordConfirmation" :type="showPasswordConfirmation ? 'text' : 'password'" autocomplete="new-password" minlength="8" required>
            <button
              type="button"
              class="password-toggle"
              :aria-label="showPasswordConfirmation ? 'Ocultar contraseña' : 'Mostrar contraseña'"
              :title="showPasswordConfirmation ? 'Ocultar contraseña' : 'Mostrar contraseña'"
              @click="showPasswordConfirmation = !showPasswordConfirmation"
            >
              <i :class="showPasswordConfirmation ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
            </button>
          </div>
        </label>

        <button type="submit" class="recovery-submit" :disabled="isSubmitting">
          {{ isSubmitting ? 'Actualizando...' : 'Guardar nueva contraseña' }}
        </button>
      </template>
    </form>
  </div>
</template>

<script>
import axios from 'axios'
import { buildApiUrl } from '../config/api'
import { show_alerta } from '../funciones'

export default {
  name: 'PasswordRecoveryView',
  data() {
    return {
      rut: String(this.$route.query.rut || ''),
      password: '',
      passwordConfirmation: '',
      showPassword: false,
      showPasswordConfirmation: false,
      isSubmitting: false,
      requestSent: false
    }
  },
  computed: {
    isReset() {
      return Boolean(this.$route.query.token && this.$route.query.rut)
    }
  },
  methods: {
    formatRut(value) {
      const cleaned = String(value || '').replace(/[^0-9kK]/g, '').toUpperCase()
      if (!cleaned) return ''

      const body = cleaned.slice(0, -1)
      const verifier = cleaned.slice(-1)
      return body ? `${body.replace(/\B(?=(\d{3})+(?!\d))/g, '.')}-${verifier}` : verifier
    },
    onRutInput(event) {
      this.rut = this.formatRut(event.target.value)
      event.target.value = this.rut
    },
    async requestResetLink() {
      this.isSubmitting = true
      this.requestSent = false

      try {
        await axios.post(buildApiUrl('password/forgot'), { rut: this.rut })
        this.requestSent = true
      } catch (error) {
        const message = error.response?.status === 429
          ? 'Espera unos minutos antes de solicitar otro enlace.'
          : 'No se pudo procesar la solicitud. Intenta nuevamente.'
        show_alerta(message, 'error')
      } finally {
        this.isSubmitting = false
      }
    },
    async resetPassword() {
      if (this.password.length < 8) {
        show_alerta('La contraseña debe tener al menos 8 caracteres.', 'warning')
        return
      }

      if (this.password !== this.passwordConfirmation) {
        show_alerta('Las contraseñas no coinciden.', 'warning')
        return
      }

      this.isSubmitting = true

      try {
        await axios.post(buildApiUrl('password/reset'), {
          rut: this.$route.query.rut,
          token: this.$route.query.token,
          password: this.password,
          password_confirmation: this.passwordConfirmation
        })
        await show_alerta('Contraseña actualizada. Ya puedes iniciar sesión.', 'success')
        this.$router.replace({ name: 'login' })
      } catch (error) {
        show_alerta(error.response?.data?.message || 'No se pudo actualizar la contraseña.', 'error')
      } finally {
        this.isSubmitting = false
      }
    }
  }
}
</script>

<style scoped>
.recovery-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 1.5rem;
  background:
    radial-gradient(circle at top left, rgba(224, 30, 30, 0.12), transparent 24%),
    radial-gradient(circle at bottom right, rgba(15, 47, 95, 0.14), transparent 26%),
    linear-gradient(180deg, #f3efe9 0%, #edf2f8 100%);
}

.recovery-card {
  width: min(560px, 100%);
  display: grid;
  gap: 1.2rem;
  padding: 2rem 2.5rem 2.5rem;
  border: 1px solid var(--cr-navy-shadow);
  border-radius: 30px;
  background: rgba(255, 255, 255, 0.97);
  box-shadow: 0 30px 70px rgba(15, 47, 95, 0.12);
}

.recovery-back-link {
  justify-self: start;
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0.75rem 1rem;
  border: 1px solid rgba(15, 47, 95, 0.14);
  border-radius: 999px;
  color: var(--cr-navy-dark);
  font-weight: 800;
  text-decoration: none;
}

.recovery-brand {
  display: flex;
  justify-content: center;
}

.recovery-logo {
  width: min(230px, 100%);
  height: auto;
}

h1 {
  margin: 0 0 0.45rem;
  color: var(--cr-navy-dark);
  font-size: clamp(1.55rem, 5vw, 2rem);
}

p {
  margin: 0;
  color: #5d6d84;
}

.recovery-field {
  display: grid;
  gap: 0.55rem;
}

.recovery-field span {
  color: var(--cr-navy-dark);
  font-size: 0.82rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.recovery-field input {
  width: 100%;
  min-height: 58px;
  padding: 1rem 1.1rem;
  border: 1px solid #ccd7e4;
  border-radius: 18px;
  outline: none;
}

.recovery-field input:focus {
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
.recovery-submit {
  min-height: 58px;
  padding: 1rem 1.2rem;
  border: 0;
  border-radius: 999px;
  background: var(--cr-red);
  color: var(--cr-white);
  font-weight: 800;
}

.recovery-submit:disabled {
  opacity: 0.7;
  cursor: wait;
}

.recovery-status,
.admin-help {
  padding: 1rem;
  border-radius: 16px;
  font-size: 0.92rem;
}

.recovery-status {
  background: #eaf7ef;
  color: #17633a;
}

.admin-help {
  display: grid;
  gap: 0.25rem;
  background: #eef3f9;
  color: var(--cr-navy-dark);
}

@media (max-width: 575.98px) {
  .recovery-page {
    padding: 0.5rem;
  }

  .recovery-card {
    min-height: calc(100vh - 1rem);
    align-content: start;
    padding: 1rem 1.4rem 2rem;
    border-radius: 24px;
  }
}
</style>