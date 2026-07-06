const fs = require('fs');
const path = 'frontend/src/views/UserView.vue';
let content = fs.readFileSync(path, 'utf8').replace(/\r\n/g, '\n');
function replaceExact(search, replacement, label) {
  if (!content.includes(search)) {
    throw new Error(`No se encontro bloque: ${label}`);
  }
  content = content.replace(search, replacement);
}
replaceExact(
`                    <div class="role-badges role-badges--mobile">
                      <span
                        v-for="role in visibleRoles(user)"
                        :key="\`mobile-role-\${role.id}\`"
                        class="badge role-badge-mobile"
                      >
                        {{ role.nombre }}
                      </span>
                      <span v-if="!visibleRoles(user).length" class="text-muted small">Sin roles</span>
                    </div>
`,
`                    <div class="role-badges role-badges--mobile">
                      <span
                        v-for="label in permissionBadges(user)"
                        :key="\`mobile-role-\${user.id}-\${label}\`"
                        class="badge role-badge-mobile"
                      >
                        {{ label }}
                      </span>
                    </div>
`,
'mobile role badges'
);
replaceExact(
`                  <div class="profile-card__row">
                    <span class="profile-card__label"><i class="fa-solid fa-phone"></i> Telefono</span>
                    <strong>{{ user.voluntario?.celular || '-' }}</strong>
                  </div>
`,
`                  <div class="profile-card__row">
                    <span class="profile-card__label"><i class="fa-solid fa-briefcase"></i> Cargo</span>
                    <strong>{{ currentCargoLabel(user) }}</strong>
                  </div>
                  <div class="profile-card__row">
                    <span class="profile-card__label"><i class="fa-solid fa-phone"></i> Telefono</span>
                    <strong>{{ user.voluntario?.celular || '-' }}</strong>
                  </div>
`,
'mobile cargo row'
);
replaceExact(
`                      <th>Rol</th>
                      <th class="column-mobile-hidden">Telefono</th>
`,
`                      <th>Cargo</th>
                      <th>Rol</th>
                      <th class="column-mobile-hidden">Telefono</th>
`,
'header cargo'
);
replaceExact(
`                      <td data-label="RUT" class="column-mobile-hidden">{{ user.voluntario?.rut || '-' }}</td>
                      <td data-label="Rol">
                        <div class="role-badges">
                          <span
                            v-for="role in visibleRoles(user)"
                            :key="role.id"
                            class="badge role-badge"
                          >
                            {{ role.nombre }}
                          </span>
                          <span v-if="!visibleRoles(user).length" class="text-muted small">Sin roles</span>
                        </div>
                      </td>
`,
`                      <td data-label="RUT" class="column-mobile-hidden">{{ user.voluntario?.rut || '-' }}</td>
                      <td data-label="Cargo">{{ currentCargoLabel(user) }}</td>
                      <td data-label="Rol">
                        <div class="role-badges">
                          <span
                            v-for="label in permissionBadges(user)"
                            :key="\`\${user.id}-\${label}\`"
                            class="badge role-badge"
                          >
                            {{ label }}
                          </span>
                        </div>
                      </td>
`,
'row cargo/role'
);
replaceExact('colspan="7"', 'colspan="8"', 'colspan');
replaceExact(
`    visibleRoles(user) {
      const roles = user?.roles || []
      const hasAdditionalRole = roles.some((role) => role?.nombre?.toLowerCase() !== 'voluntario')

      if (!hasAdditionalRole) {
        return roles
      }

      return roles.filter((role) => role?.nombre?.toLowerCase() !== 'voluntario')
    }
`,
`    currentYearRecord(user) {
      const currentYear = new Date().getFullYear()
      return (user?.voluntario?.hojaVidaAnual || []).find((record) => Number(record?.anio) === currentYear) || null
    },
    currentCargoLabel(user) {
      if (!user?.voluntario) {
        return '-'
      }

      const record = this.currentYearRecord(user)
      if (!record || !record.cargo_clave) {
        return 'Voluntario'
      }

      return this.formatCargoLabel(record)
    },
    formatCargoLabel(record) {
      const nombre = (record?.cargo_nombre || '').trim()
      const grupo = (record?.cargo_grupo || '').trim()
      const direccion = (record?.cargo_direccion || '').trim()

      if (grupo === 'Gobernanza') {
        return nombre || 'Voluntario'
      }

      if (nombre && direccion) {
        return nombre + ' (' + direccion + ')'
      }

      return nombre || 'Voluntario'
    },
    permissionBadges(user) {
      return [this.hasAdminPermission(user) ? 'Administrador' : 'Usuario']
    },
    hasAdminPermission(user) {
      return (user?.roles || []).some((role) => role?.clave === 'administrador')
    }
`,
'methods role/cargo'
);
fs.writeFileSync(path, content.replace(/\n/g, '\r\n'));
