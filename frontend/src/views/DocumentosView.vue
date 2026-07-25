<template>
  <div class="d-flex">
    <SidebarMenu />

    <div class="content-wrapper">
      <div class="content-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h3 class="m-0"><i class="fa-solid fa-folder-open me-2"></i>Documentos</h3>
            <p class="header-copy mb-0">Selecciona un tipo de documento y abre su formulario desde el botón de acceso.</p>
          </div>
          <span class="documents-chip">Módulo administrativo</span>
        </div>
      </div>

      <div class="content">
        <div class="documents-grid">
          <article
            v-for="documentType in documentTypes"
            :key="documentType.key"
            class="document-card"
            :class="{ active: selectedType === documentType.key }"
          >
            <div class="document-card__icon">
              <i :class="documentType.icon"></i>
            </div>
            <h4>{{ documentType.title }}</h4>
            <p>{{ documentType.description }}</p>
            <button type="button" class="btn btn-danger document-card__button" @click="openForm(documentType.key)">
              <i class="fa-solid fa-pen-to-square me-2"></i>Abrir formulario
            </button>
          </article>
        </div>

        <section v-if="selectedTypeMeta" class="form-shell card shadow-sm mt-4">
          <div class="card-body">
            <div class="form-shell__header">
              <div>
                <span class="form-badge">{{ selectedTypeMeta.shortLabel }}</span>
                <h4>{{ selectedTypeMeta.formTitle }}</h4>
                <p class="mb-0">Selecciona una actividad para precargar la información disponible y completar el resto manualmente.</p>
              </div>
              <button type="button" class="btn btn-outline-secondary btn-sm" @click="closeForm">
                Cerrar
              </button>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-12 col-lg-6">
                <label class="form-label">Actividad base</label>
                <div ref="actividadComboboxRef" class="activity-combobox">
                  <div class="activity-combobox__control" :class="{ 'is-open': showActividadMenu }">
                    <input
                      v-model.trim="activitySearchTerm"
                      type="text"
                      class="form-control activity-combobox__input"
                      placeholder="Selecciona o busca una actividad"
                      autocomplete="off"
                      @focus="handleActividadInputInteraction"
                      @click="handleActividadInputInteraction"
                      @input="handleActivitySearchInput"
                      @keydown.down.prevent="moveActividadHighlight(1)"
                      @keydown.up.prevent="moveActividadHighlight(-1)"
                      @keydown.enter.prevent="confirmHighlightedActividad"
                      @keydown.esc.prevent="closeActividadMenu"
                    >
                    <button
                      type="button"
                      class="activity-combobox__toggle"
                      :aria-expanded="showActividadMenu ? 'true' : 'false'"
                      aria-label="Mostrar actividades"
                      @click="toggleActividadMenu"
                    >
                      <i class="fa-solid fa-chevron-down"></i>
                    </button>
                  </div>

                  <div v-if="showActividadMenu" class="activity-combobox__menu">
                    <button
                      v-for="(actividad, index) in filteredActividades"
                      :key="actividad.id"
                      type="button"
                      class="activity-combobox__option"
                      :class="{
                        'is-active': index === highlightedActividadIndex,
                        'is-selected': String(actividad.id) === selectedActividadId
                      }"
                      @mousedown.prevent="selectActividad(actividad)"
                    >
                      <span class="activity-combobox__title">{{ actividad.nombre || 'Actividad sin nombre' }}</span>
                      <span class="activity-combobox__meta">
                        {{ actividad.filial?.nombre || 'Sin filial' }} · {{ formatDateRange(actividad.fecha_inicio, actividad.fecha_termino) }}
                      </span>
                    </button>

                    <div v-if="!filteredActividades.length" class="activity-combobox__empty">
                      No se encontraron actividades con ese nombre.
                    </div>
                  </div>
                </div>
                <small class="text-muted">Puedes escribir para buscar por nombre o abrir la lista con la flecha para seleccionar una actividad.</small>
              </div>

              <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label">Estado actual</label>
                <select v-model="form.estado" class="form-select" disabled>
                  <option value="borrador">Borrador</option>
                  <option value="final">Final</option>
                </select>
                <small class="text-muted">Puedes guardar el documento como borrador o marcarlo directamente como final.</small>
              </div>

              <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label">Fecha del documento</label>
                <input v-model="form.fecha_documento" type="date" class="form-control">
              </div>

              <div class="col-12">
                <label class="form-label">Título</label>
                <input v-model="form.titulo" type="text" class="form-control" :placeholder="selectedTypeMeta.placeholderTitle">
              </div>
            </div>

            <div v-if="loadingPrefill" class="loading-panel mt-3">
              <i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando datos de la actividad...
            </div>

            <template v-else>
              <div v-if="contexto" class="context-grid mt-4">
                <article class="context-card context-card--wide">
                  <span class="context-card__label">Actividad</span>
                  <strong>{{ contexto.actividad?.nombre || 'Sin nombre' }}</strong>
                  <small>{{ contexto.actividad?.tipo || 'Sin tipo' }}</small>
                </article>

                <article class="context-card">
                  <span class="context-card__label">Filial</span>
                  <strong>{{ contexto.filial?.nombre || 'Sin registro' }}</strong>
                  <small>{{ contexto.filial?.comuna || 'Sin comuna' }}</small>
                </article>

                <article class="context-card">
                  <span class="context-card__label">Periodo</span>
                  <strong>{{ formatDateRange(contexto.actividad?.fecha_inicio, contexto.actividad?.fecha_termino) }}</strong>
                  <small>{{ formatHours(contexto.actividad?.horas_totales) }}</small>
                </article>


                <article class="context-card">
                  <span class="context-card__label">Evidencias</span>
                  <strong>{{ contexto.resumen?.total_evidencias ?? 0 }}</strong>
                  <small>{{ contexto.resumen?.total_boletas ?? 0 }} boleta(s)</small>
                </article>
              </div>

              <div v-if="contexto" class="row g-3 mt-1">
                <div class="col-12 col-xl-7">
                  <div class="prefill-panel">
                    <h5>Información precargada</h5>
                    <div class="prefill-panel__block">
                      <span class="prefill-panel__label">Objetivo</span>
                      <p>{{ contexto.actividad?.objetivo || 'Sin objetivo registrado.' }}</p>
                    </div>
                    <div class="prefill-panel__block">
                      <span class="prefill-panel__label">Lugar</span>
                      <p>{{ contexto.actividad?.lugar || 'Sin lugar registrado.' }}</p>
                    </div>
                  </div>
                </div>

                <div class="col-12 col-xl-5">
                  <div class="prefill-panel">
                    <h5>Documentos guardados</h5>
                    <ul v-if="documentosGuardados.length" class="saved-documents">
                      <li
                        v-for="documento in documentosGuardados"
                        :key="documento.id"
                        :class="{ 'is-active': isSavedDocumentLoaded(documento) }"
                      >
                        <div>
                          <strong>{{ documento.titulo }}</strong>
                          <small>{{ formatSavedDocument(documento) }}</small>
                        </div>
                        <div class="saved-documents__actions">
                          <span class="saved-documents__state" :class="`state-${documento.estado || 'borrador'}`">
                            {{ documento.estado || 'borrador' }}
                          </span>
                          <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            @click="loadSavedDocument(documento)"
                          >
                            {{ isSavedDocumentLoaded(documento) ? 'Abierto' : 'Abrir' }}
                          </button>
                        </div>
                      </li>
                    </ul>
                    <p v-else class="mb-0 text-muted">Todavía no hay documentos guardados para esta actividad.</p>
                  </div>
                </div>
              </div>

              <template v-if="isNarrativeType">
                <div class="narrative-layout mt-4">
                  <section class="narrative-section">
                    <h5>Objetivos, horario y lugar</h5>
                    <div class="row g-3">
                      <div class="col-12 col-xl-6">
                        <label class="form-label">Objetivo general</label>
                        <textarea v-model.trim="form.contenido.objetivo_general" class="form-control" rows="4"></textarea>
                      </div>
                      <div class="col-12 col-xl-6">
                        <label class="form-label">Objetivo específico</label>
                        <textarea v-model.trim="form.contenido.objetivo_especifico" class="form-control" rows="4"></textarea>
                      </div>
                      <div class="col-12 col-md-6">
                        <label class="form-label">Horario</label>
                        <input v-model.trim="form.contenido.horario" type="text" class="form-control">
                      </div>
                      <div class="col-12 col-md-6">
                        <label class="form-label">Lugar</label>
                        <input v-model.trim="form.contenido.lugar" type="text" class="form-control">
                      </div>
                    </div>
                  </section>

                  <section class="narrative-section">
                    <h5>Descripción General</h5>
                    <p class="narrative-note">Breve narrativo de la actividad. No más de 10 renglones.</p>
                    <textarea v-model.trim="form.contenido.descripcion_general" class="form-control" rows="6"></textarea>

                    <div class="row g-3 mt-3 align-items-start">
                      <div class="col-12 col-xl-6">
                        <div class="section-headline">
                          <h6 class="m-0">Personas asistidas</h6>
                          <button type="button" class="btn btn-outline-primary btn-sm" @click="addNarrativeCountRow()">
                            <i class="fa-solid fa-plus me-2"></i>Agregar fila
                          </button>
                        </div>

                        <div class="narrative-count-list">
                          <div v-for="(row, index) in form.contenido.recuentos_personas_asistidas" :key="row.id" class="narrative-count-row row g-2 align-items-end">
                            <div class="col-12 col-md-7">
                              <label class="form-label">Tipo o nombre del actor</label>
                              <input v-model.trim="row.tipo" type="text" class="form-control" placeholder="Ej. Mujeres, Enfermería, Traslados">
                            </div>
                            <div class="col-12 col-md-4">
                              <label class="form-label">Número</label>
                              <input v-model.number="row.numero" type="number" min="0" class="form-control">
                            </div>
                            <div class="col-12 col-md-1 d-flex justify-content-md-end">
                              <button type="button" class="attendance-row__remove" @click="removeNarrativeCountRow(index)">
                                <i class="fa-solid fa-trash"></i>
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-12 col-xl-6">
                        <div class="row g-3">
                          <div class="col-12">
                            <label class="form-label">Situaciones de interés ocurridas</label>
                            <textarea v-model.trim="form.contenido.situaciones_interes" class="form-control" rows="4"></textarea>
                          </div>
                          <div class="col-12">
                            <label class="form-label">N° de Puestos (por región/comuna y ubicación de estos)</label>
                            <textarea v-model.trim="form.contenido.numero_puestos" class="form-control" rows="4"></textarea>
                          </div>
                        </div>
                      </div>
                    </div>
                  </section>

                  <section class="narrative-section">
                    <div class="section-headline">
                      <h5>Participantes (Lista de asistencia)</h5>
                      <button type="button" class="btn btn-outline-primary btn-sm" @click="addNarrativeParticipantRow()">
                        <i class="fa-solid fa-plus me-2"></i>Agregar fila
                      </button>
                    </div>
                    <p class="narrative-note">Registra tipos o nombres de actores y la cantidad correspondiente.</p>

                    <div class="narrative-count-list">
                      <div class="narrative-count-row row g-3 align-items-center">
                        <div class="col-12 col-xl-7">
                          <strong>Voluntarios inscritos</strong>
                          <p class="mb-0 text-muted small">Carga automáticamente a los usuarios con rol exclusivo de voluntario y excluye a quienes tienen otro rol adicional.</p>
                        </div>
                        <div class="col-12 col-xl-5 d-flex justify-content-xl-end">
                          <button
                            type="button"
                            class="btn btn-outline-secondary btn-sm"
                            :disabled="!eligibleNarrativeVolunteerCount"
                            @click="loadNarrativeVolunteerParticipants()"
                          >
                            <i class="fa-solid fa-users me-2"></i>Cargar voluntarios inscritos{{ eligibleNarrativeVolunteerCount ? ' (' + eligibleNarrativeVolunteerCount + ')' : '' }}
                          </button>
                        </div>
                      </div>

                      <div v-for="(row, index) in form.contenido.participantes_asistencia" :key="row.id" class="narrative-count-row row g-2 align-items-end">
                        <div class="col-12 col-md-7">
                          <label class="form-label">Tipo o nombre del actor</label>
                          <input v-model.trim="row.tipo" type="text" class="form-control" placeholder="Ej. Staff Psicólogos, Voluntarios inscritos">
                        </div>
                        <div class="col-12 col-md-4">
                          <label class="form-label">Número</label>
                          <input v-model.number="row.numero" type="number" min="0" class="form-control">
                        </div>
                        <div class="col-12 col-md-1 d-flex justify-content-md-end">
                          <button type="button" class="attendance-row__remove" @click="removeNarrativeParticipant(index)">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                        </div>
                      </div>
                    </div>

                    <div v-if="!form.contenido.participantes_asistencia.length" class="empty-inline">Todavía no hay participantes cargados.</div>
                  </section>

                  <section class="narrative-section">
                    <h5>Observaciones Generales</h5>
                    <div class="row g-3">
                      <div v-for="field in narrativeObservationFields" :key="field.key" class="col-12 col-xl-6">
                        <label class="form-label">{{ field.label }}</label>
                        <textarea v-model.trim="form.contenido.observaciones_generales[field.key]" class="form-control" rows="4"></textarea>
                      </div>
                    </div>
                  </section>

                  <section class="narrative-section">
                    <h5>Cierre del Informe</h5>
                    <div class="row g-3">
                      <div class="col-12">
                        <label class="form-label">Autorización del Informe</label>
                        <input
                          v-model.trim="form.contenido.autorizacion_informe"
                          type="text"
                          class="form-control"
                          list="authorization-volunteer-options"
                          placeholder="Busca un voluntario con cargo"
                          @change="applyAuthorizationCandidate($event.target.value)"
                          @blur="applyAuthorizationCandidate($event.target.value)"
                        >
                        <datalist id="authorization-volunteer-options">
                          <option
                            v-for="option in authorizationVolunteerOptions"
                            :key="option.value"
                            :value="option.value"
                          >
                            {{ option.label }}
                          </option>
                        </datalist>
                        <small class="text-muted">Busca y selecciona un voluntario con cargo. Al elegirlo se guardará como Nombre - Cargo en el documento.</small>
                      </div>
                    </div>
                  </section>

                  <section class="narrative-section">
                    <div class="section-headline">
                      <h5>Adjunte fotografías JPG y agregue su descripción</h5>
                      <button type="button" class="btn btn-danger btn-sm" @click="openPhotoModal()">
                        <i class="fa-solid fa-images me-2"></i>Agregar fotos
                      </button>
                    </div>
                    <p class="narrative-note">En el siguiente cuadro adjunte las fotos en formato JPG y al lado agregue la descripción. 3 fotografías mínimo.</p>

                    <div v-if="form.contenido.fotos.length" class="photo-report-grid">
                      <article v-for="(photo, index) in visibleNarrativePhotos" :key="photo.id" class="photo-report-card">
                        <img :src="photo.imagen_url" :alt="photo.titulo || `Fotografía ${narrativePhotoStartIndex + index + 1}`" class="photo-report-card__image">
                        <div class="photo-report-card__body">
                          <strong>{{ photo.titulo || `Fotografía ${narrativePhotoStartIndex + index + 1}` }}</strong>
                          <small>{{ photo.origen === 'galeria_actividad' ? 'Galería de la actividad' : 'Subida desde dispositivo' }}</small>
                          <label class="form-label">Descripción</label>
                          <textarea v-model.trim="photo.descripcion_informe" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="button" class="photo-report-card__remove" @click="removeNarrativePhoto(narrativePhotoStartIndex + index)">
                          <i class="fa-solid fa-trash me-2"></i>Quitar
                        </button>
                      </article>
                    </div>
                    <nav v-if="showNarrativePhotoNavigation" class="photo-report-navigation" aria-label="Navegación de fotografías del informe">
                      <button type="button" class="photo-report-navigation__button" :disabled="narrativePhotoPage === 1" aria-label="Ver fotografías anteriores" @click="goToPreviousNarrativePhotos">
                        <i class="fa-solid fa-chevron-left"></i>
                      </button>
                      <span>{{ narrativePhotoPage }} de {{ narrativePhotoPageCount }}</span>
                      <button type="button" class="photo-report-navigation__button" :disabled="narrativePhotoPage === narrativePhotoPageCount" aria-label="Ver fotografías siguientes" @click="goToNextNarrativePhotos">
                        <i class="fa-solid fa-chevron-right"></i>
                      </button>
                    </nav>
                    <div v-if="!form.contenido.fotos.length" class="empty-inline">Aún no has agregado fotografías al informe.</div>
                  </section>
                </div>
              </template>

              <template v-else-if="isContextAnalysisType">
                <div class="narrative-layout mt-4">
                  <section class="narrative-section">
                    <h5>Objetivo del análisis</h5>
                    <textarea
                      v-model.trim="form.contenido.proposito_documento"
                      class="form-control"
                      rows="5"
                      placeholder="Describe el objetivo del análisis, el contexto del apoyo solicitado y el alcance del documento."
                    ></textarea>
                  </section>

                  <section class="narrative-section">
                    <h5>Descripción del evento</h5>
                    <div class="row g-3">
                      <div class="col-12 col-xl-6">
                        <label class="form-label">Nombre del evento</label>
                        <input v-model.trim="form.contenido.descripcion_evento.nombre_evento" type="text" class="form-control">
                      </div>
                      <div class="col-12 col-md-6 col-xl-3">
                        <label class="form-label">Fecha</label>
                        <input v-model.trim="form.contenido.descripcion_evento.fecha_evento" type="text" class="form-control" placeholder="Ej. 31 de agosto de 2024">
                      </div>
                      <div class="col-12 col-md-6 col-xl-3">
                        <label class="form-label">Horario</label>
                        <input v-model.trim="form.contenido.descripcion_evento.horario_evento" type="text" class="form-control" placeholder="09:30 - 15:00">
                      </div>
                      <div class="col-12 col-xl-6">
                        <label class="form-label">Lugar</label>
                        <input v-model.trim="form.contenido.descripcion_evento.lugar_evento" type="text" class="form-control">
                      </div>
                      <div class="col-12 col-md-6 col-xl-3">
                        <label class="form-label">Participantes</label>
                        <input v-model.trim="form.contenido.descripcion_evento.participantes_evento" type="text" class="form-control" placeholder="Ej. 150 jugadores">
                      </div>
                      <div class="col-12 col-md-6 col-xl-3">
                        <label class="form-label">Organizador</label>
                        <input v-model.trim="form.contenido.descripcion_evento.organizador_evento" type="text" class="form-control">
                      </div>
                      <div class="col-12">
                        <div class="section-headline mb-2">
                          <div>
                            <label class="form-label mb-1">Clima esperado</label>
                            <p class="narrative-note mb-0">Registra uno o varios climas con mínima, máxima, tipo de clima y una imagen asociada. Puedes subirla desde el dispositivo, pegarla desde el portapapeles o usar una foto ya guardada en la galería de la actividad.</p>
                          </div>
                          <button type="button" class="btn btn-outline-primary btn-sm" @click="addAnalysisClimateRow()">
                            <i class="fa-solid fa-plus me-2"></i>Agregar clima
                          </button>
                        </div>

                        <input
                          ref="climatePhotoInputRef"
                          type="file"
                          class="visually-hidden"
                          accept="image/jpeg,image/jpg,image/png,image/webp"
                          @change="onClimatePhotoFileSelected"
                        >

                        <div class="climate-list">
                          <article v-for="(climate, index) in analysisClimateRows" :key="climate.id" class="climate-card row g-3 align-items-start">
                            <div class="col-12 col-md-3">
                              <label class="form-label">Mínima</label>
                              <div class="input-group">
                                <input v-model="climate.temperatura_minima" type="number" step="0.1" class="form-control" placeholder="Ej. 4">
                                <span class="input-group-text">°C</span>
                              </div>
                            </div>
                            <div class="col-12 col-md-3">
                              <label class="form-label">Máxima</label>
                              <div class="input-group">
                                <input v-model="climate.temperatura_maxima" type="number" step="0.1" class="form-control" placeholder="Ej. 17">
                                <span class="input-group-text">°C</span>
                              </div>
                            </div>
                            <div class="col-12 col-md-5">
                              <label class="form-label">Tipo de clima</label>
                              <input v-model.trim="climate.tipo_clima" type="text" class="form-control" placeholder="Ej. Parcialmente nublado">
                            </div>
                            <div class="col-12 col-md-1 d-flex justify-content-md-end">
                              <button type="button" class="attendance-row__remove mt-md-4" @click="removeAnalysisClimateRow(index)">
                                <i class="fa-solid fa-trash"></i>
                              </button>
                            </div>

                            <div class="col-12">
                              <div class="climate-card__media">
                                <div v-if="climate.imagen_url" class="climate-card__preview">
                                  <img :src="climate.imagen_url" :alt="climate.titulo_imagen || ('Clima ' + (index + 1))">
                                </div>
                                <div v-else class="climate-card__empty">
                                  Sin fotografía asociada.
                                </div>

                                <div class="climate-card__actions">
                                  <button type="button" class="btn btn-outline-primary btn-sm" :disabled="uploadingClimatePhoto" @click="openClimatePhotoPicker(climate.id)">
                                    <i class="fa-solid fa-folder-open me-2"></i>Subir foto
                                  </button>
                                  <button type="button" class="btn btn-outline-secondary btn-sm" :disabled="pastingClimatePhoto" @click="pasteClimatePhoto(climate.id)">
                                    <i class="fa-regular fa-clipboard me-2"></i>{{ pastingClimatePhoto ? 'Pegando...' : 'Pegar portapapeles' }}
                                  </button>
                                  <button type="button" class="btn btn-outline-dark btn-sm" @click="openClimateGalleryModal(climate.id)">
                                    <i class="fa-solid fa-images me-2"></i>Elegir de galería
                                  </button>
                                  <button v-if="climate.imagen_url" type="button" class="btn btn-outline-danger btn-sm" @click="clearClimatePhoto(climate.id)">
                                    <i class="fa-solid fa-trash me-2"></i>Quitar foto
                                  </button>
                                </div>
                              </div>
                            </div>
                          </article>
                        </div>
                      </div>
                    </div>
                  </section>

                  <section class="narrative-section">
                    <div class="section-headline">
                      <h5>Identificación de riesgos</h5>
                      <button type="button" class="btn btn-outline-primary btn-sm" @click="addAnalysisRiskRow()">
                        <i class="fa-solid fa-plus me-2"></i>Agregar riesgo
                      </button>
                    </div>

                    <div class="narrative-count-list">
                      <div v-for="(risk, index) in form.contenido.riesgos" :key="risk.id" class="narrative-count-row row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                          <label class="form-label">Riesgo</label>
                          <input v-model.trim="risk.nombre" type="text" class="form-control" placeholder="Ej. Lesiones deportivas">
                        </div>
                        <div class="col-12 col-xl-4">
                          <label class="form-label">Probabilidad</label>
                          <div class="risk-option-group">
                            <label v-for="option in analysisProbabilityOptions" :key="`probabilidad-${risk.id}-${option}`" class="risk-option">
                              <input
                                :checked="isRiskOptionSelected(risk.probabilidad, option)"
                                type="checkbox"
                                class="form-check-input"
                                @change="toggleRiskOption(risk, 'probabilidad', option)"
                              >
                              <span>{{ option }}</span>
                            </label>
                          </div>
                        </div>
                        <div class="col-12 col-xl-3">
                          <label class="form-label">Impacto</label>
                          <div class="risk-option-group">
                            <label v-for="option in analysisImpactOptions" :key="`impacto-${risk.id}-${option}`" class="risk-option">
                              <input
                                :checked="isRiskOptionSelected(risk.impacto, option)"
                                type="checkbox"
                                class="form-check-input"
                                @change="toggleRiskOption(risk, 'impacto', option)"
                              >
                              <span>{{ option }}</span>
                            </label>
                          </div>
                        </div>
                        <div class="col-12 col-xl-1 d-flex justify-content-xl-end">
                          <button type="button" class="attendance-row__remove" @click="removeAnalysisRiskRow(index)">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                        </div>
                        <div class="col-12 col-xl-6">
                          <label class="form-label">Descripción</label>
                          <textarea v-model.trim="risk.descripcion" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="col-12 col-xl-6">
                          <label class="form-label">Medidas de mitigación</label>
                          <textarea v-model.trim="risk.mitigacion" class="form-control" rows="4"></textarea>
                        </div>
                      </div>
                    </div>
                  </section>

                  <section class="narrative-section">
                    <h5>Plan de traslados</h5>
                    <textarea v-model.trim="form.contenido.plan_traslados" class="form-control" rows="5"></textarea>
                  </section>

                  <section class="narrative-section">
                    <h5>Protocolo de traslado</h5>
                    <textarea v-model.trim="form.contenido.protocolo_traslado" class="form-control" rows="5"></textarea>
                  </section>

                  <section class="narrative-section">
                    <h5>Centros de salud cercanos</h5>
                    <textarea v-model.trim="form.contenido.centros_salud" class="form-control" rows="5"></textarea>
                  </section>

                  <section class="narrative-section">
                    <h5>Conclusión</h5>
                    <textarea v-model.trim="form.contenido.conclusion" class="form-control" rows="5"></textarea>
                  </section>

                  <section class="narrative-section">
                    <h5>Observaciones complementarias</h5>
                    <textarea v-model.trim="form.contenido.observaciones_finales" class="form-control" rows="5" placeholder="Notas de seguimiento, ajustes o precisiones finales."></textarea>
                  </section>
                </div>
              </template>

              <div v-else class="content-fields mt-4">
                <div v-for="section in currentSections" :key="section.key" class="content-field">
                  <label class="form-label">{{ section.label }}</label>
                  <textarea
                    v-model="form.contenido[section.key]"
                    class="form-control"
                    rows="4"
                    :placeholder="section.placeholder"
                  ></textarea>
                </div>
              </div>
            </template>

            <div class="form-actions mt-4">
              <button type="button" class="btn btn-outline-secondary" @click="saveDocument('borrador')" :disabled="isSaving || !canSubmit">
                <i class="fa-regular fa-floppy-disk me-2"></i>Guardar borrador
              </button>
              <button
                type="button"
                class="btn btn-outline-danger"
                @click="exportDocumentPdf()"
                :disabled="isSaving || !canSubmit"
              >
                <i class="fa-regular fa-file-pdf me-2"></i>{{ isSaving ? 'Preparando...' : 'Exportar PDF' }}
              </button>
              <button type="button" class="btn btn-danger" @click="handlePrimaryDocumentAction()" :disabled="isSaving || !canSubmit">
                <i class="fa-solid fa-circle-check me-2"></i>{{ isSaving ? 'Guardando...' : 'Guardar final' }}
              </button>
            </div>
          </div>
        </section>
      </div>
    </div>

    <div v-if="showPhotoPickerModal" class="photo-modal">
      <section class="photo-modal__panel" role="dialog" aria-modal="true" aria-labelledby="photo-modal-title">
        <div class="photo-modal__header">
          <div>
            <span class="form-badge">Fotografías</span>
            <h5 id="photo-modal-title">Seleccionar evidencias del informe narrativo</h5>
            <p class="mb-0">Elige entre cargar fotos nuevas desde el dispositivo o reutilizar imágenes ya asociadas a la actividad.</p>
          </div>
          <button type="button" class="btn btn-outline-secondary btn-sm" @click="closePhotoModal">Cerrar</button>
        </div>

        <div class="photo-modal__tabs">
          <button type="button" class="photo-modal__tab" :class="{ active: photoModalTab === 'device' }" @click="openDeviceGalleryTab">Galería del dispositivo</button>
          <button type="button" class="photo-modal__tab" :class="{ active: photoModalTab === 'activity' }" @click="photoModalTab = 'activity'">Fotos por actividad</button>
        </div>

        <div v-if="photoModalTab === 'device'" class="photo-modal__content">
          <div class="photo-upload-box">
            <label class="form-label">Seleccionar fotografías desde el dispositivo</label>
            <button type="button" class="btn btn-outline-primary mb-2" @click="openDevicePhotoPicker">
              <i class="fa-solid fa-folder-open me-2"></i>Abrir documentos o galería
            </button>
            <input
              ref="devicePhotoInputRef"
              type="file"
              class="visually-hidden"
              accept="image/jpeg,image/jpg,image/png,image/webp"
              multiple
              @change="onNarrativePhotoFilesSelected"
            >
            <small class="text-muted">El selector abrirá los documentos o la galería del dispositivo donde corre el sistema. Las imágenes quedarán asociadas a la actividad actual.</small>

            <ul v-if="pendingNarrativePhotoFiles.length" class="pending-upload-list">
              <li v-for="file in pendingNarrativePhotoFiles" :key="`${file.name}-${file.lastModified}`">{{ file.name }}</li>
            </ul>

            <button type="button" class="btn btn-danger mt-3" :disabled="uploadingNarrativePhotos || !pendingNarrativePhotoFiles.length" @click="uploadNarrativePhotosFromDevice">
              <i class="fa-solid fa-upload me-2"></i>{{ uploadingNarrativePhotos ? 'Subiendo...' : 'Subir y agregar al informe' }}
            </button>
          </div>
        </div>

        <div v-else class="photo-modal__content">
          <div v-if="availableNarrativeGalleryPhotos.length" class="photo-gallery-grid">
            <article v-for="item in availableNarrativeGalleryPhotos" :key="item.id" class="photo-gallery-card">
              <img :src="item.imagen_url" :alt="item.titulo || 'Imagen de actividad'" class="photo-gallery-card__image">
              <div class="photo-gallery-card__body">
                <strong>{{ item.titulo || 'Sin título' }}</strong>
                <p>{{ item.descripcion || 'Sin descripción registrada.' }}</p>
                <small>{{ item.fecha ? formatDate(item.fecha) : 'Sin fecha' }}</small>
              </div>
              <button type="button" class="btn btn-outline-primary btn-sm" :disabled="isGalleryPhotoSelected(item)" @click="addGalleryPhotoToNarrative(item)">
                {{ isGalleryPhotoSelected(item) ? 'Agregada' : 'Usar en informe' }}
              </button>
            </article>
          </div>
          <div v-else class="empty-inline">Esta actividad aún no tiene imágenes guardadas en su galería.</div>
        </div>
      </section>
    </div>

    <div v-if="showClimateGalleryModal" class="photo-modal">
      <section class="photo-modal__panel" role="dialog" aria-modal="true" aria-labelledby="climate-gallery-title">
        <div class="photo-modal__header">
          <div>
            <span class="form-badge">Clima esperado</span>
            <h5 id="climate-gallery-title">Seleccionar fotografía para el clima</h5>
            <p class="mb-0">Usa una imagen ya asociada a la galería de la actividad para este registro de clima.</p>
          </div>
          <button type="button" class="btn btn-outline-secondary btn-sm" @click="closeClimateGalleryModal">Cerrar</button>
        </div>

        <div v-if="availableNarrativeGalleryPhotos.length" class="photo-gallery-grid">
          <article v-for="item in availableNarrativeGalleryPhotos" :key="`climate-${item.id}`" class="photo-gallery-card">
            <img :src="item.imagen_url" :alt="item.titulo || 'Imagen de actividad'" class="photo-gallery-card__image">
            <div class="photo-gallery-card__body">
              <strong>{{ item.titulo || 'Sin título' }}</strong>
              <p>{{ item.descripcion || 'Sin descripción registrada.' }}</p>
              <small>{{ item.fecha ? formatDate(item.fecha) : 'Sin fecha' }}</small>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" @click="selectGalleryPhotoForClimate(item)">
              Usar en este clima
            </button>
          </article>
        </div>
        <div v-else class="empty-inline">Esta actividad aún no tiene imágenes guardadas en su galería.</div>
      </section>
    </div>  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from 'vuex'
