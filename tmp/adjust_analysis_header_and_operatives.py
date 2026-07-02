from pathlib import Path

path = Path(r'frontend/src/views/DocumentoActividadPdfView.vue')
text = path.read_text(encoding='utf-8')

old_header_block = '''        <article class="print-page">
          <div class="print-page__content">
            <header class="document-cover">
              <div class="document-cover__brand">
                <img :src="logoSrc" alt="Cruz Roja Chilena" class="document-cover__logo">
              </div>
              <div class="document-cover__titles">
                <h1>ANÁLISIS DE CONTEXTO</h1>
                <h2>{{ analysisTitleLine }}</h2>
              </div>
            </header>

            <section class="print-section narrative-block">
              <table class="print-table print-table--two-col">
                <tbody>
                  <tr class="section-row">
                    <th colspan="2">Información General Operativo</th>
                  </tr>
                  <tr>
                    <td class="label-cell">Fecha del informe</td>
                    <td>{{ documentDateLongLabel }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Fecha de la actividad</td>
                    <td>{{ activityDateLongLabel }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Nombre de la actividad</td>
                    <td>{{ actividad.nombre || 'Sin nombre' }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Horario</td>
                    <td>{{ analysisEvent.horario_evento || activitySchedule }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Lugar</td>
                    <td>{{ analysisEvent.lugar_evento || primaryLocation }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Filial</td>
                    <td>{{ filialName }}</td>
                  </tr>
                </tbody>
              </table>
            </section>

            <section class="print-section narrative-block">
              <table class="print-table print-table--two-col">
                <tbody>
                  <tr class="section-row">
                    <th colspan="2">Objetivo del análisis</th>
                  </tr>
                  <tr>
                    <td colspan="2" class="multiline-cell">{{ analysis.proposito_documento || 'Sin información registrada.' }}</td>
                  </tr>
                </tbody>
              </table>
            </section>

            <section class="print-section narrative-block analysis-section">
              <div class="analysis-section__heading">DESCRIPCION DEL EVENTO</div>
              <div class="analysis-event-grid">
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">NOMBRE DEL EVENTO</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.nombre_evento || actividad.nombre || 'Sin nombre' }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">FECHA</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.fecha_evento || activityDateLongLabel }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">HORARIO</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.horario_evento || activitySchedule }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">LUGAR</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.lugar_evento || primaryLocation }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">PARTICIPANTES</div>
                  <div class="analysis-event-card__value analysis-event-card__value--strong">{{ analysisEvent.participantes_evento || 'Sin participantes estimados' }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">ORGANIZADOR</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.organizador_evento || 'Sin organizador registrado' }}</div>
                </div>
              </div>
            </section>
          </div>
          <footer class="page-footer">{{ analysisPageNumber(1) }}</footer>
        </article>
'''

new_header_block = '''        <article class="print-page">
          <div class="print-page__content print-page__content--analysis-intro">
            <header class="document-cover document-cover--analysis">
              <div class="document-cover__brand document-cover__brand--analysis">
                <img :src="logoSrc" alt="Cruz Roja Chilena" class="document-cover__logo document-cover__logo--analysis">
              </div>
              <div class="document-cover__titles document-cover__titles--analysis">
                <div class="document-cover__eyebrow">COMUNICADO FILIAL {{ filialName.toUpperCase() }}</div>
                <h1>ANALISIS DE CONTEXTO</h1>
                <h2>{{ analysisTitleLine }}</h2>
              </div>
            </header>

            <section class="print-section narrative-block analysis-intro-copy">
              <p>{{ analysis.proposito_documento || 'Sin información registrada.' }}</p>
            </section>

            <section class="print-section narrative-block analysis-section analysis-section--compact">
              <div class="analysis-section__heading">DESCRIPCION DEL EVENTO</div>
              <div class="analysis-event-grid analysis-event-grid--compact">
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">NOMBRE DEL EVENTO</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.nombre_evento || actividad.nombre || 'Sin nombre' }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">FECHA</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.fecha_evento || activityDateLongLabel }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">HORARIO</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.horario_evento || activitySchedule }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">LUGAR</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.lugar_evento || primaryLocation }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">PARTICIPANTES</div>
                  <div class="analysis-event-card__value analysis-event-card__value--strong">{{ analysisEvent.participantes_evento || 'Sin participantes estimados' }}</div>
                </div>
                <div class="analysis-event-card">
                  <div class="analysis-event-card__label">ORGANIZADOR</div>
                  <div class="analysis-event-card__value">{{ analysisEvent.organizador_evento || 'Sin organizador registrado' }}</div>
                </div>
              </div>
            </section>
          </div>
          <footer class="page-footer">{{ analysisPageNumber(1) }}</footer>
        </article>
'''

