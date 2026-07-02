from pathlib import Path

path = Path(r'frontend/src/views/DocumentoActividadPdfView.vue')
text = path.read_text(encoding='utf-8')

replacements = [
    ('<footer class="page-footer">Hoja {{ narrativePageNumber(1) }} de {{ narrativeTotalPages }}</footer>', '<footer class="page-footer">{{ narrativePageNumber(1) }}</footer>'),
    ('<footer class="page-footer">Hoja {{ narrativePageNumber(2) }} de {{ narrativeTotalPages }}</footer>', '<footer class="page-footer">{{ narrativePageNumber(2) }}</footer>'),
    ('<footer class="page-footer">Hoja {{ narrativePageNumber(pageIndex + 3) }} de {{ narrativeTotalPages }}</footer>', '<footer class="page-footer">{{ narrativePageNumber(pageIndex + 3) }}</footer>'),
    ('<footer class="page-footer">Hoja {{ narrativePageNumber(narrativeTotalPages) }} de {{ narrativeTotalPages }}</footer>', '<footer class="page-footer">{{ narrativePageNumber(narrativeTotalPages) }}</footer>'),
    ('<footer class="page-footer">Hoja {{ analysisPageNumber(1) }} de {{ analysisTotalPages }}</footer>', '<footer class="page-footer">{{ analysisPageNumber(1) }}</footer>'),
    ('<footer class="page-footer">Hoja {{ analysisPageNumber(pageIndex + 2) }} de {{ analysisTotalPages }}</footer>', '<footer class="page-footer">{{ analysisPageNumber(pageIndex + 2) }}</footer>'),
    ('<footer class="page-footer">Hoja {{ analysisPageNumber(pageIndex + 2 + analysisClimatePages.length) }} de {{ analysisTotalPages }}</footer>', '<footer class="page-footer">{{ analysisPageNumber(pageIndex + 2 + analysisClimatePages.length) }}</footer>'),
    ('<footer class="page-footer">Hoja {{ analysisPageNumber(analysisTotalPages) }} de {{ analysisTotalPages }}</footer>', '<footer class="page-footer">{{ analysisPageNumber(analysisTotalPages) }}</footer>'),
]
for old, new in replacements:
    if old in text:
        text = text.replace(old, new)

old_header = '''.document-cover {
  text-align: center;
  margin-bottom: 0.18in;
}

.document-cover--compact {
  margin-bottom: 0.08in;
}

.document-cover__logo {
  width: 0.75in;
  margin-bottom: 0.08in;
}

.document-cover h1,
.document-cover h2 {
  margin: 0;
  font-weight: 700;
}

.document-cover h1 {
  font-size: 18pt;
  letter-spacing: 0.02em;
}

.document-cover h2 {
  margin-top: 0.06in;
  font-size: 12pt;
}
'''
new_header = '''.document-cover {
  display: grid;
  grid-template-columns: 1.35in 1fr;
  align-items: center;
  column-gap: 0.24in;
  margin-bottom: 0.18in;
}

.document-cover--compact {
  margin-bottom: 0.08in;
}

.document-cover__logo {
  width: 1.08in;
  justify-self: start;
  align-self: start;
}

.document-cover h1,
.document-cover h2 {
  margin: 0;
  font-weight: 700;
  text-align: center;
}

.document-cover h1 {
  grid-column: 2;
  font-size: 18pt;
  letter-spacing: 0.02em;
}

.document-cover h2 {
  grid-column: 2;
  margin-top: 0.06in;
  font-size: 12pt;
}
'''
if old_header not in text:
    raise SystemExit('No se encontró el bloque de estilos del encabezado.')
text = text.replace(old_header, new_header, 1)

old_label = '''.label-cell {
  width: 32%;
  font-weight: 700;
}
'''
new_label = '''.label-cell {
  width: 32%;
  font-weight: 700;
  background: #d9d9d9;
}
'''
if old_label not in text:
    raise SystemExit('No se encontró el bloque de estilos de la columna izquierda.')
text = text.replace(old_label, new_label, 1)

old_footer = '''.page-footer {
  position: absolute;
  right: 0.4in;
  bottom: 0.2in;
  font-size: 9pt;
  color: #333;
}
'''
new_footer = '''.page-footer {
  position: absolute;
  right: 0.4in;
  bottom: 0.2in;
  font-size: 9pt;
  color: #333;
  line-height: 1;
  min-width: 0.18in;
  text-align: right;
}
'''
if old_footer not in text:
    raise SystemExit('No se encontró el bloque de estilos del pie de página.')
text = text.replace(old_footer, new_footer, 1)

path.write_text(text, encoding='utf-8')