import axios from 'axios'
import Swal from 'sweetalert2'
import SidebarMenu from '../components/SidebarMenu.vue'
import { buildApiUrl } from '../config/api'
import { optimizeImage } from '../utils/imageOptimization'
import { formatDate, formatDateRange } from '../utils/formatters'

const documentTypes = [
  {
    key: 'analisis_contexto',
    title: 'Análisis de contexto',
    shortLabel: 'Análisis',
    formTitle: 'Formulario de análisis de contexto',
    placeholderTitle: 'Ej. Análisis de contexto - Operativo invierno 2026',
    description: 'Prepara y gestiona análisis de contexto vinculados a actividades.',
    icon: 'fa-solid fa-map',
    sections: []
  },
  {
    key: 'informe_narrativo',
    title: 'Informe Narrativo',
    shortLabel: 'Informe',
    formTitle: 'Formulario de informe narrativo',
    placeholderTitle: 'Ej. Operativo invierno 2026',
    description: 'Centraliza la emisión de informes narrativos vinculados a actividades.',
    icon: 'fa-solid fa-file-lines',
    sections: [
      { key: 'resumen', label: 'Resumen', placeholder: 'Resume la actividad y sus resultados principales.' },
      { key: 'introduccion', label: 'Introducción', placeholder: 'Introduce el contexto general del informe.' },
      { key: 'metodologia', label: 'Metodología', placeholder: 'Explica la metodología utilizada.' },
      { key: 'desarrollo', label: 'Desarrollo', placeholder: 'Describe el desarrollo de la actividad.' },
      { key: 'participacion_comunitaria', label: 'Participación comunitaria', placeholder: 'Detalla la participación de la comunidad o voluntariado.' },
      { key: 'resultados', label: 'Resultados', placeholder: 'Describe los resultados obtenidos.' },
      { key: 'dificultades', label: 'Dificultades', placeholder: 'Registra dificultades o incidentes.' },
      { key: 'conclusiones', label: 'Conclusiones', placeholder: 'Escribe las conclusiones finales.' },
      { key: 'recomendaciones', label: 'Recomendaciones', placeholder: 'Anota recomendaciones para futuras acciones.' },
      { key: 'observaciones', label: 'Observaciones', placeholder: 'Agrega observaciones complementarias.' }
    ]
  }
]

