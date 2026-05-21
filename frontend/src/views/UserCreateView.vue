<template>
    <div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="newUserModalLabel">
                        <i class="fa-solid fa-user-plus me-2"></i>Nuevo Registro
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="guardar">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="create-nombre" class="form-label">Nombre</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                    <input
                                        id="create-nombre"
                                        v-model="nombre"
                                        type="text"
                                        class="form-control"
                                        required
                                        placeholder="Nombre completo"
                                        :class="{ 'is-invalid': nombre && !isNameValid }"
                                    >
                                </div>
                                <div v-if="nombre && !isNameValid" class="invalid-feedback d-block">
                                    El nombre solo debe contener letras y espacios.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="create-rut" class="form-label">RUT</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
                                    <input
                                        id="create-rut"
                                        v-model="rut"
                                        type="text"
                                        class="form-control"
                                        required
                                        placeholder="Ej: 12345678-9"
                                        :class="{ 'is-invalid': rut && !isRutValid }"
                                    >
                                </div>
                                <div v-if="rut && !isRutValid" class="invalid-feedback d-block">
                                    RUT invalido.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="create-correo" class="form-label">Correo electronico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                                    <input
                                        id="create-correo"
                                        v-model="correo"
                                        type="email"
                                        class="form-control"
                                        required
                                        placeholder="correo@ejemplo.com"
                                        :class="{ 'is-invalid': correo && !isEmailValid }"
                                    >
                                </div>
                                <div v-if="correo && !isEmailValid" class="invalid-feedback d-block">
                                    Correo electronico invalido.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="create-telefono" class="form-label">Telefono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                    <input
                                        id="create-telefono"
                                        v-model="telefono"
                                        type="text"
                                        class="form-control"
                                        placeholder="Numero de telefono"
                                        :class="{ 'is-invalid': telefono && !isPhoneValid }"
                                    >
                                </div>
                                <div v-if="telefono && !isPhoneValid" class="invalid-feedback d-block">
                                    El telefono solo debe contener numeros.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="create-estado" class="form-label">Estado</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-circle-check"></i></span>
                                    <select id="create-estado" v-model="estado" class="form-select" required>
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
                                            :id="`create-role-${role.id}`"
                                            v-model="selectedRoles"
                                            class="form-check-input"
                                            type="checkbox"
                                            :value="role.id"
                                        >
                                        <div>
                                            <div class="fw-semibold">{{ role.name }}</div>
                                            <small class="text-muted">{{ role.description || 'Sin descripcion' }}</small>
                                        </div>
                                    </label>
                                </div>
                                <div v-if="rolesOptions.length === 0" class="text-muted small mt-2">
                                    No hay roles disponibles.
                                </div>
                                <div class="form-text">
                                    Si asignas el rol Voluntario, se habilitara su ficha.
                                </div>
                            </div>

                            <template v-if="esVoluntario">
                                <div class="col-md-6">
                                    <label for="create-fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                                        <input id="create-fecha_nacimiento" v-model="fecha_nacimiento" type="date" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="create-fecha_ingreso" class="form-label">Fecha de ingreso</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-calendar-check"></i></span>
                                        <input id="create-fecha_ingreso" v-model="fecha_ingreso" type="date" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="create-grupo_sanguineo" class="form-label">Grupo sanguineo</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-tint"></i></span>
                                        <select id="create-grupo_sanguineo" v-model="grupo_sanguineo" class="form-select">
                                            <option value="A">A</option>
                                            <option value="B">B</option>
                                            <option value="AB">AB</option>
                                            <option value="O">O</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="create-factor_rh" class="form-label">Factor RH</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-tint"></i></span>
                                        <select id="create-factor_rh" v-model="factor_rh" class="form-select">
                                            <option value="+">+</option>
                                            <option value="-">-</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="create-n_registro" class="form-label">N registro</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                                        <input id="create-n_registro" v-model="n_registro" type="text" class="form-control" placeholder="Ej: VOL-003">
                                    </div>
                                </div>
                            </template>

                            <div class="col-md-12">
                                <label for="create-password" class="form-label">Contrasena</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                    <input id="create-password" v-model="password" type="password" class="form-control" required placeholder="Contrasena">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" @click="guardar" :disabled="!isFormValid">
                        <i class="fa-solid fa-save me-1"></i>Guardar Registro
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
    name: 'UserCreateView',
    data() {
        return {
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
            password: '',
            rolesOptions: [],
            selectedRoles: [],
            url: 'http://localhost:8000/api/user',
            rolesUrl: 'http://localhost:8000/api/role',
            modalInstance: null
        };
    },
    computed: {
        isNameValid() {
            return this.nombre.trim() !== '' && /^[a-zA-ZÀ-ÿ\s]+$/.test(this.nombre);
        },
        isRutValid() {
            return this.validaRut(this.rut.trim());
        },
        isEmailValid() {
            return this.correo.trim() !== '' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.correo);
        },
        isPhoneValid() {
            return this.telefono.trim() !== '' && /^[0-9]+$/.test(this.telefono);
        },
        isFormValid() {
            return this.isNameValid && this.isRutValid && this.isEmailValid && this.isPhoneValid;
        },
        esVoluntario() {
            return this.selectedRoleDetails.some((role) => role.slug === 'voluntario');
        },
        selectedRoleDetails() {
            return this.rolesOptions.filter((role) => this.selectedRoles.includes(role.id));
        }
    },
    mounted() {
        this.modalInstance = new Modal(document.getElementById('newUserModal'));
        this.fetchRoles();
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
        validaRut(rutCompleto) {
            rutCompleto = rutCompleto.replace('‐', '-');
            if (!/^[0-9]+[-|‐]{1}[0-9kK]{1}$/.test(rutCompleto)) return false;
            const tmp = rutCompleto.split('-');
            let digv = tmp[1];
            const rut = tmp[0];
            if (digv === 'K') digv = 'k';

            return this.dv(rut) === digv;
        },
        dv(T) {
            let M = 0;
            let S = 1;
            for (; T; T = Math.floor(T / 10)) {
                S = (S + T % 10 * (9 - M++ % 6)) % 11;
            }
            return S ? S - 1 : 'k';
        },
        show() {
            this.resetForm();
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
        },
        resetForm() {
            this.nombre = '';
            this.rut = '';
            this.telefono = '';
            this.correo = '';
            this.estado = 'ACTIVO';
            this.password = '';
            this.selectedRoles = [];
            this.clearVoluntarioFields();
        },
        async guardar() {
            if (this.nombre.trim() === '') {
                show_alerta('Escribe el nombre', 'warning', 'create-nombre');
                return;
            }
            if (this.rut.trim() === '') {
                show_alerta('Escribe el rut', 'warning', 'create-rut');
                return;
            }
            if (this.telefono.trim() === '') {
                show_alerta('Escribe el telefono', 'warning', 'create-telefono');
                return;
            }
            if (this.correo.trim() === '') {
                show_alerta('Escribe el correo', 'warning', 'create-correo');
                return;
            }
            if (this.esVoluntario && this.fecha_nacimiento.trim() === '') {
                show_alerta('Escribe la fecha de nacimiento', 'warning', 'create-fecha_nacimiento');
                return;
            }
            if (this.esVoluntario && this.fecha_ingreso.trim() === '') {
                show_alerta('Escribe la fecha de ingreso', 'warning', 'create-fecha_ingreso');
                return;
            }
            if (this.esVoluntario && this.n_registro.trim() === '') {
                show_alerta('Escribe el numero de registro', 'warning', 'create-n_registro');
                return;
            }
            if (this.password.trim() === '') {
                show_alerta('Escribe la contrasena', 'warning', 'create-password');
                return;
            }

            try {
                const parametros = {
                    nombre: this.nombre,
                    rut: this.rut,
                    telefono: this.telefono,
                    email: this.correo,
                    estado: this.estado,
                    password: this.password,
                    roles: this.selectedRoles
                };

                if (this.esVoluntario) {
                    Object.assign(parametros, {
                        fecha_nacimiento: this.fecha_nacimiento,
                        grupo_sanguineo: this.grupo_sanguineo,
                        factor_rh: this.factor_rh,
                        fecha_ingreso: this.fecha_ingreso,
                        n_registro: this.n_registro
                    });
                }

                const respuesta = await axios.post(this.url, parametros);

                if (respuesta.status === 201 || respuesta.status === 200) {
                    show_alerta('Registro creado correctamente', 'success');
                    this.hide();
                    this.$emit('user-created');
                    this.resetForm();
                    return;
                }

                show_alerta('No se pudo crear el registro', 'error');
            } catch (error) {
                if (error.response && error.response.data) {
                    const errores = error.response.data.errors || {};
                    let listado = '';
                    Object.keys(errores).forEach((key) => {
                        listado += `${errores[key][0]}. `;
                    });
                    show_alerta(listado || 'Error al crear el registro', 'error');
                } else {
                    show_alerta('Error al crear el registro', 'error');
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

.is-invalid {
    border-color: #dc3545 !important;
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
</style>
