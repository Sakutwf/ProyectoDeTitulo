<template>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="@/assets/LogoHorizontal.svg" alt="Cruz Roja Logo" class="logo">
            </div>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item" :class="{ active: activeLink === 'inicio' }">
                <router-link to="/inicio" class="nav-link" @click="setActive('inicio')">
                    <i class="fa-solid fa-tachometer-alt me-2"></i> Inicio
                </router-link>
            </li>
            <li class="nav-item" :class="{ active: activeLink === 'voluntarios' }">
                <router-link to="/voluntarios" class="nav-link" @click="setActive('voluntarios')">
                    <i class="fa-solid fa-users me-2"></i> Voluntarios
                </router-link>
            </li>
            <li class="nav-item" :class="{ active: activeLink === 'actividades' }">
                <router-link to="/actividades" class="nav-link" @click="setActive('actividades')">
                    <i class="fa-solid fa-list me-2"></i> Actividades
                </router-link>
            </li>
            <li class="nav-item" :class="{ active: activeLink === 'eventos' }">
                <router-link to="/eventos" class="nav-link" @click="setActive('eventos')">
                    <i class="fa-solid fa-calendar-check me-2"></i> Eventos
                </router-link>
            </li>
            <li class="nav-item" :class="{ active: activeLink === 'donaciones' }">
                <router-link to="/donaciones" class="nav-link" @click="setActive('donaciones')">
                    <i class="fa-solid fa-hand-holding-heart me-2"></i> Donaciones
                </router-link>
            </li>
            <li class="nav-item" :class="{ active: activeLink === 'configuracion' }">
                <router-link to="/configuracion" class="nav-link" @click="setActive('configuracion')">
                    <i class="fa-solid fa-cog me-2"></i> Configuración
                </router-link>
            </li>
            <li class="nav-item mt-auto">
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-sign-out-alt me-2"></i> Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    name: 'SidebarMenu',
    data() {
        return {
            activeLink: 'usuarios' // Por defecto, la sección activa es 'usuarios'
        };
    },
    methods: {
        setActive(link) {
            this.activeLink = link;
        }
    },
    mounted() {
        // Detectar la ruta actual y activar el enlace correspondiente
        const path = this.$route.path;
        if (path === '/') this.activeLink = 'inicio';
        else if (path.includes('inicio')) this.activeLink = 'inicio';
        else if (path.includes('beneficiarios')) this.activeLink = 'beneficiarios';
        else if (path.includes('voluntarios')) this.activeLink = 'voluntarios';
        else if (path.includes('actividades')) this.activeLink = 'actividades';
        else if (path.includes('eventos')) this.activeLink = 'eventos';
        else if (path.includes('donaciones')) this.activeLink = 'donaciones';
        else if (path.includes('capacitaciones')) this.activeLink = 'capacitaciones';
        else if (path.includes('configuracion')) this.activeLink = 'configuracion';
    }
}
</script>

<style scoped>
.sidebar {
    width: 250px;
    min-height: 100vh;
    background-color: #e01e1e; /* Rojo de Cruz Roja */
    color: #fff;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
}

.sidebar-header {
    padding: 1.5rem 0.5rem; /* Reducido el padding lateral para dar más espacio al logo */
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    justify-content: center;
}

.logo-container {
    display: flex;
    align-items: center;
    width: 100%; /* Hacer que el contenedor ocupe todo el ancho disponible */
    justify-content: center;
}

.logo {
    width: 90%; /* Establecer el ancho al 90% del contenedor para dejar un pequeño margen */
    height: auto; /* Mantener proporciones */
    max-height: 50px; /* Limitar altura máxima */
}

.sidebar-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.sidebar .nav-link {
    color: rgba(255, 255, 255, 0.9);
    padding: 0.75rem 1rem;
    font-weight: 500;
    border-left: 3px solid transparent;
    transition: all 0.2s;
}

.sidebar .nav-link:hover,
.sidebar .nav-item.active .nav-link {
    color: #fff;
    background-color: rgba(255, 255, 255, 0.15);
    border-left-color: #ffffff;
}

.sidebar .nav-item.active .nav-link {
    background-color: rgba(255, 255, 255, 0.2);
    font-weight: 600;
}
</style>