const narrativeMetricFields = [
  { key: 'atenciones_ppaa', label: 'N° de Atenciones PPAA' },
  { key: 'asistencias_movilidad_reducida', label: 'N° de Asistencias Movilidad Reducida' },
  { key: 'votos_asistidos', label: 'N° de Votos Asistidos' },
  { key: 'traslados', label: 'N° de Traslados' },
  { key: 'hombres', label: 'N° de Hombres' },
  { key: 'mujeres', label: 'N° de Mujeres' }
]

const narrativeObservationFields = [
  { key: 'logros', label: 'Logros' },
  { key: 'desafios_dificultades', label: 'Desafíos y dificultades' },
  { key: 'recomendaciones_acciones', label: 'Recomendaciones y acciones por mejorar' },
  { key: 'percepcion', label: 'Percepción: Opinión subjetiva del supervisor o encargado de la actividad.' }
]

const analysisProbabilityOptions = ['Baja', 'Media', 'Alta']
const analysisImpactOptions = ['Bajo', 'Medio', 'Alto']

const store = useStore()
const currentUser = computed(() => store.getters.authUser)
const actividades = ref([])
const selectedType = ref('')
const selectedActividadId = ref('')
const activitySearchTerm = ref('')
const showActividadMenu = ref(false)
const showAllActividades = ref(false)
const highlightedActividadIndex = ref(-1)
const actividadComboboxRef = ref(null)
const showPhotoPickerModal = ref(false)
const photoModalTab = ref('device')
const devicePhotoInputRef = ref(null)
const climatePhotoInputRef = ref(null)
const selectedClimateRowId = ref('')
const showClimateGalleryModal = ref(false)
const pendingNarrativePhotoFiles = ref([])
const narrativePhotoPage = ref(1)
const uploadingNarrativePhotos = ref(false)
const uploadingClimatePhoto = ref(false)
const pastingClimatePhoto = ref(false)
const participantIdSeed = ref(0)
const loadingPrefill = ref(false)
const isSaving = ref(false)
const documentosGuardados = ref([])
const currentSavedDocumentId = ref(null)
const route = useRoute()
const router = useRouter()

const form = reactive({
  titulo: '',
  estado: 'borrador',
  fecha_documento: todayAsInput(),
  datos_contexto: null,
  contenido: {}
})

