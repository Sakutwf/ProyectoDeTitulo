<template>
  <div class="d-flex">
    <SidebarMenu />
    <div class="content-wrapper">
      <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <h3 class="m-0"><i class="fa-solid fa-users me-2"></i>Gestion de perfiles</h3>
            <button
              type="button"
              class="btn btn-outline-secondary btn-sm zoom-toggle-btn"
              :title="isMaxZoom ? 'Reiniciar tamano del texto' : 'Agrandar texto de la tabla'"
              @click="toggleTextZoom"
            >
              <i class="fa-solid fa-magnifying-glass-plus"></i>
            </button>
          </div>
          <div class="d-flex">
            <input
              v-model="search"
              @input="onSearch"
              type="text"
              class="form-control form-control-sm me-2"
              placeholder="Buscar por nombre, RUT o telefono..."
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
              <table class="table custom-table" :style="tableZoomStyle">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>RUT</th>
                    <th>Telefono</th>
                    <th>Rol</th>
                    <th class="text-center">Hoja de vida</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in users" :key="user.id">
                    <td>{{ user.id }}</td>
                    <td>{{ displayName(user) }}</td>
                    <td>{{ user.voluntario?.rut || '-' }}</td>
                    <td>{{ user.voluntario?.celular || '-' }}</td>
                    <td>
                      <div class="role-badges">
                        <span
                          v-for="role in visibleRoles(user)"
                          :key="role.id"
                          class="badge bg-light text-dark border"
                        >
                          {{ role.nombre }}
                        </span>
                        <span v-if="!visibleRoles(user).length" class="text-muted small">Sin roles</span>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex justify-content-center">
                        <button
                          v-if="canViewHistory(user)"
                          type="button"
                          class="btn btn-sm btn-danger"
                          @click="viewHistory(user.id)"
                        >
                          Ver historial
                        </button>
                        <span v-else class="text-muted small">No aplica</span>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex justify-content-center actions-cell">
                        <button @click="editUser(user.id)" class="btn btn-sm btn-outline-primary me-2" title="Editar">
                          <i class="fa-solid fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" title="Eliminar" @click="eliminar(user.id, displayName(user))">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!users.length">
                    <td colspan="7" class="text-center py-3">No hay perfiles disponibles.</td>
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
import { buildApiUrl } from '../config/api'
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
      selectedUserId: null,
      zoomLevelIndex: 0,
      zoomLevels: [1.25, 1.57]
    }
  },
  computed: {
    tableZoomScale() {
      return this.zoomLevels[this.zoomLevelIndex] || 1
    },
    isMaxZoom() {
      return this.zoomLevelIndex === this.zoomLevels.length - 1
    },
    tableZoomStyle() {
      return {
        '--table-header-font-size': `${0.9 * this.tableZoomScale}rem`,
        '--table-cell-font-size': `${1 * this.tableZoomScale}rem`,
        '--table-cell-line-height': this.tableZoomScale > 1 ? '1.45' : '1.35',
        '--table-badge-font-size': `${0.85 * Math.min(this.tableZoomScale, 1.18)}rem`
      }
    }
  },
  mounted() {
    this.getUsers()
  },
  methods: {
    async getUsers(page = 1) {
      const params = { page }
      if (this.search) params.search = this.search

      const response = await axios.get(buildApiUrl('user'), { params })
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
    toggleTextZoom() {
      this.zoomLevelIndex = this.isMaxZoom ? 0 : this.zoomLevels.length - 1
    },
    canViewHistory(user) {
      return Boolean(user?.voluntario)
    },
    viewHistory(userId) {
      this.$router.push({ name: 'HistorialView', params: { id: userId } })
    },
    async eliminar(id, nombre) {
      const result = await Swal.fire({
        title: `Eliminar a ${nombre}?`,
        text: 'Se perdera la informacion del perfil.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
      })

      if (!result.isConfirmed) {
        return
      }

      try {
        await axios.delete(buildApiUrl(`user/${id}`))
        this.getUsers()
        show_alerta('Perfil eliminado.', 'success')
      } catch (error) {
        show_alerta('No se pudo eliminar el perfil.', 'error')
      }
    },
    displayName(user) {
      const volunteerName = [user?.voluntario?.nombres, user?.voluntario?.apellidos].filter(Boolean).join(' ').trim()
      return volunteerName || user?.username || 'Usuario'
    },
    visibleRoles(user) {
      const roles = user?.roles || []
      const hasAdditionalRole = roles.some((role) => role?.nombre?.toLowerCase() !== 'voluntario')

      if (!hasAdditionalRole) {
        return roles
      }

      return roles.filter((role) => role?.nombre?.toLowerCase() !== 'voluntario')
    }
  }
}
</script>

<style scoped>
.custom-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  table-layout: fixed;
}

.custom-table th {
  font-size: var(--table-header-font-size, 0.9rem);
  letter-spacing: 0.5px;
  text-transform: uppercase;
  padding: 10px 12px;
  color: #333;
  background-color: #f5f5f5;
}

.custom-table td {
  font-size: var(--table-cell-font-size, 1rem);
  line-height: var(--table-cell-line-height, 1.35);
  padding: 6px 12px;
  vertical-align: middle;
  border-bottom: 1px solid #e0e0e0;
}

.custom-table th,
.custom-table td {
  white-space: normal;
  overflow-wrap: anywhere;
}

.custom-table th:nth-child(1),
.custom-table td:nth-child(1) {
  width: 8%;
}

.custom-table th:nth-child(2),
.custom-table td:nth-child(2) {
  width: 21%;
  padding-right: 6px;
}

.custom-table th:nth-child(3),
.custom-table td:nth-child(3) {
  width: 17%;
  padding-left: 6px;
}

.custom-table th:nth-child(4),
.custom-table td:nth-child(4) {
  width: 14%;
}

.custom-table th:nth-child(5),
.custom-table td:nth-child(5) {
  width: 14%;
}

.custom-table th:nth-child(6),
.custom-table td:nth-child(6) {
  width: 14%;
  white-space: nowrap;
}

.custom-table th:nth-child(7),
.custom-table td:nth-child(7) {
  width: 12%;
  white-space: nowrap;
}

.role-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.role-badges .badge {
  font-size: var(--table-badge-font-size, 0.85rem);
}

.zoom-toggle-btn {
  flex-shrink: 0;
  min-width: 74px;
}

.actions-cell {
  flex-wrap: nowrap;
  white-space: nowrap;
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
