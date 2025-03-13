<template>
    <div>
      <div id="map" style="width: 100%; height: 500px"></div>
      <div class="search-container">
        <input
          id="pac-input"
          class="controls"
          type="text"
          placeholder="Buscar lugares..."
          ref="searchInput"
        />
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'GoogleMapsAutocomplete',
    props: {
      initialLocation: {
        type: Object,
        default: () => ({ lat: -11.849538799381891, lng: -77.14742280000002 }) // Default to -11.849538799381891, -77.14742280000002
      },
      initialAddress: {
        type: String,
        default: ''
      }
    },
    data() {
      return {
        map: null,
        apiKey: '',
        markers: [],
        autocomplete: null,
        currentMarker: null
      }
    },
    async mounted() {
      try {
        // Obtener la clave API desde el backend
        const response = await fetch('/api/google-maps-key')
        const data = await response.json()
        this.apiKey = data.apiKey
        
        // Cargar el script de Google Maps con las bibliotecas de Places
        this.loadGoogleMapsScript()
      } catch (error) {
        console.error('Error al obtener la clave API:', error)
      }
    },
    methods: {
      loadGoogleMapsScript() {
        // Check if script is already loaded
        if (window.google && window.google.maps) {
          this.initMap()
          return
        }
  
        const script = document.createElement('script')
        // Añadir la biblioteca de Places
        script.src = `https://maps.googleapis.com/maps/api/js?key=${this.apiKey}&libraries=places&callback=initGoogleMapsCallback`
        script.async = true
        script.defer = true
        
        // Definir la función de inicialización global
        window.initGoogleMapsCallback = this.initMap
        
        document.head.appendChild(script)
      },
      initMap() {
        // Initialize the map with initial location (from props or default)
        this.map = new google.maps.Map(document.getElementById('map'), {
          center: this.initialLocation,
          zoom: 15,
          mapTypeControl: true
        })
        
        // Create the search box
        const input = this.$refs.searchInput
        this.autocomplete = new google.maps.places.Autocomplete(input)
        this.autocomplete.bindTo('bounds', this.map)
        
        // Position the control on the map
        this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(input)
        
        // Add initial marker if we have coordinates
        if (this.initialLocation) {
          this.addMarker(this.initialLocation)
        }
        
        // Set initial address in search box if provided
        if (this.initialAddress) {
          input.value = this.initialAddress
        }
        
        // Listen for place selection events
        this.autocomplete.addListener('place_changed', () => {
          const place = this.autocomplete.getPlace()
          
          if (!place.geometry || !place.geometry.location) {
            console.log("No se encontraron detalles para: ", place.name)
            return
          }
          
          // If the place has geometry, show on the map
          if (place.geometry.viewport) {
            this.map.fitBounds(place.geometry.viewport)
          } else {
            this.map.setCenter(place.geometry.location)
            this.map.setZoom(17)
          }
          
          // Store place data
          const locationData = {
            name: place.name,
            address: place.formatted_address,
            location: {
              lat: place.geometry.location.lat(),
              lng: place.geometry.location.lng()
            }
          }
          
          // Add or update marker for the selected place
          this.addMarker(locationData.location)
          
          // Emit event with the place information
          this.$emit('place-selected', locationData)
        })
        
        // Allow clicking on the map to place marker
        this.map.addListener('click', (event) => {
          const clickedLocation = {
            lat: event.latLng.lat(),
            lng: event.latLng.lng()
          }
          
          this.addMarker(clickedLocation)
          
          // Get address for clicked location (reverse geocoding)
          const geocoder = new google.maps.Geocoder()
          geocoder.geocode({ location: clickedLocation }, (results, status) => {
            if (status === 'OK' && results[0]) {
              // Update search input with the address
              if (this.$refs.searchInput) {
                this.$refs.searchInput.value = results[0].formatted_address
              }
              
              // Emit event with the place information
              this.$emit('place-selected', {
                name: results[0].formatted_address,
                address: results[0].formatted_address,
                location: clickedLocation
              })
            }
          })
        })
      },
      addMarker(location) {
        // Remove previous marker if exists
        if (this.currentMarker) {
          this.currentMarker.setMap(null)
        }
        
        // Add new marker
        this.currentMarker = new google.maps.Marker({
          position: location,
          map: this.map,
          draggable: true
        })
        
        // Listen for marker drag events
        this.currentMarker.addListener('dragend', () => {
          const position = this.currentMarker.getPosition()
          const newLocation = {
            lat: position.lat(),
            lng: position.lng()
          }
          
          // Get address for new location
          const geocoder = new google.maps.Geocoder()
          geocoder.geocode({ location: newLocation }, (results, status) => {
            if (status === 'OK' && results[0]) {
              // Update search input with the address
              if (this.$refs.searchInput) {
                this.$refs.searchInput.value = results[0].formatted_address
              }
              
              // Emit event with the place information
              this.$emit('place-selected', {
                name: results[0].formatted_address,
                address: results[0].formatted_address,
                location: newLocation
              })
            }
          })
        })
      }
    }
  }
  </script>
  
  <style scoped>
  .search-container {
    position: relative;
  }
  
  #pac-input {
    background-color: #fff;
    font-family: sans-serif;
    font-size: 15px;
    font-weight: 300;
    margin-left: 12px;
    padding: 0 11px 0 13px;
    text-overflow: ellipsis;
    width: 300px;
    height: 40px;
    border-radius: 3px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    border: 1px solid transparent;
    margin-top: 10px;
  }
  
  #pac-input:focus {
    border-color: #4d90fe;
  }
  </style>