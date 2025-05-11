<template>
  <div class="modal fade" id="editEventoModal" tabindex="-1" aria-labelledby="editEventoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="editEventoModalLabel">
            <i class="fa-solid fa-calendar-days me-2"></i>Editar Evento
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" @click="hide"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="guardar">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="edit-nombre" class="form-label">Nombre</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                  <input type="text" class="form-control" id="edit-nombre" v-model="nombre" required>
                </div>
              </div>
              <div class="col-md-3">
                <label for="edit-fecha_inicio" class="form-label">Fecha Inicio</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-calendar-day"></i></span>
                  <input type="date" class="form-control" id="edit-fecha_inicio" v-model="fecha_inicio" required>
                </div>
              </div>
              <div class="col-md-3">
                <label for="edit-fecha_termino" class="form-label">Fecha Término</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-calendar-day"></i></span>
                  <input type="date" class="form-control" id="edit-fecha_termino" v-model="fecha_termino" required>
                </div>
              </div>
              <div class="col-md-12">
                <label for="edit-descripcion" class="form-label">Descripción</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-align-left"></i></span>
                  <textarea class="form-control" id="edit-descripcion" v-model="descripcion" rows="2"></textarea>
                </div>
              </div>
              <div class="col-md-6">
                <label for="edit-tipo" class="form-label">Tipo</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-tag"></i></span>
                  <input type="text" class="form-control" id="edit-tipo" v-model="tipo">
                </div>
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
import { show_alerta } from '../funciones';

export default {
  name: 'EventoEditView',
  props: {
    eventoId: {
      type: [Number, String, null],
      required: false,
      default: null
    }
  },
  data() {
    return {
      id: null,
      nombre: '',
      fecha_inicio: '',
      fecha_termino: '',
      descripcion: '',
      tipo: '',
      url: 'http://localhost:8000/api/evento/',
      modalInstance: null
    }
  },
  watch: {
    eventoId: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.id = newVal;
          this.getEvento();
        }
      }
    }
  },
  mounted() {
    this.modalInstance = new Modal(document.getElementById('editEventoModal'));
  },
  methods: {
    show() {
      this.modalInstance.show();
    },
    hide() {
      this.modalInstance.hide();
    },
    async getEvento() {
      try {
        const response = await axios.get(this.url + this.id);
        this.nombre = response.data.nombre;
        this.fecha_inicio = response.data.fecha_inicio;
        this.fecha_termino = response.data.fecha_termino;
        this.descripcion = response.data.descripcion;
        this.tipo = response.data.tipo;
      } catch (error) {
        show_alerta('Error al cargar los datos del evento', 'error');
      }
    },
    async guardar() {
      if (this.nombre.trim() === '') {
        show_alerta('Escribe el nombre', 'warning', 'nombre');
      } else if (this.fecha_inicio.trim() === '') {
        show_alerta('Escribe la fecha de inicio', 'warning', 'fecha_inicio');
      } else if (this.fecha_termino.trim() === '') {
        show_alerta('Escribe la fecha de término', 'warning', 'fecha_termino');
      } else if (this.tipo.trim() === '') {
        show_alerta('Escribe el tipo', 'warning', 'tipo');
      } else {
        try {
          const parametros = {
            nombre: this.nombre,
            fecha_inicio: this.fecha_inicio,
            fecha_termino: this.fecha_termino,
            descripcion: this.descripcion,
            tipo: this.tipo
          };
          const respuesta = await axios.put(this.url + this.id, parametros);
          if (respuesta.status === 200) {
            show_alerta('Evento actualizado', 'success');
            this.hide();
            this.$emit('evento-updated');
          } else {
            show_alerta('Error al actualizar evento', 'error');
          }
        } catch (error) {
          show_alerta('Error al actualizar evento', 'error');
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
