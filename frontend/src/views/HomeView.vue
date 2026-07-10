<template>
  <div class="home-shell" :class="{ 'home-shell--internal': isAuthenticated }">
    <SidebarMenu v-if="isAuthenticated" />

    <div class="home-content">
      <header class="landing-header">
        <router-link to="/inicio" class="landing-brand">
          <img :src="brandLogo" alt="Cruz Roja" class="landing-brand__logo">
          <div>
            <span class="landing-brand__eyebrow">Cruz Roja</span>
            <strong>Inicio y novedades</strong>
          </div>
        </router-link>

        <div class="landing-header__actions">
          <button
            v-if="!isAuthenticated"
            type="button"
            class="header-login-button"
            @click="goToLogin"
          >
            <i class="fa-solid fa-right-to-bracket"></i>
            Iniciar sesión
          </button>

          <template v-else>
            <button
              v-if="canSwitchAccess"
              type="button"
              class="header-ghost-button"
              @click="navigateToAccessSelection"
            >
              <i class="fa-solid fa-right-left"></i>
              Cambiar vista
            </button>
            <button type="button" class="header-login-button" @click="navigatePrimary">
              <i :class="primaryActionIcon"></i>
              {{ primaryActionLabel }}
            </button>
          </template>
        </div>
      </header>

      <main class="landing-main">
        <section class="hero-section">
          <div class="hero-copy">
            <p class="section-kicker">Novedades de actividades y operativos</p>
            <h1>
              Una portada lista para mostrar
              <span>impacto, fotografías y resultados</span>
            </h1>
            <p class="hero-text">
              Esta vista funciona como inicio del sistema y como vitrina de novedades. Puedes reemplazar las imágenes,
              objetivos, beneficiarios y descripciones de cada bloque para destacar las actividades más recientes de la filial.
            </p>

            <div class="hero-actions">
              <button type="button" class="cta-primary" @click="navigatePrimary">
                <i :class="primaryActionIcon"></i>
                {{ primaryActionLabel }}
              </button>
              <button type="button" class="cta-secondary" @click="scrollToSection('novedades-grid')">
                <i class="fa-solid fa-arrow-down"></i>
                Ver novedades
              </button>
            </div>

            <div class="hero-stats">
              <article v-for="stat in impactStats" :key="stat.label" class="hero-stat-card">
                <strong>{{ stat.value }}</strong>
                <span>{{ stat.label }}</span>
                <small>{{ stat.description }}</small>
              </article>
            </div>
          </div>

          <aside class="hero-spotlight">
            <p class="hero-spotlight__eyebrow">Actividad destacada</p>
            <h2>{{ featuredActivity.title }}</h2>
            <p class="hero-spotlight__text">{{ featuredActivity.description }}</p>

            <div class="hero-spotlight__meta">
              <div>
                <span>Objetivo</span>
                <strong>{{ featuredActivity.objective }}</strong>
              </div>
              <div>
                <span>Beneficiarios</span>
                <strong>{{ featuredActivity.beneficiaries }}</strong>
              </div>
            </div>

            <div class="media-frame media-frame--hero" :class="{ 'media-frame--placeholder': !hasImage(featuredActivity.image) }">
              <img v-if="hasImage(featuredActivity.image)" :src="featuredActivity.image" :alt="featuredActivity.title">
              <div v-else class="media-frame__placeholder">
                <i class="fa-solid fa-image"></i>
                <span>Espacio para fotografía principal</span>
                <small>Reemplaza la propiedad `image` en `featuredActivity`.</small>
              </div>
            </div>
          </aside>
        </section>

        <section id="novedades-grid" class="news-grid-section">
          <div class="section-heading">
            <div>
              <p class="section-kicker">Bloques editables</p>
              <h2>Novedades recientes</h2>
            </div>
            <p>
              Cada tarjeta admite una imagen, una breve descripción y datos concretos de la actividad. La grilla se adapta sola
              cuando agregues o cambies fotografías.
            </p>
          </div>

          <div class="news-grid">
            <article v-for="item in newsCards" :key="item.title" class="news-card">
              <div class="media-frame" :class="{ 'media-frame--placeholder': !hasImage(item.image) }">
                <img v-if="hasImage(item.image)" :src="item.image" :alt="item.title">
                <div v-else class="media-frame__placeholder">
                  <i class="fa-solid fa-camera-retro"></i>
                  <span>{{ item.placeholderLabel }}</span>
                  <small>Inserta una imagen y se ajustará automáticamente.</small>
                </div>
              </div>

              <div class="news-card__body">
                <span class="news-card__tag">{{ item.tag }}</span>
                <h3>{{ item.title }}</h3>
                <p>{{ item.description }}</p>
                <ul class="news-card__facts">
                  <li><strong>Objetivo:</strong> {{ item.objective }}</li>
                  <li><strong>Beneficiarios:</strong> {{ item.beneficiaries }}</li>
                </ul>
              </div>
            </article>
          </div>
        </section>

        <section class="story-section">
          <div class="story-section__copy">
            <p class="section-kicker">Espacio para relato</p>
            <h2>Un bloque amplio para contar el contexto de una actividad</h2>
            <p>
              Aquí puedes dejar una narración más completa: por qué se realizó el operativo, qué se coordinó con la filial y
              qué resultados se obtuvieron. Está pensado para uno o dos párrafos más extensos sin que la página pierda orden.
            </p>
            <p>
              También puedes usar este espacio para destacar cifras, llamados a participar o resúmenes de campañas estacionales.
            </p>
          </div>

          <div class="story-gallery">
            <div
              v-for="item in storyGallery"
              :key="item.title"
              class="story-gallery__item"
              :class="{ 'story-gallery__item--featured': item.featured }"
            >
              <div class="media-frame" :class="{ 'media-frame--placeholder': !hasImage(item.image) }">
                <img v-if="hasImage(item.image)" :src="item.image" :alt="item.title">
                <div v-else class="media-frame__placeholder">
                  <i class="fa-solid fa-image"></i>
                  <span>{{ item.title }}</span>
                  <small>{{ item.caption }}</small>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="impact-section">
          <div class="impact-section__panel">
            <p class="section-kicker">Cierre de portada</p>
            <h2>Una última franja para cerrar con un mensaje institucional</h2>
            <p>
              Puedes usar este bloque para resumir el compromiso de la filial, invitar a revisar más actividades o dejar una
              frase institucional antes del acceso al sistema.
            </p>
          </div>

          <div class="impact-section__actions">
            <button type="button" class="cta-primary" @click="navigatePrimary">
              <i :class="primaryActionIcon"></i>
              {{ primaryActionLabel }}
            </button>
            <button v-if="!isAuthenticated" type="button" class="cta-secondary" @click="goToLogin">
              <i class="fa-solid fa-user-group"></i>
              Acceso voluntarios
            </button>
          </div>
        </section>
      </main>
    </div>
  </div>
