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
        class="bg-green-600 text-white px-4 py-2 rounded mb-4"
        :disabled="!selectedTouristId || isCalculating"
      >
        {{ isCalculating ? 'Calculando...' : 'Calcular Ruta' }}
      </button>

      <!-- Mapa -->
      <div id="map" class="w-full h-64 mb-4 border rounded"></div>

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
    initGoogleMaps: () => void;
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
// Usar variable de entorno para la API key
const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY || "AIzaSyAJzGS5DYAIYPFC7MBTQ40gqC7iOZ2vmGg";
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
const map = ref<any>(null);
const originMarker = ref<any>(null);
const destinationMarker = ref<any>(null);

// --- Breadcrumbs ---
const breadcrumbs = [
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

// Inicializar el mapa
const initMap = () => {
  if (!googleMapsLoaded.value) return;
  
  try {
    // Crear el mapa centrado en el CC Base (punto de origen)
    map.value = new window.google.maps.Map(document.getElementById("map"), {
      center: ccBase,
      zoom: 14,
      mapTypeId: window.google.maps.MapTypeId.ROADMAP,
    });
    
    // Crear un marcador para el CC Base
    originMarker.value = new window.google.maps.Marker({
      position: ccBase,
      map: map.value,
      title: ccBaseAddress,
      icon: {
        url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png",
      },
    });
    
    console.log("Mapa inicializado correctamente");
  } catch (error) {
    console.error("Error al inicializar el mapa:", error);
  }
};

// Cargar Google Maps API (con todas las bibliotecas necesarias)
const loadGoogleMapsScript = () => {
  if (window.google && window.google.maps) {
    googleMapsLoaded.value = true;
    initMap();
    return;
  }
  
  const script = document.createElement('script');
  script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&callback=initGoogleMaps`;
  script.async = true;
  script.defer = true;
  
  document.head.appendChild(script);
};

// Actualiza las coordenadas del turista seleccionado
const updateSelectedTouristCoords = async () => {
  if (!selectedTourist.value) return;
  
  // Si el turista ya tiene coordenadas guardadas, usarlas
  if (selectedTourist.value.coordenadas) {
    selectedTouristCoords.value = selectedTourist.value.coordenadas;
    updateMapWithDestination();
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
          
          // Actualizar el mapa con el destino
          updateMapWithDestination();
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

// Añadir marcador del destino y centrar el mapa
const updateMapWithDestination = () => {
  if (!map.value || !selectedTouristCoords.value) return;
  
  // Eliminar marcador anterior si existe
  if (destinationMarker.value) {
    destinationMarker.value.setMap(null);
  }
  
  // Crear un nuevo marcador para el destino
  destinationMarker.value = new window.google.maps.Marker({
    position: selectedTouristCoords.value,
    map: map.value,
    title: selectedTourist.value?.nombre || "Destino",
    icon: {
      url: "http://maps.google.com/mapfiles/ms/icons/red-dot.png",
    },
  });
  
  // Ajustar el mapa para mostrar origen y destino
  const bounds = new window.google.maps.LatLngBounds();
  bounds.extend(ccBase);
  bounds.extend(selectedTouristCoords.value);
  map.value.fitBounds(bounds);
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

// Dibujar línea entre puntos (alternativa a DirectionsService)
const drawLine = () => {
  if (!map.value || !selectedTouristCoords.value) return;
  
  // Crear una línea directa entre origen y destino
  const path = new window.google.maps.Polyline({
    path: [ccBase, selectedTouristCoords.value],
    geodesic: true,
    strokeColor: "#FF0000",
    strokeOpacity: 1.0,
    strokeWeight: 2,
    map: map.value,
  });
};

// Calcular distancia directa entre dos puntos en metros
const calculateHaversineDistance = (lat1: number, lon1: number, lat2: number, lon2: number) => {
  const R = 6371000; // Radio de la Tierra en metros
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a = 
    Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
    Math.sin(dLon/2) * Math.sin(dLon/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  const distance = R * c;
  return distance;
};

// Estimar tiempo de caminata basado en la distancia
const estimateWalkingTime = (distanceInMeters: number) => {
  // Suponemos una velocidad media de caminata de 5 km/h = 1.4 m/s
  const walkingSpeedMps = 1.4;
  const timeInSeconds = distanceInMeters / walkingSpeedMps;
  
  // Convertir segundos a formato legible
  if (timeInSeconds < 60) {
    return Math.round(timeInSeconds) + " segundos";
  } else if (timeInSeconds < 3600) {
    return Math.round(timeInSeconds / 60) + " minutos";
  } else {
    const hours = Math.floor(timeInSeconds / 3600);
    const minutes = Math.round((timeInSeconds % 3600) / 60);
    return hours + " horas " + minutes + " minutos";
  }
};

// Formato para la distancia
const formatDistance = (meters: number) => {
  if (meters < 1000) {
    return Math.round(meters) + " m";
  } else {
    return (meters / 1000).toFixed(2) + " km";
  }
};

// Calcular ruta utilizando método alternativo ya que hay problemas con DirectionsService
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
  
  try {
    // Dibujar línea directa entre puntos
    drawLine();
    
    // Calcular distancia usando la fórmula de Haversine
    const distanceInMeters = calculateHaversineDistance(
      ccBase.lat, 
      ccBase.lng, 
      selectedTouristCoords.value.lat, 
      selectedTouristCoords.value.lng
    );
    
    // Estimar tiempo de caminata
    const walkingTime = estimateWalkingTime(distanceInMeters);
    
    // Actualizar resultados
    routeData.value = {
      distance: formatDistance(distanceInMeters),
      duration: walkingTime
    };
    
    isCalculating.value = false;
  } catch (error) {
    console.error("Error al calcular ruta:", error);
    isCalculating.value = false;
    alert("Error al calcular la ruta. Por favor, intenta de nuevo.");
  }
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
  
  // Definir callback global para Google
  window.initGoogleMaps = () => {
    googleMapsLoaded.value = true;
    initMap();
  };

  // Cargar el script de Google Maps
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