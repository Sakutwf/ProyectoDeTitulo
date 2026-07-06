<template>
    <div class="d-flex">
        <SidebarMenu />

        <div class="content-wrapper">
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
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
                            <button class="btn btn-danger" @click="navigatePrimary">
                                <i :class="primaryActionIcon"></i>{{ primaryActionLabel }}
                            </button>
                            <button v-if="canManagePlatform && hasVolunteerProfile" class="btn btn-outline-secondary" @click="navigateToVolunteerProfile">
                                <i class="fa-solid fa-id-card me-2"></i>Ir a mi perfil de voluntario
                            </button>
                            <button v-else-if="canManagePlatform" class="btn btn-outline-secondary" @click="navigateToActividades">
                                <i class="fa-solid fa-chart-line me-2"></i>Ver actividades
                            </button>
                            <button v-else class="btn btn-outline-secondary" @click="navigateToVolunteerActivities">
                                <i class="fa-solid fa-list-check me-2"></i>Actividades activas
                            </button>
                            <button v-if="canSwitchAccess" class="btn btn-outline-secondary" @click="navigateToAccessSelection">
                                <i class="fa-solid fa-right-left me-2"></i>Cambiar vista
                            </button>
                        </div>
                    </div>
                </div>

                <section v-if="isVolunteerExperience" class="volunteer-sections">
                    <article class="volunteer-section-card volunteer-section-card--gallery">
                        <div class="volunteer-section-card__icon">
                            <i class="fa-solid fa-images"></i>
                        </div>
                        <div>
                            <p class="volunteer-section-card__eyebrow"></p>
                            <h3>Galería</h3>
                            <p>
                                Sube fotografías a las actividades en las que estás inscrito y revisa el material compartido.
                            </p>
                        </div>
                        <button type="button" class="btn btn-outline-light volunteer-section-card__action" @click="navigateToVolunteerGallery">
                            Ir a galería
                        </button>
                    </article>

                    <article class="volunteer-section-card volunteer-section-card--receipts">
                        <div class="volunteer-section-card__icon">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div>
                            <p class="volunteer-section-card__eyebrow"></p>
                            <h3>Boletas</h3>
                            <p>
                                Registra y gestiona tus boletas.
                            </p>
                        </div>
                        <button type="button" class="btn btn-outline-light volunteer-section-card__action" @click="navigateToVolunteerBoletas">
                            Ir a boletas
                        </button>
                    </article>
                </section>
            </div>
        </div>
    </div>
</template>

<script>
    import SidebarMenu from '../components/SidebarMenu.vue'

    export default {
        name: 'HomeView',
        components: {
            SidebarMenu
        },
        computed: {
            currentUser() {
                return this.$store.getters.authUser
            },
            canManagePlatform() {
                return this.$store.getters.isAdministratorExperience
            },
            isVolunteerExperience() {
                return this.$store.getters.isVolunteerExperience || this.$store.getters.isVolunteerOnly
            },
            hasVolunteerProfile() {
                return Boolean(this.currentUser?.voluntario?.id || this.currentUser?.voluntario)
            },
            canSwitchAccess() {
                return this.$store.getters.requiresAccessSelection
            },
            welcomeTitle() {
                return this.canManagePlatform
                    ? 'Bienvenido, Administrador'
                    : 'Bienvenido, Voluntario'
            },
            welcomeText() {
                if (this.canManagePlatform) {
                    return 'Has iniciado sesión correctamente en el sistema de gestión. Desde aquí puedes administrar voluntarios y actividades.'
                }

                return 'Desde esta vista puedes entrar a tu hoja de vida, revisar tus actividades y gestionar tu galería y tus boletas.'
            },
            primaryActionLabel() {
                return this.canManagePlatform ? 'Gestionar usuarios' : 'Ver hoja de vida'
            },
            primaryActionIcon() {
                return this.canManagePlatform
                    ? 'fa-solid fa-users me-2'
                    : 'fa-solid fa-id-card me-2'
            },
            welcomeIcon() {
                return this.canManagePlatform
                    ? 'fa-solid fa-user-shield'
                    : 'fa-solid fa-hand-holding-heart'
            },
            roleLabel() {
                return this.canManagePlatform ? 'Vista de administrador' : 'Perfil de voluntario'
            }
        },
        methods: {
            navigatePrimary() {
                if (this.canManagePlatform) {
                    this.$router.push('/voluntarios')
                    return
                }

                this.$router.push(`/historial/${this.currentUser.id}`)
            },
            navigateToActividades() {
                this.$router.push('/actividades')
            },
            navigateToVolunteerProfile() {
                this.$router.push(`/historial/${this.currentUser.id}`)
            },
            navigateToVolunteerActivities() {
                this.$router.push({ name: 'volunteer-activities' })
            },
            navigateToVolunteerGallery() {
                this.$router.push({ name: 'volunteer-gallery' })
            },
            navigateToVolunteerBoletas() {
                this.$router.push({ name: 'volunteer-boletas' })
            },
            navigateToAccessSelection() {
                this.$store.dispatch('chooseAccessMode', null)
                this.$router.push({ name: 'access-selection' })
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
    display: grid;
    gap: 1.5rem;
}

.d-flex {
    display: flex;
}

.welcome-card {
    padding: 2rem;
    margin-top: 0.5rem;
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
    max-width: 700px;
    margin: 0 auto;
}

.welcome-actions {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.85rem;
}

.welcome-actions .btn {
    padding: 0.7rem 1.5rem;
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

.volunteer-sections {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.volunteer-section-card {
    border-radius: 28px;
    padding: 1.5rem;
    color: #fff;
    display: grid;
    gap: 1rem;
    min-height: 260px;
    box-shadow: 0 20px 44px rgba(15, 47, 95, 0.14);
}

.volunteer-section-card--gallery {
    background: linear-gradient(160deg, #0f2f5f 0%, #214f8f 100%);
}

.volunteer-section-card--receipts {
    background: linear-gradient(160deg, #c1121f 0%, #ff4a4a 100%);
}

.volunteer-section-card__icon {
    width: 64px;
    height: 64px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.16);
    font-size: 1.55rem;
}

.volunteer-section-card__eyebrow {
    margin: 0 0 0.45rem;
    color: rgba(255, 255, 255, 0.76);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.78rem;
    font-weight: 800;
}

.volunteer-section-card h3 {
    margin: 0 0 0.65rem;
    font-size: 1.8rem;
    font-weight: 900;
}

.volunteer-section-card p {
    margin: 0;
    color: rgba(255, 255, 255, 0.88);
    line-height: 1.55;
}

.volunteer-section-card__action {
    justify-self: start;
    border-width: 1.5px;
    border-radius: 999px;
    font-weight: 700;
    color: #fff;
}

.volunteer-section-card__action:hover,
.volunteer-section-card__action:focus,
.volunteer-section-card__action:active {
    color: #fff;
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.88);
}

@media (max-width: 991.98px) {
    .volunteer-sections {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px) {
    .content-header {
        padding: 1rem 1rem 0.9rem;
        margin-bottom: 1rem;
    }

    .content {
        padding: 0 1rem 1rem;
    }

    .welcome-card {
        padding: 1.4rem 1rem;
    }

    .welcome-icon {
        font-size: 3.2rem;
    }

    .welcome-title {
        font-size: 1.8rem;
    }
}
</style>
