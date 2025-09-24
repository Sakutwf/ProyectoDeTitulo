<template>
    <div class="d-flex">
        <!-- Importar el componente de barra lateral -->
        <SidebarMenu />

        <!-- Contenido principal -->
        <div class="content-wrapper">
            <!-- Encabezado -->
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="m-0"><i class="fa-solid fa-home me-2"></i>Inicio</h3>
                </div>
            </div>
            
            <!-- Contenido principal -->
            <div class="content">
                <div class="card shadow welcome-card">
                    <div class="card-body text-center">
                        <div class="welcome-icon">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <h2 class="welcome-title">¡Bienvenido, Administrador!</h2>
                        <p class="welcome-text">
                            Has iniciado sesión correctamente en el sistema de gestión.
                            Desde aquí podrás administrar todos los aspectos de la aplicación.
                        </p>
                        <div class="welcome-actions mt-4">
                            <button class="btn btn-danger me-3" @click="navigateToVoluntarios">
                                <i class="fa-solid fa-users me-2"></i>Gestionar Usuarios
                            </button>
                            <button class="btn btn-outline-secondary">
                                <i class="fa-solid fa-chart-line me-2"></i>Ver Estadísticas
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import SidebarMenu from '../components/SidebarMenu.vue';
    
    export default {
        name: 'HomeView',
        components: {
            SidebarMenu
        },
        data() {
            return {
                // Datos básicos que podrían ser necesarios en el futuro
            }
        },
        mounted() {
            // Se puede añadir lógica de inicialización si es necesaria
        },
        methods: {
            // Métodos básicos que podrían ser necesarios en el futuro
            navigateToVoluntarios() {
                this.$router.push('/voluntarios');
            }
        }
    }
</script>

<style scoped>
.card {
    border: none;
    border-radius: 8px;
    overflow: hidden;
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

/* Estilos para la tarjeta de bienvenida */
.welcome-card {
    padding: 2rem;
    margin-top: 2rem;
}

.welcome-icon {
    font-size: 4rem;
    color: #e01e1e;
    margin-bottom: 1.5rem;
}

.welcome-title {
    font-weight: 700;
    margin-bottom: 1rem;
    color: #333;
}

.welcome-text {
    font-size: 1.1rem;
    color: #666;
    max-width: 600px;
    margin: 0 auto;
}

.welcome-actions .btn {
    padding: 0.6rem 1.5rem;
    font-weight: 600;
}
</style>