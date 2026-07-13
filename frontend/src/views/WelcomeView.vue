<template>
  <div class="welcome-shell">
    <SidebarMenu />
    <main class="welcome-page">
      <header class="welcome-hero">
        <div>
          <p>{{ todayLabel }}</p>
          <h1>{{ greeting }}, {{ firstName }}</h1>
          <span>{{ experienceMessage }}</span>
        </div>
        <router-link to="/portada" class="public-home-link"><i class="fa-solid fa-globe"></i> Ver página pública</router-link>
      </header>

      <section class="profile-summary" aria-label="Resumen de perfil">
        <div class="profile-identity">
          <img v-if="volunteer?.foto_perfil_url" :src="volunteer.foto_perfil_url" :alt="fullName">
          <span v-else class="profile-avatar">{{ initials }}</span>
          <div>
            <small>Mi perfil</small>
            <h2>{{ fullName }}</h2>
            <div class="role-list"><span v-for="role in roleNames" :key="role">{{ role }}</span></div>
          </div>
        </div>

        <div class="profile-details">
          <article v-for="item in profileDetails" :key="item.label">
            <i :class="item.icon"></i>
            <div><small>{{ item.label }}</small><strong>{{ item.value }}</strong></div>
          </article>
        </div>

        <router-link v-if="volunteer" :to="profileRoute" class="profile-button"><i class="fa-regular fa-id-card"></i> Ver mi perfil completo</router-link>
      </section>

      <section class="today-section">
        <div class="section-heading">
          <p>Accesos rápidos</p>
          <h2>¿Qué quieres que hagamos hoy?</h2>
          <span>Escoge una opción para comenzar.</span>
        </div>
        <div class="action-grid">
          <router-link v-for="action in actions" :key="action.to" :to="action.to" class="action-card">
            <span class="action-icon"><i :class="action.icon"></i></span>
            <div><h3>{{ action.title }}</h3><p>{{ action.description }}</p></div>
            <i class="fa-solid fa-arrow-right action-arrow"></i>
          </router-link>
        </div>
      </section>
    </main>
  </div>
</template>

<script>
import SidebarMenu from '../components/SidebarMenu.vue'

export default {
  name: 'WelcomeView',
  components: { SidebarMenu },
  computed: {
    user() { return this.$store.getters.authUser || {} },
    volunteer() { return this.user.voluntario || null },
    isAdministrator() { return this.$store.getters.isAdministratorExperience },
    fullName() {
      const name = [this.volunteer?.nombres, this.volunteer?.apellidos].filter(Boolean).join(' ').trim()
      return name || this.user.name || this.user.username || 'Usuario de Cruz Roja'
    },
    firstName() { return this.volunteer?.nombres?.trim().split(/\s+/)[0] || this.fullName.split(/\s+/)[0] },
    initials() { return this.fullName.split(/\s+/).slice(0, 2).map(part => part[0]).join('').toUpperCase() || 'CR' },
    greeting() {
      const hour = new Date().getHours()
      if (hour < 12) return 'Buenos días'
      if (hour < 20) return 'Buenas tardes'
      return 'Buenas noches'
    },
    todayLabel() { return new Intl.DateTimeFormat('es-CL', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date()) },
    experienceMessage() { return this.isAdministrator ? 'Aquí tienes un resumen y las herramientas para gestionar la filial.' : 'Aquí tienes tu información y los accesos para continuar tu labor voluntaria.' },
    roleNames() {
      const labels = { administrador: 'Administrador', voluntario: 'Voluntario', 'secretario-directiva': 'Secretaría de directiva' }
      return (this.user.roles || []).map(role => labels[role.clave] || role.nombre || role.clave)
    },
    profileRoute() { return `/historial/${this.user.id}` },
    profileDetails() {
      const details = this.volunteer ? [
        { label: 'Registro de filial', value: this.volunteer.registro_filial, icon: 'fa-solid fa-id-badge' },
        { label: 'Filial', value: this.volunteer.filial?.nombre, icon: 'fa-solid fa-building' },
        { label: 'Correo', value: this.volunteer.correo_electronico, icon: 'fa-regular fa-envelope' },
        { label: 'Teléfono', value: this.volunteer.celular, icon: 'fa-solid fa-phone' },
        { label: 'Grupo sanguíneo', value: this.volunteer.grupo_sanguineo, icon: 'fa-solid fa-droplet' },
        { label: 'Incorporación', value: this.formatDate(this.volunteer.fecha_incorporacion), icon: 'fa-regular fa-calendar-check' }
      ] : [
        { label: 'Usuario', value: this.user.username, icon: 'fa-solid fa-user-shield' },
        { label: 'Acceso actual', value: 'Administración', icon: 'fa-solid fa-shield-halved' }
      ]
      return details.filter(item => item.value)
    },
    actions() {
      if (this.isAdministrator) {
        return [
          { to: '/voluntarios', title: 'Gestionar voluntarios', description: 'Consulta, registra y actualiza perfiles.', icon: 'fa-solid fa-users' },
          { to: '/actividades', title: 'Gestionar actividades', description: 'Organiza actividades y participantes.', icon: 'fa-solid fa-list-check' },
          { to: '/documentos', title: 'Preparar documentos', description: 'Crea informes y documentación de actividades.', icon: 'fa-solid fa-folder-open' },
          { to: '/galeria-fotos', title: 'Revisar fotografías', description: 'Administra álbumes y galerías.', icon: 'fa-solid fa-images' },
          { to: '/boletas', title: 'Gestionar boletas', description: 'Revisa comprobantes y rendiciones.', icon: 'fa-solid fa-receipt' },
          { to: '/administrar-portada', title: 'Editar página pública', description: 'Actualiza carrusel, novedades y contacto.', icon: 'fa-solid fa-pen-to-square' }
        ]
      }
      return [
        { to: this.profileRoute, title: 'Ver mi perfil', description: 'Consulta tu información e historial personal.', icon: 'fa-regular fa-id-card' },
        { to: '/mis-actividades', title: 'Mis actividades', description: 'Revisa actividades, fechas y participación.', icon: 'fa-solid fa-list-check' },
        { to: '/mi-galeria', title: 'Mi galería', description: 'Consulta y aporta fotografías de actividades.', icon: 'fa-solid fa-images' },
        { to: '/mis-boletas', title: 'Mis boletas', description: 'Carga y revisa tus comprobantes.', icon: 'fa-solid fa-receipt' }
      ]
    }
  },
  methods: {
    formatDate(value) {
      if (!value) return ''
      const date = new Date(`${value}T00:00:00`)
      return Number.isNaN(date.getTime()) ? value : new Intl.DateTimeFormat('es-CL', { day: 'numeric', month: 'long', year: 'numeric' }).format(date)
    }
  }
}
</script>

