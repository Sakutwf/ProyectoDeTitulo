from pathlib import Path
path = Path(r'D:/Escritorio/Miscy/U/Proyecto de titulo/ProyectoDeTitulo/frontend/src/views/DocumentosView.vue')
text = path.read_text(encoding='utf-8')
text = text.replace(".join('\n\n')", ".join('\\n\\n')")
path.write_text(text, encoding='utf-8')
