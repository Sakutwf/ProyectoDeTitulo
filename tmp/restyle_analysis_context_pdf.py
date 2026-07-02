from pathlib import Path

path = Path(r'frontend/src/views/DocumentoActividadPdfView.vue')
text = path.read_text(encoding='utf-8')

text = text.replace('const analysisClimatesPerPage = 4', 'const analysisClimatesPerPage = 1', 1)
text = text.replace('const analysisRisksPerPage = 5', 'const analysisRisksPerPage = 2', 1)

old_description = '''            <section class="print-section narrative-block">
              <table class="print-table print-table--two-col">
                <tbody>
                  <tr class="section-row">
                    <th colspan="2">Descripción del evento</th>
                  </tr>
                  <tr>
                    <td class="label-cell">Nombre del evento</td>
                    <td>{{ analysisEvent.nombre_evento || actividad.nombre || 'Sin nombre' }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Fecha</td>
                    <td>{{ analysisEvent.fecha_evento || activityDateLongLabel }}</td>
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
                    <td class="label-cell">Participantes</td>
                    <td>{{ analysisEvent.participantes_evento || 'Sin participantes estimados' }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Organizador</td>
                    <td>{{ analysisEvent.organizador_evento || 'Sin organizador registrado' }}</td>
                  </tr>
                </tbody>
              </table>
            </section>
'''
new_description = '''            <section class="print-section narrative-block analysis-section">
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
'''
if old_description not in text:
    raise SystemExit('No se encontró el bloque de descripción del evento.')
text = text.replace(old_description, new_description, 1)

old_climate = '''        <article v-for="(page, pageIndex) in analysisClimatePages" :key="`analysis-climate-${pageIndex}`" class="print-page">
          <div class="print-page__content">
            <section class="print-section print-section--tight-top">
              <table class="print-table print-table--climate">
                <tbody>
                  <tr class="section-row">
                    <th colspan="4">Clima esperado</th>
                  </tr>
                  <tr class="subheader-row">
                    <th>Imagen</th>
                    <th>Tipo de clima</th>
                    <th>Mínima</th>
                    <th>Máxima</th>
                  </tr>
                  <tr v-for="(item, itemIndex) in page" :key="item.id || `${pageIndex}-${itemIndex}`">
                    <td class="climate-photo-cell">
                      <img v-if="item.imagen_url" :src="item.imagen_url" :alt="item.titulo_imagen || `Clima ${itemIndex + 1}`" class="print-climate-photo">
                      <span v-else>Sin imagen</span>
                    </td>
                    <td>{{ item.tipo_clima || 'Sin descripción' }}</td>
                    <td class="value-cell">{{ formatTemperature(item.temperatura_minima) }}</td>
                    <td class="value-cell">{{ formatTemperature(item.temperatura_maxima) }}</td>
                  </tr>
                  <tr v-if="!page.length">
                    <td colspan="4">Sin climas registrados.</td>
                  </tr>
                </tbody>
              </table>
            </section>
          </div>
          <footer class="page-footer">{{ analysisPageNumber(pageIndex + 2) }}</footer>
        </article>
'''
new_climate = '''        <article v-for="(page, pageIndex) in analysisClimatePages" :key="`analysis-climate-${pageIndex}`" class="print-page">
          <div class="print-page__content">
            <section class="print-section print-section--tight-top analysis-section">
              <div class="analysis-section__heading">CLIMA ESPERADO</div>
              <div v-for="(item, itemIndex) in page" :key="item.id || `${pageIndex}-${itemIndex}`" class="analysis-climate-card">
                <div class="analysis-climate-card__meta">
                  <div class="analysis-climate-card__metric">
                    <div class="analysis-climate-card__label">MINIMA:</div>
                    <div class="analysis-climate-card__value">{{ formatTemperature(item.temperatura_minima) }}</div>
                  </div>
                  <div class="analysis-climate-card__metric">
                    <div class="analysis-climate-card__label">MAXIMA:</div>
                    <div class="analysis-climate-card__value">{{ formatTemperature(item.temperatura_maxima) }}</div>
                  </div>
                  <div class="analysis-climate-card__metric">
                    <div class="analysis-climate-card__label">TIPO:</div>
                    <div class="analysis-climate-card__value">{{ item.tipo_clima || 'Sin descripción' }}</div>
                  </div>
                </div>
                <div class="analysis-climate-card__image-wrap">
                  <img v-if="item.imagen_url" :src="item.imagen_url" :alt="item.titulo_imagen || `Clima ${itemIndex + 1}`" class="analysis-climate-card__image">
                  <div v-else class="analysis-climate-card__empty">Sin imagen climática registrada.</div>
                </div>
              </div>
              <div v-if="!page.length" class="analysis-empty-state">Sin climas registrados.</div>
            </section>
          </div>
          <footer class="page-footer">{{ analysisPageNumber(pageIndex + 2) }}</footer>
        </article>
'''
if old_climate not in text:
    raise SystemExit('No se encontró el bloque de clima esperado.')
text = text.replace(old_climate, new_climate, 1)

