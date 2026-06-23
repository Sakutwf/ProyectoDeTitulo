<template>
  <div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="newUserModalLabel">
            <i class="fa-solid fa-user-plus me-2"></i>Nuevo perfil
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <form @submit.prevent="guardar">
            <div class="row g-3">
              <div class="col-md-6" v-if="!esVoluntario">
                <label for="create-name" class="form-label">Nombre</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                  <input
                    id="create-name"
                    v-model.trim="name"
                    type="text"
                    class="form-control"
                    required
                    placeholder="Nombre visible del usuario"
                  >
                </div>
              </div>

              <div class="col-md-6">
                <label for="create-email" class="form-label">Correo electrónico</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                  <input
                    id="create-email"
                    v-model.trim="email"
                    type="email"
                    class="form-control"
                    required
                    placeholder="correo@ejemplo.com"
                  >
                </div>
              </div>

              <div class="col-md-6">
                <label for="create-estado" class="form-label">Estado</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-circle-check"></i></span>
                  <select id="create-estado" v-model="estado" class="form-select" required>
                    <option :value="true">Activo</option>
                    <option :value="false">Inactivo</option>
                  </select>
                </div>
              </div>

              <div class="col-md-12">
                <label class="form-label">Roles</label>
                <div class="role-grid">
                  <label
                    v-for="role in rolesOptions"
                    :key="role.id"
                    class="role-card"
                    :class="{ selected: selectedRoles.includes(role.id) }"
                  >
                    <input
                      :id="`create-role-${role.id}`"
                      v-model="selectedRoles"
                      class="form-check-input"
                      type="checkbox"
                      :value="role.id"
                    >
                    <div>
                      <div class="fw-semibold">{{ role.nombre }}</div>
                      <small class="text-muted">{{ role.clave }}</small>
                    </div>
                  </label>
                </div>
              </div>

              <div class="col-12">
                <div class="alert alert-warning mb-0">
                  <strong>Clave inicial:</strong> al crear el perfil se asignará
                  <code>cruzRojaCco26</code>. Luego podrás cambiarla desde la edición del usuario.
                </div>
              </div>

              <template v-if="esVoluntario">
                <div class="col-12">
                  <div class="volunteer-section-title">Datos del voluntario</div>
                </div>

                <div class="col-md-4">
                  <label for="create-n-registro" class="form-label">N° de registro</label>
                  <input id="create-n-registro" v-model.trim="n_registro" type="text" class="form-control" placeholder="Ej: 00001" required>
                </div>

                <div class="col-md-4">
                  <label for="create-rut" class="form-label">RUT</label>
                  <input id="create-rut" v-model.trim="rut" type="text" class="form-control" placeholder="Ej: 12.345.678-9" required>
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
                  <label for="create-celular" class="form-label">Celular</label>
                  <input id="create-celular" v-model.trim="celular" type="text" class="form-control" placeholder="Ej: 912345678">
                </div>

                <div class="col-md-6">
                  <label for="create-nacionalidad" class="form-label">Nacionalidad</label>
                  <input id="create-nacionalidad" v-model.trim="nacionalidad" type="text" class="form-control" placeholder="Ej: Chilena">
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
                  <label for="create-domicilio" class="form-label">Domicilio</label>
                  <input id="create-domicilio" v-model.trim="domicilio" type="text" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="create-contacto-emergencia-nombre" class="form-label">Contacto de emergencia</label>
                  <input id="create-contacto-emergencia-nombre" v-model.trim="contacto_emergencia_nombre" type="text" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="create-contacto-emergencia-numero" class="form-label">Número de emergencia</label>
                  <input id="create-contacto-emergencia-numero" v-model.trim="contacto_emergencia_numero" type="text" class="form-control">
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

const DEFAULT_PASSWORD = 'cruzRojaCco26'

export default {
  name: 'UserCreateView',
  data() {
    return {
      name: '',
      email: '',
      estado: true,
      selectedRoles: [],
      rolesOptions: [],
      filialesOptions: [],
      n_registro: '',
      filial_id: '',
      rut: '',
      nombres: '',
      apellidos: '',
      nacionalidad: '',
      fecha_nacimiento: '',
      fecha_incorporacion: '',
      celular: '',
      domicilio: '',
      contacto_emergencia_nombre: '',
      contacto_emergencia_numero: '',
      foto_perfil: null,
      url: 'http://localhost:8000/api/user',
      rolesUrl: 'http://localhost:8000/api/role',
      filialesUrl: 'http://localhost:8000/api/filiales',
      modalInstance: null
    }
  },
  computed: {
    selectedRoleDetails() {
      return this.rolesOptions.filter((role) => this.selectedRoles.includes(role.id))
    },
    esVoluntario() {
      return this.selectedRoleDetails.some((role) => role.clave === 'voluntario')
    },
    computedDisplayName() {
      return `${this.nombres} ${this.apellidos}`.trim()
    }
  },
  async mounted() {
    this.modalInstance = new Modal(document.getElementById('newUserModal'))
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
      this.resetForm()
      this.modalInstance.show()
    },
    hide() {
      this.modalInstance.hide()
    },
    onPhotoSelected(event) {
      this.foto_perfil = event.target.files?.[0] || null
    },
    buildFormData() {
      const formData = new FormData()

      formData.append('name', this.esVoluntario ? this.computedDisplayName : this.name.trim())
      formData.append('email', this.email.trim())
      formData.append('estado', this.estado ? '1' : '0')
      this.selectedRoles.forEach((roleId) => formData.append('roles[]', roleId))

      if (this.esVoluntario) {
        formData.append('n_registro', this.n_registro.trim())
        formData.append('filial_id', String(this.filial_id))
        formData.append('rut', this.rut.trim())
        formData.append('nombres', this.nombres.trim())
        formData.append('apellidos', this.apellidos.trim())
        formData.append('nacionalidad', this.nacionalidad.trim())
        formData.append('fecha_nacimiento', this.fecha_nacimiento)
        formData.append('fecha_incorporacion', this.fecha_incorporacion)
        formData.append('celular', this.celular.trim())
        formData.append('domicilio', this.domicilio.trim())
        formData.append('contacto_emergencia_nombre', this.contacto_emergencia_nombre.trim())
        formData.append('contacto_emergencia_numero', this.contacto_emergencia_numero.trim())

        if (this.foto_perfil) {
          formData.append('foto_perfil', this.foto_perfil)
        }
      }

      return formData
    },
    resetForm() {
      this.name = ''
      this.email = ''
      this.estado = true
      this.selectedRoles = []
      this.n_registro = ''
      this.filial_id = ''
      this.rut = ''
      this.nombres = ''
      this.apellidos = ''
      this.nacionalidad = ''
      this.fecha_nacimiento = ''
      this.fecha_incorporacion = ''
      this.celular = ''
      this.domicilio = ''
      this.contacto_emergencia_nombre = ''
      this.contacto_emergencia_numero = ''
      this.foto_perfil = null
    },
    validateForm() {
      if (!this.email.trim()) {
        show_alerta('Debes ingresar el correo electrónico.', 'warning', 'create-email')
        return false
      }

      if (!this.esVoluntario && !this.name.trim()) {
        show_alerta('Debes ingresar el nombre del usuario.', 'warning', 'create-name')
        return false
      }

      if (this.esVoluntario) {
        if (!this.n_registro.trim()) {
          show_alerta('Debes ingresar el N° de registro.', 'warning', 'create-n-registro')
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
          show_alerta(`Perfil creado. Clave inicial: ${DEFAULT_PASSWORD}`, 'success')
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
</style>
