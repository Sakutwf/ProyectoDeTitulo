<template>
  <div class="modal fade" id="newActividadModal" tabindex="-1" aria-labelledby="newActividadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="newActividadModalLabel">
            <i class="fa-solid fa-calendar-plus me-2"></i>Nueva Actividad
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="guardar">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="create-evento_id" class="form-label">Evento</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-list"></i></span>
                  <select class="form-select" id="create-evento_id" v-model="evento_id" required>
                    <option value="">Seleccione un evento</option>
                    <option v-for="evento in eventos" :key="evento.id" :value="evento.id">{{ evento.nombre }}</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <label for="create-tipo" class="form-label">Tipo de Actividad</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-tag"></i></span>
                  <input type="text" class="form-control" id="create-tipo" v-model="tipo" required placeholder="Tipo de actividad">
                </div>
              </div>
              <div class="col-md-6">
                <label for="create-N_beneficiarios" class="form-label">N° Beneficiarios</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-users"></i></span>
                  <input
                    type="number"
                    class="form-control"
                    id="create-N_beneficiarios"
                    v-model="N_beneficiarios"
                    min="0"
                    placeholder="Cantidad de beneficiarios"
                  >
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" @click="guardar" :disabled="!isFormValid">
            <i class="fa-solid fa-save me-1"></i>Guardar Actividad
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para seleccionar voluntarios tras crear actividad -->
  <div class="modal fade" id="selectVoluntariosModal" tabindex="-1" aria-labelledby="selectVoluntariosModalLabel" aria-hidden="true" ref="selectVoluntariosModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="selectVoluntariosModalLabel">
            <i class="fa-solid fa-user-plus me-2"></i>Asociar voluntarios a la actividad
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div v-if="voluntariosDisponibles.length === 0">
            No hay voluntarios disponibles.
          </div>
          <div v-else>
            <div class="mb-2">Seleccione voluntarios:</div>
            <ul class="list-group">
              <li v-for="user in voluntariosDisponibles" :key="user.id" class="list-group-item d-flex align-items-center">
                <input
                  class="form-check-input me-2"
                  type="checkbox"
                  :id="'voluntario-' + user.id"
                  :value="user.id"
                  v-model="voluntariosSeleccionados"
                >
                <label class="form-check-label" :for="'voluntario-' + user.id">
                  {{ user.nombre || user.name || user.email }}
                </label>
              </li>
            </ul>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="cerrarModalVoluntarios">Asociar en otro momento</button>
          <button type="button" class="btn btn-danger" @click="asociarVoluntarios">
            <i class="fa-solid fa-save me-1"></i>Asociar Voluntarios
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { Modal } from 'bootstrap';
import { show_alerta } from '../funciones';
import Swal from 'sweetalert2';
import * as bootstrap from 'bootstrap'

