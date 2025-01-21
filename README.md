# Proyecto de Gestión de Itinerarios

Este proyecto es una aplicación web para gestionar itinerarios de clientes utilizando Vue.js y PHP. La aplicación permite asignar y visualizar itinerarios para diferentes días de la semana.

## Archivos del Proyecto

- `.env`
- `.env.example`
- `.gitignore`
- `docker-compose.dev.yml`
- `Dockerfile`
- `endpoint.php`
- `index.js`
- `index.php`
- `README.md`
- `resources/`

## Instalación

1. Clona el repositorio:
    ```sh
    git clone https://github.com/ernestoarce/itinerariosPreventa.git
    ```

2. Copia el archivo `.env.example` a `.env` y configura las variables de entorno según sea necesario.

3. Construye y levanta los contenedores de Docker:
    ```sh
    docker-compose -f docker-compose.dev.yml up --build
    ```