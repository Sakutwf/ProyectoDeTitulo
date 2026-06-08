<template>
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="editUserModalLabel">
                        <i class="fa-solid fa-user-edit me-2"></i>Editar Registro
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="guardar">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit-nombre" class="form-label">Nombre</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                    <input id="edit-nombre" v-model="nombre" type="text" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="edit-rut" class="form-label">RUT</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
                                    <input id="edit-rut" v-model="rut" type="text" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="edit-correo" class="form-label">Correo electronico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                                    <input id="edit-correo" v-model="correo" type="email" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="edit-telefono" class="form-label">Telefono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                    <input id="edit-telefono" v-model="telefono" type="text" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="edit-estado" class="form-label">Estado</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-circle-check"></i></span>
                                    <select id="edit-estado" v-model="estado" class="form-select" required>
                                        <option value="ACTIVO">ACTIVO</option>
                                        <option value="INACTIVO">INACTIVO</option>
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
                                            :id="`edit-role-${role.id}`"
                                            v-model="selectedRoles"
                                            class="form-check-input"
                                            type="checkbox"
                                            :value="role.id"
                                        >
                                        <div>
                                            <div class="fw-semibold">{{ role.name }}</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="form-text">
                                    La ficha de voluntario depende del rol Voluntario.
                                </div>
                            </div>

                            <template v-if="esVoluntario">
                                <div class="col-md-12">
                                    <label for="edit-foto_perfil" class="form-label">Foto de perfil</label>
                                    <div class="photo-upload-card">
                                        <div class="photo-preview">
                                            <img v-if="fotoPreview" :src="fotoPreview" alt="Vista previa de foto de perfil">
                                            <span v-else>Sin foto</span>
                                        </div>
                                        <div class="photo-upload-fields">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-image"></i></span>
                                                <input
                                                    id="edit-foto_perfil"
                                                    type="file"
                                                    class="form-control"
                                                    accept=".jpg,.jpeg,.png,.webp"
                                                    @change="onPhotoSelected"
                                                >
                                            </div>
                                            <small class="text-muted">
                                                Sube una nueva imagen solo si deseas reemplazar la foto actual del voluntario.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="edit-fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                                        <input id="edit-fecha_nacimiento" v-model="fecha_nacimiento" type="date" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="edit-fecha_ingreso" class="form-label">Fecha de ingreso</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-calendar-check"></i></span>
                                        <input id="edit-fecha_ingreso" v-model="fecha_ingreso" type="date" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="edit-grupo_sanguineo" class="form-label">Grupo sanguineo</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-tint"></i></span>
                                        <select id="edit-grupo_sanguineo" v-model="grupo_sanguineo" class="form-select">
                                            <option value="A">A</option>
                                            <option value="B">B</option>
                                            <option value="AB">AB</option>
                                            <option value="O">O</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="edit-factor_rh" class="form-label">Factor RH</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-tint"></i></span>
                                        <select id="edit-factor_rh" v-model="factor_rh" class="form-select">
                                            <option value="+">+</option>
                                            <option value="-">-</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="edit-n_registro" class="form-label">N registro</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                                        <input id="edit-n_registro" v-model="n_registro" type="text" class="form-control">
                                    </div>
                                </div>
                            </template>

                            <div class="col-md-12">
                                <label for="edit-password" class="form-label">Contrasena</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                    <input
                                        id="edit-password"
                                        v-model="password"
                                        type="password"
                                        class="form-control"
                                        placeholder="Dejala vacia si no deseas modificarla"
                                    >
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" @click="guardar">
                        <i class="fa-solid fa-save me-1"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { show_alerta } from '../funciones';
import { Modal } from 'bootstrap';

