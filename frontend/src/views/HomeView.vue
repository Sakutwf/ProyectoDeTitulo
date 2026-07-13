<template>
  <div class="home-shell">
    <div class="home-content">
      <header class="landing-header">
        <router-link to="/portada" class="landing-brand"><img :src="brandLogo" alt="Cruz Roja"><div><small>Cruz Roja</small><strong>Ven a conocer nuestras novedades</strong></div></router-link>
        <div class="header-actions">
          <router-link v-if="isAuthenticated" :to="profileRoute" class="button button--red"><i class="fa-solid fa-user"></i> Mi perfil</router-link>
          <button v-if="!isAuthenticated" class="button button--red" @click="goToLogin"><i class="fa-solid fa-right-to-bracket"></i> Iniciar sesión</button>
        </div>
      </header>

      <main>
        <section v-if="slides.length" class="carousel" aria-label="Actividades destacadas">
          <div v-for="(slide, index) in slides" v-show="index === activeSlide" :key="slide.id" class="carousel-slide">
            <div class="carousel-images" :class="`carousel-images--${slide.imagenes.length}`">
              <span v-for="image in slide.imagenes" :key="image.id" class="carousel-image-cell"><img :src="image.url_publica" :style="{ objectPosition: `${image.pivot?.posicion_x ?? 50}% ${image.pivot?.posicion_y ?? 50}%`, transform: `scale(${(image.pivot?.zoom ?? 100) / 100})`, transformOrigin: `${image.pivot?.posicion_x ?? 50}% ${image.pivot?.posicion_y ?? 50}%` }" :alt="slide.titulo || 'Actividad de Cruz Roja'"></span>
            </div>
            <div v-if="slide.titulo || slide.bajada" class="carousel-caption" :style="{ '--carousel-title-color': slide.titulo_color || texts.carrusel_texto_color, '--carousel-subtitle-color': slide.bajada_color || texts.carrusel_texto_color, '--carousel-label-color': texts.carrusel_etiqueta_color }"><p>{{ texts.carrusel_etiqueta }}</p><h1>{{ slide.titulo }}</h1><span>{{ slide.bajada }}</span></div>
          </div>
          <button v-if="slides.length > 1" class="carousel-arrow carousel-arrow--left" aria-label="Anterior" @click="previousSlide"><i class="fa-solid fa-chevron-left"></i></button>
          <button v-if="slides.length > 1" class="carousel-arrow carousel-arrow--right" aria-label="Siguiente" @click="nextSlide"><i class="fa-solid fa-chevron-right"></i></button>
          <div v-if="slides.length > 1" class="carousel-dots"><button v-for="(slide, index) in slides" :key="slide.id" :class="{ active: index === activeSlide }" :aria-label="`Ir a diapositiva ${index + 1}`" @click="activeSlide = index"></button></div>
        </section>

        <section class="intro" :style="{ '--news-label-color': texts.novedades_etiqueta_color, '--news-title-color': texts.novedades_titulo_color, '--news-description-color': texts.novedades_descripcion_color }">
          <p>{{ texts.novedades_etiqueta }}</p>
          <h1>{{ texts.novedades_titulo }}</h1>
          <span>{{ texts.novedades_descripcion }}</span>
        </section>

        <section v-if="news.length" class="news-grid">
          <article v-for="(item, index) in news" :key="item.id" class="news-card news-card--completo" :class="{ 'news-card--reverse': index % 2 === 1 }">
            <div v-if="item.archivo_portada?.url_publica" class="news-card__image"><img :src="item.archivo_portada.url_publica" :style="{ objectPosition: `${item.posicion_x ?? 50}% ${item.posicion_y ?? 50}%`, transform: `scale(${(item.zoom ?? 100) / 100})`, transformOrigin: `${item.posicion_x ?? 50}% ${item.posicion_y ?? 50}%` }" :alt="item.titulo"></div>
            <div class="news-card__body">
              <span class="tag">{{ item.actividad?.tipo || 'Actividad' }}</span>
              <h2>{{ item.titulo }}</h2>
              <p v-if="item.resumen">{{ item.resumen }}</p>
              <dl>
                <template v-for="field in item.campos_visibles || []" :key="field">
                  <div v-if="fieldValue(item, field)"><dt><i :class="fieldMeta[field].icon"></i>{{ fieldMeta[field].label }}</dt><dd>{{ fieldValue(item, field) }}</dd></div>
                </template>
              </dl>
              <p v-if="item.contenido" class="news-card__content">{{ item.contenido }}</p>
            </div>
          </article>
        </section>
        <section v-else-if="!loading" class="empty-home"><i class="fa-regular fa-newspaper"></i><h2>Pronto compartiremos nuevas historias</h2><p>La portada todavía no tiene novedades publicadas.</p></section>
        <footer class="landing-footer">
          <div class="footer-logo"><router-link to="/portada" aria-label="Ir a Inicio y novedades"><img :src="brandLogo" alt="Cruz Roja"></router-link></div>
          <div class="footer-brand">
            <div class="footer-info-block">
              <h2>Dirección</h2>
              <a v-if="texts.direccion" :href="locationUrl" target="_blank" rel="noopener" class="footer-address">{{ texts.direccion }}</a>
            </div>
            <div v-if="texts.horario_atencion" class="footer-info-block">
              <h2>Horario de atención</h2>
              <p class="footer-hours">{{ texts.horario_atencion }}</p>
            </div>
          </div>
          <div class="footer-links">
            <h2>Redes sociales y contacto</h2>
            <div class="footer-socials">
              <a v-if="texts.instagram_url" :href="texts.instagram_url" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
              <a v-if="texts.facebook_url" :href="texts.facebook_url" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
              <a v-if="texts.telefono" :href="`tel:${texts.telefono}`" aria-label="Teléfono"><i class="fa-solid fa-phone"></i></a>
            </div>
            <div class="footer-contact-links">
              <a v-if="texts.telefono" :href="`tel:${texts.telefono}`"><i class="fa-solid fa-phone"></i> {{ texts.telefono }}</a>
              <a v-if="texts.correo_contacto" :href="`mailto:${texts.correo_contacto}`"><i class="fa-solid fa-envelope"></i> {{ texts.correo_contacto }}</a>
            </div>
          </div>
          <div class="footer-related">
            <h2>Páginas relacionadas</h2>
            <a v-for="link in texts.enlaces_relacionados || []" :key="link.url" :href="link.url" target="_blank" rel="noopener">{{ link.nombre }} <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
          </div>
        </footer>
      </main>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import logoHorizontal from '@/assets/LogoHorizontal.svg'
