export const EVENT_TYPE_OPTIONS = [
  { value: 'FORMATIVO', label: 'Formativo' },
  { value: 'SERVICIO', label: 'Servicio' }
]

export const ACTIVITY_TYPE_OPTIONS = [
  { value: 'CURSO', label: 'Curso' },
  { value: 'TALLER', label: 'Taller' },
  { value: 'SEMINARIO', label: 'Seminario' },
  { value: 'CAMPAÑA', label: 'Campaña' },
  { value: 'OPERATIVO', label: 'Operativo' },
  { value: 'COBERTURA', label: 'Cobertura' },
  { value: 'COMUNITARIA', label: 'Comunitaria' }
]

export const FORMATIVE_ACTIVITY_TYPES = ['CURSO', 'TALLER', 'SEMINARIO']

export function normalizeCatalogValue(value) {
  return String(value || '').trim().toUpperCase()
}

export function isFormativeEvent(value) {
  return normalizeCatalogValue(value) === 'FORMATIVO'
}

export function isServiceEvent(value) {
  return normalizeCatalogValue(value) === 'SERVICIO'
}
