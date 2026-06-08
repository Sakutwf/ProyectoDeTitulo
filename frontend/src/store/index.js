import { createStore } from 'vuex'
import {
  canManagePlatform,
  defaultRouteForUser,
  getUserRoleSlugs,
  hasAnyRole,
  isAdministratorExperience,
  isVolunteerExperience,
  isVolunteerOnly,
  requiresAccessSelection,
  resolveAccessMode
} from '../utils/auth'

const SESSION_STORAGE_KEY = 'cruz-roja-session'

function readStoredSession() {
  if (typeof window === 'undefined') {
    return null
  }

  const rawSession = window.localStorage.getItem(SESSION_STORAGE_KEY)

  if (!rawSession) {
    return null
  }

  try {
    return JSON.parse(rawSession)
  } catch (error) {
    window.localStorage.removeItem(SESSION_STORAGE_KEY)
    return null
  }
}

function persistSession(session) {
  if (typeof window === 'undefined') {
    return
  }

  if (session) {
    window.localStorage.setItem(SESSION_STORAGE_KEY, JSON.stringify(session))
    return
  }

  window.localStorage.removeItem(SESSION_STORAGE_KEY)
}

function normalizeSession(session) {
  if (!session?.user) {
    return null
  }

  return {
    ...session,
    access_mode: resolveAccessMode(session.user, session.access_mode ?? null)
  }
}

export default createStore({
  state: {
    session: normalizeSession(readStoredSession()),
  },
  getters: {
    authUser: (state) => state.session?.user || null,
    isAuthenticated: (state) => Boolean(state.session?.user),
    accessMode: (state) => state.session?.access_mode || null,
    userRoleSlugs: (state, getters) => getUserRoleSlugs(getters.authUser),
    hasRole: (state, getters) => (role) => getters.userRoleSlugs.includes(role),
    hasAnyRole: (state, getters) => (roles) => hasAnyRole(getters.authUser, roles),
    canManagePlatform: (state, getters) => canManagePlatform(getters.authUser),
    isVolunteerOnly: (state, getters) => isVolunteerOnly(getters.authUser),
    requiresAccessSelection: (state, getters) => requiresAccessSelection(getters.authUser),
    isAdministratorExperience: (state, getters) => isAdministratorExperience(getters.authUser, getters.accessMode),
    isVolunteerExperience: (state, getters) => isVolunteerExperience(getters.authUser, getters.accessMode),
    defaultRoute: (state, getters) => defaultRouteForUser(getters.authUser, getters.accessMode),
  },
  mutations: {
    setSession(state, session) {
      const normalizedSession = normalizeSession(session)
      state.session = normalizedSession
      persistSession(normalizedSession)
    },
    clearSession(state) {
      state.session = null
      persistSession(null)
    },
    setAccessMode(state, accessMode) {
      if (!state.session?.user) {
        return
      }

      const nextSession = normalizeSession({
        ...state.session,
        access_mode: accessMode
      })

      state.session = nextSession
      persistSession(nextSession)
    }
  },
  actions: {
    restoreSession({ commit }) {
      commit('setSession', readStoredSession())
    },
    login({ commit }, session) {
      commit('setSession', session)
    },
    chooseAccessMode({ commit }, accessMode) {
      commit('setAccessMode', accessMode)
    },
    logout({ commit }) {
      commit('clearSession')
    },
  },
  modules: {
  }
})
