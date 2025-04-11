<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useEcomStore } from '@/stores/apps/eCommerce';
import UiParentCard from '@/components/shared/UiParentCard.vue';
import type { Header, Item } from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import { format } from 'date-fns';
import axios from 'axios';
import DataTable from 'datatables.net-vue3'
import DataTablesLib from 'datatables.net';
  
DataTable.use(DataTablesLib);


let items = [];
const store = useEcomStore();
onMounted(() => {
    console.log('Fetching data...');
    fetchData();
});

async function fetchData() {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/user/');
        console.log(response.data);
      } catch (error) {
        console.error('Error fetching data:', error);
      }
    };

const searchField = ref('name');
const searchValue = ref('');

const headers: Header[] = [
    { text: '#', value: 'id', sortable: true },
    { text: 'Rut', value: 'rut', sortable: true },
    { text: 'Correo', value: 'correo', sortable: true },
    { text: 'Telefono', value: 'telefono', sortable: true },
    { text: 'Rol', value: 'rol', sortable: true },
    { text: 'Nombre', value: 'nombre', sortable: true },
    { text: 'Fecha_nacimiento', value: 'fecha_nacimiento', sortable: true },
    { text: 'Fecha_ingreso', value: 'fecha_ingreso', sortable: true },
    { text: 'Grupo_sanguineo', value: 'grupo_sanguineo', sortable: true  },
    { text: 'Factor_rt', value: 'factor_rt', sortable: true }
];

const themeColor = ref('rgb(var(--v-theme-secondary))');

const itemsSelected = ref<Item[]>([]);
</script>

<template>
    <v-row>
        <v-col cols="12" md="12">
            <UiParentCard title="Product List">
                <v-row justify="space-between" class="align-center mb-3">
                    <v-col cols="12" md="3">
                        <v-text-field
                            type="text"
                            variant="outlined"
                            placeholder="Search Product"
                            v-model="searchValue"
                            density="compact"
                            hide-details
                            prepend-inner-icon="mdi-magnify"
                        />
                    </v-col>
                    <v-col cols="12" md="3">
                        <div class="d-flex gap-2 justify-end">
                            <v-btn icon variant="text">
                                <CopyIcon size="20" />
                            </v-btn>
                            <v-btn icon variant="text">
                                <PrinterIcon size="20" />
                            </v-btn>
                            <v-btn icon variant="text">
                                <FilterIcon size="20" />
                            </v-btn>
                        </div>
                    </v-col>
                </v-row>
                

                <DataTable
                    class="table table-striped table-bordered display"
                    ></DataTable>
                    


            </UiParentCard>
        </v-col>
    </v-row>
</template>