</template>

<script>
import SidebarMenu from '../components/SidebarMenu.vue'
import logoHorizontal from '@/assets/LogoHorizontal.svg'

export default {
  name: 'HomeView',
  components: {
    SidebarMenu
  },
  data() {
    return {
      brandLogo: logoHorizontal,
      impactStats: [
        {
          value: '03',
          label: 'secciones principales',
          description: 'Bloques listos para novedades, relato e impacto.'
        },
        {
          value: '06',
          label: 'espacios de imagen',
          description: 'Marcos preparados para reemplazar por fotos reales.'
        },
        {
          value: '02',
          label: 'zonas de texto amplio',
          description: 'Espacios para contexto, objetivo y resultados.'
        }
      ],
      featuredActivity: {
        title: 'Operativo de invierno y apoyo territorial',
        description: 'Usa esta tarjeta principal para destacar una actividad con fotografía de portada, objetivo general y una cifra visible de impacto.',
        objective: 'Describe aquí el propósito principal de la actividad.',
        beneficiaries: 'Ejemplo: 120 personas beneficiadas',
        image: ''
      },
      newsCards: [
        {
          tag: 'Campaña destacada',
          title: 'Operativo comunitario en terreno',
          description: 'Espacio breve para resumir qué se hizo, quién participó y por qué fue importante para la comunidad.',
          objective: 'Reemplaza este texto con el objetivo puntual de la actividad.',
          beneficiaries: 'Ejemplo: 45 beneficiarios directos',
          image: '',
          placeholderLabel: 'Fotografía de actividad 1'
        },
        {
          tag: 'Voluntariado',
          title: 'Jornada de apoyo y acompañamiento',
          description: 'Otro bloque pensado para mostrar una segunda actividad, con texto corto y una imagen principal que se recorta automáticamente.',
          objective: 'Explica aquí el propósito operativo o formativo.',
          beneficiaries: 'Ejemplo: 28 familias acompañadas',
          image: '',
          placeholderLabel: 'Fotografía de actividad 2'
        },
        {
          tag: 'Territorio',
          title: 'Acción focalizada por filial',
          description: 'Puedes usar esta tarjeta para una tercera novedad, una campaña estacional o un resumen de resultados recientes.',
          objective: 'Resume el objetivo institucional o comunitario.',
          beneficiaries: 'Ejemplo: 3 sectores cubiertos',
          image: '',
          placeholderLabel: 'Fotografía de actividad 3'
        }
      ],
      storyGallery: [
        {
          title: 'Imagen secundaria A',
          caption: 'Ideal para una escena de contexto o preparación.',
          image: '',
          featured: true
        },
        {
          title: 'Imagen secundaria B',
          caption: 'Puedes mostrar beneficiarios, equipos o insumos.',
          image: '',
          featured: false
        },
        {
          title: 'Imagen secundaria C',
          caption: 'También sirve para cerrar la historia con detalle visual.',
          image: '',
          featured: false
        }
      ]
    }
  },
  computed: {
    currentUser() {
      return this.$store.getters.authUser
    },
    isAuthenticated() {
      return this.$store.getters.isAuthenticated
    },
    canManagePlatform() {
      return this.$store.getters.isAdministratorExperience
    },
    isVolunteerExperience() {
      return this.$store.getters.isVolunteerExperience || this.$store.getters.isVolunteerOnly
    },
    canSwitchAccess() {
      return this.$store.getters.requiresAccessSelection
    },
    primaryActionLabel() {
      if (!this.isAuthenticated) {
        return 'Iniciar sesión'
      }

      return this.canManagePlatform ? 'Ir a voluntarios' : 'Ir a mi perfil'
    },
    primaryActionIcon() {
      if (!this.isAuthenticated) {
        return 'fa-solid fa-right-to-bracket'
      }

      return this.canManagePlatform ? 'fa-solid fa-users' : 'fa-solid fa-id-card'
    }
  },
  methods: {
    hasImage(value) {
      return Boolean(String(value || '').trim())
    },
    goToLogin() {
      this.$router.push({ name: 'login', query: { redirect: '/inicio' } })
    },
    navigatePrimary() {
      if (!this.isAuthenticated) {
        this.goToLogin()
        return
      }

      if (this.canManagePlatform) {
        this.$router.push('/voluntarios')
        return
      }

      this.$router.push(`/historial/${this.currentUser.id}`)
    },
    navigateToAccessSelection() {
      this.$store.dispatch('chooseAccessMode', null)
      this.$router.push({ name: 'access-selection' })
    },
    scrollToSection(sectionId) {
      if (typeof document === 'undefined') {
        return
      }

      document.getElementById(sectionId)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  }
}
</script>

<style scoped>
.home-shell {
  min-height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(245, 51, 63, 0.14), transparent 26%),
    linear-gradient(180deg, #ffffff 0%, #f7f8fb 48%, #ededed 100%);
}

.home-shell--internal {
  display: flex;
  align-items: stretch;
}

.home-content {
  flex: 1;
  min-width: 0;
}

.landing-header {
  position: sticky;
  top: 0;
  z-index: 20;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(14px);
  border-bottom: 1px solid rgba(1, 30, 65, 0.08);
}

.landing-brand {
  display: inline-flex;
  align-items: center;
  gap: 0.95rem;
  text-decoration: none;
  color: #011e41;
}

.landing-brand__logo {
  width: 168px;
  max-width: 38vw;
  height: auto;
}

.landing-brand__eyebrow {
  display: block;
  font-family: 'Open Sans', sans-serif;
  font-size: 0.72rem;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #6d7a8f;
  margin-bottom: 0.18rem;
}

.landing-brand strong {
  display: block;
  font-family: 'Montserrat', sans-serif;
  font-size: 1.05rem;
  font-weight: 800;
  color: #011e41;
}

.landing-header__actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.header-login-button,
.header-ghost-button,
.cta-primary,
.cta-secondary {
  border: none;
  border-radius: 999px;
  font-family: 'Montserrat', sans-serif;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.55rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, color 0.2s ease;
}

.header-login-button,
.cta-primary {
  background: #f5333f;
  color: #ffffff;
  box-shadow: 0 14px 28px rgba(245, 51, 63, 0.22);
}

.header-login-button:hover,
.header-login-button:focus,
.cta-primary:hover,
.cta-primary:focus {
  transform: translateY(-1px);
  background: #de2a36;
  color: #ffffff;
}

.header-ghost-button,
.cta-secondary {
  background: #ffffff;
  color: #011e41;
  border: 1px solid rgba(1, 30, 65, 0.15);
}

.header-ghost-button:hover,
.header-ghost-button:focus,
.cta-secondary:hover,
.cta-secondary:focus {
  transform: translateY(-1px);
  color: #011e41;
  box-shadow: 0 12px 24px rgba(1, 30, 65, 0.1);
}

.header-login-button,
.header-ghost-button {
  min-height: 46px;
  padding: 0 1.1rem;
}

.cta-primary,
.cta-secondary {
  min-height: 52px;
  padding: 0 1.35rem;
}

.landing-main {
  padding: 1.5rem;
  display: grid;
  gap: 1.5rem;
}

.hero-section,
.story-section,
.impact-section {
  display: grid;
  gap: 1.25rem;
}

.hero-section {
  grid-template-columns: minmax(0, 1.4fr) minmax(320px, 0.9fr);
  align-items: stretch;
}

.hero-copy,
.hero-spotlight,
.news-card,
.story-section__copy,
.impact-section__panel {
  background: #ffffff;
  border: 1px solid rgba(1, 30, 65, 0.08);
  border-radius: 32px;
  box-shadow: 0 22px 46px rgba(1, 30, 65, 0.08);
}

.hero-copy {
  padding: clamp(1.5rem, 2vw, 2.4rem);
  position: relative;
  overflow: hidden;
}

.hero-copy::after {
  content: '';
  position: absolute;
  inset: auto -120px -120px auto;
  width: 280px;
  height: 280px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(245, 51, 63, 0.16), transparent 68%);
}

