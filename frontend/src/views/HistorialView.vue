<template>
  <div class="d-flex">
    <SidebarMenu compact collapsible />

    <div class="content-wrapper">
      <div class="content">
        <div class="page-shell">
          <div v-if="isLoading" class="panel-empty">
            Cargando hoja de vida...
          </div>

          <div v-else-if="errorMessage" class="panel-empty panel-empty--error">
            {{ errorMessage }}
          </div>

          <template v-else-if="volunteer">
            <section v-if="canEditOwnPersonalData && pendingRequests.length" class="pending-requests-panel">
              <div>
                <p class="panel-kicker">En revisión</p>
                <h3>Solicitudes de modificación</h3>
              </div>
              <div class="pending-request-list">
                <article v-for="request in pendingRequests" :key="request.id">
                  <span :class="`pending-status pending-status--${request.estado}`">{{ request.estado }}</span>
                  <strong>{{ pendingRequestLabel(request) }}</strong>
                  <small v-if="request.motivo_revision">{{ request.motivo_revision }}</small>
                </article>
              </div>
            </section>
            <section class="hero-stage">
              <aside class="hero-history-column">
                <section class="panel history-panel hero-history-panel">
                  <div class="panel-header panel-header--history">
                    <div>
                      <p class="panel-kicker">Historial</p>
                      <h3>Hojas anuales</h3>
                    </div>

                    <div v-if="canManageHojaVida && selectedAnnual" class="history-panel__actions history-panel__actions--header">
                      <button
                        type="button"
                        class="history-panel__action-button history-panel__action-button--danger"
                        @click="deleteSelectedAnnual"
                        aria-label="Eliminar hoja anual seleccionada"
                        title="Eliminar hoja anual seleccionada"
                      >
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </div>
                  </div>

                  <div v-if="annualRecords.length" class="annual-list">
                    <HistorialAnualCard
                      v-for="record in visibleAnnualRecords"
                      :key="record.id"
                      :historial="record"
                      :active="record.anio === selectedYear"
                      :to="yearLink(record.anio)"
                    />
                  </div>

                  <div v-else class="empty-inline">
                    Este voluntario todavía no tiene hoja de vida anual registrada.
                  </div>

                  <div v-if="annualRecords.length" class="history-controls history-controls--footer">
                    <span class="history-counter">
                      {{ annualWindowStart + 1 }}-{{ annualWindowEnd }} de {{ annualRecords.length }}
                    </span>
                    <div class="history-nav">
                      <button
                        type="button"
                        class="history-nav__button"
                        :disabled="!canGoPrevAnnuals"
                        @click="goToPreviousAnnualPage"
                        aria-label="Ver hojas anuales anteriores"
                      >
                        <i class="fa-solid fa-chevron-left"></i>
                      </button>
                      <button
                        type="button"
                        class="history-nav__button"
                        :disabled="!canGoNextAnnuals"
                        @click="goToNextAnnualPage"
                        aria-label="Ver hojas anuales siguientes"
                      >
                        <i class="fa-solid fa-chevron-right"></i>
                      </button>
                    </div>
                  </div>
                </section>

                <section v-if="selectedAnnual && filteredRecognitionItems.length" class="panel recognition-panel hero-recognition-panel">
                  <div class="panel-header">
                    <div>
                      <p class="panel-kicker">Reconocimiento anual</p>
                      <h3>Estado del periodo</h3>
                    </div>
                  </div>

                  <div class="recognition-grid recognition-grid--sidebar">
                    <article
                      v-for="recognition in filteredRecognitionItems"
                      :key="recognition.label"
                      class="recognition-item"
                      :class="{ active: recognition.value }"
                    >
                      <span>{{ recognition.label }}</span>
                      <strong>{{ recognition.value ? 'Si' : 'No' }}</strong>
                    </article>
                  </div>
                </section>
              </aside>

              <div class="hero-main">
                <section class="hero-layout">
                  <div class="hero-rail">
                    <button type="button" class="back-button" @click="goBack" aria-label="Volver">
                      <i class="fa-solid fa-arrow-left"></i>
                    </button>

                    <div class="year-card">
                      {{ activePeriodYearLabel }}
                    </div>

                    <div class="photo-card">
                      <div class="photo-frame">
                        <img
                          v-if="volunteer.foto_perfil_url"
                          :src="volunteer.foto_perfil_url"
                          :alt="`Foto de ${displayName}`"
                        >
                        <span v-else>Sin foto</span>
                      </div>

                      <button
                        v-if="canUpdatePhoto"
                        type="button"
                        class="photo-action"
                        :disabled="isUploadingPhoto"
                        @click="triggerPhotoInput"
                      >
                        {{ isUploadingPhoto ? 'Actualizando...' : photoActionLabel }}
                      </button>

                      <input
                        ref="photoInput"
                        type="file"
                        class="d-none"
                        accept=".jpg,.jpeg,.png,.webp"
                        @change="onProfilePhotoSelected"
                      >

                      <button
                        v-if="showPhotoHistoryTrigger"
                        type="button"
                        class="action-button action-button--primary photo-history-trigger"
                        @click="openAnnualHistoryModal"
                      >
                        Revisar Hojas Anuales de otros años
                      </button>
                    </div>
                  </div>

                  <article class="hero-panel">
                    <div class="hero-top">
                      <div class="hero-copy">
                        <div class="hero-meta">
                          <p class="hero-kicker">
                            N. registro {{ volunteer.registro_filial || 'Sin registro' }}
                          </p>
                          <p class="hero-kicker">
                            Filial {{ volunteer.filial?.nombre || 'Sin filial' }}
                          </p>
                        </div>
                        <h2>{{ displayName }}</h2>

                        <div class="hero-stats">
                          <article
                            v-for="stat in heroSummaryItems"
                            :key="stat.label"
                            class="hero-stat"
                          >
                            <span>{{ stat.label }}</span>
                            <strong>{{ stat.value }}</strong>
                          </article>
                        </div>
                      </div>

                      <div class="hero-side">
                        <div class="hero-brand">
                          <router-link to="/portada" aria-label="Ir a Inicio y novedades"><img :src="logoSrc" alt="Cruz Roja Chilena"></router-link>
                        </div>

                        <div class="hero-brand-actions">
                          <button
                            type="button"
                            class="action-button action-button--primary hero-brand-action"
                            :disabled="!selectedAnnual"
                            @click="openPdfExport"
                          >
                            Exportar PDF
                          </button>

                          <button
                            v-if="canManageHojaVida"
                            type="button"
                            class="action-button hero-brand-action"
                            @click="openCreateEditor"
                          >
                            Agregar hoja anual
                          </button>

                          <button
                            v-if="canManageHojaVida"
                            type="button"
                            class="action-button action-button--ghost hero-brand-action"
                            :disabled="!selectedAnnual"
                            @click="openEditEditor"
                          >
                            Editar periodo
                          </button>
                        </div>
                      </div>
                    </div>
                  </article>

                <div class="hero-actions-row">
                  <button
                    type="button"
                    class="action-button action-button--primary hero-brand-action"
                    :disabled="!selectedAnnual"
                    @click="openPdfExport"
                  >
                    Exportar PDF
                  </button>

                  <button
                    v-if="canManageHojaVida"
                    type="button"
                    class="action-button hero-brand-action"
                    @click="openCreateEditor"
                  >
                    Agregar hoja anual
                  </button>

                  <button
                    v-if="canManageHojaVida"
                    type="button"
                    class="action-button action-button--ghost hero-brand-action"
                    :disabled="!selectedAnnual"
                    @click="openEditEditor"
                  >
                    Editar periodo
                  </button>
                </div>
                </section>

                <div class="toolbar-row toolbar-row--hero-outside">
                  <label class="search-shell">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                      v-model.trim="searchTerm"
                      type="text"
                      placeholder="Buscar datos personales, cursos, documentos, sanciones o comentarios del año seleccionado"
                    >
                  </label>
                </div>

            <section class="content-grid">
              <div class="main-column">
                <div v-if="!selectedAnnual" class="panel-empty panel-empty--soft">
                  Este voluntario todavía no tiene una hoja de vida anual para mostrar.
                </div>

                <div v-else-if="!hasSearchResults" class="panel-empty panel-empty--soft">
                  No encontramos coincidencias en la hoja de vida del periodo {{ selectedAnnual?.anio || 'seleccionado' }}.
                </div>

                <template v-else>

                  <section v-if="filteredPersonalFacts.length" class="panel section-personal-panel">
                    <div class="panel-header">
                      <div>
                        <p class="panel-kicker panel-kicker--section-title">Datos Personales</p>
                        <h3 class="section-personal-panel__title">Información del Voluntario</h3>
                      </div>
                      <button
                        v-if="canEditOwnPersonalData"
                        type="button"
                        class="section-upload-button"
                        :disabled="!selectedAnnual"
                        @click="openPersonalDataEditor"
                      >
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Editar mis datos</span>
                      </button>
                    </div>

                    <div class="fact-grid fact-grid--personal">
                      <article
                        v-for="fact in filteredPersonalFacts"
                        :key="fact.label"
                        class="fact-tile fact-tile--personal"
                        :class="fact.layoutClass"
                      >
                        <span class="fact-tile__label">{{ fact.label }}</span>
                        <div v-if="fact.secondaryValue" class="fact-tile__split">
                          <strong>{{ fact.value }}</strong>
                          <strong class="fact-tile__secondary">{{ fact.secondaryValue }}</strong>
                        </div>
                        <strong v-else>{{ fact.value }}</strong>
                      </article>
                    </div>
                  </section>

                  <section v-if="selectedAnnual" class="panel volunteer-activities-panel">
                    <div class="panel-header">
                      <div>
                        <p class="panel-kicker panel-kicker--section-title">Actividades del voluntario</p>
                        <span class="panel-record-count">{{ filteredVolunteerActivities.length }} registro(s)</span>
                      </div>
                    </div>

                    <div v-if="filteredVolunteerActivities.length" class="stack-list volunteer-activities-grid">
                      <article
                        v-for="activity in visibleVolunteerActivities"
                        :key="activity.id || `${activity.nombre}-${activity.fecha_inicio}`"
                        class="stack-card volunteer-activity-card"
                      >
                        <header class="volunteer-activity-card__header">
                          <h4 class="volunteer-activity-card__name">{{ activity.nombre || 'Actividad sin registro' }}</h4>
                          <span class="volunteer-activity-card__type">{{ activity.tipo || 'Sin registro' }}</span>
                        </header>

                        <div class="volunteer-activity-card__details">
                          <div class="volunteer-activity-card__item">
                            <span class="volunteer-activity-card__icon" aria-hidden="true">
                              <i class="fa-regular fa-calendar-days"></i>
                            </span>
                            <div class="volunteer-activity-card__content">
                              <span class="volunteer-activity-card__label">Fecha</span>
                              <strong class="volunteer-activity-card__value">{{ formatActivityDateRange(activity.fecha_inicio, activity.fecha_termino) }}</strong>
                            </div>
                          </div>

                          <div class="volunteer-activity-card__item">
                            <span class="volunteer-activity-card__icon" aria-hidden="true">
                              <i class="fa-solid fa-building"></i>
                            </span>
                            <div class="volunteer-activity-card__content">
                              <span class="volunteer-activity-card__label">Filial</span>
                              <strong class="volunteer-activity-card__value">{{ activity.filial?.nombre || 'Sin registro' }}</strong>
                            </div>
                          </div>

                          <div class="volunteer-activity-card__item">
                            <span class="volunteer-activity-card__icon" aria-hidden="true">
                              <i class="fa-solid fa-location-dot"></i>
                            </span>
                            <div class="volunteer-activity-card__content">
                              <span class="volunteer-activity-card__label">Lugar</span>
                              <strong class="volunteer-activity-card__value">{{ activity.lugar || 'Sin registro' }}</strong>
                            </div>
                          </div>

                          <div class="volunteer-activity-card__item">
                            <span class="volunteer-activity-card__icon" aria-hidden="true">
                              <i class="fa-regular fa-clock"></i>
                            </span>
                            <div class="volunteer-activity-card__content">
                              <span class="volunteer-activity-card__label">Horas asistidas</span>
                              <strong class="volunteer-activity-card__value">{{ formatVolunteerActivityHours(activity.pivot?.horas_asistidas) }}</strong>
                            </div>
                          </div>
                        </div>
                      </article>
                    </div>

                    <div v-else class="empty-inline">
                      No hay actividades inscritas registradas para este periodo.
                    </div>

                    <div v-if="filteredVolunteerActivities.length > volunteerActivitiesWindowSize" class="history-controls history-controls--footer volunteer-activities-pagination">
                      <span class="history-counter">
                        {{ volunteerActivitiesWindowStart + 1 }}-{{ volunteerActivitiesWindowEnd }} de {{ filteredVolunteerActivities.length }}
                      </span>
                      <div class="history-nav">
                        <button
                          type="button"
                          class="history-nav__button"
                          :disabled="!canGoPrevVolunteerActivities"
                          @click="goToPreviousVolunteerActivitiesPage"
                          aria-label="Ver actividades anteriores"
                        >
                          <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button
                          type="button"
                          class="history-nav__button"
                          :disabled="!canGoNextVolunteerActivities"
                          @click="goToNextVolunteerActivitiesPage"
                          aria-label="Ver actividades siguientes"
                        >
                          <i class="fa-solid fa-chevron-right"></i>
                        </button>
                      </div>
                    </div>
                  </section>
                  <div class="split-grid section-titles-group">
                    <section class="panel">
                      <div class="panel-header">
                        <div>
                          <p class="panel-kicker panel-kicker--section-title">Títulos</p>
                          <span class="panel-record-count">{{ filteredTitles.length }} registro(s)</span>
                        </div>
                        <button
                          v-if="canUploadAcademicRecords"
                          type="button"
                          class="section-upload-button"
                          :disabled="!selectedAnnual"
                          @click="openSectionEditor('titles')"
                        >
                          <i class="fa-solid fa-pen-to-square"></i>
                          <span>Editar registros</span>
                        </button>
                      </div>

                      <div v-if="filteredTitles.length" class="table-responsive">
                        <table class="sheet-table">
                          <thead>
                            <tr>
                              <th>Título</th>
                              <th>Entregado por</th>
                              <th>Código</th>
                              <th>Respaldo</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="title in filteredTitles" :key="title.id || `${title.titulo}-${title.codigo_titulo}`">
                              <td>{{ title.titulo || 'Sin registro' }}</td>
                              <td>{{ title.entregado_por || 'Sin registro' }}</td>
                              <td>{{ title.codigo_titulo || 'Sin registro' }}</td>
                              <td>
                                <div v-if="attachmentLinks(title).length" class="sheet-link-list">
                                  <a
                                    v-for="attachment in attachmentLinks(title)"
                                    :key="attachment.id"
                                    :href="attachment.url"
                                    target="_blank"
                                    rel="noopener"
                                    class="sheet-link"
                                  >
                                    {{ attachment.name }}
                                  </a>
                                </div>
                                <span v-else>Sin respaldo</span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <div v-else class="empty-inline">
                        No hay títulos aprobados registrados para este periodo.
                      </div>
                    </section>

                    <section class="panel">
                      <div class="panel-header">
                        <div>
                          <p class="panel-kicker panel-kicker--section-title">Cursos aprobados</p>
                          <span class="panel-record-count">{{ filteredCourses.length }} registro(s)</span>
                        </div>
                        <button
                          v-if="canUploadAcademicRecords"
                          type="button"
                          class="section-upload-button"
                          :disabled="!selectedAnnual"
                          @click="openSectionEditor('courses')"
                        >
                          <i class="fa-solid fa-pen-to-square"></i>
                          <span>Editar registros</span>
                        </button>
                      </div>

                      <div v-if="filteredCourses.length" class="table-responsive">
                        <table class="sheet-table">
                          <thead>
                            <tr>
                              <th>Curso</th>
                              <th>Entregado por</th>
                              <th>Código</th>
                              <th>Respaldo</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="course in filteredCourses" :key="course.id || `${course.nombre_curso}-${course.codigo_curso}`">
                              <td>{{ course.nombre_curso || 'Sin registro' }}</td>
                              <td>{{ course.entregado_por || 'Sin registro' }}</td>
                              <td>{{ course.codigo_curso || 'Sin registro' }}</td>
                              <td>
                                <div v-if="attachmentLinks(course).length" class="sheet-link-list">
                                  <a
                                    v-for="attachment in attachmentLinks(course)"
                                    :key="attachment.id"
                                    :href="attachment.url"
                                    target="_blank"
                                    rel="noopener"
                                    class="sheet-link"
                                  >
                                    {{ attachment.name }}
                                  </a>
                                </div>
                                <span v-else>Sin respaldo</span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <div v-else class="empty-inline">
                        No hay cursos aprobados registrados para este periodo.
                      </div>
                    </section>
                  </div>

                  <section class="panel section-other-documents-panel">
                    <div class="panel-header">
                      <div>
                        <p class="panel-kicker panel-kicker--section-title">Otros documentos</p>
                        <span class="panel-record-count">{{ filteredOtherDocuments.length }} registro(s)</span>
                      </div>
                      <button
                        v-if="canUploadAcademicRecords"
                        type="button"
                        class="section-upload-button"
                        :disabled="!selectedAnnual"
                        @click="openSectionEditor('documents')"
                      >
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Editar registros</span>
                      </button>
                    </div>

                    <div v-if="filteredOtherDocuments.length" class="table-responsive">
                      <table class="sheet-table">
                        <thead>
                          <tr>
                            <th>Documento</th>
                            <th>Motivo</th>
                            <th>Archivo</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="document in filteredOtherDocuments" :key="document.id || `${document.nombre_documento}-${document.motivo}`">
                            <td>{{ document.nombre_documento || 'Sin registro' }}</td>
                            <td>{{ document.motivo || 'Sin registro' }}</td>
                            <td>
                              <div v-if="attachmentLinks(document).length" class="sheet-link-list">
                                <a
                                  v-for="attachment in attachmentLinks(document)"
                                  :key="attachment.id"
                                  :href="attachment.url"
                                  target="_blank"
                                  rel="noopener"
                                  class="sheet-link"
                                >
                                  {{ attachment.name }}
                                </a>
                              </div>
                              <span v-else>Sin respaldo</span>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <div v-else class="empty-inline">
                      No hay otros documentos registrados para este periodo.
                    </div>
                  </section>
                  <div class="split-grid section-sanctions-group">
                  <section v-if="showCommissionPanel" class="panel section-commission-panel">
                    <div class="panel-header">
                      <div>
                        <p class="panel-kicker panel-kicker--section-title">Comisión de servicio</p>
                        <h3>Registro del periodo</h3>
                      </div>
                      <span class="counter-chip">
                        {{ commissionInfo.inService ? 'En comision' : 'Sin comision' }}
                      </span>
                    </div>

                    <div v-if="commissionInfo.hasAnyData" class="commission-card">
                      <div class="commission-top">
                        <span class="mini-pill" :class="{ 'mini-pill--active': commissionInfo.inService }">
                          {{ commissionInfo.inService ? 'Si estuvo en comision' : 'No estuvo en comision' }}
                        </span>
                        <span class="mini-pill">{{ commissionInfo.dateRange }}</span>
                      </div>

                      <div class="commission-grid">
                        <article class="commission-item">
                          <span>Lugar</span>
                          <strong>{{ commissionInfo.place }}</strong>
                        </article>
                        <article class="commission-item commission-item--wide">
                          <span>Actividad</span>
                          <strong>{{ commissionInfo.activity }}</strong>
                        </article>
                      </div>
                    </div>

                    <div v-else class="empty-inline">
                      No hay antecedentes de comisión de servicio para este periodo.
                    </div>
                  </section>

                    <section class="panel">
                      <div class="panel-header">
                        <div>
                          <p class="panel-kicker panel-kicker--section-title">Sanciones</p>
                          <span class="panel-record-count">{{ filteredSanctions.length }} registro(s)</span>
                        </div>
                      </div>

                      <div v-if="filteredSanctions.length" class="stack-list">
                        <article
                          v-for="sanction in filteredSanctions"
                          :key="sanction.id || `${sanction.tipo_sancion}-${sanction.fecha}`"
                          class="stack-card"
                        >
                          <div class="stack-card__row">
                            <span>Tipo</span>
                            <strong>{{ sanction.tipo_sancion || 'Sin registro' }}</strong>
                          </div>
                          <div class="stack-card__row">
                            <span>Fecha</span>
                            <strong>{{ formatDate(sanction.fecha) }}</strong>
                          </div>
                          <div class="stack-card__row stack-card__row--wide">
                            <span>Resumen</span>
                            <strong>{{ sanction.resumen_sancion || 'Sin registro' }}</strong>
                          </div>
                          <div class="stack-card__row stack-card__row--wide">
                            <span>Apelación</span>
                            <strong>{{ sanction.apelacion || 'Sin registro' }}</strong>
                          </div>
                          <div class="stack-card__row">
                            <span>Fecha apelación</span>
                            <strong>{{ formatDate(sanction.fecha_apelacion) }}</strong>
                          </div>
                          <div class="stack-card__row stack-card__row--wide">
                            <span>Decisión CIG</span>
                            <strong>{{ sanction.decision_cig || 'Sin registro' }}</strong>
                          </div>
                        </article>
                      </div>

                      <div v-else class="empty-inline">
                        No hay sanciones registradas para este periodo.
                      </div>
                    </section>
                  </div>

                  <section v-if="showCommentsPanel" class="panel section-comments-panel">
                    <div class="panel-header">
                      <div>
                        <p class="panel-kicker panel-kicker--section-title">Comentarios</p>
                        <h3>Observaciones del periodo {{ selectedAnnual?.anio || '' }}</h3>
                      </div>
                    </div>

                    <div class="comments-box">
                      {{ selectedAnnual?.comentarios || 'Sin comentarios registrados para este periodo.' }}
                    </div>
                  </section>
                </template>
              </div>
              <aside class="sidebar-column sidebar-column--mobile-only">
                <section v-if="selectedAnnual && filteredRecognitionItems.length" class="panel recognition-panel hero-recognition-panel">
                  <div class="panel-header">
                    <div>
                      <p class="panel-kicker">Reconocimiento anual</p>
                      <h3>Estado del periodo</h3>
                    </div>
                  </div>

                  <div class="recognition-grid recognition-grid--sidebar">
                    <article
                      v-for="recognition in filteredRecognitionItems"
                      :key="recognition.label"
                      class="recognition-item"
                      :class="{ active: recognition.value }"
                    >
                      <span>{{ recognition.label }}</span>
                      <strong>{{ recognition.value ? 'Si' : 'No' }}</strong>
                    </article>
                  </div>
                </section>              </aside>
            </section>
              </div>
            </section>

            <HistorialAnualEditor
              v-if="isEditorOpen"
              :volunteer-id="volunteer.id"
              :record="editorRecord"
              :volunteer="volunteer"
              :current-user-id="currentUser?.id || null"
              :initial-section="editorSection"
              :active-year="activePeriodYear"
              @saved="handleRecordSaved"
              @cancel="closeEditor"
            />
            <div
              v-if="isAnnualHistoryModalOpen"
              class="history-modal"
              @click.self="closeAnnualHistoryModal"
            >
              <div class="history-modal__panel">
                <div class="history-modal__header">
                  <div>
                    <p class="panel-kicker">Historial</p>
                    <h3>Hojas anuales</h3>
                  </div>
                  <button type="button" class="history-modal__close" @click="closeAnnualHistoryModal" aria-label="Cerrar historial">
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>

                <div v-if="annualRecords.length" class="history-modal__controls">
                  <span class="history-counter">
                    {{ annualWindowStart + 1 }}-{{ annualWindowEnd }} de {{ annualRecords.length }}
                  </span>
                  <div class="history-nav">
                    <button
                      type="button"
                      class="history-nav__button"
                      :disabled="!canGoPrevAnnuals"
                      @click="goToPreviousAnnualPage"
                      aria-label="Ver hojas anuales anteriores"
                    >
                      <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button
                      type="button"
                      class="history-nav__button"
                      :disabled="!canGoNextAnnuals"
                      @click="goToNextAnnualPage"
                      aria-label="Ver hojas anuales siguientes"
                    >
                      <i class="fa-solid fa-chevron-right"></i>
                    </button>
                  </div>
                </div>

                <div v-if="annualRecords.length" class="annual-list history-modal__list">
                  <HistorialAnualCard
                    v-for="record in visibleAnnualRecords"
                    :key="`mobile-history-${record.id}`"
                    :historial="record"
                    :active="record.anio === selectedYear"
                    :to="yearLink(record.anio)"
                    @click="closeAnnualHistoryModal"
                  />
                </div>

                <div v-else class="empty-inline">
                  Este voluntario todavía no tiene hoja de vida anual registrada.
                </div>
              </div>
            </div>

          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from 'vuex'