const selectedTypeMeta = computed(() => documentTypes.find((item) => item.key === selectedType.value) || null)
const currentSections = computed(() => selectedTypeMeta.value?.sections || [])
const contexto = computed(() => form.datos_contexto)
const isNarrativeType = computed(() => selectedType.value === 'informe_narrativo')
const isContextAnalysisType = computed(() => selectedType.value === 'analisis_contexto')
const narrativePhotoPageSize = 3
const narrativePhotos = computed(() => Array.isArray(form.contenido?.fotos) ? form.contenido.fotos : [])
const narrativePhotoPageCount = computed(() => Math.max(1, Math.ceil(narrativePhotos.value.length / narrativePhotoPageSize)))
const narrativePhotoStartIndex = computed(() => (narrativePhotoPage.value - 1) * narrativePhotoPageSize)
const visibleNarrativePhotos = computed(() => narrativePhotos.value.slice(narrativePhotoStartIndex.value, narrativePhotoStartIndex.value + narrativePhotoPageSize))
const showNarrativePhotoNavigation = computed(() => narrativePhotos.value.length > narrativePhotoPageSize)
const selectedActividad = computed(() => actividades.value.find((actividad) => String(actividad.id) === selectedActividadId.value) || null)
const filteredActividades = computed(() => {
  if (showAllActividades.value) {
    return actividades.value
  }

  const normalizedTerm = normalizeActividadSearch(activitySearchTerm.value)

  return actividades.value.filter((actividad) => {
    if (!normalizedTerm) {
      return true
    }

    return [
      actividad.nombre,
      actividad.filial?.nombre,
      actividadLabel(actividad)
    ].some((value) => normalizeActividadSearch(value).includes(normalizedTerm))
  })
})
const canSubmit = computed(() => Boolean(selectedType.value && selectedActividadId.value && form.titulo.trim()))
const availableNarrativeGalleryPhotos = computed(() => normalizeGalleryItems(contexto.value?.galeria))
const analysisClimateRows = computed(() => Array.isArray(form.contenido?.descripcion_evento?.climas_esperados) ? form.contenido.descripcion_evento.climas_esperados : [])
const eligibleNarrativeVolunteerCount = computed(() => {
  if (!Array.isArray(contexto.value?.participantes)) {
    return 0
  }

  return contexto.value.participantes.filter((participant) => isEligibleNarrativeVolunteerParticipant(participant)).length
})
const authorizationVolunteerOptions = computed(() => {
  if (!Array.isArray(contexto.value?.participantes)) {
    return []
  }

  const uniqueCandidates = new Map()

  contexto.value.participantes.forEach((participant) => {
    const candidate = buildAuthorizationCandidate(participant)
    if (!candidate || uniqueCandidates.has(candidate.value)) {
      return
    }

    uniqueCandidates.set(candidate.value, candidate)
  })

  return Array.from(uniqueCandidates.values())
    .sort((a, b) => a.label.localeCompare(b.label, 'es'))
})

function nextParticipantId() {
  participantIdSeed.value += 1
  return `participant-${participantIdSeed.value}`
}

function normalizeGalleryItems(items) {
  if (!Array.isArray(items)) {
    return []
  }

  return items
    .map((item) => ({
      id: item?.id,
      archivo_id: item?.archivo_id || item?.archivo?.id || null,
      titulo: item?.titulo || item?.archivo?.nombre_original || '',
      descripcion: item?.descripcion || item?.archivo?.descripcion || '',
      fecha: item?.fecha || '',
      imagen_url: item?.imagen_url || item?.archivo?.url_publica || '',
      origen: item?.origen || 'galeria_actividad'
    }))
    .filter((item) => item.imagen_url)
}

function extractParticipantRoleKeys(participant) {
  if (!Array.isArray(participant?.roles)) {
    return []
  }

  return participant.roles
    .map((role) => {
      if (typeof role === 'string') {
        return normalizeActividadSearch(role)
      }

      return normalizeActividadSearch(role?.clave || role?.nombre || '')
    })
    .filter(Boolean)
}

function isEligibleNarrativeVolunteerParticipant(participant) {
  const roles = extractParticipantRoleKeys(participant)
  return roles.length === 1 && roles[0] === 'voluntario'
}

function extractParticipantRoleLabels(participant) {
  if (!Array.isArray(participant?.roles)) {
    return []
  }

  return participant.roles
    .map((role) => {
      if (typeof role === 'string') {
        return String(role).trim()
      }

      return String(role?.nombre || role?.clave || '').trim()
    })
    .filter(Boolean)
}

function buildAuthorizationCandidate(participant) {
  const name = String(participant?.nombre || '').trim()
  if (!name) {
    return null
  }

  const roleKeys = extractParticipantRoleKeys(participant)
  const roleLabels = extractParticipantRoleLabels(participant)
  const additionalRoles = roleLabels.filter((label, index) => roleKeys[index] && roleKeys[index] !== 'voluntario')

  if (!additionalRoles.length) {
    return null
  }

  const primaryRole = additionalRoles[0]
  return {
    label: `${name} - ${primaryRole}`,
    value: `${name} - ${primaryRole}`,
    name,
    role: primaryRole
  }
}

function applyAuthorizationCandidate(value) {
  const normalizedValue = normalizeActividadSearch(value)
  const match = authorizationVolunteerOptions.value.find((option) => normalizeActividadSearch(option.value) === normalizedValue)

  if (match) {
    form.contenido.autorizacion_informe = match.value
    return
  }

  form.contenido.autorizacion_informe = String(value || '').trim()
}

function normalizeRiskOptionValues(value) {
  if (Array.isArray(value)) {
    return value
      .map((item) => String(item || '').trim())
      .filter(Boolean)
  }

  const normalized = String(value || '').trim()
  if (!normalized) {
    return []
  }

  return normalized
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean)
}

function isRiskOptionSelected(value, option) {
  return normalizeRiskOptionValues(value).includes(option)
}

function toggleRiskOption(risk, field, option) {
  const nextValues = normalizeRiskOptionValues(risk[field])

  if (nextValues.includes(option)) {
    risk[field] = nextValues.filter((item) => item !== option)
    return
  }

  risk[field] = [...nextValues, option]
}

function createEmptyAnalysisRiskRow(nombre = '', descripcion = '', probabilidad = '', impacto = '', mitigacion = '') {
  return {
    id: nextParticipantId(),
    nombre,
    descripcion,
    probabilidad: normalizeRiskOptionValues(probabilidad),
    impacto: normalizeRiskOptionValues(impacto),
    mitigacion
  }
}

function createEmptyAnalysisClimateRow(temperaturaMinima = '', temperaturaMaxima = '', tipoClima = '', source = {}) {
  return {
    id: source?.id || nextParticipantId(),
    temperatura_minima: temperaturaMinima ?? '',
    temperatura_maxima: temperaturaMaxima ?? '',
    tipo_clima: tipoClima || '',
    archivo_id: source?.archivo_id || null,
    imagen_url: source?.imagen_url || source?.archivo?.url_publica || '',
    titulo_imagen: source?.titulo_imagen || source?.titulo || source?.archivo?.nombre_original || '',
    descripcion_imagen: source?.descripcion_imagen || source?.descripcion || source?.archivo?.descripcion || '',
    fecha: source?.fecha || '',
    origen: source?.origen || 'galeria_actividad',
    es_imagen_temporal: source?.es_imagen_temporal ?? (source?.archivo?.categoria === 'clima_documento')
  }
}

function normalizeAnalysisClimateRows(rows, fallbackRows = [], legacyClimate = '') {
  const source = Array.isArray(rows) && rows.length
    ? rows
    : Array.isArray(fallbackRows) && fallbackRows.length
      ? fallbackRows
      : []

  if (source.length) {
    return source.map((row) => createEmptyAnalysisClimateRow(
      row?.temperatura_minima ?? row?.minima ?? '',
      row?.temperatura_maxima ?? row?.maxima ?? '',
      row?.tipo_clima ?? row?.tipo ?? '',
      row || {}
    ))
  }

  if (String(legacyClimate || '').trim()) {
    return [createEmptyAnalysisClimateRow('', '', String(legacyClimate).trim())]
  }

  return [createEmptyAnalysisClimateRow()]
}

function serializeAnalysisClimateRows(rows) {
  if (!Array.isArray(rows)) {
    return []
  }

  return rows
    .filter((row) => [row?.temperatura_minima, row?.temperatura_maxima, row?.tipo_clima, row?.archivo_id].some((value) => value !== null && value !== undefined && String(value).trim() !== ''))
    .map((row) => ({
      id: /^\d+$/.test(String(row?.id || '')) ? Number(row.id) : undefined,
      temperatura_minima: row?.temperatura_minima === '' || row?.temperatura_minima === null || row?.temperatura_minima === undefined ? null : Number(row.temperatura_minima),
      temperatura_maxima: row?.temperatura_maxima === '' || row?.temperatura_maxima === null || row?.temperatura_maxima === undefined ? null : Number(row.temperatura_maxima),
      tipo_clima: String(row?.tipo_clima || '').trim(),
      archivo_id: row?.archivo_id ? Number(row.archivo_id) : null,
      imagen_url: row?.imagen_url || row?.archivo?.url_publica || '',
      titulo_imagen: row?.titulo_imagen || row?.archivo?.nombre_original || '',
      descripcion_imagen: row?.descripcion_imagen || row?.archivo?.descripcion || '',
      fecha: row?.fecha || '',
      origen: row?.origen || 'galeria_actividad',
      es_imagen_temporal: row?.es_imagen_temporal ?? (row?.archivo?.categoria === 'clima_documento')
    }))
}

function buildClimateSummary(rows) {
  return serializeAnalysisClimateRows(rows)
    .map((row) => {
      const parts = []
      if (row.temperatura_minima !== null && row.temperatura_minima !== undefined && row.temperatura_minima !== '') {
        parts.push(`Minima ${row.temperatura_minima}°C`)
      }
      if (row.temperatura_maxima !== null && row.temperatura_maxima !== undefined && row.temperatura_maxima !== '') {
        parts.push(`Maxima ${row.temperatura_maxima}°C`)
      }
      if (row.tipo_clima) {
        parts.push(row.tipo_clima)
      }
      return parts.join(', ')
    })
    .filter(Boolean)
    .join(' | ')
}

function flattenLegacyAnalysisPlan(value) {
  if (typeof value === 'string') {
    return value.trim()
  }

  if (!value || typeof value !== 'object') {
    return ''
  }

  const blocks = [
    ['Coordinación con APS-SAMU 131', value.coordinacion_samu],
    ['Punto de encuentro o ubicación adecuada', value.punto_encuentro],
    ['Comunicación interna', value.comunicacion_interna],
    ['Documentación medica', value.documentacion_medica]
  ]

  return blocks
    .filter(([, item]) => String(item || '').trim())
    .map(([label, item]) => `${label}: ${String(item).trim()}`)
    .join('\n')
}

function flattenLegacyAnalysisProtocol(value) {
  if (typeof value === 'string') {
    return value.trim()
  }

  if (!Array.isArray(value)) {
    return ''
  }

  return value
    .map((item) => {
      const title = String(item?.titulo || '').trim()
      const detail = String(item?.detalle || '').trim()

      if (title && detail) {
        return `${title}: ${detail}`
      }

      return title || detail
    })
    .filter(Boolean)
    .join('\n')
}

function flattenLegacyAnalysisCenters(value) {
  if (typeof value === 'string') {
    return value.trim()
  }

  if (!Array.isArray(value)) {
    return ''
  }

  return value
    .map((item) => {
      const name = String(item?.nombre || '').trim()
      const detail = String(item?.detalle || '').trim()

      if (name && detail) {
        return `${name}: ${detail}`
      }

      return name || detail
    })
    .filter(Boolean)
    .join('\n')
}

function createAnalysisContent(context = null) {
  const activity = context?.actividad || {}

  return {
    proposito_documento: '',
    descripcion_evento: {
      nombre_evento: activity.nombre || '',
      fecha_evento: formatDateRange(activity.fecha_inicio, activity.fecha_termino),
      horario_evento: formatActivitySchedule(activity),
      lugar_evento: activity.lugar || '',
      participantes_evento: '',
      organizador_evento: activity.colaborador_externo || context?.filial?.nombre || '',
      clima_esperado: '',
      climas_esperados: normalizeAnalysisClimateRows([], context?.climas || [])
    },
    riesgos: [createEmptyAnalysisRiskRow()],
    plan_traslados: '',
    protocolo_traslado: '',
    centros_salud: '',
    conclusion: '',
    observaciones_finales: ''
  }
}

function normalizeAnalysisRiskRows(rows, legacyRisk = '') {
  if (Array.isArray(rows) && rows.length) {
    return rows.map((row) => ({
      id: row?.id || nextParticipantId(),
      nombre: row?.nombre || row?.titulo || '',
      descripcion: row?.descripcion || '',
      probabilidad: normalizeRiskOptionValues(row?.probabilidad),
      impacto: normalizeRiskOptionValues(row?.impacto),
      mitigacion: row?.mitigacion || row?.medidas_mitigacion || ''
    }))
  }

  if (String(legacyRisk || '').trim()) {
    return [createEmptyAnalysisRiskRow('Riesgo identificado', String(legacyRisk).trim())]
  }

  return [createEmptyAnalysisRiskRow()]
}

function joinAnalysisLegacyNotes(incoming) {
  const blocks = [
    ['Diagnóstico', incoming?.diagnostico],
    ['Oportunidades', incoming?.oportunidades],
    ['Desarrollo', incoming?.desarrollo],
    ['Resultados', incoming?.resultados],
    ['Recomendaciones', incoming?.recomendaciones],
    ['Observaciones', incoming?.observaciones]
  ]

  return blocks
    .filter(([, value]) => String(value || '').trim())
    .map(([label, value]) => label + ':\n' + String(value).trim())
    .join('\n\n')
}

