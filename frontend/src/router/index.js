import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import UserView from '../views/UserView.vue'
import ActividadView from '../views/ActividadView.vue'
import HistorialView from '../views/HistorialView.vue'
import HistorialPdfView from '../views/HistorialPdfView.vue'
import LoginView from '../views/LoginView.vue'
import AccessSelectionView from '../views/AccessSelectionView.vue'
import VolunteerActivitiesView from '../views/VolunteerActivitiesView.vue'
import DocumentosView from '../views/DocumentosView.vue'
import store from '../store'
import { defaultRouteForUser } from '../utils/auth'

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
    redirect: '/inicio'
  },
  {
    path: '/inicio',
    name: 'inicio',
    component: HomeView,
    meta: { requiresAuth: true }
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
    path: '/mis-actividades',
    name: 'volunteer-activities',
    component: VolunteerActivitiesView,
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
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

router.beforeEach((to, from, next) => {
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

  if (isAuthenticated && needsAccessSelection && to.name !== 'access-selection') {
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


