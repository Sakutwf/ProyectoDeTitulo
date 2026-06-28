<template>
  <div class="d-flex">
    <SidebarMenu />

    <div class="content-wrapper">
      <div class="content-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h3 class="m-0"><i class="fa-solid fa-folder-open me-2"></i>Documentos</h3>
            <p class="header-copy mb-0">Selecciona un tipo de documento y abre su formulario desde el boton de acceso.</p>
          </div>
          <span class="documents-chip">Modulo administrativo</span>
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
                <p class="mb-0">Selecciona una actividad para precargar la informacion disponible y completar el resto manualmente.</p>
              </div>
              <button type="button" class="btn btn-outline-secondary btn-sm" @click="closeForm">
                Cerrar
              </button>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-12 col-lg-6">
                <label class="form-label">Actividad base</label>
                <select v-model="selectedActividadId" class="form-select" @change="handleActividadChange">
                  <option value="">Selecciona una actividad</option>
                  <option v-for="actividad in actividades" :key="actividad.id" :value="String(actividad.id)">
                    {{ actividadLabel(actividad) }}
                  </option>
                </select>
                <small class="text-muted">Se usa para rellenar automaticamente objetivo, filial, participantes y evidencias.</small>
              </div>

              <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label">Estado</label>
                <select v-model="form.estado" class="form-select">
                  <option value="borrador">Borrador</option>
                  <option value="final">Final</option>
                </select>
              </div>

              <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label">Fecha del documento</label>
                <input v-model="form.fecha_documento" type="date" class="form-control">
              </div>

              <div class="col-12">
                <label class="form-label">Titulo</label>
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
                  <span class="context-card__label">Participantes</span>
                  <strong>{{ contexto.resumen?.total_participantes ?? 0 }}</strong>
                  <small>{{ formatHours(contexto.resumen?.total_horas_participantes) }} registradas</small>
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
                    <h5>Informacion precargada</h5>
                    <div class="prefill-panel__block">
                      <span class="prefill-panel__label">Objetivo</span>
                      <p>{{ contexto.actividad?.objetivo || 'Sin objetivo registrado.' }}</p>
                    </div>
                    <div class="prefill-panel__block">
                      <span class="prefill-panel__label">Lugar</span>
                      <p>{{ contexto.actividad?.lugar || 'Sin lugar registrado.' }}</p>
                    </div>
                    <div class="prefill-panel__block">
                      <span class="prefill-panel__label">Participantes registrados</span>
                      <ul v-if="contexto.participantes?.length" class="prefill-list">
                        <li v-for="participante in contexto.participantes" :key="participante.id">
                          {{ participante.nombre || 'Voluntario' }} · {{ formatHours(participante.horas_asistidas) }}
                        </li>
                      </ul>
                      <p v-else>No hay participantes asociados.</p>
                    </div>
                  </div>
                </div>

                <div class="col-12 col-xl-5">
                  <div class="prefill-panel">
                    <h5>Documentos guardados</h5>
                    <ul v-if="documentosGuardados.length" class="saved-documents">
                      <li v-for="documento in documentosGuardados" :key="documento.id">
                        <div>
                          <strong>{{ documento.titulo }}</strong>
                          <small>{{ formatSavedDocument(documento) }}</small>
                        </div>
                        <span class="saved-documents__state" :class="`state-${documento.estado || 'borrador'}`">
                          {{ documento.estado || 'borrador' }}
                        </span>
                      </li>
                    </ul>
                    <p v-else class="mb-0 text-muted">Todavia no hay documentos guardados para esta actividad.</p>
                  </div>
                </div>
              </div>

              <div class="content-fields mt-4">
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
              <button type="button" class="btn btn-danger" @click="saveDocument('final')" :disabled="isSaving || !canSubmit">
                <i class="fa-solid fa-file-circle-check me-2"></i>{{ isSaving ? 'Guardando...' : 'Guardar como final' }}
              </button>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import SidebarMenu from '../components/SidebarMenu.vue'
import { buildApiUrl } from '../config/api'

