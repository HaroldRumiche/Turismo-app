<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="distance-container max-w-3xl mx-auto">
      <h1 class="text-2xl font-bold mb-6 text-center">Calcular Recorrido</h1>

      <!-- Card principal -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <!-- Seleccionar turista -->
        <div class="mb-5">
          <label class="block font-semibold mb-2 text-gray-700" for="touristSelect">Selecciona un Turista:</label>
          <select 
            id="touristSelect" 
            v-model="selectedTouristId" 
            @change="onTouristChange"
            class="w-full p-3 border border-gray-300 rounded-md bg-gray-50 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200"
          >
            <option disabled value="">-- Selecciona un turista --</option>
            <option v-for="tourist in tourists" :key="tourist.id" :value="tourist.id">
              {{ tourist.nombre }}
            </option>
          </select>
        </div>

        <!-- Mostrar datos del turista seleccionado -->
        <div v-if="selectedTourist" class="mb-5 p-4 border border-gray-200 rounded-md bg-gray-50">
          <div class="flex items-center mb-2 text-blue-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <h3 class="font-semibold">Información del Destino</h3>
          </div>
          <p class="mb-1"><strong>Turista:</strong> {{ selectedTourist.nombre }}</p>
          <p class="mb-1"><strong>Destino:</strong> {{ selectedTourist.direccion }}</p>
          <div class="mt-2 pt-2 border-t border-gray-200">
            <p v-if="selectedTourist.coordenadas" class="text-green-600 font-medium flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Coordenadas registradas: {{ selectedTourist.coordenadas.lat.toFixed(6) }}, {{ selectedTourist.coordenadas.lng.toFixed(6) }}
            </p>
            <p v-else-if="selectedTouristCoords" class="text-blue-600 font-medium flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              Coordenadas geocodificadas: {{ selectedTouristCoords.lat.toFixed(6) }}, {{ selectedTouristCoords.lng.toFixed(6) }}
            </p>
            <p v-else class="text-red-600 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              No se pudo obtener la ubicación. Verifique la dirección.
            </p>
          </div>
        </div>

        <!-- Datos de origen (cc base) -->
        <div class="mb-5 p-4 border border-gray-200 rounded-md bg-gray-50">
          <div class="flex items-center mb-2 text-green-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <h3 class="font-semibold">Punto de Partida</h3>
          </div>
          <p class="mb-1"><strong>Origen:</strong> {{ ccBaseAddress }}</p>
          <p><strong>Coordenadas:</strong> {{ ccBase.lat.toFixed(6) }}, {{ ccBase.lng.toFixed(6) }}</p>
        </div>

        <!-- Botón para calcular ruta -->
        <button 
          @click="calculateRoute" 
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-md shadow transition duration-200 flex items-center justify-center"
          :disabled="!selectedTouristId || isCalculating"
          :class="{'opacity-50 cursor-not-allowed': !selectedTouristId || isCalculating}"
        >
          <svg v-if="isCalculating" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
          </svg>
          {{ isCalculating ? 'Calculando...' : 'Calcular Ruta' }}
        </button>
      </div>

      <!-- Mapa -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
          </svg>
          Visualización del Recorrido
        </h2>
        <div id="map" class="w-full h-80 rounded-md border border-gray-200"></div>
        
        <!-- Leyenda del mapa -->
        <div class="mt-3 flex flex-wrap items-center text-sm text-gray-600">
          <div class="mr-6 flex items-center">
            <div class="h-4 w-4 bg-blue-500 rounded-full mr-1"></div>
            <span>Punto de partida</span>
          </div>
          <div class="flex items-center">
            <div class="h-4 w-4 bg-red-500 rounded-full mr-1"></div>
            <span>Destino</span>
          </div>
        </div>
      </div>

      <!-- Resultados -->
      <transition name="fade">
        <div v-if="routeData" class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Resultados del Recorrido
          </h2>
          <div class="flex flex-col md:flex-row md:space-x-6">
            <div class="mb-4 md:mb-0 flex-1 bg-gray-50 rounded-lg p-4 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
              </svg>
              <div>
                <div class="text-sm font-medium text-gray-500">Distancia Total</div>
                <div class="text-2xl font-bold text-gray-800">{{ routeData.distance }}</div>
              </div>
            </div>
            <div class="flex-1 bg-gray-50 rounded-lg p-4 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <div class="text-sm font-medium text-gray-500">Tiempo Estimado</div>
                <div class="text-2xl font-bold text-gray-800">{{ routeData.duration }}</div>
              </div>
            </div>
          </div>
          
          <!-- Información adicional -->
          <div class="mt-4 text-sm text-gray-600 bg-blue-50 p-4 rounded-md border-l-4 border-blue-500">
            <p class="font-medium mb-1">Información importante:</p>
            <p>Este cálculo muestra la distancia en línea recta entre los puntos. Para un recorrido más preciso considerando calles y restricciones de tráfico, utilice la aplicación de mapas en su dispositivo.</p>
          </div>
        </div>
      </transition>
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
const directionsRenderer = ref<any>(null);
const originMarker = ref<any>(null);
const destinationMarker = ref<any>(null);
const routeLine = ref<any>(null);

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
    showNotification("Error al cargar la lista de turistas. Por favor, recarga la página.", "error");
  }
};

