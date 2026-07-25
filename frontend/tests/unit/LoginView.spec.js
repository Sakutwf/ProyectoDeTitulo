import { shallowMount } from '@vue/test-utils'
import axios from 'axios'
import LoginView from '@/views/LoginView.vue'
import { show_alerta } from '@/funciones'

jest.mock('axios', () => ({ post: jest.fn() }))
jest.mock('@/funciones', () => ({ show_alerta: jest.fn() }))

function mountView() {
  return shallowMount(LoginView, {
    global: {
      mocks: {
        $store: { dispatch: jest.fn() },
        $route: { query: {} },
        $router: { replace: jest.fn() }
      },
      stubs: { RouterLink: true }
    }
  })
}

describe('LoginView', () => {
  beforeEach(() => {
    jest.clearAllMocks()
  })

  test('avisa cuando el RUT tiene un dígito verificador inválido', async () => {
    const wrapper = mountView()
    wrapper.vm.form = { user: '22.222.222-3', id: 'clave' }

    await wrapper.vm.submitLogin()

    expect(axios.post).not.toHaveBeenCalled()
    expect(show_alerta).toHaveBeenCalledWith('El RUT ingresado no es válido.', 'warning', 'login-user')
  })

  test('no revela si falló el usuario o la contraseña', async () => {
    axios.post.mockRejectedValueOnce({ response: { status: 422 } })
    const wrapper = mountView()
    wrapper.vm.form = { user: '22.222.222-2', id: 'incorrecta' }

    await wrapper.vm.submitLogin()

    expect(show_alerta).toHaveBeenCalledWith('El RUT/usuario o la contraseña son incorrectos.', 'error')
  })
})