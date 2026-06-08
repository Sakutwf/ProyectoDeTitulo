<template>
    <div class="d-flex">
        <SidebarMenu />

        <div class="content-wrapper">
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="m-0"><i class="fa-solid fa-home me-2"></i>Inicio</h3>
                    <span class="role-chip">{{ roleLabel }}</span>
                </div>
            </div>

            <div class="content">
                <div class="card shadow welcome-card">
                    <div class="card-body text-center">
                        <div class="welcome-icon">
                            <i :class="welcomeIcon"></i>
                        </div>
                        <h2 class="welcome-title">{{ welcomeTitle }}</h2>
                        <p class="welcome-text">
                            {{ welcomeText }}
                        </p>
                        <div class="welcome-actions mt-4">
                            <button class="btn btn-danger me-3" @click="navigatePrimary">
                                <i :class="primaryActionIcon"></i>{{ primaryActionLabel }}
                            </button>
                            <button v-if="canManagePlatform" class="btn btn-outline-secondary" @click="navigateToActividades">
                                <i class="fa-solid fa-chart-line me-2"></i>Ver actividades
                            </button>
                            <button v-else class="btn btn-outline-secondary" @click="navigateToVolunteerActivities">
                                <i class="fa-solid fa-list-check me-2"></i>Actividades activas
                            </button>
                            <button v-if="canSwitchAccess" class="btn btn-outline-secondary ms-3" @click="navigateToAccessSelection">
                                <i class="fa-solid fa-right-left me-2"></i>Cambiar vista
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
        computed: {
            currentUser() {
                return this.$store.getters.authUser;
            },
            canManagePlatform() {
                return this.$store.getters.isAdministratorExperience;
            },
            isVolunteerExperience() {
                return this.$store.getters.isVolunteerExperience || this.$store.getters.isVolunteerOnly;
            },
            canSwitchAccess() {
                return this.$store.getters.requiresAccessSelection;
            },
            welcomeTitle() {
                return this.canManagePlatform
                    ? 'Bienvenido, Administrador'
                    : 'Bienvenido, Voluntario';
            },
            welcomeText() {
                if (this.canManagePlatform) {
                    return 'Has iniciado sesion correctamente en el sistema de gestion. Desde aqui puedes administrar voluntarios, actividades y eventos.';
                }

                return 'Has iniciado sesion correctamente como voluntario. Para obtener tu hoja de vida puedes dirigirte a la seccion Hoja de vida.';
            },
            primaryActionLabel() {
                return this.canManagePlatform ? 'Gestionar usuarios' : 'Ver hoja de vida';
            },
            primaryActionIcon() {
                return this.canManagePlatform
                    ? 'fa-solid fa-users me-2'
                    : 'fa-solid fa-id-card me-2';
            },
            welcomeIcon() {
                return this.canManagePlatform
                    ? 'fa-solid fa-user-shield'
                    : 'fa-solid fa-hand-holding-heart';
            },
            roleLabel() {
                return this.canManagePlatform ? 'Vista de administrador' : 'Perfil de voluntario';
            }
        },
        methods: {
            navigatePrimary() {
                if (this.canManagePlatform) {
                    this.$router.push('/voluntarios');
                    return;
                }

                this.$router.push(`/historial/${this.currentUser.id}`);
            },
            navigateToActividades() {
                this.$router.push('/actividades');
            },
            navigateToVolunteerActivities() {
                this.$router.push({ name: 'volunteer-activities' });
            },
            navigateToAccessSelection() {
                this.$store.dispatch('chooseAccessMode', null);
                this.$router.push({ name: 'access-selection' });
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

.role-chip {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    background: #eef4fb;
    color: #0f2f5f;
    font-weight: 700;
    padding: 0.5rem 0.9rem;
}
</style>
