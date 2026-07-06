const fs = require('fs');

function load(path) {
  return fs.readFileSync(path, 'utf8').replace(/\r\n/g, '\n');
}

function save(path, content) {
  fs.writeFileSync(path, content.replace(/\n/g, '\r\n'));
}

function replaceExact(content, search, replacement, label) {
  if (!content.includes(search)) {
    throw new Error(`No se encontro bloque: ${label}`);
  }
  return content.replace(search, replacement);
}

function replaceRegex(content, regex, replacement, label) {
  if (!regex.test(content)) {
    throw new Error(`No se encontro patron: ${label}`);
  }
  return content.replace(regex, replacement);
}

let create = load('frontend/src/views/UserCreateView.vue');
create = replaceRegex(
  create,
  /\n\s*<div class="col-12">\s*<label class="form-check border rounded px-3 py-3 mb-0 d-flex align-items-start gap-2" for="create-has-cargo">[\s\S]*?<\/template>\s*\n\s*<div class="col-12">\s*<div class="volunteer-section-title">Datos del voluntario<\/div>/,
  '\n                <div class="col-12">\n                  <div class="volunteer-section-title">Datos del voluntario</div>',
  'bloque cargo create template'
);
create = replaceRegex(create, /const CARGO_OPTIONS = \[[\s\S]*?\]\n\n/, '', 'const cargo options');
create = replaceExact(create, "      selectedCargoKey: '',\n      hasCargo: false,\n", '', 'data cargo create');
create = replaceExact(
  create,
  "      volunteerDefaultPassword: VOLUNTEER_DEFAULT_PASSWORD,\n      cargoOptions: CARGO_OPTIONS,\n      escolaridadOptions: ESCOLARIDAD_OPTIONS,\n",
  "      volunteerDefaultPassword: VOLUNTEER_DEFAULT_PASSWORD,\n      escolaridadOptions: ESCOLARIDAD_OPTIONS,\n",
  'cargo options data ref'
);
create = replaceRegex(create, /\n\s*selectedCargoDetails\(\) \{[\s\S]*?\n\s*\},/, '', 'computed cargo create');
create = replaceRegex(create, /,\n\s*hasCargo\(newValue\) \{[\s\S]*?\n\s*\}/, '', 'watch cargo create');
create = replaceRegex(create, /\n\s*cargoDescription\(cargo\) \{[\s\S]*?\n\s*\},/, '', 'method cargoDescription create');
create = replaceExact(create, "      this.hasCargo = false\n      this.selectedCargoKey = ''\n", '', 'clear voluntario cargo create');
create = replaceRegex(create, /\n\s*if \(this\.hasCargo && this\.selectedCargoDetails\) \{[\s\S]*?\n\s*\}/, '', 'buildFormData cargo create');
create = replaceExact(
  create,
  "        if (this.hasCargo && !this.selectedCargoKey) {\n          show_alerta('Debes seleccionar un cargo para el voluntario.', 'warning')\n          return false\n        }\n",
  '',
  'validate cargo create'
);
save('frontend/src/views/UserCreateView.vue', create);

