from pathlib import Path

path = Path(r'frontend/src/views/DocumentoActividadPdfView.vue')
text = path.read_text(encoding='utf-8')

text = text.replace('const narrativePhotosPerPage = 2', 'const narrativePhotosPerPage = 3', 1)

old_block = '''        <article v-for="(page, pageIndex) in narrativePhotoPages" :key="`narrative-photo-page-${pageIndex}`" class="print-page">
          <div class="print-page__content">
            <section class="print-section print-section--tight-top">
              <table class="print-table print-table--photo">
                <tbody>
                  <tr class="section-row">
                    <th colspan="2">Registro fotográfico</th>
                  </tr>
                  <tr v-for="(photo, photoIndex) in page" :key="photo.id || `${pageIndex}-${photoIndex}`" class="photo-row">
                    <td class="photo-cell photo-cell--joined">
                      <img :src="photo.imagen_url" :alt="photo.titulo || `Fotografía ${photoIndex + 1}`" class="print-photo">
                    </td>
                    <td class="multiline-cell photo-description-cell photo-description-cell--joined">
                      <strong>{{ photo.titulo || `Fotografía ${pageIndex * narrativePhotosPerPage + photoIndex + 1}` }}</strong>
                      <div>{{ photo.descripcion_informe || 'Sin descripción registrada.' }}</div>
                    </td>
                  </tr>
                  <tr v-if="!page.length">
                    <td colspan="2">Sin fotografías registradas.</td>
                  </tr>
                </tbody>
              </table>
            </section>
          </div>
          <footer class="page-footer">Hoja {{ narrativePageNumber(pageIndex + 3) }} de {{ narrativeTotalPages }}</footer>
        </article>

        <article class="print-page">
          <div class="print-page__content print-page__content--footer-sheet">
            <section class="signoff-sheet">
              <table class="print-table print-table--two-col print-table--signoff">
                <tbody>
                  <tr>
                    <td class="label-cell">Elaboración del Informe</td>
                    <td>{{ signoffDateLabel }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Autorización del Informe</td>
                    <td>{{ narrative.autorizacion_informe || 'Sin autorización registrada.' }}</td>
                  </tr>
                </tbody>
              </table>
            </section>
          </div>
          <footer class="page-footer">Hoja {{ narrativePageNumber(narrativeTotalPages) }} de {{ narrativeTotalPages }}</footer>
        </article>
'''
new_block = '''        <article v-for="(page, pageIndex) in narrativePhotoPages" :key="`narrative-photo-page-${pageIndex}`" class="print-page">
          <div class="print-page__content">
            <section class="print-section print-section--tight-top">
              <table class="print-table print-table--photo">
                <tbody>
                  <tr class="section-row">
                    <th colspan="2">Registro fotográfico</th>
                  </tr>
                  <tr v-for="(photo, photoIndex) in page" :key="photo.id || `${pageIndex}-${photoIndex}`" class="photo-row">
                    <td class="photo-cell photo-cell--joined">
                      <img :src="photo.imagen_url" :alt="photo.titulo || `Fotografía ${photoIndex + 1}`" class="print-photo">
                    </td>
                    <td class="multiline-cell photo-description-cell photo-description-cell--joined">
                      <strong>{{ photo.titulo || `Fotografía ${pageIndex * narrativePhotosPerPage + photoIndex + 1}` }}</strong>
                      <div>{{ photo.descripcion_informe || 'Sin descripción registrada.' }}</div>
                    </td>
                  </tr>
                  <tr v-if="!page.length">
                    <td colspan="2">Sin fotografías registradas.</td>
                  </tr>
                </tbody>
              </table>
            </section>

            <section v-if="pageIndex === narrativePhotoPages.length - 1" class="signoff-sheet signoff-sheet--attached">
              <table class="print-table print-table--two-col print-table--signoff">
                <tbody>
                  <tr>
                    <td class="label-cell">Elaboración del Informe</td>
                    <td>{{ signoffDateLabel }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Autorización del Informe</td>
                    <td>{{ narrative.autorizacion_informe || 'Sin autorización registrada.' }}</td>
                  </tr>
                </tbody>
              </table>
            </section>
          </div>
          <footer class="page-footer">Hoja {{ narrativePageNumber(pageIndex + 3) }} de {{ narrativeTotalPages }}</footer>
        </article>

        <article v-if="!narrativePhotos.length" class="print-page">
          <div class="print-page__content print-page__content--footer-sheet">
            <section class="signoff-sheet">
              <table class="print-table print-table--two-col print-table--signoff">
                <tbody>
                  <tr>
                    <td class="label-cell">Elaboración del Informe</td>
                    <td>{{ signoffDateLabel }}</td>
                  </tr>
                  <tr>
                    <td class="label-cell">Autorización del Informe</td>
                    <td>{{ narrative.autorizacion_informe || 'Sin autorización registrada.' }}</td>
                  </tr>
                </tbody>
              </table>
            </section>
          </div>
          <footer class="page-footer">Hoja {{ narrativePageNumber(narrativeTotalPages) }} de {{ narrativeTotalPages }}</footer>
        </article>
'''
if old_block not in text:
    raise SystemExit('No se encontró el bloque de páginas fotográficas/finales.')
text = text.replace(old_block, new_block, 1)

text = text.replace('const narrativeTotalPages = computed(() => 3 + narrativePhotoPages.value.length)', 'const narrativeTotalPages = computed(() => 2 + Math.max(1, narrativePhotoPages.value.length))', 1)

old_style = '''.signoff-sheet {
  width: 100%;
}
'''
new_style = '''.signoff-sheet {
  width: 100%;
}

.signoff-sheet--attached {
  margin-top: 0.12in;
}

.print-table--photo .photo-row td {
  padding-top: 0.05in;
  padding-bottom: 0.05in;
}

.print-table--photo .print-photo {
  aspect-ratio: 16 / 10;
}

.print-table--signoff td {
  padding-top: 0.1in;
  padding-bottom: 0.1in;
}
'''
if old_style not in text:
    raise SystemExit('No se encontró el bloque base de estilos de signoff.')
text = text.replace(old_style, new_style, 1)

path.write_text(text, encoding='utf-8')
