<template>
    <div class="d-flex">
        <!-- Importar el componente de barra lateral -->
        <SidebarMenu />

        <!-- Contenido principal -->
        <div class="content-wrapper">
            <!-- Encabezado -->
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="m-0"><i class="fa-solid fa-users me-2"></i>Gestión de Usuarios</h3>
                    <div class="d-flex">
                        <input v-model="search" type="text" class="form-control form-control-sm me-2" placeholder="Buscar usuario...">
                        <button class="btn btn-cr-primary btn-sm" @click="abrirModalNuevoUsuario">
                            <i class="fa-solid fa-user-plus me-1"></i> Nuevo Usuario
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Contenido principal -->
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
                                    <tr v-for="user, i in filteredUsers" :key="user.id">
                                        <td>{{ i + 1 }}</td>
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
                                                <button @click="editarUsuario(user)" class="btn btn-sm btn-outline-primary me-2" title="Editar">
                                                    <i class="fa-solid fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" title="Eliminar" v-on:click="eliminar(user.id, user.nombre)">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!filteredUsers || filteredUsers.length === 0">
                                        <td colspan="10" class="text-center py-3">No hay usuarios disponibles</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edición de Usuario -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true" ref="editModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editUserModalLabel">
                        <i class="fa-solid fa-user-edit me-2"></i>Editar Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="guardarCambios">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" v-model="userEdit.nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="rut" class="form-label">RUT</label>
                                <input type="text" class="form-control" id="rut" v-model="userEdit.rut" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" v-model="userEdit.email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" v-model="userEdit.telefono">
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" v-model="userEdit.fecha_nacimiento">
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_ingreso" class="form-label">Fecha de Ingreso</label>
                                <input type="date" class="form-control" id="fecha_ingreso" v-model="userEdit.fecha_ingreso">
                            </div>
                            <div class="col-md-4">
                                <label for="grupo_sanguineo" class="form-label">Grupo Sanguíneo</label>
                                <select class="form-select" id="grupo_sanguineo" v-model="userEdit.grupo_sanguineo">
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="AB">AB</option>
                                    <option value="O">O</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="factor_rh" class="form-label">Factor RH</label>
                                <select class="form-select" id="factor_rh" v-model="userEdit.factor_rh">
                                    <option value="+">+</option>
                                    <option value="-">-</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="role_id" class="form-label">Rol</label>
                                <select class="form-select" id="role_id" v-model="userEdit.role_id">
                                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" @click="guardarCambios">
                        <i class="fa-solid fa-save me-1"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Nuevo Usuario -->
    <div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-cr-red text-white">
                    <h5 class="modal-title" id="newUserModalLabel">
                        <i class="fa-solid fa-user-plus me-2"></i>Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="guardarNuevoUsuario">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                    <input type="text" class="form-control" id="nombre" v-model="newUser.nombre" required placeholder="Nombre completo">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="rut" class="form-label">RUT</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
                                    <input type="text" class="form-control" id="rut" v-model="newUser.rut" required placeholder="Ej: 12345678-9">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                                    <input type="email" class="form-control" id="correo" v-model="newUser.email" required placeholder="correo@ejemplo.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                    <input type="text" class="form-control" id="telefono" v-model="newUser.telefono" placeholder="Número de teléfono">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                                    <input type="date" class="form-control" id="fecha_nacimiento" v-model="newUser.fecha_nacimiento">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_ingreso" class="form-label">Fecha de Ingreso</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-calendar-check"></i></span>
                                    <input type="date" class="form-control" id="fecha_ingreso" v-model="newUser.fecha_ingreso">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="grupo_sanguineo" class="form-label">Grupo Sanguíneo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-tint"></i></span>
                                    <select class="form-select" id="grupo_sanguineo" v-model="newUser.grupo_sanguineo">
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="AB">AB</option>
                                        <option value="O">O</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="factor_rh" class="form-label">Factor RH</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-tint"></i></span>
                                    <select class="form-select" id="factor_rh" v-model="newUser.factor_rh">
                                        <option value="+">Positivo</option>
                                        <option value="-">Negativo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="role_id" class="form-label">Rol</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-user-tag"></i></span>
                                    <select class="form-select" id="role_id" v-model="newUser.role_id">
                                        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                    <input type="password" class="form-control" id="password" v-model="newUser.password" required placeholder="Contraseña">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-cr-primary" @click="guardarNuevoUsuario">
                        <i class="fa-solid fa-save me-1"></i>Guardar Usuario
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import { show_alerta } from '../funciones';
    import Swal from 'sweetalert2';
    import { Modal } from 'bootstrap';
    import SidebarMenu from '../components/SidebarMenu.vue';
    
    export default {
        components: {
            SidebarMenu
        },
        data() {
            return {
                users: null,
                search: '',
                userEdit: {
                    id: '',
                    nombre: '',
                    rut: '',
                    email: '',
                    telefono: '',
                    fecha_nacimiento: '',
                    fecha_ingreso: '',
                    grupo_sanguineo: '',
                    factor_rh: '',
                    role_id: ''
                },
                newUser: {
                    nombre: '',
                    rut: '',
                    role_id: 1,
                    filial_id: 1, // Campo requerido según CreateView.vue
                    telefono: '',
                    email: '',
                    fecha_nacimiento: '',
                    fecha_ingreso: '',
                    grupo_sanguineo: 'O',
                    factor_rh: '+',
                    password: '' // Campo requerido según CreateView.vue
                },
                modalInstance: null,
                newModalInstance: null,
                roles: [
                    { id: 1, name: 'Administrador' },
                    { id: 2, name: 'Usuario' },
                    { id: 3, name: 'Voluntario' },
                    { id: 4, name: 'Coordinador' },
                    { id: 5, name: 'Beneficiario' }
                ] // Roles predeterminados en caso de error, adaptados a Cruz Roja
            }
        },
        computed: {
            filteredUsers() {
                if (!this.users) return [];
                if (!this.search) return this.users;
                
                const searchTerm = this.search.toLowerCase();
                return this.users.filter(user => 
                    user.nombre.toLowerCase().includes(searchTerm) ||
                    user.email.toLowerCase().includes(searchTerm) ||
                    user.rut.toLowerCase().includes(searchTerm)
                );
            }
        },
        mounted() {
            this.getUsers();
        //    this.getRoles();
        },
        methods: {
            async getUsers() {
                axios.get('http://127.0.0.1:8000/api/user').then(
                    response => (
                        this.users = response.data,
                        console.log(this.users)
                    )
                );
            },
            async getRoles() {
                try {
                    // Intenta primero con 'roles' (plural)
                    const response = await axios.get('http://127.0.0.1:8000/api/roles');
                    if (response.data && Array.isArray(response.data)) {
                        this.roles = response.data;
                    }
                } catch (errorPlural) {
                    console.log("Intentando con endpoint alternativo...");
                    
                    try {
                        // Si falla, intenta con 'role' (singular)
                        const response = await axios.get('http://127.0.0.1:8000/api/role');
                        if (response.data && Array.isArray(response.data)) {
                            this.roles = response.data;
                        }
                    } catch (errorSingular) {
                        console.error("Error al cargar roles (plural):", errorPlural);
                        console.error("Error al cargar roles (singular):", errorSingular);
                        // No mostrar alerta para no molestar al usuario - usamos los roles predeterminados
                        console.log("Usando roles predeterminados");
                    }
                }
            },
            editarUsuario(user) {
                // Crear una copia profunda del usuario para editar
                this.userEdit = {
                    id: user.id,
                    nombre: user.nombre,
                    rut: user.rut,
                    email: user.email,
                    telefono: user.telefono,
                    fecha_nacimiento: user.fecha_nacimiento,
                    fecha_ingreso: user.fecha_ingreso,
                    grupo_sanguineo: user.grupo_sanguineo,
                    factor_rh: user.factor_rh,
                    role_id: user.role_id.id || 1 // Valor predeterminado si falta
                };
                
                // Verifica si el rol existe en la lista de roles
                const rolExists = this.roles.some(rol => rol.id === this.userEdit.role_id);
                if (!rolExists) {
                    // Si el rol no existe en la lista, usa el primer rol disponible
                    this.userEdit.role_id = this.roles[0].id;
                }
                
                // Inicializar y mostrar el modal
                if (!this.modalInstance) {
                    this.modalInstance = new Modal(document.getElementById('editUserModal'));
                }
                this.modalInstance.show();
            },
            async guardarCambios() {
                try {
                    const url = `http://127.0.0.1:8000/api/user/${this.userEdit.id}`;
                    const response = await axios.put(url, this.userEdit);
                    
                    if (response.status === 200) {
                        this.modalInstance.hide();
                        show_alerta('Usuario actualizado correctamente', 'success');
                        this.getUsers(); // Recargar la lista de usuarios
                    }
                } catch (error) {
                    console.error("Error al actualizar usuario:", error);
                    show_alerta('Error al actualizar usuario', 'error');
                }
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
                            console.log('entra al if');
                            axios.delete(url).then(
                                response => {
                                    console.log(response);
                                    this.getUsers();
                                    show_alerta('Usuario eliminado', 'success');
                                }
                            ).catch(error => {
                                console.log(error);
                                show_alerta('Error al eliminar el usuario', 'error');
                            });
                        }else{
                            show_alerta('Operación cancelada', 'info');
                        }
                    })
            },
            abrirModalNuevoUsuario() {
                // Reset the new user form
                this.newUser = {
                    nombre: '',
                    rut: '',
                    role_id: 1,
                    filial_id: 1,
                    telefono: '',
                    email: '',
                    fecha_nacimiento: '',
                    fecha_ingreso: new Date().toISOString().split('T')[0], // Current date
                    grupo_sanguineo: 'O',
                    factor_rh: '+',
                    password: ''
                };
                
                // Initialize and show modal
                if (!this.newModalInstance) {
                    this.newModalInstance = new Modal(document.getElementById('newUserModal'));
                }
                this.newModalInstance.show();
            },
            
            async guardarNuevoUsuario() {
                try {
                    // Validaciones
                    if (this.newUser.nombre.trim() === '') {
                        show_alerta('Escribe el nombre', 'warning');
                        return;
                    }
                    if (this.newUser.rut.trim() === '') {
                        show_alerta('Escribe el rut', 'warning');
                        return;
                    }
                    if (!this.newUser.role_id) {
                        show_alerta('Selecciona un rol', 'warning');
                        return;
                    }
                    if (this.newUser.email.trim() === '') {
                        show_alerta('Escribe el correo electrónico', 'warning');
                        return;
                    }
                    if (this.newUser.password.trim() === '') {
                        show_alerta('Escribe la contraseña', 'warning');
                        return;
                    }
                    
                    const url = 'http://127.0.0.1:8000/api/user';
                    const response = await axios.post(url, this.newUser);
                    
                    if (response.status === 201 || response.status === 200) {
                        this.newModalInstance.hide();
                        show_alerta('Usuario creado correctamente', 'success');
                        this.getUsers(); // Reload users list
                    }
                } catch (error) {
                    console.error("Error al crear usuario:", error);
                    
                    // Manejo de errores más detallado
                    if (error.response && error.response.data) {
                        const errores = error.response.data.errors || {};
                        let listado = '';
                        Object.keys(errores).forEach(key => {
                            listado += errores[key][0] + '. ';
                        });
                        show_alerta(listado || 'Error al crear el usuario', 'error');
                    } else {
                        show_alerta('Error al crear el usuario', 'error');
                    }
                }
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
    padding: 16px 12px;
    color: #333;
    background-color: #f5f5f5;
}

.custom-table td {
    padding: 12px;
    vertical-align: middle;
    border-bottom: 1px solid #e0e0e0;
    transition: background-color 0.2s;
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
</style>