if old_header_block not in text:
    raise SystemExit('No se encontró el bloque inicial del análisis.')
text = text.replace(old_header_block, new_header_block, 1)

old_operatives = '''        <article class="print-page">
          <div class="print-page__content">
            <section class="print-section print-section--tight-top narrative-block">
              <table class="print-table print-table--two-col">
                <tbody>
                  <tr class="section-row">
                    <th colspan="2">Medidas operativas</th>
                  </tr>
                  <tr>
                    <td class="label-cell">Plan de traslados</td>
                    <td class="multiline-cell">{{ analysis.plan_traslados || 'Sin información registrada.' }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Protocolo de traslado</td>
                    <td class="multiline-cell">{{ analysis.protocolo_traslado || 'Sin información registrada.' }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Centros de salud cercanos</td>
                    <td class="multiline-cell">{{ analysis.centros_salud || 'Sin información registrada.' }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Conclusión</td>
                    <td class="multiline-cell">{{ analysis.conclusion || 'Sin información registrada.' }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Observaciones complementarias</td>
                    <td class="multiline-cell">{{ analysis.observaciones_finales || 'Sin información registrada.' }}</td>
                  </tr>
                </tbody>
              </table>
            </section>
          </div>
          <footer class="page-footer">{{ analysisPageNumber(analysisTotalPages) }}</footer>
        </article>
'''

new_operatives = '''        <article class="print-page">
          <div class="print-page__content">
            <section class="print-section print-section--tight-top analysis-narrative-section">
              <div class="analysis-section__heading">PROTOCOLO DE TRASLADO</div>
              <div class="analysis-narrative-copy multiline-cell">{{ analysis.protocolo_traslado || 'Sin información registrada.' }}</div>
            </section>

            <section class="print-section analysis-narrative-section">
              <div class="analysis-section__heading">CENTROS DE SALUD CERCANOS</div>
              <div class="analysis-narrative-copy multiline-cell">{{ analysis.centros_salud || 'Sin información registrada.' }}</div>
            </section>

            <section class="print-section analysis-narrative-section" v-if="analysis.plan_traslados">
              <div class="analysis-section__heading">PLAN DE TRASLADOS</div>
              <div class="analysis-narrative-copy multiline-cell">{{ analysis.plan_traslados }}</div>
            </section>

            <section class="print-section analysis-narrative-section">
              <div class="analysis-section__heading">CONCLUSION</div>
              <div class="analysis-narrative-copy multiline-cell">{{ analysis.conclusion || 'Sin información registrada.' }}</div>
            </section>

            <section class="print-section analysis-narrative-section" v-if="analysis.observaciones_finales">
              <div class="analysis-section__footer-note multiline-cell">{{ analysis.observaciones_finales }}</div>
            </section>

            <section class="print-section analysis-narrative-section analysis-narrative-section--meta">
              <div class="analysis-narrative-meta"><strong>Elaboracion del documento:</strong> {{ signoffDateLabel }}</div>
            </section>
          </div>
          <footer class="page-footer">{{ analysisPageNumber(analysisTotalPages) }}</footer>
        </article>
'''