function normalizeAnalysisContent(content, context = null) {
  const defaults = createAnalysisContent(context)
  const incoming = content && typeof content === 'object' ? content : {}
  const mergedDescription = {
    ...defaults.descripcion_evento,
    ...(incoming.descripcion_evento && typeof incoming.descripcion_evento === 'object' ? incoming.descripcion_evento : {})
  }
  const legacyIntro = [incoming.resumen, incoming.analisis_contextual]
    .filter((value) => String(value || '').trim())
    .map((value) => String(value).trim())
    .join('\n\n')
  const legacyNotes = joinAnalysisLegacyNotes(incoming)

  return {
    proposito_documento: typeof incoming.proposito_documento === 'string' && incoming.proposito_documento.trim()
      ? incoming.proposito_documento
      : (legacyIntro || defaults.proposito_documento),
    descripcion_evento: {
      nombre_evento: String(mergedDescription.nombre_evento || defaults.descripcion_evento.nombre_evento),
      fecha_evento: String(mergedDescription.fecha_evento || defaults.descripcion_evento.fecha_evento),
      horario_evento: String(mergedDescription.horario_evento || defaults.descripcion_evento.horario_evento),
      lugar_evento: String(mergedDescription.lugar_evento || defaults.descripcion_evento.lugar_evento),
      participantes_evento: typeof mergedDescription.participantes_evento === 'string' ? mergedDescription.participantes_evento : defaults.descripcion_evento.participantes_evento,
      organizador_evento: String(mergedDescription.organizador_evento || defaults.descripcion_evento.organizador_evento),
      clima_esperado: String(mergedDescription.clima_esperado || defaults.descripcion_evento.clima_esperado),
      climas_esperados: normalizeAnalysisClimateRows(mergedDescription.climas_esperados, context?.climas || [], mergedDescription.clima_esperado || '')
    },
    riesgos: normalizeAnalysisRiskRows(incoming.riesgos, typeof incoming.riesgos === 'string' ? incoming.riesgos : ''),
    plan_traslados: flattenLegacyAnalysisPlan(incoming.plan_traslados) || defaults.plan_traslados,
    protocolo_traslado: flattenLegacyAnalysisProtocol(incoming.protocolo_traslado) || defaults.protocolo_traslado,
    centros_salud: flattenLegacyAnalysisCenters(incoming.centros_salud) || defaults.centros_salud,
    conclusion: typeof incoming.conclusion === 'string' && incoming.conclusion.trim()
      ? incoming.conclusion
      : (typeof incoming.conclusiones === 'string' ? incoming.conclusiones : defaults.conclusion),
    observaciones_finales: typeof incoming.observaciones_finales === 'string' && incoming.observaciones_finales.trim()
      ? incoming.observaciones_finales
      : legacyNotes
  }
}
function ensureAnalysisContent() {
  if (!isContextAnalysisType.value) {
    return null
  }

  const normalized = normalizeAnalysisContent(form.contenido, form.datos_contexto)
  form.contenido = normalized
  return normalized
}

function createEmptyNarrativeParticipantRow(tipo = '', numero = null, source = 'manual') {
  return {
    id: nextParticipantId(),
    tipo,
    numero,
    source
  }
}

function createVolunteerNarrativeParticipantRow(count = null) {
  return createEmptyNarrativeParticipantRow('Voluntarios inscritos', count, 'voluntarios')
}

function createEmptyNarrativeCountRow(tipo = '', numero = null) {
  return {
    id: nextParticipantId(),
    tipo,
    numero
  }
}

function formatScheduleTime(value) {
  const match = String(value || '').match(/^(\d{1,2}):(\d{2})/)
  if (!match) {
    return ''
  }

  return match[1].padStart(2, '0') + ':' + match[2]
}

function formatActivitySchedule(activity) {
  if (!activity || typeof activity !== 'object') {
    return ''
  }

  const start = formatScheduleTime(activity.hora_inicio)
  const end = formatScheduleTime(activity.hora_termino)

  if (start && end) {
    return start + ' - ' + end
  }

  return start || end || ''
}

function createNarrativeContent(context = null) {
  return {
    objetivo_general: context?.actividad?.objetivo || '',
    objetivo_especifico: '',
    horario: formatActivitySchedule(context?.actividad),
    lugar: context?.actividad?.lugar || '',
    descripcion_general: '',
    recuentos_personas_asistidas: [
      createEmptyNarrativeCountRow('Asistencias Movilidad Reducida'),
      createEmptyNarrativeCountRow('Votos Asistidos'),
      createEmptyNarrativeCountRow('Mujeres')
    ],
    situaciones_interes: '',
    numero_puestos: '',
    participantes_asistencia: [],
    observaciones_generales: {
      logros: '',
      desafios_dificultades: '',
      recomendaciones_acciones: '',
      percepcion: ''
    },
    autorizacion_informe: '',
    fotos: []
  }
}

function normalizeNarrativeParticipants(items, fallbackRows = []) {
  const source = Array.isArray(items) && items.length ? items : fallbackRows

  if (!Array.isArray(source) || !source.length) {
    return []
  }

  const hasStructuredRows = source.some((item) => item && (Object.prototype.hasOwnProperty.call(item, 'tipo') || Object.prototype.hasOwnProperty.call(item, 'número')))

  if (hasStructuredRows) {
    return source.map((item) => ({
      id: item?.id || nextParticipantId(),
      tipo: item?.tipo || '',
      numero: item?.numero ?? null,
      source: item?.source || 'manual'
    }))
  }

  const groupedRows = new Map()

  source.forEach((item) => {
    const tipo = String(item?.cargo || item?.nombre || '').trim()
    if (!tipo) {
      return
    }

    const currentTotal = groupedRows.get(tipo) || 0
    const numericValue = Number(item?.numero)
    const increment = Number.isFinite(numericValue) && numericValue > 0 ? numericValue : 1
    groupedRows.set(tipo, currentTotal + increment)
  })

  return Array.from(groupedRows.entries()).map(([tipo, numero]) => createEmptyNarrativeParticipantRow(tipo, numero))
}

function normalizeNarrativePhotos(items) {
  if (!Array.isArray(items)) {
    return []
  }

  return items
    .map((item, index) => ({
      id: item?.id || ('photo-' + (index + 1)),
      gallery_item_id: item?.gallery_item_id || item?.id || null,
      titulo: item?.titulo || '',
      descripcion_origen: item?.descripcion_origen || item?.descripcion || '',
      descripcion_informe: item?.descripcion_informe || '',
      imagen_url: item?.imagen_url || '',
      fecha: item?.fecha || '',
      origen: item?.origen || 'galeria_actividad'
    }))
    .filter((item) => item.imagen_url)
}

function normalizeNarrativeCounts(rows, legacyCounts = null, fallbackRows = []) {
  if (Array.isArray(rows) && rows.length) {
    return rows.map((row) => ({
      id: row?.id || nextParticipantId(),
      tipo: row?.tipo || '',
      numero: row?.numero ?? null
    }))
  }

  if (legacyCounts && typeof legacyCounts === 'object') {
    const labelMap = {
      atenciones_ppaa: 'Atenciones PPAA',
      asistencias_movilidad_reducida: 'Asistencias Movilidad Reducida',
      votos_asistidos: 'Votos Asistidos',
      traslados: 'Traslados',
      hombres: 'Hombres',
      mujeres: 'Mujeres'
    }

    return Object.entries(labelMap)
      .map(([key, tipo]) => ({
        id: nextParticipantId(),
        tipo,
        numero: legacyCounts[key] ?? null
      }))
      .filter((row) => row.numero !== null && row.numero !== undefined && row.numero !== '')
  }

  return fallbackRows.map((row) => ({
    id: row?.id || nextParticipantId(),
    tipo: row?.tipo || '',
    numero: row?.numero ?? null
  }))
}

function normalizeNarrativeContent(content, context = null) {
  const defaults = createNarrativeContent(context)
  const incoming = content && typeof content === 'object' ? content : {}

  return {
    objetivo_general: typeof incoming.objetivo_general === 'string' ? incoming.objetivo_general : defaults.objetivo_general,
    objetivo_especifico: typeof incoming.objetivo_especifico === 'string' ? incoming.objetivo_especifico : defaults.objetivo_especifico,
    horario: typeof incoming.horario === 'string' && incoming.horario.trim() ? incoming.horario : defaults.horario,
    lugar: typeof incoming.lugar === 'string' ? incoming.lugar : defaults.lugar,
    descripcion_general: typeof incoming.descripcion_general === 'string' ? incoming.descripcion_general : defaults.descripcion_general,
    recuentos_personas_asistidas: normalizeNarrativeCounts(
      incoming.recuentos_personas_asistidas,
      incoming.personas_asistidas,
      defaults.recuentos_personas_asistidas
    ),
    situaciones_interes: typeof incoming.situaciones_interes === 'string' ? incoming.situaciones_interes : defaults.situaciones_interes,
    numero_puestos: typeof incoming.numero_puestos === 'string' ? incoming.numero_puestos : defaults.numero_puestos,
    participantes_asistencia: normalizeNarrativeParticipants(incoming.participantes_asistencia, defaults.participantes_asistencia),
    observaciones_generales: {
      ...defaults.observaciones_generales,
      ...(incoming.observaciones_generales && typeof incoming.observaciones_generales === 'object' ? incoming.observaciones_generales : {})
    },
    autorizacion_informe: typeof incoming.autorizacion_informe === 'string' ? incoming.autorizacion_informe : defaults.autorizacion_informe,
    fotos: normalizeNarrativePhotos(incoming.fotos)
  }
}

function ensureNarrativeContent() {
  if (!isNarrativeType.value) {
    return null
  }

  const normalized = normalizeNarrativeContent(form.contenido, form.datos_contexto)
  form.contenido = normalized
  return normalized
}

function normalizeActividadSearch(value) {
  return String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()
}

function syncActividadSearchTerm() {
  activitySearchTerm.value = selectedActividad.value ? actividadLabel(selectedActividad.value) : ''
}

function syncHighlightedActividad() {
  if (!filteredActividades.value.length) {
    highlightedActividadIndex.value = -1
    return
  }

  const selectedIndex = filteredActividades.value.findIndex((actividad) => String(actividad.id) === selectedActividadId.value)
  highlightedActividadIndex.value = selectedIndex >= 0 ? selectedIndex : 0
}

function openActividadMenu() {
  showAllActividades.value = true
  showActividadMenu.value = true
  syncHighlightedActividad()
}

function handleActividadInputInteraction(event) {
  openActividadMenu()
  const input = event?.currentTarget
  nextTick(() => input?.select())
}

function closeActividadMenu() {
  showActividadMenu.value = false
  showAllActividades.value = false
  highlightedActividadIndex.value = -1

  if (selectedActividad.value) {
    syncActividadSearchTerm()
  }
}

function toggleActividadMenu() {
  if (showActividadMenu.value) {
    closeActividadMenu()
    return
  }

  openActividadMenu()
}

function handleActivitySearchInput() {
  showAllActividades.value = false
  showActividadMenu.value = true

  if (selectedActividad.value && normalizeActividadSearch(activitySearchTerm.value) !== normalizeActividadSearch(actividadLabel(selectedActividad.value))) {
    selectedActividadId.value = ''
    currentSavedDocumentId.value = null
    form.datos_contexto = null
    documentosGuardados.value = []
    form.contenido = emptyContent(selectedType.value)
  }

  syncHighlightedActividad()
}

function moveActividadHighlight(direction) {
  if (!showActividadMenu.value) {
    openActividadMenu()
    return
  }

  if (!filteredActividades.value.length) {
    highlightedActividadIndex.value = -1
    return
  }

  const lastIndex = filteredActividades.value.length - 1
  const currentIndex = highlightedActividadIndex.value < 0 ? 0 : highlightedActividadIndex.value
  highlightedActividadIndex.value = direction > 0
    ? (currentIndex >= lastIndex ? 0 : currentIndex + 1)
    : (currentIndex <= 0 ? lastIndex : currentIndex - 1)
}

function confirmHighlightedActividad() {
  if (!showActividadMenu.value) {
    openActividadMenu()
    return
  }

  if (highlightedActividadIndex.value < 0 || highlightedActividadIndex.value >= filteredActividades.value.length) {
    return
  }

  selectActividad(filteredActividades.value[highlightedActividadIndex.value])
}

async function selectActividad(actividad) {
  selectedActividadId.value = String(actividad.id)
  syncActividadSearchTerm()
  closeActividadMenu()
  await handleActividadChange()
}

