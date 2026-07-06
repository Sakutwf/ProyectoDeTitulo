<template>
  <div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  
    <div class="modal-dialog modal-lg modal-dialog-scrollable volunteer-modal-dialog">
      <div class="modal-content volunteer-modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="newUserModalLabel">
            <i class="fa-solid fa-user-plus me-2"></i>Nuevo perfil
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body volunteer-modal-body">
          <form @submit.prevent="guardar">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Tipo de perfil</label>
                <div class="role-grid">
                  <label
                    v-for="role in availableRoleOptions"
                    :key="role.id"
                    class="role-card"
                    :class="{ selected: selectedRoleId === role.id }"
                  >
                    <input
                      :id="`create-role-${role.id}`"
                      v-model="selectedRoleId"
                      class="form-check-input"
                      type="radio"
                      name="create-role"
                      :value="role.id"
                    >
                    <div>
                      <div class="fw-semibold">{{ role.nombre }}</div>
                      <small class="text-muted">{{ roleDescription(role) }}</small>
                    </div>
                  </label>
                </div>
              </div>

              <div v-if="!selectedRoleId" class="col-12">
                <div class="alert alert-light border mb-0">
                  Selecciona si crearás un perfil de administrador o de voluntario.
                </div>
              </div>

              <template v-if="esAdministrador">
                <div class="col-md-6">
                  <label for="create-username" class="form-label">Nombre de usuario</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                    <input
                      id="create-username"
                      v-model.trim="username"
                      type="text"
                      class="form-control"
                      required
                      placeholder="Ej: admin"
                    >
                  </div>
                </div>

                <div class="col-md-6">
                  <label for="create-password" class="form-label">Contraseña</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input
                      id="create-password"
                      v-model.trim="password"
                      type="password"
                      class="form-control"
                      required
                      minlength="6"
                      autocomplete="new-password"
                      placeholder="Minimo 6 caracteres"
                    >
                  </div>
                </div>

                <div class="col-12">
                  <div class="alert alert-info mb-0">
                    El perfil administrativo utilizará el nombre de usuario y la contraseña definidas aquí.
                  </div>
                </div>
              </template>

              <template v-if="esVoluntario">
                <div class="col-12">
                  <div class="alert alert-warning mb-0">
                    <strong>Clave inicial:</strong> al crear el perfil se asignará
                    <code>{{ volunteerDefaultPassword }}</code>.
                    <span class="d-block mt-2">
                      Para perfil de voluntario, el username será el RUT.
                    </span>
                  </div>
                </div>
                <div class="col-12">
                  <div class="volunteer-section-title">Datos del voluntario</div>
                </div>

                <div class="col-md-4">
                  <label for="create-registro-filial" class="form-label">Número de registro</label>
                  <input id="create-registro-filial" v-model.trim="registro_filial" type="text" class="form-control" placeholder="Ej: 623" required>
                </div>

                <div class="col-md-4">
                  <label for="create-rut" class="form-label">RUT</label>
                  <input id="create-rut" v-model="rut" type="text" class="form-control" placeholder="Ej: 12.345.678-9" maxlength="12" required @input="onRutInput">
                </div>

                <div class="col-md-4">
                  <label for="create-filial" class="form-label">Filial</label>
                  <select id="create-filial" v-model="filial_id" class="form-select" required>
                    <option value="">Selecciona una filial</option>
                    <option v-for="filial in filialesOptions" :key="filial.id" :value="filial.id">
                      {{ filial.nombre }}
                    </option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="create-nombres" class="form-label">Nombres</label>
                  <input id="create-nombres" v-model.trim="nombres" type="text" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label for="create-apellidos" class="form-label">Apellidos</label>
                  <input id="create-apellidos" v-model.trim="apellidos" type="text" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label for="create-correo-electronico" class="form-label">Correo electrónico</label>
                  <input id="create-correo-electronico" v-model.trim="correo_electronico" type="email" class="form-control" placeholder="correo@ejemplo.com">
                </div>

                <div class="col-md-6">
                  <label for="create-celular" class="form-label">Celular</label>
                  <input id="create-celular" v-model.trim="celular" type="text" class="form-control" placeholder="Ej: +56912345678">
                </div>

                <div class="col-md-6">
                  <label for="create-nacionalidad" class="form-label">Nacionalidad</label>
                  <input id="create-nacionalidad" v-model.trim="nacionalidad" type="text" class="form-control" placeholder="Ej: chilena">
                </div>

                <div class="col-md-6">
                  <label for="create-fecha-nacimiento" class="form-label">Fecha de nacimiento</label>
                  <input id="create-fecha-nacimiento" v-model="fecha_nacimiento" type="date" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="create-fecha-incorporacion" class="form-label">Fecha de incorporación</label>
                  <input id="create-fecha-incorporacion" v-model="fecha_incorporacion" type="date" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="create-nivel-escolaridad" class="form-label">Nivel de escolaridad</label>
                  <select id="create-nivel-escolaridad" v-model="nivel_escolaridad" class="form-select">
                    <option value="">Selecciona una opción</option>
                    <option v-for="option in escolaridadOptions" :key="option" :value="option">
                      {{ option }}
                    </option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="create-estado-civil" class="form-label">Estado civil</label>
                  <select id="create-estado-civil" v-model="estado_civil" class="form-select">
                    <option value="">Selecciona una opción</option>
                    <option v-for="option in estadoCivilOptions" :key="option" :value="option">
                      {{ option }}
                    </option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="create-ocupacion" class="form-label">Ocupación</label>
                  <input id="create-ocupacion" v-model.trim="ocupacion" type="text" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="create-grupo-sanguineo" class="form-label">Grupo sanguíneo</label>
                  <input id="create-grupo-sanguineo" v-model.trim="grupo_sanguineo" type="text" class="form-control" placeholder="Ej: A+, O-, AB+">
                </div>

                <div class="col-md-6">
                  <label for="create-domicilio" class="form-label">Domicilio</label>
                  <input id="create-domicilio" v-model.trim="domicilio" type="text" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="create-contacto-emergencia-nombre" class="form-label">Contacto de emergencia</label>
                  <input id="create-contacto-emergencia-nombre" v-model.trim="contacto_emergencia_nombre" type="text" class="form-control" placeholder="Nombre del contacto">
                </div>

                <div class="col-md-6">
                  <label for="create-contacto-emergencia-numero" class="form-label">Numero de emergencia</label>
                  <input id="create-contacto-emergencia-numero" v-model.trim="contacto_emergencia_numero" type="text" class="form-control" placeholder="Número de teléfono del contacto">
                </div>

                <div class="col-md-6">
                  <label for="create-enfermedades" class="form-label">Enfermedades</label>
                  <textarea id="create-enfermedades" v-model.trim="enfermedades" class="form-control" rows="3"></textarea>
                </div>

                <div class="col-md-6">
                  <label for="create-alergias" class="form-label">Alergias</label>
                  <textarea id="create-alergias" v-model.trim="alergias" class="form-control" rows="3"></textarea>
                </div>

                <div class="col-md-6">
                  <label for="create-foto-perfil" class="form-label">Foto de perfil</label>
                  <input
                    id="create-foto-perfil"
                    type="file"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                    @change="onPhotoSelected"
                  >
                </div>
              </template>
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" @click="guardar">
            <i class="fa-solid fa-save me-1"></i>Guardar perfil
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { show_alerta } from '../funciones'
import { Modal } from 'bootstrap'
import { buildApiUrl } from '../config/api'

