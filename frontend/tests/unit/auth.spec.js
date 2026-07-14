import {
  canAccessVolunteerProfile,
  canManagePlatform,
  defaultRouteForUser,
  getUserRoleSlugs,
  hasAnyRole,
  hasRole,
  requiresAccessSelection,
  resolveAccessMode
} from '@/utils/auth'

const dualAccessUser = {
  roles: [{ clave: 'administrador' }, { clave: 'voluntario' }],
  voluntario: { id: 42 }
}

describe('reglas de acceso', () => {
  test('extrae y consulta los roles del usuario', () => {
    expect(getUserRoleSlugs(dualAccessUser)).toEqual(['administrador', 'voluntario'])
    expect(hasRole(dualAccessUser, 'administrador')).toBe(true)
    expect(hasAnyRole(dualAccessUser, ['tesorero', 'voluntario'])).toBe(true)
    expect(hasRole(null, 'administrador')).toBe(false)
  })

  test('reconoce las capacidades de administracion y voluntariado', () => {
    expect(canManagePlatform(dualAccessUser)).toBe(true)
    expect(canAccessVolunteerProfile(dualAccessUser)).toBe(true)
    expect(requiresAccessSelection(dualAccessUser)).toBe(true)
  })

  test('exige una seleccion para las cuentas con acceso dual', () => {
    expect(resolveAccessMode(dualAccessUser)).toBeNull()
    expect(defaultRouteForUser(dualAccessUser)).toEqual({ name: 'access-selection' })
  })

  test.each(['administrador', 'voluntario'])('conserva el modo valido %s', (mode) => {
    expect(resolveAccessMode(dualAccessUser, mode)).toBe(mode)
    expect(defaultRouteForUser(dualAccessUser, mode)).toEqual({ name: 'inicio' })
  })
})
