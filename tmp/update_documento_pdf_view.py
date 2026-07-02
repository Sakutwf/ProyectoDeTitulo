from pathlib import Path

path = Path(r'frontend/src/views/DocumentoActividadPdfView.vue')
text = path.read_text(encoding='utf-8')

old_block = '''                  <tr>
                    <td class="label-cell">Descripción General (Breve narrativo de la actividad. No más de 10 renglones)</td>
                    <td class="multiline-cell">{{ narrative.descripcion_general || 'Sin información registrada.' }}</td>
                  </tr>
                  <tr class="section-row">
                    <th colspan="2">Personas asistidas (disgregado por género, tipo de atención, traslado)</th>
                  </tr>
                  <tr v-for="item in narrativeCounts" :key="item.tipo">
                    <td>{{ item.tipo }}</td>
                    <td class="value-cell">{{ item.numero }}</td>
                  </tr>
                  <tr v-if="!narrativeCounts.length">
                    <td colspan="2">Sin registros de personas asistidas.</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Situaciones de interés ocurridas</td>
                    <td class="multiline-cell">{{ narrative.situaciones_interes || 'Sin información registrada.' }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">N° de Puestos (por región/comuna y ubicación de estos)</td>
                    <td class="multiline-cell">{{ narrative.numero_puestos || 'Sin información registrada.' }}</td>
                  </tr>
'''
new_block = '''                  <tr class="narrative-summary-row">
                    <td class="label-cell narrative-summary-label">Descripción General (Breve narrativo de la actividad. No más de 10 renglones)</td>
                    <td class="narrative-summary-cell">
                      <div class="multiline-cell narrative-summary-description">{{ narrative.descripcion_general || 'Sin información registrada.' }}</div>
                      <table class="embedded-counts-table" aria-label="Personas asistidas">
                        <tbody>
                          <tr class="section-row">
                            <th colspan="2">
                              Personas asistidas
                              <span class="embedded-counts-table__subtitle">(disgregado por género, tipo de atención, traslado)</span>
                            </th>
                          </tr>
                          <tr v-for="item in narrativeCounts" :key="item.tipo">
                            <td>{{ item.tipo }}</td>
                            <td class="value-cell">{{ item.numero }}</td>
                          </tr>
                          <tr v-if="!narrativeCounts.length">
                            <td colspan="2">Sin registros de personas asistidas.</td>
                          </tr>
                        </tbody>
                      </table>
                      <div class="narrative-summary-meta">
                        <strong>Situaciones de Interés ocurridas:</strong>
                        <span class="multiline-cell">{{ narrative.situaciones_interes || 'Sin información registrada.' }}</span>
                      </div>
                      <div class="narrative-summary-meta">
                        <strong>N° de Puestos</strong>
                        <span class="narrative-summary-meta__hint">(por región/comuna y ubicación de estos)</span>
                        <span class="multiline-cell">{{ narrative.numero_puestos || 'Sin información registrada.' }}</span>
                      </div>
                    </td>
                  </tr>
'''
if old_block not in text:
    raise SystemExit('No se encontró el bloque narrativo esperado.')
text = text.replace(old_block, new_block, 1)

old_photo = '''                  <tr v-for="(photo, photoIndex) in page" :key="photo.id || `${pageIndex}-${photoIndex}`">
                    <td class="photo-cell">
                      <img :src="photo.imagen_url" :alt="photo.titulo || `Fotografía ${photoIndex + 1}`" class="print-photo">
                    </td>
                    <td class="multiline-cell photo-description-cell">
                      <strong>{{ photo.titulo || `Fotografía ${pageIndex * narrativePhotosPerPage + photoIndex + 1}` }}</strong>
                      <div>{{ photo.descripcion_informe || 'Sin descripción registrada.' }}</div>
                    </td>
                  </tr>
'''
new_photo = '''                  <tr v-for="(photo, photoIndex) in page" :key="photo.id || `${pageIndex}-${photoIndex}`" class="photo-row">
                    <td class="photo-cell photo-cell--joined">
                      <img :src="photo.imagen_url" :alt="photo.titulo || `Fotografía ${photoIndex + 1}`" class="print-photo">
                    </td>
                    <td class="multiline-cell photo-description-cell photo-description-cell--joined">
                      <strong>{{ photo.titulo || `Fotografía ${pageIndex * narrativePhotosPerPage + photoIndex + 1}` }}</strong>
                      <div>{{ photo.descripcion_informe || 'Sin descripción registrada.' }}</div>
                    </td>
                  </tr>
'''
if old_photo not in text:
    raise SystemExit('No se encontró el bloque de fotografías esperado.')
text = text.replace(old_photo, new_photo, 1)

old_style_1 = '''.photo-instruction-cell {
  padding-top: 0.14in;
  padding-bottom: 0.14in;
  line-height: 1.35;
}

.print-table--narrative-flow th,
.print-table--narrative-flow td {
'''
new_style_1 = '''.photo-instruction-cell {
  padding-top: 0.14in;
  padding-bottom: 0.14in;
  line-height: 1.35;
}

.narrative-summary-label {
  vertical-align: middle;
}

.narrative-summary-cell {
  padding: 0.08in 0.1in 0.12in;
}

.narrative-summary-description {
  margin-bottom: 0.08in;
}

.embedded-counts-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
  margin-bottom: 0.08in;
  font-size: 10.5pt;
}

.embedded-counts-table th,
.embedded-counts-table td {
  border: 1px solid #1f1f1f;
  padding: 0.06in 0.08in;
  vertical-align: top;
}

.embedded-counts-table .section-row th {
  text-align: left;
}

.embedded-counts-table__subtitle,
.narrative-summary-meta__hint {
  font-style: italic;
  font-weight: 400;
}

.narrative-summary-meta {
  line-height: 1.35;
}

.narrative-summary-meta + .narrative-summary-meta {
  margin-top: 0.05in;
}

.print-table--narrative-flow th,
.print-table--narrative-flow td {
'''
if old_style_1 not in text:
    raise SystemExit('No se encontró el ancla de estilos narrativos.')
text = text.replace(old_style_1, new_style_1, 1)

old_style_2 = '''.photo-cell,
.climate-photo-cell {
  width: 42%;
}

.print-photo,
.print-climate-photo {
  display: block;
  width: 100%;
  object-fit: cover;
  border: 1px solid #8f8f8f;
}

.print-photo {
  aspect-ratio: 4 / 3;
}
'''
new_style_2 = '''.photo-cell,
.climate-photo-cell {
  width: 42%;
}

.print-table--photo .photo-cell--joined {
  padding: 0;
}

.print-table--photo .photo-description-cell--joined {
  vertical-align: middle;
}

.print-photo,
.print-climate-photo {
  display: block;
  width: 100%;
  object-fit: cover;
  border: 1px solid #8f8f8f;
}

.print-photo {
  aspect-ratio: 4 / 3;
  border: 0;
}
'''
if old_style_2 not in text:
    raise SystemExit('No se encontró el ancla de estilos de fotografías.')
text = text.replace(old_style_2, new_style_2, 1)

path.write_text(text, encoding='utf-8')
