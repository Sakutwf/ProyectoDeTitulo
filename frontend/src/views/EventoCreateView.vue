<template>
  <div class="modal fade" id="newEventoModal" tabindex="-1" aria-labelledby="newEventoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="newEventoModalLabel">
            <i class="fa-solid fa-calendar-plus me-2"></i>Nuevo Evento
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="guardar">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="create-nombre" class="form-label">Nombre</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                  <input
                    type="text"
                    class="form-control"
                    id="create-nombre"
                    v-model="nombre"
                    required
                    placeholder="Nombre del evento"
                  >
                </div>
              </div>
              <div class="col-md-3">
                <label for="create-fecha_inicio" class="form-label">Fecha Inicio</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-calendar-day"></i></span>
                  <input
                    type="datetime-local"
                    class="form-control"
                    id="create-fecha_inicio"
                    v-model="fecha_inicio"
                    required
                  >
                </div>
              </div>
              <div class="col-md-3">
                <label for="create-fecha_termino" class="form-label">Fecha Término</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-calendar-day"></i></span>
                  <input
                    type="datetime-local"
                    class="form-control"
                    id="create-fecha_termino"
                    v-model="fecha_termino"
                    required
                  >
                </div>
              </div>
              <div class="col-md-12">
                <label for="create-descripcion" class="form-label">Descripción</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-align-left"></i></span>
                  <textarea
                    class="form-control"
                    id="create-descripcion"
                    v-model="descripcion"
                    rows="2"
                    placeholder="Descripción del evento"
                  ></textarea>
                </div>
              </div>
              <div class="col-md-6">
                <label for="create-tipo" class="form-label">Tipo</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-tag"></i></span>
                  <input
                    type="text"
                    class="form-control"
                    id="create-tipo"
                    v-model="tipo"
                    required
                    placeholder="Tipo de evento"
                  >
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" @click="guardar" :disabled="!isFormValid">
            <i class="fa-solid fa-save me-1"></i>Guardar Evento
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
  name: 'EventoCreateView',
  data() {
    return {
      nombre: '',
      fecha_inicio: '',
      fecha_termino: '',
      descripcion: '',
      tipo: '',
      url: 'http://localhost:8000/api/evento',
      modalInstance: null
    }
  },
  computed: {
    isFormValid() {
      return (
        this.nombre.trim() !== '' &&
        this.fecha_inicio &&
        this.fecha_termino &&
        this.tipo.trim() !== ''
      );
    }
  },
  mounted() {
    this.modalInstance = new Modal(document.getElementById('newEventoModal'));
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
      this.nombre = '';
      this.fecha_inicio = '';
      this.fecha_termino = '';
      this.descripcion = '';
      this.tipo = '';
    },
    async guardar() {
      if (!this.isFormValid) {
        show_alerta('Complete todos los campos obligatorios', 'warning');
        return;
      }
      try {
        const parametros = {
          nombre: this.nombre,
          fecha_inicio: this.fecha_inicio,
          fecha_termino: this.fecha_termino,
          descripcion: this.descripcion,
          tipo: this.tipo
        };
        const respuesta = await axios.post(this.url, parametros);
        if (respuesta.status === 201 || respuesta.status === 200) {
          show_alerta('Evento creado', 'success');
          this.hide();
          this.$emit('evento-created');
          this.resetForm();
        } else {
          show_alerta('Error al crear el evento', 'error');
        }
      } catch (error) {
        if (error.response && error.response.data) {
          const errores = error.response.data.errors || {};
          let listado = '';
          Object.keys(errores).forEach(key => {
            listado += errores[key][0] + '. ';
          });
          show_alerta(listado || 'Error al crear el evento', 'error');
        } else {
          show_alerta('Error al crear el evento', 'error');
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
