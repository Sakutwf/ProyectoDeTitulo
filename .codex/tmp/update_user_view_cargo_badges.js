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
`                  <div class="profile-card__row">
                    <span class="profile-card__label"><i class="fa-solid fa-briefcase"></i> Cargo</span>
                    <strong>{{ currentCargoLabel(user) }}</strong>
                  </div>
`,
`                  <div class="profile-card__row">
                    <span class="profile-card__label"><i class="fa-solid fa-briefcase"></i> Cargo</span>
                    <div class="role-badges role-badges--mobile">
                      <span
                        v-for="label in cargoBadges(user)"
                        :key="'mobile-cargo-' + user.id + '-' + label"
                        class="badge role-badge-mobile cargo-badge-mobile"
                      >
                        {{ label }}
                      </span>
                    </div>
                  </div>
`,
'mobile cargo badge'
);
replaceExact(
`                      <td data-label="Cargo">{{ currentCargoLabel(user) }}</td>
`,
`                      <td data-label="Cargo">
                        <div class="role-badges">
                          <span
                            v-for="label in cargoBadges(user)"
                            :key="user.id + '-cargo-' + label"
                            class="badge role-badge cargo-badge"
                          >
                            {{ label }}
                          </span>
                        </div>
                      </td>
`,
'desktop cargo badge'
);
replaceExact(
`    currentCargoLabel(user) {
      if (!user?.voluntario) {
        return '-'
      }

      const record = this.currentYearRecord(user)
      if (!record || !record.cargo_clave) {
        return 'Voluntario'
      }

      return this.formatCargoLabel(record)
    },
`,
`    currentCargoLabel(user) {
      if (!user?.voluntario) {
        return '-'
      }

      const record = this.currentYearRecord(user)
      if (!record || !record.cargo_clave) {
        return 'Voluntario'
      }

      return this.formatCargoLabel(record)
    },
    cargoBadges(user) {
      return [this.currentCargoLabel(user)]
    },
`,
'cargoBadges method'
);
replaceExact(
`.role-badges .badge {
  text-align: left;
}
`,
`.role-badges .badge {
  text-align: left;
}

.cargo-badge,
.role-badges--mobile .badge.cargo-badge-mobile {
  background: #dc3545;
  border-color: #dc3545;
  color: #fff;
}
`,
'cargo badge styles'
);
fs.writeFileSync(path, content.replace(/\n/g, '\r\n'));
