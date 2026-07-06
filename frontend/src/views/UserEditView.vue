<template>
  <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  
    <div class="modal-dialog modal-lg modal-dialog-scrollable volunteer-modal-dialog">
      <div class="modal-content volunteer-modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="editUserModalLabel">
            <i class="fa-solid fa-user-edit me-2"></i>Editar perfil
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body volunteer-modal-body">
          <form @submit.prevent="guardar">
            <div class="row g-3">
              <div class="col-md-12" v-if="!esVoluntario">
                <label for="edit-username" class="form-label">Username</label>
                <input id="edit-username" v-model.trim="username" type="text" class="form-control" required>
              </div>

              <template v-if="esVoluntario">
                <div class="col-12">
                  <div class="volunteer-section-title">Datos del voluntario</div>
                </div>

                <div class="col-md-4">
                  <label for="edit-registro-filial" class="form-label">Número de registro</label>
                  <input id="edit-registro-filial" v-model.trim="registro_filial" type="text" class="form-control" required>
                </div>

                <div class="col-md-4">
                  <label for="edit-rut" class="form-label">RUT</label>
                  <input id="edit-rut" v-model.trim="rut" type="text" class="form-control" required>
                </div>

                <div class="col-md-4">
                  <label for="edit-filial" class="form-label">Filial</label>
                  <select id="edit-filial" v-model="filial_id" class="form-select" required>
                    <option value="">Selecciona una filial</option>
                    <option v-for="filial in filialesOptions" :key="filial.id" :value="filial.id">
                      {{ filial.nombre }}
                    </option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="edit-nombres" class="form-label">Nombres</label>
                  <input id="edit-nombres" v-model.trim="nombres" type="text" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label for="edit-apellidos" class="form-label">Apellidos</label>
                  <input id="edit-apellidos" v-model.trim="apellidos" type="text" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label for="edit-correo-electronico" class="form-label">Correo electrónico</label>
                  <input id="edit-correo-electronico" v-model.trim="correo_electronico" type="email" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="edit-celular" class="form-label">Celular</label>
                  <input id="edit-celular" v-model.trim="celular" type="text" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="edit-nacionalidad" class="form-label">Nacionalidad</label>
                  <input id="edit-nacionalidad" v-model.trim="nacionalidad" type="text" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="edit-fecha-nacimiento" class="form-label">Fecha de nacimiento</label>
                  <input id="edit-fecha-nacimiento" v-model="fecha_nacimiento" type="date" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="edit-fecha-incorporacion" class="form-label">Fecha de incorporación</label>
                  <input id="edit-fecha-incorporacion" v-model="fecha_incorporacion" type="date" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="edit-nivel-escolaridad" class="form-label">Nivel de escolaridad</label>
                  <select id="edit-nivel-escolaridad" v-model="nivel_escolaridad" class="form-select">
                    <option value="">Selecciona una opción</option>
                    <option v-for="option in escolaridadOptions" :key="option" :value="option">
                      {{ option }}
                    </option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="edit-estado-civil" class="form-label">Estado civil</label>
                  <select id="edit-estado-civil" v-model="estado_civil" class="form-select">
                    <option value="">Selecciona una opción</option>
                    <option v-for="option in estadoCivilOptions" :key="option" :value="option">
                      {{ option }}
                    </option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="edit-ocupacion" class="form-label">Ocupación</label>
                  <input id="edit-ocupacion" v-model.trim="ocupacion" type="text" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="edit-grupo-sanguineo" class="form-label">Grupo sanguíneo</label>
                  <input id="edit-grupo-sanguineo" v-model.trim="grupo_sanguineo" type="text" class="form-control" placeholder="Ej: A+, O-, AB+">
                </div>

                <div class="col-md-6">
                  <label for="edit-domicilio" class="form-label">Domicilio</label>
                  <input id="edit-domicilio" v-model.trim="domicilio" type="text" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="edit-contacto-emergencia-nombre" class="form-label">Contacto de emergencia</label>
                  <input id="edit-contacto-emergencia-nombre" v-model.trim="contacto_emergencia_nombre" type="text" class="form-control" placeholder="Nombre del contacto">
                </div>

                <div class="col-md-6">
                  <label for="edit-contacto-emergencia-numero" class="form-label">Número de emergencia</label>
                  <input id="edit-contacto-emergencia-numero" v-model.trim="contacto_emergencia_numero" type="text" class="form-control" placeholder="Número de teléfono del contacto">
                </div>

                <div class="col-md-6">
                  <label for="edit-enfermedades" class="form-label">Enfermedades</label>
                  <textarea id="edit-enfermedades" v-model.trim="enfermedades" class="form-control" rows="3"></textarea>
                </div>

                <div class="col-md-6">
                  <label for="edit-alergias" class="form-label">Alergias</label>
                  <textarea id="edit-alergias" v-model.trim="alergias" class="form-control" rows="3"></textarea>
                </div>

                <div class="col-md-6">
                  <label for="edit-foto-perfil" class="form-label">Foto de perfil</label>
                  <input
                    id="edit-foto-perfil"
                    type="file"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                    @change="onPhotoSelected"
                  >
                </div>

                <div class="col-md-6">
                  <label for="edit-permission-level" class="form-label">Rol</label>
                  <select id="edit-permission-level" v-model="permissionLevel" class="form-select">
                    <option value="usuario">Usuario</option>
                    <option value="administrador">Administrador</option>
                  </select>
                </div>
              </template>

              <div class="col-12">
                <label for="edit-password" class="form-label">Nueva contraseña</label>
                <input
                  id="edit-password"
                  v-model.trim="password"
                  type="password"
                  class="form-control"
                  placeholder="Déjala vacía si no quieres cambiarla"
                >
              </div>
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" @click="guardar">
            <i class="fa-solid fa-save me-1"></i>Guardar cambios
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
  name: 'UserEditView',
  props: {
    userId: {
      type: [Number, String, null],
      default: null
    }
  },
  data() {
    return {
      id: null,
      username: '',
      selectedRoles: [],
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
      permissionLevel: 'usuario',
      password: '',
      url: buildApiUrl('user'),
      rolesUrl: buildApiUrl('role'),
      filialesUrl: buildApiUrl('filiales'),
      modalInstance: null,
      escolaridadOptions: ESCOLARIDAD_OPTIONS,
      estadoCivilOptions: ESTADO_CIVIL_OPTIONS
    }
  },
  computed: {
    availableRoleOptions() {
      const preferredOrder = {
        administrador: 0,
        voluntario: 1,
        'secretario-directiva': 2,
        'encargada-finanzas': 3
      }

      return [...this.rolesOptions].sort((a, b) => {
        const orderA = preferredOrder[a.clave] ?? 99
        const orderB = preferredOrder[b.clave] ?? 99

        if (orderA !== orderB) {
          return orderA - orderB
        }

        return a.nombre.localeCompare(b.nombre)
      })
    },
    selectedRoleDetails() {
      return this.rolesOptions.filter((role) => this.selectedRoles.includes(role.id))
    },
    esVoluntario() {
      return this.selectedRoleDetails.some((role) => role.clave === 'voluntario')
    }
  },
  watch: {
    userId: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.id = newVal
          this.getUser()
        }
      }
    }
  },
  async mounted() {
    this.modalInstance = new Modal(document.getElementById('editUserModal'), { backdrop: 'static', keyboard: false })
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
    },
    show() {
      this.modalInstance.show()
    },
    hide() {
      this.modalInstance.hide()
    },
    roleDescription(role) {
      const descriptions = {
        administrador: 'Acceso administrativo del sistema',
        voluntario: 'Perfil con ficha completa de voluntario',
        'secretario-directiva': 'Gestión de voluntarios, actividades y actas',
        'encargada-finanzas': 'Acceso a reportes y gestión financiera'
      }

      return descriptions[role.clave] || role.clave
    },
    onPhotoSelected(event) {
      this.foto_perfil = event.target.files?.[0] || null
    },
    appendIfFilled(formData, key, value) {
      if (value !== null && value !== undefined && String(value).trim() !== '') {
        formData.append(key, String(value).trim())
      }
    },
    getRoleIdByKey(roleKey) {
      return this.rolesOptions.find((role) => role.clave === roleKey)?.id || null
    },
    async getUser() {
      if (this.rolesOptions.length === 0 || this.filialesOptions.length === 0) {
        await Promise.all([this.fetchRoles(), this.fetchFiliales()])
      }

      const response = await axios.get(`${this.url}/${this.id}`)
      const user = response.data
      const voluntario = user.voluntario || null

      this.username = user.username || ''
      this.selectedRoles = (user.roles || []).map((role) => role.id)
      this.permissionLevel = (user.roles || []).some((role) => role?.clave === 'administrador') ? 'administrador' : 'usuario'
      this.registro_filial = voluntario?.registro_filial || ''
      this.filial_id = voluntario?.filial_id || ''
      this.rut = voluntario?.rut || ''
      this.nombres = voluntario?.nombres || ''
      this.apellidos = voluntario?.apellidos || ''
      this.correo_electronico = voluntario?.correo_electronico || ''
      this.nacionalidad = voluntario?.nacionalidad || ''
      this.fecha_nacimiento = voluntario?.fecha_nacimiento || ''
      this.fecha_incorporacion = voluntario?.fecha_incorporacion || ''
      this.nivel_escolaridad = voluntario?.nivel_escolaridad || ''
      this.estado_civil = voluntario?.estado_civil || ''
      this.ocupacion = voluntario?.ocupacion || ''
      this.grupo_sanguineo = voluntario?.grupo_sanguineo || ''
      this.celular = voluntario?.celular || ''
      this.domicilio = voluntario?.domicilio || ''
      this.enfermedades = voluntario?.enfermedades || ''
      this.alergias = voluntario?.alergias || ''
      this.contacto_emergencia_nombre = voluntario?.contacto_emergencia_nombre || ''
      this.contacto_emergencia_numero = voluntario?.contacto_emergencia_numero || ''
      this.foto_perfil = null
      this.password = ''
    },
    buildFormData() {
      const formData = new FormData()
      formData.append('_method', 'PUT')

      if (!this.esVoluntario) {
        formData.append('username', this.username.trim())
      }

      const roleIds = this.esVoluntario
        ? [this.getRoleIdByKey('voluntario'), this.permissionLevel === 'administrador' ? this.getRoleIdByKey('administrador') : null]
        : [...this.selectedRoles]

      ;[...new Set(roleIds.filter(Boolean))].forEach((roleId) => formData.append('roles[]', roleId))

      if (this.esVoluntario) {
        formData.append('registro_filial', this.registro_filial.trim())
        formData.append('filial_id', String(this.filial_id))
        formData.append('rut', this.rut.trim())
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

      if (this.password) {
        formData.append('password', this.password)
      }

      return formData
    },
    validateForm() {
      if (!this.esVoluntario && !this.selectedRoles.length) {
        show_alerta('Debes seleccionar al menos un rol.', 'warning')
        return false
      }

      if (this.esVoluntario && !this.getRoleIdByKey('voluntario')) {
        show_alerta('No se encontró el rol base de voluntario.', 'error')
        return false
      }

      if (!this.esVoluntario && !this.username.trim()) {
        show_alerta('Debes ingresar el username del usuario.', 'warning', 'edit-username')
        return false
      }

      if (this.esVoluntario) {
        if (!this.registro_filial.trim()) {
          show_alerta('Debes ingresar el número de registro.', 'warning', 'edit-registro-filial')
          return false
        }
        if (!this.filial_id) {
          show_alerta('Debes seleccionar una filial.', 'warning', 'edit-filial')
          return false
        }
        if (!this.rut.trim() || !this.nombres.trim() || !this.apellidos.trim()) {
          show_alerta('Completa RUT, nombres y apellidos del voluntario.', 'warning')
          return false
        }
      }

      return true
    },
    async guardar() {
      if (!this.validateForm()) {
        return
      }

      try {
        const response = await axios.post(`${this.url}/${this.id}`, this.buildFormData(), {
          headers: { 'Content-Type': 'multipart/form-data' }
        })

        if (response.status === 200) {
          show_alerta('Perfil actualizado correctamente.', 'success')
          this.hide()
          this.$emit('user-updated')
        }
      } catch (error) {
        const errores = error.response?.data?.errors || {}
        const listado = Object.values(errores).flat().join(' ')
        show_alerta(listado || 'No se pudo actualizar el perfil.', 'error')
      }
    }
  }
}
</script>

<style scoped>
#editUserModal {
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
}

.role-card.selected {
  border-color: #dc3545;
  box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.15);
}

.volunteer-section-title {
  font-weight: 700;
  color: #dc3545;
  border-bottom: 1px solid #f1d7d7;
  padding-bottom: 0.35rem;
}

@media (max-width: 991.98px) {
  #editUserModal {
    padding: 4.25rem 0.5rem 1rem;
  }

  #editUserModal.show {
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
  #editUserModal {
    padding: 5.25rem 0.5rem;
  }

  #editUserModal.show {
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


