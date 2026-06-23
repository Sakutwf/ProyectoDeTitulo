<template>
  <div class="d-flex">
    <SidebarMenu />
    <div class="content-wrapper">
      <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
          <h3 class="m-0"><i class="fa-solid fa-users me-2"></i>Gestión de perfiles</h3>
          <div class="d-flex">
            <input
              v-model="search"
              @input="onSearch"
              type="text"
              class="form-control form-control-sm me-2"
              placeholder="Buscar por nombre, correo, RUT o N° registro..."
            >
            <button class="btn btn-danger btn-sm" @click="abrirModalNuevoUsuario">
              <i class="fa-solid fa-user-plus me-1"></i> Nuevo perfil
            </button>
          </div>
        </div>
      </div>

      <div class="content">
        <div class="card shadow">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table custom-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>N° registro</th>
                    <th>RUT</th>
                    <th>Filial</th>
                    <th>Celular</th>
                    <th>Ingreso</th>
                    <th>Roles</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(user, i) in users" :key="user.id">
                    <td>{{ (meta.from || 1) + i }}</td>
                    <td>
                      <div class="d-flex align-items-center py-2">
                        <div class="avatar-circle" :class="{ 'has-photo': Boolean(user.voluntario?.foto_perfil_url) }">
                          <img
                            v-if="user.voluntario?.foto_perfil_url"
                            :src="user.voluntario.foto_perfil_url"
                            :alt="`Foto de ${user.name}`"
                            class="avatar-image"
                          >
                          <span v-else class="initials">{{ getInitials(user.name) }}</span>
                        </div>
                        <div class="ms-3">
                          <h6 class="mb-0">{{ user.name }}</h6>
                        </div>
                      </div>
                    </td>
                    <td>{{ user.email }}</td>
                    <td>{{ user.voluntario?.n_registro || '-' }}</td>
                    <td>{{ user.voluntario?.rut || '-' }}</td>
                    <td>{{ user.voluntario?.filial?.nombre || '-' }}</td>
                    <td>{{ user.voluntario?.celular || '-' }}</td>
                    <td>{{ formatDate(user.voluntario?.fecha_incorporacion) }}</td>
                    <td>
                      <div class="role-badges">
                        <span
                          v-for="role in user.roles || []"
                          :key="role.id"
                          class="badge bg-light text-dark border"
                        >
                          {{ role.nombre }}
                        </span>
                        <span v-if="!user.roles?.length" class="text-muted small">Sin roles</span>
                      </div>
                    </td>
                    <td>
                      <span :class="['badge', user.estado ? 'bg-danger' : 'bg-secondary']">
                        {{ user.estado ? 'Activo' : 'Inactivo' }}
                      </span>
                    </td>
                    <td>
                      <div class="d-flex justify-content-center">
                        <button @click="editUser(user.id)" class="btn btn-sm btn-outline-primary me-2" title="Editar">
                          <i class="fa-solid fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" title="Eliminar" @click="eliminar(user.id, user.name)">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!users.length">
                    <td colspan="11" class="text-center py-3">No hay perfiles disponibles.</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <nav v-if="meta.last_page > 1" class="mt-3">
              <ul class="pagination cruz-roja-pagination justify-content-end">
                <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                  <button class="page-link" @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page === 1">
                    <i class="fa-solid fa-chevron-left"></i> Anterior
                  </button>
                </li>
                <li class="page-item" v-for="page in meta.last_page" :key="page" :class="{ active: meta.current_page === page }">
                  <button class="page-link" @click="goToPage(page)">{{ page }}</button>
                </li>
                <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
                  <button class="page-link" @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page">
                    Siguiente <i class="fa-solid fa-chevron-right"></i>
                  </button>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <UserCreateView ref="userCreateModal" @user-created="getUsers" />
  <UserEditView :userId="selectedUserId" ref="userEditModal" @user-updated="getUsers" />
</template>

<script>
import axios from 'axios'
import { show_alerta } from '../funciones'
import Swal from 'sweetalert2'
import SidebarMenu from '../components/SidebarMenu.vue'
import UserEditView from './UserEditView.vue'
import UserCreateView from './UserCreateView.vue'

export default {
  name: 'UserView',
  components: {
    SidebarMenu,
    UserEditView,
    UserCreateView
  },
  data() {
    return {
      users: [],
      meta: {
        current_page: 1,
        last_page: 1,
        from: 1
      },
      search: '',
      selectedUserId: null
    }
  },
  mounted() {
    this.getUsers()
  },
  methods: {
    async getUsers(page = 1) {
      const params = { page }
      if (this.search) params.search = this.search

      const response = await axios.get('http://127.0.0.1:8000/api/user', { params })
      this.users = response.data.data
      this.meta = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        from: response.data.from
      }
    },
    onSearch() {
      this.getUsers(1)
    },
    goToPage(page) {
      if (page >= 1 && page <= this.meta.last_page) {
        this.getUsers(page)
      }
    },
    editUser(userId) {
      this.selectedUserId = userId
      setTimeout(() => this.$refs.userEditModal.show(), 100)
    },
    abrirModalNuevoUsuario() {
      this.$refs.userCreateModal.show()
    },
    async eliminar(id, nombre) {
      const result = await Swal.fire({
        title: `¿Eliminar a ${nombre}?`,
        text: 'Se perderá la información del perfil.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      })

      if (!result.isConfirmed) {
        return
      }

      try {
        await axios.delete(`http://localhost:8000/api/user/${id}`)
        this.getUsers()
        show_alerta('Perfil eliminado.', 'success')
      } catch (error) {
        show_alerta('No se pudo eliminar el perfil.', 'error')
      }
    },
    getInitials(name) {
      if (!name) return 'U'
      return name.split(' ').map((part) => part[0]).join('').toUpperCase().slice(0, 2)
    },
    formatDate(dateString) {
      if (!dateString) return '-'
      return /^\d{4}-\d{2}-\d{2}/.test(dateString)
        ? dateString.slice(0, 10).split('-').reverse().join('-')
        : dateString
    }
  }
}
</script>

<style scoped>
.custom-table {
  border-collapse: separate;
  border-spacing: 0;
}

.custom-table th {
  font-size: 0.9rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  padding: 10px 12px;
  color: #333;
  background-color: #f5f5f5;
}

.custom-table td {
  padding: 6px 12px;
  vertical-align: middle;
  border-bottom: 1px solid #e0e0e0;
}

.avatar-circle {
  width: 40px;
  height: 40px;
  background-color: #e01e1e;
  border-radius: 50%;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  overflow: hidden;
}

.avatar-circle.has-photo {
  background-color: #e8edf5;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.role-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

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

.cruz-roja-pagination .page-link {
  color: #e01e1e;
  font-weight: 600;
  border: 1px solid #e01e1e;
  background: #fff;
  border-radius: 6px;
  margin: 0 2px;
}

.cruz-roja-pagination .page-item.active .page-link,
.cruz-roja-pagination .page-link:hover {
  background: #e01e1e;
  color: #fff;
  border-color: #e01e1e;
}
</style>