.section-kicker,
.hero-spotlight__eyebrow {
  margin: 0 0 0.75rem;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #f5333f;
}

.hero-copy h1 {
  max-width: 13ch;
  margin: 0;
  font-family: 'Montserrat', sans-serif;
  font-size: clamp(2.6rem, 5vw, 4.6rem);
  line-height: 0.94;
  font-weight: 800;
  color: #011e41;
}

.hero-copy h1 span {
  display: block;
  color: #f5333f;
}

.hero-text,
.section-heading p,
.news-card__body p,
.story-section__copy p,
.impact-section__panel p,
.hero-spotlight__text,
.hero-stat-card small,
.media-frame__placeholder small,
.news-card__facts {
  font-family: 'Open Sans', sans-serif;
}

.hero-text {
  max-width: 60ch;
  margin: 1.2rem 0 0;
  font-size: 1.02rem;
  line-height: 1.75;
  color: #4d5b73;
}

.hero-actions,
.impact-section__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.8rem;
  margin-top: 1.5rem;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.85rem;
  margin-top: 1.6rem;
}

.hero-stat-card {
  background: #011e41;
  color: #ffffff;
  border-radius: 24px;
  padding: 1rem;
  display: grid;
  gap: 0.2rem;
}

.hero-stat-card strong {
  font-family: 'Montserrat', sans-serif;
  font-size: 2rem;
  line-height: 1;
}

