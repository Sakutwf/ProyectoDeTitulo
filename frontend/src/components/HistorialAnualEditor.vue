<template>
  <div class="editor-modal">
    <section class="editor-panel" role="dialog" aria-modal="true" :aria-labelledby="dialogTitleId">
      <div class="editor-panel__header">
        <div>
          <p class="editor-kicker">{{ panelKicker }}</p>
          <h3 :id="dialogTitleId">{{ panelTitle }}</h3>
          <p v-if="panelDescription" class="editor-description">{{ panelDescription }}</p>
        </div>

        <div class="editor-actions">
          <span v-if="isSectionModal" class="period-chip">
            Periodo {{ form.anio || props.activeYear || props.record?.anio || 'sin anio' }}
          </span>
          <button
            type="button"
            class="editor-close"
            :disabled="isSubmitting"
            aria-label="Cerrar modal"
            @click="requestClose"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>

      <form class="editor-grid" @submit.prevent="submit">
        <article v-if="shouldShowPersonalSection" class="editor-card editor-card--section">
          <div class="section-header">
            <div>
              <h4>Datos personales del voluntario</h4>
              <p class="section-note">Edita aqui la ficha personal que hoy se mantiene en el perfil del voluntario.</p>
            </div>
          </div>

          <div class="form-grid personal-profile-grid">
            <label>
              <span>Numero de registro</span>
              <input v-model.trim="form.registro_filial" type="text" class="form-control" required>
            </label>
            <label>
              <span>RUT</span>
              <input v-model.trim="form.rut" type="text" class="form-control" required>
            </label>
            <label>
              <span>Filial</span>
              <select v-model="form.filial_id" class="form-control" required>
                <option value="">Selecciona una filial</option>
                <option v-for="filial in filialesOptions" :key="filial.id" :value="String(filial.id)">
                  {{ filial.nombre }}
                </option>
              </select>
            </label>
            <label>
              <span>Nombres</span>
              <input v-model.trim="form.nombres" type="text" class="form-control" required>
            </label>
            <label>
              <span>Apellidos</span>
              <input v-model.trim="form.apellidos" type="text" class="form-control" required>
            </label>
            <label>
              <span>Correo electronico</span>
              <input v-model.trim="form.correo_electronico" type="email" class="form-control">
            </label>
            <label>
              <span>Celular</span>
              <input v-model.trim="form.celular" type="text" class="form-control">
            </label>
            <label>
              <span>Nacionalidad</span>
              <input v-model.trim="form.nacionalidad" type="text" class="form-control">
            </label>
            <label>
              <span>Fecha de nacimiento</span>
              <input v-model="form.fecha_nacimiento" type="date" class="form-control">
            </label>
            <label>
              <span>Fecha de incorporacion</span>
              <input v-model="form.fecha_incorporacion" type="date" class="form-control">
            </label>
            <label>
              <span>Nivel de escolaridad</span>
              <select v-model="form.nivel_escolaridad" class="form-control">
                <option value="">Selecciona una opcion</option>
                <option v-for="option in escolaridadOptions" :key="option" :value="option">{{ option }}</option>
              </select>
            </label>
            <label>
              <span>Estado civil</span>
              <select v-model="form.estado_civil" class="form-control">
                <option value="">Selecciona una opcion</option>
                <option v-for="option in estadoCivilOptions" :key="option" :value="option">{{ option }}</option>
              </select>
            </label>
            <label>
              <span>Ocupacion</span>
              <input v-model.trim="form.ocupacion" type="text" class="form-control">
            </label>
            <label>
              <span>Grupo sanguineo</span>
              <input v-model.trim="form.grupo_sanguineo" type="text" class="form-control">
            </label>
            <label class="form-grid__wide">
              <span>Domicilio</span>
              <input v-model.trim="form.domicilio" type="text" class="form-control">
            </label>
            <label>
              <span>Contacto de emergencia</span>
              <input v-model.trim="form.contacto_emergencia_nombre" type="text" class="form-control">
            </label>
            <label>
              <span>Numero de emergencia</span>
              <input v-model.trim="form.contacto_emergencia_numero" type="text" class="form-control">
            </label>
            <label class="form-grid__wide">
              <span>Enfermedades</span>
              <textarea v-model.trim="form.enfermedades" class="form-control" rows="3"></textarea>
            </label>
            <label class="form-grid__wide">
              <span>Alergias</span>
              <textarea v-model.trim="form.alergias" class="form-control" rows="3"></textarea>
            </label>
            <label class="form-grid__wide">
              <span>Foto de perfil</span>
              <input type="file" class="form-control" accept=".jpg,.jpeg,.png,.webp" @change="onPhotoSelected">
            </label>
          </div>

          <div v-if="props.volunteer?.foto_perfil_url || form.foto_perfil" class="attachment-row">
            <a
              v-if="props.volunteer?.foto_perfil_url"
              :href="props.volunteer.foto_perfil_url"
              target="_blank"
              rel="noopener"
              class="attachment-link"
            >
              Ver foto actual
            </a>
            <span v-if="form.foto_perfil" class="attachment-badge">
              Nueva foto: {{ form.foto_perfil.name }}
            </span>
          </div>
        </article>

        <article v-if="!isSectionModal" class="editor-card">
          <div class="attendance-card__header">
            <h4>Asistencia del periodo</h4>
            <span v-if="props.record" class="attendance-card__year-badge">{{ activePeriodYearLabel }}</span>
          </div>
          <div v-if="!props.record" class="form-grid attendance-card__year-input-row">
            <label>
              <span>Anio</span>
              <input v-model.trim="form.anio" type="number" min="1900" max="2100" class="form-control" required>
            </label>
          </div>
          <div class="attendance-summary">
            <div class="attendance-summary__header">
              <div>
                <strong>Se calcula sobre {{ formatWholeHours(attendancePreview.requeridas) }} anuales.</strong>
              </div>
              <span class="attendance-summary__badge">
                <span class="attendance-summary__badge-value">{{ formatPercentage(attendancePreview.porcentaje) }}</span>
              </span>
            </div>
            <div class="attendance-summary__layout">
              <div class="attendance-summary__grid">
                <div class="attendance-summary__item">
                  <span>Reuniones en filial</span>
                  <div class="attendance-summary__metric-row">
                    <strong>{{ formatWholeHours(attendancePreview.reuniones) }}</strong>
                    <div class="attendance-summary__actions">
                      <button type="button" class="attendance-summary__action attendance-summary__action--increase" @click="adjustAttendance('reuniones', 1)">+</button>
                      <button type="button" class="attendance-summary__action attendance-summary__action--decrease" @click="adjustAttendance('reuniones', -1)">-</button>
                    </div>
                  </div>
                </div>
                <div class="attendance-summary__item">
                  <span>Actividades de voluntariado</span>
                  <div class="attendance-summary__metric-row">
                    <strong>{{ formatWholeHours(attendancePreview.voluntariado) }}</strong>
                    <div class="attendance-summary__actions">
                      <button type="button" class="attendance-summary__action attendance-summary__action--increase" @click="adjustAttendance('voluntariado', 1)">+</button>
                      <button type="button" class="attendance-summary__action attendance-summary__action--decrease" @click="adjustAttendance('voluntariado', -1)">-</button>
                    </div>
                  </div>
                </div>
                <div class="attendance-summary__item">
                  <span>Horas en filial</span>
                  <div class="attendance-summary__metric-row">
                    <strong>{{ formatWholeHours(attendancePreview.filial) }}</strong>
                    <div class="attendance-summary__actions">
                      <button type="button" class="attendance-summary__action attendance-summary__action--increase" @click="adjustAttendance('filial', 1)">+</button>
                      <button type="button" class="attendance-summary__action attendance-summary__action--decrease" @click="adjustAttendance('filial', -1)">-</button>
                    </div>
                  </div>
                </div>
                <div class="attendance-summary__item">
                  <span>Horas formativas</span>
                  <div class="attendance-summary__metric-row">
                    <strong>{{ formatWholeHours(attendancePreview.formativas) }}</strong>
                    <div class="attendance-summary__actions">
                      <button type="button" class="attendance-summary__action attendance-summary__action--increase" @click="adjustAttendance('formativas', 1)">+</button>
                      <button type="button" class="attendance-summary__action attendance-summary__action--decrease" @click="adjustAttendance('formativas', -1)">-</button>
                    </div>
                  </div>
                </div>
                <div class="attendance-summary__item attendance-summary__item--total-period attendance-summary__item--manual-hours">
                  <span>Horas sin clasificacion</span>
                  <div class="attendance-summary__metric-row">
                    <strong>{{ formatWholeHours(attendancePreview.unclassified) }}</strong>
                    <button
                      type="button"
                      class="attendance-summary__manual-button"
                      :disabled="isSubmitting"
                      @click="openUnclassifiedHoursModal"
                    >
                      Ingresar
                    </button>
                  </div>
                </div>
              </div>
              <div class="attendance-summary__total">
                <span>Total horas efectivas:</span>
                <strong>{{ formatWholeHours(attendancePreview.total) }}</strong>
              </div>
            </div>
            <p v-if="!attendanceCanPreviewBreakdown" class="attendance-summary__note">
              Las horas se completaran automaticamente cuando existan registros del periodo para este voluntario.
            </p>
          </div>
          <input v-model.trim="form.asistencia_anual_ajuste_horas" type="hidden">
        </article>

        <article v-if="!isSectionModal" class="editor-card editor-card--section">
          <div class="section-header">
            <div>
              <h4>Cargo durante el periodo</h4>
              <p class="section-note">Marca esta opcion solo si el voluntario ejercio un cargo durante este anio.</p>
            </div>
            <button
              v-if="form.tiene_cargo_periodo"
              type="button"
              class="btn btn-sm btn-outline-secondary"
              @click="toggleCargoSelector"
            >
              {{ showCargoSelector ? 'Ocultar cargos' : 'Mostrar cargos' }}
            </button>
          </div>

          <label class="section-toggle section-toggle--compact">
            <input v-model="form.tiene_cargo_periodo" type="checkbox">
            <span>El voluntario tuvo un cargo en este periodo</span>
          </label>

          <p v-if="form.tiene_cargo_periodo && selectedCargoLabel && !showCargoSelector" class="section-toggle__hint">
            Cargo seleccionado: <strong>{{ selectedCargoLabel }}</strong>
          </p>
          <p v-else-if="form.tiene_cargo_periodo && !selectedCargoLabel && !showCargoSelector" class="section-toggle__hint">
            Todavia no has seleccionado un cargo para este periodo.
          </p>

          <div v-if="form.tiene_cargo_periodo && showCargoSelector" class="role-grid">
            <label
              v-for="cargo in cargoOptions"
              :key="cargo.key"
              class="role-card"
              :class="{ selected: form.cargo_clave === cargo.key }"
            >
              <input
                :id="`periodo-cargo-${cargo.key}`"
                v-model="form.cargo_clave"
                class="form-check-input"
                type="radio"
                name="periodo-cargo"
                :value="cargo.key"
              >
              <div>
                <div class="fw-semibold">{{ cargo.nombre }}</div>
                <small class="text-muted">{{ cargoDescription(cargo) }}</small>
              </div>
            </label>
          </div>
        </article>

        <article v-if="!isSectionModal" class="editor-card editor-card--section">
          <div class="section-header">
            <div>
              <h4>Comision de servicio</h4>
              <p class="section-note">Completa esta seccion solo si el voluntario estuvo en comision de servicio durante el periodo.</p>
            </div>
          </div>

          <div class="form-grid period-service-grid">
            <label class="checkbox-field">
              <input v-model="form.estuvo_comision_servicio" type="checkbox">
              <span>Estuvo en comision de servicio</span>
            </label>
            <label v-if="form.estuvo_comision_servicio">
              <span>Inicio de comision</span>
              <input v-model="form.comision_fecha_inicio" type="date" class="form-control">
            </label>
            <label v-if="form.estuvo_comision_servicio">
              <span>Termino de comision</span>
              <input v-model="form.comision_fecha_termino" type="date" class="form-control">
            </label>
            <label v-if="form.estuvo_comision_servicio" class="form-grid__wide">
              <span>Lugar de comision</span>
              <input v-model.trim="form.comision_lugar" type="text" class="form-control">
            </label>
            <label v-if="form.estuvo_comision_servicio" class="form-grid__wide">
              <span>Actividad de comision</span>
              <textarea v-model.trim="form.comision_actividad" class="form-control" rows="3"></textarea>
            </label>
          </div>
        </article>        <article
          v-if="shouldShowTitlesSection"
          ref="titlesSectionRef"
          class="editor-card"
          :class="{ 'editor-card--highlight': props.initialSection === 'titles' }"
        >
          <div class="section-header">
            <h4>Titulos</h4>
            <button type="button" class="btn btn-sm btn-outline-danger" @click="addTitleRow">Agregar</button>
          </div>

          <div class="stack-list">
            <div v-for="(title, index) in form.titulos" :key="`title-${index}`" class="stack-item">
              <div class="form-grid">
                <label>
                  <span>Nombre del titulo</span>
                  <input v-model.trim="title.titulo" type="text" class="form-control">
                </label>
                <label>
                  <span>Entregado por</span>
                  <input v-model.trim="title.entregado_por" type="text" class="form-control">
                </label>
                <label>
                  <span>Codigo del titulo</span>
                  <input v-model.trim="title.codigo_titulo" type="text" class="form-control">
                </label>              </div>
              <div class="attachment-stack">
                <div
                  v-for="(attachment, attachmentIndex) in title.archivos_adjuntos"
                  :key="`title-${index}-attachment-${attachment.id ?? attachmentIndex}`"
                  class="attachment-panel"
                >                  <label class="form-grid__wide">
                    <span>{{ attachmentIndex === 0 ? 'Imagen, foto o documento del titulo aprobado' : 'Archivo adicional' }}</span>
                    <input
                      type="file"
                      class="form-control"
                      accept=".jpg,.jpeg,.png,.webp,.pdf"
                      @change="onTitleFileSelected(index, attachmentIndex, $event)"
                    >
                  </label>
                  <p v-if="attachment.error" class="attachment-error">
                    {{ attachment.error }}
                  </p>
                  <div v-if="attachment.file || attachment.url" class="attachment-row">
                    <a
                      v-if="attachment.url"
                      :href="attachment.url"
                      target="_blank"
                      rel="noopener"
                      class="attachment-link"
                    >
                      {{ attachmentDisplayName(attachment, 'Ver respaldo actual') }}
                    </a>
                    <span v-if="attachment.file" class="attachment-badge">
                      Nuevo archivo: {{ attachment.file.name }}
                    </span>
                  </div>
                  <div v-if="hasAttachmentPreview(attachment)" class="attachment-preview">
                    <img
                      v-if="attachmentPreviewKind(attachment) === 'image'"
                      :src="attachmentPreviewUrl(attachment)"
                      :alt="`Vista previa de ${attachmentDisplayName(attachment, 'titulo')}`"
                      class="attachment-preview__image"
                    >
                    <iframe
                      v-else-if="attachmentPreviewKind(attachment) === 'pdf'"
                      :src="attachmentPreviewUrl(attachment)"
                      class="attachment-preview__frame"
                      title="Vista previa del respaldo del titulo"
                    ></iframe>
                    <a
                      :href="attachmentPreviewUrl(attachment)"
                      target="_blank"
                      rel="noopener"
                      class="attachment-preview__open"
                    >
                      Abrir vista completa
                    </a>
                  </div>
                </div>
                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary attachment-add-button"
                  @click="addTitleAttachment(index)"
                >
                  Archivo adicional
                </button>
              </div>
              <button
  type="button"
  class="icon-delete-button"
  @click="removeTitleRow(index)"
  :aria-label="`Eliminar titulo ${index + 1}`"
  :title="`Eliminar titulo ${index + 1}`"
