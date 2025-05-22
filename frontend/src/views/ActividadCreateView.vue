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
                <label for="create-planilla" class="form-label">Planilla</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-file-alt"></i></span>
                  <input type="text" class="form-control" id="create-planilla" v-model="planilla" required placeholder="Nombre de la planilla">
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
                <label for="create-n_beneficiarios" class="form-label">N° Beneficiarios</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-users"></i></span>
                  <input type="number" class="form-control" id="create-n_beneficiarios" v-model="n_beneficiarios" min="0" required placeholder="Cantidad de beneficiarios">
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
</template>

<script>
import axios from 'axios';
import { Modal } from 'bootstrap';
import { show_alerta } from '../funciones';

export default {
  name: 'ActividadCreateView',
  data() {
    return {
      evento_id: '',
      planilla: '',
      tipo: '',
      n_beneficiarios: '',
      eventos: [],
      url: 'http://localhost:8000/api/actividad',
      modalInstance: null
    }
  },
  computed: {
    isFormValid() {
      return (
        this.evento_id &&
        this.planilla.trim() !== '' &&
        this.tipo.trim() !== '' &&
        this.n_beneficiarios !== '' &&
        !isNaN(this.n_beneficiarios) &&
        Number(this.n_beneficiarios) >= 0
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
      this.planilla = '';
      this.tipo = '';
      this.n_beneficiarios = '';
    },
    async getEventos() {
      try {
        const res = await axios.get('http://localhost:8000/api/evento');
        // Si la respuesta es paginada, usar res.data.data
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
          planilla: this.planilla,
          tipo: this.tipo,
          n_beneficiarios: this.n_beneficiarios
        };
        const respuesta = await axios.post(this.url, parametros);
        if (respuesta.status === 201 || respuesta.status === 200) {
          show_alerta('Actividad creada', 'success');
          this.hide();
          this.$emit('actividad-created');
          this.resetForm();
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
          show_alerta(listado || 'Error al crear la actividad', 'error');
        } else {
          show_alerta('Error al crear la actividad', 'error');
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
</style>
