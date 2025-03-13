<template>
  <v-dialog v-model="localModelValue" max-width="500px">
    <v-card>
      <v-card-title>
        {{ tourist?.id ? 'Editar Turista' : 'Agregar Turista' }}
      </v-card-title>
      <form :action="action" method="POST" @submit="onSubmit">
        <input type="hidden" name="_token" :value="csrfToken">
        <input v-if="method === 'PUT'" type="hidden" name="_method" value="PUT">
        
        <v-card-text>
          <v-text-field 
            v-model="form.nombre" 
            name="nombre" 
            label="Nombre" 
            outlined
            :error-messages="errors.nombre"
          ></v-text-field>
          
          <v-text-field 
            v-model="form.direccion" 
            name="direccion" 
            label="Dirección" 
            outlined
            :error-messages="errors.direccion"
          ></v-text-field>
          
          <v-text-field 
            v-model="form.telefono" 
            name="telefono" 
            label="Teléfono" 
            outlined
            :error-messages="errors.telefono"
          ></v-text-field>
        </v-card-text>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" @click="$emit('update:modelValue', false)">Cancelar</v-btn>
          <v-btn color="primary" type="submit">Guardar</v-btn>
        </v-card-actions>
      </form>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { defineProps, defineEmits, reactive, ref, watch } from 'vue';

const props = defineProps({
  modelValue: Boolean,
  tourist: Object,
  action: {
    type: String,
    default: '/tourists'
  },
  method: {
    type: String,
    default: 'POST'
  }
});

const emit = defineEmits(['update:modelValue']);

// Obtener CSRF token del meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const form = reactive({
  nombre: '',
  direccion: '',
  telefono: ''
});

const errors = reactive({
  nombre: '',
  direccion: '',
  telefono: ''
});

const localModelValue = ref(props.modelValue);

watch(
  () => props.modelValue,
  (newVal) => {
    localModelValue.value = newVal;
  }
);

watch(
  () => localModelValue.value,
  (newVal) => {
    emit('update:modelValue', newVal);
  }
);

watch(
  () => props.tourist,
  (newVal) => {
    if (newVal) {
      form.nombre = newVal.nombre || '';
      form.direccion = newVal.direccion || '';
      form.telefono = newVal.telefono || '';
    } else {
      form.nombre = '';
      form.direccion = '';
      form.telefono = '';
    }
  },
  { immediate: true }
);

const onSubmit = () => {
  // El formulario se enviará directamente al backend de Laravel
  // Sin necesidad de Ajax
};
</script>