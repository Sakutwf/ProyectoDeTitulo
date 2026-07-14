/**
 * Formatea una fecha de API sin desplazarla por zona horaria.
 * Lo usan actividades, documentos, boletas y galería.
 */
export function formatDate(value, emptyValue = '-') {
  if (!value) return emptyValue
  const parsed = new Date(`${String(value).slice(0, 10)}T00:00:00`)
  if (Number.isNaN(parsed.getTime())) return String(value)
  return parsed.toLocaleDateString('es-CL')
}

/**
 * Presenta un periodo usando el formateador común de fechas.
 * Lo usan documentos y galería de voluntarios.
 */
export function formatDateRange(start, end, emptyValue = '-') {
  if (!start && !end) return emptyValue
  if (start && end) return `${formatDate(start, emptyValue)} - ${formatDate(end, emptyValue)}`
  return formatDate(start || end, emptyValue)
}

/**
 * Presenta montos enteros en pesos chilenos.
 * Lo usan la gestión de boletas y las boletas del voluntario.
 */
export function formatCurrency(value) {
  return Number(value || 0).toLocaleString('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0
  })
}