let edit = load('frontend/src/views/UserEditView.vue');
edit = replaceExact(
  edit,
  `                <div class="col-md-6">\n                  <label for="edit-foto-perfil" class="form-label">Foto de perfil</label>\n                  <input\n                    id="edit-foto-perfil"\n                    type="file"\n                    class="form-control"\n                    accept=".jpg,.jpeg,.png,.webp"\n                    @change="onPhotoSelected"\n                  >\n                </div>\n`,
  `                <div class="col-md-6">\n                  <label for="edit-foto-perfil" class="form-label">Foto de perfil</label>\n                  <input\n                    id="edit-foto-perfil"\n                    type="file"\n                    class="form-control"\n                    accept=".jpg,.jpeg,.png,.webp"\n                    @change="onPhotoSelected"\n                  >\n                </div>\n\n                <div class="col-md-6">\n                  <label for="edit-permission-level" class="form-label">Rol</label>\n                  <select id="edit-permission-level" v-model="permissionLevel" class="form-select">\n                    <option value="usuario">Usuario</option>\n                    <option value="administrador">Administrador</option>\n                  </select>\n                </div>\n`,
  'insert permission select edit'
);
edit = replaceExact(edit, "      foto_perfil: null,\n      password: '',\n", "      foto_perfil: null,\n      permissionLevel: 'usuario',\n      password: '',\n", 'data permission level');
edit = replaceExact(
  edit,
  "      this.username = user.username || ''\n      this.selectedRoles = (user.roles || []).map((role) => role.id)\n",
  "      this.username = user.username || ''\n      this.selectedRoles = (user.roles || []).map((role) => role.id)\n      this.permissionLevel = (user.roles || []).some((role) => role?.clave === 'administrador') ? 'administrador' : 'usuario'\n",
  'load permission level'
);
edit = replaceExact(
  edit,
  `    appendIfFilled(formData, key, value) {\n      if (value !== null && value !== undefined && String(value).trim() !== '') {\n        formData.append(key, String(value).trim())\n      }\n    },\n`,
  `    appendIfFilled(formData, key, value) {\n      if (value !== null && value !== undefined && String(value).trim() !== '') {\n        formData.append(key, String(value).trim())\n      }\n    },\n    getRoleIdByKey(roleKey) {\n      return this.rolesOptions.find((role) => role.clave === roleKey)?.id || null\n    },\n`,
  'method getRoleIdByKey edit'
);
edit = replaceExact(
  edit,
  "      this.selectedRoles.forEach((roleId) => formData.append('roles[]', roleId))\n",
  "      const roleIds = this.esVoluntario\n        ? [this.getRoleIdByKey('voluntario'), this.permissionLevel === 'administrador' ? this.getRoleIdByKey('administrador') : null]\n        : [...this.selectedRoles]\n\n      ;[...new Set(roleIds.filter(Boolean))].forEach((roleId) => formData.append('roles[]', roleId))\n",
  'build role ids edit'
);
edit = replaceExact(
  edit,
  `      if (!this.selectedRoles.length) {\n        show_alerta('Debes seleccionar al menos un rol.', 'warning')\n        return false\n      }\n\n      if (!this.esVoluntario && !this.username.trim()) {\n`,
  `      if (!this.esVoluntario && !this.selectedRoles.length) {\n        show_alerta('Debes seleccionar al menos un rol.', 'warning')\n        return false\n      }\n\n      if (this.esVoluntario && !this.getRoleIdByKey('voluntario')) {\n        show_alerta('No se encontro el rol base de voluntario.', 'error')\n        return false\n      }\n\n      if (!this.esVoluntario && !this.username.trim()) {\n`,
  'validate roles edit'
);
save('frontend/src/views/UserEditView.vue', edit);

