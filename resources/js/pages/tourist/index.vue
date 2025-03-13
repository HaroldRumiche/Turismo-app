<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="tourists-container">
      <!-- Encabezado con título y botón para agregar -->
      <div class="header-section">
        <h1 class="text-3xl font-bold text-indigo-800">Directorio de Turistas</h1>
        <button 
          @click="showAddModal = true" 
          class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center"
        >
          <span class="mr-2">Nuevo Turista</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </button>
      </div>

      <!-- Barra de búsqueda -->
      <div class="search-section">
        <div class="relative">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Buscar turistas..." 
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
          />
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute right-3 top-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <!-- Tarjetas de turistas -->
      <div v-if="loading" class="flex justify-center p-8">
        <div class="loader"></div>
      </div>
      
      <div v-else-if="filteredTourists.length === 0" class="text-center py-12">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-500 mt-4">No se encontraron turistas</h3>
        <p class="text-gray-400">Intenta con una búsqueda diferente o agrega un nuevo turista</p>
      </div>
      
      <div v-else class="tourists-grid">
        <div 
          v-for="tourist in filteredTourists" 
          :key="tourist.id" 
          class="tourist-card"
          :class="{'border-indigo-400': selectedTourist && selectedTourist.id === tourist.id}"
          @click="selectTourist(tourist)"
        >
          <div class="avatar bg-indigo-100 text-indigo-600">
            {{ tourist.nombre.charAt(0).toUpperCase() }}
          </div>
          <div class="tourist-info">
            <h3 class="font-semibold text-gray-800">{{ tourist.nombre }}</h3>
            <div class="tourist-contact">
              <div class="flex items-center text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ tourist.direccion }}
              </div>
              <div class="flex items-center text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                {{ tourist.telefono || 'No disponible' }}
              </div>
            </div>
          </div>
          <div class="tourist-actions">
            <button @click.stop="viewLocation(tourist)" class="action-btn text-green-600 hover:bg-green-50 mr-1" title="Ver ubicación">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </button>
            <button @click.stop="editTourist(tourist)" class="action-btn text-blue-600 hover:bg-blue-50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button @click.stop="deleteConfirm(tourist)" class="action-btn text-red-600 hover:bg-red-50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Paginación -->
      <div class="pagination">
        <button 
          @click="currentPage > 1 ? currentPage-- : null" 
          :disabled="currentPage <= 1"
          class="pagination-btn"
          :class="{'opacity-50 cursor-not-allowed': currentPage <= 1}"
        >
          Anterior
        </button>
        <span class="px-4 py-2">Página {{ currentPage }} de {{ totalPages }}</span>
        <button 
          @click="currentPage < totalPages ? currentPage++ : null" 
          :disabled="currentPage >= totalPages"
          class="pagination-btn"
          :class="{'opacity-50 cursor-not-allowed': currentPage >= totalPages}"
        >
          Siguiente
        </button>
      </div>

      <!-- Modal para agregar/editar turista -->
      <div v-if="showAddModal || showEditModal" class="modal-backdrop">
        <div class="modal-content">
          <div class="modal-header">
            <h2 class="text-xl font-bold">{{ showEditModal ? 'Editar Turista' : 'Nuevo Turista' }}</h2>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="form-group">
                <label for="nombre" class="form-label">Nombre</label>
                <input 
                  id="nombre" 
                  v-model="form.nombre" 
                  type="text" 
                  class="form-input"
                  required
                />
              </div>
              <div class="form-group">
                <label for="direccion" class="form-label">Dirección</label>
                <div class="flex">
                  <input 
                    id="direccion" 
                    ref="addressInput"
                    v-model="form.direccion" 
                    type="text" 
                    class="form-input flex-grow"
                    required
                  />
                  <button 
                    type="button" 
                    @click="showLocationPicker = true" 
                    class="ml-2 bg-indigo-600 hover:bg-indigo-700 text-white px-3 rounded"
                    title="Seleccionar ubicación en el mapa"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </button>
                </div>
                <div v-if="form.coordenadas" class="text-sm text-gray-500 mt-1">
                  Coordenadas: {{ form.coordenadas.lat.toFixed(6) }}, {{ form.coordenadas.lng.toFixed(6) }}
                </div>
              </div>
              <div class="form-group">
                <label for="telefono" class="form-label">Teléfono</label>
                <input 
                  id="telefono" 
                  v-model="form.telefono" 
                  type="text" 
                  class="form-input"
                />
              </div>
             
              <div class="form-actions">
                <button 
                  type="button" 
                  @click="closeModal" 
                  class="btn-cancel"
                >
                  Cancelar
                </button>
                <button 
                  type="submit" 
                  class="btn-submit"
                  :disabled="submitting"
                >
                  {{ submitting ? 'Guardando...' : 'Guardar' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal de confirmación de eliminación -->
      <div v-if="showDeleteModal" class="modal-backdrop">
        <div class="modal-content-sm">
          <div class="modal-header">
            <h2 class="text-xl font-bold">Confirmar Eliminación</h2>
            <button @click="showDeleteModal = false" class="text-gray-500 hover:text-gray-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <p class="mb-4">¿Estás seguro de que deseas eliminar al turista <strong>{{ touristToDelete?.nombre }}</strong>? Esta acción no se puede deshacer.</p>
            <div class="flex justify-end space-x-2">
              <button 
                @click="showDeleteModal = false" 
                class="btn-cancel"
              >
                Cancelar
              </button>
              <button 
                @click="confirmDelete" 
                class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg"
                :disabled="submitting"
              >
                {{ submitting ? 'Eliminando...' : 'Eliminar' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal del mapa para seleccionar ubicación -->
      <div v-if="showLocationPicker" class="modal-backdrop">
        <div class="modal-content-lg">
          <div class="modal-header">
        <h2 class="text-xl font-bold">Seleccionar ubicación</h2>
        <button @click="showLocationPicker = false" class="text-gray-500 hover:text-gray-700">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
          </div>
          <div class="modal-body">
        <div class="mb-4">
          <input 
            ref="mapSearchInput" 
            type="text" 
            placeholder="Buscar lugares..." 
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
          />
        </div>
        <div id="map" class="h-96 rounded-lg border border-gray-300"></div>
        <div class="flex justify-end mt-4">
          <button 
            @click="showLocationPicker = false" 
            class="btn-submit"
          >
            Confirmar ubicación
          </button>
        </div>
          </div>
        </div>
      </div>

      <!-- Modal del mapa para ver ubicación -->
      <div v-if="showViewMap" class="modal-backdrop">
        <div class="modal-content-lg">
          <div class="modal-header">
            <h2 class="text-xl font-bold">Ubicación: {{ selectedTourist?.nombre }}</h2>
            <button @click="showViewMap = false" class="text-gray-500 hover:text-gray-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <div id="viewMap" class="h-96 rounded-lg border border-gray-300"></div>
            <div class="mt-4">
              <p class="text-gray-700"><strong>Dirección:</strong> {{ selectedTourist?.direccion }}</p>
              <p v-if="selectedTourist?.telefono" class="text-gray-700"><strong>Teléfono:</strong> {{ selectedTourist?.telefono }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';
import GoogleMapsAutocomplete from '@/components/AutoComplete.vue'
import GoogleMap from '@/components/GoogleMap.vue';

// Configuramos el token CSRF para Laravel
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Definición de breadcrumbs
const breadcrumbs = ref([
  { title: 'Turistas', href: '/tourists' }
]);

// Estado
interface Tourist {
  id: number;
  nombre: string;
  direccion: string;
  telefono?: string;
  coordenadas?: {
    lat: number;
    lng: number;
  };
}

const tourists = ref<Tourist[]>([]);
const loading = ref(true);
const currentPage = ref(1);
const perPage = ref(10);
const totalItems = ref(0);
const searchQuery = ref('');
const selectedTourist = ref<Tourist | null>(null);

// Modales y mapas
const showAddModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showLocationPicker = ref(false);
const showViewMap = ref(false);
const touristToDelete = ref<Tourist | null>(null);

// Referencias para autocompletado y mapas
const addressInput = ref<HTMLInputElement | null>(null);
const mapSearchInput = ref<HTMLInputElement | null>(null);
let addressAutocomplete: any = null;
let mapAutocomplete: any = null;
let map: any = null;
let marker: any = null;
let viewMap: any = null;
let viewMarker: any = null;

// Formulario
const form = ref({
  nombre: '',
  direccion: '',
  telefono: '',
  coordenadas: null as { lat: number, lng: number } | null
});
const submitting = ref(false);

// Cálculos
const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value));

const filteredTourists = computed(() => {
  if (!searchQuery.value) return tourists.value;
  
  const query = searchQuery.value.toLowerCase();
  return tourists.value.filter(tourist => 
    tourist.nombre.toLowerCase().includes(query) || 
    tourist.direccion.toLowerCase().includes(query) ||
    (tourist.telefono && tourist.telefono.includes(query))
  );
});

// Métodos para cargar scripts de Google Maps
const loadGoogleMapsScript = () => {
  // Comprobar si ya está cargado
  if (window.google && window.google.maps) {
    initGoogleMaps();
    return;
  }

  // Obtener la clave de API
  axios.get('/api/google-maps-key')
    .then(response => {
      const apiKey = response.data.apiKey;
      const script = document.createElement('script');
      script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=initGoogleMapsCallback`;
      script.async = true;
      script.defer = true;
      document.head.appendChild(script);
    })
    .catch(error => {
      console.error('Error al obtener la clave de API de Google Maps:', error);
    });
  
  // Función global de callback
  window.initGoogleMapsCallback = initGoogleMaps;
};

const initGoogleMaps = () => {
  // Inicializar autocomplete para el campo de dirección
  if (showAddModal.value || showEditModal.value) {
    nextTick(() => {
      if (addressInput.value) {
        addressAutocomplete = new google.maps.places.Autocomplete(addressInput.value);
        addressAutocomplete.addListener('place_changed', () => {
          const place = addressAutocomplete.getPlace();
          if (!place.geometry) return;
          
          form.value.direccion = place.formatted_address;
          form.value.coordenadas = {
            lat: place.geometry.location.lat(),
            lng: place.geometry.location.lng()
          };
        });
      }
    });
  }
  
  // Inicializar mapa para seleccionar ubicación
  if (showLocationPicker.value) {
    nextTick(() => {
      const mapElement = document.getElementById('map');
      if (mapElement) {
        const defaultLocation = { lat: -11.8495387993818916, lng: -77.14742280000002 }; // CDVC por defecto
        const mapOptions = {
          center: form.value.coordenadas || defaultLocation,
          zoom: 14,
          mapTypeControl: true
        };
        
        map = new google.maps.Map(mapElement, mapOptions);
        
        // Agregar marcador si hay coordenadas existentes
        if (form.value.coordenadas) {
          marker = new google.maps.Marker({
            position: form.value.coordenadas,
            map: map,
            draggable: true
          });
        } else {
          marker = new google.maps.Marker({
            position: defaultLocation,
            map: map,
            draggable: true
          });
        }
        
        // Actualizar coordenadas al arrastrar el marcador
        google.maps.event.addListener(marker, 'dragend', function() {
          const position = marker.getPosition();
          form.value.coordenadas = {
            lat: position.lat(),
            lng: position.lng()
          };
          
          // Obtener dirección a partir de coordenadas (geocodificación inversa)
          const geocoder = new google.maps.Geocoder();
          geocoder.geocode({ location: position }, (results, status) => {
            if (status === 'OK' && results[0]) {
              form.value.direccion = results[0].formatted_address;
            }
          });
        });
        
        // Configurar autocompletado para el campo de búsqueda del mapa
        if (mapSearchInput.value) {
          mapAutocomplete = new google.maps.places.Autocomplete(mapSearchInput.value);
          mapAutocomplete.bindTo('bounds', map);
          
          mapAutocomplete.addListener('place_changed', () => {
            const place = mapAutocomplete.getPlace();
            if (!place.geometry) return;
            
            // Centrar mapa en el lugar seleccionado
            if (place.geometry.viewport) {
              map.fitBounds(place.geometry.viewport);
            } else {
              map.setCenter(place.geometry.location);
              map.setZoom(17);
            }
            
            // Actualizar marcador
            marker.setPosition(place.geometry.location);
            
            // Actualizar formulario
            form.value.direccion = place.formatted_address;
            form.value.coordenadas = {
              lat: place.geometry.location.lat(),
              lng: place.geometry.location.lng()
            };
          });
        }
        
        // Permitir hacer clic en el mapa para colocar el marcador
        google.maps.event.addListener(map, 'click', (event: any) => {
          marker.setPosition(event.latLng);
          
          form.value.coordenadas = {
            lat: event.latLng.lat(),
            lng: event.latLng.lng()
          };
          
          // Obtener dirección a partir de coordenadas
          const geocoder = new google.maps.Geocoder();
          geocoder.geocode({ location: event.latLng }, (results, status) => {
            if (status === 'OK' && results[0]) {
              form.value.direccion = results[0].formatted_address;
            }
          });
        });
      }
    });
  }
  
  // Inicializar mapa para ver ubicación
  if (showViewMap.value && selectedTourist.value) {
    nextTick(() => {
      const viewMapElement = document.getElementById('viewMap');
      if (viewMapElement) {
        const location = selectedTourist.value?.coordenadas || 
                       { lat: -11.849538799381891, lng: -77.14742280000002 }; // CDVC por defecto
        
        viewMap = new google.maps.Map(viewMapElement, {
          center: location,
          zoom: 14
        });
        
        // Agregar marcador
        viewMarker = new google.maps.Marker({
          position: location,
          map: viewMap,
          title: selectedTourist.value?.nombre
        });
        
        // Si no hay coordenadas guardadas, intentar geocodificar la dirección
        if (!selectedTourist.value?.coordenadas && selectedTourist.value?.direccion) {
          const geocoder = new google.maps.Geocoder();
          geocoder.geocode({ address: selectedTourist.value.direccion }, (results, status) => {
            if (status === 'OK' && results[0]) {
              const position = results[0].geometry.location;
              viewMap.setCenter(position);
              viewMarker.setPosition(position);
            }
          });
        }
      }
    });
  }
};

// Métodos
const updateLocation = (place: any) => {
  if (!place.geometry) return;

  form.value.direccion = place.formatted_address;
  form.value.coordenadas = {
    lat: place.geometry.location.lat(),
    lng: place.geometry.location.lng()
  };
};

const fetchTourists = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/tourists', {
      params: {
        page: currentPage.value,
        per_page: perPage.value,
        search: searchQuery.value
      }
    });
    
    // Comprobar la estructura de la respuesta
    if (response.data.data) {
      tourists.value = response.data.data.map((tourist: any) => {
        // Asegurarse de que las coordenadas estén en formato adecuado
        if (tourist.coordenadas && typeof tourist.coordenadas === 'string') {
          try {
            tourist.coordenadas = JSON.parse(tourist.coordenadas);
          } catch (e) {
            console.error('Error al parsear coordenadas:', e);
            tourist.coordenadas = null;
          }
        }
        return tourist;
      });
      totalItems.value = response.data.total || response.data.data.length;
    } else {
      // Si la respuesta no tiene una propiedad 'data', asumimos que los datos están en la raíz
      tourists.value = response.data.map((tourist: any) => {
        // Asegurarse de que las coordenadas estén en formato adecuado
        if (tourist.coordenadas && typeof tourist.coordenadas === 'string') {
          try {
            tourist.coordenadas = JSON.parse(tourist.coordenadas);
          } catch (e) {
            console.error('Error al parsear coordenadas:', e);
            tourist.coordenadas = null;
          }
        }
        return tourist;
      });
      totalItems.value = response.data.length;
    }
  } catch (error) {
    console.error('Error al cargar turistas:', error);
    // Aquí podrías mostrar un mensaje de error al usuario
  } finally {
    loading.value = false;
  }
};

const selectTourist = (tourist: Tourist) => {
  // Ensure coordinates are properly formatted
  if (tourist.coordenadas && typeof tourist.coordenadas === 'string') {
    try {
      tourist.coordenadas = JSON.parse(tourist.coordenadas);
    } catch (e) {
      console.error('Error parsing coordinates:', e);
      tourist.coordenadas = null;
    }
  }
  selectedTourist.value = selectedTourist.value?.id === tourist.id ? null : tourist;
};

const editTourist = (tourist: Tourist) => {
  // Ensure coordinates are properly formatted
  let coordinates = null;
  if (tourist.coordenadas) {
    if (typeof tourist.coordenadas === 'string') {
      try {
        coordinates = JSON.parse(tourist.coordenadas);
      } catch (e) {
        console.error('Error parsing coordinates:', e);
        coordinates = null;
      }
    } else {
      coordinates = tourist.coordenadas;
    }
  }
  
  form.value = {
    nombre: tourist.nombre,
    direccion: tourist.direccion,
    telefono: tourist.telefono || '',
    coordenadas: coordinates
  };
  
  selectedTourist.value = {
    ...tourist,
    coordenadas: coordinates
  };
  
  showEditModal.value = true;
  
  // Inicializar Maps después de que se muestre el modal
  nextTick(() => {
    loadGoogleMapsScript();
  });
};

const viewLocation = (tourist: Tourist) => {
  // Ensure coordinates are properly formatted
  if (tourist.coordenadas && typeof tourist.coordenadas === 'string') {
    try {
      tourist.coordenadas = JSON.parse(tourist.coordenadas);
    } catch (e) {
      console.error('Error parsing coordinates:', e);
      tourist.coordenadas = null;
    }
  }
  
  selectedTourist.value = tourist;
  showViewMap.value = true;
  
  // Inicializar Maps después de que se muestre el modal
  nextTick(() => {
    loadGoogleMapsScript();
  });
};

const closeModal = () => {
  showAddModal.value = false;
  showEditModal.value = false;
  showLocationPicker.value = false;
  showViewMap.value = false;
  form.value = {
    nombre: '',
    direccion: '',
    telefono: '',
    coordenadas: null
  };
};

const submitForm = async () => {
  submitting.value = true;
  try {
    // Asegurarse de que las coordenadas estén en el formato correcto
    const formData = {
      nombre: form.value.nombre,
      direccion: form.value.direccion,
      telefono: form.value.telefono,
      // Siempre enviar las coordenadas como string
      coordenadas: form.value.coordenadas ? JSON.stringify(form.value.coordenadas) : null
    };
    
    if (showEditModal.value && selectedTourist.value) {
      await axios.put(`/tourists/${selectedTourist.value.id}`, formData);
    } else {
      await axios.post('/tourists', formData);
    }
    closeModal();
    fetchTourists();
  } catch (error) {
    console.error('Error al guardar turista:', error);
    // Aquí podrías mostrar un mensaje de error al usuario
  } finally {
    submitting.value = false;
  }
};

const deleteConfirm = (tourist: Tourist) => {
  touristToDelete.value = tourist;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!touristToDelete.value) return;
  
  submitting.value = true;
  try {
    await axios.delete(`/tourists/${touristToDelete.value.id}`);
    if (selectedTourist.value?.id === touristToDelete.value.id) {
      selectedTourist.value = null;
    }
    showDeleteModal.value = false;
    touristToDelete.value = null;
    fetchTourists();
  } catch (error) {
    console.error('Error al eliminar turista:', error);
    // Aquí podrías mostrar un mensaje de error al usuario
  } finally {
    submitting.value = false;
  }
};

// Watchers para reiniciar la paginación cuando cambia la búsqueda
watch(searchQuery, () => {
  currentPage.value = 1;
});

// Cargar datos iniciales
onMounted(() => {
  fetchTourists();
});

// Ver cambios en paginación o búsqueda
watch([currentPage, searchQuery], () => {
  fetchTourists();
});

// Escuchar cambios en los modales para inicializar mapas
watch([showAddModal, showEditModal, showLocationPicker, showViewMap], (newValues) => {
  const [newAdd, newEdit, newPicker, newView] = newValues;
  if (newAdd || newEdit || newPicker || newView) {
    nextTick(() => {
      loadGoogleMapsScript();
    });
  }
});
</script>