<style scoped>
.welcome-shell{display:flex;min-height:100vh;background:#f2f5f8;color:#011e41}.welcome-page{flex:1;min-width:0;padding:clamp(1.5rem,4vw,4rem);background:radial-gradient(circle at 90% 0,rgba(224,30,30,.08),transparent 27%),#f2f5f8}.welcome-hero{display:flex;align-items:center;justify-content:space-between;gap:2rem;margin-bottom:2rem}.welcome-hero p,.section-heading p{margin:0;color:#d72732;font-size:.78rem;font-weight:800;letter-spacing:.13em;text-transform:uppercase}.welcome-hero p{text-transform:capitalize}.welcome-hero h1{margin:.35rem 0;color:#011e41;font-size:clamp(2rem,4vw,3.4rem);font-weight:800}.welcome-hero>div>span,.section-heading>span{color:#637083}.public-home-link{display:inline-flex;align-items:center;gap:.55rem;flex:0 0 auto;padding:.8rem 1.1rem;border:1px solid #d9dfe8;border-radius:999px;background:#fff;color:#173352;text-decoration:none;font-weight:700}.profile-summary{display:grid;grid-template-columns:minmax(250px,.9fr) minmax(360px,1.4fr) auto;align-items:center;gap:clamp(1.3rem,3vw,3rem);padding:clamp(1.3rem,3vw,2.2rem);border:1px solid rgba(1,30,65,.08);border-radius:28px;background:#fff;box-shadow:0 18px 45px rgba(1,30,65,.08)}.profile-identity{display:flex;align-items:center;gap:1rem;min-width:0}.profile-identity>img,.profile-avatar{width:92px;height:92px;flex:0 0 auto;border-radius:24px;object-fit:cover;background:#e01e1e;color:#fff;display:grid;place-items:center;font-size:1.7rem;font-weight:800}.profile-identity small{color:#d72732;font-weight:800;text-transform:uppercase;letter-spacing:.1em}.profile-identity h2{margin:.25rem 0 .5rem;font-size:1.45rem}.role-list{display:flex;flex-wrap:wrap;gap:.35rem}.role-list span{padding:.28rem .55rem;border-radius:999px;background:#fff0f1;color:#c91f2b;font-size:.68rem;font-weight:800}.profile-details{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.9rem 1.2rem}.profile-details article{display:grid;grid-template-columns:36px 1fr;align-items:center;gap:.65rem;min-width:0}.profile-details>article>i{width:36px;height:36px;border-radius:11px;background:#eef2f7;color:#173352;display:grid;place-items:center}.profile-details article div{display:grid;min-width:0}.profile-details small{color:#7a8597;font-size:.7rem;text-transform:uppercase}.profile-details strong{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.86rem}.profile-button{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.75rem 1rem;border-radius:12px;background:#e01e1e;color:#fff;text-decoration:none;font-weight:800;white-space:nowrap}.today-section{margin-top:2.4rem}.section-heading h2{margin:.3rem 0;font-size:clamp(1.65rem,3vw,2.35rem)}.action-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem;margin-top:1.4rem}.action-card{position:relative;display:grid;grid-template-columns:54px 1fr auto;align-items:center;gap:1rem;min-height:130px;padding:1.2rem;border:1px solid #e1e6ed;border-radius:20px;background:#fff;color:#011e41;text-decoration:none;box-shadow:0 10px 25px rgba(1,30,65,.05);transition:.2s}.action-card:hover{transform:translateY(-3px);border-color:#e01e1e;box-shadow:0 16px 32px rgba(1,30,65,.1)}.action-icon{width:54px;height:54px;border-radius:16px;background:#fff0f1;color:#d72732;display:grid;place-items:center;font-size:1.25rem}.action-card h3{margin:0 0 .3rem;font-size:1.05rem}.action-card p{margin:0;color:#68758a;font-size:.82rem;line-height:1.45}.action-arrow{color:#9aa4b2}.action-card:hover .action-arrow{color:#e01e1e}@media(max-width:1200px){.profile-summary{grid-template-columns:1fr 1.3fr}.profile-button{grid-column:1/-1}.action-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:800px){.welcome-hero{align-items:flex-start;flex-direction:column}.profile-summary{grid-template-columns:1fr}.profile-details{grid-template-columns:1fr 1fr}.profile-button{grid-column:auto}.action-grid{grid-template-columns:1fr}}@media(max-width:560px){.welcome-shell{display:block}.welcome-page{padding:1rem}.profile-identity{align-items:flex-start;flex-direction:column}.profile-details{grid-template-columns:1fr}.action-card{grid-template-columns:48px 1fr}.action-arrow{display:none}}
</style>
