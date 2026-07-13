<template>
  <div class="d-flex">
    <SidebarMenu />
    <main class="content-wrapper portada-editor">
      <div class="editor-column">
      <header class="editor-header">
        <div>
          <p>Administración de contenido</p>
          <h1>Portada y novedades</h1>
          <span>Escoge qué se publica, cómo se ordena y cuánto espacio ocupa.</span>
        </div>
      </header>

      <div v-if="loading" class="editor-state">Cargando actividades y fotografías…</div>
      <div v-else class="editor-sections">
        <section class="editor-panel">
          <div class="panel-heading">
            <div><span>Paso 1</span><h2>Carrusel principal</h2><p>Cada diapositiva puede combinar entre una y tres fotografías.</p></div>
            <button class="btn btn-sm btn-danger" :disabled="!images.length || slides.length >= 5" @click="addSlide"><i class="fa-solid fa-plus me-1"></i> Diapositiva</button>
          </div>
          <p v-if="!images.length" class="empty-note">Primero deben existir fotografías en galerías o álbumes de actividades.</p>
          <div class="editor-list">
            <article v-for="(slide, index) in slides" :key="slide.localId" class="editor-card">
              <div class="card-toolbar">
                <strong>Diapositiva {{ index + 1 }}</strong>
                <div>
                  <button class="icon-button" :disabled="index === 0" @click="move(slides, index, -1)"><i class="fa-solid fa-arrow-up"></i></button>
                  <button class="icon-button" :disabled="index === slides.length - 1" @click="move(slides, index, 1)"><i class="fa-solid fa-arrow-down"></i></button>
                  <button class="icon-button icon-button--danger" @click="slides.splice(index, 1)"><i class="fa-solid fa-trash"></i></button>
                </div>
              </div>
              <div class="form-grid">
                <label><span class="color-label-row"><span>Título</span><button type="button" class="color-shortcut" @click="openSlideColorPicker(slide, 'titulo_color', `Título de la diapositiva ${index + 1}`)"><i :style="{ backgroundColor: slide.titulo_color }"></i> Color</button></span><input v-model="slide.titulo" class="form-control" maxlength="180"></label>
                <label><span class="color-label-row"><span>Bajada</span><button type="button" class="color-shortcut" @click="openSlideColorPicker(slide, 'bajada_color', `Bajada de la diapositiva ${index + 1}`)"><i :style="{ backgroundColor: slide.bajada_color }"></i> Color</button></span><input v-model="slide.bajada" class="form-control" maxlength="800"></label>
              </div>
              <div class="image-picker-grid">
                <div v-for="slot in 3" :key="slot" class="image-slot">
                  Foto {{ slot }} <small>{{ slot === 1 ? '(obligatoria)' : '(opcional)' }}</small>
                  <div
                    v-if="imageById(slide.imagenes[slot - 1])"
                    class="draggable-image"
                    :style="carouselFrameStyle(slide, slot - 1)"
                    @pointerdown="startImageDrag($event, slide.posiciones_x, slide.posiciones_y, slot - 1)"
                    @pointermove="moveImageDrag"
                    @pointerup="endImageDrag"
                    @pointercancel="endImageDrag"
                  >
                    <img :src="imageById(slide.imagenes[slot - 1]).url_publica" :style="imagePosition(slide.posiciones_x[slot - 1], slide.posiciones_y[slot - 1], slide.zooms[slot - 1])" draggable="false" alt="Foto seleccionada">
                    <span class="drag-hint"><i class="fa-solid fa-up-down-left-right"></i> Arrastra para encuadrar</span>
                    <span class="zoom-controls" @pointerdown.stop @click.stop>
                      <button type="button" :disabled="slide.zooms[slot - 1] <= 100" title="Quitar zoom" @click="adjustArrayZoom(slide.zooms, slot - 1, -10)"><i class="fa-solid fa-minus"></i></button>
                      <strong>{{ slide.zooms[slot - 1] || 100 }}%</strong>
                      <button type="button" :disabled="slide.zooms[slot - 1] >= 250" title="Aumentar zoom" @click="adjustArrayZoom(slide.zooms, slot - 1, 10)"><i class="fa-solid fa-plus"></i></button>
                    </span>
                  </div>
                  <button v-else type="button" class="image-select-button" :style="carouselFrameStyle(slide, slot - 1)" @click="openImagePicker('slide', index, slot - 1)"><span><i class="fa-solid fa-images"></i> Buscar en galerías</span></button>
                  <button v-if="slide.imagenes[slot - 1]" type="button" class="change-image" @click="openImagePicker('slide', index, slot - 1)"><i class="fa-solid fa-images"></i> Cambiar foto</button>
                  <button v-if="slide.imagenes[slot - 1]" type="button" class="remove-image" @click="removeSlideImage(slide, slot - 1)">Quitar foto</button>
                </div>
              </div>
            </article>
          </div>
          <div class="section-preview-action"><button type="button" class="btn btn-outline-danger" @click="refreshPreview('slides')"><i class="fa-solid fa-eye me-2"></i>Previsualizar cambios del carrusel</button></div>
        </section>

        <section class="editor-panel">
          <div class="panel-heading"><div><span>Textos generales</span><h2>Encabezados de la portada</h2><p>Edita los textos institucionales que acompañan al carrusel y las novedades.</p></div></div>
          <div class="form-grid general-text-fields">
            <label><span class="color-label-row"><span>Etiqueta sobre el carrusel</span><button type="button" class="color-shortcut" @click="openTextColorPicker('carrusel_etiqueta_color', 'Etiqueta sobre el carrusel')"><i :style="{ backgroundColor: texts.carrusel_etiqueta_color }"></i> Color</button></span><input v-model="texts.carrusel_etiqueta" class="form-control" maxlength="120"></label>
            <label><span class="color-label-row"><span>Etiqueta de novedades</span><button type="button" class="color-shortcut" @click="openTextColorPicker('novedades_etiqueta_color', 'Etiqueta de novedades')"><i :style="{ backgroundColor: texts.novedades_etiqueta_color }"></i> Color</button></span><input v-model="texts.novedades_etiqueta" class="form-control" maxlength="120"></label>
            <label><span class="color-label-row"><span>Título de novedades</span><button type="button" class="color-shortcut" @click="openTextColorPicker('novedades_titulo_color', 'Título de novedades')"><i :style="{ backgroundColor: texts.novedades_titulo_color }"></i> Color</button></span><input v-model="texts.novedades_titulo" class="form-control" maxlength="180"></label>
            <label><span class="color-label-row"><span>Descripción de novedades</span><button type="button" class="color-shortcut" @click="openTextColorPicker('novedades_descripcion_color', 'Descripción de novedades')"><i :style="{ backgroundColor: texts.novedades_descripcion_color }"></i> Color</button></span><textarea v-model="texts.novedades_descripcion" class="form-control" maxlength="300" rows="2"></textarea></label>
          </div>
          <div class="section-preview-action"><button type="button" class="btn btn-outline-danger" @click="refreshPreview('texts')"><i class="fa-solid fa-eye me-2"></i>Previsualizar cambios de textos</button></div>
        </section>

        <section class="editor-panel">
          <div class="panel-heading">
            <div><span>Paso 2</span><h2>Tarjetas de novedades</h2><p>Selecciona una actividad y decide qué información mostrar.</p></div>
            <button class="btn btn-sm btn-danger" :disabled="!activities.length" @click="addNews"><i class="fa-solid fa-plus me-1"></i> Novedad</button>
          </div>
          <div class="editor-list">
            <article v-for="(item, index) in news" :key="item.localId" class="editor-card">
              <div class="card-toolbar">
                <strong>Novedad {{ index + 1 }}</strong>
                <div>
                  <button class="icon-button" :disabled="index === 0" @click="move(news, index, -1)"><i class="fa-solid fa-arrow-up"></i></button>
                  <button class="icon-button" :disabled="index === news.length - 1" @click="move(news, index, 1)"><i class="fa-solid fa-arrow-down"></i></button>
                  <button class="icon-button icon-button--danger" @click="news.splice(index, 1)"><i class="fa-solid fa-trash"></i></button>
                </div>
              </div>
              <div class="form-grid">
                <label>Actividad<select v-model="item.actividad_id" class="form-select" @change="activityChanged(item)"><option v-for="activity in activities" :key="activity.id" :value="activity.id">{{ activity.nombre }}</option></select></label>
                <label>Foto de portada
                  <span
                    v-if="imageById(item.archivo_portada_id)"
                    class="draggable-image"
                    @pointerdown="startObjectDrag($event, item)"
                    @pointermove="moveImageDrag"
                    @pointerup="endImageDrag"
                    @pointercancel="endImageDrag"
                  >
                    <img :src="imageById(item.archivo_portada_id).url_publica" :style="imagePosition(item.posicion_x, item.posicion_y, item.zoom)" draggable="false" alt="Portada seleccionada">
                    <span class="drag-hint"><i class="fa-solid fa-up-down-left-right"></i> Arrastra para encuadrar</span>
                    <span class="zoom-controls" @pointerdown.stop @click.stop>
                      <button type="button" :disabled="item.zoom <= 100" title="Quitar zoom" @click="adjustObjectZoom(item, -10)"><i class="fa-solid fa-minus"></i></button>
                      <strong>{{ item.zoom || 100 }}%</strong>
                      <button type="button" :disabled="item.zoom >= 250" title="Aumentar zoom" @click="adjustObjectZoom(item, 10)"><i class="fa-solid fa-plus"></i></button>
                    </span>
                  </span>
                  <button v-else type="button" class="cover-picker" @click="openImagePicker('news', index)"><span><i class="fa-solid fa-images"></i> Buscar en galerías</span></button>
                  <button v-if="item.archivo_portada_id" type="button" class="change-image" @click="openImagePicker('news', index)"><i class="fa-solid fa-images"></i> Cambiar foto</button>
                </label>
              </div>
              <div class="form-grid">
                <label>Título<input v-model="item.titulo" class="form-control" maxlength="180"></label>
                <label>Resumen<textarea v-model="item.resumen" class="form-control" rows="2"></textarea></label>
                <label>Beneficiarios<input v-model.number="item.personas_ayudadas" type="number" min="0" class="form-control" placeholder="Dato informado por la actividad"></label>
              </div>
              <div class="field-options">
                <span>Datos visibles:</span>
                <label v-for="field in availableFields" :key="field.value"><input v-model="item.campos_visibles" type="checkbox" :value="field.value"> {{ field.label }}</label>
              </div>
            </article>
          </div>
          <div class="section-preview-action"><button type="button" class="btn btn-outline-danger" @click="refreshPreview('news')"><i class="fa-solid fa-eye me-2"></i>Previsualizar cambios de novedades</button></div>
        </section>

        <section class="editor-panel">
          <div class="panel-heading"><div><span>Pie de página</span><h2>Contacto, redes y enlaces</h2><p>Configura la información institucional que cerrará la landing.</p></div></div>
          <div class="form-grid">
            <label>Teléfono de la filial<input v-model="texts.telefono" class="form-control" maxlength="40" placeholder="Ej.: +56 9 1234 5678"></label>
            <label>Correo de contacto<input v-model="texts.correo_contacto" type="email" class="form-control" maxlength="255" placeholder="contacto@cruzroja.cl"></label>
            <label>Instagram<input v-model="texts.instagram_url" type="url" class="form-control" placeholder="https://instagram.com/..."></label>
            <label>Facebook<input v-model="texts.facebook_url" type="url" class="form-control" placeholder="https://facebook.com/..."></label>
            <label>Dirección de la filial<input v-model="texts.direccion" class="form-control" maxlength="300" placeholder="Calle, número, comuna"></label>
            <label>Enlace de Google Maps<input v-model="texts.ubicacion_url" type="url" class="form-control" placeholder="https://maps.google.com/..."></label>
            <label>Horario de atención<textarea v-model="texts.horario_atencion" class="form-control" maxlength="500" rows="2" placeholder="Ej.: Lunes a viernes, de 09:00 a 18:00"></textarea></label>
          </div>
          <div class="footer-editor-block">
            <div class="subsection-heading"><div><h3>Páginas relacionadas</h3><p>Agrega sitios institucionales de interés.</p></div><button type="button" class="btn btn-sm btn-outline-danger" @click="addRelatedLink"><i class="fa-solid fa-plus me-1"></i>Enlace</button></div>
            <div class="directory-editor-list">
              <div v-for="(link, index) in texts.enlaces_relacionados" :key="index" class="directory-editor-row">
                <input v-model="link.nombre" class="form-control" maxlength="120" placeholder="Nombre de la página">
                <input v-model="link.url" type="url" class="form-control" placeholder="https://...">
                <button type="button" class="icon-button icon-button--danger" @click="texts.enlaces_relacionados.splice(index, 1)"><i class="fa-solid fa-trash"></i></button>
              </div>
            </div>
          </div>
          <div class="section-preview-action"><button type="button" class="btn btn-outline-danger" @click="refreshPreview('footer')"><i class="fa-solid fa-eye me-2"></i>Previsualizar cambios del pie</button></div>
        </section>

        <footer class="editor-footer">
          <div><strong>¿Terminaste de ordenar la portada?</strong><span>Revisa el resultado antes de publicar los cambios.</span></div>
          <div class="editor-footer__actions">
            <button class="btn btn-danger" :disabled="saving" @click="save"><i class="fa-solid fa-floppy-disk me-2"></i>{{ saving ? 'Guardando...' : 'Guardar cambios' }}</button>
          </div>
        </footer>
      </div>
      </div>

      <aside v-if="!loading" class="live-preview-panel">
        <header><div><p>Vista de escritorio</p><h2>Previsualización</h2></div><span>Landing page</span></header>
        <div ref="livePreview" class="live-preview-viewport">
          <div class="mini-page">
            <header class="mini-site-header">
              <div class="mini-site-brand"><img :src="brandLogo" alt="Cruz Roja"><span><small>Cruz Roja</small><strong>Ven a conocer nuestras novedades</strong></span></div>
              <span class="mini-profile-button"><i class="fa-solid fa-user"></i> Mi perfil</span>
            </header>
            <div ref="previewSlidesSection" class="mini-hero">
              <template v-if="visiblePreviewSlides[0]?.imagenes?.filter(Boolean).length">
                <div class="mini-hero__images" :style="{ gridTemplateColumns: `repeat(${visiblePreviewSlides[0].imagenes.filter(Boolean).length}, 1fr)` }">
                  <span v-for="(id, imageIndex) in visiblePreviewSlides[0].imagenes.filter(Boolean)" :key="id" class="mini-image-cell"><img :src="imageById(id)?.url_publica" :style="imagePosition(visiblePreviewSlides[0].posiciones_x[imageIndex], visiblePreviewSlides[0].posiciones_y[imageIndex], visiblePreviewSlides[0].zooms[imageIndex])" alt=""></span>
                </div>
                <div v-if="previewTexts.carrusel_etiqueta || visiblePreviewSlides[0].titulo || visiblePreviewSlides[0].bajada" class="mini-hero__copy" :style="{ '--carousel-title-color': visiblePreviewSlides[0].titulo_color || previewTexts.carrusel_texto_color, '--carousel-subtitle-color': visiblePreviewSlides[0].bajada_color || previewTexts.carrusel_texto_color, '--carousel-label-color': previewTexts.carrusel_etiqueta_color }"><small v-if="previewTexts.carrusel_etiqueta">{{ previewTexts.carrusel_etiqueta }}</small><h1 v-if="visiblePreviewSlides[0].titulo">{{ visiblePreviewSlides[0].titulo }}</h1><p v-if="visiblePreviewSlides[0].bajada">{{ visiblePreviewSlides[0].bajada }}</p></div>
              </template>
              <div v-else class="mini-placeholder"><i class="fa-regular fa-images"></i><span>El carrusel aparecerá aquí</span></div>
            </div>
            <section v-if="previewTexts.novedades_etiqueta || previewTexts.novedades_titulo || previewTexts.novedades_descripcion" class="mini-intro" :style="{ '--news-label-color': previewTexts.novedades_etiqueta_color, '--news-title-color': previewTexts.novedades_titulo_color, '--news-description-color': previewTexts.novedades_descripcion_color }"><small v-if="previewTexts.novedades_etiqueta">{{ previewTexts.novedades_etiqueta }}</small><h2 v-if="previewTexts.novedades_titulo">{{ previewTexts.novedades_titulo }}</h2><p v-if="previewTexts.novedades_descripcion">{{ previewTexts.novedades_descripcion }}</p></section>
            <section ref="previewNewsSection" class="mini-news">
              <article v-for="(item, index) in visiblePreviewNews" :key="item.localId" class="mini-news--completo" :class="{ 'mini-news--reverse': index % 2 === 1 }">
                <span v-if="imageById(item.archivo_portada_id)" class="mini-news__image"><img :src="imageById(item.archivo_portada_id).url_publica" :style="imagePosition(item.posicion_x, item.posicion_y, item.zoom)" alt=""></span>
                <div class="mini-news__body"><h3 v-if="item.titulo">{{ item.titulo }}</h3><p v-if="item.resumen">{{ item.resumen }}</p><dl><template v-for="field in item.campos_visibles || []" :key="field"><div v-if="previewFieldValue(item, field)"><dt><i :class="previewFieldMeta(field).icon"></i>{{ previewFieldMeta(field).label }}</dt><dd>{{ previewFieldValue(item, field) }}</dd></div></template></dl><p v-if="item.contenido" class="mini-news__content">{{ item.contenido }}</p></div>
              </article>
              <div v-if="!visiblePreviewNews.length" class="mini-placeholder mini-placeholder--news"><i class="fa-regular fa-newspaper"></i><span>Las novedades aparecerán aquí</span></div>
            </section>
            <footer ref="previewFooterSection" class="mini-footer">
              <div class="mini-footer__logo"><router-link to="/portada" custom v-slot="{ navigate }"><img :src="brandLogo" alt="Cruz Roja" role="link" tabindex="0" @click="navigate" @keydown.enter="navigate"></router-link></div>
              <div class="mini-footer__contact"><h3>Dirección</h3><a v-if="previewTexts.direccion" :href="locationUrl(previewTexts)">{{ previewTexts.direccion }}</a><template v-if="previewTexts.horario_atencion"><h3>Horario de atención</h3><p>{{ previewTexts.horario_atencion }}</p></template></div>
              <div><h3>Redes sociales y contacto</h3><div class="mini-socials"><a v-if="previewTexts.instagram_url" :href="previewTexts.instagram_url"><i class="fa-brands fa-instagram"></i></a><a v-if="previewTexts.facebook_url" :href="previewTexts.facebook_url"><i class="fa-brands fa-facebook-f"></i></a><a v-if="previewTexts.telefono" :href="`tel:${previewTexts.telefono}`"><i class="fa-solid fa-phone"></i></a></div><div class="mini-contact-links"><a v-if="previewTexts.telefono" :href="`tel:${previewTexts.telefono}`"><i class="fa-solid fa-phone"></i> {{ previewTexts.telefono }}</a><a v-if="previewTexts.correo_contacto" :href="`mailto:${previewTexts.correo_contacto}`"><i class="fa-solid fa-envelope"></i> {{ previewTexts.correo_contacto }}</a></div></div>
              <div class="mini-footer__related"><h3>Páginas relacionadas</h3><a v-for="link in previewTexts.enlaces_relacionados || []" :key="link.url" :href="link.url">{{ link.nombre }} <i class="fa-solid fa-arrow-up-right-from-square"></i></a></div>
            </footer>
          </div>
        </div>
        <div class="preview-navigation">
          <button type="button" title="Subir en la previsualización" @click="scrollPreview(-1)"><i class="fa-solid fa-chevron-up"></i></button>
          <button type="button" title="Bajar en la previsualización" @click="scrollPreview(1)"><i class="fa-solid fa-chevron-down"></i></button>
        </div>
      </aside>
    </main>

    <div v-if="colorModalOpen" class="modal-layer" @click.self="closeTextColorPicker">
      <section class="color-modal" role="dialog" aria-modal="true" :aria-label="`Seleccionar color para ${colorTargetLabel}`">
        <header><div><p>Paleta de colores</p><h2>{{ colorTargetLabel }}</h2><span>Escoge un color institucional o define uno personalizado.</span></div><button type="button" class="modal-close" @click="closeTextColorPicker"><i class="fa-solid fa-xmark"></i></button></header>
        <div class="color-modal__body">
          <div class="color-modal__palette">
            <button v-for="color in brandTextColors" :key="color.value" type="button" :class="{ active: currentTextColor.toLowerCase() === color.value.toLowerCase() }" @click="selectTextColor(color.value)"><i :style="{ backgroundColor: color.value }"></i><span><strong>{{ color.name }}</strong><small>{{ color.value.toUpperCase() }}</small></span><i v-if="currentTextColor.toLowerCase() === color.value.toLowerCase()" class="fa-solid fa-check color-check"></i></button>
          </div>
          <label class="custom-color-field">Color personalizado<span><input :value="currentTextColor" type="color" @input="selectTextColor($event.target.value)"><input :value="currentTextColor" class="form-control" maxlength="7" pattern="#[0-9A-Fa-f]{6}" @input="updateCustomTextColor($event.target.value)"></span></label>
        </div>
        <footer><button type="button" class="btn btn-danger" @click="closeTextColorPicker"><i class="fa-solid fa-check me-2"></i>Listo</button></footer>
      </section>
    </div>

    <div v-if="pickerOpen" class="modal-layer" @click.self="closePicker">
      <section class="gallery-modal" role="dialog" aria-modal="true" aria-label="Seleccionar fotografía">
        <header><div><p>Galerías y álbumes</p><h2>Seleccionar fotografía</h2></div><button class="modal-close" @click="closePicker"><i class="fa-solid fa-xmark"></i></button></header>
        <div class="device-upload-row">
          <div><strong>Cargar desde este dispositivo</strong><span>JPG, PNG o WebP · máximo 5 MB por imagen</span></div>
          <input ref="deviceImageInput" type="file" accept="image/jpeg,image/png,image/webp" multiple hidden @change="uploadDeviceImages">
          <button type="button" class="btn btn-danger" :disabled="uploadingImages" @click="$refs.deviceImageInput.click()"><i class="fa-solid fa-arrow-up-from-bracket me-2"></i>{{ uploadingImages ? 'Subiendo…' : 'Elegir imágenes' }}</button>
        </div>
        <div class="gallery-filters">
          <select v-model="galleryActivity" class="form-select"><option value="">Todas las actividades</option><option v-for="activity in activities" :key="activity.id" :value="String(activity.id)">{{ activity.nombre }}</option></select>
          <div class="search-box"><i class="fa-solid fa-magnifying-glass"></i><input v-model="gallerySearch" class="form-control" placeholder="Buscar actividad, álbum o nombre de foto"></div>
        </div>
        <div class="gallery-results">
          <button v-for="image in paginatedImages" :key="image.id" type="button" class="gallery-photo" @click="chooseImage(image.id)">
            <img :src="image.url_publica" :alt="image.nombre_original || 'Fotografía'">
            <span><strong>{{ image.nombre_original || `Fotografía ${image.id}` }}</strong><small>{{ image.actividad_nombre }}</small><small><i class="fa-regular fa-folder"></i> {{ image.album_nombre }}</small></span>
          </button>
          <div v-if="!filteredImages.length" class="gallery-empty"><i class="fa-regular fa-images"></i><p>No se encontraron fotografías con esos filtros.</p></div>
        </div>
        <nav v-if="galleryTotalPages > 1" class="gallery-pagination" aria-label="Páginas de fotografías">
          <button type="button" :disabled="galleryPage === 1" aria-label="Página anterior" @click="galleryPage--"><i class="fa-solid fa-chevron-left"></i></button>
          <span>Página <strong>{{ galleryPage }}</strong> de {{ galleryTotalPages }}</span>
          <button type="button" :disabled="galleryPage === galleryTotalPages" aria-label="Página siguiente" @click="galleryPage++"><i class="fa-solid fa-chevron-right"></i></button>
        </nav>
      </section>
    </div>

    <div v-if="previewOpen" class="modal-layer" @click.self="previewOpen = false">
      <section class="preview-modal" role="dialog" aria-modal="true" aria-label="Vista previa de portada">
        <header><div><p>Sin necesidad de guardar</p><h2>Vista previa de la portada</h2></div><button class="modal-close" @click="previewOpen = false"><i class="fa-solid fa-xmark"></i></button></header>
        <div class="preview-content preview-content--landing">
          <div class="mini-page mini-page--modal">
            <header class="mini-site-header"><div class="mini-site-brand"><img :src="brandLogo" alt="Cruz Roja"><span><small>Cruz Roja</small><strong>Ven a conocer nuestras novedades</strong></span></div><span class="mini-profile-button"><i class="fa-solid fa-user"></i> Mi perfil</span></header>
            <div class="mini-hero">
              <template v-if="visibleEditorSlides[0]?.imagenes?.filter(Boolean).length"><div class="mini-hero__images" :style="{ gridTemplateColumns: `repeat(${visibleEditorSlides[0].imagenes.filter(Boolean).length}, 1fr)` }"><span v-for="(id, imageIndex) in visibleEditorSlides[0].imagenes.filter(Boolean)" :key="id" class="mini-image-cell"><img :src="imageById(id)?.url_publica" :style="imagePosition(visibleEditorSlides[0].posiciones_x[imageIndex], visibleEditorSlides[0].posiciones_y[imageIndex], visibleEditorSlides[0].zooms[imageIndex])" alt=""></span></div><div v-if="texts.carrusel_etiqueta || visibleEditorSlides[0].titulo || visibleEditorSlides[0].bajada" class="mini-hero__copy" :style="{ '--carousel-title-color': visibleEditorSlides[0].titulo_color || texts.carrusel_texto_color, '--carousel-subtitle-color': visibleEditorSlides[0].bajada_color || texts.carrusel_texto_color, '--carousel-label-color': texts.carrusel_etiqueta_color }"><small v-if="texts.carrusel_etiqueta">{{ texts.carrusel_etiqueta }}</small><h1 v-if="visibleEditorSlides[0].titulo">{{ visibleEditorSlides[0].titulo }}</h1><p v-if="visibleEditorSlides[0].bajada">{{ visibleEditorSlides[0].bajada }}</p></div></template>
              <div v-else class="mini-placeholder"><i class="fa-regular fa-images"></i><span>El carrusel aparecerá aquí</span></div>
            </div>
            <section v-if="texts.novedades_etiqueta || texts.novedades_titulo || texts.novedades_descripcion" class="mini-intro" :style="{ '--news-label-color': texts.novedades_etiqueta_color, '--news-title-color': texts.novedades_titulo_color, '--news-description-color': texts.novedades_descripcion_color }"><small v-if="texts.novedades_etiqueta">{{ texts.novedades_etiqueta }}</small><h2 v-if="texts.novedades_titulo">{{ texts.novedades_titulo }}</h2><p v-if="texts.novedades_descripcion">{{ texts.novedades_descripcion }}</p></section>
            <section class="mini-news"><article v-for="(item, index) in visibleEditorNews" :key="item.localId" class="mini-news--completo" :class="{ 'mini-news--reverse': index % 2 === 1 }"><span v-if="imageById(item.archivo_portada_id)" class="mini-news__image"><img :src="imageById(item.archivo_portada_id).url_publica" :style="imagePosition(item.posicion_x, item.posicion_y, item.zoom)" alt=""></span><div class="mini-news__body"><h3 v-if="item.titulo">{{ item.titulo }}</h3><p v-if="item.resumen">{{ item.resumen }}</p><dl><template v-for="field in item.campos_visibles || []" :key="field"><div v-if="previewFieldValue(item, field)"><dt><i :class="previewFieldMeta(field).icon"></i>{{ previewFieldMeta(field).label }}</dt><dd>{{ previewFieldValue(item, field) }}</dd></div></template></dl><p v-if="item.contenido" class="mini-news__content">{{ item.contenido }}</p></div></article><div v-if="!visibleEditorNews.length" class="mini-placeholder mini-placeholder--news"><i class="fa-regular fa-newspaper"></i><span>Las novedades aparecerán aquí</span></div></section>
            <footer class="mini-footer"><div class="mini-footer__logo"><img :src="brandLogo" alt="Cruz Roja"></div><div class="mini-footer__contact"><h3>Dirección</h3><a v-if="texts.direccion" :href="locationUrl(texts)">{{ texts.direccion }}</a><template v-if="texts.horario_atencion"><h3>Horario de atención</h3><p>{{ texts.horario_atencion }}</p></template></div><div><h3>Redes sociales y contacto</h3><div class="mini-socials"><a v-if="texts.instagram_url" :href="texts.instagram_url"><i class="fa-brands fa-instagram"></i></a><a v-if="texts.facebook_url" :href="texts.facebook_url"><i class="fa-brands fa-facebook-f"></i></a><a v-if="texts.telefono" :href="`tel:${texts.telefono}`"><i class="fa-solid fa-phone"></i></a></div><div class="mini-contact-links"><a v-if="texts.telefono" :href="`tel:${texts.telefono}`"><i class="fa-solid fa-phone"></i> {{ texts.telefono }}</a><a v-if="texts.correo_contacto" :href="`mailto:${texts.correo_contacto}`"><i class="fa-solid fa-envelope"></i> {{ texts.correo_contacto }}</a></div></div><div class="mini-footer__related"><h3>Páginas relacionadas</h3><a v-for="link in texts.enlaces_relacionados || []" :key="link.url" :href="link.url">{{ link.nombre }} <i class="fa-solid fa-arrow-up-right-from-square"></i></a></div></footer>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import SidebarMenu from '../components/SidebarMenu.vue'
