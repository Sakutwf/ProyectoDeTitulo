from pathlib import Path
path = Path(r'D:/Escritorio/Miscy/U/Proyecto de titulo/ProyectoDeTitulo/frontend/src/views/DocumentosView.vue')
text = path.read_text(encoding='utf-8')
marker = """              </template>

              <div v-else class="content-fields mt-4">
"""
insert = """              </template>

              <template v-else-if="isContextAnalysisType">
                <div class="narrative-layout mt-4">
                  <section class="narrative-section">
                    <h5>Objetivo del analisis</h5>
                    <textarea
                      v-model.trim="form.contenido.proposito_documento"
                      class="form-control"
                      rows="5"
                      placeholder="Describe el objetivo del analisis, el contexto del apoyo solicitado y el alcance del documento."
                    ></textarea>
                  </section>

                  <section class="narrative-section">
                    <h5>Descripcion del evento</h5>
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
                        <label class="form-label">Clima esperado</label>
                        <textarea v-model.trim="form.contenido.descripcion_evento.clima_esperado" class="form-control" rows="3" placeholder="Ej. Minima 4°, maxima 17°, parcialmente nublado."></textarea>
                      </div>
                    </div>
                  </section>

                  <section class="narrative-section">
                    <div class="section-headline">
                      <h5>Identificacion de riesgos</h5>
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
                          <select v-model="risk.probabilidad" class="form-select">
                            <option value="">Selecciona</option>
                            <option v-for="option in analysisProbabilityOptions" :key="option" :value="option">{{ option }}</option>
                          </select>
                        </div>
                        <div class="col-12 col-xl-3">
                          <label class="form-label">Impacto</label>
                          <select v-model="risk.impacto" class="form-select">
                            <option value="">Selecciona</option>
                            <option v-for="option in analysisImpactOptions" :key="option" :value="option">{{ option }}</option>
                          </select>
                        </div>
                        <div class="col-12 col-xl-1 d-flex justify-content-xl-end">
                          <button type="button" class="attendance-row__remove" @click="removeAnalysisRiskRow(index)">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                        </div>
                        <div class="col-12 col-xl-6">
                          <label class="form-label">Descripcion</label>
                          <textarea v-model.trim="risk.descripcion" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="col-12 col-xl-6">
                          <label class="form-label">Medidas de mitigacion</label>
                          <textarea v-model.trim="risk.mitigacion" class="form-control" rows="4"></textarea>
                        </div>
                      </div>
                    </div>
                  </section>

                  <section class="narrative-section">
                    <h5>Plan de traslados</h5>
                    <div class="row g-3">
                      <div class="col-12 col-xl-6">
                        <label class="form-label">Coordinacion con APS-SAMU 131</label>
                        <textarea v-model.trim="form.contenido.plan_traslados.coordinacion_samu" class="form-control" rows="4"></textarea>
                      </div>
                      <div class="col-12 col-xl-6">
                        <label class="form-label">Punto de encuentro o ubicacion adecuada</label>
                        <textarea v-model.trim="form.contenido.plan_traslados.punto_encuentro" class="form-control" rows="4"></textarea>
                      </div>
                      <div class="col-12 col-xl-6">
                        <label class="form-label">Comunicacion interna</label>
                        <textarea v-model.trim="form.contenido.plan_traslados.comunicacion_interna" class="form-control" rows="4"></textarea>
                      </div>
                      <div class="col-12 col-xl-6">
                        <label class="form-label">Documentacion medica</label>
                        <textarea v-model.trim="form.contenido.plan_traslados.documentacion_medica" class="form-control" rows="4"></textarea>
                      </div>
                    </div>
                  </section>

                  <section class="narrative-section">
                    <div class="section-headline">
                      <h5>Protocolo de traslado</h5>
                      <button type="button" class="btn btn-outline-primary btn-sm" @click="addAnalysisProtocolStep()">
                        <i class="fa-solid fa-plus me-2"></i>Agregar paso
                      </button>
                    </div>

                    <div class="narrative-count-list">
                      <div v-for="(step, index) in form.contenido.protocolo_traslado" :key="step.id" class="narrative-count-row row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                          <label class="form-label">Paso</label>
                          <input v-model.trim="step.titulo" type="text" class="form-control" placeholder="Ej. Evaluacion inicial">
                        </div>
                        <div class="col-12 col-xl-7">
                          <label class="form-label">Detalle</label>
                          <textarea v-model.trim="step.detalle" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12 col-xl-1 d-flex justify-content-xl-end">
                          <button type="button" class="attendance-row__remove" @click="removeAnalysisProtocolStep(index)">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </section>

                  <section class="narrative-section">
                    <div class="section-headline">
                      <h5>Centros de salud cercanos</h5>
                      <button type="button" class="btn btn-outline-primary btn-sm" @click="addAnalysisCenterRow()">
                        <i class="fa-solid fa-plus me-2"></i>Agregar centro
                      </button>
                    </div>

                    <div class="narrative-count-list">
                      <div v-for="(center, index) in form.contenido.centros_salud" :key="center.id" class="narrative-count-row row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                          <label class="form-label">Centro</label>
                          <input v-model.trim="center.nombre" type="text" class="form-control" placeholder="Ej. Hospital de Curico">
                        </div>
                        <div class="col-12 col-xl-7">
                          <label class="form-label">Detalle</label>
                          <textarea v-model.trim="center.detalle" class="form-control" rows="3" placeholder="Ubicacion, tiempo estimado o capacidad de respuesta."></textarea>
                        </div>
                        <div class="col-12 col-xl-1 d-flex justify-content-xl-end">
                          <button type="button" class="attendance-row__remove" @click="removeAnalysisCenterRow(index)">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </section>

                  <section class="narrative-section">
                    <h5>Conclusion</h5>
                    <textarea v-model.trim="form.contenido.conclusion" class="form-control" rows="5"></textarea>
                  </section>

                  <section class="narrative-section">
                    <h5>Observaciones complementarias</h5>
                    <textarea v-model.trim="form.contenido.observaciones_finales" class="form-control" rows="5" placeholder="Notas de seguimiento, ajustes o precisiones finales."></textarea>
                  </section>
                </div>
              </template>

              <div v-else class="content-fields mt-4">
"""
if marker not in text:
    raise SystemExit('No se encontro el marcador para insertar el formulario de analisis')
text = text.replace(marker, insert, 1)
text = text.replace("""              <button type="button" class="btn btn-danger" @click="handlePrimaryDocumentAction()" :disabled="isSaving || !canSubmit">
                <i :class="isNarrativeType ? 'fa-solid fa-file-pdf me-2' : 'fa-solid fa-file-circle-check me-2'"></i>{{ isSaving ? (isNarrativeType ? 'Exportando...' : 'Guardando...') : (isNarrativeType ? 'Exportar a PDF' : 'Guardar como final') }}
              </button>
""", """              <button type="button" class="btn btn-danger" @click="handlePrimaryDocumentAction()" :disabled="isSaving || !canSubmit">
                <i class="fa-solid fa-file-pdf me-2"></i>{{ isSaving ? 'Exportando...' : 'Exportar a PDF' }}
              </button>
""")
path.write_text(text, encoding='utf-8')
