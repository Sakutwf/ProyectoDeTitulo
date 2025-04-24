<template>
    <div class="row mt-3">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header bg-dark text-white text-center">Editar Usuario</div>
                <div class="card-body">
                    <form v-on:submit="guardar">
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-gift"></i></span>
                            <input type="text" v-model="nombre" id="nombre" class="form-control"
                            maxlenght="50" placeholder="Nombre" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-comment"></i></span>
                            <input type="text" v-model="rut" id="rut" class="form-control"
                            maxlenght="50" placeholder="Rut" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <select id="rol" v-model="rol" class="form-select" required>
                                <option value="">Seleccione un rol</option>
                                <option value="1">Administrador</option>
                                <option value="2">Voluntario</option>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                            <input type="text" v-model="telefono" id="telefono" class="form-control"
                            maxlenght="50" placeholder="Telefono" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                            <input type="email" v-model="correo" id="correo" class="form-control"
                            maxlenght="50" placeholder="Correo" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                            <input type="date" v-model="fecha_nacimiento" id="fecha_nacimiento" class="form-control"
                            maxlenght="50" placeholder="Fecha de nacimiento" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-tint"></i></span>
                            <select id="grupo_sanguineo" v-model="grupo_sanguineo" class="form-select" required>
                                <option value="">Seleccione un grupo sanguineo</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-tint"></i></span>
                            <select id="factor_rh" v-model="factor_rh" class="form-select" required>
                                <option value="">Seleccione un factor Rh</option>
                                <option value="Positivo">Positivo</option>
                                <option value="Negativo">Negativo</option>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                            <input type="date" v-model="fecha_ingreso" id="fecha_ingreso" class="form-control"
                            maxlenght="50" placeholder="Fecha de ingreso" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                            <input type="password" v-model="password" id="password" class="form-control"
                            maxlenght="50" placeholder="Contraseña (Deja el campo en blanco si no deseas modificarla)">
                        </div>
                        <div class="d-grid col-6 mx-auto">
                            <button class="btn btn-success"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import axios from 'axios';
import {show_alerta } from '../funciones';
import { useRoute } from 'vue-router';
export default{
    data() {
        return {
            id:0,
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
            url:'http://localhost:8000/api/user'
        }
    },
    mounted() {
        const route = useRoute();
        this.id = route.params.id;
        this.url = this.url + '/' + this.id;
        this.getUser();
    },
    methods:{
        async getUser() {
            await axios.get(this.url).then(
                response => (
                    this.nombre = response.data.nombre,
                    this.rut = response.data.rut,
                    this.rol = response.data.role_id.id,
                    this.telefono = response.data.telefono,
                    this.correo = response.data.email,
                    this.fecha_nacimiento = response.data.fecha_nacimiento,
                    this.grupo_sanguineo = response.data.grupo_sanguineo,
                    this.factor_rh = response.data.factor_rh,
                    this.fecha_ingreso = response.data.fecha_ingreso
                )
            );
        },
        async guardar(event){
            event.preventDefault();
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
            else{
                var parametros= {
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
                const respuesta = await axios.put(this.url, parametros);
                const status = respuesta.status;
                const mensaje = respuesta.mensaje || 'Usuario Actualizado';

                if (status === 200) {
                    show_alerta(mensaje, 'success');
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 1000);
                } else {
                    const errores = respuesta.errors || {};
                    let listado = '';
                    Object.keys(errores).forEach(key => {
                        listado += errores[key][0] + '. ';
                    });
                    show_alerta(listado, 'error');
                }               
            }   
        }
    }
}
</script>