.hero-stat-card span {
  font-family: 'Montserrat', sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
}

.hero-stat-card small {
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.45;
}

.hero-spotlight {
  padding: 1.5rem;
  background: linear-gradient(180deg, #ffffff 0%, #f8f9fc 100%);
  display: grid;
  gap: 1rem;
}

.hero-spotlight h2,
.section-heading h2,
.story-section__copy h2,
.impact-section__panel h2,
.news-card__body h3 {
  margin: 0;
  font-family: 'Montserrat', sans-serif;
  color: #011e41;
}

.hero-spotlight h2 {
  font-size: clamp(1.6rem, 2.3vw, 2.25rem);
  line-height: 1.08;
}

.hero-spotlight__text {
  margin: 0;
  color: #4d5b73;
  line-height: 1.7;
}

.hero-spotlight__meta {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
}

.hero-spotlight__meta div {
  background: #ffffff;
  border: 1px solid rgba(1, 30, 65, 0.08);
  border-radius: 20px;
  padding: 0.9rem 1rem;
}

.hero-spotlight__meta span {
  display: block;
  font-family: 'Open Sans', sans-serif;
  font-size: 0.8rem;
  color: #6f7b90;
  margin-bottom: 0.35rem;
}

.hero-spotlight__meta strong {
  font-family: 'Montserrat', sans-serif;
  color: #011e41;
}

.media-frame {
  position: relative;
  overflow: hidden;
  border-radius: 26px;
  background: linear-gradient(135deg, #dfe5ee 0%, #f4f6f9 100%);
  min-height: 220px;
}

.media-frame img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.media-frame--hero {
  min-height: 310px;
}

.media-frame--placeholder {
  border: 1px dashed rgba(1, 30, 65, 0.18);
}

.media-frame__placeholder {
  position: absolute;
  inset: 0;
  display: grid;
  place-content: center;
  gap: 0.4rem;
  padding: 1.25rem;
  text-align: center;
  color: #52627b;
}

.media-frame__placeholder i {
  font-size: 1.5rem;
  color: #f5333f;
}

.media-frame__placeholder span {
  font-family: 'Montserrat', sans-serif;
  font-weight: 700;
  color: #011e41;
}

.news-grid-section,
.story-section,
.impact-section {
  background: transparent;
}

.section-heading {
  display: grid;
  grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
  gap: 1rem;
  align-items: end;
  margin-bottom: 1rem;
}

.section-heading p {
  margin: 0;
  color: #596780;
  line-height: 1.7;
}

.news-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
}

.news-card {
  overflow: hidden;
}

.news-card__body {
  padding: 1.25rem;
}

.news-card__tag {
  display: inline-flex;
  align-items: center;
  min-height: 32px;
  padding: 0 0.8rem;
  margin-bottom: 0.8rem;
  border-radius: 999px;
  background: rgba(245, 51, 63, 0.1);
  font-family: 'Montserrat', sans-serif;
  font-size: 0.78rem;
  font-weight: 700;
  color: #d6232f;
}

.news-card__body p {
  margin: 0.85rem 0 0;
  color: #58657c;
  line-height: 1.7;
}

.news-card__facts {
  list-style: none;
  padding: 0;
  margin: 1rem 0 0;
  display: grid;
  gap: 0.45rem;
  color: #36455f;
}

.story-section {
  grid-template-columns: minmax(300px, 0.95fr) minmax(0, 1.05fr);
  align-items: stretch;
}

.story-section__copy {
  padding: 1.5rem;
}

.story-section__copy p {
  margin: 1rem 0 0;
  color: #55637b;
  line-height: 1.78;
}

.story-gallery {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  grid-template-rows: repeat(2, minmax(180px, 1fr));
  gap: 1rem;
}

.story-gallery__item--featured {
  grid-row: 1 / span 2;
}

.story-gallery__item,
.story-gallery__item .media-frame {
  height: 100%;
}

.impact-section {
  grid-template-columns: minmax(0, 1.1fr) auto;
  align-items: center;
  background: linear-gradient(135deg, #011e41 0%, #12345f 100%);
  border-radius: 32px;
  padding: 1.4rem;
  box-shadow: 0 28px 42px rgba(1, 30, 65, 0.16);
}

.impact-section__panel {
  padding: 1.4rem;
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.12);
  box-shadow: none;
}

.impact-section__panel h2,
.impact-section__panel p {
  color: #ffffff;
}

.impact-section__panel p {
  margin: 0.9rem 0 0;
  opacity: 0.88;
  line-height: 1.75;
}

.impact-section__actions {
  margin-top: 0;
  justify-content: flex-end;
}

@media (max-width: 1200px) {
  .hero-section,
  .story-section,
  .section-heading,
  .impact-section {
    grid-template-columns: 1fr;
  }

  .news-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 860px) {
  .landing-header {
    padding: 1rem;
    align-items: flex-start;
    flex-direction: column;
  }

  .landing-header__actions {
    width: 100%;
    justify-content: flex-end;
    flex-wrap: wrap;
  }

  .landing-main {
    padding: 1rem;
  }

  .hero-stats,
  .news-grid,
  .hero-spotlight__meta,
  .story-gallery {
    grid-template-columns: 1fr;
  }

  .story-gallery {
    grid-template-rows: none;
  }

  .story-gallery__item--featured {
    grid-row: auto;
  }

  .impact-section__actions {
    justify-content: flex-start;
  }
}

@media (max-width: 640px) {
  .landing-brand {
    width: 100%;
  }

  .landing-brand__logo {
    width: 140px;
  }

  .landing-header__actions,
  .hero-actions,
  .impact-section__actions {
    width: 100%;
  }

  .header-login-button,
  .header-ghost-button,
  .cta-primary,
  .cta-secondary {
    width: 100%;
  }

  .hero-copy,
  .hero-spotlight,
  .story-section__copy,
  .impact-section__panel,
  .news-card__body {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .hero-copy h1 {
    max-width: none;
    font-size: clamp(2.2rem, 14vw, 3.1rem);
  }
}
</style>
