import { unref } from 'vue'
import { show_alerta } from '../funciones'

/**
 * Centraliza la impresión de los documentos PDF mostrados en el navegador.
 * La vista sólo aporta su estado de preparación; el composable evita imprimir
 * antes de tiempo y muestra el único aviso fiable: el error al abrir la
 * impresión. Lo usan HistorialPdfView y DocumentoActividadPdfView.
 */
export function useDocumentPrint(readyToPrint) {
  function printDocument() {
    if (!unref(readyToPrint)) return

    try {
      window.print()
    } catch {
      show_alerta('Hubo un problema al descargar el documento.', 'error')
    }
  }

  return { printDocument }
}