const ADMIN_ROLE_KEY = 'administrador'
const VOLUNTEER_ROLE_KEY = 'voluntario'
const VOLUNTEER_DEFAULT_PASSWORD = 'cruzRojaCco26'
const ESCOLARIDAD_OPTIONS = [
  'Sin escolaridad',
  'Educación básica incompleta',
  'Educación básica completa',
  'Educación media incompleta',
  'Educación media completa',
  'Educación técnica de nivel superior incompleta',
  'Educación técnica de nivel superior completa',
  'Educación universitaria incompleta',
  'Educación universitaria completa',
  'Postítulo o diplomado',
  'Magíster',
  'Doctorado'
]
const ESTADO_CIVIL_OPTIONS = [
  'Soltero(a)',
  'Casado(a)',
  'Conviviente civil',
  'Divorciado(a)',
  'Viudo(a)'
]
export default {
  name: 'UserCreateView',
  data() {
    return {
      selectedRoleId: null,
      username: '',
      password: '',
      rolesOptions: [],
      filialesOptions: [],
      registro_filial: '',
      filial_id: '',
      rut: '',
      nombres: '',
      apellidos: '',
      correo_electronico: '',
      nacionalidad: '',
      fecha_nacimiento: '',
      fecha_incorporacion: '',
      nivel_escolaridad: '',
      estado_civil: '',
      ocupacion: '',
      grupo_sanguineo: '',
      celular: '',
      domicilio: '',
      enfermedades: '',
      alergias: '',
      contacto_emergencia_nombre: '',
      contacto_emergencia_numero: '',
      foto_perfil: null,
      url: buildApiUrl('user'),
      rolesUrl: buildApiUrl('role'),
      filialesUrl: buildApiUrl('filiales'),
      modalInstance: null,
      volunteerDefaultPassword: VOLUNTEER_DEFAULT_PASSWORD,
      escolaridadOptions: ESCOLARIDAD_OPTIONS,
      estadoCivilOptions: ESTADO_CIVIL_OPTIONS
    }
  },
  computed: {
    availableRoleOptions() {
      const allowedOrder = {
        [ADMIN_ROLE_KEY]: 0,
        [VOLUNTEER_ROLE_KEY]: 1
      }

      return this.rolesOptions
        .filter((role) => [ADMIN_ROLE_KEY, VOLUNTEER_ROLE_KEY].includes(role.clave))
        .sort((a, b) => allowedOrder[a.clave] - allowedOrder[b.clave])
    },
    selectedRoleDetails() {
      return this.availableRoleOptions.find((role) => role.id === this.selectedRoleId) || null
    },
    esAdministrador() {
      return this.selectedRoleDetails?.clave === ADMIN_ROLE_KEY
    },
    esVoluntario() {
      return this.selectedRoleDetails?.clave === VOLUNTEER_ROLE_KEY
    }
  },
  watch: {
    selectedRoleId() {
      if (this.esVoluntario) {
        this.clearAdminFields()
        return
      }

      if (this.esAdministrador) {
        this.clearVoluntarioFields()
      }
    }
  },
  async mounted() {
    this.modalInstance = new Modal(document.getElementById('newUserModal'), { backdrop: 'static', keyboard: false })
    await Promise.all([this.fetchRoles(), this.fetchFiliales()])
  },
  methods: {
    async fetchRoles() {
      const response = await axios.get(this.rolesUrl)
      this.rolesOptions = response.data
    },
    async fetchFiliales() {
      const response = await axios.get(this.filialesUrl)
      this.filialesOptions = response.data
      this.applyDefaultFilialSelection()
    },
    show() {
      this.resetForm()
      this.modalInstance.show()
    },
    hide() {
      this.modalInstance.hide()
    },
    roleDescription(role) {
      if (role.clave === ADMIN_ROLE_KEY) {
        return 'Acceso administrativo del sistema'
      }

      return 'Perfil con ficha completa de voluntario'
    },
    onPhotoSelected(event) {
      this.foto_perfil = event.target.files?.[0] || null
    },
    normalizeRut(value) {
      return String(value || '')
        .replace(/[^0-9kK]/g, '')
        .toUpperCase()
    },
    formatRut(value) {
      const cleaned = this.normalizeRut(value)

      if (!cleaned) {
        return ''
      }

      const body = cleaned.slice(0, -1)
      const dv = cleaned.slice(-1)
      const formattedBody = body.replace(/\B(?=(\d{3})+(?!\d))/g, '.')

      return body ? `${formattedBody}-${dv}` : dv
    },
    isValidRut(value) {
      const cleaned = this.normalizeRut(value)

      if (!/^[0-9]{7,8}[0-9K]$/.test(cleaned)) {
        return false
      }

      const body = cleaned.slice(0, -1)
      const dv = cleaned.slice(-1)
      let sum = 0
      let multiplier = 2

      for (let index = body.length - 1; index >= 0; index -= 1) {
        sum += Number(body[index]) * multiplier
        multiplier = multiplier === 7 ? 2 : multiplier + 1
      }

      const remainder = 11 - (sum % 11)
      const expectedDv = remainder === 11 ? '0' : remainder === 10 ? 'K' : String(remainder)

      return dv === expectedDv
    },
    onRutInput(event) {
      const formatted = this.formatRut(event.target.value)
      this.rut = formatted
      event.target.value = formatted
    },
    clearPhotoSelection() {
      this.foto_perfil = null
      const input = document.getElementById('create-foto-perfil')
      if (input) {
        input.value = ''
      }
    },
    clearAdminFields() {
      this.username = ''
      this.password = ''
    },
    clearVoluntarioFields() {
      this.registro_filial = ''
      this.filial_id = this.getDefaultFilialId()
      this.rut = ''
      this.nombres = ''
      this.apellidos = ''
      this.correo_electronico = ''
      this.nacionalidad = ''
      this.fecha_nacimiento = ''
      this.fecha_incorporacion = ''
      this.nivel_escolaridad = ''
      this.estado_civil = ''
      this.ocupacion = ''
      this.grupo_sanguineo = ''
      this.celular = ''
      this.domicilio = ''
      this.enfermedades = ''
      this.alergias = ''
      this.contacto_emergencia_nombre = ''
      this.contacto_emergencia_numero = ''
      this.clearPhotoSelection()
    },
    getDefaultFilialId() {
      if (this.filialesOptions.length !== 1) {
        return ''
      }

      return this.filialesOptions[0].id
    },
    applyDefaultFilialSelection() {
      if (this.filialesOptions.length === 1) {
        this.filial_id = this.filialesOptions[0].id
      }
    },
    appendIfFilled(formData, key, value) {
      if (value !== null && value !== undefined && String(value).trim() !== '') {
        formData.append(key, String(value).trim())
      }
    },
    buildFormData() {
      const formData = new FormData()

      if (this.selectedRoleId) {
        formData.append('roles[]', this.selectedRoleId)
      }

      if (this.esAdministrador) {
        formData.append('username', this.username.trim())
        formData.append('password', this.password.trim())
        formData.append('must_change_password', '0')
      }

      if (this.esVoluntario) {
        formData.append('registro_filial', this.registro_filial.trim())
        formData.append('filial_id', String(this.filial_id))
        formData.append('rut', this.formatRut(this.rut))
        formData.append('nombres', this.nombres.trim())
        formData.append('apellidos', this.apellidos.trim())

        this.appendIfFilled(formData, 'correo_electronico', this.correo_electronico)
        this.appendIfFilled(formData, 'nacionalidad', this.nacionalidad)
        this.appendIfFilled(formData, 'fecha_nacimiento', this.fecha_nacimiento)
        this.appendIfFilled(formData, 'fecha_incorporacion', this.fecha_incorporacion)
        this.appendIfFilled(formData, 'nivel_escolaridad', this.nivel_escolaridad)
        this.appendIfFilled(formData, 'estado_civil', this.estado_civil)
        this.appendIfFilled(formData, 'ocupacion', this.ocupacion)
        this.appendIfFilled(formData, 'grupo_sanguineo', this.grupo_sanguineo)
        this.appendIfFilled(formData, 'celular', this.celular)
        this.appendIfFilled(formData, 'domicilio', this.domicilio)
        this.appendIfFilled(formData, 'enfermedades', this.enfermedades)
        this.appendIfFilled(formData, 'alergias', this.alergias)
        this.appendIfFilled(formData, 'contacto_emergencia_nombre', this.contacto_emergencia_nombre)
        this.appendIfFilled(formData, 'contacto_emergencia_numero', this.contacto_emergencia_numero)

        if (this.foto_perfil) {
          formData.append('foto_perfil', this.foto_perfil)
        }
      }

      return formData
    },
    resetForm() {
      this.selectedRoleId = null
      this.clearAdminFields()
      this.clearVoluntarioFields()
    },
    validateForm() {
      if (!this.selectedRoleId) {
        show_alerta('Debes seleccionar el tipo de perfil.', 'warning')
        return false
      }

      if (this.esAdministrador) {
        if (!this.username.trim()) {
          show_alerta('Debes ingresar el nombre de usuario del administrador.', 'warning', 'create-username')
          return false
        }

        if (this.password.trim().length < 6) {
          show_alerta('La contraseña debe tener al menos 6 caracteres.', 'warning', 'create-password')
          return false
        }
      }

      if (this.esVoluntario) {
        if (!this.registro_filial.trim()) {
          show_alerta('Debes ingresar el número de registro.', 'warning', 'create-registro-filial')
          return false
        }
        if (!this.filial_id) {
          show_alerta('Debes seleccionar una filial.', 'warning', 'create-filial')
          return false
        }
        if (!this.rut.trim() || !this.nombres.trim() || !this.apellidos.trim()) {
          show_alerta('Completa RUT, nombres y apellidos del voluntario.', 'warning')
          return false
        }
        if (!this.isValidRut(this.rut)) {
          show_alerta('Debes ingresar un RUT válido con formato XX.XXX.XXX-Y.', 'warning', 'create-rut')
          return false
        }
        this.rut = this.formatRut(this.rut)
      }

      return true
    },
    async guardar() {
      if (!this.validateForm()) {
        return
      }

      try {
        const response = await axios.post(this.url, this.buildFormData(), {
          headers: { 'Content-Type': 'multipart/form-data' }
        })

        if (response.status === 201) {
          const successMessage = this.esAdministrador
            ? 'Perfil administrativo creado correctamente.'
            : `Perfil creado. Clave inicial: ${VOLUNTEER_DEFAULT_PASSWORD}`

          show_alerta(successMessage, 'success')
          this.hide()
          this.$emit('user-created')
          this.resetForm()
        }
      } catch (error) {
        const errores = error.response?.data?.errors || {}
        const listado = Object.values(errores).flat().join(' ')
        show_alerta(listado || 'No se pudo crear el perfil.', 'error')
      }
    }
  }
}
</script>

