<template>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="@/assets/LogoHorizontal.svg" alt="Cruz Roja Logo" class="logo">
            </div>
        </div>

        <div class="sidebar-user">
            <strong>{{ currentUser?.nombre || 'Sesion activa' }}</strong>
            <span>{{ roleLabel }}</span>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item" :class="{ active: activeLink === 'inicio' }">
                <router-link to="/inicio" class="nav-link">
                    <i class="fa-solid fa-tachometer-alt me-2"></i> Inicio
                </router-link>
            </li>
            <li v-if="canManagePlatform" class="nav-item" :class="{ active: activeLink === 'voluntarios' }">
                <router-link to="/voluntarios" class="nav-link">
                    <i class="fa-solid fa-users me-2"></i> Voluntarios
                </router-link>
            </li>
            <li v-if="canManagePlatform" class="nav-item" :class="{ active: activeLink === 'actividades' }">
                <router-link to="/actividades" class="nav-link">
                    <i class="fa-solid fa-list me-2"></i> Actividades
                </router-link>
            </li>
            <li v-if="canManagePlatform" class="nav-item" :class="{ active: activeLink === 'eventos' }">
                <router-link to="/eventos" class="nav-link">
                    <i class="fa-solid fa-calendar-check me-2"></i> Eventos
                </router-link>
            </li>
            <li v-if="showVolunteerMenu && historyLink" class="nav-item" :class="{ active: activeLink === 'historial' }">
                <router-link :to="historyLink" class="nav-link">
                    <i class="fa-solid fa-id-card me-2"></i> Ver hoja de vida
                </router-link>
            </li>
            <li v-if="showVolunteerMenu" class="nav-item" :class="{ active: activeLink === 'mis-actividades' }">
                <router-link :to="{ name: 'volunteer-activities' }" class="nav-link">
                    <i class="fa-solid fa-list-check me-2"></i> Actividades
                </router-link>
            </li>
            <li v-if="showVolunteerMenu" class="nav-item" :class="{ active: activeLink === 'boletas' }">
                <router-link :to="{ name: 'volunteer-boletas' }" class="nav-link">
                    <i class="fa-solid fa-receipt me-2"></i> Boletas
                </router-link>
            </li>
            <li v-if="canSwitchAccess" class="nav-item">
                <button type="button" class="nav-link nav-link-button" @click="changeAccess">
                    <i class="fa-solid fa-right-left me-2"></i> Cambiar vista
                </button>
            </li>
            <li class="nav-item mt-auto">
                <button type="button" class="nav-link nav-link-button" @click="logout">
                    <i class="fa-solid fa-sign-out-alt me-2"></i> Cerrar Sesion
                </button>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    name: 'SidebarMenu',
    computed: {
        currentUser() {
            return this.$store.getters.authUser
        },
        canManagePlatform() {
            return this.$store.getters.isAdministratorExperience
        },
        canSwitchAccess() {
            return this.$store.getters.requiresAccessSelection
        },
        showVolunteerMenu() {
            return this.$store.getters.isVolunteerExperience || this.$store.getters.isVolunteerOnly
        },
        historyLink() {
            if (!this.currentUser?.id || !this.currentUser?.voluntario) {
                return null
            }

            return { name: 'HistorialView', params: { id: this.currentUser.id } }
        },
        roleLabel() {
            return this.canManagePlatform ? 'Vista de administrador' : 'Perfil de voluntario'
        },
        activeLink() {
            const path = this.$route.path

            if (path.includes('/voluntarios')) return 'voluntarios'
            if (path.includes('/mis-actividades')) return 'mis-actividades'
            if (path.includes('/mis-boletas')) return 'boletas'
            if (path.includes('/actividades')) return 'actividades'
            if (path.includes('/eventos')) return 'eventos'
            if (path.includes('/historial')) return 'historial'
            return 'inicio'
        }
    },
    methods: {
        changeAccess() {
            this.$store.dispatch('chooseAccessMode', null)
            this.$router.push({ name: 'access-selection' })
        },
        logout() {
            this.$store.dispatch('logout')
            this.$router.push('/login')
        }
    }
}
</script>

<style scoped>
.sidebar {
    width: 250px;
    min-height: 100vh;
    background-color: #e01e1e;
    color: #fff;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
}

.sidebar-header {
    padding: 1.5rem 0.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    justify-content: center;
}

.sidebar-user {
    display: grid;
    gap: 0.2rem;
    padding: 1rem 1rem 0.75rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}

.sidebar-user strong {
    font-size: 0.95rem;
}

.sidebar-user span {
    font-size: 0.82rem;
    color: rgba(255, 255, 255, 0.82);
}

.logo-container {
    display: flex;
    align-items: center;
    width: 100%;
    justify-content: center;
}

.logo {
    width: 90%;
    height: auto;
    max-height: 50px;
}

.sidebar .nav-link {
    color: rgba(255, 255, 255, 0.9);
    padding: 0.75rem 1rem;
    font-weight: 500;
    border-left: 3px solid transparent;
    transition: all 0.2s;
    width: 100%;
    text-align: left;
    background: transparent;
    border-top: none;
    border-right: none;
    border-bottom: none;
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

.nav-link-button {
    cursor: pointer;
}
</style>
