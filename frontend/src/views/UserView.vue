<template>
    <div class="d-flex">
        <SidebarMenu />
        <div class="content-wrapper">
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="m-0"><i class="fa-solid fa-users me-2"></i>Gestión de Usuarios</h3>
                    <div class="d-flex">
                        <input v-model="search" @input="onSearch" type="text" class="form-control form-control-sm me-2" placeholder="Buscar usuario...">
                        <button class="btn btn-danger btn-sm" @click="abrirModalNuevoUsuario">
                            <i class="fa-solid fa-user-plus me-1"></i> Nuevo Usuario
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
                                        <th class="fw-semibold">#</th>
                                        <th class="fw-semibold">Usuario</th>
                                        <th class="fw-semibold">Rut</th>
                                        <th class="fw-semibold">Rol</th>
                                        <th class="fw-semibold">Teléfono</th>
                                        <th class="fw-semibold">Fecha de nacimiento</th>
                                        <th class="fw-semibold">Grupo sanguíneo</th>
                                        <th class="fw-semibold">Factor Rh</th>
                                        <th class="fw-semibold">Fecha de ingreso</th>
                                        <th class="fw-semibold text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(user, i) in users" :key="user.id">
                                        <td>{{ (meta.from || 1) + i }}</td>
                                        <td>
                                            <div class="d-flex align-items-center py-2">
                                                <div class="avatar-circle">
                                                    <span class="initials">{{ getInitials(user.nombre) }}</span>
                                                </div>
                                                <div class="ms-3">
                                                    <h6 class="mb-0">{{ user.nombre }}</h6>
                                                    <span class="text-muted small">{{ user.email }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ user.rut }}</td>
                                        <td>
                                            <span :class="'badge ' + getRoleBadgeClass(user.role_id.name)">{{ user.role_id.name }}</span>
                                        </td>
                                        <td>{{ user.telefono }}</td>
                                        <td>{{ formatDate(user.fecha_nacimiento) }}</td>
                                        <td class="text-center">{{ user.grupo_sanguineo }}</td>
                                        <td class="text-center">{{ user.factor_rh }}</td>
                                        <td>{{ formatDate(user.fecha_ingreso) }}</td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <button @click="editUser(user.id)" class="btn btn-sm btn-outline-primary me-2" title="Editar">
                                                    <i class="fa-solid fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" title="Eliminar" v-on:click="eliminar(user.id, user.nombre)">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!users || users.length === 0">
                                        <td colspan="10" class="text-center py-3">No hay usuarios disponibles</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Paginación alineada a la derecha -->
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

    <UserCreateView
        ref="userCreateModal"
        @user-created="getUsers"
    />
    <UserEditView
        :userId="selectedUserId"
        ref="userEditModal"
        @user-updated="getUsers"
    />
</template>

<script>
    import axios from 'axios';
    import { show_alerta } from '../funciones';
    import Swal from 'sweetalert2';
    import SidebarMenu from '../components/SidebarMenu.vue';
    import UserEditView from './UserEditView.vue';
    import UserCreateView from './UserCreateView.vue';
    
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
                roles: [
                    { id: 1, name: 'Administrador' },
                    { id: 2, name: 'Voluntario' },
                ]
            }
        },
        mounted() {
            this.getUsers();
            this.getRoles();
        },
        methods: {
            async getUsers(page = 1) {
                let params = { page };
                if (this.search) params.search = this.search;
                const response = await axios.get('http://127.0.0.1:8000/api/user', { params });
                // Laravel paginator returns { data, current_page, last_page, from, ... }
                this.users = response.data.data;
                this.meta = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    from: response.data.from
                };
            },
            onSearch() {
                this.getUsers(1);
            },
            goToPage(page) {
                if (page >= 1 && page <= this.meta.last_page) {
                    this.getUsers(page);
                }
            },
            async getRoles() {
                try {
                    const response = await axios.get('http://127.0.0.1:8000/api/role');
                    if (response.data && Array.isArray(response.data)) {
                        this.roles = response.data;
                    }
                } catch (error) {
                    // Error al cargar roles
                }
            },
            editUser(userId) {
                this.selectedUserId = userId;
                setTimeout(() => {
                    this.$refs.userEditModal.show();
                }, 100);
            },
            abrirModalNuevoUsuario() {
                this.$refs.userCreateModal.show();
            },
            async eliminar(id, nombre){
                var url = 'http://localhost:8000/api/user/' + id;
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {confirmButton: 'btn btn-success me-3', cancelButton: 'btn btn-danger' },
                    buttonsStyling: false
                });
                swalWithBootstrapButtons.fire({
                    title: '¿Está seguro de que desea eliminar al usuario ' + nombre + '?',
                    text: "Se perderá la información del usuario",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-check"></i>Sí, eliminar!',
                    cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar!'}).then((result) => {
                        if (result.isConfirmed) {
                            axios.delete(url).then(
                                response => {
                                    this.getUsers();
                                    show_alerta('Usuario eliminado', 'success');
                                }
                            ).catch(error => {
                                show_alerta('Error al eliminar el usuario', 'error');
                            });
                        }else{
                            show_alerta('Operación cancelada', 'info');
                        }
                    })
            },
            getInitials(name) {
                if (!name) return 'U';
                return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
            },
            formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleDateString();
            },
            getRoleBadgeClass(role) {
                const roleMap = {
                    'Admin': 'bg-danger',
                    'Administrador': 'bg-danger',
                    'Usuario': 'bg-primary',
                    'Voluntario': 'bg-success',
                    'Coordinador': 'bg-info',
                    'Beneficiario': 'bg-warning'
                };
                return roleMap[role] || 'bg-secondary';
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
    transition: background-color 0.2s;
    height: 36px;
}

.custom-table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
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
}

.initials {
    font-size: 15px;
}

.btn-outline-primary, .btn-outline-danger {
    border-radius: 4px;
    padding: 4px 8px;
}

.btn-outline-primary:hover {
    background-color: #e01e1e;
    border-color: #e01e1e;
    color: white;
}

.btn-outline-danger:hover {
    background-color: #f44336;
    color: white;
}

.card {
    border: none;
    border-radius: 8px;
    overflow: hidden;
}

.card-header {
    padding: 15px 20px;
}

/* Estilos adicionales para el modal */
.modal-header {
    border-bottom: 0;
}

.modal-footer {
    border-top: 0;
}

.modal-content {
    border-radius: 8px;
    border: none;
}

.modal-body {
    padding: 20px 30px;
}

/* Estilos para el contenido principal */
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

.d-flex {
    display: flex;
}

.cruz-roja-pagination .page-link {
    color: #e01e1e;
    font-weight: 600;
    border: 1px solid #e01e1e;
    background: #fff;
    border-radius: 6px;
    margin: 0 2px;
    transition: background 0.2s, color 0.2s;
}

.cruz-roja-pagination .page-link:focus {
    box-shadow: 0 0 0 0.15rem #e01e1e33;
}

.cruz-roja-pagination .page-item.active .page-link,
.cruz-roja-pagination .page-link:hover {
    background: #e01e1e;
    color: #fff;
    border-color: #e01e1e;
}

.cruz-roja-pagination .page-item.disabled .page-link {
    color: #aaa;
    background: #f5f5f5;
    border-color: #e0e0e0;
    cursor: not-allowed;
}

.cruz-roja-pagination .page-link i {
    font-size: 0.95em;
    vertical-align: middle;
}
</style>