import logoSrc from '../assets/LogoVertical.svg'
import SidebarMenu from '../components/SidebarMenu.vue'
import HistorialAnualCard from '../components/HistorialAnualCard.vue'
import HistorialAnualEditor from '../components/HistorialAnualEditor.vue'
import { API_BASE } from '../config/api'
import { show_alerta } from '../funciones'
import Swal from 'sweetalert2'
import { optimizeImage } from '../utils/imageOptimization'

const route = useRoute()
const router = useRouter()
const store = useStore()

const isLoading = ref(true)
const errorMessage = ref('')
const user = ref(null)
const selectedYear = ref(null)
const isEditorOpen = ref(false)
const editorRecord = ref(null)
const editorSection = ref(null)
const editorActiveYear = ref(null)
const searchTerm = ref('')
const isUploadingPhoto = ref(false)
const photoInput = ref(null)
const annualWindowStart = ref(0)
const volunteerActivitiesWindowStart = ref(0)
const viewportWidth = ref(typeof window === 'undefined' ? 1920 : window.innerWidth)
const isAnnualHistoryModalOpen = ref(false)
const pendingRequests = ref([])

const currentUser = computed(() => store.getters.authUser)
const canManageHojaVida = computed(() => store.getters.isAdministratorExperience && store.getters.canManagePlatform)
const canEditOwnPersonalData = computed(() => Boolean(user.value?.id) && currentUser.value?.id === user.value?.id)
const canUpdatePhoto = computed(() => Boolean(user.value?.id) && (canManageHojaVida.value || canEditOwnPersonalData.value))
const canUploadAcademicRecords = computed(() => Boolean(user.value?.id) && (canManageHojaVida.value || canEditOwnPersonalData.value))
const photoActionLabel = computed(() => volunteer.value?.foto_perfil_url ? 'Cambiar foto' : 'Subir foto')

