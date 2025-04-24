<template>
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="editUserModalLabel">
                        <i class="fa-solid fa-user-edit me-2"></i>Editar Usuario
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
                                    <input type="text" class="form-control" id="edit-nombre" v-model="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-rut" class="form-label">RUT</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
                                    <input type="text" class="form-control" id="edit-rut" v-model="rut" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-correo" class="form-label">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                                    <input type="email" class="form-control" id="edit-correo" v-model="correo" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-telefono" class="form-label">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                    <input type="text" class="form-control" id="edit-telefono" v-model="telefono">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                                    <input type="date" class="form-control" id="edit-fecha_nacimiento" v-model="fecha_nacimiento">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-fecha_ingreso" class="form-label">Fecha de Ingreso</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-calendar-check"></i></span>
                                    <input type="date" class="form-control" id="edit-fecha_ingreso" v-model="fecha_ingreso">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="edit-grupo_sanguineo" class="form-label">Grupo Sanguíneo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-tint"></i></span>
                                    <select class="form-select" id="edit-grupo_sanguineo" v-model="grupo_sanguineo">
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
                                    <select class="form-select" id="edit-factor_rh" v-model="factor_rh">
                                        <option value="+">+</option>
                                        <option value="-">-</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="edit-rol" class="form-label">Rol</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-user-tag"></i></span>
                                    <select id="edit-rol" v-model="rol" class="form-select" required>
                                        <option value="">Seleccione un rol</option>
                                        <option value="1">Administrador</option>
                                        <option value="2">Voluntario</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label for="edit-password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                    <input type="password" class="form-control" id="edit-password" v-model="password" 
                                           placeholder="Contraseña (Deja el campo en blanco si no deseas modificarla)">
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
            rol: '',
            telefono: '',
            correo: '',
            fecha_nacimiento: '',
            grupo_sanguineo: '',
            factor_rh: '',
            fecha_ingreso: '',
            password: '',
            url: 'http://localhost:8000/api/user/',
            modalInstance: null
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
    mounted() {
        this.modalInstance = new Modal(document.getElementById('editUserModal'));
    },
    methods: {
        show() {
            this.modalInstance.show();
        },
        hide() {
            this.modalInstance.hide();
        },
        async getUser() {
            try {
                const response = await axios.get(this.url + this.id);
                this.nombre = response.data.nombre;
                this.rut = response.data.rut;
                this.rol = response.data.role_id.id;
                this.telefono = response.data.telefono;
                this.correo = response.data.email;
                this.fecha_nacimiento = response.data.fecha_nacimiento;
                this.grupo_sanguineo = response.data.grupo_sanguineo;
                this.factor_rh = response.data.factor_rh;
                this.fecha_ingreso = response.data.fecha_ingreso;
                
                console.log("Datos cargados:", {
                    grupo_sanguineo: this.grupo_sanguineo,
                    factor_rh: this.factor_rh
                });
            } catch (error) {
                console.error("Error al cargar usuario:", error);
                show_alerta('Error al cargar los datos del usuario', 'error');
            }
        },
        async guardar() {
            if(this.nombre.trim() === ''){
                show_alerta('Escribe el nombre', 'warning', 'nombre');
            }
            else if(this.rut.trim() === ''){
                show_alerta('Escribe el rut', 'warning', 'rut');
            }
            else if(!this.rol){
                show_alerta('Selecciona un rol', 'warning', 'rol');
            }
            else if(this.telefono.trim() === ''){
                show_alerta('Escribe el telefono', 'warning', 'telefono');
            }
            else if(this.correo.trim() === ''){
                show_alerta('Escribe el correo', 'warning', 'correo');
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

                    if (this.password.trim() === '') {
                        delete parametros.password;
                    }
                    
                    const respuesta = await axios.put(this.url + this.id, parametros);
                    const status = respuesta.status;
                    const mensaje = respuesta.mensaje || 'Usuario Actualizado';

                    if (status === 200) {
                        show_alerta(mensaje, 'success');
                        this.hide();
                        this.$emit('user-updated');
                    } else {
                        const errores = respuesta.errors || {};
                        let listado = '';
                        Object.keys(errores).forEach(key => {
                            listado += errores[key][0] + '. ';
                        });
                        show_alerta(listado, 'error');
                    }
                } catch (error) {
                    console.error("Error al actualizar:", error);
                    show_alerta('Error al actualizar usuario', 'error');
                }
            }
        }
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
</style>