const documentTypes = [
  {
    key: 'analisis_contexto',
    title: 'Analisis de contexto',
    shortLabel: 'Analisis',
    formTitle: 'Formulario de analisis de contexto',
    placeholderTitle: 'Ej. Analisis de contexto - Operativo invierno 2026',
    description: 'Genera y organiza los documentos de analisis por filial y actividad.',
    icon: 'fa-solid fa-map',
    sections: [
      { key: 'resumen', label: 'Resumen', placeholder: 'Resume el contexto general de la actividad.' },
      { key: 'analisis_contextual', label: 'Analisis contextual', placeholder: 'Describe el entorno, condiciones y actores involucrados.' },
      { key: 'diagnostico', label: 'Diagnostico', placeholder: 'Expone el diagnostico detectado.' },
      { key: 'oportunidades', label: 'Oportunidades', placeholder: 'Senala oportunidades detectadas.' },
      { key: 'riesgos', label: 'Riesgos', placeholder: 'Detalla los riesgos o limitaciones.' },
      { key: 'desarrollo', label: 'Desarrollo', placeholder: 'Describe como se abordo el analisis.' },
      { key: 'resultados', label: 'Resultados', placeholder: 'Sintetiza los principales resultados.' },
      { key: 'conclusiones', label: 'Conclusiones', placeholder: 'Escribe las conclusiones del analisis.' },
      { key: 'recomendaciones', label: 'Recomendaciones', placeholder: 'Agrega recomendaciones para etapas posteriores.' },
      { key: 'observaciones', label: 'Observaciones', placeholder: 'Agrega observaciones complementarias.' }
    ]
  },
  {
    key: 'informe_narrativo',
    title: 'Informes narrativos',
    shortLabel: 'Informe',
    formTitle: 'Formulario de informe narrativo',
    placeholderTitle: 'Ej. Informe narrativo - Operativo invierno 2026',
    description: 'Centraliza la emision de informes narrativos vinculados a actividades.',
    icon: 'fa-solid fa-file-lines',
    sections: [
      { key: 'resumen', label: 'Resumen', placeholder: 'Resume la actividad y sus resultados principales.' },
      { key: 'introduccion', label: 'Introduccion', placeholder: 'Introduce el contexto general del informe.' },
      { key: 'metodologia', label: 'Metodologia', placeholder: 'Explica la metodologia utilizada.' },
      { key: 'desarrollo', label: 'Desarrollo', placeholder: 'Describe el desarrollo de la actividad.' },
      { key: 'participacion_comunitaria', label: 'Participacion comunitaria', placeholder: 'Detalla la participacion de la comunidad o voluntariado.' },
      { key: 'resultados', label: 'Resultados', placeholder: 'Describe los resultados obtenidos.' },
      { key: 'dificultades', label: 'Dificultades', placeholder: 'Registra dificultades o incidentes.' },
      { key: 'conclusiones', label: 'Conclusiones', placeholder: 'Escribe las conclusiones finales.' },
      { key: 'recomendaciones', label: 'Recomendaciones', placeholder: 'Anota recomendaciones para futuras acciones.' },
      { key: 'observaciones', label: 'Observaciones', placeholder: 'Agrega observaciones complementarias.' }
    ]
  }
]

const actividades = ref([])
const selectedType = ref('')
const selectedActividadId = ref('')
const loadingPrefill = ref(false)
const isSaving = ref(false)
const documentosGuardados = ref([])

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
const canSubmit = computed(() => Boolean(selectedType.value && selectedActividadId.value && form.titulo.trim()))

function todayAsInput() {
  return new Date().toISOString().slice(0, 10)
}

function emptyContent(type) {
  const meta = documentTypes.find((item) => item.key === type)
  return (meta?.sections || []).reduce((accumulator, section) => {
    accumulator[section.key] = ''
    return accumulator
  }, {})
}

function openForm(type) {
  selectedType.value = type
  form.titulo = ''
  form.estado = 'borrador'
  form.fecha_documento = todayAsInput()
  form.datos_contexto = null
  form.contenido = emptyContent(type)
  documentosGuardados.value = []

  if (selectedActividadId.value) {
    handleActividadChange()
  }
}

function closeForm() {
  selectedType.value = ''
  form.titulo = ''
  form.datos_contexto = null
  form.contenido = {}
  documentosGuardados.value = []
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
    return
  }

  await Promise.all([loadPrefill(), fetchSavedDocuments()])
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
    form.contenido = {
      ...emptyContent(selectedType.value),
      ...(response.data.contenido_inicial || {})
    }
  } catch (error) {
    form.datos_contexto = null
    form.contenido = emptyContent(selectedType.value)
    Swal.fire('Error', 'No se pudo precargar la informacion de la actividad.', 'error')
  } finally {
    loadingPrefill.value = false
  }
}