const volunteer = computed(() => user.value?.voluntario || null)

const annualWindowSize = 3
const volunteerActivitiesWindowSize = computed(() => (viewportWidth.value <= 860 ? 1 : viewportWidth.value <= 1439 ? 2 : 3))

const annualRecords = computed(() =>
  [...(volunteer.value?.hoja_vida_anual || [])].sort((left, right) => Number(right.anio) - Number(left.anio))
)

const visibleAnnualRecords = computed(() =>
  annualRecords.value.slice(annualWindowStart.value, annualWindowStart.value + annualWindowSize)
)

const annualWindowEnd = computed(() =>
  Math.min(annualWindowStart.value + annualWindowSize, annualRecords.value.length)
)

const showPhotoHistoryTrigger = computed(() => viewportWidth.value <= 767)
const canGoPrevAnnuals = computed(() => annualWindowStart.value > 0)
const canGoNextAnnuals = computed(() => annualWindowEnd.value < annualRecords.value.length)

const selectedAnnual = computed(() => {
  if (!selectedYear.value) {
    return annualRecords.value[0] || null
  }

  return annualRecords.value.find((record) => Number(record.anio) === Number(selectedYear.value)) || null
})

const activePeriodYear = computed(() => {
  const year = selectedAnnual.value?.anio ?? selectedYear.value
  return year ? Number(year) : null
})

const activePeriodYearLabel = computed(() => {
  return activePeriodYear.value ? String(activePeriodYear.value) : ''
})

const normalizedSearch = computed(() => normalizeSearch(searchTerm.value))

const displayName = computed(() => {
  if (volunteer.value) {
    return [volunteer.value.nombres, volunteer.value.apellidos].filter(Boolean).join(' ') || user.value?.username || 'Voluntario'
  }

  return user.value?.username || 'Hoja de vida'
})

const cargoLabel = computed(() =>
  selectedAnnual.value?.cargo_nombre ||
  selectedAnnual.value?.cargo ||
  volunteer.value?.cargo ||
  'Sin Cargo registrado'
)

const periodCargoInfo = computed(() => {
  const annual = selectedAnnual.value

  if (!annual) {
    return {
      hasCargo: false,
      label: 'Sin cargo registrado para este periodo'
    }
  }

  return {
    hasCargo: Boolean(annual.cargo_nombre || annual.cargo),
    label: annual.cargo_nombre || annual.cargo || 'Sin cargo registrado para este periodo'
  }
})

const attendanceLabel = computed(() => {
  const percentage = selectedAnnual.value?.asistencia_anual_porcentaje

  if (percentage === null || percentage === undefined || percentage === '') {
    return 'Sin registro'
  }

  return `${Number(percentage)}%`
})

const heroSummaryItems = computed(() => {
  return [
    { label: 'Cargo', value: cargoLabel.value },
    { label: 'Asistencia en el periodo', value: attendanceLabel.value },
  ]
})

const personalFacts = computed(() => {
  if (!volunteer.value) {
    return []
  }

  return [
    { label: 'Nombre', value: displayName.value, layoutClass: 'fact-tile--span-2 fact-tile--primary' },
    { label: 'RUT', value: volunteer.value.rut || '-' },
    { label: 'Edad', value: ageLabel.value === 'Sin registro' ? '-' : ageLabel.value },
    { label: 'Estado Civil', value: volunteer.value.estado_civil || '-' },
    { label: 'Domicilio', value: volunteer.value.domicilio || '-', layoutClass: 'fact-tile--span-2' },
    { label: 'Nacionalidad', value: volunteer.value.nacionalidad || '-' },
    { label: 'Celular', value: volunteer.value.celular || '-' },
    { label: 'Fecha de nacimiento', value: formatDate(volunteer.value.fecha_nacimiento) === 'Sin registro' ? '-' : formatDate(volunteer.value.fecha_nacimiento) },
    { label: 'Correo electrónico', value: volunteer.value.correo_electronico || '-', layoutClass: 'fact-tile--span-2 fact-tile--email' },
    { label: 'Alergias', value: volunteer.value.alergias || '-' },
    { label: 'Enfermedades', value: volunteer.value.enfermedades || '-' },
    { label: 'Grupo Sanguíneo', value: volunteer.value.grupo_sanguineo || '-' },
    { label: 'Nivel de escolaridad', value: volunteer.value.nivel_escolaridad || '-', layoutClass: 'fact-tile--span-2' },
    { label: 'Ocupación', value: volunteer.value.ocupacion || '-' },
    {
      label: 'Nombre y Contacto para emergencias',
      value: volunteer.value.contacto_emergencia_nombre || '-',
      secondaryValue: volunteer.value.contacto_emergencia_numero || '-',
      layoutClass: 'fact-tile--span-2 fact-tile--contact'
    },
  ]
})