import { buildApiUrl } from '../config/api'

export default {
  name: 'HomeView',
  data: () => ({ brandLogo: logoHorizontal, loading: true, slides: [], news: [], texts: { carrusel_etiqueta: 'Historias que nos unen', carrusel_texto_color: '#ffffff', carrusel_etiqueta_color: '#ffffff', novedades_etiqueta: 'Actualidad de nuestra comunidad', novedades_etiqueta_color: '#d72732', novedades_titulo: 'Novedades de Cruz Roja', novedades_titulo_color: '#011e41', novedades_descripcion: 'Conoce las actividades y el impacto de nuestros voluntarios.', novedades_descripcion_color: '#5f6b7c', telefono: '', correo_contacto: '', horario_atencion: '', instagram_url: '', facebook_url: '', direccion: '', ubicacion_url: '', directorio: [], enlaces_relacionados: [] }, activeSlide: 0, timer: null,
    fieldMeta: { objetivo: { label: 'Objetivo', icon: 'fa-solid fa-bullseye' }, fecha: { label: 'Cuándo', icon: 'fa-regular fa-calendar' }, lugar: { label: 'Dónde', icon: 'fa-solid fa-location-dot' }, voluntarios: { label: 'Voluntarios', icon: 'fa-solid fa-people-group' }, personas_ayudadas: { label: 'Beneficiarios', icon: 'fa-solid fa-hand-holding-heart' }, filial: { label: 'Filial', icon: 'fa-solid fa-building' }, tipo: { label: 'Tipo', icon: 'fa-solid fa-tag' } } }),
  computed: {
    isAuthenticated() { return this.$store.getters.isAuthenticated },
    profileRoute() {
      const userId = this.$store.getters.authUser?.id
      return this.$store.getters.isVolunteerExperience && userId ? `/historial/${userId}` : '/inicio'
    },
    locationUrl() { return this.texts.ubicacion_url || `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(this.texts.direccion || '')}` }
  },
  async mounted() {
    try { const { data } = await axios.get(buildApiUrl('portada')); this.texts = { ...this.texts, ...(data.textos || {}) }; this.slides = data.carrusel || []; this.news = data.novedades || []; this.startCarousel() }
    catch (error) { console.error('No fue posible cargar la portada.', error) } finally { this.loading = false }
  },
  beforeUnmount() { window.clearInterval(this.timer) },
  methods: {
    startCarousel() { window.clearInterval(this.timer); if (this.slides.length > 1) this.timer = window.setInterval(this.nextSlide, 5000) },
    nextSlide() { this.activeSlide = (this.activeSlide + 1) % this.slides.length },
    previousSlide() { this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length },
    goToLogin() { this.$router.push({ name: 'login', query: { redirect: '/inicio' } }) },
    fieldValue(item, field) {
      const activity = item.actividad
      if (!activity) return ''
      if (field === 'personas_ayudadas') return item.personas_ayudadas === null || item.personas_ayudadas === undefined ? '' : `${item.personas_ayudadas} personas`
      if (field === 'objetivo') return activity.objetivo
      if (field === 'lugar') return activity.lugar
      if (field === 'tipo') return activity.tipo
      if (field === 'filial') return activity.filial?.nombre
      if (field === 'voluntarios') return `${activity.voluntarios?.length || 0} voluntarios participaron`
      if (field === 'fecha') { const start = this.formatDate(activity.fecha_inicio); const end = this.formatDate(activity.fecha_termino); return end && end !== start ? `${start} al ${end}` : start }
      return ''
    },
    formatDate(value) { if (!value) return ''; return new Intl.DateTimeFormat('es-CL', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(value)) }
  }
}
</script>

