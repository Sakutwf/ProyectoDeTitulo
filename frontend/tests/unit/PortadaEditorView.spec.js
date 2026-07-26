import { flushPromises, shallowMount } from '@vue/test-utils'
import axios from 'axios'
import Swal from 'sweetalert2'
import PortadaEditorView from '@/views/PortadaEditorView.vue'

jest.mock('axios', () => ({ get: jest.fn(), put: jest.fn(), post: jest.fn() }))
jest.mock('sweetalert2', () => ({ fire: jest.fn() }))

function mountView() {
  axios.get.mockResolvedValueOnce({
    data: {
      actividades: [],
      imagenes: [],
      configuracion: { textos: {}, carrusel: [], novedades: [] }
    }
  })

  return shallowMount(PortadaEditorView, {
    global: {
      stubs: { SidebarMenu: true }
    }
  })
}

describe('PortadaEditorView', () => {
  beforeEach(() => {
    jest.clearAllMocks()
  })

  test('informa cuando los cambios se guardan correctamente', async () => {
    axios.put.mockResolvedValueOnce({ data: {} })
    const wrapper = mountView()
    await flushPromises()

    await wrapper.vm.save()

    expect(axios.put).toHaveBeenCalled()
    expect(Swal.fire).toHaveBeenCalledWith(
      'Cambios guardados',
      'La portada fue actualizada correctamente.',
      'success'
    )
  })

  test('informa cuando los cambios no pudieron guardarse', async () => {
    axios.put.mockRejectedValueOnce({ response: { data: { message: 'Error de validación.' } } })
    const wrapper = mountView()
    await flushPromises()

    await wrapper.vm.save()

    expect(Swal.fire).toHaveBeenCalledWith(
      'No se guardaron los cambios',
      'Error de validación.',
      'error'
    )
  })
})