function handleActividadClickOutside(event) {
  if (actividadComboboxRef.value && !actividadComboboxRef.value.contains(event.target)) {
    closeActividadMenu()
  }
}


function addAnalysisRiskRow() {
  const analysis = ensureAnalysisContent()
  analysis.riesgos.push(createEmptyAnalysisRiskRow())
}

function removeAnalysisRiskRow(index) {
  const analysis = ensureAnalysisContent()
  analysis.riesgos.splice(index, 1)

  if (!analysis.riesgos.length) {
    analysis.riesgos.push(createEmptyAnalysisRiskRow())
  }
}

function addAnalysisClimateRow() {
  const analysis = ensureAnalysisContent()
  analysis.descripcion_evento.climas_esperados.push(createEmptyAnalysisClimateRow())
}

async function removeAnalysisClimateRow(index) {
  const analysis = ensureAnalysisContent()
  const [removedRow] = analysis.descripcion_evento.climas_esperados.splice(index, 1)

  if (!analysis.descripcion_evento.climas_esperados.length) {
    analysis.descripcion_evento.climas_esperados.push(createEmptyAnalysisClimateRow())
  }

  await deleteTemporaryClimateImage(removedRow)
}

function findAnalysisClimateRow(rowId) {
  const analysis = ensureAnalysisContent()
  return analysis.descripcion_evento.climas_esperados.find((row) => String(row.id) === String(rowId)) || null
}

function assignGalleryPhotoToClimate(rowId, item) {
  const row = findAnalysisClimateRow(rowId)

  if (!row) {
    return
  }

  row.archivo_id = item?.archivo_id || item?.archivo?.id || null
  row.imagen_url = item?.imagen_url || item?.archivo?.url_publica || ''
  row.titulo_imagen = item?.titulo || item?.archivo?.nombre_original || ''
  row.descripcion_imagen = item?.descripcion || item?.archivo?.descripcion || ''
  row.fecha = item?.fecha || ''
  row.origen = item?.origen || 'galeria_actividad'
  row.es_imagen_temporal = item?.es_imagen_temporal ?? (item?.archivo?.categoria === 'clima_documento')
}

async function clearClimatePhoto(rowId) {
  const row = findAnalysisClimateRow(rowId)

  if (!row) {
    return
  }

  const removedPhoto = { ...row }

  row.archivo_id = null
  row.imagen_url = ''
  row.titulo_imagen = ''
  row.descripcion_imagen = ''
  row.fecha = ''
  row.origen = 'galeria_actividad'
  row.es_imagen_temporal = false

  await deleteTemporaryClimateImage(removedPhoto)
}

function isTemporaryClimateImage(photo) {
  return Boolean(
    photo?.archivo_id
    && (photo?.es_imagen_temporal || photo?.archivo?.categoria === 'clima_documento')
  )
}

async function deleteTemporaryClimateImage(photo) {
  if (!selectedActividadId.value || !isTemporaryClimateImage(photo)) {
    return
  }

  try {
    await axios.delete(buildApiUrl(
      `actividad/${selectedActividadId.value}/galeria-temporal/${photo.archivo_id}`
    ))
    await refreshActivityGallery()
  } catch (error) {
    Swal.fire(
      'Aviso',
      'La imagen se quitó del clima, pero no fue posible eliminar el archivo temporal.',
      'warning'
    )
  }
}

function openClimatePhotoPicker(rowId) {
  if (!selectedActividadId.value) {
    Swal.fire('Actividad requerida', 'Selecciona primero una actividad base para asociar la foto del clima.', 'warning')
    return
  }

  selectedClimateRowId.value = String(rowId)
  climatePhotoInputRef.value?.click()
}

async function onClimatePhotoFileSelected(event) {
  const file = Array.from(event.target.files || [])[0]

  if (!file || !selectedClimateRowId.value) {
    return
  }

  try {
    await uploadClimatePhotoFromFile(selectedClimateRowId.value, file)
  } finally {
    if (climatePhotoInputRef.value) {
      climatePhotoInputRef.value.value = ''
    }
  }
}

