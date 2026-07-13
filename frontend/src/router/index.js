import { createRouter, createWebHistory } from 'vue-router'
import { Modal } from 'bootstrap'
import HomeView from '../views/HomeView.vue'
import WelcomeView from '../views/WelcomeView.vue'
import UserView from '../views/UserView.vue'
import ActividadView from '../views/ActividadView.vue'
import HistorialView from '../views/HistorialView.vue'
import HistorialPdfView from '../views/HistorialPdfView.vue'
import DocumentoActividadPdfView from '../views/DocumentoActividadPdfView.vue'
import LoginView from '../views/LoginView.vue'
import AccessSelectionView from '../views/AccessSelectionView.vue'
import VolunteerActivitiesView from '../views/VolunteerActivitiesView.vue'
import VolunteerGalleryView from '../views/VolunteerGalleryView.vue'
import VolunteerBoletasView from '../views/VolunteerBoletasView.vue'
import BoletasGestionView from '../views/BoletasGestionView.vue'
import DocumentosView from '../views/DocumentosView.vue'
import GaleriaFotosView from '../views/GaleriaFotosView.vue'
import PortadaEditorView from '../views/PortadaEditorView.vue'
import SolicitudesHojaVidaView from '../views/SolicitudesHojaVidaView.vue'
import store from '../store'
import { defaultRouteForUser } from '../utils/auth'

function closeOpenModals() {
  if (typeof document === 'undefined') {
    return
  }

  document.querySelectorAll('.modal.show').forEach((element) => {
    const instance = Modal.getInstance(element) || new Modal(element)
    instance.hide()
  })

  document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove())
  document.body.classList.remove('modal-open')
  document.body.style.removeProperty('padding-right')
}

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guestOnly: true }
  },
  {
    path: '/',
    name: 'home',
    redirect: () => store.getters.isAuthenticated ? { name: 'inicio' } : { name: 'portada-publica' }
  },
  {
    path: '/inicio',
    name: 'inicio',
    component: WelcomeView,
    meta: { requiresAuth: true }
  },
  {
    path: '/portada',
    name: 'portada-publica',
    component: HomeView
  },
  {
    path: '/seleccionar-acceso',
    name: 'access-selection',
    component: AccessSelectionView,
    meta: { requiresAuth: true }
  },
  {
    path: '/voluntarios',
    name: 'voluntarios',
    component: UserView,
    meta: { requiresAuth: true, roles: ['administrador', 'secretario-directiva'], experience: 'admin' }
  },
  {
    path: '/actividades',
    name: 'actividades',
    component: ActividadView,
    meta: { requiresAuth: true, roles: ['administrador', 'secretario-directiva'], experience: 'admin' }
  },
  {
    path: '/documentos',
    name: 'documentos',
    component: DocumentosView,
    meta: { requiresAuth: true, roles: ['administrador', 'secretario-directiva'], experience: 'admin' }
  },
  {
    path: '/boletas',
    name: 'boletas-gestion',
    component: BoletasGestionView,
    meta: { requiresAuth: true, roles: ['administrador', 'secretario-directiva'], experience: 'admin' }
  },
  {
    path: '/galeria-fotos',
    name: 'galeria-fotos',
    component: GaleriaFotosView,
    meta: { requiresAuth: true, roles: ['administrador', 'secretario-directiva'], experience: 'admin' }
  },
  {
    path: '/solicitudes-hoja-vida',
    name: 'solicitudes-hoja-vida',
    component: SolicitudesHojaVidaView,
    meta: { requiresAuth: true, roles: ['administrador', 'secretario-directiva'], experience: 'admin' }
  },
  {
    path: '/administrar-portada',
    name: 'administrar-portada',
    component: PortadaEditorView,
    meta: { requiresAuth: true, roles: ['administrador'], experience: 'admin' }
  },
  {
    path: '/mis-actividades',
    name: 'volunteer-activities',
    component: VolunteerActivitiesView,
    meta: { requiresAuth: true, roles: ['voluntario'], experience: 'volunteer' }
  },
  {
    path: '/mi-galeria',
    name: 'volunteer-gallery',
    component: VolunteerGalleryView,
    meta: { requiresAuth: true, roles: ['voluntario'], experience: 'volunteer' }
  },
  {
    path: '/mis-boletas',
    name: 'volunteer-boletas',
    component: VolunteerBoletasView,
    meta: { requiresAuth: true, roles: ['voluntario'], experience: 'volunteer' }
  },
  {
    path: '/historial/:id',
    name: 'HistorialView',
    component: HistorialView,
    meta: { requiresAuth: true, roles: ['administrador', 'secretario-directiva', 'voluntario'] }
  },
  {
    path: '/historial/:id/pdf',
    name: 'HistorialPdfView',
    component: HistorialPdfView,
    meta: { requiresAuth: true, roles: ['administrador', 'secretario-directiva', 'voluntario'] }
  },
  {
    path: '/documentos-actividad/:id/pdf',
    name: 'DocumentoActividadPdfView',
    component: DocumentoActividadPdfView,
    meta: { requiresAuth: true, roles: ['administrador', 'secretario-directiva'], experience: 'admin' }
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

router.beforeEach((to, from, next) => {
  if (to.fullPath !== from.fullPath) {
    closeOpenModals()
  }

  const isAuthenticated = store.getters.isAuthenticated
  const authUser = store.getters.authUser
  const accessMode = store.getters.accessMode
  const needsAccessSelection = store.getters.requiresAccessSelection && !accessMode

  if (to.meta.guestOnly && isAuthenticated) {
    next(defaultRouteForUser(authUser, accessMode))
    return
  }

  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  if (isAuthenticated && to.name === 'access-selection' && accessMode) {
    next(defaultRouteForUser(authUser, accessMode))
    return
  }

  if (isAuthenticated && to.name === 'access-selection' && !store.getters.requiresAccessSelection) {
    next(defaultRouteForUser(authUser, accessMode))
    return
  }

  if (isAuthenticated && needsAccessSelection && !['access-selection', 'portada-publica'].includes(to.name)) {
    next({ name: 'access-selection', query: { redirect: to.fullPath } })
    return
  }

  if (to.meta.roles?.length && !store.getters.hasAnyRole(to.meta.roles)) {
    next(defaultRouteForUser(authUser, accessMode))
    return
  }

  if (to.meta.experience === 'admin' && store.getters.isVolunteerExperience) {
    next(defaultRouteForUser(authUser, accessMode))
    return
  }

  if (to.meta.experience === 'volunteer' && store.getters.isAdministratorExperience) {
    next(defaultRouteForUser(authUser, accessMode))
    return
  }

  if (['HistorialView', 'HistorialPdfView'].includes(to.name) && (store.getters.isVolunteerOnly || store.getters.isVolunteerExperience)) {
    const requestedUserId = Number(to.params.id)

    if (requestedUserId !== authUser?.id) {
      next({ name: to.name, params: { id: authUser.id }, query: to.query })
      return
    }
  }

  next()
})

export default router