const ageLabel = computed(() => {
  const dateValue = volunteer.value?.fecha_nacimiento

  if (!dateValue) {
    return 'Sin registro'
  }

  const birthDate = new Date(`${dateValue}T00:00:00`)

  if (Number.isNaN(birthDate.getTime())) {
    return 'Sin registro'
  }

  const today = new Date()
  let age = today.getFullYear() - birthDate.getFullYear()
  const monthDelta = today.getMonth() - birthDate.getMonth()

  if (monthDelta < 0 || (monthDelta === 0 && today.getDate() < birthDate.getDate())) {
    age -= 1
  }

  return `${age} años`
})

const commissionInfo = computed(() => {
  const annual = selectedAnnual.value

  if (!annual) {
    return {
      inService: false,
      hasAnyData: false,
      dateRange: 'Sin fechas registradas',
      place: 'Sin registro',
      activity: 'Sin registro',
    }
  }

  const start = formatDate(annual.comision_fecha_inicio)
  const end = formatDate(annual.comision_fecha_termino)
  const place = annual.comision_lugar || 'Sin registro'
  const activity = annual.comision_actividad || 'Sin registro'
  const hasAnyData = Boolean(
    annual.estuvo_comision_servicio ||
    annual.comision_fecha_inicio ||
    annual.comision_fecha_termino ||
    annual.comision_lugar ||
    annual.comision_actividad
  )

  return {
    inService: Boolean(annual.estuvo_comision_servicio),
    hasAnyData,
    dateRange: [start, end].filter((value) => value !== 'Sin registro').join(' - ') || 'Sin fechas registradas',
    place,
    activity,
  }
})

const recognitionItems = computed(() => {
  const recognition = selectedAnnual.value?.reconocimiento || {}

  return [
    { label: 'Servicio extraordinario', value: Boolean(recognition.servicio_extraordinario) },
    { label: 'Abnegación', value: Boolean(recognition.abnegacion) },
    { label: '3a medalla de honor', value: Boolean(recognition.medalla_honor_3) },
    { label: '2a medalla de honor', value: Boolean(recognition.medalla_honor_2) },
    { label: '1a medalla de honor', value: Boolean(recognition.medalla_honor_1) },
    { label: 'Vittorio Cucchini', value: Boolean(recognition.vittorio_cucchini) },
    { label: 'Promesa', value: Boolean(recognition.promesa) },
    { label: 'Juramento', value: Boolean(recognition.juramento) },
  ]
})

function attachmentLinks(record) {
  const attachments = Array.isArray(record?.archivos_adjuntos) && record.archivos_adjuntos.length
    ? record.archivos_adjuntos
    : (record?.archivo_url
        ? [{ id: record.archivo_id ?? record.archivo_url, url: record.archivo_url, nombre_original: record.archivo_nombre }]
        : [])

  return attachments
    .map((attachment, index) => ({
      id: attachment.id ?? `${record?.id || 'record'}-${index}`,
      url: attachment.url ?? attachment.url_publica ?? '',
      name: attachment.nombre_original ?? attachment.nombre ?? attachment.archivo_nombre ?? `Respaldo ${index + 1}`
    }))
    .filter((attachment) => attachment.url)
}

const volunteerActivitiesForYear = computed(() => {
  const year = activePeriodYear.value
  const activities = Array.isArray(volunteer.value?.actividades) ? volunteer.value.actividades : []

  if (!year) {
    return []
  }

  return [...activities]
    .filter((activity) => Number(String(activity.fecha_inicio || activity.fecha_termino || '').slice(0, 4)) === Number(year))
    .sort((left, right) => String(right.fecha_inicio || right.fecha_termino || '').localeCompare(String(left.fecha_inicio || left.fecha_termino || '')))
})

const filteredVolunteerActivities = computed(() => volunteerActivitiesForYear.value.filter((activity) => matchesSearch([
  activity.nombre,
  activity.tipo,
  activity.lugar,
  activity.colaborador_externo,
  activity.filial?.nombre,
  activity.fecha_inicio,
  activity.fecha_termino,
  activity.pivot?.horas_asistidas,
].filter(Boolean).join(' '))))

const visibleVolunteerActivities = computed(() =>
  filteredVolunteerActivities.value.slice(volunteerActivitiesWindowStart.value, volunteerActivitiesWindowStart.value + volunteerActivitiesWindowSize.value)
)

const volunteerActivitiesWindowEnd = computed(() =>
  Math.min(volunteerActivitiesWindowStart.value + volunteerActivitiesWindowSize.value, filteredVolunteerActivities.value.length)
)

const canGoPrevVolunteerActivities = computed(() => volunteerActivitiesWindowStart.value > 0)
const canGoNextVolunteerActivities = computed(() => volunteerActivitiesWindowEnd.value < filteredVolunteerActivities.value.length)

const filteredPersonalFacts = computed(() => personalFacts.value.filter((item) => matchesSearch(`${item.label} ${item.value}`)))
const filteredTitles = computed(() => (selectedAnnual.value?.titulos || []).filter((item) => matchesSearch(`${item.titulo} ${item.entregado_por} ${item.codigo_titulo} ${attachmentLinks(item).map((attachment) => attachment.name).join(' ')}`)))
const filteredCourses = computed(() => (selectedAnnual.value?.cursos || []).filter((item) => matchesSearch(`${item.nombre_curso} ${item.entregado_por} ${item.codigo_curso} ${attachmentLinks(item).map((attachment) => attachment.name).join(' ')}`)))
const filteredOtherDocuments = computed(() => (selectedAnnual.value?.otros_documentos || []).filter((item) => matchesSearch(`${item.nombre_documento} ${item.motivo} ${attachmentLinks(item).map((attachment) => attachment.name).join(' ')}`)))
const filteredSanctions = computed(() => (selectedAnnual.value?.sanciones || []).filter((item) => matchesSearch(`${item.tipo_sancion} ${item.fecha} ${item.resumen_sancion} ${item.apelacion} ${item.fecha_apelacion} ${item.decision_cig}`)))
const filteredRecognitionItems = computed(() => recognitionItems.value.filter((item) => matchesSearch(`${item.label} ${item.value ? 'si' : 'no'}`)))

const showCommissionPanel = computed(() =>
  Boolean(selectedAnnual.value) &&
  (!normalizedSearch.value || matchesSearch(`comision ${commissionInfo.value.place} ${commissionInfo.value.activity} ${commissionInfo.value.dateRange}`))
)
const showCommentsPanel = computed(() =>
  Boolean(selectedAnnual.value) &&
  (!normalizedSearch.value || matchesSearch(selectedAnnual.value?.comentarios || ''))
)

const hasSearchResults = computed(() => {
  if (!selectedAnnual.value) {
    return false
  }

  if (!normalizedSearch.value) {
    return true
  }

  return Boolean(
    filteredPersonalFacts.value.length ||
    filteredVolunteerActivities.value.length ||
    filteredTitles.value.length ||
    filteredCourses.value.length ||
    filteredOtherDocuments.value.length ||
    filteredSanctions.value.length ||
    filteredRecognitionItems.value.length ||
    showCommissionPanel.value ||
    showCommentsPanel.value
  )
})

watch(
  () => route.query.anio,
  () => syncSelectedYear(),
  { immediate: true }
)

watch(annualRecords, () => {
  syncSelectedYear()
  syncAnnualWindow()
})

watch([filteredVolunteerActivities, volunteerActivitiesWindowSize], () => {
  syncVolunteerActivitiesWindow()
}, { immediate: true })
onMounted(() => {
  updateViewportWidth()
  window.addEventListener('resize', updateViewportWidth)
  fetchUser()
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateViewportWidth)
})

async function fetchUser() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await axios.get(`${API_BASE}/user/${route.params.id}`)
    user.value = response.data

    if (!response.data?.voluntario) {
      errorMessage.value = 'El usuario no tiene una ficha de voluntario asociada.'
      return
    }

    syncSelectedYear()
    if (canEditOwnPersonalData.value) {
      await loadMyRequests()
    }
  } catch (error) {
    errorMessage.value = 'No se pudo cargar la hoja de vida del voluntario.'
  } finally {
    isLoading.value = false
  }
}

async function loadMyRequests() {
  try {
    const response = await axios.get(`${API_BASE}/mis-solicitudes-hoja-vida`)
    pendingRequests.value = Array.isArray(response.data) ? response.data : []
  } catch (error) {
    pendingRequests.value = []
  }
}

function pendingRequestLabel(request) {
  const action = { crear: 'Agregar', actualizar: 'Modificar', eliminar: 'Eliminar' }[request.accion] || request.accion
  const type = { titulo: 'título', curso: 'curso', documento: 'documento' }[request.tipo_registro] || request.tipo_registro
  return `${action} ${type}`
}

function syncSelectedYear() {
  const requestedYear = Number(route.query.anio)
  const years = annualRecords.value.map((record) => Number(record.anio))

  if (requestedYear && years.includes(requestedYear)) {
    selectedYear.value = requestedYear
    return
  }

  selectedYear.value = years[0] || null
}

function syncAnnualWindow() {
  if (!annualRecords.value.length) {
    annualWindowStart.value = 0
    return
  }

  const maxStart = Math.max(annualRecords.value.length - annualWindowSize, 0)
  const selectedIndex = annualRecords.value.findIndex((record) => Number(record.anio) === Number(selectedYear.value))
  const targetIndex = selectedIndex >= 0 ? selectedIndex : 0

  if (targetIndex < annualWindowStart.value) {
    annualWindowStart.value = targetIndex
  } else if (targetIndex >= annualWindowStart.value + annualWindowSize) {
    annualWindowStart.value = targetIndex - annualWindowSize + 1
  }

  annualWindowStart.value = Math.min(Math.max(annualWindowStart.value, 0), maxStart)
}