async function uploadClimatePhotoFromFile(rowId, file) {
  if (!selectedActividadId.value) {
    throw new Error('Actividad requerida')
  }

  uploadingClimatePhoto.value = true
  const climateRow = findAnalysisClimateRow(rowId)
  const previousPhoto = climateRow ? { ...climateRow } : null
  let uploadedPhoto = null
  let associationPersisted = false

  try {
    const optimizedFile = await optimizeImage(file)
    const formData = new FormData()
    formData.append('archivo', optimizedFile)
    formData.append('titulo', file.name.replace(/\.[^.]+$/, ''))
    formData.append('fecha', todayAsInput())
    formData.append('categoria', 'clima_documento')

    if (currentUser.value?.id) {
      formData.append('subido_por', String(currentUser.value.id))
    }

    const response = await axios.post(buildApiUrl('actividad/' + selectedActividadId.value + '/galeria'), formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    const normalizedPhoto = normalizeGalleryItems([response.data])[0]
    uploadedPhoto = normalizedPhoto
      ? { ...normalizedPhoto, es_imagen_temporal: true }
      : null

    if (uploadedPhoto) {
      assignGalleryPhotoToClimate(rowId, uploadedPhoto)
      form.contenido = await syncAnalysisClimateRowsWithActivity(ensureAnalysisContent())
      associationPersisted = true

      if (previousPhoto?.archivo_id !== uploadedPhoto.archivo_id) {
        await deleteTemporaryClimateImage(previousPhoto)
      }
    }

    await refreshActivityGallery()
    Swal.fire('Foto asociada', 'La imagen del clima fue cargada y vinculada correctamente.', 'success')
  } catch (error) {
    if (uploadedPhoto && !associationPersisted) {
      await deleteTemporaryClimateImage(uploadedPhoto)
    }

    const currentRow = findAnalysisClimateRow(rowId)
    if (currentRow && previousPhoto && !associationPersisted) {
      Object.assign(currentRow, previousPhoto)
    }

    Swal.fire('Error', 'No se pudo cargar la imagen del clima.', 'error')
    throw error
  } finally {
    uploadingClimatePhoto.value = false
  }
}

async function pasteClimatePhoto(rowId) {
  if (!selectedActividadId.value) {
    Swal.fire('Actividad requerida', 'Selecciona primero una actividad base para asociar la foto del clima.', 'warning')
    return
  }

  if (!navigator.clipboard?.read) {
    Swal.fire('Portapapeles no disponible', 'Este navegador no permite leer imágenes desde el portapapeles.', 'info')
    return
  }

  pastingClimatePhoto.value = true

  try {
    const items = await navigator.clipboard.read()
    let imageFile = null

    for (const item of items) {
      const imageType = item.types.find((type) => type.startsWith('image/'))
      if (!imageType) {
        continue
      }

      const blob = await item.getType(imageType)
      const extension = imageType.split('/')[1] || 'png'
      imageFile = new File([blob], `clima-${Date.now()}.${extension}`, { type: imageType })
      break
    }

    if (!imageFile) {
      Swal.fire('Sin imagen', 'No encontramos una imagen en el portapapeles del dispositivo.', 'info')
      return
    }

    await uploadClimatePhotoFromFile(rowId, imageFile)
  } catch (error) {
    Swal.fire('Error', 'No se pudo pegar la imagen desde el portapapeles.', 'error')
  } finally {
    pastingClimatePhoto.value = false
  }
}

function openClimateGalleryModal(rowId) {
  if (!selectedActividadId.value) {
    Swal.fire('Actividad requerida', 'Selecciona primero una actividad base para usar fotos de su galería.', 'warning')
    return
  }

  selectedClimateRowId.value = String(rowId)
  showClimateGalleryModal.value = true
}

function closeClimateGalleryModal() {
  showClimateGalleryModal.value = false
  selectedClimateRowId.value = ''
}

function selectGalleryPhotoForClimate(item) {
  if (!selectedClimateRowId.value) {
    return
  }

  assignGalleryPhotoToClimate(selectedClimateRowId.value, item)
  showClimateGalleryModal.value = false
}
function addNarrativeCountRow() {
  const narrative = ensureNarrativeContent()
  narrative.recuentos_personas_asistidas.push(createEmptyNarrativeCountRow())
}

function removeNarrativeCountRow(index) {
  const narrative = ensureNarrativeContent()
  narrative.recuentos_personas_asistidas.splice(index, 1)
}

function addNarrativeParticipantRow() {
  const narrative = ensureNarrativeContent()
  narrative.participantes_asistencia.push(createEmptyNarrativeParticipantRow())
}

function loadNarrativeVolunteerParticipants() {
  const narrative = ensureNarrativeContent()
  const volunteerCount = eligibleNarrativeVolunteerCount.value

  if (!volunteerCount) {
    Swal.fire('Sin voluntarios elegibles', 'No hay voluntarios inscritos con rol exclusivo de voluntario para cargar en esta sección.', 'info')
    return
  }

  const existingIndex = narrative.participantes_asistencia.findIndex((row) => row?.source === 'voluntarios' || normalizeActividadSearch(row?.tipo) === 'voluntarios inscritos')
  const volunteerRow = createVolunteerNarrativeParticipantRow(volunteerCount)

  if (existingIndex >= 0) {
    narrative.participantes_asistencia.splice(existingIndex, 1, {
      ...narrative.participantes_asistencia[existingIndex],
      tipo: volunteerRow.tipo,
      numero: volunteerRow.numero,
      source: volunteerRow.source
    })
    return
  }

  narrative.participantes_asistencia.unshift(volunteerRow)
}

function removeNarrativeParticipant(index) {
  const narrative = ensureNarrativeContent()
  narrative.participantes_asistencia.splice(index, 1)
}

function goToPreviousNarrativePhotos() {
  narrativePhotoPage.value = Math.max(1, narrativePhotoPage.value - 1)
}

function goToNextNarrativePhotos() {
  narrativePhotoPage.value = Math.min(narrativePhotoPageCount.value, narrativePhotoPage.value + 1)
}

function isGalleryPhotoSelected(item) {
  const photos = Array.isArray(form.contenido?.fotos) ? form.contenido.fotos : []

  return photos.some((photo) => String(photo.gallery_item_id || photo.id) === String(item.id))
}

function addGalleryPhotoToNarrative(item) {
  const narrative = ensureNarrativeContent()

  if (isGalleryPhotoSelected(item)) {
    return
  }

  narrative.fotos.push({
    id: 'gallery-' + item.id,
    gallery_item_id: item.id,
    titulo: item.titulo || '',
    descripcion_origen: item.descripcion || '',
    descripcion_informe: item.descripcion || '',
    imagen_url: item.imagen_url,
    fecha: item.fecha || '',
    origen: 'galeria_actividad'
  })
  narrativePhotoPage.value = Math.ceil(narrative.fotos.length / narrativePhotoPageSize)
}

function removeNarrativePhoto(index) {
  const narrative = ensureNarrativeContent()
  narrative.fotos.splice(index, 1)
  narrativePhotoPage.value = Math.min(narrativePhotoPage.value, Math.max(1, Math.ceil(narrative.fotos.length / narrativePhotoPageSize)))
}

function openDevicePhotoPicker() {
  devicePhotoInputRef.value?.click()
}

async function openDeviceGalleryTab() {
  photoModalTab.value = 'device'
  await nextTick()
  openDevicePhotoPicker()
}

function openPhotoModal(tab = 'device') {
  if (!selectedActividadId.value) {
    Swal.fire('Actividad requerida', 'Selecciona primero una actividad base para adjuntar fotografías.', 'warning')
    return
  }

  ensureNarrativeContent()
  photoModalTab.value = tab
  showPhotoPickerModal.value = true
}

function closePhotoModal() {
  showPhotoPickerModal.value = false
  showClimateGalleryModal.value = false
  selectedClimateRowId.value = ''
  pendingNarrativePhotoFiles.value = []
  if (devicePhotoInputRef.value) {
    devicePhotoInputRef.value.value = ''
  }
}

function onNarrativePhotoFilesSelected(event) {
  pendingNarrativePhotoFiles.value = Array.from(event.target.files || [])
}

async function refreshActivityGallery() {
  if (!selectedActividadId.value) {
    return []
  }

  const response = await axios.get(buildApiUrl('actividad/' + selectedActividadId.value + '/galeria'))
  const gallery = normalizeGalleryItems(Array.isArray(response.data) ? response.data : [])

  if (form.datos_contexto) {
    form.datos_contexto = {
      ...form.datos_contexto,
      galeria: gallery,
      resumen: {
        ...(form.datos_contexto.resumen || {}),
        total_evidencias: gallery.length
      }
    }
  }

  return gallery
}

async function uploadNarrativePhotosFromDevice() {
  if (!selectedActividadId.value) {
    Swal.fire('Actividad requerida', 'Selecciona primero una actividad base.', 'warning')
    return
  }

  if (!pendingNarrativePhotoFiles.value.length) {
    Swal.fire('Sin fotografías', 'Selecciona al menos una imagen antes de subir.', 'warning')
    return
  }

  uploadingNarrativePhotos.value = true

  try {
    for (const file of pendingNarrativePhotoFiles.value) {
      const optimizedFile = await optimizeImage(file)
      const formData = new FormData()
      formData.append('archivo', optimizedFile)
      formData.append('titulo', file.name.replace(/\.[^.]+$/, ''))
      formData.append('fecha', todayAsInput())

      if (currentUser.value?.id) {
        formData.append('subido_por', String(currentUser.value.id))
      }

      await axios.post(buildApiUrl('actividad/' + selectedActividadId.value + '/galeria'), formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }

    const gallery = await refreshActivityGallery()
    const narrative = ensureNarrativeContent()

    pendingNarrativePhotoFiles.value.forEach((file) => {
      const title = file.name.replace(/\.[^.]+$/, '')
      const match = [...gallery].reverse().find((item) => item.titulo === title && !narrative.fotos.some((photo) => String(photo.gallery_item_id || photo.id) === String(item.id)))
      if (match) {
        addGalleryPhotoToNarrative(match)
      }
    })

    pendingNarrativePhotoFiles.value = []
    if (devicePhotoInputRef.value) {
      devicePhotoInputRef.value.value = ''
    }

    photoModalTab.value = 'activity'
    Swal.fire('Fotografías cargadas', 'Las imágenes fueron subidas y agregadas al informe.', 'success')
  } catch (error) {
    Swal.fire('Error', 'No se pudieron subir las fotografías seleccionadas.', 'error')
  } finally {
    uploadingNarrativePhotos.value = false
  }
}

function todayAsInput() {
  return new Date().toISOString().slice(0, 10)
}

function emptyContent(type) {
  if (type === 'informe_narrativo') {
    return createNarrativeContent(form.datos_contexto)
  }

  if (type === 'analisis_contexto') {
    return createAnalysisContent(form.datos_contexto)
  }

  const meta = documentTypes.find((item) => item.key === type)
  return (meta?.sections || []).reduce((accumulator, section) => {
    accumulator[section.key] = ''
    return accumulator
  }, {})
}

function openForm(type) {
  selectedType.value = type
  currentSavedDocumentId.value = null
  form.titulo = ''
  form.estado = 'borrador'
  form.fecha_documento = todayAsInput()
  form.datos_contexto = null
  form.contenido = type === 'informe_narrativo'
    ? normalizeNarrativeContent(emptyContent(type), form.datos_contexto)
    : type === 'analisis_contexto'
      ? normalizeAnalysisContent(emptyContent(type), form.datos_contexto)
      : emptyContent(type)
  documentosGuardados.value = []
  showActividadMenu.value = false
  showPhotoPickerModal.value = false

  if (selectedActividadId.value) {
    syncActividadSearchTerm()
    handleActividadChange()
  } else {
    activitySearchTerm.value = ''
  }
}

function closeForm() {
  selectedType.value = ''
  selectedActividadId.value = ''
  currentSavedDocumentId.value = null
  activitySearchTerm.value = ''
  showActividadMenu.value = false
  highlightedActividadIndex.value = -1
  showPhotoPickerModal.value = false
  showClimateGalleryModal.value = false
  selectedClimateRowId.value = ''
  pendingNarrativePhotoFiles.value = []
  narrativePhotoPage.value = 1
  form.titulo = ''
  form.estado = 'borrador'
  form.fecha_documento = todayAsInput()
  form.datos_contexto = null
  form.contenido = {}
  documentosGuardados.value = []
}

async function initializeFromRoute() {
  const requestedType = String(route.query.tipo || '').trim()
  const requestedActividadId = String(route.query.actividad || '').trim()
  const isAllowedType = documentTypes.some((item) => item.key === requestedType)

  if (!isAllowedType || !requestedActividadId) {
    return
  }

  selectedActividadId.value = requestedActividadId
  openForm(requestedType)
  await handleActividadChange()
}

async function fetchActividades() {
  const collected = []
  let currentPage = 1
  let lastPage = 1

  try {
    do {
      const response = await axios.get(buildApiUrl('actividad'), {
        params: { page: currentPage }
      })
      collected.push(...(response.data.data || []))
      lastPage = Number(response.data.last_page || currentPage)
      currentPage += 1
    } while (currentPage <= lastPage)

    actividades.value = collected
  } catch (error) {
    actividades.value = []
    Swal.fire('Error', 'No se pudo cargar la lista de actividades.', 'error')
  }
}

async function handleActividadChange() {
  if (!selectedActividadId.value || !selectedType.value) {
    form.datos_contexto = null
    documentosGuardados.value = []
    currentSavedDocumentId.value = null
    return
  }

  await loadPrefill()
  await fetchSavedDocuments()
  preloadLatestDraftForSelectedType()
}

function latestSavedDraftForSelectedType() {
  return documentosGuardados.value.find((documento) => {
    return documento?.tipo_documento === selectedType.value && (documento?.estado || 'borrador') === 'borrador'
  }) || null
}

function applySavedDocumentToForm(documento) {
  if (!documento) {
    currentSavedDocumentId.value = null
    return
  }

  currentSavedDocumentId.value = documento.id || null
  form.titulo = documento.titulo || form.titulo
  form.estado = documento.estado || 'borrador'
  form.fecha_documento = String(documento.fecha_documento || '').slice(0, 10) || form.fecha_documento
  form.datos_contexto = documento.datos_contexto || form.datos_contexto
  form.contenido = isNarrativeType.value
    ? normalizeNarrativeContent(documento.contenido || {}, form.datos_contexto)
    : isContextAnalysisType.value
      ? normalizeAnalysisContent(documento.contenido || {}, form.datos_contexto)
      : {
          ...emptyContent(selectedType.value),
          ...(documento.contenido || {})
        }
}

function isSavedDocumentLoaded(documento) {
  return Boolean(documento?.id) && Number(documento.id) === Number(currentSavedDocumentId.value)
}

function preloadLatestDraftForSelectedType() {
  const draft = latestSavedDraftForSelectedType()
  if (!draft) {
    currentSavedDocumentId.value = null
    return
  }

  applySavedDocumentToForm(draft)
}

function loadSavedDocument(documento) {
  if (!documento) {
    return
  }

  applySavedDocumentToForm(documento)
}
async function loadPrefill() {
  if (!selectedActividadId.value || !selectedType.value) {
    return
  }

  loadingPrefill.value = true

  try {
    const response = await axios.get(buildApiUrl(`actividad/${selectedActividadId.value}/documentos/prefill`), {
      params: { tipo_documento: selectedType.value }
    })

    form.titulo = response.data.titulo_sugerido || ''
    form.datos_contexto = response.data.prefill || null
    form.contenido = selectedType.value === 'informe_narrativo'
      ? normalizeNarrativeContent(response.data.contenido_inicial || {}, response.data.prefill || null)
      : selectedType.value === 'analisis_contexto'
        ? normalizeAnalysisContent(response.data.contenido_inicial || {}, response.data.prefill || null)
        : {
            ...emptyContent(selectedType.value),
            ...(response.data.contenido_inicial || {})
          }
  } catch (error) {
    form.datos_contexto = null
    form.contenido = emptyContent(selectedType.value)
    Swal.fire('Error', 'No se pudo precargar la información de la actividad.', 'error')
  } finally {
    loadingPrefill.value = false
  }
}

async function fetchSavedDocuments() {
  if (!selectedActividadId.value) {
    documentosGuardados.value = []
    currentSavedDocumentId.value = null
    return
  }

  try {
    const response = await axios.get(buildApiUrl(`actividad/${selectedActividadId.value}/documentos`))
    documentosGuardados.value = response.data || []
    if (currentSavedDocumentId.value && !documentosGuardados.value.some((documento) => Number(documento.id) === Number(currentSavedDocumentId.value))) {
      currentSavedDocumentId.value = null
    }
  } catch (error) {
    documentosGuardados.value = []
    currentSavedDocumentId.value = null
  }
}

function validateNarrativeFinalContent() {
  if (!canSubmit.value) {
    Swal.fire('Faltan datos', 'Selecciona una actividad y completa al menos el título del documento.', 'warning')
    return null
  }

  if (!isNarrativeType.value) {
    return form.contenido
  }

  const narrative = ensureNarrativeContent()
  if ((narrative.fotos || []).length < 3) {
    Swal.fire('Faltan datos', 'El informe narrativo final debe incluir al menos 3 fotografías.', 'warning')
    return null
  }

  if (narrative.fotos.some((photo) => !String(photo.descripcion_informe || '').trim())) {
    Swal.fire('Faltan datos', 'Cada fotografía del informe narrativo final debe tener una descripción.', 'warning')
    return null
  }

  return narrative
}

async function syncAnalysisClimateRowsWithActivity(content) {
  const climateRows = serializeAnalysisClimateRows(content?.descripcion_evento?.climas_esperados || [])
  const payloadRows = climateRows.map((row) => ({
    ...(row.id ? { id: row.id } : {}),
    temperatura_minima: row.temperatura_minima,
    temperatura_maxima: row.temperatura_maxima,
    tipo_clima: row.tipo_clima,
    archivo_id: row.archivo_id
  }))

  const response = await axios.put(buildApiUrl(`actividad/${selectedActividadId.value}/climas`), {
    climas: payloadRows
  })

  const syncedRows = serializeAnalysisClimateRows(response.data?.climas || climateRows)

  if (form.datos_contexto) {
    form.datos_contexto = {
      ...form.datos_contexto,
      climas: syncedRows
    }
  }

  return {
    ...content,
    descripcion_evento: {
      ...(content?.descripcion_evento || {}),
      climas_esperados: syncedRows,
      clima_esperado: buildClimateSummary(syncedRows)
    }
  }
}
async function persistDocument(targetState, options = {}) {
  const { showSuccess = true, successTitle = 'Guardado', successText = 'El documento fue guardado correctamente.' } = options

  isSaving.value = true

  try {
    let normalizedContent = isNarrativeType.value
      ? ensureNarrativeContent()
      : isContextAnalysisType.value
        ? ensureAnalysisContent()
        : form.contenido

    if (isContextAnalysisType.value) {
      normalizedContent = await syncAnalysisClimateRowsWithActivity(normalizedContent)
      form.contenido = normalizedContent
    }

    const payload = {
      tipo_documento: selectedType.value,
      titulo: form.titulo.trim(),
      estado: targetState,
      fecha_documento: form.fecha_documento || null,
      datos_contexto: form.datos_contexto,
      contenido: normalizedContent
    }
    const response = currentSavedDocumentId.value
      ? await axios.put(buildApiUrl(`documentos-actividad/${currentSavedDocumentId.value}`), payload)
      : await axios.post(buildApiUrl(`actividad/${selectedActividadId.value}/documentos`), payload)

    applySavedDocumentToForm(response.data)
    await fetchSavedDocuments()

    if (showSuccess) {
      Swal.fire(successTitle, successText, 'success')
    }

    return response.data
  } catch (error) {
    const message = error?.response?.data?.message || 'No se pudo guardar el documento.'
    Swal.fire('Error', message, 'error')
    return null
  } finally {
    isSaving.value = false
  }
}

async function saveDocument(targetState) {
  if (!canSubmit.value) {
    Swal.fire('Faltan datos', 'Selecciona una actividad y completa al menos el título del documento.', 'warning')
    return
  }

  if (targetState === 'final') {
    const validatedContent = isNarrativeType.value
      ? validateNarrativeFinalContent()
      : isContextAnalysisType.value
        ? ensureAnalysisContent()
        : form.contenido

    if (!validatedContent) {
      return
    }

    form.contenido = validatedContent
  }

  await persistDocument(targetState)
}

function validateDocumentForFinalSave() {
  if (!canSubmit.value) {
    Swal.fire('Faltan datos', 'Selecciona una actividad y completa al menos el título del documento.', 'warning')
    return null
  }

  if (isNarrativeType.value) {
    return validateNarrativeFinalContent()
  }

  if (isContextAnalysisType.value) {
    return ensureAnalysisContent()
  }

  return form.contenido
}

async function handlePrimaryDocumentAction() {
  const validatedContent = validateDocumentForFinalSave()

  if (!validatedContent) {
    return
  }

  form.contenido = validatedContent
  await persistDocument('final')
}

async function exportDocumentPdf() {
  const validatedContent = validateDocumentForFinalSave()

  if (!validatedContent) {
    return
  }

  form.contenido = validatedContent
  const documento = await persistDocument('final', {
    showSuccess: false
  })

  if (!documento?.id) {
    return
  }

  await router.push({
    name: 'DocumentoActividadPdfView',
    params: { id: documento.id },
    query: {
      from: 'documentos',
      tipo: selectedType.value,
      actividad: selectedActividadId.value || undefined
    }
  })
}

function actividadLabel(actividad) {
  const dates = formatDateRange(actividad.fecha_inicio, actividad.fecha_termino)
  return `${actividad.nombre || 'Actividad'} · ${actividad.filial?.nombre || 'Sin filial'} · ${dates}`
}

function formatHours(value) {
  if (value === null || value === undefined || value === '') return '-'
  const numericValue = Number(value)
  if (!Number.isFinite(numericValue)) return '-'
  return `${numericValue % 1 === 0 ? numericValue.toFixed(0) : numericValue.toFixed(2)} h`
}

function formatSavedDocument(documento) {
  const date = formatDate(documento.fecha_documento)
  const author = documento.generador?.name || 'Sin autor'
  return `${date} · ${author}`
}

watch(selectedActividad, (actividad) => {
  if (actividad) {
    activitySearchTerm.value = actividadLabel(actividad)
  } else if (!showActividadMenu.value) {
    activitySearchTerm.value = ''
  }
})

onMounted(async () => {
  document.addEventListener('mousedown', handleActividadClickOutside)
  await fetchActividades()
  await initializeFromRoute()
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleActividadClickOutside)
})
</script>

<style scoped>
.header-copy {
  color: #6e7f95;
  margin-top: 0.35rem;
}

.documents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.document-card {
  border: 1px solid #e3e9f1;
  border-radius: 20px;
  background: var(--cr-white);
  box-shadow: 0 14px 32px var(--cr-navy-shadow);
  padding: 1.35rem;
  display: grid;
  gap: 0.8rem;
}

.document-card.active {
  border-color: var(--cr-navy);
  box-shadow: 0 0 0 4px rgba(23, 59, 112, 0.42), 0 18px 36px rgba(23, 59, 112, 0.18);
}

.document-card__icon {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--cr-blue-pale);
  color: var(--cr-navy);
  font-size: 1.3rem;
}

