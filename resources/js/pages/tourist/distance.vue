<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="distance-container">
      <h1 class="text-2xl font-bold mb-4 text-center">Calcular Recorrido</h1>

      <!-- Seleccionar turista -->
      <div class="mb-4">
        <label class="block font-semibold mb-1" for="touristSelect">Selecciona un Turista:</label>
        <select 
          id="touristSelect" 
          v-model="selectedTouristId" 
          @change="onTouristChange"
          class="w-full p-2 border rounded"
        >
          <option disabled value="">-- Selecciona un turista --</option>
          <option v-for="tourist in tourists" :key="tourist.id" :value="tourist.id">
            {{ tourist.nombre }}
          </option>
        </select>
      </div>

      <!-- Mostrar datos del turista seleccionado -->
      <div v-if="selectedTourist" class="mb-4 border p-2 rounded">
        <p><strong>Destino (registrado):</strong> {{ selectedTourist.direccion }}</p>
        <p v-if="selectedTourist.coordenadas">
          <strong>Coordenadas Destino (guardadas en CRUD):</strong> 
          {{ selectedTourist.coordenadas.lat }}, {{ selectedTourist.coordenadas.lng }}
        </p>
        <p v-else-if="selectedTouristCoords">
          <strong>Coordenadas Destino (geocodificadas):</strong> 
          {{ selectedTouristCoords.lat }}, {{ selectedTouristCoords.lng }}
        </p>
        <p v-else class="text-red-600">
          No se pudo obtener la ubicación. No hay coordenadas guardadas ni se pudo geocodificar la dirección.
        </p>
      </div>

      <!-- Datos de origen fijo (cc base) -->
      <div class="mb-4 border p-2 rounded">
        <p><strong>Origen (cc base):</strong> {{ ccBaseAddress }}</p>
        <p><strong>Coordenadas Origen:</strong> {{ ccBase.lat }}, {{ ccBase.lng }}</p>
      </div>

      <!-- Botón para calcular ruta -->
      <button 
        @click="calculateRoute" 
        class="bg-green-600 text-white px-4 py-2 rounded"
        :disabled="!selectedTouristId || isCalculating"
      >
        {{ isCalculating ? 'Calculando...' : 'Calcular Ruta' }}
      </button>

      <!-- Resultados -->
      <div v-if="routeData" class="mt-6 p-4 border rounded bg-gray-50">
        <h2 class="text-xl font-bold mb-2">Resultados del Recorrido</h2>
        <p><strong>Distancia:</strong> {{ routeData.distance }}</p>
        <p><strong>Tiempo Estimado:</strong> {{ routeData.duration }}</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

// Declara tipos de Google Maps para evitar errores de TypeScript
declare global {
  interface Window {
    google: any;
  }
}

interface Tourist {
  id: number;
  nombre: string;
  direccion: string;
  telefono?: string;
  // Si se registran, se guardarán; de lo contrario, será null
  coordenadas: { lat: number; lng: number } | null;
}

interface RouteData {
  distance: string;
  duration: string;
}

// --- Configuración ---
const apiKey = ""; // Tu API Key
const ccBase = { lat: -11.849538799381891, lng: -77.14742280000002 };
const ccBaseAddress = "Centro Comercial Base";

// --- Estado ---
const tourists = ref<Tourist[]>([]);
const selectedTouristId = ref<number | ''>('');
const selectedTourist = computed(() =>
  tourists.value.find(t => t.id === selectedTouristId.value) || null
);

// Para el destino, se usará la dirección registrada
const selectedTouristCoords = ref<{ lat: number; lng: number } | null>(null);
const routeData = ref<RouteData | null>(null);
const googleMapsLoaded = ref(false);
const isCalculating = ref(false);

// --- Breadcrumbs ---
const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Calcular Recorrido', href: '/tourist/distance' }
];

// --- Funciones ---
const fetchTourists = async () => {
  try {
    const response = await axios.get('/tourists');
    tourists.value = response.data.data || response.data;
  } catch (error) {
    console.error("Error al obtener turistas:", error);
    alert("Error al cargar la lista de turistas. Por favor, recarga la página.");
  }
};

