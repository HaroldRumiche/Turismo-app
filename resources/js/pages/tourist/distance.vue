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

      <!-- Modo de transporte -->
      <div class="mb-4">
        <label class="block font-semibold mb-1">Modo de Transporte:</label>
        <div class="flex space-x-2">
          <button 
            @click="travelMode = 'DRIVING'" 
            class="px-3 py-1 rounded"
            :class="travelMode === 'DRIVING' ? 'bg-blue-600 text-white' : 'bg-gray-200'"
          >
            🚗 En Auto
          </button>
          <button 
            @click="travelMode = 'WALKING'" 
            class="px-3 py-1 rounded"
            :class="travelMode === 'WALKING' ? 'bg-blue-600 text-white' : 'bg-gray-200'"
          >
            🚶 Caminando
          </button>
          <button 
            @click="travelMode = 'TRANSIT'" 
            class="px-3 py-1 rounded"
            :class="travelMode === 'TRANSIT' ? 'bg-blue-600 text-white' : 'bg-gray-200'"
          >
            🚌 Transporte Público
          </button>
        </div>
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
        {{ isCalculating ? 'Calculando...' : 'Calcular Mejor Ruta' }}
      </button>

      <!-- Mapa -->
      <div id="map" class="w-full h-64 mb-4 border rounded"></div>

      <!-- Resultados -->
      <div v-if="routeData" class="mt-6 p-4 border rounded bg-gray-50">
        <h2 class="text-xl font-bold mb-2">Resultados del Recorrido</h2>
        <p><strong>Distancia:</strong> {{ routeData.distance }}</p>
        <p><strong>Tiempo Estimado:</strong> {{ routeData.duration }}</p>
        <div v-if="routeData.steps && routeData.steps.length > 0" class="mt-4">
          <h3 class="font-bold">Indicaciones paso a paso:</h3>
          <ol class="list-decimal pl-5 mt-2">
            <li v-for="(step, index) in routeData.steps" :key="index" class="mb-2" v-html="step"></li>
          </ol>
        </div>
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
  steps?: string[];
}

// --- Configuración ---
// La clave API se importa desde el archivo .env
const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;
const ccBase = { lat: -12.044397120486341, lng: -77.02966533388735 };
const ccBaseAddress = "Palacio de Gobierno, Lima, Perú";

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
const directionsService = ref<any>(null);
const directionsRenderer = ref<any>(null);
const travelMode = ref('DRIVING'); // Modo de transporte por defecto

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

// Inicializar el mapa y los servicios de direcciones
const initMap = () => {
  if (!googleMapsLoaded.value) return;
  
  try {
    // Crear el mapa centrado en el CC Base (punto de origen)
    map.value = new window.google.maps.Map(document.getElementById("map"), {
      center: ccBase,
      zoom: 14,
      mapTypeId: window.google.maps.MapTypeId.ROADMAP,
    });
    
    // Inicializar el servicio de direcciones
    directionsService.value = new window.google.maps.DirectionsService();
    directionsRenderer.value = new window.google.maps.DirectionsRenderer({
      map: map.value,
      suppressMarkers: false, // Mostrar marcadores de origen y destino
    });
    
    console.log("Mapa y servicios de direcciones inicializados correctamente");
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
  script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=initGoogleMaps`;
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
  
  // Limpiamos la ruta dibujada si hay una
  if (directionsRenderer.value) {
    directionsRenderer.value.setDirections({routes: []});
  }
};

// Calcular la mejor ruta usando el servicio de direcciones de Google Maps
const calculateRoute = () => {
  if (!selectedTourist.value) {
    alert("Selecciona un turista.");
    return;
  }
  
  if (!selectedTouristCoords.value) {
    alert("No se pudo obtener la ubicación del turista.");
    return;
  }
  
  if (!googleMapsLoaded.value || !directionsService.value) {
    alert("Google Maps todavía está cargando. Por favor, espera un momento.");
    return;
  }
  
  isCalculating.value = true;
  
  // Preparar los parámetros para la petición de ruta
  const request = {
    origin: ccBase,
    destination: selectedTouristCoords.value,
    travelMode: window.google.maps.TravelMode[travelMode.value],
    provideRouteAlternatives: true,
    optimizeWaypoints: true,
  };
  
  // Realizar la petición al servicio de direcciones
  directionsService.value.route(request, (result: any, status: string) => {
    isCalculating.value = false;
    
    if (status === "OK") {
      // Mostrar la ruta en el mapa
      directionsRenderer.value.setDirections(result);
      
      // Extraer los datos de la ruta (distancia y duración)
      const route = result.routes[0];
      const leg = route.legs[0];
      
      // Extraer las instrucciones paso a paso
      const steps = leg.steps.map((step: any) => {
        return step.instructions;
      });
      
      // Actualizar los resultados
      routeData.value = {
        distance: leg.distance.text,
        duration: leg.duration.text,
        steps: steps
      };
      
      // Ajustar el zoom para mostrar toda la ruta
      const bounds = new window.google.maps.LatLngBounds();
      bounds.extend(ccBase);
      bounds.extend(selectedTouristCoords.value);
      map.value.fitBounds(bounds);
    } else {
      console.error("Error al calcular ruta:", status);
      alert(`No se pudo calcular la ruta: ${status}`);
      
      // Si falla el cálculo de ruta, volver al método Haversine como respaldo
      calculateHaversineBackup();
    }
  });
};

// Método de respaldo: calcular distancia directa
const calculateHaversineBackup = () => {
  if (!selectedTouristCoords.value) return;
  
  try {
    // Calcular distancia usando la fórmula de Haversine
    const distance = calculateHaversineDistance(
      ccBase.lat, 
      ccBase.lng, 
      selectedTouristCoords.value.lat, 
      selectedTouristCoords.value.lng
    );
    
    // Estimar tiempo en base a la distancia
    const time = estimateWalkingTime(distance);
    
    // Actualizar los resultados
    routeData.value = {
      distance: formatDistance(distance),
      duration: time + " (estimación lineal)",
    };
    
    // Mostrar una línea directa en el mapa como alternativa
    const path = new window.google.maps.Polyline({
      path: [ccBase, selectedTouristCoords.value],
      geodesic: true,
      strokeColor: "#FF0000",
      strokeOpacity: 1.0,
      strokeWeight: 2,
      map: map.value,
    });
    
    // Ajustar el zoom
    const bounds = new window.google.maps.LatLngBounds();
    bounds.extend(ccBase);
    bounds.extend(selectedTouristCoords.value);
    map.value.fitBounds(bounds);
  } catch (error) {
    console.error("Error en cálculo de respaldo:", error);
  }
};

// Calcular distancia directa entre dos puntos en metros (fórmula Haversine)
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

// Observar cuando Google Maps se carga para actualizar los datos
watch(googleMapsLoaded, (isLoaded) => {
  if (isLoaded && selectedTourist.value) {
    updateSelectedTouristCoords();
  }
});

// Observar cambios en el modo de transporte
watch(travelMode, () => {
  // Si hay un turista seleccionado y hay una ruta calculada previamente, recalcular
  if (selectedTourist.value && routeData.value) {
    calculateRoute();
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
  max-width: 900px;
  margin: 0 auto;
  padding: 1rem;
}

#map {
  min-height: 400px;
  border-radius: 0.375rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
</style>