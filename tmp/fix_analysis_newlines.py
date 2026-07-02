from pathlib import Path
path = Path(r'D:/Escritorio/Miscy/U/Proyecto de titulo/ProyectoDeTitulo/frontend/src/views/DocumentosView.vue')
text = path.read_text(encoding='utf-8')
text = text.replace(".map(([label, value]) => label + ':\n' + String(value).trim())", ".map(([label, value]) => label + ':\\n' + String(value).trim())")
text = text.replace(".join('\\n\\n')", ".join('\\\\n\\\\n')", 1)
text = text.replace(".join('\\n\\n')", ".join('\\\\n\\\\n')", 1)
path.write_text(text, encoding='utf-8')
