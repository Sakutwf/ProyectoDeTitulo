<template>
    <div class="sidebar" :class="{ 'sidebar--compact': displayCompact, 'sidebar--collapsible': collapsible }">
        <div class="sidebar-header">
            <router-link to="/portada" class="logo-container" aria-label="Ir a Inicio y novedades">
                <img :src="logoSrc" alt="Cruz Roja Logo" class="logo">
            </router-link>
        </div>

        <div v-if="!displayCompact" class="sidebar-user-row">
            <div class="sidebar-user">
                <span>{{ roleLabel }}</span>
            </div>

            <button
                v-if="collapsible"
                type="button"
                class="sidebar-toggle"
                :title="displayCompact ? 'Extender barra lateral' : 'Contraer barra lateral'"
                :aria-label="displayCompact ? 'Extender barra lateral' : 'Contraer barra lateral'"
                @click="toggleSidebar"
            >
                <i class="fa-solid" :class="displayCompact ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
            </button>
        </div>

        <div v-else-if="collapsible" class="sidebar-toggle-row sidebar-toggle-row--compact">
            <button
                type="button"
                class="sidebar-toggle"
                :title="displayCompact ? 'Extender barra lateral' : 'Contraer barra lateral'"
                :aria-label="displayCompact ? 'Extender barra lateral' : 'Contraer barra lateral'"
                @click="toggleSidebar"
            >
                <i class="fa-solid" :class="displayCompact ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
            </button>
        </div>

        <ul ref="navigation" class="nav flex-column" @wheel="scrollNavigation">
            <li class="nav-item nav-item--inicio" :class="{ active: activeLink === 'inicio' }">
                <router-link to="/inicio" class="nav-link nav-link--inicio" :title="displayCompact ? 'Inicio' : null" :aria-label="displayCompact ? 'Inicio' : null">
                    <span class="nav-link__icon">
                        <i class="fa-solid fa-house"></i>
                    </span>
                    <span class="nav-link__label">Inicio</span>
                </router-link>
            </li>
            <li v-if="canManagePlatform" class="nav-item" :class="{ active: activeLink === 'voluntarios' }">
                <router-link to="/voluntarios" class="nav-link" :title="displayCompact ? 'Voluntarios' : null" :aria-label="displayCompact ? 'Voluntarios' : null">
                    <span class="nav-link__icon">
                        <i class="fa-solid fa-users"></i>
                    </span>
                    <span class="nav-link__label">Voluntarios</span>
                </router-link>
            </li>
            <li v-if="canManagePlatform" class="nav-item" :class="{ active: activeLink === 'actividades' }">
                <router-link to="/actividades" class="nav-link" :title="displayCompact ? 'Actividades' : null" :aria-label="displayCompact ? 'Actividades' : null">
                    <span class="nav-link__icon">
                        <i class="fa-solid fa-list"></i>
                    </span>
                    <span class="nav-link__label">Actividades</span>
                </router-link>
            </li>
            <li v-if="canManagePlatform" class="nav-item" :class="{ active: activeLink === 'documentos' }">
                <router-link to="/documentos" class="nav-link" :title="displayCompact ? 'Documentos' : null" :aria-label="displayCompact ? 'Documentos' : null">
                    <span class="nav-link__icon">
                        <i class="fa-solid fa-folder-open"></i>
                    </span>
                    <span class="nav-link__label">Documentos</span>
                </router-link>
            </li>
            <li v-if="canManagePlatform" class="nav-item" :class="{ active: activeLink === 'boletas-gestion' }">
                <router-link to="/boletas" class="nav-link" :title="displayCompact ? 'Boletas' : null" :aria-label="displayCompact ? 'Boletas' : null">
                    <span class="nav-link__icon">
                        <i class="fa-solid fa-receipt"></i>
                    </span>
                    <span class="nav-link__label">Boletas</span>
                </router-link>
            </li>
            <li v-if="canManagePlatform" class="nav-item" :class="{ active: activeLink === 'galeria-fotos' }">
                <router-link to="/galeria-fotos" class="nav-link" :title="displayCompact ? 'Galería de fotos' : null" :aria-label="displayCompact ? 'Galería de fotos' : null">
                    <span class="nav-link__icon">
                        <i class="fa-solid fa-images"></i>
                    </span>
                    <span class="nav-link__label">Galería de fotos</span>
                </router-link>
            </li>
            <li v-if="canManagePlatform" class="nav-item" :class="{ active: activeLink === 'solicitudes-hoja-vida' }">
                <router-link to="/solicitudes-hoja-vida" class="nav-link" :title="displayCompact ? 'Solicitudes de hoja de vida' : null">
                    <span class="nav-link__icon"><i class="fa-solid fa-user-check"></i></span>
                    <span class="nav-link__label">Solicitudes</span>
                </router-link>
            </li>
            <li v-if="isAdministrator" class="nav-item" :class="{ active: activeLink === 'administrar-portada' }">
                <router-link to="/administrar-portada" class="nav-link" :title="displayCompact ? 'Administrar portada' : null">
                    <span class="nav-link__icon"><i class="fa-solid fa-newspaper"></i></span>
                    <span class="nav-link__label">Administrar portada</span>
                </router-link>
            </li>
            <li v-if="isVolunteerProfileAvailable" class="nav-item" :class="{ active: activeLink === 'mi-perfil' }">
                <router-link :to="volunteerProfileRoute" class="nav-link" :title="displayCompact ? 'Mi perfil' : null" :aria-label="displayCompact ? 'Mi perfil' : null">
                    <span class="nav-link__icon">
                        <i class="fa-solid fa-id-card"></i>
                    </span>
                    <span class="nav-link__label">Mi perfil</span>
                </router-link>
            </li>
            <li v-if="isVolunteerProfileAvailable" class="nav-item" :class="{ active: activeLink === 'mis-actividades' }">
                <router-link to="/mis-actividades" class="nav-link" :title="displayCompact ? 'Mis actividades' : null" :aria-label="displayCompact ? 'Mis actividades' : null">
                    <span class="nav-link__icon">
                        <i class="fa-solid fa-list-check"></i>
                    </span>
                    <span class="nav-link__label">Mis actividades</span>
                </router-link>
            </li>
            <li v-if="isVolunteerProfileAvailable" class="nav-item" :class="{ active: activeLink === 'mi-galeria' }">
                <router-link to="/mi-galeria" class="nav-link" :title="displayCompact ? 'Mi galeria' : null" :aria-label="displayCompact ? 'Mi galeria' : null">
                    <span class="nav-link__icon">
                        <i class="fa-solid fa-images"></i>
                    </span>
                    <span class="nav-link__label">Galería</span>
                </router-link>
            </li>
            <li v-if="isVolunteerProfileAvailable" class="nav-item" :class="{ active: activeLink === 'mis-boletas' }">
                <router-link to="/mis-boletas" class="nav-link" :title="displayCompact ? 'Mis boletas' : null" :aria-label="displayCompact ? 'Mis boletas' : null">
                    <span class="nav-link__icon">
                        <i class="fa-solid fa-receipt"></i>
                    </span>
                    <span class="nav-link__label">Boletas</span>
                </router-link>
            </li>
            <li v-if="canSwitchAccess" class="nav-item">
                <button type="button" class="nav-link nav-link-button" :title="displayCompact ? 'Cambiar vista' : null" :aria-label="displayCompact ? 'Cambiar vista' : null" @click="changeAccess">
                    <span class="nav-link__icon">
                        <i class="fa-solid fa-right-left"></i>
                    </span>
                    <span class="nav-link__label">Cambiar vista</span>
                </button>
            </li>
            <li class="nav-item mt-auto">
                <button type="button" class="nav-link nav-link-button" :title="displayCompact ? 'Cerrar sesion' : null" :aria-label="displayCompact ? 'Cerrar sesion' : null" @click="logout">
                    <span class="nav-link__icon">
                        <i class="fa-solid fa-sign-out-alt"></i>
                    </span>
                    <span class="nav-link__label">Cerrar Sesion</span>
                </button>
            </li>
        </ul>
    </div>

