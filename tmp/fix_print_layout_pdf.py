from pathlib import Path

path = Path(r'frontend/src/views/DocumentoActividadPdfView.vue')
text = path.read_text(encoding='utf-8')

old = '''@media print {
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  @page {
    size: letter portrait;
    margin: 0.2in;
  }

  body {
    margin: 0;
    background: #fff;
  }

  .no-print {
    display: none !important;
  }

  .pdf-document {
    display: block;
  }

  .print-page {
    width: 8.1in;
    min-height: 10.6in;
    box-shadow: none;
    margin: 0;
    break-after: page;
  }

  .print-page:last-child {
    break-after: auto;
  }

  .print-table,
  .photo-instruction,
  .print-photo,
  .print-climate-photo {
    break-inside: avoid;
    page-break-inside: avoid;
  }
}
'''

new = '''@media print {
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  @page {
    size: letter portrait;
    margin: 0;
  }

  html,
  body {
    margin: 0;
    padding: 0;
    background: #fff;
  }

  .no-print {
    display: none !important;
  }

  .pdf-document {
    display: block;
    margin: 0;
    padding: 0;
  }

  .print-page {
    width: 8.5in;
    min-height: 11in;
    margin: 0;
    box-shadow: none;
    overflow: hidden;
    break-after: page;
    page-break-after: always;
    break-inside: avoid;
    page-break-inside: avoid;
  }

  .print-page:last-child {
    break-after: auto;
    page-break-after: auto;
  }

  .print-page__content {
    padding: 0.45in 0.45in 0.65in;
  }

  .print-page__content--footer-sheet {
    min-height: calc(11in - 1.1in);
  }

  .print-section,
  .narrative-block,
  .signoff-sheet,
  .photo-instruction {
    break-inside: avoid;
    page-break-inside: avoid;
  }

  .print-table tr,
  .print-table th,
  .print-table td,
  .print-photo,
  .print-climate-photo {
    break-inside: avoid;
    page-break-inside: avoid;
  }
}
'''

if old not in text:
    raise SystemExit('No se encontró el bloque @media print esperado.')

text = text.replace(old, new, 1)
path.write_text(text, encoding='utf-8')
