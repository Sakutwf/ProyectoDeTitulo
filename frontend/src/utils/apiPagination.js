/**
 * Recupera todos los elementos de un endpoint paginado de Laravel.
 * La primera respuesta determina cuántas páginas restantes se solicitan.
 * Lo usan actividades, boletas y galería de la experiencia voluntaria.
 */
export async function fetchAllPages(httpClient, url, params = {}) {
  const firstResponse = await httpClient.get(url, { params: { ...params, page: 1 } })
  const firstPage = firstResponse.data || {}
  const totalPages = Number(firstPage.last_page || 1)
  const pages = [firstPage]

  if (totalPages > 1) {
    const responses = await Promise.all(
      Array.from({ length: totalPages - 1 }, (_, index) =>
        httpClient.get(url, { params: { ...params, page: index + 2 } })
      )
    )
    pages.push(...responses.map((response) => response.data || {}))
  }

  return pages.flatMap((page) => page.data || [])
}