function goToPreviousAnnualPage() {
  annualWindowStart.value = Math.max(annualWindowStart.value - annualWindowSize, 0)
}

function goToNextAnnualPage() {
  const maxStart = Math.max(annualRecords.value.length - annualWindowSize, 0)
  annualWindowStart.value = Math.min(annualWindowStart.value + annualWindowSize, maxStart)
}

function syncVolunteerActivitiesWindow() {
  if (!filteredVolunteerActivities.value.length) {
    volunteerActivitiesWindowStart.value = 0
    return
  }

  const maxStart = Math.max(filteredVolunteerActivities.value.length - volunteerActivitiesWindowSize.value, 0)
  volunteerActivitiesWindowStart.value = Math.min(Math.max(volunteerActivitiesWindowStart.value, 0), maxStart)
}

function goToPreviousVolunteerActivitiesPage() {
  volunteerActivitiesWindowStart.value = Math.max(volunteerActivitiesWindowStart.value - volunteerActivitiesWindowSize.value, 0)
}

function goToNextVolunteerActivitiesPage() {
  const maxStart = Math.max(filteredVolunteerActivities.value.length - volunteerActivitiesWindowSize.value, 0)
  volunteerActivitiesWindowStart.value = Math.min(volunteerActivitiesWindowStart.value + volunteerActivitiesWindowSize.value, maxStart)
}

function yearLink(year) {
  return {
    name: 'HistorialView',
    params: { id: route.params.id },
    query: { anio: year }
  }
}

function goBack() {
  router.push({ name: canManageHojaVida.value ? 'voluntarios' : 'inicio' })
}

function updateViewportWidth() {
  viewportWidth.value = window.innerWidth
}

function openAnnualHistoryModal() {
  isAnnualHistoryModalOpen.value = true
}

function closeAnnualHistoryModal() {
  isAnnualHistoryModalOpen.value = false
}

function openPdfExport() {
  if (!selectedAnnual.value) {
    return
  }

  router.push({
    name: 'HistorialPdfView',
    params: { id: route.params.id },
    query: { anio: selectedAnnual.value.anio }
  })
}

function openCreateEditor() {
  editorRecord.value = null
  editorSection.value = null
  isEditorOpen.value = true
}

function openEditEditor() {
  if (!selectedAnnual.value) {
    return
  }

  editorRecord.value = selectedAnnual.value
  editorSection.value = null
  isEditorOpen.value = true
}

function openSectionEditor(section) {
  if (!selectedAnnual.value) {
    return
  }

  editorRecord.value = selectedAnnual.value
  editorSection.value = section
  isEditorOpen.value = true
}

function openPersonalDataEditor() {
  if (!selectedAnnual.value || !canEditOwnPersonalData.value) {
    return
  }

  editorRecord.value = selectedAnnual.value
  editorSection.value = 'personal'
  isEditorOpen.value = true
}

function closeEditor() {
  isEditorOpen.value = false
  editorRecord.value = null
  editorSection.value = null
}

async function handleRecordSaved(record) {
  closeEditor()
  await fetchUser()
  selectedYear.value = Number(record?.anio || selectedYear.value)
  router.replace({
    name: 'HistorialView',
    params: { id: route.params.id },
    query: { anio: selectedYear.value }
  })
}

async function deleteSelectedAnnual() {
  if (!selectedAnnual.value?.id) {
    return
  }

  const result = await Swal.fire({
    title: `Eliminar hoja anual ${selectedAnnual.value.anio}?`,
    text: 'Se perderá la información registrada para ese periodo.',
    icon: 'warning',
    iconHtml: '×',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    buttonsStyling: false,
    customClass: {
      popup: 'annual-delete-alert',
      icon: 'annual-delete-alert__icon',
      title: 'annual-delete-alert__title',
      htmlContainer: 'annual-delete-alert__text',
      confirmButton: 'annual-delete-alert__confirm',
      cancelButton: 'annual-delete-alert__cancel',
      actions: 'annual-delete-alert__actions'
    },
    didOpen: (popup) => {
      const icon = popup.querySelector('.swal2-icon')
      const iconContent = popup.querySelector('.swal2-icon-content')
      const actions = popup.querySelector('.swal2-actions')
      const confirmButton = popup.querySelector('.annual-delete-alert__confirm')
      const cancelButton = popup.querySelector('.annual-delete-alert__cancel')

      popup.style.width = 'min(32rem, calc(100vw - 2rem))'
      popup.style.padding = '1.75rem 1.5rem 1.6rem'
      popup.style.borderRadius = '22px'

      if (icon) {
        icon.style.width = '5.25rem'
        icon.style.height = '5.25rem'
        icon.style.margin = '0 auto 1rem'
        icon.style.border = '4px solid #ff3743'
        icon.style.borderRadius = '999px'
        icon.style.color = '#ff3743'
      }

      if (iconContent) {
        iconContent.style.color = '#ff3743'
        iconContent.style.fontSize = '3rem'
        iconContent.style.lineHeight = '1'
      }

      if (actions) {
        actions.style.display = 'flex'
        actions.style.justifyContent = 'center'
        actions.style.gap = '0.75rem'
        actions.style.marginTop = '1.25rem'
      }

      ;[confirmButton, cancelButton].forEach((button) => {
        if (!button) {
          return
        }

        button.style.minWidth = '6.25rem'
        button.style.minHeight = '2.7rem'
        button.style.padding = '0.7rem 1.15rem'
        button.style.border = '0'
        button.style.borderRadius = '0.28rem'
        button.style.fontSize = '0.98rem'
        button.style.fontWeight = '700'
        button.style.color = '#fff'
      })

      if (confirmButton) {
        confirmButton.style.background = '#ff3743'
      }

      if (cancelButton) {
        cancelButton.style.background = '#7f8a96'
      }
    }
  })

  if (!result.isConfirmed) {
    return
  }

  try {
    await axios.delete(`${API_BASE}/hoja-vida-anual/${selectedAnnual.value.id}`)
    show_alerta('Hoja anual eliminada correctamente.', 'success')
    await fetchUser()
    router.replace({
      name: 'HistorialView',
      params: { id: route.params.id },
      query: selectedYear.value ? { anio: selectedYear.value } : {}
    })
  } catch (error) {
    show_alerta('No se pudo eliminar la hoja anual.', 'error')
  }
}
function triggerPhotoInput() {
  if (!canUpdatePhoto.value || isUploadingPhoto.value) {
    return
  }

  photoInput.value?.click()
}

async function onProfilePhotoSelected(event) {
  const file = event.target.files?.[0]

  if (!file || !user.value?.id) {
    return
  }

  isUploadingPhoto.value = true

  try {
    const optimizedFile = await optimizeImage(file, { maxOutputBytes: 2 * 1024 * 1024 })
    const formData = new FormData()
    formData.append('foto_perfil', optimizedFile)

    const response = await axios.post(`${API_BASE}/user/${user.value.id}/foto-perfil`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    user.value = response.data
    show_alerta('Foto de perfil actualizada correctamente.', 'success')
  } catch (error) {
    show_alerta('No se pudo actualizar la foto de perfil.', 'error')
  } finally {
    isUploadingPhoto.value = false
    event.target.value = ''
  }
}

function formatDate(value) {
  if (!value) {
    return 'Sin registro'
  }

  const parsed = new Date(`${value}T00:00:00`)

  if (Number.isNaN(parsed.getTime())) {
    return value
  }

  return parsed.toLocaleDateString('es-CL')
}

function formatActivityDateRange(start, end) {
  if (!start && !end) {
    return 'Sin registro'
  }

  const formatSingleDate = (value) => {
    if (!value) {
      return 'Sin registro'
    }

    const [year, month, day] = String(value).slice(0, 10).split('-')

    if (!year || !month || !day) {
      return String(value)
    }

    return `${day}-${month}-${year}`
  }

  const startLabel = formatSingleDate(start)
  const endLabel = formatSingleDate(end)

  if (start && end && start !== end) {
    return `${startLabel} - ${endLabel}`
  }

  return startLabel !== 'Sin registro' ? startLabel : endLabel
}

function formatVolunteerActivityHours(value) {
  if (value === null || value === undefined || value === '') {
    return 'Sin registro'
  }

  const numericValue = Number(value)

  if (!Number.isFinite(numericValue)) {
    return 'Sin registro'
  }

  return `${numericValue % 1 === 0 ? numericValue.toFixed(0) : numericValue.toFixed(2)} h`
}
function normalizeSearch(value) {
  return String(value || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .trim()
}

function matchesSearch(value) {
  if (!normalizedSearch.value) {
    return true
  }

  return normalizeSearch(value).includes(normalizedSearch.value)
}
</script>

<style scoped>
.content-wrapper {
  flex: 1;
  background: #f4f6f8;
  min-height: 100vh;
}

.content {
  padding: clamp(1.25rem, 1rem + 1vw, 2rem);
}

.page-shell {
  width: min(100%, 1840px);
  margin: 0 auto;
}

.hero-kicker,
.panel-kicker {
  margin: 0 0 0.3rem;
  color: #8a96a8;
  font-size: 0.92rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.panel-kicker--section-title {
  color: var(--cr-navy);
  font-family: 'Montserrat', sans-serif;
  font-size: clamp(1.35rem, 1.05rem + 0.9vw, 2.2rem);
  font-weight: 800;
  letter-spacing: -0.03em;
  line-height: 1.02;
  margin-bottom: 0.45rem;
  text-transform: none;
}

.hero-panel h2,
.panel-header h3 {
  margin: 0;
  color: var(--cr-navy-medium);
}

.panel-header h3 {
  font-size: clamp(1.35rem, 1.1rem + 0.55vw, 1.72rem);
  line-height: 1.2;
}

.section-personal-panel__title {
  color: var(--cr-red-vivid);
}
.panel-record-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 38px;
  padding: 0.48rem 0.95rem;
  border: 1.5px solid var(--cr-red-vivid);
  border-radius: 999px;
  background: var(--cr-white);
  color: var(--cr-red-vivid);
  font-family: 'Montserrat', sans-serif;
  font-size: 0.98rem;
  font-weight: 800;
  line-height: 1;
  white-space: nowrap;
}

.hero-layout,
.content-grid,
.hero-stats,
.fact-grid,
.split-grid,
.recognition-grid,
.commission-grid {
  display: grid;
}

.hero-stage {
  display: block;
  margin-bottom: 1rem;
}

.hero-stage::after {
  content: "";
  display: block;
  clear: both;
}

.hero-main {
  min-width: 0;
  display: grid;
  gap: 1rem;
  margin-right: calc(21.5rem + 1.2rem);
}

.hero-layout {
  grid-template-columns: 170px minmax(0, 1fr);
  grid-template-areas: "rail panel";
  gap: 1.2rem;
  align-items: center;
}

.hero-rail {
  grid-area: rail;
  display: grid;
  grid-template-columns: 48px minmax(0, 1fr);
  grid-template-areas:
    "back year"
    "photo photo";
  grid-template-rows: auto 1fr;
  gap: 0.7rem;
  align-items: start;
  align-self: center;
}

.back-button,
.year-card,
.photo-card,
.hero-panel,
.panel,
.search-shell,
.panel-empty {
  border: 1px solid var(--cr-border);
  background: var(--cr-white);
}

.back-button,
.year-card {
  min-height: 44px;
  border-radius: 14px;
}

.back-button {
  grid-area: back;
  color: var(--cr-white);
  background: var(--cr-navy);
  border-color: var(--cr-navy);
  font-size: 0.96rem;
}

.year-card {
  grid-area: year;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--cr-red-bright);
  border-color: var(--cr-red-bright);
  color: var(--cr-white);
  font-size: 1.2rem;
  font-weight: 800;
}

.photo-card,
.hero-panel,
.panel,
.panel-empty {
  border-radius: 18px;
  box-shadow: 0 16px 36px var(--cr-navy-shadow);
}

.photo-card {
  grid-area: photo;
  width: min(100%, 10.25rem);
  justify-self: center;
  align-self: center;
  margin-top: 0;
  padding: 0.4rem;
  display: grid;
  gap: 0.4rem;
  align-content: start;
}


.photo-frame {
  width: 100%;
  aspect-ratio: 1 / 1;
  border-radius: 14px;
  background: #f3f6fb;
  border: 1px dashed #c8d3e1;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #71829a;
  font-weight: 600;
}

.photo-frame img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-action {
  border: none;
  border-radius: 999px;
  min-height: 38px;
  background: var(--cr-navy);
  color: var(--cr-white);
  font-size: 0.9rem;
  font-weight: 700;
}

.photo-action:disabled {
  opacity: 0.65;
}

.photo-history-trigger {
  display: none;
}

.hero-panel {
  grid-area: panel;
  padding: 1.2rem 1.35rem;
  min-height: 216px;
}

.hero-panel h2 {
  font-size: clamp(2.55rem, 2rem + 1vw, 3.35rem);
  line-height: 1.05;
  font-weight: 800;
}

.hero-top,
.toolbar-row,
.panel-header,
.commission-top,
.stack-card__row {
  display: flex;
  justify-content: space-between;
  gap: 0.8rem;
  align-items: center;
  flex-wrap: wrap;
}

.hero-top {
  align-items: center;
  justify-content: flex-start;
  flex-wrap: nowrap;
  gap: 1.2rem;
}

.hero-copy {
  min-width: 0;
  flex: 1;
}

.hero-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1.2rem;
  margin-bottom: 0.4rem;
}

