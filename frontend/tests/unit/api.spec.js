describe('configuracion de la API', () => {
  const originalBaseUrl = process.env.VUE_APP_API_BASE_URL

  afterEach(() => {
    jest.resetModules()
    if (originalBaseUrl === undefined) {
      delete process.env.VUE_APP_API_BASE_URL
    } else {
      process.env.VUE_APP_API_BASE_URL = originalBaseUrl
    }
  })

  test('usa /api cuando no existe configuracion', () => {
    delete process.env.VUE_APP_API_BASE_URL
    const { API_BASE, buildApiUrl } = require('@/config/api')

    expect(API_BASE).toBe('/api')
    expect(buildApiUrl()).toBe('/api')
    expect(buildApiUrl('/actividad')).toBe('/api/actividad')
  })

  test('normaliza espacios y barras de una base configurada', () => {
    process.env.VUE_APP_API_BASE_URL = ' https://api.example.test/base/ '
    const { API_BASE, buildApiUrl } = require('@/config/api')

    expect(API_BASE).toBe('https://api.example.test/base')
    expect(buildApiUrl('///usuarios')).toBe('https://api.example.test/base/usuarios')
  })
})
