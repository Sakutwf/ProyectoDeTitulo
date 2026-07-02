from pathlib import Path

path = Path(r'frontend/src/views/DocumentoActividadPdfView.vue')
text = path.read_text(encoding='utf-8')

old_narrative_header = '''            <header class="document-cover document-cover--compact">
              <img :src="logoSrc" alt="Cruz Roja Chilena" class="document-cover__logo">
              <h1>INFORME NARRATIVO</h1>
              <h2>{{ narrativeTitleLine }}</h2>
            </header>
'''
new_narrative_header = '''            <header class="document-cover document-cover--compact">
              <div class="document-cover__brand">
                <img :src="logoSrc" alt="Cruz Roja Chilena" class="document-cover__logo">
              </div>
              <div class="document-cover__titles">
                <h1>INFORME NARRATIVO</h1>
                <h2>{{ narrativeTitleLine }}</h2>
              </div>
            </header>
'''
text = text.replace(old_narrative_header, new_narrative_header, 1)

old_analysis_header = '''            <header class="document-cover">
              <img :src="logoSrc" alt="Cruz Roja Chilena" class="document-cover__logo">
              <h1>ANÁLISIS DE CONTEXTO</h1>
              <h2>{{ analysisTitleLine }}</h2>
            </header>
'''
new_analysis_header = '''            <header class="document-cover">
              <div class="document-cover__brand">
                <img :src="logoSrc" alt="Cruz Roja Chilena" class="document-cover__logo">
              </div>
              <div class="document-cover__titles">
                <h1>ANÁLISIS DE CONTEXTO</h1>
                <h2>{{ analysisTitleLine }}</h2>
              </div>
            </header>
'''
text = text.replace(old_analysis_header, new_analysis_header, 1)

old_css = '''.page-footer {
  position: absolute;
  right: 0.4in;
  bottom: 0.2in;
  font-size: 10pt;
  color: #333;
  line-height: 1;
  min-width: 0.18in;
  text-align: right;
}

.document-cover {
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
new_css = '''.page-footer {
  position: absolute;
  right: 0.4in;
  bottom: 0.2in;
  font-size: 10pt;
  color: #333;
  line-height: 1;
  min-width: 0.18in;
  text-align: right;
}

.document-cover {
  display: flex;
  align-items: center;
  gap: 0.28in;
  margin-bottom: 0.18in;
}

.document-cover--compact {
  margin-bottom: 0.08in;
}

.document-cover__brand {
  width: 1.45in;
  flex: 0 0 1.45in;
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.document-cover__logo {
  display: block;
  width: 1.12in;
  max-width: 100%;
}

.document-cover__titles {
  flex: 1;
}

.document-cover__titles h1,
.document-cover__titles h2 {
  margin: 0;
  font-weight: 700;
  text-align: center;
}

.document-cover__titles h1 {
  font-size: 18pt;
  letter-spacing: 0.02em;
}

.document-cover__titles h2 {
  margin-top: 0.06in;
  font-size: 12pt;
}
'''
if old_css not in text:
    raise SystemExit('No se encontró el bloque CSS principal esperado.')
text = text.replace(old_css, new_css, 1)

text = text.replace('.label-cell {\n  width: 32%;\n  font-weight: 700;\n  background: #d9d9d9;\n}\n', '.label-cell {\n  width: 32%;\n  font-weight: 700;\n  background-color: #d9d9d9 !important;\n  -webkit-print-color-adjust: exact;\n  print-color-adjust: exact;\n}\n', 1)

text = text.replace('@media print {\n  @page {', '@media print {\n  * {\n    -webkit-print-color-adjust: exact !important;\n    print-color-adjust: exact !important;\n  }\n\n  @page {', 1)

path.write_text(text, encoding='utf-8')