.hero-brand {
  width: 112px;
  min-width: 112px;
  height: 90px;
  border-radius: 16px;
  border: 1px solid var(--cr-border);
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--cr-white);
}
.hero-side {
  width: 7.2rem;
  min-width: 7.2rem;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  justify-self: end;
  gap: 0.6rem;
}
.hero-brand-actions {
  display: grid;
  justify-items: stretch;
  gap: 0.55rem;
  width: 100%;
  box-sizing: border-box;
}

.hero-actions-row {
  display: none;
}

.hero-brand-actions .hero-brand-action {
  width: 100%;
  max-width: 100%;
  justify-self: end;
  border-radius: 12px;
  min-height: 34px;
  padding: 0.4rem 0.5rem;
  font-size: 0.76rem;
  line-height: 1.15;
  white-space: normal;
}


.hero-brand img {
  width: 72px;
  height: auto;
}

.hero-stats {
  grid-template-columns: repeat(2, minmax(0, 260px));
  gap: 1rem;
  margin-top: 0.9rem;
}

.fact-tile,
.recognition-item,
.commission-item,
.stack-card,
.counter-chip,
.mini-pill {
  border-radius: 14px;
}

.hero-stat span,
.fact-tile span,
.recognition-item span,
.commission-item span,
.stack-card__row span {
  display: block;
  font-size: 0.83rem;
  color: #708198;
  margin-bottom: 0.28rem;
  text-transform: uppercase;
}

.hero-stat span {
  margin-bottom: 0.2rem;
  color: var(--cr-navy);
  font-size: 1.05rem;
  line-height: 1.2;
  text-transform: none;
}

.hero-stat strong,
.fact-tile strong,
.recognition-item strong,
.commission-item strong,
.stack-card__row strong {
  display: block;
  font-size: 1.22rem;
  line-height: 1.35;
  color: #323232;
}

.hero-stat strong {
  color: var(--cr-navy-dark);
  font-size: 1.22rem;
  line-height: 1.3;
}

.toolbar-row {
  margin-bottom: 1rem;
}

.toolbar-row--hero-outside {
  width: 100%;
  max-width: none;
  margin-bottom: 0;
}

.search-shell {
  width: 100%;
  min-height: 60px;
  border-radius: 999px;
  padding: 0 1.2rem;
  display: flex;
  align-items: center;
  gap: 0.7rem;
  box-shadow: 0 10px 26px rgba(15, 47, 95, 0.06);
}

.search-shell i {
  color: #8a96a8;
}

.search-shell input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  color: var(--cr-navy);
  -webkit-text-fill-color: var(--cr-navy);
  caret-color: var(--cr-navy);
  font-size: 1.08rem;
}

.search-shell input::placeholder {
  color: #7d8ba0;
  opacity: 1;
}


.counter-chip,
.mini-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.6rem 0.95rem;
  font-weight: 700;
  font-size: 0.96rem;
}

.content-grid {
  width: 100%;
  min-width: 0;
  grid-template-columns: 1fr;
  margin-right: 0;
  gap: 0.95rem;
  align-items: start;
}

.main-column {
  width: 100%;
  min-width: 0;
  display: grid;
  gap: 1.2rem;
}

.main-column > * {
  width: 100%;
}

.sidebar-column {
  display: none;
  gap: 1.2rem;
  width: 100%;
  max-width: 21.5rem;
  justify-self: stretch;
}

.history-panel {
  min-width: 0;
  width: 100%;
}

.hero-history-column {
  float: right;
  width: 21.5rem;
  margin-left: 1.2rem;
  display: grid;
  gap: 1.2rem;
  align-content: start;
}

.hero-history-panel {
  min-height: 100%;
}

.hero-history-panel > .annual-list {
  margin-top: 1.25rem;
}

.history-modal {
  position: fixed;
  inset: 0;
  z-index: 1300;
  display: none;
  align-items: flex-end;
  justify-content: center;
  padding: 1rem;
  background: rgba(15, 29, 55, 0.4);
  backdrop-filter: blur(4px);
}

.history-modal__panel {
  width: min(100%, 28rem);
  max-height: min(78vh, 42rem);
  overflow: auto;
  border-radius: 22px;
  background: var(--cr-white);
  border: 1px solid var(--cr-border);
  box-shadow: 0 20px 48px rgba(15, 47, 95, 0.18);
  padding: 1rem;
}

.history-modal__header,
.history-modal__controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.history-modal__header {
  margin-bottom: 0.9rem;
}

.history-modal__controls {
  margin-bottom: 0.9rem;
  flex-wrap: wrap;
}

.history-modal__close {
  width: 2.4rem;
  height: 2.4rem;
  border: 1px solid #d6dfeb;
  border-radius: 999px;
  background: var(--cr-white);
  color: var(--cr-navy);
}

.history-modal__list {
  padding-right: 0.1rem;
}

.panel-header--history {
  align-items: flex-start;
}

.history-panel__actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.7rem;
}

.history-panel__actions--header {
  align-self: flex-start;
  margin-top: -0.35rem;
}

.history-panel__action-button {
  width: 52px;
  height: 52px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #c7d8ea;
  border-radius: 16px;
  background: var(--cr-white);
  color: var(--cr-navy);
  font-size: 1.2rem;
  box-shadow: 0 10px 22px var(--cr-navy-shadow);
}

.history-panel__action-button--danger {
  border-color: #ffb4bb;
  color: #d3272d;
}

.history-panel__action-button:hover {
  transform: translateY(-1px);
}

.history-panel__actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.7rem;
}

.history-panel__actions--header {
  align-self: flex-start;
  margin-top: -0.35rem;
}

.history-panel__action-button {
  width: 52px;
  height: 52px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #c7d8ea;
  border-radius: 16px;
  background: var(--cr-white);
  color: var(--cr-navy);
  font-size: 1.2rem;
  box-shadow: 0 10px 22px var(--cr-navy-shadow);
}

.history-panel__action-button--danger {
  border-color: #ffb4bb;
  color: #d3272d;
}

.history-panel__action-button:hover {
  transform: translateY(-1px);
}

.history-controls {
  display: grid;
  gap: 0.65rem;
  justify-items: end;
}

.history-controls--footer {
  margin-top: 1.15rem;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  flex-wrap: nowrap;
  gap: 0.65rem;
}

.history-counter {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
  padding: 0.45rem 0.8rem;
  border-radius: 999px;
  background: var(--cr-blue-pale);
  color: var(--cr-navy);
  font-size: 0.95rem;
  font-weight: 700;
}

.history-controls--footer .history-nav {
  justify-content: flex-start;
  flex: 0 0 auto;
}

.history-nav {
  display: flex;
  gap: 0.55rem;
}

.history-nav__button {
  width: 42px;
  height: 42px;
  border: 1px solid #d6dfeb;
  border-radius: 999px;
  background: var(--cr-white);
  color: var(--cr-navy);
}

.history-nav__button:disabled {
  opacity: 0.45;
}
.recognition-panel {
  min-width: 0;
  width: 100%;
}