</template>

<script>
import logoHorizontal from '@/assets/LogoHorizontal.svg'
import logoVertical from '@/assets/LogoVertical.svg'

export default {
    name: 'SidebarMenu',
    props: {
        compact: {
            type: Boolean,
            default: false
        },
        collapsible: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            isCollapsed: this.compact
        }
    },
    computed: {
        currentUser() {
            return this.$store.getters.authUser
        },
        displayCompact() {
            return this.collapsible ? this.isCollapsed : this.compact
        },
        logoSrc() {
            return this.displayCompact ? logoVertical : logoHorizontal
        },
        canManagePlatform() {
            return this.$store.getters.isAdministratorExperience
        },
        isAdministrator() {
            return this.canManagePlatform && this.$store.getters.hasRole('administrador')
        },
        canSwitchAccess() {
            return this.$store.getters.requiresAccessSelection
        },
        isVolunteerProfileAvailable() {
            return Boolean(this.currentUser?.id) && !this.canManagePlatform
        },
        volunteerProfileRoute() {
            return this.currentUser?.id ? `/historial/${this.currentUser.id}` : '/inicio'
        },
        roleLabel() {
            return this.canManagePlatform ? 'Administrador' : 'Perfil de voluntario'
        },
        activeLink() {
            const path = this.$route.path

            if (path.includes('/voluntarios')) return 'voluntarios'
            if (path.includes('/actividades')) return 'actividades'
            if (path.includes('/documentos')) return 'documentos'
            if (path.includes('/mis-boletas')) return 'mis-boletas'
            if (path.includes('/solicitudes-hoja-vida')) return 'solicitudes-hoja-vida'
            if (path.includes('/boletas')) return 'boletas-gestion'
            if (path.includes('/galeria-fotos')) return 'galeria-fotos'
            if (path.includes('/administrar-portada')) return 'administrar-portada'
            if (path.includes('/mis-actividades')) return 'mis-actividades'
            if (path.includes('/mi-galeria')) return 'mi-galeria'
            if (path.includes('/historial')) return 'mi-perfil'
            return 'inicio'
        }
    },
    methods: {
        scrollNavigation(event) {
            const navigation = this.$refs.navigation

            if (window.innerWidth >= 1200 || !navigation || navigation.scrollWidth <= navigation.clientWidth) {
                return
            }

            const movement = Math.abs(event.deltaX) > Math.abs(event.deltaY) ? event.deltaX : event.deltaY
            if (!movement) return

            event.preventDefault()
            navigation.scrollLeft += movement
        },
        toggleSidebar() {
            if (!this.collapsible) {
                return
            }

            this.isCollapsed = !this.isCollapsed
        },
        changeAccess() {
            this.$store.dispatch('chooseAccessMode', null)
            this.$router.push({ name: 'access-selection' })
        },
        async logout() {
            try {
                await this.$store.dispatch('logout')
            } finally {
                this.$router.replace('/portada')
            }
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

.sidebar--compact {
    width: 84px;
}

.sidebar-header {
    padding: 1rem 1rem 0.9rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    justify-content: center;
}

.sidebar-user-row,
.sidebar-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 1rem 1rem 0.75rem;
    min-height: 72px;
    box-sizing: border-box;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}

.sidebar-toggle {
    width: 36px;
    height: 36px;
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    align-self: flex-end;
}

.sidebar-user {
    display: grid;
    gap: 0.2rem;
    min-width: 0;
}

.sidebar-user span {
    font-size: 0.88rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.92);
}

