import { flushPromises, shallowMount } from '@vue/test-utils'
import AccessSelectionView from '@/views/AccessSelectionView.vue'
import { confirm_logout } from '@/funciones'

jest.mock('@/funciones', () => ({
  confirm_logout: jest.fn()
}))

const currentUser = {
  roles: [{ clave: 'moderador' }],
  voluntario: { id: 42 }
}

function mountView(query = {}) {
  const dispatch = jest.fn()
  const replace = jest.fn()
  const wrapper = shallowMount(AccessSelectionView, {
    global: {
      mocks: {
        $store: { getters: { authUser: currentUser }, dispatch },
        $route: { query },
        $router: { replace }
      },
      stubs: { RouterLink: true }
    }
  })

  return { wrapper, dispatch, replace }
}

describe('AccessSelectionView', () => {
  beforeEach(() => {
    confirm_logout.mockResolvedValue(true)
  })

  test('guarda la experiencia elegida y entra al inicio', async () => {
    const { wrapper, dispatch, replace } = mountView()

    await wrapper.get('button.volunteer').trigger('click')

    expect(dispatch).toHaveBeenCalledWith('chooseAccessMode', 'voluntario')
    expect(replace).toHaveBeenCalledWith({ name: 'inicio' })
  })

  test('cierra la sesión y vuelve al login', async () => {
    const { wrapper, dispatch, replace } = mountView()

    await wrapper.get('button.access-logout').trigger('click')
    await flushPromises()

    expect(dispatch).toHaveBeenCalledWith('logout')
    expect(replace).toHaveBeenCalledWith({ name: 'login' })
  })

  test('mantiene la sesión cuando se cancela el cierre', async () => {
    confirm_logout.mockResolvedValueOnce(false)
    const { wrapper, dispatch, replace } = mountView()

    await wrapper.get('button.access-logout').trigger('click')
    await flushPromises()

    expect(dispatch).not.toHaveBeenCalled()
    expect(replace).not.toHaveBeenCalled()
  })

  test('respeta la ruta solicitada antes de seleccionar el acceso', async () => {
    const { wrapper, dispatch, replace } = mountView({ redirect: '/historial/9' })

    await wrapper.get('button.admin').trigger('click')

    expect(dispatch).toHaveBeenCalledWith('chooseAccessMode', 'administrador')
    expect(replace).toHaveBeenCalledWith('/historial/9')
  })
})
