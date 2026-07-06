const fs = require('fs');
const path = 'frontend/src/views/ActividadView.vue';
let content = fs.readFileSync(path, 'utf8').replace(/\r\n/g, '\n');
function replaceExact(search, replacement, label) {
  if (!content.includes(search)) throw new Error(`No se encontro bloque: ${label}`);
  content = content.replace(search, replacement);
}
replaceExact(
`                    <button
                      v-if="hasAssociatedDocuments(actividad)"
                      type="button"
                      class="btn btn-sm activity-action-button document-icon-button document-icon-button--card-action"
                      title="Ver documentos asociados"
                      aria-label="Ver documentos asociados"
                      @click="openAssociatedDocumentsModal(actividad)"
                    >
                      <img :src="documentsIcon" alt="" class="document-icon-button__icon activity-card__asset-icon--blue">
                    </button>
`,
`                    <button
                      type="button"
                      class="btn btn-sm activity-action-button document-icon-button document-icon-button--card-action"
                      :class="{ 'document-icon-button--disabled': !hasAssociatedDocuments(actividad) }"
                      :title="hasAssociatedDocuments(actividad) ? 'Ver documentos asociados' : 'Sin documentos asociados'"
                      :aria-label="hasAssociatedDocuments(actividad) ? 'Ver documentos asociados' : 'Sin documentos asociados'"
                      :disabled="!hasAssociatedDocuments(actividad)"
                      @click="openAssociatedDocumentsModal(actividad)"
                    >
                      <img :src="documentsIcon" alt="" class="document-icon-button__icon" :class="hasAssociatedDocuments(actividad) ? 'activity-card__asset-icon--blue' : 'activity-card__asset-icon--gray'">
                    </button>
`,
'card documents button'
);
replaceExact(
`                          <button
                            v-if="hasAssociatedDocuments(actividad)"
                            type="button"
                            class="btn btn-sm activity-action-button document-icon-button document-icon-button--table"
                            title="Ver documentos asociados"
                            aria-label="Ver documentos asociados"
                            @click="openAssociatedDocumentsModal(actividad)"
                          >
                            <img :src="documentsIcon" alt="" class="document-icon-button__icon activity-card__asset-icon--blue">
                          </button>
`,
`                          <button
                            type="button"
                            class="btn btn-sm activity-action-button document-icon-button document-icon-button--table"
                            :class="{ 'document-icon-button--disabled': !hasAssociatedDocuments(actividad) }"
                            :title="hasAssociatedDocuments(actividad) ? 'Ver documentos asociados' : 'Sin documentos asociados'"
                            :aria-label="hasAssociatedDocuments(actividad) ? 'Ver documentos asociados' : 'Sin documentos asociados'"
                            :disabled="!hasAssociatedDocuments(actividad)"
                            @click="openAssociatedDocumentsModal(actividad)"
                          >
                            <img :src="documentsIcon" alt="" class="document-icon-button__icon" :class="hasAssociatedDocuments(actividad) ? 'activity-card__asset-icon--blue' : 'activity-card__asset-icon--gray'">
                          </button>
`,
'table documents button'
);
replaceExact(
`.activity-card__asset-icon--blue {
  filter: brightness(0) saturate(100%) invert(18%) sepia(30%) saturate(1812%) hue-rotate(174deg) brightness(91%) contrast(93%);
}
`,
`.activity-card__asset-icon--blue {
  filter: brightness(0) saturate(100%) invert(18%) sepia(30%) saturate(1812%) hue-rotate(174deg) brightness(91%) contrast(93%);
}

.activity-card__asset-icon--gray {
  filter: grayscale(1) brightness(0.7);
  opacity: 0.75;
}
`,
'gray icon style'
);
replaceExact(
`.document-icon-button__icon {
  width: 1.45rem;
  height: 1.45rem;
  object-fit: contain;
}
`,
`.document-icon-button__icon {
  width: 1.45rem;
  height: 1.45rem;
  object-fit: contain;
}

.document-icon-button--disabled,
.document-icon-button:disabled {
  border-color: #c3ceda;
  color: #8a97a6;
  background: #f4f6f8;
  cursor: not-allowed;
}

.document-icon-button--disabled:hover,
.document-icon-button--disabled:focus,
.document-icon-button--disabled:active,
.document-icon-button:disabled:hover,
.document-icon-button:disabled:focus,
.document-icon-button:disabled:active {
  background: #f4f6f8;
  border-color: #c3ceda;
  color: #8a97a6;
}
`,
'disabled document button styles'
);
fs.writeFileSync(path, content.replace(/\n/g, '\r\n'));