<style scoped>
.home-shell{min-height:100vh;background:#f6f7f9;color:#011e41}.home-shell--internal{display:flex}.home-content{flex:1;min-width:0}.landing-header{height:82px;padding:0 2rem;background:#fff;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e7e9ee;position:relative;z-index:5}.landing-brand{display:flex;align-items:center;gap:1rem;color:#011e41;text-decoration:none}.landing-brand img{width:165px}.landing-brand small{display:block;text-transform:uppercase;letter-spacing:.15em;color:#7a8495}.landing-brand strong{font-size:1rem}.header-actions{display:flex;gap:.7rem}.button{border:0;border-radius:999px;padding:.75rem 1.1rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.5rem}.button--red{background:#e01e1e;color:#fff}.button--ghost{background:#fff;border:1px solid #d7dbe2;color:#011e41}.carousel{position:relative;height:clamp(200px,27.5vw,340px);background:#17243a;overflow:hidden}.carousel-slide,.carousel-images{position:absolute;inset:0}.carousel-images{display:grid;gap:3px}.carousel-images--1{grid-template-columns:1fr}.carousel-images--2{grid-template-columns:repeat(2,1fr)}.carousel-images--3{grid-template-columns:repeat(3,1fr)}.carousel-images img{width:100%;height:100%;object-fit:cover;min-width:0}.carousel-slide::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,rgba(1,30,65,.78),rgba(1,30,65,.08) 70%)}.carousel-caption{position:absolute;z-index:2;left:clamp(1.5rem,6vw,6rem);bottom:clamp(2rem,4.5vw,3.5rem);max-width:680px;color:#fff}.carousel-caption p{font-weight:800;text-transform:uppercase;letter-spacing:.16em;color:#ffccd0}.carousel-caption h1{font-size:clamp(1.6rem,3.4vw,3.2rem);font-weight:800;line-height:1;margin:.35rem 0}.carousel-caption span{font-size:clamp(.9rem,1.3vw,1.1rem)}.carousel-arrow{position:absolute;z-index:3;top:50%;transform:translateY(-50%);width:42px;height:42px;border:1px solid rgba(255,255,255,.5);border-radius:50%;background:rgba(1,30,65,.35);color:#fff}.carousel-arrow--left{left:1.2rem}.carousel-arrow--right{right:1.2rem}.carousel-dots{position:absolute;z-index:3;bottom:1rem;left:50%;transform:translateX(-50%);display:flex;gap:.5rem}.carousel-dots button{width:10px;height:10px;padding:0;border:0;border-radius:50%;background:rgba(255,255,255,.55)}.carousel-dots button.active{width:30px;border-radius:10px;background:#fff}.intro{text-align:center;padding:2.8rem 1.5rem 1.4rem}.intro p{margin:0;color:#d72732;font-weight:800;text-transform:uppercase;letter-spacing:.14em}.intro h1{font-size:clamp(2rem,3.2vw,2.8rem);margin:.4rem 0;font-weight:800}.intro span{color:#5f6b7c}.news-grid{padding:.7rem clamp(1rem,4vw,4rem) 3rem;display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:1rem}.news-card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 12px 28px rgba(1,30,65,.08);grid-column:span 2}.news-card--mitad{grid-column:span 3}.news-card--completo{grid-column:span 6;display:grid;grid-template-columns:minmax(260px,38%) 1fr}.news-card__image{height:140px}.news-card--completo .news-card__image{height:100%}.news-card__image img{width:100%;height:100%;object-fit:cover}.news-card__body{padding:1rem}.tag{display:inline-block;background:#ffe9ea;color:#c91f2b;padding:.3rem .65rem;border-radius:99px;font-size:.7rem;font-weight:800;text-transform:uppercase}.news-card h2{font-size:1.25rem;margin:.5rem 0}.news-card p{color:#596579;line-height:1.45;margin-bottom:.45rem}.news-card dl{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.35rem .8rem;margin:.6rem 0 0}.news-card dl div{border-top:1px solid #edf0f3;padding-top:.4rem}.news-card dt{font-size:.68rem;text-transform:uppercase;color:#778195}.news-card dt i{width:19px;color:#e01e1e}.news-card dd{margin:.15rem 0 0;font-size:.85rem;font-weight:700}.empty-home{text-align:center;padding:4rem 1rem}.empty-home i{font-size:3rem;color:#e01e1e}@media(max-width:1000px){.news-card,.news-card--mitad{grid-column:span 3}}@media(max-width:700px){.landing-header{height:auto;padding:1rem}.landing-brand div{display:none}.landing-brand img{width:140px}.carousel{height:230px}.carousel-caption{left:1rem;right:1rem;bottom:2.2rem}.carousel-images--2,.carousel-images--3{grid-template-columns:1fr}.carousel-images--2 img:not(:first-child),.carousel-images--3 img:not(:first-child){display:none}.news-card,.news-card--mitad,.news-card--completo{grid-column:span 6;display:block}.news-card--completo .news-card__image{height:150px}.news-card dl{grid-template-columns:1fr}}
.carousel-image-cell{display:block;min-width:0;height:100%;overflow:hidden}.carousel-image-cell img{transition:transform .2s ease}.news-card__image{overflow:hidden}
.news-card--completo{min-height:190px}.news-card--completo.news-card--reverse{grid-template-columns:1fr minmax(260px,38%)}.news-card--reverse .news-card__image{order:2}.news-card--reverse .news-card__body{order:1}.news-card--completo .news-card__body{display:flex;flex-direction:column;justify-content:center;padding:clamp(1rem,2vw,1.6rem)}@media(max-width:700px){.news-card--completo,.news-card--completo.news-card--reverse{min-height:0;grid-template-columns:1fr}.news-card--reverse .news-card__image,.news-card--reverse .news-card__body{order:initial}}
.landing-footer{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));column-gap:clamp(1.25rem,2.5vw,2.5rem);row-gap:1.15rem;padding:clamp(1.25rem,2.5vw,2.25rem) clamp(1.25rem,3vw,3rem);margin-top:1.25rem;background:#fff;border-top:4px solid #e01e1e;color:#011e41}.landing-footer h2{font-size:.92rem;text-transform:uppercase;letter-spacing:.08em;margin:0 0 .75rem}.footer-logo{grid-column:1/-1}.footer-logo img{display:block;width:220px;max-width:100%}.footer-brand{display:flex;flex-direction:column;align-items:flex-start}.footer-info-block{display:grid;gap:.25rem;margin-bottom:1rem}.footer-info-block h2{margin:0 0 .1rem}.footer-info-block a{color:inherit;text-decoration:none;line-height:1.4}.footer-phone{color:#011e41;text-decoration:none}.footer-links,.footer-related{display:flex;flex-direction:column;align-items:flex-start;width:100%}.footer-related>a{padding:.4rem 0;color:#344054;text-decoration:none;border-bottom:1px solid #e7eaf0;width:100%}.footer-related i,.footer-links i{font-size:.7rem;color:#e01e1e}.footer-socials{display:flex;gap:.5rem;margin:0 0 1rem}.footer-socials a{width:38px;height:38px;border-radius:50%;display:grid;place-items:center;background:#e01e1e;color:#fff;text-decoration:none;font-size:1rem}@media(max-width:950px){.landing-footer{grid-template-columns:repeat(2,minmax(0,1fr))}.footer-related{grid-column:1/-1}}@media(max-width:700px){.landing-footer{grid-template-columns:1fr;gap:1.25rem}.footer-logo,.footer-related{grid-column:auto}.footer-links,.footer-related{width:100%}}
.landing-footer{background:#e01e1e;border-top-color:#b5121b;color:#fff}.landing-footer h2,.landing-footer p,.landing-footer strong,.landing-footer small,.landing-footer a,.footer-phone{color:#fff}.footer-logo img{background:#fff;border-radius:10px;padding:.6rem}.footer-socials a{background:#fff;color:#e01e1e;border:2px solid rgba(255,255,255,.8)}.footer-socials i{color:#e01e1e}.footer-related a{border-bottom-color:rgba(255,255,255,.3)}.footer-links i,.footer-related i{color:#fff}
.footer-location{line-height:1.5}.footer-location>i{margin-right:.35rem}
.landing-footer a:visited{color:#fff}.landing-footer a:hover,.landing-footer a:focus{color:#ffe3e5}.footer-socials a:visited{color:#e01e1e}.footer-socials a:hover,.footer-socials a:focus{color:#b5121b;background:#fff3f4}.footer-related a:hover{text-decoration:underline;text-decoration-color:rgba(255,255,255,.75)}
.carousel-slide::after{background:linear-gradient(180deg,rgba(1,30,65,.55) 0%,rgba(1,30,65,.18) 8%,transparent 22%)}.carousel-caption{text-shadow:0 2px 12px rgba(1,30,65,.7)}
.carousel-caption p{color:var(--carousel-label-color,#fff)!important}.carousel-caption h1{color:var(--carousel-title-color,#fff)!important}.carousel-caption span{color:var(--carousel-subtitle-color,#fff)!important}.intro p{color:var(--news-label-color,#d72732)!important}.intro h1{color:var(--news-title-color,#011e41)!important}.intro span{color:var(--news-description-color,#5f6b7c)!important}
.footer-hours{margin:0;white-space:pre-line;line-height:1.5}
.footer-contact-links{display:grid;width:100%;margin:-.25rem 0 .9rem}.footer-contact-links a{display:flex;align-items:center;gap:.5rem;padding:.3rem 0;color:#fff;text-decoration:none}.footer-contact-links i{width:18px;font-size:.85rem;color:#fff}
.intro{padding:2.8rem 1.5rem 1.4rem;background:#fff}.carousel{background:#e01e1e}.news-grid{background:#e01e1e}.news-card{background:#fff;color:#011e41}.news-card h2{color:#011e41}.news-card p,.news-card dd,.news-card__content{color:#596579}.news-card dt{color:#778195}.news-card dl div{border-top-color:#edf0f3}.news-card dt i{color:#e01e1e}.news-card .tag{background:#ffe9ea;color:#c91f2b}.footer-logo{justify-self:end}@media(max-width:700px){.intro{padding:2.4rem 1rem 1.3rem}.footer-logo{justify-self:end}}
.landing-footer .footer-socials i{color:#e01e1e}
.landing-footer{margin-top:0}
@media(max-width:1100px){.news-card--completo,.news-card--completo.news-card--reverse{grid-column:span 6;display:block;min-height:0}.news-card--completo .news-card__image{height:clamp(150px,32vw,260px);min-height:0}.news-card--reverse .news-card__image,.news-card--reverse .news-card__body{order:initial}}
</style>
