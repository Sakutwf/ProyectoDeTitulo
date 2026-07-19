import { fetchAllPages } from '@/utils/apiPagination'

describe('fetchAllPages', () => {
  test('devuelve los datos de un endpoint de una sola pagina', async () => {
    const httpClient = {
      get: jest.fn().mockResolvedValue({ data: { data: [{ id: 1 }], last_page: 1 } })
    }

    await expect(fetchAllPages(httpClient, '/items', { estado: 'activo' })).resolves.toEqual([{ id: 1 }])
    expect(httpClient.get).toHaveBeenCalledWith('/items', {
      params: { estado: 'activo', page: 1 }
    })
  })

  test('consulta en paralelo y combina las paginas restantes', async () => {
    const httpClient = {
      get: jest.fn()
        .mockResolvedValueOnce({ data: { data: [{ id: 1 }], last_page: 3 } })
        .mockResolvedValueOnce({ data: { data: [{ id: 2 }] } })
        .mockResolvedValueOnce({ data: { data: [{ id: 3 }] } })
    }

    await expect(fetchAllPages(httpClient, '/items')).resolves.toEqual([
      { id: 1 },
      { id: 2 },
      { id: 3 }
    ])
    expect(httpClient.get).toHaveBeenNthCalledWith(2, '/items', { params: { page: 2 } })
    expect(httpClient.get).toHaveBeenNthCalledWith(3, '/items', { params: { page: 3 } })
  })

  test('tolera respuestas vacias', async () => {
    const httpClient = { get: jest.fn().mockResolvedValue({ data: null }) }

    await expect(fetchAllPages(httpClient, '/items')).resolves.toEqual([])
  })

  test('ignora una pagina adicional sin datos', async () => {
    const httpClient = {
      get: jest.fn()
        .mockResolvedValueOnce({ data: { data: [{ id: 1 }], last_page: 2 } })
        .mockResolvedValueOnce({ data: null })
    }

    await expect(fetchAllPages(httpClient, '/items')).resolves.toEqual([{ id: 1 }])
  })
})