.document-card h4 {
  margin: 0;
  color: var(--cr-navy-medium);
  font-size: 1.35rem;
  font-weight: 800;
}

.document-card p {
  margin: 0;
  color: #667a93;
  line-height: 1.55;
}

.document-card__button {
  justify-self: flex-start;
}

.documents-chip,
.form-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.45rem 0.85rem;
  border-radius: 999px;
  background: var(--cr-blue-pale);
  color: var(--cr-navy-medium);
  font-weight: 700;
}

.form-shell {
  border: 1px solid #e5ebf3;
  border-radius: 24px;
}

.form-shell__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  flex-wrap: wrap;
}

.form-shell__header h4 {
  color: var(--cr-navy-medium);
  margin: 0.6rem 0 0.35rem;
  font-size: 1.7rem;
  font-weight: 800;
}

.form-shell__header p {
  color: #6a7d94;
  max-width: 760px;
}

.activity-combobox {
  position: relative;
}

.activity-combobox__control {
  display: flex;
  align-items: stretch;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  background: var(--cr-white);
  overflow: hidden;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.activity-combobox__control:focus-within,
.activity-combobox__control.is-open {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.activity-combobox__input {
  border: 0;
  box-shadow: none !important;
  padding-right: 0.65rem;
}

.activity-combobox__input:focus {
  box-shadow: none;
}

.activity-combobox__toggle {
  width: 48px;
  border: 0;
  border-left: 1px solid #e2e8f0;
  background: var(--cr-white);
  color: #355782;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
}

.activity-combobox__control.is-open .activity-combobox__toggle i {
  transform: rotate(180deg);
}

.activity-combobox__toggle:hover {
  background: #f5f8fc;
}

.activity-combobox__menu {
  position: absolute;
  top: calc(100% + 0.4rem);
  left: 0;
  right: 0;
  z-index: 20;
  max-height: 280px;
  overflow-y: auto;
  padding: 0.45rem;
  border: 1px solid #dbe7f4;
  border-radius: 18px;
  background: var(--cr-white);
  box-shadow: 0 18px 36px rgba(15, 47, 95, 0.12);
}

.activity-combobox__option {
  width: 100%;
  border: 0;
  background: transparent;
  border-radius: 14px;
  padding: 0.8rem 0.9rem;
  text-align: left;
  display: grid;
  gap: 0.18rem;
}

.activity-combobox__option:hover,
.activity-combobox__option.is-active {
  background: #f4f8ff;
}

.activity-combobox__option.is-selected {
  background: var(--cr-blue-pale);
}

.activity-combobox__title {
  color: var(--cr-navy);
  font-weight: 700;
}

.activity-combobox__meta {
  color: #6a7d94;
  font-size: 0.9rem;
}

.activity-combobox__empty {
  padding: 0.8rem 0.9rem;
  color: #6a7d94;
}

.loading-panel {
  border: 1px dashed #c8d6ea;
  border-radius: 18px;
  padding: 1rem 1.1rem;
  background: #f8fbff;
  color: #355782;
  font-weight: 600;
}

.context-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 0.9rem;
}

.context-card {
  background: #f8fbff;
  border: 1px solid #dbe7f4;
  border-radius: 18px;
  padding: 0.95rem 1rem;
  display: grid;
  gap: 0.25rem;
}

.context-card--wide {
  grid-column: span 2;
}

.context-card__label,
.prefill-panel__label {
  color: #7083a0;
  font-size: 0.82rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  font-weight: 700;
}

.context-card strong {
  color: var(--cr-navy);
  font-size: 1.02rem;
}

.context-card small {
  color: #617791;
}

.prefill-panel {
  height: 100%;
  background: var(--cr-white);
  border: 1px solid #e4ebf3;
  border-radius: 20px;
  padding: 1rem 1.05rem;
}

.prefill-panel h5 {
  color: var(--cr-navy);
  font-weight: 800;
  margin-bottom: 1rem;
}

.prefill-panel__block + .prefill-panel__block {
  margin-top: 1rem;
}

.prefill-panel__block p {
  margin: 0.35rem 0 0;
  color: #304861;
}


.saved-documents {
  margin: 0.5rem 0 0;
  padding-left: 1.15rem;
  color: #304861;
}

.saved-documents {
  list-style: none;
  padding-left: 0;
}

.saved-documents li {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.8rem 0;
  border-bottom: 1px solid #edf2f8;
}

.saved-documents li.is-active {
  background: rgba(23, 59, 112, 0.06);
  border-radius: 0.9rem;
  padding: 0.8rem;
  margin: 0 -0.35rem;
}

.saved-documents li:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.saved-documents strong {
  display: block;
  color: var(--cr-navy);
}

.saved-documents small {
  color: #7387a1;
}

.saved-documents__state {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  padding: 0.28rem 0.7rem;
  font-size: 0.82rem;
  font-weight: 700;
  text-transform: capitalize;
  white-space: nowrap;
}

.saved-documents__actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.state-borrador {
  background: #fff4d8;
  color: #8c6400;
}

.state-final {
  background: #e5f7ea;
  color: #1c6f3a;
}

.content-fields {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.content-field {
  background: #f8fbff;
  border: 1px solid #dce8f4;
  border-radius: 20px;
  padding: 1rem;
}

.content-field .form-label {
  color: var(--cr-navy);
  font-weight: 700;
}

.content-field textarea {
  min-height: 132px;
  resize: vertical;
}

.narrative-layout {
  display: grid;
  gap: 1rem;
}

.narrative-section {
  background: #f8fbff;
  border: 1px solid #dce8f4;
  border-radius: 20px;
  padding: 1rem;
}

.narrative-section h5 {
  color: var(--cr-navy);
  font-weight: 800;
  margin-bottom: 0.75rem;
}

.narrative-note {
  color: #6a7d94;
  margin-bottom: 0.75rem;
}

.section-headline {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-bottom: 0.5rem;
}

.narrative-count-list {
  display: grid;
  gap: 0.85rem;
}

.narrative-count-row {
  background: var(--cr-white);
  border: 1px solid #e4ebf3;
  border-radius: 16px;
  padding: 0.8rem;
}

.attendance-row__remove,
.photo-report-card__remove {
  border: 0;
  background: transparent;
  color: #c7343c;
  font-weight: 700;
}

.photo-report-grid,
.photo-gallery-grid {
  display: grid;
  gap: 1rem;
}

.photo-report-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.photo-gallery-grid {
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
}

.photo-report-navigation {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.65rem;
  margin-top: 1rem;
  color: var(--cr-navy-medium);
  font-weight: 700;
}

.photo-report-navigation__button {
  width: 2.25rem;
  height: 2.25rem;
  padding: 0;
  border: 1px solid var(--cr-navy);
  border-radius: 999px;
  background: var(--cr-navy);
  color: var(--cr-white);
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.photo-report-navigation__button:disabled {
  border-color: var(--cr-gray-300);
  background: var(--cr-gray-300);
  color: var(--cr-gray-500);
  cursor: default;
}

.photo-report-card,
.photo-gallery-card {
  background: var(--cr-white);
  border: 1px solid #e2eaf4;
  border-radius: 18px;
  overflow: hidden;
  display: grid;
}

.photo-report-card__image,
.photo-gallery-card__image {
  width: 100%;
  aspect-ratio: 16 / 10;
  object-fit: cover;
  background: #e9eef5;
}

.photo-report-card__body,
.photo-gallery-card__body {
  padding: 0.9rem;
  display: grid;
  gap: 0.55rem;
}

.photo-modal {
  position: fixed;
  inset: 0;
  background: rgba(15, 29, 51, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
  z-index: 1200;
}

.photo-modal__panel {
  width: min(1100px, 100%);
  max-height: calc(100vh - 2.5rem);
  overflow: auto;
  background: var(--cr-white);
  border-radius: 24px;
  padding: 1.15rem;
  box-shadow: 0 24px 60px rgba(15, 29, 51, 0.22);
}

.photo-modal__header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
  flex-wrap: wrap;
}

.photo-modal__tabs {
  display: flex;
  gap: 0.75rem;
  margin: 1rem 0;
  flex-wrap: wrap;
}

.photo-modal__tab {
  border: 1px solid #d7e4f2;
  background: #f7faff;
  color: var(--cr-navy);
  border-radius: 999px;
  padding: 0.55rem 0.9rem;
  font-weight: 700;
}

.photo-modal__tab.active {
  background: var(--cr-navy);
  color: var(--cr-white);
  border-color: var(--cr-navy);
}

.photo-upload-box {
  border: 1px dashed #d2dfed;
  border-radius: 20px;
  background: #f8fbff;
  padding: 1rem;
}

.pending-upload-list {
  margin: 0.8rem 0 0;
  padding-left: 1.1rem;
  color: #48607d;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  flex-wrap: wrap;
}

@media (max-width: 991.98px) {
  .context-card--wide {
    grid-column: span 1;
  }

  .photo-report-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 767.98px) {

  .form-shell__header h4 {
    font-size: 1.45rem;
  }

  .form-actions {
    justify-content: stretch;
  }

  .form-actions .btn,
  .photo-modal__header .btn {
    width: 100%;
  }

  .photo-modal {
    padding: 0.75rem;
  }

  .photo-report-grid {
    grid-template-columns: minmax(0, 1fr);
  }

  .photo-modal__panel {
    padding: 1rem;
  }
}
.climate-list {
  display: grid;
  gap: 1rem;
}

.climate-card {
  margin: 0;
  padding: 1rem;
  border: 1px solid #d7e3ef;
  border-radius: 18px;
  background: #f8fbff;
}

.climate-card__media {
  display: grid;
  gap: 0.9rem;
}

.climate-card__preview {
  width: 100%;
  max-width: 280px;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #dbe5f0;
  background: var(--cr-white);
}

.climate-card__preview img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  display: block;
}

.climate-card__empty {
  padding: 1.25rem;
  border: 1px dashed #b8c7d9;
  border-radius: 16px;
  color: #6a7d94;
  background: var(--cr-white);
}

.climate-card__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem;
}

.risk-option-group {
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem 1rem;
  padding: 0.85rem 1rem;
  border: 1px solid #d9e0ea;
  border-radius: 12px;
  background: #fbfcfe;
}

.risk-option {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.95rem;
}

.risk-option .form-check-input {
  margin-top: 0;
}

</style>














