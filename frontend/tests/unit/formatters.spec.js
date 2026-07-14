import { formatCurrency, formatDate, formatDateRange } from '@/utils/formatters'

describe('formateadores de presentacion', () => {
  test('formatea fechas sin desplazar el dia por zona horaria', () => {
    const expected = new Date('2026-05-15T00:00:00').toLocaleDateString('es-CL')

    expect(formatDate('2026-05-15T23:59:59Z')).toBe(expected)
    expect(formatDate(null, 'Sin fecha')).toBe('Sin fecha')
    expect(formatDate('valor invalido')).toBe('valor invalido')
  })

  test('formatea rangos completos e incompletos', () => {
    expect(formatDateRange(null, null, 'Sin fecha')).toBe('Sin fecha')
    expect(formatDateRange('2026-05-15', null)).toBe(formatDate('2026-05-15'))
    expect(formatDateRange('2026-05-15', '2026-05-16')).toBe(
      `${formatDate('2026-05-15')} - ${formatDate('2026-05-16')}`
    )
  })

  test('formatea pesos chilenos sin decimales', () => {
    const formatted = formatCurrency(12345.67).replace(/\s/g, '')

    expect(formatted).toContain('$')
    expect(formatted).toContain('12.346')
  })
})
