import {
  canAccessVolunteerProfile,
  canManagePlatform,
  defaultRouteForUser,
  getUserRoleSlugs,
  hasAnyRole,
  hasRole,
  isAdministratorExperience,
  isVolunteerExperience,
  isVolunteerOnly,
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

  test('asigna automaticamente la experiencia de voluntario', () => {
    const user = {
      roles: [{ clave: 'voluntario' }],
      voluntario: { id: 10 }
    }

    expect(resolveAccessMode(user)).toBe('voluntario')
    expect(isVolunteerExperience(user)).toBe(true)
    expect(isAdministratorExperience(user)).toBe(false)
    expect(isVolunteerOnly(user)).toBe(true)
    expect(defaultRouteForUser(user)).toEqual({ name: 'inicio' })
  })

  test('asigna la experiencia administrativa a quien gestiona la plataforma', () => {
    const user = { roles: [{ clave: 'secretario-directiva' }] }

    expect(resolveAccessMode(user)).toBe('administrador')
    expect(isAdministratorExperience(user)).toBe(true)
    expect(isVolunteerOnly(user)).toBe(false)
  })

  test('rechaza un modo invalido para una cuenta con acceso dual', () => {
    expect(resolveAccessMode(dualAccessUser, 'modo-invalido')).toBeNull()
  })

  test('usa el modo indicado o el administrativo cuando no hay roles', () => {
    expect(resolveAccessMode({}, 'lector')).toBe('lector')
    expect(resolveAccessMode({})).toBe('administrador')
  })
})
