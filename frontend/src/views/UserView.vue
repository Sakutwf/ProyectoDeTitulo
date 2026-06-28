<template>
  <div class="d-flex">
    <SidebarMenu />
    <div class="content-wrapper">
      <div class="content-header">
        <div class="profiles-header">
          <div class="profiles-header__top">
            <h3 class="m-0"><i class="fa-solid fa-users me-2"></i>Gestion de perfiles</h3>
            <button class="btn btn-danger btn-sm profiles-header__create" @click="abrirModalNuevoUsuario" aria-label="Nuevo perfil">
              <i class="fa-solid fa-user-plus"></i>
              <span class="profiles-header__create-label">Nuevo perfil</span>
            </button>
          </div>

          <div class="profiles-header__search">
            <input
              v-model="search"
              @input="onSearch"
              type="text"
              class="form-control form-control-sm"
              placeholder="Buscar por nombre, RUT o telefono..."
            >
          </div>
        </div>
      </div>

      <div class="content">
        <div class="card shadow">
          <div class="card-body">
            <div class="profiles-mobile-list">
              <article v-for="user in users" :key="`mobile-${user.id}`" class="profile-card">
                <div class="profile-card__header">
                  <div class="profile-card__identity">
                    <h4>{{ displayName(user) }}</h4>
                    <div class="role-badges role-badges--mobile">
                      <span
                        v-for="role in visibleRoles(user)"
                        :key="`mobile-role-${role.id}`"
                        class="badge role-badge-mobile"
                      >
                        {{ role.nombre }}
                      </span>
                      <span v-if="!visibleRoles(user).length" class="text-muted small">Sin roles</span>
                    </div>
                  </div>

                  <div class="profile-card__actions">
                    <button @click="editUser(user.id)" class="btn btn-sm profile-action-button" title="Editar" aria-label="Editar">
                      <i class="fa-solid fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" title="Eliminar" aria-label="Eliminar" @click="eliminar(user.id, displayName(user))">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </div>
                </div>

                <div class="profile-card__details">
                  <div class="profile-card__row">
                    <span class="profile-card__label"><i class="fa-solid fa-id-card"></i> N° Registro</span>
                    <strong>{{ volunteerRegistrationLabel(user) }}</strong>
                  </div>
                  <div class="profile-card__row">
                    <span class="profile-card__label"><i class="fa-solid fa-address-card"></i> RUT</span>
                    <strong>{{ user.voluntario?.rut || '-' }}</strong>
                  </div>
                  <div class="profile-card__row">
                    <span class="profile-card__label"><i class="fa-solid fa-phone"></i> Telefono</span>
                    <strong>{{ user.voluntario?.celular || '-' }}</strong>
                  </div>

                  <div class="profile-card__row profile-card__row--history">
                    <span class="profile-card__label"><i class="fa-solid fa-file-lines"></i> Hoja de Vida</span>
                    <button
                      v-if="canViewHistory(user)"
                      type="button"
                      class="btn btn-danger btn-sm profile-card__history-button"
                      @click="viewHistory(user.id)"
                    >
                      Ver historial
                    </button>
                    <span v-else class="text-muted small">No aplica</span>
                  </div>
                </div>
              </article>

              <div v-if="!users.length" class="profile-card profile-card--empty">
                No hay perfiles disponibles.
              </div>
            </div>

            <div class="table-shell table-shell--profiles">
              <div v-if="showProfilesTableNavigation" class="table-scroll-controls" aria-label="Navegacion horizontal de tabla">
                <button
                  type="button"
                  class="table-scroll-button"
                  :disabled="!canScrollProfilesLeft"
                  @click="scrollProfilesTable(-1)"
                  aria-label="Desplazar tabla hacia la izquierda"
                >
                  <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button
                  type="button"
                  class="table-scroll-button"
                  :disabled="!canScrollProfilesRight"
                  @click="scrollProfilesTable(1)"
                  aria-label="Desplazar tabla hacia la derecha"
                >
                  <i class="fa-solid fa-chevron-right"></i>
                </button>
              </div>

              <div ref="profilesTableShell" class="table-responsive profiles-table-shell" @scroll="updateProfilesTableScroll">
                <table class="table custom-table custom-table--responsive">
                  <thead>
                    <tr>
                      <th class="column-mobile-hidden">N° Registro</th>
                      <th>Nombre</th>
                      <th class="column-mobile-hidden">RUT</th>
                      <th>Rol</th>
                      <th class="column-mobile-hidden">Telefono</th>
                      <th class="text-center">Hoja de vida</th>
                      <th class="text-center">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="user in users" :key="user.id">
                      <td data-label="N° Registro" class="column-mobile-hidden">{{ volunteerRegistrationLabel(user) }}</td>
                      <td data-label="Nombre">{{ displayName(user) }}</td>
                      <td data-label="RUT" class="column-mobile-hidden">{{ user.voluntario?.rut || '-' }}</td>
                      <td data-label="Rol">
                        <div class="role-badges">
                          <span
                            v-for="role in visibleRoles(user)"
                            :key="role.id"
                            class="badge role-badge"
                          >
                            {{ role.nombre }}
                          </span>
                          <span v-if="!visibleRoles(user).length" class="text-muted small">Sin roles</span>
                        </div>
                      </td>
                      <td data-label="Telefono" class="column-mobile-hidden">{{ user.voluntario?.celular || '-' }}</td>
                      <td data-label="Hoja de Vida">
                        <div class="d-flex justify-content-center history-cell">
                          <button
                            v-if="canViewHistory(user)"
                            type="button"
                            class="btn btn-danger btn-sm profile-card__history-button"
                            @click="viewHistory(user.id)"
                          >
                            Ver historial
                          </button>
                          <span v-else class="text-muted small">No aplica</span>
                        </div>
                      </td>
                      <td data-label="Acciones">
                        <div class="d-flex justify-content-center actions-cell">
                          <button @click="editUser(user.id)" class="btn btn-sm profile-action-button" title="Editar">
                            <i class="fa-solid fa-edit"></i>
                          </button>
                          <button class="btn btn-sm btn-outline-danger" title="Eliminar" @click="eliminar(user.id, displayName(user))">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="!users.length" class="no-results-row">
                      <td colspan="7" class="text-center py-3 no-results-cell">No hay perfiles disponibles.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
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
      showProfilesTableNavigation: false,
      canScrollProfilesLeft: false,
      canScrollProfilesRight: false
    }
  },
  mounted() {
    this.getUsers()
    window.addEventListener('resize', this.updateProfilesTableScroll)
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.updateProfilesTableScroll)
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

      this.$nextTick(() => this.updateProfilesTableScroll())
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
    updateProfilesTableScroll() {
      const shell = this.$refs.profilesTableShell

      if (!shell || window.innerWidth < 768) {
        this.showProfilesTableNavigation = false
        this.canScrollProfilesLeft = false
        this.canScrollProfilesRight = false
        return
      }

      const overflowOffset = shell.scrollWidth - shell.clientWidth
      const hasOverflow = overflowOffset > 8

      this.showProfilesTableNavigation = hasOverflow
      this.canScrollProfilesLeft = hasOverflow && shell.scrollLeft > 4
      this.canScrollProfilesRight = hasOverflow && shell.scrollLeft < overflowOffset - 4
    },
    scrollProfilesTable(direction) {
      const shell = this.$refs.profilesTableShell
      if (!shell) {
        return
      }

      shell.scrollBy({
        left: direction * Math.max(shell.clientWidth * 0.72, 220),
        behavior: 'smooth'
      })

      window.setTimeout(() => this.updateProfilesTableScroll(), 260)
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
    volunteerRegistrationLabel(user) {
      const registration = (user?.voluntario?.registro_filial || '').trim()
      const filialName = this.normalizeFilialName(user?.voluntario?.filial?.nombre)

      if (filialName && registration) {
        return `${filialName} ${registration}`
      }

      return registration || filialName || '-'
    },
    normalizeFilialName(name) {
      const value = (name || '').trim()

      if (!value) {
        return ''
      }

      return value
        .replace(/^filial\s+de\s+/i, '')
        .replace(/^filial\s+/i, '')
        .trim()
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
.profiles-mobile-list {
  display: none;
}

.profiles-header {
  display: grid;
  gap: 0.9rem;
}

.profiles-header__top {
  display: flex;
  align-items: stretch;
  justify-content: space-between;
  gap: 0.75rem;
}

.profiles-header__top h3 {
  display: flex;
  align-items: center;
  min-height: 56px;
  font-size: 2rem;
  line-height: 1.05;
}

.profiles-header__search {
  display: flex;
  width: min(100%, 28rem);
}

.profiles-header__create {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.55rem;
  min-width: 44px;
  min-height: 56px;
  padding: 0.85rem 1.2rem;
  border-radius: 16px;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.1;
  white-space: nowrap;
}

.table-shell {
  display: grid;
  gap: 0.7rem;
}

.table-scroll-controls {
  display: none;
  justify-content: flex-end;
  gap: 0.55rem;
}

.table-scroll-button {
  width: 2.3rem;
  height: 2.3rem;
  border: 1px solid #d5deea;
  border-radius: 999px;
  background: #fff;
  color: #274062;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 18px rgba(15, 47, 95, 0.08);
}

.table-scroll-button:disabled {
  opacity: 0.45;
  cursor: default;
  box-shadow: none;
}

.custom-table {
  width: 100%;
  min-width: 72rem;
  border-collapse: separate;
  border-spacing: 0;
  table-layout: auto;
}

.custom-table th {
  font-size: 0.9rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  padding: 10px 12px;
  color: #333;
  background-color: #f5f5f5;
  white-space: nowrap;
  overflow-wrap: normal;
  word-break: normal;
  hyphens: none;
  line-height: 1.25;
}

.custom-table td {
  font-size: 1rem;
  line-height: 1.35;
  padding: 6px 12px;
  vertical-align: middle;
  border-bottom: 1px solid #e0e0e0;
  white-space: nowrap;
  overflow-wrap: normal;
  word-break: normal;
  hyphens: none;
}

.custom-table th:nth-child(1),
.custom-table td:nth-child(1) {
  min-width: 9rem;
}

.custom-table th:nth-child(2),
.custom-table td:nth-child(2) {
  min-width: 14rem;
  white-space: normal;
}

.custom-table th:nth-child(3),
.custom-table td:nth-child(3) {
  min-width: 10rem;
}

.custom-table th:nth-child(4),
.custom-table td:nth-child(4) {
  min-width: 11rem;
}

.custom-table th:nth-child(5),
.custom-table td:nth-child(5) {
  min-width: 8rem;
}

.custom-table th:nth-child(6),
.custom-table td:nth-child(6) {
  min-width: 11rem;
}

.custom-table th:nth-child(7),
.custom-table td:nth-child(7) {
  min-width: 8rem;
}

.role-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  min-width: max-content;
}

.role-badge,
.role-badges--mobile .badge.role-badge-mobile {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 38px;
  padding: 0.45rem 0.95rem;
  border: 1.5px solid #0f4c81;
  border-radius: 0.9rem;
  color: #0f4c81;
  background: #fff;
  font-size: 1rem;
  font-weight: 800;
  line-height: 1;
  white-space: nowrap;
  box-shadow: none;
}

.role-badges .badge {
  text-align: left;
}

.actions-cell {
  flex-wrap: nowrap;
  white-space: nowrap;
  gap: 0.55rem;
}

.actions-cell .btn,
.profile-card__actions .btn {
  width: 44px;
  height: 44px;
  padding: 0;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.actions-cell .btn i,
.profile-card__actions .btn i {
  font-size: 1.1rem;
}

.profile-action-button {
  border-color: #0f4c81;
  color: #0f4c81;
  border-radius: 12px;
}

.profile-action-button:hover,
.profile-action-button:focus,
.profile-action-button:active {
  border-color: #0c416d;
  color: #0c416d;
  background: #edf5fb;
}

.history-cell {
  min-height: 100%;
  white-space: nowrap;
}

.profile-card__history-button {
  min-height: 40px;
  padding: 0.72rem 1.4rem;
  border-radius: 22px;
  font-weight: 800;
  font-size: 1rem;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  white-space: nowrap;
  border-color: #df3342;
  background: #df3342;
  box-shadow: none;
}

.profile-card__history-button:hover,
.profile-card__history-button:focus,
.profile-card__history-button:active {
  border-color: #c92b39;
  background: #c92b39;
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

@media (min-width: 768px) {
  .table-scroll-controls {
    display: flex;
  }

  .profiles-table-shell {
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: thin;
    padding-bottom: 0.25rem;
  }

  .custom-table th,
  .custom-table td {
    padding: 0.7rem 0.75rem;
  }

  .role-badges {
    gap: 0.45rem;
  }

  .role-badges .badge {
    max-width: 100%;
  }
}

@media (max-width: 767.98px) {
  .content-header {
    padding: 1rem 1rem 0.9rem;
  }

  .content {
    padding: 0 1rem 1rem;
  }

  .profiles-header__top {
    align-items: center;
  }

  .profiles-header__top h3 {
    min-height: 44px;
    font-size: 1.75rem;
  }

  .profiles-header__search {
    width: 100%;
  }

  .profiles-header__search .form-control {
    min-height: 46px;
    border-radius: 14px;
    font-size: 0.98rem;
  }

  .profiles-header__create {
    width: 44px;
    min-width: 44px;
    height: 44px;
    min-height: 44px;
    padding: 0;
    border-radius: 12px;
    flex-shrink: 0;
  }

  .profiles-header__create-label,
  .table-shell--profiles {
    display: none;
  }

  .profiles-mobile-list {
    display: grid;
    gap: 0.95rem;
  }

  .profile-card {
    border: 1px solid #e5eaf1;
    border-radius: 22px;
    padding: 1.1rem 1rem 1rem;
    background:
      linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 252, 255, 0.98)),
      radial-gradient(circle at top right, rgba(224, 30, 30, 0.08), transparent 38%);
    box-shadow: 0 16px 30px rgba(15, 47, 95, 0.08);
  }

  .profile-card--empty {
    text-align: center;
    color: #64748b;
    font-weight: 600;
  }

  .profile-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.8rem;
    margin-bottom: 0.85rem;
  }

  .profile-card__identity {
    min-width: 0;
  }

  .profile-card__identity h4 {
    margin: 0 0 0.55rem;
    color: #12284c;
    font-size: 1.7rem;
    line-height: 1.08;
    font-weight: 800;
  }

  .profile-card__actions {
    display: flex;
    gap: 0.45rem;
    flex-shrink: 0;
  }

  .profile-card__details {
    display: grid;
    gap: 0.15rem;
  }

  .profile-card__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.9rem;
    padding: 0.78rem 0;
    border-top: 1px solid #e6edf5;
  }

  .profile-card__row:first-child {
    border-top: none;
    padding-top: 0;
  }

  .profile-card__row strong {
    color: #12284c;
    font-size: 1.05rem;
    line-height: 1.25;
    text-align: right;
  }

  .profile-card__label {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    color: #334155;
    font-size: 1rem;
    line-height: 1.25;
  }

  .profile-card__label i {
    width: 1.15rem;
    color: #e01e1e;
    font-size: 1.05rem;
    text-align: center;
  }

  .profile-card__row--history {
    align-items: center;
  }
}
</style>