export default {
    name: 'UserEditView',
    props: {
        userId: {
            type: [Number, String, null],
            required: false,
            default: null
        }
    },
    data() {
        return {
            id: 0,
            nombre: '',
            rut: '',
            telefono: '',
            correo: '',
            estado: 'ACTIVO',
            fecha_nacimiento: '',
            grupo_sanguineo: 'O',
            factor_rh: '+',
            fecha_ingreso: '',
            n_registro: '',
            foto_perfil: null,
            fotoPreview: '',
            password: '',
            rolesOptions: [],
            selectedRoles: [],
            url: 'http://localhost:8000/api/user/',
            rolesUrl: 'http://localhost:8000/api/role',
            modalInstance: null
        };
    },
    computed: {
        esVoluntario() {
            return this.selectedRoleDetails.some((role) => role.slug === 'voluntario');
        },
        selectedRoleDetails() {
            return this.rolesOptions.filter((role) => this.selectedRoles.includes(role.id));
        }
    },
    watch: {
        userId: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.id = newVal;
                    this.getUser();
                }
            }
        }
    },
    async mounted() {
        this.modalInstance = new Modal(document.getElementById('editUserModal'));
        await this.fetchRoles();
    },
    methods: {
        async fetchRoles() {
            try {
                const response = await axios.get(this.rolesUrl);
                this.rolesOptions = response.data;
            } catch (error) {
                show_alerta('No se pudieron cargar los roles', 'error');
            }
        },
        show() {
            this.modalInstance.show();
        },
        hide() {
            this.modalInstance.hide();
        },
        clearVoluntarioFields() {
            this.fecha_nacimiento = '';
            this.grupo_sanguineo = 'O';
            this.factor_rh = '+';
            this.fecha_ingreso = '';
            this.n_registro = '';
            this.clearPhotoSelection();
        },
        clearPhotoSelection() {
            this.foto_perfil = null;
            this.fotoPreview = '';
        },
        onPhotoSelected(event) {
            const file = event.target.files?.[0] || null;
            this.foto_perfil = file;
            this.fotoPreview = file ? URL.createObjectURL(file) : (this.fotoPreview || '');
        },
        buildFormData() {
            const formData = new FormData();

            formData.append('_method', 'PUT');
            formData.append('nombre', this.nombre);
            formData.append('rut', this.rut);
            formData.append('telefono', this.telefono);
            formData.append('email', this.correo);
            formData.append('estado', this.estado);
            this.selectedRoles.forEach((roleId) => formData.append('roles[]', roleId));

            if (this.esVoluntario) {
                formData.append('fecha_nacimiento', this.fecha_nacimiento);
                formData.append('grupo_sanguineo', this.grupo_sanguineo);
                formData.append('factor_rh', this.factor_rh);
                formData.append('fecha_ingreso', this.fecha_ingreso);
                formData.append('n_registro', this.n_registro);

                if (this.foto_perfil) {
                    formData.append('foto_perfil', this.foto_perfil);
                }
            }

            if (this.password.trim() !== '') {
                formData.append('password', this.password);
            }

            return formData;
        },
        async getUser() {
            try {
                if (this.rolesOptions.length === 0) {
                    await this.fetchRoles();
                }

                const response = await axios.get(this.url + this.id);
                const user = response.data;
                const voluntario = user.voluntario || null;

                this.nombre = user.nombre;
                this.rut = user.rut;
                this.telefono = user.telefono;
                this.correo = user.email;
                this.estado = user.estado || 'ACTIVO';
                this.selectedRoles = (user.roles || []).map((role) => role.id);
                this.fecha_nacimiento = voluntario?.fecha_nacimiento || '';
                this.grupo_sanguineo = voluntario?.grupo_sanguineo || 'O';
                this.factor_rh = voluntario?.factor_rh || '+';
                this.fecha_ingreso = voluntario?.fecha_ingreso || '';
                this.n_registro = voluntario?.n_registro || '';
                this.foto_perfil = null;
                this.fotoPreview = voluntario?.foto_perfil_url || '';
                this.password = '';
            } catch (error) {
                show_alerta('Error al cargar los datos del registro', 'error');
            }
        },
        async guardar() {
            if (this.nombre.trim() === '') {
                show_alerta('Escribe el nombre', 'warning', 'edit-nombre');
                return;
            }
            if (this.rut.trim() === '') {
                show_alerta('Escribe el rut', 'warning', 'edit-rut');
                return;
            }
            if (this.telefono.trim() === '') {
                show_alerta('Escribe el telefono', 'warning', 'edit-telefono');
                return;
            }
            if (this.correo.trim() === '') {
                show_alerta('Escribe el correo', 'warning', 'edit-correo');
                return;
            }
            if (this.esVoluntario && this.fecha_nacimiento.trim() === '') {
                show_alerta('Escribe la fecha de nacimiento', 'warning', 'edit-fecha_nacimiento');
                return;
            }
            if (this.esVoluntario && this.fecha_ingreso.trim() === '') {
                show_alerta('Escribe la fecha de ingreso', 'warning', 'edit-fecha_ingreso');
                return;
            }
            if (this.esVoluntario && this.n_registro.trim() === '') {
                show_alerta('Escribe el numero de registro', 'warning', 'edit-n_registro');
                return;
            }

            try {
                const respuesta = await axios.post(this.url + this.id, this.buildFormData(), {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                if (respuesta.status === 200) {
                    show_alerta('Registro actualizado', 'success');
                    this.hide();
                    this.$emit('user-updated');
                    return;
                }

                show_alerta('No se pudo actualizar el registro', 'error');
            } catch (error) {
                if (error.response && error.response.data) {
                    const errores = error.response.data.errors || {};
                    let listado = '';
                    Object.keys(errores).forEach((key) => {
                        listado += `${errores[key][0]}. `;
                    });
                    show_alerta(listado || 'Error al actualizar el registro', 'error');
                } else {
                    show_alerta('Error al actualizar el registro', 'error');
                }
            }
        }
    }
};
</script>

<style scoped>
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

.photo-upload-card {
    display: grid;
    grid-template-columns: 120px 1fr;
    gap: 16px;
    align-items: center;
    padding: 12px;
    border: 1px solid #d9d9d9;
    border-radius: 10px;
    background: #fafafa;
}

.photo-preview {
    width: 120px;
    height: 120px;
    border-radius: 16px;
    background: #f0f2f5;
    border: 2px dashed #c9d2dd;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #7a8699;
    font-weight: 600;
}

.photo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-upload-fields {
    display: grid;
    gap: 8px;
}
</style>
