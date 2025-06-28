BioSystem - Sistema Biométrico de Gestión Estudiantil
https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
https://img.shields.io/badge/Python-3776AB?style=for-the-badge&logo=python&logoColor=white
https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white

BioSystem es una solución integral para instituciones educativas que combina gestión estudiantil con autenticación biométrica mediante huellas digitales.

Características Principales
🔐 Autenticación dual: Credenciales tradicionales o huellas digitales

👨‍🎓 Gestión completa de estudiantes: Registro, actualización y eliminación

📊 Sistema biométrico: Registro y verificación de huellas digitales

📈 Reportes detallados: Seguimiento de asistencia y actividades

👥 Gestión de roles: Administradores y personal con permisos diferenciados

💰 Control de deudas: Seguimiento de obligaciones estudiantiles

Tecnologías Utilizadas
Backend
Laravel 10+ - Framework PHP principal

Eloquent ORM - Gestión de base de datos

Python 3.8+ - Integración con dispositivos biométricos

MySQL - Base de datos relacional

Frontend
Tailwind CSS - Estilización de componentes

Vite - Bundler y build tool

Font Awesome - Iconografía

Alpine.js - Interactividad en vistas

Hardware
Dispositivos biométricos compatibles con FTRAPI

DLLs de integración: FTRAPI.dll, ftrScanAPI.dll

Estructura del Proyecto
bash
.
├── app
│   ├── Http
│   │   ├── Controllers  # Lógica de controladores
│   │   └── Middleware   # Control de acceso
│   ├── Models           # Modelos de datos
│   └── Providers        # Service Providers
├── resources
│   ├── python           # Scripts de integración biométrica
│   └── views            # Vistas Blade
├── public
│   └── build            # Assets compilados
├── routes
│   └── web.php          # Definición de rutas
└── database
    ├── migrations       # Esquema de base de datos
    └── seeders          # Datos iniciales
Instalación
Requisitos previos
PHP 8.1+

Composer

Node.js 16+

Python 3.8+

MySQL 5.7+

Pasos de instalación
Clonar el repositorio:

bash
git clone https://github.com/tu-usuario/biosystem.git
cd biosystem
Instalar dependencias PHP:

bash
composer install
Instalar dependencias JavaScript:

bash
npm install
Configurar entorno (copiar y editar .env):

bash
cp .env.example .env
php artisan key:generate
Configurar variables de entorno para biométrica en .env:

env
BIOMETRIC_DEVICE_PATH=C:\ruta\al\dispositivo
BIOMETRIC_SCRIPT_PATH=resources/python/
Ejecutar migraciones:

bash
php artisan migrate --seed
Compilar assets:

bash
npm run build
Iniciar servidor:

bash
php artisan serve
Uso de la Biometría
Flujo de registro de huella
Diagram
Code
sequenceDiagram
    Usuario->>+Sistema: Selecciona "Registrar huella"
    Sistema->>+Dispositivo: Solicita captura de huella
    Dispositivo-->>-Sistema: Envía datos biométricos
    Sistema->>+Python: Ejecuta script de registro
    Python-->>-Sistema: Confirma registro exitoso
    Sistema->>+Base de Datos: Almacena huella asociada
    Base de Datos-->>-Sistema: Confirma almacenamiento
    Sistema-->>-Usuario: Muestra confirmación
Scripts Python disponibles
Script	Función
agregarHuellaExistente.py	Asocia huella a estudiante existente
login.py	Autenticación biométrica
probarDispositivo.py	Verifica estado del dispositivo
agregarEstudiante.py	Crea nuevo estudiante con huella
Capturas de Pantalla
https://screenshots/admin-dashboard.png
Panel de Administración - Vista general

https://screenshots/biometric-registration.png
Interfaz de registro biométrico

https://screenshots/student-management.png
Panel de gestión estudiantil

Contribución
Las contribuciones son bienvenidas. Sigue estos pasos:

Haz un fork del proyecto

Crea tu rama (git checkout -b feature/nueva-funcionalidad)

Realiza tus cambios

Haz commit de los cambios (git commit -m 'Añade nueva funcionalidad')

Haz push a la rama (git push origin feature/nueva-funcionalidad)

Abre un Pull Request

Licencia
Este proyecto está bajo la licencia MIT.

BioSystem - Gestión Estudiantil con Biometría · Desarrollado con ❤️ para instituciones educativas
