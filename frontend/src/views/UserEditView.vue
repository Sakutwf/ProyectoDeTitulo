<template>
  <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="editUserModalLabel">
            <i class="fa-solid fa-user-edit me-2"></i>Editar perfil
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <form @submit.prevent="guardar">
            <div class="row g-3">
              <div class="col-md-6" v-if="!esVoluntario">
                <label for="edit-name" class="form-label">Nombre</label>
                <input id="edit-name" v-model.trim="name" type="text" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label for="edit-email" class="form-label">Correo electrónico</label>
                <input id="edit-email" v-model.trim="email" type="email" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label for="edit-estado" class="form-label">Estado</label>
                <select id="edit-estado" v-model="estado" class="form-select" required>
                  <option :value="true">Activo</option>
                  <option :value="false">Inactivo</option>
                </select>
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
                      :id="`edit-role-${role.id}`"
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

              <template v-if="esVoluntario">
                <div class="col-12">
                  <div class="volunteer-section-title">Datos del voluntario</div>
                </div>

                <div class="col-md-4">
                  <label for="edit-n-registro" class="form-label">N° de registro</label>
                  <input id="edit-n-registro" v-model.trim="n_registro" type="text" class="form-control" required>
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
                  <label for="edit-domicilio" class="form-label">Domicilio</label>
                  <input id="edit-domicilio" v-model.trim="domicilio" type="text" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="edit-contacto-emergencia-nombre" class="form-label">Contacto de emergencia</label>
                  <input id="edit-contacto-emergencia-nombre" v-model.trim="contacto_emergencia_nombre" type="text" class="form-control">
                </div>

                <div class="col-md-6">
                  <label for="edit-contacto-emergencia-numero" class="form-label">Número de emergencia</label>
                  <input id="edit-contacto-emergencia-numero" v-model.trim="contacto_emergencia_numero" type="text" class="form-control">
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
      password: '',
      url: 'http://localhost:8000/api/user/',
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
    this.modalInstance = new Modal(document.getElementById('editUserModal'))
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
    onPhotoSelected(event) {
      this.foto_perfil = event.target.files?.[0] || null
    },
    async getUser() {
      if (this.rolesOptions.length === 0 || this.filialesOptions.length === 0) {
        await Promise.all([this.fetchRoles(), this.fetchFiliales()])
      }

      const response = await axios.get(this.url + this.id)
      const user = response.data
      const voluntario = user.voluntario || null

      this.name = user.name || ''
      this.email = user.email || ''
      this.estado = Boolean(user.estado)
      this.selectedRoles = (user.roles || []).map((role) => role.id)
      this.n_registro = voluntario?.n_registro || ''
      this.filial_id = voluntario?.filial_id || ''
      this.rut = voluntario?.rut || ''
      this.nombres = voluntario?.nombres || ''
      this.apellidos = voluntario?.apellidos || ''
      this.nacionalidad = voluntario?.nacionalidad || ''
      this.fecha_nacimiento = voluntario?.fecha_nacimiento || ''
      this.fecha_incorporacion = voluntario?.fecha_incorporacion || ''
      this.celular = voluntario?.celular || ''
      this.domicilio = voluntario?.domicilio || ''
      this.contacto_emergencia_nombre = voluntario?.contacto_emergencia_nombre || ''
      this.contacto_emergencia_numero = voluntario?.contacto_emergencia_numero || ''
      this.foto_perfil = null
      this.password = ''
    },
    buildFormData() {
      const formData = new FormData()
      formData.append('_method', 'PUT')
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

      if (this.password) {
        formData.append('password', this.password)
      }

      return formData
    },
    validateForm() {
      if (!this.email.trim()) {
        show_alerta('Debes ingresar el correo electrónico.', 'warning', 'edit-email')
        return false
      }

      if (!this.esVoluntario && !this.name.trim()) {
        show_alerta('Debes ingresar el nombre del usuario.', 'warning', 'edit-name')
        return false
      }

      if (this.esVoluntario) {
        if (!this.n_registro.trim()) {
          show_alerta('Debes ingresar el N° de registro.', 'warning', 'edit-n-registro')
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
        const response = await axios.post(this.url + this.id, this.buildFormData(), {
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