// Cargar Google Maps API (solo para geocodificación y cálculo de distancia)
const loadGoogleMapsScript = () => {
  if (window.google && window.google.maps) {
    googleMapsLoaded.value = true;
    return;
  }
  
  const script = document.createElement('script');
  // Necesitamos la librería de rutas para usar DistanceMatrixService
  script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places,routes&loading=async`;
  script.async = true;
  script.defer = true;
  script.onload = () => {
    googleMapsLoaded.value = true;
    console.log("Google Maps API cargada correctamente");
  };
  script.onerror = () => {
    console.error("Error al cargar Google Maps API");
  };
  document.head.appendChild(script);
};

// Actualiza las coordenadas del turista seleccionado
const updateSelectedTouristCoords = async () => {
  if (!selectedTourist.value) return;
  
  // Si el turista ya tiene coordenadas guardadas, usarlas
  if (selectedTourist.value.coordenadas) {
    selectedTouristCoords.value = selectedTourist.value.coordenadas;
    return;
  }
  
  // Esperamos a que Google Maps se cargue
  if (!googleMapsLoaded.value) {
    console.log("Esperando a que Google Maps se cargue...");
    return;
  }
  
  // Usar el geocoder del cliente JS
  const geocoder = new window.google.maps.Geocoder();
  
  try {
    geocoder.geocode(
      { 
        address: selectedTourist.value.direccion,
        region: 'pe'
      },
      (results: any, status: string) => {
        if (status === "OK" && results && results.length > 0) {
          const location = results[0].geometry.location;
          selectedTouristCoords.value = { 
            lat: location.lat(), 
            lng: location.lng() 
          };
        } else {
          console.error("No se pudieron geocodificar coordenadas:", status);
          selectedTouristCoords.value = null;
        }
      }
    );
  } catch (error) {
    console.error("Error al geocodificar:", error);
    selectedTouristCoords.value = null;
  }
};

const onTouristChange = async () => {
  // Esperamos a que Google Maps se cargue antes de intentar geocodificar
  if (!googleMapsLoaded.value) {
    setTimeout(() => {
      onTouristChange();
    }, 500);
    return;
  }
  
  await updateSelectedTouristCoords();
  // Limpiar resultados anteriores
  routeData.value = null;
};

// Calcular ruta utilizando DistanceMatrixService de Google Maps
const calculateRoute = () => {
  if (!selectedTourist.value) {
    alert("Selecciona un turista.");
    return;
  }
  
  if (!selectedTouristCoords.value) {
    alert("No se pudo obtener la ubicación del turista.");
    return;
  }
  
  if (!googleMapsLoaded.value) {
    alert("Google Maps todavía está cargando. Por favor, espera un momento.");
    return;
  }
  
  isCalculating.value = true;
  
  // Usar DistanceMatrixService para calcular distancia y tiempo
  const service = new window.google.maps.DistanceMatrixService();
  
  service.getDistanceMatrix(
    {
      origins: [ccBase],
      destinations: [selectedTouristCoords.value],
      travelMode: window.google.maps.TravelMode.WALKING,
      unitSystem: window.google.maps.UnitSystem.METRIC,
      avoidHighways: false,
      avoidTolls: false,
    },
    (response: any, status: string) => {
      isCalculating.value = false;
      
      if (status === "OK" && response) {
        if (
          response.rows && 
          response.rows[0] && 
          response.rows[0].elements && 
          response.rows[0].elements[0] && 
          response.rows[0].elements[0].status === "OK"
        ) {
          const element = response.rows[0].elements[0];
          
          routeData.value = {
            distance: element.distance.text,
            duration: element.duration.text
          };
        } else {
          console.error("No hay elementos en la respuesta o error de estado:", response);
          alert("No se pudo calcular la ruta. Comprueba que la dirección sea válida.");
        }
      } else {
        console.error("Error en DistanceMatrixService:", status);
        alert("Error al calcular la ruta. Por favor, intenta de nuevo.");
      }
    }
  );
};

// Observar cuando Google Maps se carga para actualizar los datos
watch(googleMapsLoaded, (isLoaded) => {
  if (isLoaded && selectedTourist.value) {
    updateSelectedTouristCoords();
  }
});

// Al montar el componente
onMounted(() => { 
  fetchTourists();
  loadGoogleMapsScript();
});
</script>

<style scoped>
.distance-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 1rem;
}
.distance-container h1 {
  text-align: center;
  margin-bottom: 1rem;
}
.distance-container label {
  margin-bottom: 0.5rem;
  display: block;
}
.distance-container select {
  margin-bottom: 1rem;
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.distance-container button {
  display: inline-block;
  margin-top: 0.5rem;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  width: 100%;
}
.distance-container button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}
</style>