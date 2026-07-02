from pathlib import Path
path = Path(r'D:/Escritorio/Miscy/U/Proyecto de titulo/ProyectoDeTitulo/frontend/src/views/DocumentosView.vue')
text = path.read_text(encoding='utf-8')
old = """  await router.push({
    name: 'DocumentosPdfView',
    params: { id: saved.id }
  })
"""
new = """  await router.push({
    name: isContextAnalysisType.value ? 'AnalisisContextoPdfView' : 'DocumentosPdfView',
    params: { id: saved.id }
  })
"""
if old not in text:
    raise SystemExit('No se encontro la navegacion PDF para actualizar')
path.write_text(text.replace(old, new), encoding='utf-8')
