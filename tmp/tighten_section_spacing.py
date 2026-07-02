from pathlib import Path

path = Path(r'frontend/src/views/DocumentoActividadPdfView.vue')
text = path.read_text(encoding='utf-8')
replacements = [
(""".print-section {
  margin-top: 0.08in;
}
""", """.print-section {
  margin-top: 0.03in;
}
"""),
(""".narrative-block + .narrative-block {
  margin-top: 1cm;
}
""", """.narrative-block + .narrative-block {
  margin-top: 0.08in;
}
"""),
(""".narrative-block--merged + .narrative-block--merged {
  margin-top: 0.2in;
}
""", """.narrative-block--merged + .narrative-block--merged {
  margin-top: 0.08in;
}
"""),
(""".signoff-sheet--attached {
  margin-top: 0.12in;
}
""", """.signoff-sheet--attached {
  margin-top: 0.05in;
}
"""),
(""".analysis-section__heading {
  margin-bottom: 0.12in;
  padding-bottom: 0.06in;
  border-bottom: 1px solid #1f1f1f;
  font-size: 13pt;
  font-weight: 700;
  letter-spacing: 0.01em;
}
""", """.analysis-section__heading {
  margin-bottom: 0.07in;
  padding-bottom: 0.04in;
  border-bottom: 1px solid #1f1f1f;
  font-size: 13pt;
  font-weight: 700;
  letter-spacing: 0.01em;
}
"""),
(""".analysis-intro-copy {
  margin-top: 0.12in;
}
""", """.analysis-intro-copy {
  margin-top: 0.05in;
}
"""),
(""".analysis-section--compact {
  margin-top: 0.18in;
}
""", """.analysis-section--compact {
  margin-top: 0.06in;
}
"""),
(""".analysis-climate-card__meta {
  display: grid;
  gap: 0.08in;
  margin-bottom: 0.12in;
}
""", """.analysis-climate-card__meta {
  display: grid;
  gap: 0.05in;
  margin-bottom: 0.08in;
}
"""),
(""".analysis-risk-list {
  display: grid;
  gap: 0.16in;
}
""", """.analysis-risk-list {
  display: grid;
  gap: 0.09in;
}
"""),
(""".analysis-risk-card__header {
  padding: 0.12in;
  border-bottom: 1px solid #6d6d6d;
}
""", """.analysis-risk-card__header {
  padding: 0.09in 0.12in;
  border-bottom: 1px solid #6d6d6d;
}
"""),
(""".analysis-risk-card__title {
  margin-bottom: 0.08in;
  font-size: 13pt;
  font-weight: 500;
}
""", """.analysis-risk-card__title {
  margin-bottom: 0.05in;
  font-size: 13pt;
  font-weight: 500;
}
"""),
(""".analysis-risk-card__body p + p {
  margin-top: 0.12in;
}
""", """.analysis-risk-card__body p + p {
  margin-top: 0.06in;
}
"""),
(""".analysis-narrative-section {
  margin-top: 0.18in;
}
""", """.analysis-narrative-section {
  margin-top: 0.08in;
}
"""),
(""".analysis-section__footer-note {
  margin-top: 0.08in;
  padding-top: 0.12in;
  border-top: 1px solid #7a7a7a;
  font-size: 11.5pt;
  line-height: 1.45;
}
""", """.analysis-section__footer-note {
  margin-top: 0.04in;
  padding-top: 0.08in;
  border-top: 1px solid #7a7a7a;
  font-size: 11.5pt;
  line-height: 1.45;
}
"""),
(""".analysis-narrative-section--meta {
  margin-top: 0.16in;
}
""", """.analysis-narrative-section--meta {
  margin-top: 0.08in;
}
""")
]
for old, new in replacements:
    if old not in text:
        raise SystemExit(f'No se encontró bloque esperado:\n{old}')
    text = text.replace(old, new, 1)
path.write_text(text, encoding='utf-8')
