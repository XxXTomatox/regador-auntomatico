# Sistema de Monitoreo y Control con Arduino y Laravel



Aplicación web y API REST desarrollada en **Laravel** para recibir lecturas de sensores provenientes de **Arduino**, visualizar su estado en un panel protegido y alternar el control manual de una bomba.



<!-- Opcional: añade badges y capturas de pantalla para mejorar la visibilidad en GitHub -->

<!-- ![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat-square&logo=laravel) -->

<!-- ![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat-square&logo=php) -->

<!-- ![License](https://img.shields.io/badge/License-MIT-green?style=flat-square) -->



## Características principales



* **Recepción y consulta de telemetría de Arduino (`routes/api.php`)**:

  * `POST /api/arduino/data`: Registra nuevas lecturas enviadas por el dispositivo (`ArduinoController@store`).

  * `GET /api/arduino/latest`: Obtiene la lectura más reciente (`ArduinoController@latest`).

  * `GET /api/arduino/bomba-estado`: Consulta el estado booleano actual de `bomba_manual` desde la configuración.

* **Panel web y autenticación (`routes/web.php`)**:

  * Autenticación y gestión de perfil mediante **Laravel Breeze** (`dashboard`, `profile.edit`, `profile.update`, `profile.destroy` y rutas de autenticación).

  * Vista principal del tablero (`GET /dashboard`) atendida por `ArduinoController@index` para usuarios autenticados y verificados.

  * Control manual de bomba (`POST /bomba/toggle`): Alterna el campo `bomba_manual` en el primer registro de `Configuracion` y responde en JSON.

* **Variables monitoreadas (`sensor_data`)**:

  * Lecturas numéricas: `temperatura`, `humedad` y `peso`.

  * Indicadores de estado y alertas: `alerta_termica`, `bomba_encendida` y `alerta_peso`.



## Stack tecnológico



* **Backend**: PHP `^8.3`, Laravel Framework `^13.17`, Laravel Breeze `^2.4` (dev), Laravel Tinker `^3.0`.

* **Frontend**: Vite `^8.0.0`, Tailwind CSS `^3.1.0` / `@tailwindcss/vite` `^4.0.0`, `@tailwindcss/forms` `^0.5.2`, Alpine.js `^3.4.2`, `laravel-vite-plugin` `^3.1`.

* **Base de datos por defecto**: SQLite (`DB_CONNECTION=sqlite` en `.env.example`), configurable a otros motores compatibles con Laravel.

* **Calidad y pruebas**: Pest PHP (`pestphp/pest` `^4.7` y `pestphp/pest-plugin-laravel` `^4.1`), Laravel Pint `^1.27`, Laravel Pail `^1.2.5`, Collision `^8.6`.



## Estructura del proyecto



```text

├── app/

│   ├── Http/Controllers/   # ArduinoController, ProfileController, etc.

│   └── Models/             # Configuracion y modelos del dominio

├── database/

│   └── migrations/         # users, cache, jobs, sensor_data, configuracions

├── routes/

│   ├── web.php             # Rutas web, autenticación y /bomba/toggle

│   ├── api.php             # Endpoints para Arduino (/arduino/*)

│   └── auth.php            # Rutas de autenticación de Breeze

├── composer.json           # Dependencias PHP y scripts de Composer

└── package.json            # Dependencias frontend y scripts de Vite

```



## Requisitos previos



* **PHP** `^8.3` y **Composer**.

* **Node.js** y **npm** (para compilar recursos con Vite).

* Extensión de base de datos correspondiente (por ejemplo, SQLite habilitado para la configuración por defecto).



## Instalación



### Opción rápida con Composer



`composer.json` incluye un script `setup` que instala dependencias, copia `.env.example` a `.env` (si no existe), genera la clave de aplicación, ejecuta migraciones e instala/compila assets:



```bash

composer run setup

```



*(Nota: si usas `sqlite` como en `.env.example` y aún no existe el archivo `database/database.sqlite`, créalo antes de migrar si tu entorno lo requiere.)*



### Instalación paso a paso



1. **Clonar el repositorio y entrar al directorio**:

   ```bash

   git clone <URL-DEL-REPOSITORIO>

   cd <NOMBRE-DE-LA-CARPETA>

   ```



2. **Instalar dependencias de PHP**:

   ```bash

   composer install

   ```



3. **Configurar variables de entorno**:

   ```bash

   cp .env.example .env

   php artisan key:generate

   ```



4. **Preparar la base de datos y ejecutar migraciones**:

   Por defecto `.env.example` usa `DB_CONNECTION=sqlite` (asegúrate de tener `database/database.sqlite` si utilizas SQLite):

   ```bash

   touch database/database.sqlite

   php artisan migrate

   ```



5. **Instalar dependencias frontend y compilar**:

   ```bash

   npm install

   npm run build

   ```



## Uso



### Levantar el entorno de desarrollo



Puedes iniciar el servidor con el script de Composer:



```bash

composer run dev

```



O ejecutar Vite en modo desarrollo:



```bash

npm run dev

```



### Flujo de uso



1. **Configuración inicial de la bomba**: Asegúrate de que exista al menos un registro en la tabla `configuracions` (por ejemplo, mediante un seeder, Tinker o la lógica inicial de tu app), ya que `/bomba/toggle` consulta `Configuracion::first()`.

2. **Conexión desde Arduino**: Apunta tu placa/módulo con conectividad HTTP a:

   * `POST /api/arduino/data` para enviar datos de sensores.

   * `GET /api/arduino/latest` para consultar la última lectura.

   * `GET /api/arduino/bomba-estado` para leer el estado de `bomba_manual`.

3. **Panel de control web**: Regístrate o inicia sesión en la aplicación web, ve a `/dashboard` y utiliza el control de bomba en `/bomba/toggle`.



### Pruebas



Ejecuta la suite de pruebas configurada en `composer.json` con:



```bash

composer test

```



## Variables de entorno relevantes



Revisa `.env.example` para ver todas las variables disponibles. Las más relevantes en este proyecto son:



* `APP_NAME`, `APP_ENV`, `APP_URL`: Nombre, entorno y URL base de la aplicación.

* `DB_CONNECTION` (y `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` si cambias de `sqlite` a otro motor).

* `SESSION_DRIVER`, `QUEUE_CONNECTION`, `CACHE_STORE`: Configurados por defecto en `database` en `.env.example`.

* `MAIL_*`, `REDIS_*`, `AWS_*`: Opcionales según cómo despliegues correo, caché/colas o almacenamiento.



## Contribución



Las contribuciones son bienvenidas:



1. Haz un *fork* del repositorio.

2. Crea una rama para tu cambio (`git checkout -b feature/mejora-sensores`).

3. Realiza tus cambios y ejecuta las pruebas (`composer test`).

4. Envía un *Pull Request* describiendo qué agregaste o corregiste.



## Licencia



Este proyecto se distribuye bajo la licencia **MIT** (ver `composer.json`).