.logo-container {
    display: flex;
    align-items: center;
    width: 100%;
    justify-content: center;
    min-height: 50px;
}

.logo {
    width: 90%;
    height: auto;
    max-height: 50px;
}

.sidebar--compact .sidebar-header {
    padding: 1rem 1rem 0.9rem;
}

.sidebar--compact .logo {
    width: 54px;
    max-height: 54px;
    object-fit: contain;
}

.sidebar .nav-link {
    color: rgba(255, 255, 255, 0.9);
    padding: 0.75rem 1rem;
    min-height: 52px;
    font-weight: 500;
    border-left: 3px solid transparent;
    transition: all 0.2s;
    width: 100%;
    box-sizing: border-box;
    text-align: left;
    background: transparent;
    border-top: none;
    border-right: none;
    border-bottom: none;
    display: grid;
    grid-template-columns: 1.25rem minmax(0, 1fr);
    align-items: center;
    column-gap: 0.75rem;
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

.nav-link__icon {
    width: 1.25rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.nav-link__label {
    white-space: nowrap;
    overflow: hidden;
}

.sidebar--compact .nav {
    padding-top: 0.35rem;
}

.sidebar--compact .nav-link {
    padding: 0.75rem 1rem;
}

.sidebar--compact .nav-link__label {
    opacity: 0;
    width: 0;
    pointer-events: none;
}

.sidebar--compact .sidebar-toggle-row--compact {
    padding: 1rem 1rem 0.75rem;
    justify-content: flex-end;
}



@media (max-width: 1199.98px) {
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: auto;
        width: 100%;
        min-height: 0;
        z-index: 1200;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
        border-top-left-radius: 0;
        border-top-right-radius: 0;
        border-bottom-left-radius: 18px;
        border-bottom-right-radius: 18px;
        padding: 0.4rem 0.65rem 0.55rem;
    }

    .sidebar--compact {
        width: 100%;
    }

    .sidebar-header,
    .sidebar-user-row,
    .sidebar-toggle-row {
        display: none;
    }

    .sidebar .nav {
        display: flex;
        flex-direction: row !important;
        flex-wrap: nowrap;
        align-items: stretch;
        gap: 0.45rem;
        padding: 0 0 0.35rem;
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        overscroll-behavior-x: contain;
        scroll-behavior: smooth;
        scroll-snap-type: x proximity;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.72) rgba(255, 255, 255, 0.16);
        -webkit-overflow-scrolling: touch;
    }

    .sidebar .nav::-webkit-scrollbar {
        height: 6px;
    }

    .sidebar .nav::-webkit-scrollbar-track {
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
    }

    .sidebar .nav::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.72);
    }

    .sidebar .nav-item {
        flex: 0 0 clamp(5.6rem, 11vw, 7.4rem);
        min-width: 0;
        margin-top: 0 !important;
        scroll-snap-align: start;
    }

    .sidebar .nav-link {
        min-width: 0;
        width: 100%;
        height: 4.6rem;
        min-height: 4.6rem;
        padding: 0.62rem 0.45rem;
        border-left: none;
        border-top: 3px solid transparent;
        border-radius: 14px;
        grid-template-columns: 1fr;
        justify-items: center;
        align-content: center;
        row-gap: 0.35rem;
        text-align: center;
        white-space: normal;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-item.active .nav-link {
        border-left-color: transparent;
        border-top-color: #ffffff;
    }

    .nav-link__icon {
        width: auto;
        font-size: 1rem;
    }

    .nav-link__label {
        width: auto;
        font-size: 0.78rem;
        line-height: 1.1;
        white-space: normal;
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    :global(.content-wrapper) {
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
        padding-top: 7rem !important;
        padding-bottom: 0;
    }

    :global(.content-header) {
        margin-bottom: 1rem;
    }
}

@media (max-width: 575.98px) {
    .nav-item--inicio {
        display: none;
    }
}
</style>









