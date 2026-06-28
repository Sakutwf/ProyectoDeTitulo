const DEFAULT_API_BASE = '/api'

function normalizeBaseUrl(value) {
  const trimmedValue = typeof value === 'string' ? value.trim() : ''
  const baseUrl = trimmedValue || DEFAULT_API_BASE

  return baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl
}

export const API_BASE = normalizeBaseUrl(process.env.VUE_APP_API_BASE_URL)

export function buildApiUrl(path = '') {
  const normalizedPath = String(path || '').replace(/^\/+/, '')

  return normalizedPath ? `${API_BASE}/${normalizedPath}` : API_BASE
}