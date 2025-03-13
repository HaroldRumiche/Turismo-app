<template>
    <div>
      <div id="map" style="width: 100%; height: 500px"></div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        map: null,
        apiKey: '',
        markers: []
      }
    },
    async mounted() {
      try {
        // Obtener la clave API desde el backend
        const response = await fetch('/api/google-maps-key')
        const data = await response.json()
        this.apiKey = data.apiKey
        
        // Cargar el script de Google Maps
        this.loadGoogleMapsScript()
      } catch (error) {
        console.error('Error al obtener la clave API:', error)
      }
    },
    methods: {
      loadGoogleMapsScript() {
        const script = document.createElement('script')
        script.src = `https://maps.googleapis.com/maps/api/js?key=${this.apiKey}&callback=initMap`
        script.async = true
        script.defer = true
        
        // Definir la función de inicialización global
        window.initMap = this.initMap
        
        document.head.appendChild(script)
      },
      initMap() {
        this.map = new google.maps.Map(document.getElementById('map'), {
          center: { lat: -11.849559123707929, lng: -77.14743004695576}, // Coordenadas de ejemplo (CDMX)
          zoom: 15
        })
        
        // Añadir un marcador de ejemplo -11.849559123707929, -77.14743004695576
        const marker = new google.maps.Marker({
          position: { lat: -11.849559123707929, lng: -77.14743004695576 },
          map: this.map,
          title: 'Marcador de ejemplo'
        })
        
        this.markers.push(marker)
      }
    }
  }
  </script>