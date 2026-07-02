from pathlib import Path
path = Path(r'D:/Escritorio/Miscy/U/Proyecto de titulo/ProyectoDeTitulo/frontend/src/router/index.js')
text = path.read_text(encoding='utf-8')
if "import AnalisisContextoPdfView" not in text:
    text = text.replace("import DocumentosPdfView from '../views/DocumentosPdfView.vue'\n", "import DocumentosPdfView from '../views/DocumentosPdfView.vue'\nimport AnalisisContextoPdfView from '../views/AnalisisContextoPdfView.vue'\n")
route_marker = """  {
    path: '/documentos/:id/pdf',
    name: 'DocumentosPdfView',
    component: DocumentosPdfView,
    meta: { requiresAuth: true, roles: ['administrador', 'secretario-directiva'], experience: 'admin' }
  },
"""
new_route = route_marker + """  {
    path: '/documentos/:id/analisis-pdf',
    name: 'AnalisisContextoPdfView',
    component: AnalisisContextoPdfView,
    meta: { requiresAuth: true, roles: ['administrador', 'secretario-directiva'], experience: 'admin' }
  },
"""
text = text.replace(route_marker, new_route)
path.write_text(text, encoding='utf-8')