async function fetchSavedDocuments() {
  if (!selectedActividadId.value) {
    documentosGuardados.value = []
    return
  }

  try {
    const response = await axios.get(buildApiUrl(`actividad/${selectedActividadId.value}/documentos`))
    documentosGuardados.value = response.data || []
  } catch (error) {
    documentosGuardados.value = []
  }
}

async function saveDocument(targetState) {
  if (!canSubmit.value) {
    Swal.fire('Faltan datos', 'Selecciona una actividad y completa al menos el titulo del documento.', 'warning')
    return
  }

  isSaving.value = true

  try {
    await axios.post(buildApiUrl(`actividad/${selectedActividadId.value}/documentos`), {
      tipo_documento: selectedType.value,
      titulo: form.titulo.trim(),
      estado: targetState,
      fecha_documento: form.fecha_documento || null,
      datos_contexto: form.datos_contexto,
      contenido: form.contenido
    })

    form.estado = targetState
    await fetchSavedDocuments()

    Swal.fire('Guardado', 'El documento fue guardado correctamente.', 'success')
  } catch (error) {
    const message = error?.response?.data?.message || 'No se pudo guardar el documento.'
    Swal.fire('Error', message, 'error')
  } finally {
    isSaving.value = false
  }
}

function actividadLabel(actividad) {
  const dates = formatDateRange(actividad.fecha_inicio, actividad.fecha_termino)
  return `${actividad.nombre || 'Actividad'} · ${actividad.filial?.nombre || 'Sin filial'} · ${dates}`
}

function formatDate(value) {
  if (!value) return '-'
  const parsed = new Date(`${String(value).slice(0, 10)}T00:00:00`)
  if (Number.isNaN(parsed.getTime())) return String(value)
  return parsed.toLocaleDateString('es-CL')
}

function formatDateRange(start, end) {
  if (!start && !end) return '-'
  if (start && end) return `${formatDate(start)} - ${formatDate(end)}`
  return formatDate(start || end)
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

onMounted(() => {
  fetchActividades()
})
</script>

<style scoped>
.content-wrapper {
  flex: 1;
  background-color: #f5f7fa;
  min-height: 100vh;
}

.content-header {
  padding: 1rem 1.5rem;
  background-color: #fff;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 1.5rem;
}

.header-copy {
  color: #6e7f95;
  margin-top: 0.35rem;
}

.content {
  padding: 0 1.5rem 1.5rem;
}

.documents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.document-card {
  border: 1px solid #e3e9f1;
  border-radius: 20px;
  background: #fff;
  box-shadow: 0 14px 32px rgba(15, 47, 95, 0.08);
  padding: 1.35rem;
  display: grid;
  gap: 0.8rem;
}

.document-card.active {
  border-color: #ff4d5a;
  box-shadow: 0 18px 36px rgba(255, 77, 90, 0.16);
}

.document-card__icon {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #eef4fb;
  color: #173b70;
  font-size: 1.3rem;
}

.document-card h4 {
  margin: 0;
  color: #163a69;
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
  background: #eef4fb;
  color: #163a69;
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
  color: #163a69;
  margin: 0.6rem 0 0.35rem;
  font-size: 1.7rem;
  font-weight: 800;
}

.form-shell__header p {
  color: #6a7d94;
  max-width: 760px;
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
  color: #173b70;
  font-size: 1.02rem;
}

.context-card small {
  color: #617791;
}

.prefill-panel {
  height: 100%;
  background: #fff;
  border: 1px solid #e4ebf3;
  border-radius: 20px;
  padding: 1rem 1.05rem;
}

.prefill-panel h5 {
  color: #173b70;
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

.prefill-list,
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

.saved-documents li:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.saved-documents strong {
  display: block;
  color: #173b70;
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
  color: #173b70;
  font-weight: 700;
}

.content-field textarea {
  min-height: 132px;
  resize: vertical;
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
}

@media (max-width: 767.98px) {
  .content {
    padding: 0 1rem 1rem;
  }

  .content-header {
    padding: 1rem;
  }

  .form-shell__header h4 {
    font-size: 1.45rem;
  }

  .form-actions {
    justify-content: stretch;
  }

  .form-actions .btn {
    width: 100%;
  }
}
</style>