// Mostrar notificaciones
const showNotification = (message: string, type: 'success' | 'error' | 'info' = 'info') => {
  // Si tienes un sistema de notificaciones, úsalo aquí
  // De lo contrario, usamos alert para simplicidad
  alert(message);
};

// Inicializar el mapa con estilo mejorado
const initMap = () => {
  if (!googleMapsLoaded.value) return;
  
  try {
    // Estilo personalizado para el mapa (opcional)
    const mapStyles = [
      {
        featureType: "poi",
        elementType: "labels",
        stylers: [{ visibility: "off" }],
      },
      {
        featureType: "transit",
        elementType: "labels",
        stylers: [{ visibility: "off" }],
      },
    ];
    
    // Crear el mapa centrado en el CC Base (punto de origen)
    map.value = new window.google.maps.Map(document.getElementById("map"), {
      center: ccBase,
      zoom: 14,
      mapTypeId: window.google.maps.MapTypeId.ROADMAP,
      mapTypeControl: false,
      fullscreenControl: true,
      streetViewControl: false,
      styles: mapStyles,
    });
    
    // Inicializar DirectionsRenderer para poder usar más adelante (si implementas la API de rutas)
    directionsRenderer.value = new window.google.maps.DirectionsRenderer({
      suppressMarkers: true, // Usamos marcadores personalizados
      polylineOptions: {
        strokeColor: '#4A89F3',
        strokeWeight: 5,
        strokeOpacity: 0.7
      }
    });
    directionsRenderer.value.setMap(map.value);
    
    // Crear un marcador para el CC Base
    originMarker.value = new window.google.maps.Marker({
      position: ccBase,
      map: map.value,
      title: ccBaseAddress,
      animation: window.google.maps.Animation.DROP,
      icon: {
        url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png",
        scaledSize: new window.google.maps.Size(40, 40)
      },
    });
    
    // Añadir InfoWindow para el marcador de origen
    const originInfoWindow = new window.google.maps.InfoWindow({
      content: `<div class="p-2"><strong>Punto de partida</strong><br>${ccBaseAddress}</div>`
    });
    
    originMarker.value.addListener('click', () => {
      originInfoWindow.open(map.value, originMarker.value);
    });
    
    console.log("Mapa inicializado correctamente");
  } catch (error) {
    console.error("Error al inicializar el mapa:", error);
    showNotification("Ocurrió un error al cargar el mapa. Por favor, recarga la página.", "error");
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
  script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places,geometry&callback=initGoogleMaps`;
  script.async = true;
  script.defer = true;
  
  document.head.appendChild(script);
};

// Actualiza las coordenadas del turista seleccionado con mejor manejo de errores
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
  
  isCalculating.value = true;
  
  // Usar el geocoder del cliente JS
  const geocoder = new window.google.maps.Geocoder();
  
  try {
    geocoder.geocode(
      { 
        address: selectedTourist.value.direccion,
        region: 'pe',
        componentRestrictions: { country: 'pe' } // Restringir a Perú para mayor precisión
      },
      (results: any, status: string) => {
        isCalculating.value = false;
        
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
          showNotification("No se pudo obtener la ubicación para la dirección proporcionada. Verifique que sea una dirección válida.", "error");
        }
      }
    );
  } catch (error) {
    console.error("Error al geocodificar:", error);
    selectedTouristCoords.value = null;
    isCalculating.value = false;
    showNotification("Error al procesar la dirección. Intente con otra dirección.", "error");
  }
};

// Añadir marcador del destino y centrar el mapa con animación
const updateMapWithDestination = () => {
  if (!map.value || !selectedTouristCoords.value) return;
  
  // Eliminar marcador anterior si existe
  if (destinationMarker.value) {
    destinationMarker.value.setMap(null);
  }
  
  // Eliminar línea de ruta anterior si existe
  if (routeLine.value) {
    routeLine.value.setMap(null);
  }
  
  // Crear un nuevo marcador para el destino con animación
  destinationMarker.value = new window.google.maps.Marker({
    position: selectedTouristCoords.value,
    map: map.value,
    title: selectedTourist.value?.nombre || "Destino",
    animation: window.google.maps.Animation.DROP,
    icon: {
      url: "http://maps.google.com/mapfiles/ms/icons/red-dot.png",
      scaledSize: new window.google.maps.Size(40, 40)
    },
  });
  
  // Añadir InfoWindow para el marcador de destino
  const destinationInfoWindow = new window.google.maps.InfoWindow({
    content: `<div class="p-2"><strong>${selectedTourist.value?.nombre || "Destino"}</strong><br>${selectedTourist.value?.direccion || ""}</div>`
  });
  
  destinationMarker.value.addListener('click', () => {
    destinationInfoWindow.open(map.value, destinationMarker.value);
  });
  
  // Ajustar el mapa para mostrar origen y destino con animación
  const bounds = new window.google.maps.LatLngBounds();
  bounds.extend(ccBase);
  bounds.extend(selectedTouristCoords.value);
  
  map.value.fitBounds(bounds, {
    padding: { top: 50, right: 50, bottom: 50, left: 50 }
  });
};

const onTouristChange = async () => {
  // Limpiar resultados anteriores
  routeData.value = null;
  
  // Eliminar línea de ruta anterior si existe
  if (routeLine.value) {
    routeLine.value.setMap(null);
  }
  
  // Esperamos a que Google Maps se cargue antes de intentar geocodificar
  if (!googleMapsLoaded.value) {
    setTimeout(() => {
      onTouristChange();
    }, 500);
    return;
  }
  
  await updateSelectedTouristCoords();
};

// Dibujar línea entre puntos con estilo mejorado
const drawLine = () => {
  if (!map.value || !selectedTouristCoords.value) return;
  
  // Eliminar línea anterior si existe
  if (routeLine.value) {
    routeLine.value.setMap(null);
  }
  
  // Crear una línea directa entre origen y destino con estilo mejorado
  routeLine.value = new window.google.maps.Polyline({
    path: [ccBase, selectedTouristCoords.value],
    geodesic: true,
    strokeColor: "#4A89F3",
    strokeOpacity: 0.8,
    strokeWeight: 5,
    map: map.value,
  });
  
  // Animar la línea dibujada
  animatePolyline(routeLine.value);
};

// Función para animar el dibujo de la línea
const animatePolyline = (polyline: any) => {
  let count = 0;
  const path = polyline.getPath();
  const length = path.getLength();
  const intervals = 100;
  
  polyline.setPath([]);
  
  const animationInterval = window.setInterval(() => {
    count++;
    const tempPath = [];
    
    for (let i = 0; i < count/intervals * length; i++) {
      tempPath.push(path.getAt(i));
    }
    
    polyline.setPath(tempPath);
    
    if (count >= intervals) {
      window.clearInterval(animationInterval);
      
      // Centrar el mapa en la ruta después de la animación
      const bounds = new window.google.maps.LatLngBounds();
      bounds.extend(ccBase);
      bounds.extend(selectedTouristCoords.value);
      map.value.fitBounds(bounds, {
        padding: { top: 50, right: 50, bottom: 50, left: 50 }
      });
    }
  }, 20);
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
