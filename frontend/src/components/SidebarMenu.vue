<template>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="@/assets/LogoHorizontal.svg" alt="Cruz Roja Logo" class="logo">
                <h3 class="sidebar-title">Cruz Roja</h3>
            </div>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item" :class="{ active: activeLink === 'dashboard' }">
                <router-link to="/dashboard" class="nav-link" @click="setActive('dashboard')">
                    <i class="fa-solid fa-tachometer-alt me-2"></i> Dashboard
                </router-link>
            </li>
            <li class="nav-item" :class="{ active: activeLink === 'usuarios' }">
                <router-link to="/" class="nav-link" @click="setActive('usuarios')">
                    <i class="fa-solid fa-users me-2"></i> Usuarios
                </router-link>
            </li>
            <li class="nav-item" :class="{ active: activeLink === 'beneficiarios' }">
                <router-link to="/beneficiarios" class="nav-link" @click="setActive('beneficiarios')">
                    <i class="fa-solid fa-hands-helping me-2"></i> Beneficiarios
                </router-link>
            </li>
            <li class="nav-item" :class="{ active: activeLink === 'voluntarios' }">
                <router-link to="/voluntarios" class="nav-link" @click="setActive('voluntarios')">
                    <i class="fa-solid fa-user-plus me-2"></i> Voluntarios
                </router-link>
            </li>
            <li class="nav-item" :class="{ active: activeLink === 'actividades' }">
                <router-link to="/actividades" class="nav-link" @click="setActive('actividades')">
                    <i class="fa-solid fa-calendar-check me-2"></i> Actividades
                </router-link>
            </li>
            <li class="nav-item" :class="{ active: activeLink === 'donaciones' }">
                <router-link to="/donaciones" class="nav-link" @click="setActive('donaciones')">
                    <i class="fa-solid fa-hand-holding-heart me-2"></i> Donaciones
                </router-link>
            </li>
            <li class="nav-item" :class="{ active: activeLink === 'capacitaciones' }">
                <router-link to="/capacitaciones" class="nav-link" @click="setActive('capacitaciones')">
                    <i class="fa-solid fa-book-medical me-2"></i> Capacitaciones
                </router-link>
            </li>
            <li class="nav-item" :class="{ active: activeLink === 'configuracion' }">
                <router-link to="/configuracion" class="nav-link" @click="setActive('configuracion')">
                    <i class="fa-solid fa-cog me-2"></i> Configuración
                </router-link>
            </li>
            <li class="nav-item mt-auto">
                <a href="#" class="nav-link text-danger">
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
        if (path === '/') this.activeLink = 'usuarios';
        else if (path.includes('dashboard')) this.activeLink = 'dashboard';
        else if (path.includes('beneficiarios')) this.activeLink = 'beneficiarios';
        else if (path.includes('voluntarios')) this.activeLink = 'voluntarios';
        else if (path.includes('actividades')) this.activeLink = 'actividades';
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
    padding: 1.5rem 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    justify-content: center;
}

.logo-container {
    display: flex;
    align-items: center;
}

.logo {
    height: 40px;
    margin-right: 10px;
    filter: brightness(0) invert(1); /* Hace que el logo sea blanco si es necesario */
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