old_risk = '''        <article v-for="(page, pageIndex) in analysisRiskPages" :key="`analysis-risk-${pageIndex}`" class="print-page">
          <div class="print-page__content">
            <section class="print-section print-section--tight-top">
              <table class="print-table print-table--risk">
                <tbody>
                  <tr class="section-row">
                    <th colspan="5">Identificación de riesgos</th>
                  </tr>
                  <tr class="subheader-row">
                    <th>Riesgo</th>
                    <th>Probabilidad</th>
                    <th>Impacto</th>
                    <th>Descripción</th>
                    <th>Medidas de mitigación</th>
                  </tr>
                  <tr v-for="(risk, riskIndex) in page" :key="risk.id || `${pageIndex}-${riskIndex}`">
                    <td>{{ risk.nombre || 'Sin nombre' }}</td>
                    <td>{{ joinOptions(risk.probabilidad) }}</td>
                    <td>{{ joinOptions(risk.impacto) }}</td>
                    <td class="multiline-cell">{{ risk.descripcion || '-' }}</td>
                    <td class="multiline-cell">{{ risk.mitigacion || '-' }}</td>
                  </tr>
                  <tr v-if="!page.length">
                    <td colspan="5">Sin riesgos registrados.</td>
                  </tr>
                </tbody>
              </table>
            </section>
          </div>
          <footer class="page-footer">{{ analysisPageNumber(pageIndex + 2 + analysisClimatePages.length) }}</footer>
        </article>
'''
new_risk = '''        <article v-for="(page, pageIndex) in analysisRiskPages" :key="`analysis-risk-${pageIndex}`" class="print-page">
          <div class="print-page__content">
            <section class="print-section print-section--tight-top analysis-section">
              <div class="analysis-section__heading">IDENTIFICACION DE RIESGOS</div>
              <div v-if="page.length" class="analysis-risk-list">
                <article v-for="(risk, riskIndex) in page" :key="risk.id || `${pageIndex}-${riskIndex}`" class="analysis-risk-card">
                  <div class="analysis-risk-card__header">
                    <div class="analysis-risk-card__title">{{ risk.nombre || 'Sin nombre' }}</div>
                    <div class="analysis-risk-card__badges">
                      <span class="analysis-risk-badge">Probabilidad: {{ joinOptions(risk.probabilidad) || '-' }}</span>
                      <span class="analysis-risk-badge">Impacto: {{ joinOptions(risk.impacto) || '-' }}</span>
                    </div>
                  </div>
                  <div class="analysis-risk-card__body">
                    <p><strong>Descripcion:</strong> {{ risk.descripcion || '-' }}</p>
                    <p><strong>Medidas de mitigacion:</strong> {{ risk.mitigacion || '-' }}</p>
                  </div>
                </article>
              </div>
              <div v-else class="analysis-empty-state">Sin riesgos registrados.</div>
            </section>
          </div>
          <footer class="page-footer">{{ analysisPageNumber(pageIndex + 2 + analysisClimatePages.length) }}</footer>
        </article>
'''
if old_risk not in text:
    raise SystemExit('No se encontró el bloque de riesgos.')
text = text.replace(old_risk, new_risk, 1)

anchor = '''.print-table--signoff td {
  padding-top: 0.12in;
  padding-bottom: 0.12in;
}

@media (max-width: 1100px) {
'''
insert = '''.print-table--signoff td {
  padding-top: 0.12in;
  padding-bottom: 0.12in;
}

.analysis-section__heading {
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

.analysis-event-card {
  border: 1px solid #6d6d6d;
  padding: 0.12in;
  min-height: 0.82in;
}

.analysis-event-card__label {
  margin-bottom: 0.08in;
  font-size: 9.6pt;
  font-weight: 700;
}

.analysis-event-card__value {
  font-size: 12pt;
  line-height: 1.3;
}

.analysis-event-card__value--strong {
  font-weight: 700;
}

.analysis-climate-card {
  border: 1px solid #c7d2e2;
  border-radius: 0.16in;
  padding: 0.16in;
}

.analysis-climate-card__meta {
  display: grid;
  gap: 0.08in;
  margin-bottom: 0.12in;
}

.analysis-climate-card__label {
  margin-bottom: 0.02in;
  font-size: 10pt;
  font-weight: 700;
}

.analysis-climate-card__value {
  font-size: 11.5pt;
  line-height: 1.25;
}

.analysis-climate-card__image-wrap {
  overflow: hidden;
  border-radius: 0.12in;
}

.analysis-climate-card__image {
  display: block;
  width: 100%;
  max-height: 7.1in;
  object-fit: cover;
}

.analysis-climate-card__empty,
.analysis-empty-state {
  border: 1px solid #bdbdbd;
  padding: 0.2in;
  text-align: center;
  color: #555;
}

.analysis-risk-list {
  display: grid;
  gap: 0.16in;
}

.analysis-risk-card {
  border: 1px solid #6d6d6d;
}

.analysis-risk-card__header {
  padding: 0.12in;
  border-bottom: 1px solid #6d6d6d;
}

.analysis-risk-card__title {
  margin-bottom: 0.08in;
  font-size: 13pt;
  font-weight: 500;
}

.analysis-risk-card__badges {
  display: flex;
  gap: 0.08in;
  flex-wrap: wrap;
}

.analysis-risk-badge {
  display: inline-block;
  border: 1px solid #6d6d6d;
  padding: 0.04in 0.08in;
  font-size: 9.5pt;
  font-weight: 700;
}

.analysis-risk-card__body {
  padding: 0.12in;
  line-height: 1.4;
}

.analysis-risk-card__body p {
  margin: 0;
}

.analysis-risk-card__body p + p {
  margin-top: 0.12in;
}

@media (max-width: 1100px) {
  .analysis-event-grid {
    grid-template-columns: 1fr;
  }
'''
if anchor not in text:
    raise SystemExit('No se encontró el ancla de estilos para insertar el nuevo formato.')
text = text.replace(anchor, insert, 1)

path.write_text(text, encoding='utf-8')
