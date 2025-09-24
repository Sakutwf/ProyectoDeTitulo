<template>
    <div class="row mt-3">
        <div class="col-md-6 offset-md-3">
            <div class="card shadow">
                <div class="card-header bg-danger text-white text-center">
                    <h4 class="mb-0"><i class="fa-solid fa-user-edit me-2"></i>Editar Usuario</h4>
                </div>
                <div class="card-body">
                    <form v-on:submit="guardar">
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input type="text" v-model="nombre" id="nombre" class="form-control" placeholder="Nombre">
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                            <input type="text" v-model="telefono" id="telefono" class="form-control" placeholder="Teléfono">
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                            <input type="email" v-model="correo" id="correo" class="form-control" placeholder="Correo">
                        </div>
                        <div class="d-grid col-6 mx-auto">
                            <button class="btn btn-danger">
                                <i class="fa-solid fa-floppy-disk"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            nombre: '',
            telefono: '',
            correo: ''
        };
    },
    methods: {
        guardar() {
            // Lógica para guardar los datos
        }
    }
};
</script>

<style scoped>
/* Aplicamos estilos con scoped para asegurarnos que no sean sobrescritos */
.card-header {
    background-color: #ec1c24 !important;
    color: white !important;
}

.cruz-roja-btn {
    background-color: #ec1c24 !important;
    border-color: #ec1c24 !important;
    color: white !important;
}

.cruz-roja-btn:hover {
    background-color: #c4161d !important;
    border-color: #c4161d !important;
    color: white !important;
}

/* Asegurarnos que Bootstrap no sobrescriba nuestros estilos */
.btn-primary, .bg-primary, .btn-success, .bg-success, .btn-dark, .bg-dark {
    background-color: #ec1c24 !important;
    border-color: #ec1c24 !important;
}
</style>