import logoHorizontal from '@/assets/LogoHorizontal.svg'
import { buildApiUrl } from '../config/api'
import { optimizeImages } from '../utils/imageOptimization'

export default {
  name: 'PortadaEditorView', components: { SidebarMenu },
  data: () => ({ brandLogo: logoHorizontal, loading: true, saving: false, uploadingImages: false, activities: [], images: [], slides: [], news: [], texts: { carrusel_etiqueta: 'Historias que nos unen', carrusel_texto_color: '#ffffff', carrusel_etiqueta_color: '#ffffff', novedades_etiqueta: 'Actualidad de nuestra comunidad', novedades_etiqueta_color: '#d72732', novedades_titulo: 'Novedades de Cruz Roja', novedades_titulo_color: '#011e41', novedades_descripcion: 'Conoce las actividades y el impacto de nuestros voluntarios.', novedades_descripcion_color: '#5f6b7c', telefono: '', correo_contacto: '', horario_atencion: '', instagram_url: '', facebook_url: '', direccion: '', ubicacion_url: '', directorio: [], enlaces_relacionados: [{ nombre: 'Cruz Roja Chilena', url: 'https://www.cruzroja.cl' }] }, previewTexts: {}, previewSlides: [], previewNews: [], sequence: 0, pickerOpen: false, previewOpen: false, colorModalOpen: false, colorTarget: null, colorTargetObject: null, colorTargetLabel: '', pickerTarget: null, galleryActivity: '', gallerySearch: '', galleryPage: 1, galleryPageSize: 12, dragState: null,
    brandTextColors: [{ name: 'Blanco', value: '#ffffff' }, { name: 'Rojo Cruz Roja', value: '#f5333f' }, { name: 'Azul institucional', value: '#011e41' }, { name: 'Negro', value: '#000000' }, { name: 'Gris claro', value: '#dfe5ee' }, { name: 'Gris medio', value: '#6d7a8f' }],
    availableFields: [{ value: 'objetivo', label: 'Objetivo' }, { value: 'fecha', label: 'Fecha' }, { value: 'lugar', label: 'Lugar' }, { value: 'voluntarios', label: 'Voluntarios participantes' }, { value: 'personas_ayudadas', label: 'Beneficiarios' }, { value: 'filial', label: 'Filial' }, { value: 'tipo', label: 'Tipo' }] }),
  computed: {
    currentTextColor() { return this.colorTargetObject?.[this.colorTarget] || '#000000' },
    filteredImages() {
      const query = this.gallerySearch.trim().toLocaleLowerCase('es')
      return this.images.filter(image => {
        const activityMatches = !this.galleryActivity || image.origen === 'dispositivo' || String(image.actividad_id) === this.galleryActivity
        const text = `${image.nombre_original || ''} ${image.actividad_nombre || ''} ${image.album_nombre || ''}`.toLocaleLowerCase('es')
        return activityMatches && (!query || text.includes(query))
      })
    },
    galleryTotalPages() { return Math.max(1, Math.ceil(this.filteredImages.length / this.galleryPageSize)) },
    paginatedImages() { const start = (this.galleryPage - 1) * this.galleryPageSize; return this.filteredImages.slice(start, start + this.galleryPageSize) },
    visiblePreviewSlides() { return this.previewSlides.filter(item => item.publicada !== false) },
    visiblePreviewNews() { return this.previewNews.filter(item => item.publicada !== false) },
    visibleEditorSlides() { return this.slides.filter(item => item.publicada !== false) },
    visibleEditorNews() { return this.news.filter(item => item.publicada !== false) }
  },
  watch: {
    galleryActivity() { this.galleryPage = 1 },
    gallerySearch() { this.galleryPage = 1 }
  },
  async mounted() {
    try {
      const { data } = await axios.get(buildApiUrl('portada/opciones'))
      this.activities = data.actividades || []; this.images = data.imagenes || []
      this.texts = { ...this.texts, ...(data.configuracion?.textos || {}) }
      this.slides = (data.configuracion?.carrusel || []).map(s => ({ ...s, titulo_color: s.titulo_color || '#ffffff', bajada_color: s.bajada_color || '#ffffff', localId: ++this.sequence, imagenes: s.imagenes.map(i => i.id), posiciones_x: s.imagenes.map(i => i.pivot?.posicion_x ?? 50), posiciones_y: s.imagenes.map(i => i.pivot?.posicion_y ?? 50), zooms: s.imagenes.map(i => i.pivot?.zoom ?? 100) }))
      this.news = (data.configuracion?.novedades || []).map(n => ({ ...n, ancho: 'completo', localId: ++this.sequence, archivo_portada_id: n.archivo_portada_id || null, posicion_x: n.posicion_x ?? 50, posicion_y: n.posicion_y ?? 50, zoom: n.zoom ?? 100, campos_visibles: n.campos_visibles || [] }))
      this.previewSlides = this.cloneContent(this.slides)
      this.previewNews = this.cloneContent(this.news)
      this.previewTexts = this.buildPreviewTexts()
    } catch (error) { window.alert(error.response?.data?.message || 'No fue posible cargar el editor.') } finally { this.loading = false }
  },
  methods: {
    openTextColorPicker(field, label) { this.openColorPicker(this.texts, field, label) },
    openSlideColorPicker(slide, field, label) { this.openColorPicker(slide, field, label) },
    openColorPicker(target, field, label) { this.colorTargetObject = target; this.colorTarget = field; this.colorTargetLabel = label; this.colorModalOpen = true },
    closeTextColorPicker() { this.colorModalOpen = false; this.colorTarget = null; this.colorTargetObject = null; this.colorTargetLabel = '' },
    selectTextColor(value) { if (this.colorTargetObject && this.colorTarget) this.colorTargetObject[this.colorTarget] = value.toLowerCase() },
    updateCustomTextColor(value) { if (/^#[0-9a-fA-F]{6}$/.test(value)) this.selectTextColor(value) },
    imageById(id) { return this.images.find(i => i.id === id) },
    activityById(id) { return this.activities.find(activity => activity.id === id) },
    activityImages(id) { return this.images.filter(i => i.actividad_id === id) },
    previewFieldMeta(field) {
      return {
        objetivo: { label: 'Objetivo', icon: 'fa-solid fa-bullseye' },
        fecha: { label: 'Cuándo', icon: 'fa-regular fa-calendar' },
        lugar: { label: 'Dónde', icon: 'fa-solid fa-location-dot' },
        voluntarios: { label: 'Voluntarios', icon: 'fa-solid fa-people-group' },
        personas_ayudadas: { label: 'Beneficiarios', icon: 'fa-solid fa-hand-holding-heart' },
        filial: { label: 'Filial', icon: 'fa-solid fa-building' },
        tipo: { label: 'Tipo', icon: 'fa-solid fa-tag' }
      }[field] || { label: field, icon: 'fa-solid fa-circle-info' }
    },
    previewFieldValue(item, field) {
      const activity = this.activityById(item.actividad_id)
      if (!activity) return ''
      if (field === 'personas_ayudadas') return item.personas_ayudadas === null || item.personas_ayudadas === undefined || item.personas_ayudadas === '' ? '' : `${item.personas_ayudadas} personas`
      if (field === 'objetivo') return activity.objetivo
      if (field === 'lugar') return activity.lugar
      if (field === 'tipo') return activity.tipo
      if (field === 'filial') return activity.filial?.nombre || activity.filial_nombre
      if (field === 'voluntarios') return `${activity.voluntarios?.length || 0} voluntarios participaron`
      if (field === 'fecha') {
        const start = this.formatPreviewDate(activity.fecha_inicio)
        const end = this.formatPreviewDate(activity.fecha_termino)
        return end && end !== start ? `${start} al ${end}` : start
      }
      return ''
    },
    formatPreviewDate(value) {
      if (!value) return ''
      return new Intl.DateTimeFormat('es-CL', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(value))
    },
    addSlide() { this.slides.push({ localId: ++this.sequence, titulo: '', titulo_color: '#ffffff', bajada: '', bajada_color: '#ffffff', publicada: true, imagenes: [this.images[0]?.id], posiciones_x: [50, 50, 50], posiciones_y: [50, 50, 50], zooms: [100, 100, 100] }) },
    addNews() { const a = this.activities[0]; if (!a) return; this.news.push({ localId: ++this.sequence, actividad_id: a.id, archivo_portada_id: a.imagenes?.[0]?.id || null, posicion_x: 50, posicion_y: 50, zoom: 100, titulo: a.nombre, resumen: a.objetivo || '', personas_ayudadas: null, ancho: 'completo', publicada: true, campos_visibles: ['objetivo', 'fecha', 'lugar', 'voluntarios', 'personas_ayudadas'] }) },
    activityChanged(item) { const a = this.activities.find(x => x.id === item.actividad_id); item.titulo = a?.nombre || ''; item.resumen = a?.objetivo || ''; item.archivo_portada_id = a?.imagenes?.[0]?.id || null },
    compactImages(slide) { slide.imagenes = slide.imagenes.filter(Boolean).slice(0, 3) },
    openImagePicker(type, index, slot = null) {
      this.pickerTarget = { type, index, slot }
      this.gallerySearch = ''
      this.galleryActivity = type === 'news' ? String(this.news[index]?.actividad_id || '') : ''
      this.galleryPage = 1
      this.pickerOpen = true
    },
    closePicker() { this.pickerOpen = false; this.pickerTarget = null },
    async uploadDeviceImages(event) {
      const files = Array.from(event.target.files || [])
      event.target.value = ''
      if (!files.length) return
      if (files.length > 10) return window.alert('Puedes cargar un máximo de 10 imágenes a la vez.')
      const invalid = files.find(file => !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 10 * 1024 * 1024)
      if (invalid) return window.alert('Cada imagen debe ser JPG, PNG o WebP y pesar como máximo 10 MB antes de optimizarla.')

      this.uploadingImages = true
      try {
        const formData = new FormData()
        const croppedFiles = await Promise.all(files.map(file => this.trimUniformDarkSideBars(file)))
        const preparedFiles = await optimizeImages(croppedFiles)
        preparedFiles.forEach(file => formData.append('archivos[]', file))
        const { data } = await axios.post(buildApiUrl('portada/imagenes'), formData)
        const uploaded = data.imagenes || []
        this.images = [...uploaded, ...this.images.filter(image => !uploaded.some(item => item.id === image.id))]
        this.galleryActivity = ''
        this.gallerySearch = ''
        this.galleryPage = 1
      } catch (error) {
        const validationMessage = Object.values(error.response?.data?.errors || {}).flat()[0]
        window.alert(validationMessage || error.response?.data?.message || 'No fue posible cargar las imágenes.')
      } finally {
        this.uploadingImages = false
      }
    },
    async trimUniformDarkSideBars(file) {
      if (typeof createImageBitmap !== 'function') return file
      try {
        const bitmap = await createImageBitmap(file)
        const canvas = document.createElement('canvas')
        canvas.width = bitmap.width
        canvas.height = bitmap.height
        const context = canvas.getContext('2d', { willReadFrequently: true })
        context.drawImage(bitmap, 0, 0)
        bitmap.close()
        const pixels = context.getImageData(0, 0, canvas.width, canvas.height).data
        const sampleStep = Math.max(1, Math.floor(canvas.height / 120))
        const isUniformDarkColumn = x => {
          let samples = 0
          let dark = 0
          for (let y = 0; y < canvas.height; y += sampleStep) {
            const offset = (y * canvas.width + x) * 4
            samples++
            if (pixels[offset] < 48 && pixels[offset + 1] < 48 && pixels[offset + 2] < 48 && pixels[offset + 3] > 220) dark++
          }
          return dark / samples >= 0.98
        }
        let left = 0
        let right = canvas.width - 1
        while (left < canvas.width * 0.35 && isUniformDarkColumn(left)) left++
        while (right > canvas.width * 0.65 && isUniformDarkColumn(right)) right--
        const leftBar = left
        const rightBar = canvas.width - 1 - right
        const minimumBar = Math.max(3, Math.round(canvas.width * 0.015))
        const cropLeft = leftBar >= minimumBar ? leftBar : 0
        const cropRight = rightBar >= minimumBar ? rightBar : 0
        if (!cropLeft && !cropRight) return file
        const croppedWidth = canvas.width - cropLeft - cropRight
        if (croppedWidth < canvas.width * 0.6) return file
        const cropped = document.createElement('canvas')
        cropped.width = croppedWidth
        cropped.height = canvas.height
        cropped.getContext('2d').drawImage(canvas, cropLeft, 0, croppedWidth, canvas.height, 0, 0, croppedWidth, canvas.height)
        const blob = await new Promise(resolve => cropped.toBlob(resolve, file.type, 0.94))
        return blob ? new File([blob], file.name, { type: file.type, lastModified: file.lastModified }) : file
      } catch (error) {
        console.warn('No fue posible revisar los bordes de la imagen.', error)
        return file
      }
    },
    chooseImage(id) {
      if (this.pickerTarget?.type === 'slide') {
        const slide = this.slides[this.pickerTarget.index]
        slide.imagenes[this.pickerTarget.slot] = id
        slide.posiciones_x[this.pickerTarget.slot] = 50
        slide.posiciones_y[this.pickerTarget.slot] = 50
        slide.zooms[this.pickerTarget.slot] = 100
      } else if (this.pickerTarget?.type === 'news') {
        this.news[this.pickerTarget.index].archivo_portada_id = id
        this.news[this.pickerTarget.index].posicion_x = 50
        this.news[this.pickerTarget.index].posicion_y = 50
        this.news[this.pickerTarget.index].zoom = 100
      }
      this.closePicker()
    },
    removeSlideImage(slide, slot) { slide.imagenes[slot] = undefined },
    imagePosition(x, y, zoom = 100) { return { objectPosition: `${x ?? 50}% ${y ?? 50}%`, transform: `scale(${(zoom ?? 100) / 100})`, transformOrigin: `${x ?? 50}% ${y ?? 50}%` } },
    adjustArrayZoom(zooms, index, delta) { zooms[index] = Math.max(100, Math.min(250, (zooms[index] || 100) + delta)) },
    adjustObjectZoom(item, delta) { item.zoom = Math.max(100, Math.min(250, (item.zoom || 100) + delta)) },
    carouselFrameStyle(slide, slotIndex) {
      const selectedCount = slide.imagenes.filter(Boolean).length
      const imageCount = slide.imagenes[slotIndex] ? Math.max(selectedCount, 1) : Math.max(selectedCount + 1, slotIndex + 1)
      const slideAspectRatio = 4.36
      return { aspectRatio: String(slideAspectRatio / Math.min(imageCount, 3)) }
    },
    startImageDrag(event, positionsX, positionsY, index) {
      this.beginDrag(event, positionsX[index] ?? 50, positionsY[index] ?? 50, (x, y) => { positionsX[index] = x; positionsY[index] = y })
    },
    startObjectDrag(event, target) {
      this.beginDrag(event, target.posicion_x ?? 50, target.posicion_y ?? 50, (x, y) => { target.posicion_x = x; target.posicion_y = y })
    },
    beginDrag(event, currentX, currentY, update) {
      event.preventDefault()
      event.currentTarget.setPointerCapture?.(event.pointerId)
      this.dragState = {
        pointerId: event.pointerId,
        startX: event.clientX,
        startY: event.clientY,
        startPositionX: currentX,
        startPositionY: currentY,
        width: Math.max(event.currentTarget.clientWidth, 1),
        height: Math.max(event.currentTarget.clientHeight, 1),
        update
      }
    },
    moveImageDrag(event) {
      if (!this.dragState || event.pointerId !== this.dragState.pointerId) return
      event.preventDefault()
      const deltaX = event.clientX - this.dragState.startX
      const delta = event.clientY - this.dragState.startY
      const positionX = Math.max(0, Math.min(100, this.dragState.startPositionX - (deltaX / this.dragState.width) * 100))
      const positionY = Math.max(0, Math.min(100, this.dragState.startPositionY - (delta / this.dragState.height) * 100))
      this.dragState.update(Math.round(positionX), Math.round(positionY))
    },
    endImageDrag(event) {
      if (this.dragState && event.pointerId === this.dragState.pointerId) this.dragState = null
    },
    move(list, index, direction) { const next = index + direction; if (next < 0 || next >= list.length) return; [list[index], list[next]] = [list[next], list[index]] },
    cloneContent(value) { return JSON.parse(JSON.stringify(value || [])) },
    buildPreviewTexts() {
      return this.cloneContent([this.texts])[0]
    },
    locationUrl(settings) { return settings.ubicacion_url || `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(settings.direccion || '')}` },
    addRelatedLink() { if (this.texts.enlaces_relacionados.length < 12) this.texts.enlaces_relacionados.push({ nombre: '', url: '' }) },
    refreshPreview(section) {
      if (section === 'texts' || section === 'footer') this.previewTexts = this.buildPreviewTexts()
      if (section === 'slides') this.previewSlides = this.cloneContent(this.slides)
      if (section === 'news') this.previewNews = this.cloneContent(this.news)
      this.$nextTick(() => {
        const target = section === 'slides' ? this.$refs.previewSlidesSection : section === 'news' ? this.$refs.previewNewsSection : section === 'footer' ? this.$refs.previewFooterSection : this.$refs.previewSlidesSection
        target?.scrollIntoView({ behavior: 'smooth', block: 'start' })
      })
    },
    scrollPreview(direction) {
      this.$refs.livePreview?.scrollBy({ top: direction * 320, behavior: 'smooth' })
    },
    async save() {
      if (this.slides.some(s => !s.imagenes.filter(Boolean).length)) return window.alert('Cada diapositiva necesita al menos una fotografía.')
      this.saving = true
      try {
        await axios.put(buildApiUrl('portada/configuracion'), { textos: this.texts, carrusel: this.slides.map(s => { const selected = s.imagenes.map((id, index) => ({ id, x: s.posiciones_x[index] ?? 50, y: s.posiciones_y[index] ?? 50, zoom: s.zooms[index] ?? 100 })).filter(i => i.id); return { titulo: s.titulo, titulo_color: s.titulo_color || '#ffffff', bajada: s.bajada, bajada_color: s.bajada_color || '#ffffff', publicada: s.publicada !== false, imagenes: selected.map(i => i.id), posiciones_x: selected.map(i => i.x), posiciones_y: selected.map(i => i.y), zooms: selected.map(i => i.zoom) } }), novedades: this.news.map(n => ({ actividad_id: n.actividad_id, archivo_portada_id: n.archivo_portada_id, posicion_x: n.posicion_x ?? 50, posicion_y: n.posicion_y ?? 50, zoom: n.zoom ?? 100, titulo: n.titulo, resumen: n.resumen, contenido: n.contenido || null, personas_ayudadas: n.personas_ayudadas === '' ? null : n.personas_ayudadas, ancho: 'completo', publicada: n.publicada !== false, campos_visibles: n.campos_visibles })) })
        window.alert('La portada fue actualizada correctamente.')
      } catch (error) { window.alert(error.response?.data?.message || 'No fue posible guardar la portada.') } finally { this.saving = false }
    }
  }
}
</script>

<style scoped>
.portada-editor{padding:1.5rem;background:#f4f6f9;min-height:100vh}.editor-header{display:flex;justify-content:space-between;gap:1rem;align-items:center;margin-bottom:1.5rem}.editor-header p,.panel-heading span,.gallery-modal header p,.preview-modal header p{margin:0;color:#e01e1e;font-weight:800;text-transform:uppercase;letter-spacing:.12em;font-size:.75rem}.editor-header h1{margin:.2rem 0;color:#011e41;font-weight:800}.editor-header__actions{display:flex;gap:.7rem}.editor-sections{display:grid;gap:1.5rem}.editor-panel{background:#fff;border-radius:24px;padding:1.3rem;box-shadow:0 12px 30px rgba(1,30,65,.07)}.panel-heading,.card-toolbar{display:flex;justify-content:space-between;gap:1rem;align-items:center}.panel-heading h2{margin:.15rem 0;color:#011e41}.panel-heading p{margin:0;color:#667085}.editor-list{display:grid;gap:1rem;margin-top:1.2rem}.editor-card{border:1px solid #e3e7ee;border-radius:18px;padding:1rem}.card-toolbar{margin-bottom:1rem}.icon-button{border:0;background:#eef1f5;color:#173352;border-radius:9px;width:34px;height:34px;margin-left:.3rem}.icon-button--danger{color:#d72732}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.form-grid--three{grid-template-columns:repeat(3,minmax(0,1fr))}.form-grid label,.image-slot{font-weight:700;color:#344054}.form-control,.form-select{margin-top:.35rem}.image-picker-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem;margin-top:1rem}.image-select-button,.cover-picker{width:100%;height:150px;margin-top:.4rem;border:1px dashed #aab3c2;border-radius:14px;background:#f7f8fa;overflow:hidden;padding:0;color:#526074}.image-select-button img,.cover-picker img{width:100%;height:100%;object-fit:cover}.image-select-button span,.cover-picker span{display:flex;height:100%;align-items:center;justify-content:center;gap:.5rem}.position-control{display:grid!important;gap:.15rem;margin-top:.5rem;font-size:.78rem;color:#536176}.position-control input{width:100%;accent-color:#e01e1e}.position-control small{display:flex;justify-content:space-between;color:#8690a0}.remove-image{border:0;background:none;color:#d72732;font-size:.8rem;margin-top:.3rem}.field-options{display:flex;flex-wrap:wrap;gap:1rem;margin-top:1rem;padding:.8rem;background:#f7f8fa;border-radius:12px}.field-options span{font-weight:800}.empty-note,.editor-state{padding:2rem;text-align:center;color:#667085}.modal-layer{position:fixed;inset:0;z-index:3000;background:rgba(1,18,38,.72);display:grid;place-items:center;padding:1rem}.gallery-modal,.preview-modal{width:min(1100px,96vw);max-height:92vh;background:#fff;border-radius:24px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 30px 80px rgba(0,0,0,.3)}.gallery-modal header,.preview-modal header{display:flex;align-items:center;justify-content:space-between;padding:1.2rem 1.4rem;border-bottom:1px solid #e6e9ef}.gallery-modal h2,.preview-modal h2{margin:.15rem 0 0;color:#011e41}.modal-close{border:0;background:#eef1f5;width:40px;height:40px;border-radius:50%;font-size:1.15rem}.gallery-filters{display:grid;grid-template-columns:320px 1fr;gap:1rem;padding:1rem 1.4rem}.search-box{position:relative}.search-box i{position:absolute;left:1rem;top:50%;transform:translateY(-40%);color:#778195}.search-box input{padding-left:2.7rem}.gallery-results{padding:0 1.4rem 1.4rem;overflow:auto;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem}.gallery-photo{padding:0;border:1px solid #e1e5eb;border-radius:15px;background:#fff;overflow:hidden;text-align:left;transition:.2s}.gallery-photo:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(1,30,65,.14);border-color:#e01e1e}.gallery-photo>img{width:100%;height:155px;object-fit:cover}.gallery-photo>span{display:grid;padding:.75rem;gap:.15rem}.gallery-photo small{color:#68758a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.gallery-empty{grid-column:1/-1;text-align:center;padding:4rem;color:#68758a}.gallery-empty i{font-size:2.5rem}.preview-content{overflow:auto;padding:1.3rem}.preview-hero{position:relative;height:360px;display:grid;overflow:hidden;border-radius:18px;background:#17243a}.preview-hero img{width:100%;height:100%;object-fit:cover;min-width:0}.preview-hero>div{position:absolute;inset:auto 0 0;padding:4rem 2rem 1.5rem;color:#fff;background:linear-gradient(transparent,rgba(1,30,65,.9))}.preview-hero h2{color:#fff}.preview-content>h3{margin:1.5rem 0 1rem}.preview-news{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem}.preview-news article{border:1px solid #e1e5eb;border-radius:14px;overflow:hidden}.preview-news img{width:100%;height:140px;object-fit:cover}.preview-news article div{padding:1rem}.preview-news h4{margin:.3rem 0}.preview-news p{color:#68758a}.preview-empty{text-align:center;padding:4rem;background:#f4f6f9;border-radius:18px;color:#68758a}@media(max-width:900px){.editor-header,.panel-heading{align-items:flex-start;flex-direction:column}.form-grid,.form-grid--three,.image-picker-grid{grid-template-columns:1fr}.editor-header__actions{width:100%}.gallery-filters,.gallery-results,.preview-news{grid-template-columns:1fr}.gallery-results{grid-template-columns:repeat(2,minmax(0,1fr))}}
.draggable-image{position:relative;display:block;width:100%;height:auto;min-height:110px;margin-top:.4rem;border-radius:14px;overflow:hidden;background:#e9edf2;cursor:grab;touch-action:none;user-select:none}.draggable-image:active{cursor:grabbing}.draggable-image>img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;pointer-events:none;user-select:none}.drag-hint{position:absolute;left:50%;bottom:.55rem;transform:translateX(-50%);display:flex!important;align-items:center;gap:.35rem;padding:.35rem .65rem;border-radius:999px;background:rgba(1,30,65,.78);color:#fff;font-size:.72rem;white-space:nowrap;pointer-events:none}.change-image{border:0;background:#eef2f6;color:#173352;border-radius:8px;padding:.35rem .55rem;font-size:.78rem;margin-top:.4rem}.editor-footer{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.3rem 1.5rem;background:#fff;border:1px solid #e1e5eb;border-radius:20px;box-shadow:0 12px 28px rgba(1,30,65,.08)}.editor-footer>div:first-child{display:grid;gap:.2rem}.editor-footer span{color:#68758a}.editor-footer__actions{display:flex;gap:.7rem}@media(max-width:700px){.editor-footer{align-items:stretch;flex-direction:column}.editor-footer__actions{display:grid;grid-template-columns:1fr}.editor-footer__actions .btn{width:100%}}
.portada-editor{display:grid;grid-template-columns:minmax(620px,1fr) minmax(430px,.95fr);align-items:start;gap:1.5rem;max-width:none!important;width:100%}.editor-column{min-width:0}.section-preview-action{display:flex;justify-content:flex-end;margin-top:1.2rem;padding-top:1rem;border-top:1px solid #e7eaf0}.live-preview-panel{position:sticky;top:1.5rem;height:calc(100vh - 3rem);min-width:0;background:#17243a;border-radius:24px;padding:.8rem;display:flex;flex-direction:column;box-shadow:0 20px 50px rgba(1,30,65,.2)}.live-preview-panel>header{display:flex;align-items:center;justify-content:space-between;padding:.7rem .8rem 1rem;color:#fff}.live-preview-panel>header p{margin:0;color:#ff9ba2;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em}.live-preview-panel>header h2{margin:.1rem 0 0;font-size:1.15rem}.live-preview-panel>header>span{padding:.3rem .6rem;border-radius:99px;background:rgba(255,255,255,.12);font-size:.72rem}.live-preview-viewport{flex:1;min-height:0;overflow-y:auto;scroll-behavior:smooth;border-radius:16px;background:#f5f6f8;scrollbar-width:thin}.mini-page{min-height:100%;background:#f6f7f9;color:#011e41}.mini-hero{position:relative;width:100%;height:auto;aspect-ratio:4.36;background:#dfe4eb;overflow:hidden}.mini-hero__images{position:absolute;inset:0;display:grid;gap:2px}.mini-hero__images img{width:100%;height:100%;min-width:0;object-fit:cover}.mini-hero::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,rgba(1,30,65,.72),transparent 80%);pointer-events:none}.mini-hero__copy{position:absolute;z-index:2;left:1.4rem;right:1.4rem;bottom:1rem;color:#fff}.mini-hero__copy small,.mini-intro small{color:#ffbbc0;text-transform:uppercase;letter-spacing:.12em;font-weight:800}.mini-hero__copy h1{margin:.2rem 0;font-size:1.25rem;line-height:1}.mini-hero__copy p{margin:0;font-size:.7rem}.mini-intro{text-align:center;padding:1.8rem 1rem 1rem}.mini-intro small{color:#d72732}.mini-intro h2{margin:.3rem 0;font-size:1.4rem}.mini-intro p{margin:0;color:#657184;font-size:.75rem}.mini-news{padding:.7rem 1.2rem 2rem;display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:.65rem}.mini-news article{grid-column:span 2;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 8px 20px rgba(1,30,65,.08)}.mini-news article.mini-news--mitad{grid-column:span 3}.mini-news article.mini-news--completo{grid-column:span 6;display:grid;grid-template-columns:38% 1fr}.mini-news article>img{width:100%;height:75px;object-fit:cover}.mini-news article.mini-news--completo>img{height:100%}.mini-news article>div{padding:.6rem}.mini-news article small{color:#d72732;text-transform:uppercase;font-weight:800;font-size:.55rem}.mini-news article h3{margin:.2rem 0;font-size:.8rem}.mini-news article p{margin:0;color:#657184;font-size:.62rem;line-height:1.35}.mini-placeholder{position:absolute;inset:0;z-index:2;display:grid;place-content:center;justify-items:center;gap:.5rem;color:#708096;background:#e9edf2}.mini-placeholder i{font-size:2rem}.mini-placeholder--news{position:relative;grid-column:1/-1;min-height:100px;border-radius:14px}.preview-navigation{position:absolute;right:1.1rem;top:50%;transform:translateY(-50%);display:grid;gap:.45rem;z-index:4}.preview-navigation button{width:38px;height:38px;border:1px solid rgba(255,255,255,.3);border-radius:50%;background:rgba(1,30,65,.82);color:#fff;box-shadow:0 5px 12px rgba(0,0,0,.2)}@media(max-width:1350px){.portada-editor{grid-template-columns:minmax(560px,1fr) minmax(370px,.75fr)}.form-grid--three{grid-template-columns:1fr 1fr}}@media(max-width:1050px){.portada-editor{display:block}.live-preview-panel{position:relative;top:auto;height:720px;margin-top:1.5rem}.form-grid--three{grid-template-columns:1fr}.section-preview-action .btn{width:100%}}
.image-picker-grid .image-select-button{height:auto;min-height:110px}
.zoom-controls{position:absolute;top:.5rem;right:.5rem;z-index:3;display:flex!important;align-items:center;gap:.3rem;padding:.25rem;border-radius:999px;background:rgba(1,30,65,.86);color:#fff;font-size:.7rem}.zoom-controls button{width:27px;height:27px;border:0;border-radius:50%;background:#fff;color:#011e41;display:grid;place-items:center}.zoom-controls button:disabled{opacity:.4}.zoom-controls strong{min-width:38px;text-align:center}.mini-image-cell{display:block;min-width:0;height:100%;overflow:hidden}.mini-image-cell img{width:100%;height:100%;object-fit:cover}
.mini-news article.mini-news--completo{min-height:90px}.mini-news article.mini-news--reverse{grid-template-columns:1fr 38%}.mini-news--reverse>img{order:2}.mini-news--reverse>div{order:1;display:flex;flex-direction:column;justify-content:center}
.footer-editor-block{margin-top:1.4rem;padding-top:1.2rem;border-top:1px solid #e6e9ef}.subsection-heading{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:.8rem}.subsection-heading h3{margin:0;font-size:1rem;color:#011e41}.subsection-heading p{margin:.2rem 0 0;color:#68758a;font-size:.85rem}.directory-editor-list{display:grid;gap:.65rem}.directory-editor-row{display:grid;grid-template-columns:minmax(180px,1fr) minmax(180px,1fr) auto;align-items:center;gap:.65rem}.mini-footer{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));column-gap:clamp(.6rem,1.5vw,1rem);row-gap:.65rem;padding:.8rem 1rem;background:#fff;border-top:3px solid #e01e1e}.mini-footer h3{font-size:.66rem;text-transform:uppercase;margin:.35rem 0 .1rem}.mini-footer__logo{grid-column:1/-1}.mini-footer__logo>img{display:block;width:110px;max-width:100%;padding:.25rem;background:#fff;border-radius:5px}.mini-footer__contact{display:flex;flex-direction:column;align-items:flex-start;gap:.18rem}.mini-footer__contact>a{font-size:.55rem;color:#fff;text-decoration:none;line-height:1.3}.mini-socials{display:flex;gap:.25rem}.mini-socials a{width:23px!important;height:23px;border-radius:50%;display:grid!important;place-items:center;background:#fff;color:#e01e1e!important;font-size:.6rem;padding:0!important}.mini-footer__related{display:flex;flex-direction:column;align-items:flex-start;width:100%;gap:.25rem}.mini-footer__related>a{font-size:.52rem;color:#68758a;overflow:hidden;text-overflow:ellipsis}@media(max-width:700px){.directory-editor-row{grid-template-columns:1fr}.mini-footer{grid-template-columns:1fr}.mini-footer__logo{grid-column:auto}.mini-footer__related{width:100%}}
.directory-launcher{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem;border-radius:14px;background:#f7f8fa}.directory-launcher h3{margin:0;color:#011e41}.directory-launcher p{margin:.25rem 0 0;color:#68758a}.directory-modal{width:min(900px,96vw);max-height:90vh;background:#fff;border-radius:24px;overflow:hidden;display:flex;flex-direction:column}.directory-modal>header{display:flex;align-items:flex-start;justify-content:space-between;padding:1.3rem 1.5rem;border-bottom:1px solid #e4e8ee}.directory-modal>header p{margin:0;color:#e01e1e;font-weight:800;text-transform:uppercase;letter-spacing:.12em;font-size:.72rem}.directory-modal>header h2{margin:.2rem 0;color:#011e41}.directory-modal>header span{color:#68758a}.directory-modal__body{padding:1.2rem 1.5rem;overflow:auto;display:grid;gap:.75rem}.directory-modal__row{display:grid;grid-template-columns:48px minmax(190px,1fr) minmax(190px,1fr) auto;align-items:center;gap:.7rem}.directory-person-preview img,.directory-person-preview span{width:48px;height:48px;border-radius:50%;object-fit:cover;background:#edf0f4;display:grid;place-items:center;color:#7b8799}.directory-add-button{min-height:52px;border:1px dashed #e01e1e;border-radius:12px;background:#fff6f6;color:#d21d28;font-weight:700}.directory-modal>footer{display:flex;justify-content:flex-end;padding:1rem 1.5rem;border-top:1px solid #e4e8ee}.mini-footer{background:#e01e1e;color:#fff;border-top-color:#b5121b}.mini-footer h3,.mini-footer strong,.mini-footer span,.mini-footer a,.mini-footer article small{color:#fff}.mini-socials a{background:#fff;color:#e01e1e}@media(max-width:700px){.directory-launcher{align-items:stretch;flex-direction:column}.directory-modal__row{grid-template-columns:48px 1fr auto}.directory-modal__row input{grid-column:2/-1}}
.mini-related-title{margin-top:.8rem!important}
.mini-footer__contact>p{margin:0;white-space:pre-line;font-size:.58rem;line-height:1.4;color:#fff}
.mini-contact-links{display:grid;gap:.2rem;margin:.15rem 0 .35rem}.mini-contact-links a{font-size:.55rem;color:#fff;text-decoration:none}.mini-contact-links i{width:12px}
.device-upload-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:1rem 1.4rem 0;padding:1rem 1.1rem;border:1px dashed #e01e1e;border-radius:14px;background:#fff6f6}.device-upload-row>div{display:grid;gap:.15rem;color:#344054}.device-upload-row span{color:#68758a;font-size:.8rem}@media(max-width:700px){.device-upload-row{align-items:stretch;flex-direction:column}.device-upload-row .btn{width:100%}}
.gallery-photo>img{height:auto;aspect-ratio:1/1;object-fit:contain;object-position:50% 50%;background:#eef1f5}.gallery-pagination{display:flex;align-items:center;justify-content:center;gap:1rem;padding:.8rem 1.4rem 1.1rem;border-top:1px solid #e6e9ef;color:#526074}.gallery-pagination button{width:38px;height:38px;border:1px solid #d8dee7;border-radius:50%;background:#fff;color:#173352}.gallery-pagination button:not(:disabled):hover{border-color:#e01e1e;color:#e01e1e}.gallery-pagination button:disabled{opacity:.35}.gallery-pagination span{font-size:.85rem}
.mini-intro{padding:1.8rem 1rem 1rem;background:#fff}.mini-hero{background:#e01e1e}.mini-news article{background:#e01e1e;color:#fff}.mini-news article small,.mini-news article h3,.mini-news article p{color:#fff}.mini-footer__logo{justify-self:end}
.mini-footer a:visited{color:#fff}.mini-footer a:hover,.mini-footer a:focus{color:#ffe3e5}.mini-footer .mini-socials a:visited{color:#e01e1e}
.mini-hero::after{background:linear-gradient(180deg,rgba(1,30,65,.55) 0%,rgba(1,30,65,.18) 8%,transparent 22%)}.mini-hero__copy{text-shadow:0 2px 8px rgba(1,30,65,.72)}
.mini-hero__copy small{color:var(--carousel-label-color,#fff)!important}.mini-hero__copy h1{color:var(--carousel-title-color,#fff)!important}.mini-hero__copy p{color:var(--carousel-subtitle-color,#fff)!important}.mini-intro small{color:var(--news-label-color,#d72732)!important}.mini-intro h2{color:var(--news-title-color,#011e41)!important}.mini-intro p{color:var(--news-description-color,#657184)!important}
.color-label-row{display:flex;align-items:center;justify-content:space-between;gap:.7rem}.color-shortcut{display:inline-flex;align-items:center;gap:.4rem;flex:0 0 auto;padding:.3rem .55rem;border:1px solid #d8dee7;border-radius:999px;background:#fff;color:#344054;font-size:.72rem;font-weight:700}.color-shortcut:hover{border-color:#e01e1e}.color-shortcut i{width:20px;height:20px;border-radius:50%;border:1px solid rgba(1,30,65,.2);box-shadow:inset 0 0 0 2px #fff}.color-modal{width:min(620px,96vw);max-height:90vh;background:#fff;border-radius:24px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 30px 80px rgba(0,0,0,.3)}.color-modal>header{display:flex;align-items:flex-start;justify-content:space-between;padding:1.3rem 1.5rem;border-bottom:1px solid #e4e8ee}.color-modal>header p{margin:0;color:#e01e1e;font-weight:800;text-transform:uppercase;letter-spacing:.12em;font-size:.72rem}.color-modal>header h2{margin:.2rem 0;color:#011e41}.color-modal>header span{color:#68758a}.color-modal__body{padding:1.3rem 1.5rem;overflow:auto}.color-modal__palette{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.7rem}.color-modal__palette>button{position:relative;display:flex;align-items:center;gap:.75rem;min-height:64px;padding:.65rem;border:1px solid #d9dee6;border-radius:14px;background:#fff;color:#344054;text-align:left}.color-modal__palette>button:hover,.color-modal__palette>button.active{border-color:#e01e1e;box-shadow:0 0 0 2px rgba(224,30,30,.1)}.color-modal__palette>button>i:first-child{width:40px;height:40px;flex:0 0 auto;border-radius:10px;border:1px solid rgba(1,30,65,.18)}.color-modal__palette>button>span{display:grid}.color-modal__palette small{color:#7b8798;font-weight:500}.color-check{position:absolute;right:.8rem;color:#e01e1e}.custom-color-field{display:grid;gap:.5rem;margin-top:1.2rem;color:#344054;font-weight:700}.custom-color-field>span{display:grid;grid-template-columns:58px 1fr;gap:.7rem}.custom-color-field input[type=color]{width:58px;height:42px;padding:2px;border:1px solid #ced4da;border-radius:8px;background:#fff;cursor:pointer}.custom-color-field .form-control{margin:0;text-transform:uppercase}.color-modal>footer{display:flex;justify-content:flex-end;padding:1rem 1.5rem;border-top:1px solid #e4e8ee}@media(max-width:600px){.color-modal__palette{grid-template-columns:1fr}}
.preview-content--landing{padding:0;background:#f6f7f9}.mini-site-header{min-height:58px;padding:.65rem 1rem;background:#fff;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e7e9ee}.mini-site-brand{display:flex;align-items:center;gap:.65rem;color:#011e41}.mini-site-brand>img{width:95px}.mini-site-brand>span{display:grid}.mini-site-brand small{text-transform:uppercase;letter-spacing:.12em;color:#7a8495;font-size:.48rem}.mini-site-brand strong{font-size:.63rem}.mini-profile-button{display:inline-flex;align-items:center;gap:.35rem;padding:.42rem .65rem;border-radius:999px;background:#e01e1e;color:#fff;font-size:.58rem;font-weight:800}.mini-news{background:#e01e1e}.mini-news article,.mini-news article.mini-news--completo{background:#fff;color:#011e41}.mini-news article small,.mini-news article h3,.mini-news article p{color:inherit}.mini-news__image{display:block;min-width:0;overflow:hidden}.mini-news__image img{width:100%;height:100%;object-fit:cover}.mini-news--reverse .mini-news__image{order:2}.mini-news--reverse .mini-news__body{order:1}.mini-news__body{display:flex;flex-direction:column;justify-content:center}.mini-news .mini-tag{align-self:flex-start;padding:.22rem .48rem;border-radius:99px;background:#ffe9ea;color:#c91f2b;font-size:.48rem}.mini-news__body>p{color:#596579!important}.mini-news dl{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.28rem .55rem;margin:.45rem 0 0}.mini-news dl div{padding-top:.3rem;border-top:1px solid #edf0f3}.mini-news dt{font-size:.45rem;text-transform:uppercase;color:#778195}.mini-news dt i{width:14px;color:#e01e1e}.mini-news dd{margin:.1rem 0 0;font-size:.56rem;font-weight:700;color:#596579}.mini-footer__logo img{display:block;width:110px;max-width:100%;padding:.25rem;background:#fff;border-radius:5px}.mini-page--modal{font-size:1.35rem}.mini-page--modal .mini-site-header{min-height:76px;padding:.8rem 1.5rem}.mini-page--modal .mini-site-brand>img{width:145px}.mini-page--modal .mini-site-brand small{font-size:.55rem}.mini-page--modal .mini-site-brand strong{font-size:.8rem}.mini-page--modal .mini-profile-button{font-size:.72rem;padding:.55rem .85rem}.mini-page--modal .mini-hero{height:310px;aspect-ratio:auto}.mini-page--modal .mini-hero__copy{left:3rem;bottom:2rem}.mini-page--modal .mini-hero__copy h1{font-size:2rem}.mini-page--modal .mini-hero__copy p{font-size:.85rem}.mini-page--modal .mini-intro{padding:2.2rem 1rem 1.2rem}.mini-page--modal .mini-intro h2{font-size:2rem}.mini-page--modal .mini-intro p{font-size:.85rem}.mini-page--modal .mini-news{padding:1rem 2.5rem 2.5rem;gap:1rem}.mini-page--modal .mini-news article.mini-news--completo{min-height:210px;border-radius:18px}.mini-page--modal .mini-news article>div{padding:1.25rem}.mini-page--modal .mini-news article h3{font-size:1.25rem}.mini-page--modal .mini-news article p{font-size:.78rem}.mini-page--modal .mini-news dt{font-size:.58rem}.mini-page--modal .mini-news dd{font-size:.72rem}.mini-page--modal .mini-footer{padding:1.3rem 2rem}.mini-page--modal .mini-footer h3{font-size:.75rem}.mini-page--modal .mini-footer__contact>a,.mini-page--modal .mini-footer__contact>p,.mini-page--modal .mini-contact-links a,.mini-page--modal .mini-footer__related>a{font-size:.65rem}@media(max-width:700px){.mini-site-brand>span{display:none}.mini-page--modal{font-size:1rem}.mini-page--modal .mini-hero{height:220px}.mini-page--modal .mini-news{padding:1rem}.mini-page--modal .mini-news article.mini-news--completo,.mini-page--modal .mini-news article.mini-news--reverse{display:block;min-height:0}.mini-page--modal .mini-news__image{height:170px}.mini-page--modal .mini-news__image,.mini-page--modal .mini-news__body{order:initial}.mini-news dl{grid-template-columns:1fr}}
.mini-news__body{justify-content:flex-start}.mini-news article h3{font-size:1rem;line-height:1.08;margin:0 0 .35rem}.mini-page--modal .mini-news article h3{font-size:1.55rem;margin:0 0 .55rem}
.mini-socials a{width:30px!important;height:30px;font-size:.9rem}.mini-socials a i{font-size:.9rem}.mini-page--modal .mini-socials a{width:44px!important;height:44px;font-size:1.25rem}.mini-page--modal .mini-socials a i{font-size:1.25rem}
</style>
