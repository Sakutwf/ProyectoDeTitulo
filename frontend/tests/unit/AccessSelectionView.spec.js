import { shallowMount } from '@vue/test-utils'
import AccessSelectionView from '@/views/AccessSelectionView.vue'

const currentUser = {
  roles: [{ clave: 'administrador' }, { clave: 'voluntario' }],
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
  test('guarda la experiencia elegida y entra al inicio', async () => {
    const { wrapper, dispatch, replace } = mountView()

    await wrapper.get('button.volunteer').trigger('click')

    expect(dispatch).toHaveBeenCalledWith('chooseAccessMode', 'voluntario')
    expect(replace).toHaveBeenCalledWith({ name: 'inicio' })
  })

  test('respeta la ruta solicitada antes de seleccionar el acceso', async () => {
    const { wrapper, dispatch, replace } = mountView({ redirect: '/historial/9' })

    await wrapper.get('button.admin').trigger('click')

    expect(dispatch).toHaveBeenCalledWith('chooseAccessMode', 'administrador')
    expect(replace).toHaveBeenCalledWith('/historial/9')
  })
})
