from pathlib import Path

path = Path(r'frontend/src/views/DocumentoActividadPdfView.vue')
text = path.read_text(encoding='utf-8')

replacements = [
(""".pdf-document {
  display: grid;
  gap: 1.2rem;
  justify-content: center;
}
""", """.pdf-document {
  display: grid;
  gap: 1.2rem;
  justify-content: center;
  width: fit-content;
  margin: 0 auto;
}
"""),
(""".print-page {
  position: relative;
  width: 8.5in;
  min-height: 11in;
  background: #fff;
  color: #111;
  box-shadow: 0 18px 40px rgba(15, 47, 95, 0.15);
  page-break-after: always;
}
""", """.print-page {
  position: relative;
  width: 8.5in;
  min-height: 11in;
  box-sizing: border-box;
  background: #fff;
  color: #111;
  box-shadow: 0 18px 40px rgba(15, 47, 95, 0.15);
  page-break-after: always;
}
"""),
(""".analysis-event-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.14in;
}
""", """.analysis-event-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.1in 0.14in;
}
"""),
(""".analysis-event-card {
  border: 1px solid #6d6d6d;
  padding: 0.1in 0.12in;
  min-height: 0.72in;
}
""", """.analysis-event-card {
  border: 1px solid #6d6d6d;
  padding: 0.09in 0.11in;
  min-height: 0.64in;
}
"""),
(""".analysis-event-card__label {
  margin-bottom: 0.08in;
  font-size: 9.6pt;
  font-weight: 700;
}
""", """.analysis-event-card__label {
  margin-bottom: 0.05in;
  font-size: 9.4pt;
  font-weight: 700;
}
"""),
(""".analysis-event-card__value {
  font-size: 12pt;
  line-height: 1.3;
}
""", """.analysis-event-card__value {
  font-size: 11.4pt;
  line-height: 1.22;
}
""")
]

for old, new in replacements:
    if old not in text:
        raise SystemExit(f'No se encontró bloque esperado:\n{old}')
    text = text.replace(old, new, 1)

old_mobile = '''@media (max-width: 1100px) {
  .analysis-event-grid {
    grid-template-columns: 1fr;
  }
  .print-page {
    width: 100%;
    min-height: auto;
  }

  .print-page__content--footer-sheet {
    min-height: auto;
  }
}
'''
new_mobile = '''@media (max-width: 1100px) {
  .pdf-export-page {
    overflow-x: auto;
  }

  .pdf-document {
    justify-content: start;
    margin: 0;
    padding-bottom: 0.5rem;
  }

  .analysis-event-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .print-page {
    width: 8.5in;
    min-height: 11in;
  }

  .print-page__content--footer-sheet {
    min-height: calc(11in - 1.1in);
  }
}
'''
if old_mobile not in text:
    raise SystemExit('No se encontró el bloque responsive esperado.')
text = text.replace(old_mobile, new_mobile, 1)

path.write_text(text, encoding='utf-8')
