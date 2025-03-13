<template>
  <div class="map-container">
    <form @submit.prevent="updateCenter">
      <label for="lat">Latitud:</label>
      <input v-model="newCenter.lat" id="lat" type="number" step="any" required />
      
      <label for="lng">Longitud:</label>
      <input v-model="newCenter.lng" id="lng" type="number" step="any" required />
      
      <button type="submit">Actualizar Ubicación</button>
    </form>
    
    <GoogleMap
      :api-key="apiKey"
      :center="center"
      :zoom="15"
      style="width: 100%; height: 500px"
    >
      <Marker v-for="marker in markers" :key="marker.id" :position="marker.position" />
    </GoogleMap>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { GoogleMap, Marker } from 'vue3-google-map'

const apiKey = ref('')
const center = ref({ lat: 19.4326, lng: -99.1332 }) 
const newCenter = ref({ lat: 19.4326, lng: -99.1332 }) 
const markers = ref([
  { id: 1, position: { lat: 19.4326, lng: -99.1332 } }
])

const updateCenter = () => {
  center.value = { ...newCenter.value }
}

onMounted(async () => {
  try {
    const response = await fetch('/api/google-maps-key')
    const data = await response.json()
    apiKey.value = data.apiKey
  } catch (error) {
    console.error('Error al obtener la clave API:', error)
  }
})
</script>

<style scoped>
.map-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

form {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

label {
  font-weight: bold;
}

input {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

button {
  padding: 0.5rem;
  background-color: #4B5563;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button:hover {
  background-color: #374151;
}
</style>