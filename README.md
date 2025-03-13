# turismo-app

## Descripción

**turismo-app** es una aplicación web desarrollada con Vue.js y Laravel, diseñada para gestionar y visualizar información de turistas. La aplicación permite agregar, editar, eliminar y visualizar turistas, así como calcular rutas desde una ubicación base hasta la dirección de un turista utilizando la API de Google Maps.

## Características

- **Gestión de Turistas**: Agregar, editar y eliminar información de turistas.
- **Visualización de Ubicación**: Ver la ubicación de un turista en un mapa.
- **Cálculo de Rutas**: Calcular la ruta desde una ubicación base hasta la dirección de un turista.
- **Autocompletado de Direcciones**: Utiliza Google Maps Autocomplete para facilitar la entrada de direcciones.
- **Interfaz de Usuario Intuitiva**: Diseño responsivo y fácil de usar.

## Tecnologías Utilizadas

- **Frontend**: Vue.js 3, TypeScript, Tailwind CSS
- **Backend**: Laravel
- **API de Mapas**: Google Maps API

## Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/tu-usuario/turismo-app.git
   cd turismo-app
   ```

2. Instala las dependencias del backend:
   ```bash
   composer install
   ```

3. Instala las dependencias del frontend:
   ```bash
   npm install
   ```

4. Configura el archivo **.env** con tu base de datos y la clave API de Google Maps:

5. Genera la clave de la aplicación:
   ```bash
   php artisan key:generate  
   ```

6. Ejecuta las migraciones de la base de datos:
   ```bash
   php artisan migrate  
   ```

7. Compila los assets del frontend:
   ```bash
   npm run dev  
   ```

8. Inicia el servidor de desarrollo:
   ```bash
   php artisan serve  
   ```
   Si trabajas con laragon :
   ```bash
   APP_URL_BASE={}.test 
   ```

## Uso

1. Gestión de Turistas

**a. Agregar Turista:** Haz clic en el botón "Nuevo Turista" y completa el formulario.
**b. Editar Turista:** Haz clic en el icono de edición en la tarjeta del turista y actualiza la información.
**c. Eliminar Turista:** Haz clic en el icono de eliminación en la tarjeta del turista y confirma la acción.

2. Visualización de Ubicación

**a. Ver Ubicación:** Haz clic en el icono de ubicación en la tarjeta del turista para ver su ubicación en un mapa.

3. Cálculo de Rutas

**a. Calcular Ruta:** Navega a la sección **"Calcular Recorrido"**, selecciona un turista y el modo de transporte, y haz clic en **"Calcular Ruta".**

## Contribución

Si deseas contribuir a este proyecto, por favor sigue estos pasos:

1. Haz un fork del repositorio.
2. Crea una nueva rama 
   ```bash
   git checkout -b feature/nueva-caracteristica 
   ```
3. Realiza tus cambios y haz commit (').
   ```bash
   git commit -am 'Añadir nueva característica'  
   ```
4. Sube tus cambios a tu fork.
   ```bash
   git push origin feature/nueva-caracteristica   
   ```
5. Abre un Pull Request.

## Licencia

Este proyecto está licenciado bajo la MIT License.
   ```bash
Puedes copiar y pegar este código Markdown directamente en tu archivo de documentación. Si necesitas alguna otra modificación, ¡házmelo saber!
   ```