>
  <i class="fa-solid fa-trash"></i>
</button>
            </div>
          </div>
        </article>

        <article
          v-if="shouldShowCoursesSection"
          ref="coursesSectionRef"
          class="editor-card"
          :class="{ 'editor-card--highlight': props.initialSection === 'courses' }"
        >
          <div class="section-header">
            <h4>Cursos</h4>
            <button type="button" class="btn btn-sm btn-outline-danger" @click="addCourseRow">Agregar</button>
          </div>

          <div class="stack-list">
            <div v-for="(course, index) in form.cursos" :key="`course-${index}`" class="stack-item">
              <div class="form-grid">
                <label>
                  <span>Nombre del curso</span>
                  <input v-model.trim="course.nombre_curso" type="text" class="form-control">
                </label>
                <label>
                  <span>Entregado por</span>
                  <input v-model.trim="course.entregado_por" type="text" class="form-control">
                </label>
                <label>
                  <span>Codigo del curso</span>
                  <input v-model.trim="course.codigo_curso" type="text" class="form-control">
                </label>              </div>
              <div class="attachment-stack">
                <div
                  v-for="(attachment, attachmentIndex) in course.archivos_adjuntos"
                  :key="`course-${index}-attachment-${attachment.id ?? attachmentIndex}`"
                  class="attachment-panel"
                >                  <label class="form-grid__wide">
                    <span>{{ attachmentIndex === 0 ? 'Imagen, foto o documento del curso aprobado' : 'Archivo adicional' }}</span>
                    <input
                      type="file"
                      class="form-control"
                      accept=".jpg,.jpeg,.png,.webp,.pdf"
                      @change="onCourseFileSelected(index, attachmentIndex, $event)"
                    >
                  </label>
                  <p v-if="attachment.error" class="attachment-error">
                    {{ attachment.error }}
                  </p>
                  <div v-if="attachment.file || attachment.url" class="attachment-row">
                    <a
                      v-if="attachment.url"
                      :href="attachment.url"
                      target="_blank"
                      rel="noopener"
                      class="attachment-link"
                    >
                      {{ attachmentDisplayName(attachment, 'Ver respaldo actual') }}
                    </a>
                    <span v-if="attachment.file" class="attachment-badge">
                      Nuevo archivo: {{ attachment.file.name }}
                    </span>
                  </div>
                  <div v-if="hasAttachmentPreview(attachment)" class="attachment-preview">
                    <img
                      v-if="attachmentPreviewKind(attachment) === 'image'"
                      :src="attachmentPreviewUrl(attachment)"
                      :alt="`Vista previa de ${attachmentDisplayName(attachment, 'curso')}`"
                      class="attachment-preview__image"
                    >
                    <iframe
                      v-else-if="attachmentPreviewKind(attachment) === 'pdf'"
                      :src="attachmentPreviewUrl(attachment)"
                      class="attachment-preview__frame"
                      title="Vista previa del respaldo del curso"
                    ></iframe>
                    <a
                      :href="attachmentPreviewUrl(attachment)"
                      target="_blank"
                      rel="noopener"
                      class="attachment-preview__open"
                    >
                      Abrir vista completa
                    </a>
                  </div>
                </div>
                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary attachment-add-button"
                  @click="addCourseAttachment(index)"
                >
                  Archivo adicional
                </button>
              </div>
              <button
  type="button"
  class="icon-delete-button"
  @click="removeCourseRow(index)"
  :aria-label="`Eliminar curso ${index + 1}`"
  :title="`Eliminar curso ${index + 1}`"
>
  <i class="fa-solid fa-trash"></i>
