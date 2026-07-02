from pathlib import Path

path = Path(r'frontend/src/views/HomeView.vue')
text = path.read_text(encoding='utf-8')

old_buttons = '''                        <div class="welcome-actions mt-4">
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
'''
new_buttons = '''                        <div class="welcome-actions mt-4">
                            <button class="btn btn-danger me-3" @click="navigatePrimary">
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
                            <button v-if="canSwitchAccess" class="btn btn-outline-secondary ms-3" @click="navigateToAccessSelection">
                                <i class="fa-solid fa-right-left me-2"></i>Cambiar vista
                            </button>
                        </div>
'''
if old_buttons not in text:
    raise SystemExit('No se encontró el bloque de botones de inicio.')
text = text.replace(old_buttons, new_buttons, 1)

old_computed = '''            isVolunteerExperience() {
                return this.$store.getters.isVolunteerExperience || this.$store.getters.isVolunteerOnly;
            },
            canSwitchAccess() {
                return this.$store.getters.requiresAccessSelection;
            },
'''
new_computed = '''            isVolunteerExperience() {
                return this.$store.getters.isVolunteerExperience || this.$store.getters.isVolunteerOnly;
            },
            hasVolunteerProfile() {
                return Boolean(this.currentUser?.voluntario?.id || this.currentUser?.voluntario);
            },
            canSwitchAccess() {
                return this.$store.getters.requiresAccessSelection;
            },
'''
if old_computed not in text:
    raise SystemExit('No se encontró el bloque de computed esperado.')
text = text.replace(old_computed, new_computed, 1)

old_methods = '''            navigateToActividades() {
                this.$router.push('/actividades');
            },
            navigateToVolunteerActivities() {
                this.$router.push({ name: 'volunteer-activities' });
            },
'''
new_methods = '''            navigateToActividades() {
                this.$router.push('/actividades');
            },
            navigateToVolunteerProfile() {
                this.$router.push(`/historial/${this.currentUser.id}`);
            },
            navigateToVolunteerActivities() {
                this.$router.push({ name: 'volunteer-activities' });
            },
'''
if old_methods not in text:
    raise SystemExit('No se encontró el bloque de métodos esperado.')
text = text.replace(old_methods, new_methods, 1)

path.write_text(text, encoding='utf-8')