<style scoped>
#newUserModal {
  padding-top: 4.75rem;
  padding-bottom: 4.75rem;
}

.volunteer-modal-content {
  position: relative;
  max-height: calc(100vh - 9.5rem);
  border-radius: 12px;
  border: none;
}

.volunteer-modal-dialog {
  width: min(840px, calc(100vw - 2rem));
  max-width: min(840px, calc(100vw - 2rem));
  margin: 0 auto;
}

.volunteer-modal-body {
  overflow-y: auto;
  padding: 20px 30px;
}

.role-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 12px;
}

.role-card {
  display: flex;
  gap: 10px;
  padding: 12px;
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  background: #fff;
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.role-card.selected {
  border-color: #dc3545;
  box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.15);
}

.role-card .form-check-input {
  margin-top: 0.25rem;
  flex-shrink: 0;
}

.volunteer-section-title {
  font-weight: 700;
  color: #dc3545;
  border-bottom: 1px solid #f1d7d7;
  padding-bottom: 0.35rem;
}

@media (max-width: 991.98px) {
  #newUserModal {
    padding: 4.25rem 0.5rem 1rem;
  }

  #newUserModal.show {
    display: flex !important;
    align-items: center;
    justify-content: center;
  }

  .volunteer-modal-dialog {
    width: calc(100vw - 1rem);
    max-width: calc(100vw - 1rem);
    margin: auto;
  }

  .volunteer-modal-content {
    max-height: calc(100vh - 5.25rem);
  }

  .volunteer-modal-body {
    padding: 18px 18px 24px;
  }
}

@media (max-width: 575.98px) {
  #newUserModal {
    padding: 5.25rem 0.5rem;
  }

  #newUserModal.show {
    display: flex !important;
    align-items: center;
    justify-content: center;
  }

  .volunteer-modal-dialog {
    width: calc(100vw - 1rem);
    max-width: calc(100vw - 1rem);
    margin: auto;
  }

  .volunteer-modal-content {
    max-height: calc(100vh - 10.5rem);
  }

  .volunteer-modal-body {
    padding: 14px 14px 18px;
  }

  .modal-header,
  .modal-footer {
    padding: 0.75rem 0.9rem;
  }

  .modal-title {
    font-size: 1.05rem;
  }
}
</style>