</button>
            </div>
          </div>
        </article>

        <article
          v-if="shouldShowOtherDocumentsSection"
          ref="otherDocumentsSectionRef"
          class="editor-card"
          :class="{ 'editor-card--highlight': props.initialSection === 'documents' }"
        >
          <div class="section-header">
            <h4>Otros documentos</h4>
            <button type="button" class="btn btn-sm btn-outline-danger" @click="addOtherDocumentRow">Agregar</button>
          </div>

          <div class="stack-list">
            <div v-for="(document, index) in form.otros_documentos" :key="`other-document-${index}`" class="stack-item">
              <div class="form-grid">
                <label>
                  <span>Nombre del documento</span>
                  <input v-model.trim="document.nombre_documento" type="text" class="form-control">
                </label>
                <label>
                  <span>Motivo</span>
                  <input v-model.trim="document.motivo" type="text" class="form-control">
                </label>
              </div>
              <div class="attachment-stack">
                <div
                  v-for="(attachment, attachmentIndex) in document.archivos_adjuntos"
                  :key="`other-document-${index}-attachment-${attachment.id ?? attachmentIndex}`"
                  class="attachment-panel"
                >
                  <label class="form-grid__wide">
                    <span>Archivo</span>
                    <input
                      type="file"
                      class="form-control"
                      accept=".jpg,.jpeg,.png,.webp,.pdf"
                      @change="onOtherDocumentFileSelected(index, attachmentIndex, $event)"
                    >
                  </label>
                  <p v-if="attachment.error" class="attachment-error">
                    {{ attachment.error }}
                  </p>
                  <div v-if="attachment.file || attachment.url" class="attachment-row">
                    <a
                      v-if="attachment.url"
                      :href="attachment.url"
                      target="_blank"
                      rel="noopener"
                      class="attachment-link"
                    >
                      {{ attachmentDisplayName(attachment, 'Ver respaldo actual') }}
                    </a>
                    <span v-if="attachment.file" class="attachment-badge">
                      Nuevo archivo: {{ attachment.file.name }}
                    </span>
                  </div>
                  <div v-if="hasAttachmentPreview(attachment)" class="attachment-preview">
                    <img
                      v-if="attachmentPreviewKind(attachment) === 'image'"
                      :src="attachmentPreviewUrl(attachment)"
                      :alt="`Vista previa de ${attachmentDisplayName(attachment, 'documento')}`"
                      class="attachment-preview__image"
                    >
                    <iframe
                      v-else-if="attachmentPreviewKind(attachment) === 'pdf'"
                      :src="attachmentPreviewUrl(attachment)"
                      class="attachment-preview__frame"
                      title="Vista previa del documento"
                    ></iframe>
                    <a
                      :href="attachmentPreviewUrl(attachment)"
                      target="_blank"
                      rel="noopener"
                      class="attachment-preview__open"
                    >
                      Abrir vista completa
                    </a>
                  </div>
                </div>
              </div>
              <button
  type="button"
  class="icon-delete-button"
  @click="removeOtherDocumentRow(index)"
  :aria-label="`Eliminar documento ${index + 1}`"
  :title="`Eliminar documento ${index + 1}`"
>
  <i class="fa-solid fa-trash"></i>
</button>
            </div>
          </div>
        </article>

        <article v-if="!isSectionModal" class="editor-card">
          <div class="section-header">
            <div>
              <h4>Sanciones</h4>
              <p class="section-note">Muestra este bloque solo si existieron sanciones en el periodo.</p>
            </div>
            <button v-if="showSanctionsSection" type="button" class="btn btn-sm btn-outline-danger" @click="addSanctionRow">Agregar</button>
          </div>

          <label class="section-toggle">
            <input
              type="checkbox"
              :checked="showSanctionsSection"
              :disabled="isSanctionsToggleDisabled"
              @change="toggleSanctionsSection"
            >
            <span>Existieron sanciones en el periodo</span>
          </label>
          <p v-if="isSanctionsToggleDisabled" class="section-toggle__hint">
            Para desmarcar esta opcion, elimina primero las sanciones registradas.
          </p>

          <div v-if="showSanctionsSection" class="stack-list">
            <div v-for="(sanction, index) in form.sanciones" :key="`sanction-${index}`" class="stack-item">
              <div class="stack-item__top">
                <strong class="stack-item__title">Sancion {{ index + 1 }}</strong>
                <button
                  type="button"
                  class="icon-delete-button"
                  @click="removeSanctionRow(index)"
                  :aria-label="`Eliminar sancion ${index + 1}`"
                  :title="`Eliminar sancion ${index + 1}`"
                >
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
              <div class="form-grid">
                <label>
                  <span>Tipo de sancion</span>
                  <input v-model.trim="sanction.tipo_sancion" type="text" class="form-control">
                </label>
                <label>
                  <span>Fecha</span>
                  <input v-model="sanction.fecha" type="date" class="form-control">
                </label>
                <label class="form-grid__wide">
                  <span>Resumen</span>
                  <textarea v-model.trim="sanction.resumen_sancion" class="form-control" rows="2"></textarea>
                </label>
                <label class="form-grid__wide">
                  <span>Apelacion</span>
                  <textarea v-model.trim="sanction.apelacion" class="form-control" rows="2"></textarea>
                </label>
                <label>
                  <span>Fecha apelacion</span>
                  <input v-model="sanction.fecha_apelacion" type="date" class="form-control">
                </label>
                <label class="form-grid__wide">
                  <span>Decision CIG</span>
                  <textarea v-model.trim="sanction.decision_cig" class="form-control" rows="2"></textarea>
                </label>
              </div>
            </div>
          </div>
        </article>

        <article v-if="!isSectionModal" class="editor-card">
          <h4>Reconocimiento anual</h4>
          <div class="recognition-grid">
            <label v-for="item in recognitionFields" :key="item.key" class="checkbox-card">
              <input v-model="form.reconocimiento[item.key]" type="checkbox">
              <span>{{ item.label }}</span>
            </label>
          </div>
        </article>
        <article v-if="!isSectionModal" class="editor-card">
          <h4>Labor Efectuada y Observaciones</h4>
          <label class="comments-field">
            <textarea v-model.trim="form.comentarios" class="form-control comments-textarea" rows="8"></textarea>
          </label>
        </article>
        <div class="editor-footer">
          <button type="button" class="btn btn-outline-secondary" :disabled="isSubmitting" @click="requestClose">
            Cancelar
          </button>
          <button type="submit" class="btn btn-danger" :disabled="isSubmitting">
            {{ submitLabel }}
          </button>
        </div>
      </form>
    </section>

    <div v-if="isUnclassifiedHoursModalOpen" class="inline-dialog-backdrop" @click.self="closeUnclassifiedHoursModal">
      <section class="inline-dialog" role="dialog" aria-modal="true" aria-labelledby="unclassified-hours-title">
        <div class="inline-dialog__header">
          <div>
            <p class="inline-dialog__kicker">Registro manual</p>
            <h4 id="unclassified-hours-title">Horas sin clasificacion</h4>
          </div>
          <button
            type="button"
            class="editor-close"
            :disabled="isSubmitting"
            aria-label="Cerrar modal de horas sin clasificacion"
            @click="closeUnclassifiedHoursModal"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <label class="inline-dialog__field">
          <span>Ingresa las horas sin clasificacion del periodo</span>
          <input
            v-model.trim="unclassifiedHoursDraft"
            type="number"
            min="0"
            step="0.25"
            inputmode="decimal"
            class="form-control"
            placeholder="Ejemplo: 12"
          >
        </label>

        <p class="inline-dialog__hint">
          Usa este campo para cargar horas historicas de hojas anuales anteriores que no tengan detalle por categoria.
        </p>

        <div class="inline-dialog__actions">
          <button type="button" class="btn btn-outline-secondary" :disabled="isSubmitting" @click="closeUnclassifiedHoursModal">
            Cancelar
          </button>
          <button type="button" class="btn btn-danger" :disabled="isSubmitting" @click="saveUnclassifiedHours">
            Aceptar
          </button>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { API_BASE } from '../config/api'
import { show_alerta } from '../funciones'

const MAX_ATTACHMENT_SIZE_KB = 5120
const MAX_ATTACHMENT_SIZE_BYTES = MAX_ATTACHMENT_SIZE_KB * 1024

const ESCOLARIDAD_OPTIONS = [
  'Sin escolaridad',
  'Educacion basica incompleta',
  'Educacion basica completa',
  'Educacion media incompleta',
  'Educacion media completa',
  'Educacion tecnica de nivel superior incompleta',
  'Educacion tecnica de nivel superior completa',
  'Educacion universitaria incompleta',
  'Educacion universitaria completa',
  'Postitulo o diplomado',
  'Magister',
  'Doctorado'
]

const ESTADO_CIVIL_OPTIONS = [
  'Soltero(a)',
  'Casado(a)',
  'Conviviente civil',
  'Divorciado(a)',
  'Viudo(a)'
]