.panel {
  padding: 1.05rem 1.15rem;
}

.counter-chip {
  background: var(--cr-blue-pale);
  color: var(--cr-navy);
}

.fact-grid {
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 0.85rem;
}

.fact-grid--personal {
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.85rem;
}

.fact-tile {
  padding: 0.95rem 1rem;
  background: var(--cr-gray-50);
  border: 1px solid var(--cr-gray-200);
}

.fact-tile--personal {
  min-width: 0;
  padding: 0.82rem 0.95rem;
  display: grid;
  gap: 0.22rem;
  align-content: start;
  background: #f7f9fc;
  border-color: #e9eef5;
}

.fact-tile__label {
  margin-bottom: 0 !important;
}

.fact-tile--personal .fact-tile__label {
  color: #7a8faa;
  font-size: clamp(0.98rem, 0.9rem + 0.18vw, 1.12rem);
  line-height: 1.16;
  font-weight: 800;
  text-transform: none;
  letter-spacing: 0;
  white-space: normal;
  overflow-wrap: anywhere;
}

.fact-tile--personal strong {
  min-width: 0;
  color: var(--cr-navy-medium);
  font-size: clamp(1.05rem, 0.98rem + 0.2vw, 1.24rem);
  line-height: 1.26;
  font-weight: 800;
  word-break: normal;
  overflow-wrap: anywhere;
}

.fact-tile--personal.fact-tile--primary strong {
  font-size: 1.3rem;
}

.fact-tile--email strong {
  font-size: clamp(0.98rem, 0.92rem + 0.18vw, 1.1rem);
  line-height: 1.22;
  white-space: normal;
  overflow-wrap: anywhere;
}


.fact-tile__split {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 0.6rem;
  align-items: end;
}

.fact-tile__secondary {
  text-align: right;
  white-space: nowrap;
}

.fact-tile--wide,
.fact-tile--span-2 {
  grid-column: span 2;
}

.commission-card {
  border: 1px solid var(--cr-gray-200);
  border-radius: 16px;
  background: var(--cr-gray-50);
  padding: 0.85rem 0.95rem;
}

.commission-grid {
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 0.85rem;
  margin-top: 0.7rem;
}

.commission-item {
  padding: 0.75rem 0.85rem;
  background: var(--cr-white);
  border: 1px solid var(--cr-gray-200);
}

.commission-item--wide {
  grid-column: span 2;
}

.mini-pill {
  background: #edf3fb;
  color: var(--cr-navy);
}

.mini-pill--active {
  background: #e7f8ed;
  color: #1f7a3f;
}

.split-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.2rem;
}

.table-responsive {
  overflow-x: auto;
}

.sheet-table {
  width: 100%;
  border-collapse: collapse;
}

.sheet-table th,
.sheet-table td {
  padding: 0.72rem 0.76rem;
  border-bottom: 1px solid #e8edf4;
  text-align: left;
  vertical-align: top;
  font-size: 0.97rem;
}

.sheet-table th {
  background: #f7f9fc;
  color: var(--cr-navy);
  font-size: 0.82rem;
  text-transform: uppercase;
}

.sheet-link {
  color: var(--cr-navy);
  font-weight: 700;
  text-decoration: none;
}

.sheet-link:hover {
  text-decoration: underline;
}

.stack-list {
  display: grid;
  gap: 0.85rem;
}

.stack-card {
  background: var(--cr-gray-50);
  border: 1px solid var(--cr-gray-200);
  padding: 0.8rem 0.88rem;
}

.stack-card__row--wide {
  display: grid;
}

.recognition-grid {
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 0.85rem;
}

.recognition-grid--sidebar {
  grid-template-columns: 1fr;
  gap: 0.65rem;
}

.recognition-item {
  background: var(--cr-gray-50);
  border: 1px solid var(--cr-gray-200);
  padding: 0.62rem 0.74rem;
  display: grid;
  gap: 0.12rem;
  align-content: start;
}

.recognition-item.active {
  background: #fff4f5;
  border-color: #ffd2d7;
}

.comments-box {
  min-height: 120px;
  padding: 1.05rem 1.15rem;
  border-radius: 16px;
  background: var(--cr-gray-50);
  border: 1px solid var(--cr-gray-200);
  color: #323232;
  white-space: pre-line;
  line-height: 1.65;
  font-size: 1.02rem;
}

.action-button {
  width: 13.5rem;
  max-width: 100%;
  min-height: 54px;
  padding: 0 1rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  border: 1px solid var(--cr-navy);
  background: var(--cr-navy);
  color: var(--cr-white);
  font-weight: 700;
  font-size: 1.02rem;
}

.action-button--primary {
  background: var(--cr-red-bright);
  border-color: var(--cr-red-bright);
}

.action-button--ghost {
  background: var(--cr-white);
  color: var(--cr-navy);
}

.action-button:disabled {
  opacity: 0.6;
}

.volunteer-activities-grid {
  grid-template-columns: 1fr;
  gap: 0.95rem;
}

.volunteer-activity-card {
  display: grid;
  gap: 0.95rem;
  padding: 1.05rem 1.05rem 0.95rem;
  background: var(--cr-white);
  border: 1px solid var(--cr-gray-200);
  box-shadow: 0 12px 26px var(--cr-navy-shadow);
}

.volunteer-activity-card__header {
  display: grid;
  gap: 0.72rem;
}

.volunteer-activity-card__name {
  margin: 0;
  color: var(--cr-navy);
  font-family: 'Montserrat', sans-serif;
  font-size: clamp(1.05rem, 0.98rem + 0.22vw, 1.28rem);
  font-weight: 800;
  line-height: 1.18;
}

.volunteer-activity-card__type {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  align-self: start;
  width: fit-content;
  max-width: 100%;
  min-height: 2rem;
  padding: 0.26rem 0.78rem;
  border: 1px solid #f2c4c8;
  border-radius: 999px;
  background: #fff6f7;
  color: #d83b46;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.82rem;
  font-weight: 700;
  line-height: 1.1;
  letter-spacing: 0.01em;
  white-space: normal;
}

.volunteer-activity-card__details {
  display: grid;
  gap: 0;
}

.volunteer-activity-card__item {
  display: grid;
  grid-template-columns: 2.4rem minmax(0, 1fr);
  gap: 0.82rem;
  align-items: center;
  padding: 0.78rem 0;
  border-top: 1px solid var(--cr-gray-200);
}

.volunteer-activity-card__item:first-child {
  border-top: none;
  padding-top: 0;
}

.volunteer-activity-card__item:last-child {
  padding-bottom: 0;
}

.volunteer-activity-card__icon {
  width: 2.4rem;
  height: 2.4rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.85rem;
  color: var(--cr-red-vivid);
  background: #fff5f5;
  font-size: 1.1rem;
}

.volunteer-activity-card__content {
  min-width: 0;
  display: grid;
  gap: 0.16rem;
}

.volunteer-activity-card__label {
  color: var(--cr-navy);
  font-family: 'Montserrat', sans-serif;
  font-size: 0.86rem;
  font-weight: 700;
  line-height: 1.15;
}

.volunteer-activity-card__value {
  color: #323232;
  font-family: 'Open Sans', sans-serif;
  font-size: 0.98rem;
  font-weight: 700;
  line-height: 1.38;
  word-break: break-word;
}

.volunteer-activities-pagination {
  margin-top: 0.95rem;
}.section-upload-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  min-width: 11.25rem;
  max-width: 100%;
  min-height: 40px;
  padding: 0.58rem 1rem;
  border: 1px solid var(--cr-red-vivid);
  border-radius: 0.95rem;
  background: var(--cr-red-vivid);
  color: var(--cr-white);
  box-shadow: 0 10px 24px rgba(245, 51, 63, 0.18);
  font-size: 0.94rem;
  font-weight: 800;
  line-height: 1;
  transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.section-upload-button:hover,
.section-upload-button:focus-visible {
  background: #dc2430;
  border-color: #dc2430;
  color: var(--cr-white);
  box-shadow: 0 12px 28px rgba(220, 36, 48, 0.24);
  transform: translateY(-1px);
}

.section-upload-button:disabled {
  opacity: 0.55;
  box-shadow: none;
  transform: none;
}

.annual-list {
  display: grid;
  gap: 0.72rem;
}

.panel-empty {
  display: grid;
  place-items: center;
  min-height: 190px;
  text-align: center;
  padding: 1.2rem;
  color: #617389;
  font-size: 1.05rem;
}

.panel-empty--error {
  color: #a61f2b;
  background: #fff8f8;
  border-color: #efc7cc;
}

.panel-empty--soft,
.empty-inline {
  background: #fafcfe;
  border: 1px dashed #d7e1ec;
  border-radius: 16px;
  padding: 1.05rem 1.15rem;
  color: #667a93;
  font-size: 1.08rem;
}


