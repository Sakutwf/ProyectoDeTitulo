<template>
    <div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="newUserModalLabel">
                        <i class="fa-solid fa-user-plus me-2"></i>Nuevo Usuario
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
                                        type="text"
                                        class="form-control"
                                        id="create-nombre"
                                        v-model="nombre"
                                        required
                                        placeholder="Nombre completo"
                                        :class="{'is-invalid': nombre && !isNameValid}"
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
                                        type="text"
                                        class="form-control"
                                        id="create-rut"
                                        v-model="rut"
                                        required
                                        placeholder="Ej: 12345678-9"
                                        :class="{'is-invalid': rut && !isRutValid}"
                                    >
                                </div>
                                <div v-if="rut && !isRutValid" class="invalid-feedback d-block">
                                    RUT inválido.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="create-correo" class="form-label">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                                    <input
                                        type="email"
                                        class="form-control"
                                        id="create-correo"
                                        v-model="correo"
                                        required
                                        placeholder="correo@ejemplo.com"
                                        :class="{'is-invalid': correo && !isEmailValid}"
                                    >
                                </div>
                                <div v-if="correo && !isEmailValid" class="invalid-feedback d-block">
                                    Correo electrónico inválido.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="create-telefono" class="form-label">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="create-telefono"
                                        v-model="telefono"
                                        placeholder="Número de teléfono"
                                        :class="{'is-invalid': telefono && !isPhoneValid}"
                                    >
                                </div>
                                <div v-if="telefono && !isPhoneValid" class="invalid-feedback d-block">
                                    El teléfono solo debe contener números.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="create-fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                                    <input type="date" class="form-control" id="create-fecha_nacimiento" v-model="fecha_nacimiento">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="create-fecha_ingreso" class="form-label">Fecha de Ingreso</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-calendar-check"></i></span>
                                    <input type="date" class="form-control" id="create-fecha_ingreso" v-model="fecha_ingreso">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="create-grupo_sanguineo" class="form-label">Grupo Sanguíneo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-tint"></i></span>
                                    <select class="form-select" id="create-grupo_sanguineo" v-model="grupo_sanguineo">
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
                                    <select class="form-select" id="create-factor_rh" v-model="factor_rh">
                                        <option value="+">+</option>
                                        <option value="-">-</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="create-rol" class="form-label">Rol</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-user-tag"></i></span>
                                    <select id="create-rol" v-model="rol" class="form-select" required>
                                        <option value="">Seleccione un rol</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label for="create-password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                    <input type="password" class="form-control" id="create-password" v-model="password" required placeholder="Contraseña">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" @click="guardar" :disabled="!isFormValid">
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
import { Modal } from 'bootstrap';

export default {
    name: 'UserCreateView',
    props: {
        roles: {
            type: Array,
            default: () => [
                { id: 1, name: 'Administrador' },
                { id: 2, name: 'Voluntario' }
            ]
        }
    },
    data() {
        return {
            nombre: '',
            rut: '',
            rol: '',
            telefono: '',
            correo: '',
            fecha_nacimiento: '',
            grupo_sanguineo: 'O',
            factor_rh: '+',
            fecha_ingreso: '',
            password: '',
            url: 'http://localhost:8000/api/user',
            modalInstance: null
        }
    },
    computed: {
        isNameValid() {
        /// Validar que el nombre no esté vacío y contenga solo letras y espacios
            return this.nombre.trim() !== '' && /^[a-zA-Z\s]+$/.test(this.nombre);
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
        isFormValid(){
            return this.isNameValid && this.isRutValid && this.isEmailValid && this.isPhoneValid;
        }
    },
    
    mounted() {
        this.modalInstance = new Modal(document.getElementById('newUserModal'));
    },
    methods: {
        validaRut(rutCompleto) {
            rutCompleto = rutCompleto.replace("‐", "-");
            if (!/^[0-9]+[-|‐]{1}[0-9kK]{1}$/.test(rutCompleto))
                return false;
            var tmp = rutCompleto.split('-');
            var digv = tmp[1];
            var rut = tmp[0];
            if (digv == 'K') digv = 'k';

            return (this.dv(rut) == digv);
        },
        dv(T) {
            var M = 0, S = 1;
            for (; T; T = Math.floor(T / 10))
                S = (S + T % 10 * (9 - M++ % 6)) % 11;
            return S ? S - 1 : 'k';
        },
        show() {
            this.resetForm();
            this.modalInstance.show();
        },
        hide() {
            this.modalInstance.hide();
        },
        resetForm() {
            this.nombre = '';
            this.rut = '';
            this.rol = '';
            this.telefono = '';
            this.correo = '';
            this.fecha_nacimiento = '';
            this.grupo_sanguineo = 'O';
            this.factor_rh = '+';
            this.fecha_ingreso = '';
            this.password = '';
        },
        async guardar() {
            if(this.nombre.trim() === ''){
                show_alerta('Escribe el nombre', 'warning', 'nombre');
                console.log('nombre', this.nombre);
            }
            else if(this.rut.trim() === ''){
                show_alerta('Escribe el rut', 'warning', 'rut');
                console.log('rut', this.rut);
            }
            else if(this.rol === ''){
                show_alerta('Selecciona un rol', 'warning', 'rol');
                console.log('rol', this.rol);
            }
            else if(this.telefono.trim() === ''){
                show_alerta('Escribe el telefono', 'warning', 'telefono');
                console.log('telefono', this.telefono);
            }
            else if(this.correo.trim() === ''){
                show_alerta('Escribe el correo', 'warning', 'correo');
                console.log('correo', this.correo);
            }
            else if(this.fecha_nacimiento.trim() === ''){
                show_alerta('Escribe la fecha de nacimiento', 'warning', 'fecha_nacimiento');
            }
            else if(this.grupo_sanguineo.trim() === ''){
                show_alerta('Selecciona un grupo sanguineo', 'warning', 'grupo_sanguineo');
            }
            else if(this.factor_rh.trim() === ''){
                show_alerta('Selecciona un factor Rh', 'warning', 'factor_rh');
            }
            else if(this.fecha_ingreso.trim() === ''){
                show_alerta('Escribe la fecha de ingreso', 'warning', 'fecha_ingreso');
            }
            else if(this.password.trim() === ''){
                show_alerta('Escribe la contraseña', 'warning', 'password');
            }
            else {
                try {
                    var parametros = {
                        nombre: this.nombre,
                        rut: this.rut,
                        role_id: this.rol,
                        filial_id: 1,
                        telefono: this.telefono,
                        email: this.correo,
                        fecha_nacimiento: this.fecha_nacimiento,
                        grupo_sanguineo: this.grupo_sanguineo,
                        factor_rh: this.factor_rh,
                        fecha_ingreso: this.fecha_ingreso,
                        password: this.password
                    }
                    console.log('parametros', parametros);
                    const respuesta = await axios.post(this.url, parametros);
                    const status = respuesta.status;
                    const mensaje = respuesta.mensaje || 'Operación exitosa';

                    if (status === 201 || status === 200) {
                        show_alerta(mensaje, 'success');
                        this.hide();
                        this.$emit('user-created');
                        this.resetForm();
                    } else {
                        const errores = respuesta.data?.errors || {};
                        let listado = '';
                        Object.keys(errores).forEach(key => {
                            listado += errores[key][0] + '. ';
                        });
                        show_alerta(listado, 'error');
                    }
                } catch (error) {
                    console.error("Error al crear usuario:", error);
                    
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
            }
        },
        
    }
}
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
/* Feedback visual para campos inválidos */
.is-invalid {
    border-color: #dc3545 !important;
}
</style>