let list = load('frontend/src/views/UserView.vue');
list = replaceExact(
  list,
  `                    <div class="role-badges role-badges--mobile">\n                      <span\n                        v-for="role in visibleRoles(user)"\n                        :key="\`mobile-role-\${role.id}\`"\n                        class="badge role-badge-mobile"\n                      >\n                        {{ role.nombre }}\n                      </span>\n                      <span v-if="!visibleRoles(user).length" class="text-muted small">Sin roles</span>\n                    </div>\n`,
  `                    <div class="role-badges role-badges--mobile">\n                      <span\n                        v-for="label in permissionBadges(user)"\n                        :key="\`mobile-role-\${user.id}-\${label}\`"\n                        class="badge role-badge-mobile"\n                      >\n                        {{ label }}\n                      </span>\n                    </div>\n`,
  'mobile role badges list'
);
list = replaceExact(
  list,
  `                  <div class="profile-card__row">\n                    <span class="profile-card__label"><i class="fa-solid fa-phone"></i> Telefono</span>\n                    <strong>{{ user.voluntario?.celular || '-' }}</strong>\n                  </div>\n`,
  `                  <div class="profile-card__row">\n                    <span class="profile-card__label"><i class="fa-solid fa-briefcase"></i> Cargo</span>\n                    <strong>{{ currentCargoLabel(user) }}</strong>\n                  </div>\n                  <div class="profile-card__row">\n                    <span class="profile-card__label"><i class="fa-solid fa-phone"></i> Telefono</span>\n                    <strong>{{ user.voluntario?.celular || '-' }}</strong>\n                  </div>\n`,
  'mobile cargo row list'
);
list = replaceExact(list, "                      <th>Rol</th>\n                      <th class=\"column-mobile-hidden\">Telefono</th>\n", "                      <th>Cargo</th>\n                      <th>Rol</th>\n                      <th class=\"column-mobile-hidden\">Telefono</th>\n", 'table header cargo list');
list = replaceExact(
  list,
  `                      <td data-label="RUT" class="column-mobile-hidden">{{ user.voluntario?.rut || '-' }}</td>\n                      <td data-label="Rol">\n                        <div class="role-badges">\n                          <span\n                            v-for="role in visibleRoles(user)"\n                            :key="role.id"\n                            class="badge role-badge"\n                          >\n                            {{ role.nombre }}\n                          </span>\n                          <span v-if="!visibleRoles(user).length" class="text-muted small">Sin roles</span>\n                        </div>\n                      </td>\n`,
  `                      <td data-label="RUT" class="column-mobile-hidden">{{ user.voluntario?.rut || '-' }}</td>\n                      <td data-label="Cargo">{{ currentCargoLabel(user) }}</td>\n                      <td data-label="Rol">\n                        <div class="role-badges">\n                          <span\n                            v-for="label in permissionBadges(user)"\n                            :key="\`\${user.id}-\${label}\`"\n                            class="badge role-badge"\n                          >\n                            {{ label }}\n                          </span>\n                        </div>\n                      </td>\n`,
  'table cargo/role list'
);
list = replaceExact(list, 'colspan="7"', 'colspan="8"', 'colspan list');
list = replaceExact(
  list,
  `    visibleRoles(user) {\n      const roles = user?.roles || []\n      const hasAdditionalRole = roles.some((role) => role?.nombre?.toLowerCase() !== 'voluntario')\n\n      if (!hasAdditionalRole) {\n        return roles\n      }\n\n      return roles.filter((role) => role?.nombre?.toLowerCase() !== 'voluntario')\n    }\n`,
  `    currentYearRecord(user) {\n      const currentYear = new Date().getFullYear()\n      return (user?.voluntario?.hojaVidaAnual || []).find((record) => Number(record?.anio) === currentYear) || null\n    },\n    currentCargoLabel(user) {\n      if (!user?.voluntario) {\n        return '-'\n      }\n\n      const record = this.currentYearRecord(user)\n      if (!record || !record.cargo_clave) {\n        return 'Voluntario'\n      }\n\n      return this.formatCargoLabel(record)\n    },\n    formatCargoLabel(record) {\n      const nombre = (record?.cargo_nombre || '').trim()\n      const grupo = (record?.cargo_grupo || '').trim()\n      const direccion = (record?.cargo_direccion || '').trim()\n\n      if (grupo === 'Gobernanza') {\n        return nombre || 'Voluntario'\n      }\n\n      if (nombre && direccion) {\n        return \`${nombre} (${direccion})\`\n      }\n\n      return nombre || 'Voluntario'\n    },\n    permissionBadges(user) {\n      return [this.hasAdminPermission(user) ? 'Administrador' : 'Usuario']\n    },\n    hasAdminPermission(user) {\n      return (user?.roles || []).some((role) => role?.clave === 'administrador')\n    }\n`,
  'methods cargo/role list'
);
save('frontend/src/views/UserView.vue', list);
