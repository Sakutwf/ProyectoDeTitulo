export function getUserRoleSlugs(user) {
  return (user?.roles || []).map((role) => role.clave)
}

export function hasRole(user, role) {
  return getUserRoleSlugs(user).includes(role)
}

export function hasAnyRole(user, roles) {
  const userRoles = getUserRoleSlugs(user)
  return roles.some((role) => userRoles.includes(role))
}

export function canManagePlatform(user) {
  return hasAnyRole(user, ['administrador', 'secretario-directiva'])
}

export function canAccessVolunteerProfile(user) {
  return hasRole(user, 'voluntario') && Boolean(user?.voluntario?.n_registro || user?.voluntario)
}

export function requiresAccessSelection(user) {
  return hasRole(user, 'administrador') && canAccessVolunteerProfile(user)
}

export function resolveAccessMode(user, accessMode = null) {
  if (requiresAccessSelection(user)) {
    return accessMode === 'voluntario' || accessMode === 'administrador'
      ? accessMode
      : null
  }

  if (canAccessVolunteerProfile(user) && !canManagePlatform(user)) {
    return 'voluntario'
  }

  if (canManagePlatform(user)) {
    return 'administrador'
  }

  return accessMode || 'administrador'
}

export function isAdministratorExperience(user, accessMode = null) {
  return resolveAccessMode(user, accessMode) === 'administrador'
}

export function isVolunteerExperience(user, accessMode = null) {
  return resolveAccessMode(user, accessMode) === 'voluntario'
}

export function isVolunteerOnly(user) {
  return hasAnyRole(user, ['voluntario']) && !canManagePlatform(user)
}

export function defaultRouteForUser(user, accessMode = null) {
  if (isVolunteerExperience(user, accessMode)) {
    return { name: 'inicio' }
  }

  if (requiresAccessSelection(user) && !resolveAccessMode(user, accessMode)) {
    return { name: 'access-selection' }
  }

  return { name: 'inicio' }
}