if old_operatives not in text:
    raise SystemExit('No se encontró el bloque de medidas operativas.')
text = text.replace(old_operatives, new_operatives, 1)

style_anchor = '''.document-cover__titles {
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
style_replace = '''.document-cover__titles {
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

.document-cover--analysis {
  align-items: flex-start;
  gap: 0.4in;
  margin-bottom: 0.16in;
}

.document-cover__brand--analysis {
  width: 1.7in;
  flex: 0 0 1.7in;
  min-height: 1.35in;
}

.document-cover__logo--analysis {
  width: 1.35in;
}

.document-cover__titles--analysis {
  padding-top: 0.06in;
  text-align: left;
}

.document-cover__titles--analysis h1,
.document-cover__titles--analysis h2 {
  text-align: left;
}

.document-cover__titles--analysis h1 {
  font-size: 22pt;
}

.document-cover__titles--analysis h2 {
  margin-top: 0.08in;
  font-size: 14pt;
}

.document-cover__eyebrow {
  margin-bottom: 0.08in;
  font-size: 10.5pt;
  font-weight: 700;
  letter-spacing: 0.12em;
}
'''
if style_anchor not in text:
    raise SystemExit('No se encontró el bloque de estilos del encabezado.')
text = text.replace(style_anchor, style_replace, 1)

style_anchor_2 = '''.analysis-section__heading {
  margin-bottom: 0.12in;
  padding-bottom: 0.06in;
  border-bottom: 1px solid #1f1f1f;
  font-size: 13pt;
  font-weight: 700;
  letter-spacing: 0.01em;
}

.analysis-event-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.14in;
}
'''
style_replace_2 = '''.analysis-section__heading {
  margin-bottom: 0.12in;
  padding-bottom: 0.06in;
  border-bottom: 1px solid #1f1f1f;
  font-size: 13pt;
  font-weight: 700;
  letter-spacing: 0.01em;
}

.analysis-intro-copy {
  margin-top: 0.06in;
}

.analysis-intro-copy p {
  margin: 0;
  font-size: 12pt;
  line-height: 1.45;
}

.analysis-section--compact {
  margin-top: 0.18in;
}

.analysis-event-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.14in;
}
'''
if style_anchor_2 not in text:
    raise SystemExit('No se encontró el bloque de estilos del análisis.')
text = text.replace(style_anchor_2, style_replace_2, 1)

style_anchor_3 = '''.analysis-event-card {
  border: 1px solid #6d6d6d;
  padding: 0.12in;
  min-height: 0.82in;
}
'''
style_replace_3 = '''.analysis-event-card {
  border: 1px solid #6d6d6d;
  padding: 0.1in 0.12in;
  min-height: 0.72in;
}
'''
if style_anchor_3 not in text:
    raise SystemExit('No se encontró el estilo de las tarjetas del evento.')
text = text.replace(style_anchor_3, style_replace_3, 1)

insert_anchor = '''.analysis-risk-card__body p + p {
  margin-top: 0.12in;
}

@media (max-width: 1100px) {
'''
insert_block = '''.analysis-risk-card__body p + p {
  margin-top: 0.12in;
}

.analysis-narrative-section {
  margin-top: 0.18in;
}

.analysis-narrative-copy {
  font-size: 12pt;
  line-height: 1.5;
}

.analysis-narrative-copy.multiline-cell {
  white-space: pre-line;
}

.analysis-section__footer-note {
  margin-top: 0.08in;
  padding-top: 0.12in;
  border-top: 1px solid #7a7a7a;
  font-size: 11.5pt;
  line-height: 1.45;
}

.analysis-narrative-section--meta {
  margin-top: 0.16in;
}

.analysis-narrative-meta {
  font-size: 11pt;
}

@media (max-width: 1100px) {
'''
if insert_anchor not in text:
    raise SystemExit('No se encontró el punto de inserción para estilos narrativos.')
text = text.replace(insert_anchor, insert_block, 1)

path.write_text(text, encoding='utf-8')
