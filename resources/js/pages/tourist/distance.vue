<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';

interface Tourist {
  id: number;
  nombre: string;
  direccion: string;
  telefono?: string;
  coordenadas: { lat: number; lng: number } | null;
}

interface RouteData {
  distance: string;
  duration: string;
  mode: string;
  polyline: string;
}

// --- Configuración ---
const apiKey = "TU_API_KEY"; // Reemplaza con tu clave real
const ccBase = { lat: -11.849538799381891, lng: -77.14742280000002 };
const ccBaseAddress = "Centro Comercial Base"; // Cambia a la dirección real

// --- Estado ---
const tourists = ref<Tourist[]>([]);
const selectedTouristId = ref<number | ''>('');
const selectedTourist = computed(() =>
  tourists.value.find(t => t.id === selectedTouristId.value) || null
);
const selectedTouristCoords = ref<{ lat: number; lng: number } | null>(null);
const transportMode = ref("walking");
const routeData = ref<RouteData | null>(null);

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
  }
};

// Actualizado para usar coordenadas guardadas si existen
const updateSelectedTouristCoords = async () => {
  if (!selectedTourist.value) return;
  
  // Si el turista ya tiene coordenadas guardadas en el CRUD, usarlas directamente
  if (selectedTourist.value.coordenadas) {
    selectedTouristCoords.value = selectedTourist.value.coordenadas;
    return;
  }
  
  // Si no tiene coordenadas guardadas, intentar geocodificar la dirección
  try {
    const res = await axios.get('https://maps.googleapis.com/maps/api/geocode/json', {
      params: {
        address: selectedTourist.value.direccion,
        key: apiKey,
        region: 'pe'
      }
    });
    if (res.data.results && res.data.results.length) {
      const loc = res.data.results[0].geometry.location;
      selectedTouristCoords.value = { lat: loc.lat, lng: loc.lng };
    } else {
      selectedTouristCoords.value = null;
    }
  } catch (error) {
    console.error("Error al geocodificar dirección del turista:", error);
    selectedTouristCoords.value = null;
  }
};

// Actualizar coordenadas cuando cambia el turista seleccionado
const onTouristChange = async () => {
  await updateSelectedTouristCoords();
};

const calculateRoute = async () => {
  if (!selectedTourist.value) {
    alert("Selecciona un turista.");
    return;
  }
  
  // Asegurarse de tener las coordenadas actualizadas
  await updateSelectedTouristCoords();
  
  if (!selectedTouristCoords.value) {
    alert("No se pudo obtener la ubicación del turista.");
    return;
  }

  try {
    const origin = `${ccBase.lat},${ccBase.lng}`;
    const destination = `${selectedTouristCoords.value.lat},${selectedTouristCoords.value.lng}`;

    const directionsRes = await axios.get("https://maps.googleapis.com/maps/api/directions/json", {
      params: {
        origin: origin,
        destination: destination,
        mode: transportMode.value,
        key: apiKey,
      }
    });

    if (directionsRes.data.routes && directionsRes.data.routes.length > 0) {
      const route = directionsRes.data.routes[0];
      const leg = route.legs[0];

      routeData.value = {
        distance: leg.distance.text,
        duration: leg.duration.text,
        mode: transportMode.value,
        polyline: route.overview_polyline.points
      };

      nextTick(() => {
        renderRouteOnMap();
      });
    } else {
      alert("No se pudo obtener la ruta.");
    }
  } catch (error) {
    console.error("Error al calcular la ruta:", error);
  }
};

const renderRouteOnMap = () => {
  if (!routeData.value || !selectedTouristCoords.value) return;
  const mapElement = document.getElementById("routeMap");
  if (!mapElement) return;

  const map = new google.maps.Map(mapElement, {
    center: ccBase,
    zoom: 14
  });

  const directionsService = new google.maps.DirectionsService();
  const directionsRenderer = new google.maps.DirectionsRenderer({ map: map });

  directionsService.route(
    {
      origin: ccBase,
      destination: selectedTouristCoords.value,
      travelMode: transportMode.value.toUpperCase() as google.maps.TravelMode,
    },
    (result, status) => {
      if (status === "OK" && result) {
        directionsRenderer.setDirections(result);
      } else {
        console.error("Error al renderizar la ruta:", status);
      }
    }
  );
};

onMounted(() => {
  fetchTourists();
});
</script>

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

      <!-- Selección de modo de transporte -->
      <div class="mb-4">
        <label class="block font-semibold mb-1">Modo de Transporte:</label>
        <select v-model="transportMode" class="w-full p-2 border rounded">
          <option value="walking">Caminar</option>
          <option value="driving">Auto</option>
          <option value="transit">Transporte Público</option>
        </select>
      </div>

      <!-- Botón para calcular ruta -->
      <button @click="calculateRoute" class="bg-green-600 text-white px-4 py-2 rounded">
        Calcular Ruta
      </button>

      <!-- Resultados -->
      <div v-if="routeData" class="mt-6 p-4 border rounded bg-gray-50">
        <h2 class="text-xl font-bold mb-2">Resultados del Recorrido</h2>
        <p><strong>Distancia:</strong> {{ routeData.distance }}</p>
        <p><strong>Tiempo Estimado:</strong> {{ routeData.duration }}</p>
        <p><strong>Modo:</strong> {{ routeData.mode }}</p>
      </div>

      <!-- Mapa con la ruta detallada -->
      <div id="routeMap" class="mt-6 h-96 rounded-lg border"></div>
    </div>
  </AppLayout>
</template>

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
.distance-container input,
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
}
#routeMap {
  width: 100%;
  height: 400px;
  margin-top: 1rem;
}
</style>