const props = defineProps({
  volunteerId: {
    type: [Number, String],
    required: true
  },
  record: {
    type: Object,
    default: null
  },
  currentUserId: {
    type: [Number, String, null],
    default: null
  },
  volunteer: {
    type: Object,
    default: null
  },
  initialSection: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['saved', 'cancel'])

const recognitionFields = [
  { key: 'servicio_extraordinario', label: 'Servicio extraordinario' },
  { key: 'abnegacion', label: 'Abnegacion' },
  { key: 'medalla_honor_3', label: '3a medalla de honor' },
  { key: 'medalla_honor_2', label: '2a medalla de honor' },
  { key: 'medalla_honor_1', label: '1a medalla de honor' },
  { key: 'vittorio_cucchini', label: 'Vittorio Cucchini' },
  { key: 'promesa', label: 'Promesa' },
  { key: 'juramento', label: 'Juramento' }
]

const cargoOptions = [
  { key: 'gobernanza_presidente', nombre: 'Presidente', grupo: 'Gobernanza', direccion: '' },
  { key: 'gobernanza_vicepresidente', nombre: 'Vicepresidente', grupo: 'Gobernanza', direccion: '' },
  { key: 'gobernanza_secretario', nombre: 'Secretario', grupo: 'Gobernanza', direccion: '' },
  { key: 'gobernanza_finanzas', nombre: 'Finanzas', grupo: 'Gobernanza', direccion: '' },
  { key: 'directorio_director_salud', nombre: 'Director', grupo: 'Directorio', direccion: 'Salud' },
  { key: 'directorio_director_subrogante_salud', nombre: 'Director Subrogante', grupo: 'Directorio', direccion: 'Salud' },
  { key: 'directorio_director_juventud', nombre: 'Director', grupo: 'Directorio', direccion: 'Juventud' },
  { key: 'directorio_director_subrogante_juventud', nombre: 'Director Subrogante', grupo: 'Directorio', direccion: 'Juventud' },
  { key: 'directorio_director_gestion', nombre: 'Director', grupo: 'Directorio', direccion: 'Gestion' },
  { key: 'directorio_director_subrogante_gestion', nombre: 'Director Subrogante', grupo: 'Directorio', direccion: 'Gestion' },
  { key: 'directorio_director_desarrollo', nombre: 'Director', grupo: 'Directorio', direccion: 'Desarrollo' },
  { key: 'directorio_director_subrogante_desarrollo', nombre: 'Director Subrogante', grupo: 'Directorio', direccion: 'Desarrollo' },
  { key: 'directorio_director_bienestar_social', nombre: 'Director', grupo: 'Directorio', direccion: 'Bienestar Social' },
  { key: 'directorio_director_subrogante_bienestar_social', nombre: 'Director Subrogante', grupo: 'Directorio', direccion: 'Bienestar Social' },
  { key: 'directorio_director_comunicaciones', nombre: 'Director', grupo: 'Directorio', direccion: 'Comunicaciones' },
  { key: 'directorio_director_subrogante_comunicaciones', nombre: 'Director Subrogante', grupo: 'Directorio', direccion: 'Comunicaciones' }
]

const form = reactive(createEmptyForm())
const filialesOptions = ref([])
const escolaridadOptions = ESCOLARIDAD_OPTIONS
const estadoCivilOptions = ESTADO_CIVIL_OPTIONS
const titlesSectionRef = ref(null)
const coursesSectionRef = ref(null)
const otherDocumentsSectionRef = ref(null)
const showSanctionsSection = ref(false)
const showCargoSelector = ref(false)
const isUnclassifiedHoursModalOpen = ref(false)
const unclassifiedHoursDraft = ref('')
const state = reactive({
  isSubmitting: false
})

const dialogTitleId = 'historial-anual-editor-title'
const isSectionModal = computed(() => ['personal', 'titles', 'courses', 'documents'].includes(props.initialSection))
const shouldShowPersonalSection = computed(() => !isSectionModal.value || props.initialSection === 'personal')
const shouldShowTitlesSection = computed(() => !isSectionModal.value || props.initialSection === 'titles')
const shouldShowCoursesSection = computed(() => !isSectionModal.value || props.initialSection === 'courses')
const shouldShowOtherDocumentsSection = computed(() => !isSectionModal.value || props.initialSection === 'documents')
const panelKicker = computed(() => isSectionModal.value ? 'Carga de respaldo' : 'Edicion administrativa')
const panelTitle = computed(() => {
  if (props.initialSection === 'personal') {
    return 'Editar mis datos personales'
  }

  if (props.initialSection === 'titles') {
    return 'Subir titulo aprobado'
  }

  if (props.initialSection === 'courses') {
    return 'Subir curso aprobado'
  }

  if (props.initialSection === 'documents') {
    return 'Subir otro documento'
  }

  return props.record ? `Editar periodo ${props.record.anio}` : 'Nuevo periodo anual'
})
const panelDescription = computed(() => {
  if (props.initialSection === 'personal') {
    return 'Actualiza tu informacion personal desde tu propia hoja de vida.'
  }

  if (props.initialSection === 'titles') {
    return 'Adjunta la imagen, foto o documento del titulo aprobado y completa los datos del registro.'
  }

  if (props.initialSection === 'courses') {
    return 'Adjunta la imagen, foto o documento del curso aprobado y completa los datos del registro.'
  }

  if (props.initialSection === 'documents') {
    return 'Adjunta el documento relevante del voluntario y completa el contexto del registro.'
  }

  return props.record
    ? 'Actualiza la informacion del periodo anual y sus respaldos.'
    : 'Completa los antecedentes del nuevo periodo anual.'
})

const activePeriodYearLabel = computed(() => form.anio || props.activeYear || props.record?.anio || 'sin anio')
const isSubmitting = computed(() => state.isSubmitting)
const submitLabel = computed(() => {
  if (isSubmitting.value) {
    return 'Guardando...'
  }

  if (props.initialSection === 'personal') {
    return 'Guardar mis datos'
  }

  if (props.initialSection === 'titles') {
    return 'Guardar titulo'
  }

  if (props.initialSection === 'courses') {
    return 'Guardar curso'
  }

  if (props.initialSection === 'documents') {
    return 'Guardar documento'
  }

  return 'Guardar hoja de vida'
})
const hasSanctionData = computed(() => form.sanciones.some((row) => sanctionHasData(row)))
const isSanctionsToggleDisabled = computed(() => hasSanctionData.value)
const attendanceCanPreviewBreakdown = computed(() => Boolean(props.record))
const attendanceAdjustments = computed(() => ({
  reuniones: roundToTwo(nullableNumber(form.asistencia_reuniones_filial_ajuste_horas) ?? 0),
  voluntariado: roundToTwo(nullableNumber(form.asistencia_actividades_voluntariado_ajuste_horas) ?? 0),
  filial: roundToTwo(nullableNumber(form.asistencia_horas_filial_ajuste_horas) ?? 0),
  formativas: roundToTwo(nullableNumber(form.asistencia_horas_formativas_ajuste_horas) ?? 0),
  unclassified: roundToTwo(nullableNumber(form.asistencia_anual_ajuste_horas) ?? 0)
}))
const attendancePreview = computed(() => {
  const required = roundToTwo(Number(props.record?.asistencia_anual_horas_requeridas ?? 288))
  const reunionesBase = roundToTwo(Number(props.record?.asistencia_reuniones_filial_horas_base ?? props.record?.asistencia_reuniones_filial_horas ?? 0))
  const voluntariadoBase = roundToTwo(Number(props.record?.asistencia_actividades_voluntariado_horas_base ?? props.record?.asistencia_actividades_voluntariado_horas ?? 0))
  const filialBase = roundToTwo(Number(props.record?.asistencia_en_filial_horas_base ?? props.record?.asistencia_en_filial_horas ?? 0))
  const formativasBase = roundToTwo(Number(props.record?.asistencia_horas_formativas_horas_base ?? props.record?.asistencia_horas_formativas_horas ?? 0))
  const reuniones = roundToTwo(Math.max(reunionesBase + attendanceAdjustments.value.reuniones, 0))
  const voluntariado = roundToTwo(Math.max(voluntariadoBase + attendanceAdjustments.value.voluntariado, 0))
  const filial = roundToTwo(Math.max(filialBase + attendanceAdjustments.value.filial, 0))
    const formativas = roundToTwo(Math.max(formativasBase + attendanceAdjustments.value.formativas, 0))
  const unclassified = roundToTwo(Math.max(attendanceAdjustments.value.unclassified, 0))
  const base = roundToTwo(reuniones + voluntariado + filial + formativas)
  const total = roundToTwo(base + unclassified)
  const porcentaje = required > 0 ? roundToTwo(Math.min((total / required) * 100, 100)) : 0

  return { reuniones, voluntariado, filial, formativas, unclassified, base, total, porcentaje, requeridas: required }
})
const selectedCargoDetails = computed(() => cargoOptions.find((cargo) => cargo.key === form.cargo_clave) || null)
const selectedCargoLabel = computed(() => formatCargoLabel(selectedCargoDetails.value))

onMounted(() => {
  fetchFiliales()
})

onBeforeUnmount(() => {
  cleanupAttachmentPreviews()
})

watch(
  () => [props.record, props.volunteer],
  async ([record, volunteer]) => {
    applyRecord(record, volunteer)
    await nextTick()
    focusInitialSection()
  },
  { immediate: true }
)

watch(
  () => props.initialSection,
  async () => {
    await nextTick()
    focusInitialSection()
  }
)

watch(hasSanctionData, (value) => {
  if (value) {
    showSanctionsSection.value = true
  }
})

watch(
  () => form.tiene_cargo_periodo,
  (value) => {
    if (value) {
      showCargoSelector.value = true
      return
    }

    showCargoSelector.value = false
    form.cargo_clave = ''
  }
)

function adjustAttendance(type, delta) {
  const fieldMap = {
    reuniones: 'asistencia_reuniones_filial_ajuste_horas',
    voluntariado: 'asistencia_actividades_voluntariado_ajuste_horas',
    filial: 'asistencia_horas_filial_ajuste_horas',
    formativas: 'asistencia_horas_formativas_ajuste_horas'
  }

  const field = fieldMap[type]

  if (!field) {
    return
  }

  const currentValue = nullableNumber(form[field]) ?? 0
  form[field] = String(roundToTwo(currentValue + delta))
}

function openUnclassifiedHoursModal() {
  unclassifiedHoursDraft.value = form.asistencia_anual_ajuste_horas === '' || form.asistencia_anual_ajuste_horas === null || form.asistencia_anual_ajuste_horas === undefined
    ? ''
    : String(form.asistencia_anual_ajuste_horas)
  isUnclassifiedHoursModalOpen.value = true
}

function closeUnclassifiedHoursModal() {
  isUnclassifiedHoursModalOpen.value = false
  unclassifiedHoursDraft.value = ''
}

function saveUnclassifiedHours() {
  const rawValue = unclassifiedHoursDraft.value

  if (rawValue === '') {
    form.asistencia_anual_ajuste_horas = ''
    closeUnclassifiedHoursModal()
    return
  }

  const numericValue = Number(rawValue)

  if (!Number.isFinite(numericValue) || numericValue < 0) {
    show_alerta('Ingresa un numero valido de horas sin clasificacion.', 'warning')
    return
  }

  form.asistencia_anual_ajuste_horas = String(roundToTwo(numericValue))
  closeUnclassifiedHoursModal()
}

function focusInitialSection() {
  const targetMap = {
    titles: titlesSectionRef.value,
    courses: coursesSectionRef.value,
    documents: otherDocumentsSectionRef.value
  }

  const target = targetMap[props.initialSection]

  if (target && typeof target.scrollIntoView === 'function') {
    target.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

function createEmptyAttachment() {
  return {
    id: null,
    file: null,
    url: '',
    name: '',
    mime_type: '',
    preview_url: '',
    error: ''
  }
}

function createAttachmentFromExisting(attachment) {
  return {
    id: attachment?.id ?? null,
    file: null,
    url: attachment?.url ?? attachment?.url_publica ?? '',
    name: attachment?.name ?? attachment?.nombre ?? attachment?.nombre_original ?? '',
    mime_type: attachment?.mime_type ?? '',
    preview_url: '',
    error: ''
  }
}

function normalizeAttachmentList(attachments = [], legacyAttachment = null) {
  if (Array.isArray(attachments) && attachments.length) {
    return attachments.map((attachment) => createAttachmentFromExisting(attachment))
  }

  if (legacyAttachment && (legacyAttachment.url || legacyAttachment.id)) {
    return [createAttachmentFromExisting(legacyAttachment)]
  }

  return [createEmptyAttachment()]
}

function createEmptyTitleRow() {
  return {
    id: null,
    titulo: '',
    entregado_por: '',
    codigo_titulo: '',
    archivos_adjuntos: [createEmptyAttachment()]
  }
}

function createEmptyCourseRow() {
  return {
    id: null,
    nombre_curso: '',
    entregado_por: '',
    codigo_curso: '',
    archivos_adjuntos: [createEmptyAttachment()]
  }
}

function createEmptyOtherDocumentRow() {
  return {
    id: null,
    nombre_documento: '',
    motivo: '',
    archivos_adjuntos: [createEmptyAttachment()]
  }
}

function createEmptySanctionRow() {
  return {
    tipo_sancion: '',
    fecha: '',
    resumen_sancion: '',
    apelacion: '',
    fecha_apelacion: '',
    decision_cig: ''
  }
}

function createEmptyRecognition() {
  return {
    servicio_extraordinario: false,
    abnegacion: false,
    medalla_honor_3: false,
    medalla_honor_2: false,
    medalla_honor_1: false,
    vittorio_cucchini: false,
    promesa: false,
    juramento: false
  }
}

function createEmptyForm() {
  return {
    registro_filial: '',
    filial_id: '',
    rut: '',
    nombres: '',
    apellidos: '',
    correo_electronico: '',
    nacionalidad: '',
    fecha_nacimiento: '',
    fecha_incorporacion: '',
    nivel_escolaridad: '',
    estado_civil: '',
    ocupacion: '',
    grupo_sanguineo: '',
    celular: '',
    domicilio: '',
    enfermedades: '',
    alergias: '',
    contacto_emergencia_nombre: '',
    contacto_emergencia_numero: '',
    foto_perfil: null,
    anio: String(new Date().getFullYear()),
    asistencia_anual_ajuste_horas: '',
    asistencia_reuniones_filial_ajuste_horas: '',
    asistencia_actividades_voluntariado_ajuste_horas: '',
    asistencia_horas_filial_ajuste_horas: '',
    asistencia_horas_formativas_ajuste_horas: '',
    tiene_cargo_periodo: false,
    cargo_clave: '',
    estuvo_comision_servicio: false,
    comision_fecha_inicio: '',
    comision_fecha_termino: '',
    comision_lugar: '',
    comision_actividad: '',
    comentarios: '',
    titulos: [createEmptyTitleRow()],
    cursos: [createEmptyCourseRow()],
    otros_documentos: [createEmptyOtherDocumentRow()],
    sanciones: [createEmptySanctionRow()],
    reconocimiento: createEmptyRecognition()
  }
}

function applyRecord(record, volunteer = props.volunteer) {
  const next = createEmptyForm()

  if (volunteer) {
    next.registro_filial = volunteer.registro_filial || ''
    next.filial_id = volunteer.filial_id ? String(volunteer.filial_id) : ''
    next.rut = volunteer.rut || ''
    next.nombres = volunteer.nombres || ''
    next.apellidos = volunteer.apellidos || ''
    next.correo_electronico = volunteer.correo_electronico || ''
    next.nacionalidad = volunteer.nacionalidad || ''
    next.fecha_nacimiento = formatDateInput(volunteer.fecha_nacimiento)
    next.fecha_incorporacion = formatDateInput(volunteer.fecha_incorporacion)
    next.nivel_escolaridad = volunteer.nivel_escolaridad || ''
    next.estado_civil = volunteer.estado_civil || ''
    next.ocupacion = volunteer.ocupacion || ''
    next.grupo_sanguineo = volunteer.grupo_sanguineo || ''
    next.celular = volunteer.celular || ''
    next.domicilio = volunteer.domicilio || ''
    next.enfermedades = volunteer.enfermedades || ''
    next.alergias = volunteer.alergias || ''
    next.contacto_emergencia_nombre = volunteer.contacto_emergencia_nombre || ''
    next.contacto_emergencia_numero = volunteer.contacto_emergencia_numero || ''
  }

  if (record) {
    next.anio = String(record.anio || '')
    next.asistencia_anual_ajuste_horas = record.asistencia_anual_ajuste_horas ?? ''
    next.asistencia_reuniones_filial_ajuste_horas = record.asistencia_reuniones_filial_ajuste_horas ?? ''
    next.asistencia_actividades_voluntariado_ajuste_horas = record.asistencia_actividades_voluntariado_ajuste_horas ?? ''
    next.asistencia_horas_filial_ajuste_horas = record.asistencia_horas_filial_ajuste_horas ?? ''
    next.asistencia_horas_formativas_ajuste_horas = record.asistencia_horas_formativas_ajuste_horas ?? ''
    next.tiene_cargo_periodo = Boolean(record.cargo_clave)
    next.cargo_clave = record.cargo_clave || ''
    next.estuvo_comision_servicio = Boolean(record.estuvo_comision_servicio)
    next.comision_fecha_inicio = formatDateInput(record.comision_fecha_inicio)
    next.comision_fecha_termino = formatDateInput(record.comision_fecha_termino)
    next.comision_lugar = record.comision_lugar || ''
    next.comision_actividad = record.comision_actividad || ''
    next.comentarios = record.comentarios || ''
    next.titulos = (record.titulos?.length ? record.titulos : [createEmptyTitleRow()]).map((row) => ({
      id: row.id ?? null,
      titulo: row.titulo || '',
      entregado_por: row.entregado_por || '',
      codigo_titulo: row.codigo_titulo || '',
      archivos_adjuntos: normalizeAttachmentList(row.archivos_adjuntos, {
        id: row.archivo_id ?? null,
        url: row.archivo_url || '',
        name: row.archivo_nombre || ''
      })
    }))
    next.cursos = (record.cursos?.length ? record.cursos : [createEmptyCourseRow()]).map((row) => ({
      id: row.id ?? null,
      nombre_curso: row.nombre_curso || '',
      entregado_por: row.entregado_por || '',
      codigo_curso: row.codigo_curso || '',
      archivos_adjuntos: normalizeAttachmentList(row.archivos_adjuntos, {
        id: row.archivo_id ?? null,
        url: row.archivo_url || '',
        name: row.archivo_nombre || ''
      })
    }))
    next.otros_documentos = (record.otros_documentos?.length ? record.otros_documentos : [createEmptyOtherDocumentRow()]).map((row) => ({
      id: row.id ?? null,
      nombre_documento: row.nombre_documento || '',
      motivo: row.motivo || '',
      archivos_adjuntos: normalizeAttachmentList(row.archivos_adjuntos, {
        id: row.archivo_id ?? null,
        url: row.archivo_url || '',
        name: row.archivo_nombre || ''
      })
    }))
    next.sanciones = (record.sanciones?.length ? record.sanciones : [createEmptySanctionRow()]).map((row) => ({
      tipo_sancion: row.tipo_sancion || '',
      fecha: formatDateInput(row.fecha),
      resumen_sancion: row.resumen_sancion || '',
      apelacion: row.apelacion || '',
      fecha_apelacion: formatDateInput(row.fecha_apelacion),
      decision_cig: row.decision_cig || ''
    }))
    next.reconocimiento = recognitionFields.reduce((carry, field) => {
      carry[field.key] = Boolean(record.reconocimiento?.[field.key])
      return carry
    }, createEmptyRecognition())
  }

  cleanupAttachmentPreviews()
  Object.assign(form, next)
  showSanctionsSection.value = next.sanciones.some((row) => sanctionHasData(row))
  showCargoSelector.value = next.tiene_cargo_periodo
}

function formatDateInput(value) {
  if (!value) {
    return ''
  }

  return String(value).slice(0, 10)
}

function nullableText(value) {
  const normalized = String(value ?? '').trim()
  return normalized === '' ? null : normalized
}

function nullableNumber(value) {
  if (value === '' || value === null || value === undefined) {
    return null
  }

  return Number(value)
}

function roundToTwo(value) {
  const numericValue = Number(value)

  if (!Number.isFinite(numericValue)) {
    return 0
  }

  return Math.round(numericValue * 100) / 100
}

function formatHours(value) {
  return `${new Intl.NumberFormat('es-CL', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(roundToTwo(value))} hrs`
}

function formatWholeHours(value) {
  return `${new Intl.NumberFormat('es-CL', { maximumFractionDigits: 0 }).format(Math.round(roundToTwo(value)))} hrs`
}

function formatPercentage(value) {
  return `${new Intl.NumberFormat('es-CL', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(roundToTwo(value))}%`
}

function cargoDescription(cargo) {
  if (cargo.grupo === 'Gobernanza') {
    return cargo.grupo
  }

  return `${cargo.grupo} · Direccion de ${cargo.direccion}`
}

function formatCargoLabel(cargo) {
  if (!cargo) {
    return ''
  }

  if (cargo.grupo === 'Gobernanza') {
    return `${cargo.grupo}: ${cargo.nombre}`
  }

  return `${cargo.grupo}: ${cargo.nombre} de ${cargo.direccion}`
}

function cleanupAttachmentPreviews() {
  ;[...form.titulos, ...form.cursos, ...form.otros_documentos].forEach((row) => {
    row.archivos_adjuntos?.forEach((attachment) => revokeAttachmentPreview(attachment))
  })
}

function revokeAttachmentPreview(attachment) {
  if (attachment?.preview_url && String(attachment.preview_url).startsWith('blob:')) {
    URL.revokeObjectURL(attachment.preview_url)
  }

  if (attachment) {
    attachment.preview_url = ''
  }
}

function setAttachmentFile(attachment, file) {
  if (!attachment) {
    return
  }

  revokeAttachmentPreview(attachment)
  attachment.id = null
  attachment.file = file
  attachment.url = ''
  attachment.name = file?.name || ''
  attachment.mime_type = file?.type || ''
  attachment.error = ''
  attachment.preview_url = file ? URL.createObjectURL(file) : ''
}

function attachmentPreviewUrl(attachment) {
  if (!attachment) {
    return ''
  }

  if (attachment.file && attachment.preview_url) {
    return attachment.preview_url
  }

  return attachment.url || ''
}

function attachmentDisplayName(attachment, fallback = 'archivo') {
  return attachment?.file?.name || attachment?.name || fallback
}

function validateAttachmentFile(attachment, file) {
  if (!attachment) {
    return false
  }

  if (!file) {
    attachment.error = ''
    return true
  }

  if (file.size > MAX_ATTACHMENT_SIZE_BYTES) {
    attachment.file = null
    attachment.error = `El archivo supera el tamano maximo permitido de ${MAX_ATTACHMENT_SIZE_KB / 1024} MB.`
    return false
  }

  attachment.error = ''
  return true
}

function attachmentPreviewKind(attachment) {
  if (!attachment) {
    return null
  }

  if (attachment.file?.type?.startsWith('image/')) {
    return 'image'
  }

  if (attachment.file?.type === 'application/pdf' || attachment.mime_type === 'application/pdf') {
    return 'pdf'
  }

  const reference = attachmentDisplayName(attachment, attachment.url || '')

  if (/\.(jpg|jpeg|png|webp|gif|bmp|svg)$/i.test(reference)) {
    return 'image'
  }

  if (/\.pdf$/i.test(reference)) {
    return 'pdf'
  }

  return null
}

function hasAttachmentPreview(attachment) {
  return Boolean(attachmentPreviewUrl(attachment) && attachmentPreviewKind(attachment))
}

function addAttachmentSlot(row) {
  if (!row?.archivos_adjuntos) {
    return
  }

  row.archivos_adjuntos.push(createEmptyAttachment())
}

function removeAttachmentSlot(row, attachmentIndex) {
  if (!row?.archivos_adjuntos?.length) {
    return
  }

  const attachment = row.archivos_adjuntos[attachmentIndex]

  if (attachment) {
    revokeAttachmentPreview(attachment)
  }

  if (row.archivos_adjuntos.length === 1) {
    row.archivos_adjuntos.splice(0, 1, createEmptyAttachment())
    return
  }

  row.archivos_adjuntos.splice(attachmentIndex, 1)
}

function clearAttachmentSlot(row, attachmentIndex) {
  const attachment = row?.archivos_adjuntos?.[attachmentIndex]

  if (!attachment) {
    return
  }

  revokeAttachmentPreview(attachment)
  Object.assign(attachment, createEmptyAttachment())
}

function appendAttachmentPayload(formData, key, attachments = []) {
  let existingIndex = 0
  let newIndex = 0

  attachments.forEach((attachment) => {
    if (attachment?.id && attachment.url && !attachment.file) {
      appendValue(formData, `${key}[archivo_ids][${existingIndex}]`, attachment.id)
      existingIndex += 1
    }

    if (attachment?.file) {
      formData.append(`${key}[archivos][${newIndex}]`, attachment.file)
      newIndex += 1
    }
  })
}

function appendValue(formData, key, value) {
  formData.append(key, value === null || value === undefined ? '' : String(value))
}

async function fetchFiliales() {
  try {
    const response = await axios.get(`${API_BASE}/filiales`)
    filialesOptions.value = Array.isArray(response.data) ? response.data : []
  } catch (error) {
    filialesOptions.value = []
  }
}

function sanctionHasData(row) {
  return Boolean(
    nullableText(row?.tipo_sancion) ||
    nullableText(row?.fecha) ||
    nullableText(row?.resumen_sancion) ||
    nullableText(row?.apelacion) ||
    nullableText(row?.fecha_apelacion) ||
    nullableText(row?.decision_cig)
  )
}

function buildFormData() {
  const formData = new FormData()

  if (!isSectionModal.value || props.initialSection === 'personal') {
    appendValue(formData, 'registro_filial', nullableText(form.registro_filial))
    appendValue(formData, 'filial_id', nullableText(form.filial_id))
    appendValue(formData, 'rut', nullableText(form.rut))
    appendValue(formData, 'nombres', nullableText(form.nombres))
    appendValue(formData, 'apellidos', nullableText(form.apellidos))
    appendValue(formData, 'correo_electronico', nullableText(form.correo_electronico))
    appendValue(formData, 'nacionalidad', nullableText(form.nacionalidad))
    appendValue(formData, 'fecha_nacimiento', nullableText(form.fecha_nacimiento))
    appendValue(formData, 'fecha_incorporacion', nullableText(form.fecha_incorporacion))
    appendValue(formData, 'nivel_escolaridad', nullableText(form.nivel_escolaridad))
    appendValue(formData, 'estado_civil', nullableText(form.estado_civil))
    appendValue(formData, 'ocupacion', nullableText(form.ocupacion))
    appendValue(formData, 'grupo_sanguineo', nullableText(form.grupo_sanguineo))
    appendValue(formData, 'celular', nullableText(form.celular))
    appendValue(formData, 'domicilio', nullableText(form.domicilio))
    appendValue(formData, 'enfermedades', nullableText(form.enfermedades))
    appendValue(formData, 'alergias', nullableText(form.alergias))
    appendValue(formData, 'contacto_emergencia_nombre', nullableText(form.contacto_emergencia_nombre))
    appendValue(formData, 'contacto_emergencia_numero', nullableText(form.contacto_emergencia_numero))

    if (form.foto_perfil) {
      formData.append('foto_perfil', form.foto_perfil)
    }
  }

  appendValue(formData, 'anio', Number(form.anio))
  appendValue(formData, 'asistencia_anual_ajuste_horas', nullableNumber(form.asistencia_anual_ajuste_horas) ?? 0)
  appendValue(formData, 'asistencia_reuniones_filial_ajuste_horas', nullableNumber(form.asistencia_reuniones_filial_ajuste_horas) ?? 0)
  appendValue(formData, 'asistencia_actividades_voluntariado_ajuste_horas', nullableNumber(form.asistencia_actividades_voluntariado_ajuste_horas) ?? 0)
  appendValue(formData, 'asistencia_horas_filial_ajuste_horas', nullableNumber(form.asistencia_horas_filial_ajuste_horas) ?? 0)
  appendValue(formData, 'asistencia_horas_formativas_ajuste_horas', nullableNumber(form.asistencia_horas_formativas_ajuste_horas) ?? 0)
  appendValue(formData, 'estuvo_comision_servicio', form.estuvo_comision_servicio ? '1' : '0')
  appendValue(formData, 'comision_fecha_inicio', form.estuvo_comision_servicio ? nullableText(form.comision_fecha_inicio) : null)
  appendValue(formData, 'comision_fecha_termino', form.estuvo_comision_servicio ? nullableText(form.comision_fecha_termino) : null)
  appendValue(formData, 'comision_lugar', form.estuvo_comision_servicio ? nullableText(form.comision_lugar) : null)
  appendValue(formData, 'comision_actividad', form.estuvo_comision_servicio ? nullableText(form.comision_actividad) : null)
  appendValue(formData, 'comentarios', nullableText(form.comentarios))
  appendValue(formData, 'cargo_clave', form.tiene_cargo_periodo ? nullableText(form.cargo_clave) : null)

  const normalizedCurrentUserId = Number(props.currentUserId)

  if (Number.isInteger(normalizedCurrentUserId) && normalizedCurrentUserId > 0) {
    appendValue(formData, 'generada_por', normalizedCurrentUserId)
  }

  form.titulos.forEach((row, index) => {
    appendValue(formData, `titulos[${index}][id]`, row.id)
    appendValue(formData, `titulos[${index}][titulo]`, nullableText(row.titulo))
    appendValue(formData, `titulos[${index}][entregado_por]`, nullableText(row.entregado_por))
    appendValue(formData, `titulos[${index}][codigo_titulo]`, nullableText(row.codigo_titulo))
    appendAttachmentPayload(formData, `titulos[${index}]`, row.archivos_adjuntos)
  })

  form.cursos.forEach((row, index) => {
    appendValue(formData, `cursos[${index}][id]`, row.id)
    appendValue(formData, `cursos[${index}][nombre_curso]`, nullableText(row.nombre_curso))
    appendValue(formData, `cursos[${index}][entregado_por]`, nullableText(row.entregado_por))
    appendValue(formData, `cursos[${index}][codigo_curso]`, nullableText(row.codigo_curso))
    appendAttachmentPayload(formData, `cursos[${index}]`, row.archivos_adjuntos)
  })

  form.otros_documentos.forEach((row, index) => {
    appendValue(formData, `otros_documentos[${index}][id]`, row.id)
    appendValue(formData, `otros_documentos[${index}][nombre_documento]`, nullableText(row.nombre_documento))
    appendValue(formData, `otros_documentos[${index}][motivo]`, nullableText(row.motivo))
    appendAttachmentPayload(formData, `otros_documentos[${index}]`, row.archivos_adjuntos)
  })

  if (showSanctionsSection.value) {
    form.sanciones.forEach((row, index) => {
      appendValue(formData, `sanciones[${index}][tipo_sancion]`, nullableText(row.tipo_sancion))
      appendValue(formData, `sanciones[${index}][fecha]`, nullableText(row.fecha))
      appendValue(formData, `sanciones[${index}][resumen_sancion]`, nullableText(row.resumen_sancion))
      appendValue(formData, `sanciones[${index}][apelacion]`, nullableText(row.apelacion))
      appendValue(formData, `sanciones[${index}][fecha_apelacion]`, nullableText(row.fecha_apelacion))
      appendValue(formData, `sanciones[${index}][decision_cig]`, nullableText(row.decision_cig))
    })
  }

  recognitionFields.forEach((field) => {
    appendValue(formData, `reconocimiento[${field.key}]`, form.reconocimiento[field.key] ? '1' : '0')
  })

  return formData
}

function addTitleRow() {
  form.titulos.push(createEmptyTitleRow())
}

function addTitleAttachment(index) {
  addAttachmentSlot(form.titulos[index])
}

function removeTitleAttachment(index, attachmentIndex) {
  removeAttachmentSlot(form.titulos[index], attachmentIndex)
}

function removeTitleRow(index) {
  const row = form.titulos[index]

  row?.archivos_adjuntos?.forEach((attachment) => revokeAttachmentPreview(attachment))

  if (form.titulos.length === 1) {
    form.titulos.splice(0, 1, createEmptyTitleRow())
    return
  }

  form.titulos.splice(index, 1)
}

function addCourseRow() {
  form.cursos.push(createEmptyCourseRow())
}

function addCourseAttachment(index) {
  addAttachmentSlot(form.cursos[index])
}

function removeCourseAttachment(index, attachmentIndex) {
  removeAttachmentSlot(form.cursos[index], attachmentIndex)
}

function removeCourseRow(index) {
  const row = form.cursos[index]

  row?.archivos_adjuntos?.forEach((attachment) => revokeAttachmentPreview(attachment))

  if (form.cursos.length === 1) {
    form.cursos.splice(0, 1, createEmptyCourseRow())
    return
  }

  form.cursos.splice(index, 1)
}

function addOtherDocumentRow() {
  form.otros_documentos.push(createEmptyOtherDocumentRow())
}

function removeOtherDocumentRow(index) {
  const row = form.otros_documentos[index]

  row?.archivos_adjuntos?.forEach((attachment) => revokeAttachmentPreview(attachment))

  if (form.otros_documentos.length === 1) {
    form.otros_documentos.splice(0, 1, createEmptyOtherDocumentRow())
    return
  }

  form.otros_documentos.splice(index, 1)
}

function addSanctionRow() {
  showSanctionsSection.value = true
  form.sanciones.push(createEmptySanctionRow())
}

function removeSanctionRow(index) {
  if (form.sanciones.length === 1) {
    form.sanciones.splice(0, 1, createEmptySanctionRow())
    return
  }

  form.sanciones.splice(index, 1)
}

function toggleSanctionsSection(event) {
  const nextValue = Boolean(event.target.checked)

  if (!nextValue && hasSanctionData.value) {
    return
  }

  showSanctionsSection.value = nextValue
}

function toggleCargoSelector() {
  if (!form.tiene_cargo_periodo) {
    return
  }

  showCargoSelector.value = !showCargoSelector.value
}

function requestClose() {
  emit('cancel')
}

function onPhotoSelected(event) {
  form.foto_perfil = event.target.files?.[0] || null
}

function onTitleFileSelected(index, attachmentIndex, event) {
  const file = event.target.files?.[0] || null
  const attachment = form.titulos[index]?.archivos_adjuntos?.[attachmentIndex]

  if (!attachment) {
    return
  }

  if (!validateAttachmentFile(attachment, file)) {
    event.target.value = ''
    return
  }

  setAttachmentFile(attachment, file)
  event.target.value = ''
}

function onCourseFileSelected(index, attachmentIndex, event) {
  const file = event.target.files?.[0] || null
  const attachment = form.cursos[index]?.archivos_adjuntos?.[attachmentIndex]

  if (!attachment) {
    return
  }

  if (!validateAttachmentFile(attachment, file)) {
    event.target.value = ''
    return
  }

  setAttachmentFile(attachment, file)
  event.target.value = ''
}

function onOtherDocumentFileSelected(index, attachmentIndex, event) {
  const file = event.target.files?.[0] || null
  const attachment = form.otros_documentos[index]?.archivos_adjuntos?.[attachmentIndex]

  if (!attachment) {
    return
  }

  if (!validateAttachmentFile(attachment, file)) {
    event.target.value = ''
    return
  }

  setAttachmentFile(attachment, file)
  event.target.value = ''
}

function clearTitleAttachment(index, attachmentIndex) {
  clearAttachmentSlot(form.titulos[index], attachmentIndex)
}

function clearCourseAttachment(index, attachmentIndex) {
  clearAttachmentSlot(form.cursos[index], attachmentIndex)
}

async function submit() {
  if (!isSectionModal.value || props.initialSection === 'personal') {
    if (!nullableText(form.registro_filial)) {
      show_alerta('Debes ingresar el numero de registro.', 'warning')
      return
    }

    if (!nullableText(form.filial_id)) {
      show_alerta('Debes seleccionar una filial.', 'warning')
      return
    }

    if (!nullableText(form.rut) || !nullableText(form.nombres) || !nullableText(form.apellidos)) {
      show_alerta('Completa RUT, nombres y apellidos del voluntario.', 'warning')
      return
    }
  }

  if (!form.anio || Number.isNaN(Number(form.anio))) {
    show_alerta('Debes ingresar un anio valido.', 'warning')
    return
  }

  if (form.tiene_cargo_periodo && !selectedCargoDetails.value) {
    show_alerta('Debes seleccionar un cargo para el periodo.', 'warning')
    return
  }

  state.isSubmitting = true

  try {
    const payload = buildFormData()
    const response = props.record?.id
      ? await axios.post(`${API_BASE}/hoja-vida-anual/${props.record.id}`, (() => {
        payload.append('_method', 'PUT')
        return payload
      })(), {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      : await axios.post(`${API_BASE}/voluntarios/${props.volunteerId}/hoja-vida-anual`, payload, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })

    show_alerta('Hoja de vida guardada correctamente.', 'success')
    emit('saved', response.data)
  } catch (error) {
    const errors = error.response?.data?.errors || {}
    const message = Object.values(errors).flat().join(' ')
    show_alerta(message || 'No se pudo guardar la hoja de vida.', 'error')
  } finally {
    state.isSubmitting = false
  }
}
</script>

<style scoped>
.editor-modal {
  position: fixed;
  inset: 0;
  z-index: 1200;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: clamp(1rem, 0.7rem + 1vw, 1.6rem);
  background: rgba(15, 29, 55, 0.56);
  backdrop-filter: blur(4px);
}

.editor-panel {
  width: min(100%, 980px);
  max-height: calc(100vh - 2rem);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #fff;
  border: 1px solid #d7e1ec;
  border-radius: 24px;
  box-shadow: 0 28px 60px rgba(10, 25, 48, 0.24);
}

.editor-panel__header,
.section-header,
.editor-actions {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
}

.editor-panel__header {
  padding: 1.25rem 1.25rem 1rem;
  background: #fff;
  border-bottom: 1px solid #d7e1ec;
}

.editor-kicker {
  margin: 0 0 0.35rem;
  color: #d3272d;
  font-size: 0.8rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.editor-panel__header h3,
.editor-card h4 {
  margin: 0;
  color: #0f2f5f;
}

.editor-description {
  margin: 0.45rem 0 0;
  color: #5a6d86;
  max-width: 60ch;
  line-height: 1.5;
}

.editor-close {
  width: 42px;
  height: 42px;
  border: 1px solid #d7e0eb;
  border-radius: 999px;
  background: #fff;
  color: #173b70;
}

.period-chip {
  display: inline-flex;
  align-items: center;
  min-height: 38px;
  padding: 0.4rem 0.8rem;
  border-radius: 999px;
  background: #edf4fb;
  border: 1px solid #c6d6e6;
  color: #173b70;
  font-size: 0.88rem;
  font-weight: 700;
}

.editor-grid {
  display: grid;
  gap: 1rem;
  overflow: auto;
  padding: 1rem 1.25rem 1.25rem;
}

.editor-card {
  border: 1px solid #c1d2e3;
  border-radius: 18px;
  background: #e6eff8;
  padding: 1rem;
}

.editor-card--highlight {
  border-color: #ffb4b8;
  box-shadow: 0 0 0 1px rgba(255, 55, 67, 0.08);
}

.form-grid,
.recognition-grid {
  display: grid;
  gap: 0.85rem;
}

.form-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.form-grid__wide {
  grid-column: span 3;
}

.field-hint {
  color: #5a6d86;
  font-size: 0.82rem;
  font-weight: 500;
  line-height: 1.45;
}

.attendance-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.8rem;
  flex-wrap: wrap;
  margin-bottom: 0.95rem;
}

.attendance-card__year-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 96px;
  min-height: 46px;
  padding: 0.45rem 1rem;
  border-radius: 16px;
  background: #ff3743;
  color: #fff;
  font-size: 1.3rem;
  font-weight: 900;
  line-height: 1;
  box-shadow: 0 10px 22px rgba(255, 55, 67, 0.18);
}

.attendance-card__year-input-row {
  margin-bottom: 0.95rem;
}

.attendance-summary {
  display: grid;
  gap: 1rem;
  padding: 1.1rem 1.15rem 1.15rem;
  border: 1px solid #bfd2e5;
  border-radius: 20px;
  background: linear-gradient(180deg, #f5f9fe 0%, #edf4fb 100%);
  color: #153963;
}

.attendance-summary__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.attendance-summary__eyebrow {
  display: block;
  margin-bottom: 0.22rem;
  color: #c92a35;
  font-size: 0.92rem;
  font-weight: 900;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.attendance-summary__header strong {
  color: #143761;
  font-size: 1.08rem;
  line-height: 1.35;
}

.attendance-summary__badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 88px;
  min-height: 46px;
  padding: 0.45rem 1rem;
  border-radius: 999px;
  background: #e7f0fa;
  border: 2px solid #c3d4e5;
  color: #143761;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
}

.attendance-summary__badge-value {
  font-size: 1.4rem;
  font-weight: 900;
  line-height: 1;
}

.attendance-summary__layout {
  display: grid;
  gap: 0.9rem;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  align-items: stretch;
}

.attendance-summary__grid {
  display: contents;
}

.attendance-summary__item {
  display: grid;
  align-content: center;
  gap: 0.45rem;
  min-height: 104px;
  padding: 0.9rem 1rem;
  border: 2px solid #c9d9e8;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 10px 18px rgba(16, 44, 79, 0.05);
}

.attendance-summary__item span {
  color: #6d84a3;
  font-size: 1.14rem;
  font-weight: 800;
  line-height: 1.22;
}

.attendance-summary__item strong {
  color: #0f3c74;
  font-size: clamp(1.45rem, 1.2rem + 0.6vw, 1.95rem);
  font-weight: 900;
  line-height: 1.05;
}

.attendance-summary__metric-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.8rem;
}

.attendance-summary__actions {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
}

.attendance-summary__action {
  width: 42px;
  height: 42px;
  border: none;
  border-radius: 999px;
  background: #ff3743;
  color: #fff;
  font-size: 1.7rem;
  font-weight: 900;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.attendance-summary__action--increase {
  padding-bottom: 1px;
}

.attendance-summary__action--decrease {
  padding-bottom: 3px;
}

.attendance-summary__action:hover {
  filter: brightness(0.95);
}

.attendance-summary__action:focus-visible {
  outline: 3px solid rgba(15, 60, 116, 0.2);
  outline-offset: 2px;
}

.attendance-summary__total {
  display: grid;
  align-content: center;
  gap: 0.25rem;
  min-height: 104px;
  padding: 0.75rem 0.85rem;
  border-radius: 18px;
  background: transparent;
}

.attendance-summary__total span {
  color: #143761;
  font-size: clamp(1.06rem, 0.97rem + 0.28vw, 1.18rem);
  font-weight: 900;
  line-height: 1.1;
  white-space: nowrap;
}

.attendance-summary__total strong {
  color: #0f3c74;
  font-size: clamp(1.58rem, 1.25rem + 0.55vw, 2.05rem);
  font-weight: 900;
  line-height: 1;
}

.attendance-summary__manual-button {
  min-width: 92px;
  min-height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: none;
  border-radius: 999px;
  padding: 0.55rem 1rem;
  background: #0f3c74;
  color: #fff;
  font-weight: 800;
  line-height: 1;
}

.attendance-summary__manual-button:hover {
  filter: brightness(0.95);
}

.attendance-summary__manual-button:focus-visible {
  outline: 3px solid rgba(15, 60, 116, 0.2);
  outline-offset: 2px;
}

.attendance-summary__note {
  margin: 0;
  color: #4f6480;
  font-size: 0.98rem;
  line-height: 1.5;
}

.inline-dialog-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1210;
  display: grid;
  place-items: center;
  padding: 1rem;
  background: rgba(8, 18, 34, 0.38);
  backdrop-filter: blur(3px);
}

.inline-dialog {
  width: min(100%, 430px);
  display: grid;
  gap: 1rem;
  border-radius: 24px;
  padding: 1.25rem;
  background: #fff;
  box-shadow: 0 26px 60px rgba(12, 30, 58, 0.22);
}

.inline-dialog__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.inline-dialog__header h4 {
  margin: 0.2rem 0 0;
  color: #102f57;
  font-size: 1.25rem;
  font-weight: 900;
}

.inline-dialog__kicker {
  margin: 0;
  color: #c92a35;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.inline-dialog__field {
  display: grid;
  gap: 0.45rem;
  color: #27476e;
  font-weight: 700;
}

.inline-dialog__hint {
  margin: 0;
  color: #667993;
  font-size: 0.95rem;
  line-height: 1.5;
}

.inline-dialog__actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.editor-card--section {
  margin-top: 0.1rem;
  background: #f7fbff;
}

.form-grid label,
.stack-item label {
  display: grid;
  gap: 0.35rem;
  color: #1d385f;
  font-weight: 600;
}

.checkbox-field {
  display: flex !important;
  align-items: center;
  gap: 0.65rem;
  align-self: end;
  padding-bottom: 0.55rem;
}

.stack-list {
  display: grid;
  gap: 0.9rem;
  margin-top: 1rem;
}

.stack-item {
  border: 1px solid #bfd0e0;
  border-radius: 16px;
  background: #f7fbff;
  padding: 0.9rem;
  display: grid;
  gap: 0.85rem;
}

.stack-item__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.stack-item__title {
  color: #143761;
  font-size: 0.95rem;
}

.section-note {
  margin: 0.3rem 0 0;
  color: #4f6480;
  font-size: 0.92rem;
}

.section-header--compact {
  align-items: flex-start;
  margin-bottom: 0.7rem;
}

.section-subtitle {
  margin: 0;
  color: #143761;
  font-size: 1rem;
}

.section-toggle--compact {
  margin-top: 0;
}

.period-service-grid {
  margin-top: 1rem;
}

.personal-profile-grid {
  margin-top: 1rem;
}

.role-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 0.8rem;
  margin-top: 0.85rem;
}

.role-card {
  display: flex;
  gap: 0.75rem;
  align-items: flex-start;
  border: 1px solid #c9d8e6;
  border-radius: 16px;
  background: #fff;
  padding: 0.95rem 1rem;
  color: #173b70;
  cursor: pointer;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}

.role-card.selected {
  border-color: #d3272d;
  box-shadow: 0 0 0 3px rgba(211, 39, 45, 0.12);
  transform: translateY(-1px);
}

.role-card input {
  margin-top: 0.25rem;
  flex-shrink: 0;
}

.role-card small {
  display: block;
  margin-top: 0.2rem;
}

.section-toggle {
  display: flex !important;
  align-items: center;
  gap: 0.7rem;
  min-height: 48px;
  margin-top: 0.95rem;
  padding: 0.8rem 0.9rem;
  border: 1px solid #bfd1e2;
  border-radius: 14px;
  background: #f2f7fc;
  color: #153963;
  font-weight: 700;
}

.section-toggle span {
  display: inline;
}

.section-toggle input {
  width: 18px;
  height: 18px;
  accent-color: #c92a35;
}

.section-toggle__hint {
  margin: 0.6rem 0 0;
  color: #6a7d96;
  font-size: 0.9rem;
}

.icon-delete-button {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #efb1b6;
  border-radius: 12px;
  background: #fff1f2;
  color: #c92a35;
  font-size: 1rem;
  box-shadow: 0 6px 16px rgba(201, 42, 53, 0.14);
}

.icon-delete-button:hover {
  background: #ffe4e7;
  color: #a61d2a;
}

.attachment-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.attachment-error {
  margin: 0.2rem 0 0;
  padding: 0.7rem 0.9rem;
  border: 1px solid #f3b7bd;
  border-radius: 12px;
  background: #fff1f2;
  color: #b42318;
  font-size: 0.9rem;
  font-weight: 700;
  line-height: 1.4;
}

.attachment-preview {
  display: grid;
  gap: 0.65rem;
  padding: 0.85rem;
  border: 1px solid #c9d8e6;
  border-radius: 14px;
  background: #ffffff;
}

.attachment-preview__image {
  width: 100%;
  max-height: 280px;
  object-fit: contain;
  border-radius: 12px;
  background: #f6f9fc;
}

.attachment-preview__frame {
  width: 100%;
  height: 280px;
  border: 1px solid #d8e3ee;
  border-radius: 12px;
  background: #fff;
}

.attachment-preview__open {
  justify-self: start;
  color: #173b70;
  font-size: 0.88rem;
  font-weight: 700;
  text-decoration: none;
}

.attachment-preview__open:hover {
  text-decoration: underline;
}

.attachment-link {
  color: #173b70;
  font-weight: 700;
  text-decoration: none;
}

.attachment-link:hover {
  text-decoration: underline;
}

.attachment-badge {
  border-radius: 999px;
  background: #edf3fb;
  color: #173b70;
  padding: 0.35rem 0.7rem;
  font-size: 0.82rem;
  font-weight: 700;
}

.attachment-badge--warning {
  background: #fff1d9;
  color: #936d00;
}

.comments-field {
  display: grid;
  width: 100%;
  margin-top: 0.75rem;
}

.comments-textarea {
  width: 100%;
  min-width: 100%;
  min-height: 220px;
  display: block;
  box-sizing: border-box;
  resize: vertical;
}

.editor-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding-top: 0.25rem;
}

.recognition-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
  margin-top: 1rem;
}

