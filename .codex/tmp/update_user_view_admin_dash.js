const fs = require('fs');
const path = 'frontend/src/views/UserView.vue';
let content = fs.readFileSync(path, 'utf8').replace(/\r\n/g, '\n');
function replaceExact(search, replacement, label) {
  if (!content.includes(search)) throw new Error(`No se encontro bloque: ${label}`);
  content = content.replace(search, replacement);
}
replaceExact(
`                    <div class="role-badges role-badges--mobile">
                      <span
                        v-for="label in cargoBadges(user)"
                        :key="'mobile-cargo-' + user.id + '-' + label"
                        class="badge role-badge-mobile cargo-badge-mobile"
                      >
                        {{ label }}
                      </span>
                    </div>
`,
`                    <div v-if="cargoBadges(user).length" class="role-badges role-badges--mobile">
                      <span
                        v-for="label in cargoBadges(user)"
                        :key="'mobile-cargo-' + user.id + '-' + label"
                        class="badge role-badge-mobile cargo-badge-mobile"
                      >
                        {{ label }}
                      </span>
                    </div>
                    <strong v-else>-</strong>
`,
'mobile cargo conditional'
);
replaceExact(
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
`                      <td data-label="Cargo">
                        <div v-if="cargoBadges(user).length" class="role-badges">
                          <span
                            v-for="label in cargoBadges(user)"
                            :key="user.id + '-cargo-' + label"
                            class="badge role-badge cargo-badge"
                          >
                            {{ label }}
                          </span>
                        </div>
                        <span v-else>-</span>
                      </td>
`,
'desktop cargo conditional'
);
replaceExact(
`    cargoBadges(user) {
      return [this.currentCargoLabel(user)]
    },
`,
`    cargoBadges(user) {
      const label = this.currentCargoLabel(user)
      return label === '-' ? [] : [label]
    },
`,
'cargoBadges method'
);
fs.writeFileSync(path, content.replace(/\n/g, '\r\n'));
