<template>
  <div class="modal fade" id="editActividadModal" tabindex="-1" aria-labelledby="editActividadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="editActividadModalLabel">
            <i class="fa-solid fa-edit me-2"></i>Editar Actividad
          </h5>
          <button
            type="button"
            class="btn-close"
            style="filter: invert(1) brightness(2);"
            data-bs-dismiss="modal"
            aria-label="Close"
            @click="hide"
          ></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="guardar">
            <div class="row g-3">
              <!-- Campos de Evento -->
              <div class="col-md-6">
                <label class="form-label">Nombre Evento</label>
                <input type="text" class="form-control" v-model="evento_nombre" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" class="form-control" v-model="evento_fecha_inicio" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Fecha Término</label>
                <input type="date" class="form-control" v-model="evento_fecha_termino" required>
              </div>
              <div class="col-md-12">
                <label class="form-label">Descripción</label>
                <textarea class="form-control" v-model="evento_descripcion"></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Tipo Evento</label>
                <input type="text" class="form-control" v-model="evento_tipo">
              </div>
              <!-- Campos de Actividad -->
              <div class="col-md-6">
                <label class="form-label">Planilla</label>
                <input type="text" class="form-control" v-model="planilla">
              </div>
              <div class="col-md-6">
                <label class="form-label">Tipo Actividad</label>
                <input type="text" class="form-control" v-model="tipo">
              </div>
              <div class="col-md-6">
                <label class="form-label">N° Beneficiarios</label>
                <input type="number" class="form-control" v-model="N_beneficiarios">
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="hide">Cancelar</button>
          <button type="button" class="btn btn-danger" @click="guardar">
            <i class="fa-solid fa-save me-1"></i>Guardar Cambios
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { Modal } from 'bootstrap';

export default {
  name: 'ActividadEditView',
  props: {
    actividadId: {
      type: [Number, String, null],
      required: false,
      default: null
    }
  },
  data() {
    return {
      id: null,
      planilla: '',
      tipo: '',
      N_beneficiarios: 0,
      evento_id: null,
      evento_nombre: '',
      evento_fecha_inicio: '',
      evento_fecha_termino: '',
      evento_descripcion: '',
      evento_tipo: '',
      modalInstance: null
    }
  },
  watch: {
    actividadId: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.id = newVal;
          this.getActividad();
        }
      }
    }
  },
  mounted() {
    this.modalInstance = new Modal(document.getElementById('editActividadModal'));
  },
  methods: {
    show() {
      // Cargar datos antes de mostrar el modal
      this.getActividad();
    },
    hide() {
      this.modalInstance.hide();
    },
    async getActividad() {
      try {
        const res = await axios.get(`http://localhost:8000/api/actividad/${this.id}`);
        // Cargar datos de actividad
        this.planilla = res.data.planilla;
        this.tipo = res.data.tipo;
        this.N_beneficiarios = res.data.N_beneficiarios;
        this.evento_id = res.data.evento_id;
        // Cargar datos de evento relacionado
        if (res.data.evento) {
          this.evento_nombre = res.data.evento.nombre;
          this.evento_fecha_inicio = res.data.evento.fecha_inicio;
          this.evento_fecha_termino = res.data.evento.fecha_termino;
          this.evento_descripcion = res.data.evento.descripcion;
          this.evento_tipo = res.data.evento.tipo;
        }
        // Mostrar el modal después de cargar los datos
        this.modalInstance.show();
      } catch (e) {
        // Manejo de error
      }
    },
    async guardar() {
      try {
        // Actualizar evento primero
        await axios.put(`http://localhost:8000/api/evento/${this.evento_id}`, {
          nombre: this.evento_nombre,
          fecha_inicio: this.evento_fecha_inicio,
          fecha_termino: this.evento_fecha_termino,
          descripcion: this.evento_descripcion,
          tipo: this.evento_tipo
        });
        // Actualizar actividad
        await axios.put(`http://localhost:8000/api/actividad/${this.id}`, {
          planilla: this.planilla,
          tipo: this.tipo,
          N_beneficiarios: this.N_beneficiarios,
          evento_id: this.evento_id
        });
        this.hide();
        this.$emit('actividad-updated');
      } catch (e) {
        // Manejo de error
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
