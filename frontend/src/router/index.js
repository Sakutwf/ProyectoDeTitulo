import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import CreateView from '../views/CreateView.vue'
import EditView from '../views/EditView.vue'
import UserView from '../views/UserView.vue'
import ActividadView from '../views/ActividadView.vue'
import EventoView from '@/views/EventoView.vue'
import HistorialView from '../views/HistorialView.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/inicio',
    name: 'inicio',
    component: HomeView 
  },
  {
    path: '/create',
    name: 'create',
    component: CreateView
  },
  {
    path: '/edit/:id',
    name: 'edit',
    component: EditView
  },
  {
    path: '/voluntarios',
    name: 'voluntarios',
    component: UserView
  },
  {
    path: '/eventos',
    name: 'eventos',
    component: EventoView
  },
  {
    path: '/actividades',
    name: 'actividades',
    component: ActividadView
  },
  {
    path: '/historial/:id',
    name: 'HistorialView',
    component: HistorialView
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