@media (min-width: 861px) and (max-width: 1439.98px) {
  .volunteer-activities-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1440px) {
  .volunteer-activities-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (min-width: 1200px) and (max-width: 1439.98px) {
  .hero-top {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 7rem;
    align-items: start;
    gap: 0.85rem;
  }

  .hero-side {
  width: 7.2rem;
  min-width: 7.2rem;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  justify-self: end;
  gap: 0.6rem;
}
.hero-brand-actions {
  display: grid;
  justify-items: stretch;
  gap: 0.55rem;
  width: 100%;
  box-sizing: border-box;
}

.hero-actions-row {
  display: none;
}

.hero-brand-actions .hero-brand-action {
  width: 100%;
  max-width: 100%;
  justify-self: end;
  border-radius: 12px;
  min-height: 34px;
  padding: 0.4rem 0.5rem;
  font-size: 0.76rem;
  line-height: 1.15;
  white-space: normal;
}


.hero-brand img {
    width: 64px;
  }

  .hero-brand-actions {
    width: 100%;
    justify-items: stretch;
    gap: 0.34rem;
  }

  .hero-brand-actions .hero-brand-action {
    width: 100%;
    max-width: 100%;
    min-height: 30px;
    padding: 0.35rem 0.3rem;
    font-size: 0.62rem;
    line-height: 1.08;
  }
}

@media (min-width: 1600px) {
.fact-grid--personal {
    grid-template-columns: repeat(5, minmax(0, 1fr));
  }

  .hero-layout {
    grid-template-columns: 182px minmax(0, 1fr);
  }

  .content-grid {
    grid-template-columns: 1fr;
  }

  .hero-panel h2 {
    font-size: 3.45rem;
  }
}

@media (max-width: 1199.98px) {
  .hero-stage {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .hero-main {
    display: contents;
    margin-right: 0;
  }

  .hero-history-column {
    display: contents;
    float: none;
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0;
  }

  .hero-history-panel {
    order: 3;
    width: 100%;
    max-width: none;
  }

  .hero-layout {
    order: 1;
    grid-template-columns: minmax(7.5rem, 9rem) minmax(0, 1fr);
    gap: 1rem;
    align-items: center;
  }

  .toolbar-row--hero-outside {
    order: 2;
  }

  .content-grid {
    order: 4;
  }

  .hero-top {
    display: grid;
    grid-template-columns: minmax(0, 1fr) clamp(6.6rem, 11vw, 7.8rem);
    align-items: start;
    gap: 0.75rem;
  }

  .hero-panel {
    min-height: 208px;
  }

  .hero-panel h2 {
    font-size: clamp(1.9rem, 2.9vw, 2.8rem);
    line-height: 1;
  }

  .hero-side {
  width: 7.2rem;
  min-width: 7.2rem;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  justify-self: end;
  gap: 0.6rem;
}
.hero-brand-actions {
  display: grid;
  justify-items: stretch;
  gap: 0.55rem;
  width: 100%;
  box-sizing: border-box;
}

.hero-actions-row {
  display: none;
}

.hero-brand-actions .hero-brand-action {
  width: 100%;
  max-width: 100%;
  justify-self: end;
  border-radius: 12px;
  min-height: 34px;
  padding: 0.4rem 0.5rem;
  font-size: 0.76rem;
  line-height: 1.15;
  white-space: normal;
}


.hero-brand img {
    width: clamp(56px, 6vw, 68px);
  }

  .hero-brand-actions {
    display: grid;
    justify-items: center;
    gap: clamp(0.3rem, 0.7vw, 0.5rem);
    width: 100%;
    max-width: 100%;
  }

  .hero-brand-actions .hero-brand-action {
    width: 100%;
    max-width: 100%;
    min-height: clamp(30px, 3.2vw, 38px);
    padding: clamp(0.34rem, 0.8vw, 0.5rem) clamp(0.3rem, 0.6vw, 0.45rem);
    font-size: clamp(0.58rem, 0.95vw, 0.78rem);
    font-weight: 800;
    line-height: 1.08;
  }

  .hero-actions-row {
    display: none;
  }

  .photo-history-trigger {
    display: none;
  }

  .hero-history-column .hero-recognition-panel {
    display: none;
  }

  .sidebar-column.sidebar-column--mobile-only {
    display: grid;
    order: 99;
    width: 100%;
    max-width: none;
    margin-top: 0;
    gap: 0.9rem;
  }
}

@media (max-width: 767.98px) {
  .hero-stage {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.95rem;
  }

  .hero-main {
    display: contents;
    margin-right: 0;
  }

  .hero-history-column {
    display: contents;
    float: none;
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0;
  }

  .hero-history-panel {
    order: 3;
    width: 100%;
    max-width: none;
  }

  .hero-layout {
    order: 1;
    grid-template-columns: minmax(5.9rem, 6.3rem) minmax(0, 1fr);
    grid-template-areas: "rail panel";
    gap: 0.7rem;
    align-items: center;
  }

  .toolbar-row--hero-outside {
    order: 2;
  }

  .content-grid {
    order: 4;
  }

  .photo-history-trigger {
    display: none;
  }

  .hero-rail {
    display: grid;
    grid-template-columns: 34px minmax(0, 1fr);
    grid-template-areas:
      "back year"
      "photo photo";
    gap: 0.5rem;
    align-items: start;
    align-self: center;
  }

  .back-button,
  .year-card {
    min-height: 32px;
    border-radius: 10px;
  }

  .back-button {
    font-size: 0.82rem;
  }

  .year-card {
    min-width: 0;
    width: auto;
    padding: 0 0.55rem;
    justify-self: start;
    font-size: 0.94rem;
  }

  .photo-card {
    grid-column: 1 / -1;
    width: min(100%, 6.2rem);
    max-width: none;
    justify-self: center;
    align-self: center;
    margin-inline: auto;
    margin-top: 0;
    padding: 0.28rem;
    gap: 0.28rem;
    border-radius: 14px;
  }

  .photo-frame {
    border-radius: 10px;
  }

  .photo-action {
    min-height: 31px;
    font-size: 0.75rem;
    font-weight: 800;
  }

  .hero-panel {
    min-height: 208px;
  }

  .hero-top {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 6.1rem;
    align-items: start;
    gap: 0.55rem;
  }

  .hero-copy {
    min-width: 0;
  }

  .hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.08rem 0.4rem;
    margin-bottom: 0.18rem;
  }

  .hero-kicker {
    margin-bottom: 0;
    font-size: 0.62rem;
    line-height: 1.12;
    letter-spacing: 0.04em;
  }

  .hero-panel h2 {
    font-size: clamp(0.98rem, 4.8vw, 1.52rem);
    line-height: 0.98;
    margin-bottom: 0.28rem;
  }

  .hero-stats {
    gap: 0.18rem;
  }

  .hero-stat {
    gap: 0.08rem;
    padding-block: 0;
  }

  .hero-stat span {
    font-size: 0.64rem;
    line-height: 1.1;
    margin-bottom: 0.02rem;
  }

  .hero-stat strong {
    font-size: 0.84rem;
    line-height: 1.1;
  }

  .hero-side {
  width: 7.2rem;
  min-width: 7.2rem;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  justify-self: end;
  gap: 0.6rem;
}
.hero-brand-actions {
  display: grid;
  justify-items: stretch;
  gap: 0.55rem;
  width: 100%;
  box-sizing: border-box;
}

.hero-actions-row {
  display: none;
}

.hero-brand-actions .hero-brand-action {
  width: 100%;
  max-width: 100%;
  justify-self: end;
  border-radius: 12px;
  min-height: 34px;
  padding: 0.4rem 0.5rem;
  font-size: 0.76rem;
  line-height: 1.15;
  white-space: normal;
}


.hero-brand img {
    width: 62px;
  }

  .hero-brand-actions {
    display: grid;
    justify-items: center;
    gap: 0.28rem;
    width: 100%;
    max-width: 100%;
  }

  .hero-brand-actions .hero-brand-action {
    width: 100%;
    max-width: 100%;
    min-height: 28px;
    padding: 0.34rem 0.3rem;
    font-size: 0.56rem;
    font-weight: 800;
    line-height: 1.08;
  }

  .hero-actions-row {
    display: none;
  }

  .hero-history-column .hero-recognition-panel {
    display: none;
  }

  .sidebar-column.sidebar-column--mobile-only {
    display: grid;
    order: 99;
    width: 100%;
    max-width: none;
    margin-top: 0;
    gap: 0.7rem;
  }

  .search-shell {
    min-height: 48px;
    padding: 0 0.95rem;
    border-radius: 999px;
  }

  .search-shell input {
    color: var(--cr-navy);
    -webkit-text-fill-color: var(--cr-navy);
    caret-color: var(--cr-navy);
    font-size: 0.9rem;
  }

  .fact-grid--personal {
    grid-template-columns: 1fr;
  }

  .fact-grid--personal .fact-tile--span-2 {
    grid-column: auto;
  }

  .fact-tile__split {
    grid-template-columns: 1fr;
    gap: 0.2rem;
  }

  .fact-tile__secondary {
    text-align: left;
    white-space: normal;
  }
  .search-shell,
  .history-counter {
    width: 100%;
    justify-content: center;
  }

  .history-controls--footer .history-counter {
    width: auto;
    flex: 0 0 auto;
  }
}
:deep(.annual-delete-alert) {
  width: min(32rem, calc(100vw - 2rem));
  padding: 1.75rem 1.5rem 1.6rem;
  border-radius: 22px;
}

:deep(.annual-delete-alert__icon) {
  width: 5.25rem;
  height: 5.25rem;
  margin: 0 auto 1rem;
  border: 4px solid var(--cr-red-bright) !important;
  border-radius: 999px;
  color: var(--cr-red-bright) !important;
}

:deep(.annual-delete-alert__icon .swal2-icon-content) {
  color: var(--cr-red-bright) !important;
  font-size: 3rem;
  line-height: 1;
}

:deep(.annual-delete-alert__title) {
  color: #4a4a4a;
  font-size: 1.1rem;
  font-weight: 800;
}

:deep(.annual-delete-alert__text) {
  color: #666;
  font-size: 1rem;
  line-height: 1.5;
}

:deep(.annual-delete-alert__actions) {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
  margin-top: 1.25rem;
}

:deep(.annual-delete-alert__confirm),
:deep(.annual-delete-alert__cancel) {
  min-width: 6.25rem;
  min-height: 2.7rem;
  padding: 0.7rem 1.15rem;
  border: 0;
  border-radius: 0.28rem;
  font-size: 0.98rem;
  font-weight: 700;
  color: var(--cr-white);
}

:deep(.annual-delete-alert__confirm) {
  background: var(--cr-red-bright);
}

:deep(.annual-delete-alert__cancel) {
  background: #7f8a96;
}

:deep(.annual-delete-alert__confirm:focus),
:deep(.annual-delete-alert__cancel:focus) {
  outline: none;
  box-shadow: 0 0 0 3px rgba(23, 59, 112, 0.12);
}
.pending-requests-panel{margin:0 0 1rem;background:#fff8e5;border:1px solid #f0d58a;border-radius:18px;padding:1rem 1.2rem}.pending-requests-panel h3{margin:.15rem 0 .8rem}.pending-request-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:.65rem}.pending-request-list article{display:grid;gap:.25rem;background:var(--cr-white);border-radius:12px;padding:.75rem}.pending-request-list small{color:var(--cr-slate)}.pending-status{width:max-content;padding:.2rem .45rem;border-radius:999px;font-size:.7rem;font-weight:800;text-transform:uppercase;background:#ffefb6;color:#7b5700}.pending-status--aprobada{background:#dff7e8;color:#176b3a}.pending-status--rechazada{background:#fde2e3;color:#a4212a}
</style>



























































