export default {
  name: 'ActividadCreateView',
  emits: ['actividad-created'],
  data() {
    return {
      evento_id: '',
      tipo: '',
      N_beneficiarios: '',
      eventos: [],
      url: 'http://localhost:8000/api/actividad',
      modalInstance: null,
      nuevaActividadId: null,
      voluntariosDisponibles: [],
      voluntariosSeleccionados: []
    }
  },
  computed: {
    isFormValid() {
      return (
        this.evento_id &&
        this.tipo.trim() !== ''
      );
    }
  },
  mounted() {
    this.modalInstance = new Modal(document.getElementById('newActividadModal'));
    this.getEventos();
  },
  methods: {
    show() {
      this.resetForm();
      this.modalInstance.show();
    },
    hide() {
      this.modalInstance.hide();
    },
    resetForm() {
      this.evento_id = '';
      this.tipo = '';
      this.N_beneficiarios = '';
    },
    async getEventos() {
      try {
        const res = await axios.get('http://localhost:8000/api/evento');
        this.eventos = Array.isArray(res.data) ? res.data : (res.data.data || []);
      } catch (e) {
        this.eventos = [];
      }
    },
    async guardar() {
      if (!this.isFormValid) {
        show_alerta('Complete todos los campos correctamente', 'warning');
        return;
      }
      try {
        const parametros = {
          evento_id: this.evento_id,
          tipo: this.tipo,
          ...(this.N_beneficiarios !== '' ? { N_beneficiarios: this.N_beneficiarios } : {})
        };
        const respuesta = await axios.post(this.url, parametros);
        if (respuesta.status === 201 || respuesta.status === 200) {
          // Guarda la ID de la nueva actividad
          this.nuevaActividadId = respuesta.data.id;
          this.hide();
          this.cargarVoluntariosYMostrarModal();
        } else {
          show_alerta('Error al crear la actividad', 'error');
        }
      } catch (error) {
        if (error.response && error.response.data) {
          const errores = error.response.data.errors || {};
          let listado = '';
          Object.keys(errores).forEach(key => {
            listado += errores[key][0] + '. ';
          });
          if (error.response.data.error) {
            listado += error.response.data.error;
          }
          show_alerta(listado || 'Error al crear la actividad', 'error');
        } else {
          show_alerta('Error al crear la actividad', 'error');
        }
      }
    },
    async cargarVoluntariosYMostrarModal() {
      this.voluntariosSeleccionados = [];
      try {
        const res = await axios.get('http://localhost:8000/api/users?role=voluntario');
        this.voluntariosDisponibles = Array.isArray(res.data)
          ? res.data
          : (res.data.data || []);
      } catch {
        this.voluntariosDisponibles = [];
      }
      // Mostrar el modal
      const modal = new Modal(this.$refs.selectVoluntariosModal);
      modal.show();
    },
    async asociarVoluntarios() {
      if (!this.nuevaActividadId) {
        show_alerta('No se encontró la actividad', 'error');
        return;
      }
      if (this.voluntariosSeleccionados.length === 0) {
        show_alerta('Seleccione al menos un voluntario', 'warning');
        return;
      }
      try {
        // Asocia todos los usuarios seleccionados a la actividad (en lote)
        await axios.put(`http://localhost:8000/api/actividad/${this.nuevaActividadId}`, {
          planilla: this.voluntariosSeleccionados
        });
        const modal = Modal.getInstance(this.$refs.selectVoluntariosModal);
        modal.hide();
        // Mostrar alerta de éxito con SweetAlert2
        Swal.fire({
          icon: 'success',
          title: 'Voluntarios asociados correctamente',
          showConfirmButton: true,
          confirmButtonText: 'Cerrar'
        });
        this.$emit('actividad-created');
        this.resetForm();
      } catch {
        show_alerta('No se pudo asociar voluntarios', 'error');
      }
    },
    cerrarModalVoluntarios() {
      const modal = Modal.getInstance(this.$refs.selectVoluntariosModal);
      modal.hide();
      this.$emit('actividad-created');
      this.resetForm();
    },
    async toggleVoluntario(user) {
      // Si ya está seleccionado, desasocia
      if (this.voluntariosSeleccionados.includes(user.id)) {
        // Elimina del array local
        this.voluntariosSeleccionados = this.voluntariosSeleccionados.filter(id => id !== user.id);
        // Elimina de la tabla pivote en backend
        if (this.nuevaActividadId) {
          await axios.post(`http://localhost:8000/api/actividad/${this.nuevaActividadId}/desasociar-voluntario`, {
            user_id: user.id
          });
        }
      } else {
        // Agrega al array local
        this.voluntariosSeleccionados.push(user.id);
        // Asocia en la tabla pivote en backend
        if (this.nuevaActividadId) {
          await axios.post(`http://localhost:8000/api/actividad/${this.nuevaActividadId}/asociar-voluntario`, {
            user_id: user.id
          });
        }
      }
    }
  }
}
</script>

<style scoped>
.modal-header {
  border-bottom: 0;
  background-color: #e01e1e !important;
  color: #fff !important;
}

.btn-danger {
  background-color: #e01e1e !important;
  border-color: #e01e1e !important;
  color: #fff !important;
}

.btn-danger:hover {
  background-color: #b71c1c !important;
  border-color: #b71c1c !important;
  color: #fff !important;
}

.modal-footer {
  border-top: 0;
}

.modal-content {
  border-radius: 8px;
  border: none;
}

.modal-body {
  padding: 20px 30px;
}

.btn-cruzroja {
  background-color: #e01e1e !important;
  border-color: #e01e1e !important;
  color: #fff !important;
  border-radius: 0.5rem !important;
  font-weight: bold;
  box-shadow: 0 2px 6px rgba(224,30,30,0.08);
  transition: background 0.2s, border 0.2s;
}
.btn-cruzroja:hover, .btn-cruzroja:focus {
  background-color: #b71c1c !important;
  border-color: #b71c1c !important;
  color: #fff !important;
}

.modal-cruzroja {
  border-radius: 10px;
  box-shadow: 0 4px 24px rgba(224,30,30,0.08);
  border: none;
}

.modal-header-cruzroja {
  background-color: #e01e1e !important;
  color: #fff !important;
  border-bottom: none;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
}

.modal-footer-cruzroja {
  border-top: none;
  background: #fff;
  border-bottom-left-radius: 10px;
  border-bottom-right-radius: 10px;
}

.btn-close-white {
  filter: invert(1) brightness(2);
}
</style>
