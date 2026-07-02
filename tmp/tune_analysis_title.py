from pathlib import Path

path = Path(r'frontend/src/views/DocumentoActividadPdfView.vue')
text = path.read_text(encoding='utf-8')

replacements = [
(""".document-cover--analysis {
  align-items: flex-start;
  gap: 0.4in;
  margin-bottom: 0.16in;
}
""", """.document-cover--analysis {
  align-items: center;
  gap: 0.34in;
  margin-bottom: 0.2in;
}
"""),
(""".document-cover__brand--analysis {
  width: 1.7in;
  flex: 0 0 1.7in;
  min-height: 1.35in;
}
""", """.document-cover__brand--analysis {
  width: 1.5in;
  flex: 0 0 1.5in;
  min-height: 1.2in;
  align-items: center;
}
"""),
(""".document-cover__logo--analysis {
  width: 1.35in;
}
""", """.document-cover__logo--analysis {
  width: 1.12in;
}
"""),
(""".document-cover__titles--analysis {
  padding-top: 0.06in;
  text-align: left;
}
""", """.document-cover__titles--analysis {
  flex: 1;
  padding-top: 0;
  text-align: left;
}
"""),
(""".document-cover__titles--analysis h1 {
  font-size: 22pt;
}
""", """.document-cover__titles--analysis h1 {
  font-size: 21pt;
  line-height: 1.05;
}
"""),
(""".document-cover__titles--analysis h2 {
  margin-top: 0.08in;
  font-size: 14pt;
}
""", """.document-cover__titles--analysis h2 {
  margin-top: 0.07in;
  font-size: 13pt;
  line-height: 1.15;
}
"""),
(""".document-cover__eyebrow {
  margin-bottom: 0.08in;
  font-size: 10.5pt;
  font-weight: 700;
  letter-spacing: 0.12em;
}
""", """.document-cover__eyebrow {
  margin-bottom: 0.06in;
  font-size: 10pt;
  font-weight: 700;
  letter-spacing: 0.12em;
  line-height: 1.1;
}
"""),
(""".analysis-intro-copy {
  margin-top: 0.06in;
}
""", """.analysis-intro-copy {
  margin-top: 0.12in;
}
"""),
(""".analysis-intro-copy p {
  margin: 0;
  font-size: 12pt;
  line-height: 1.45;
}
""", """.analysis-intro-copy p {
  margin: 0;
  font-size: 11.7pt;
  line-height: 1.5;
}
""")
]

for old, new in replacements:
    if old not in text:
        raise SystemExit(f'No se encontró bloque esperado:\n{old}')
    text = text.replace(old, new, 1)

path.write_text(text, encoding='utf-8')
