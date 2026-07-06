const fs = require('fs');
const path = 'frontend/src/views/UserView.vue';
let content = fs.readFileSync(path, 'utf8');
const pattern = /\.cargo-badge,\s*\n\.role-badges--mobile \.badge\.cargo-badge-mobile \{\s*\n\s*background: #dc3545;\s*\n\s*border-color: #dc3545;\s*\n\s*color: #fff;\s*\n\}/;
if (!pattern.test(content)) {
  throw new Error('No se encontro el bloque de estilos cargo');
}
content = content.replace(pattern, `.cargo-badge,\n.role-badges--mobile .badge.cargo-badge-mobile {\n  background: #fff;\n  border-color: #dc3545;\n  color: #dc3545;\n}`);
fs.writeFileSync(path, content);