.checkbox-card {
  display: flex;
  gap: 0.65rem;
  align-items: center;
  border: 1px solid #e5ebf3;
  border-radius: 14px;
  background: #fff;
  padding: 0.8rem 0.9rem;
  color: #1d385f;
  font-weight: 600;
}

@media (max-width: 991.98px) {
  .form-grid,
  .recognition-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .attendance-summary__layout {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .attendance-summary__total {
    grid-column: span 2;
    padding: 0.25rem 0;
  }

  .attendance-summary__action {
    width: 40px;
    height: 40px;
  }

  .form-grid__wide {
    grid-column: span 2;
  }
}

@media (max-width: 767.98px) {
  .editor-modal {
    padding: 0.6rem;
  }

  .editor-panel {
    max-height: calc(100vh - 1.2rem);
    border-radius: 20px;
  }

  .editor-panel__header,
  .editor-grid {
    padding-left: 0.95rem;
    padding-right: 0.95rem;
  }

  .form-grid,
  .recognition-grid {
    grid-template-columns: 1fr;
  }

  .attendance-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.8rem;
  flex-wrap: wrap;
  margin-bottom: 0.95rem;
}

.attendance-card__year-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 96px;
  min-height: 46px;
  padding: 0.45rem 1rem;
  border-radius: 16px;
  background: #ff3743;
  color: #fff;
  font-size: 1.3rem;
  font-weight: 900;
  line-height: 1;
  box-shadow: 0 10px 22px rgba(255, 55, 67, 0.18);
}

.attendance-card__year-input-row {
  margin-bottom: 0.95rem;
}

.attendance-summary {
    padding: 1rem;
  }

  .attendance-summary__layout {
    grid-template-columns: 1fr;
  }

  .attendance-summary__total {
    grid-column: auto;
    padding-left: 0;
  }

  .attendance-summary__metric-row {
    align-items: flex-end;
  }

  .attendance-summary__actions {
    justify-content: flex-start;
  }

  .attendance-summary__item {
    min-height: 94px;
  }

  .attendance-summary__item span {
    font-size: 1.05rem;
  }

  .form-grid__wide {
    grid-column: auto;
  }

  .editor-actions,
  .editor-footer {
    width: 100%;
    justify-content: stretch;
  }

  .editor-footer .btn,
  .editor-actions .period-chip {
    width: 100%;
    justify-content: center;
  }
}
</style>









































