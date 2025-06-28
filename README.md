# 🧬 BioSystem - Sistema Biométrico de Gestión Estudiantil

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Python](https://img.shields.io/badge/Python-3776AB?style=for-the-badge&logo=python&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)

**BioSystem** es una solución integral para instituciones educativas que fusiona gestión académica y autenticación biométrica mediante huellas digitales.

---

## ✨ Características Principales

- 🔐 **Autenticación dual**: credenciales tradicionales y huellas digitales  
- 👨‍🎓 **Gestión estudiantil completa**: registrar, actualizar, eliminar  
- 📊 **Sistema biométrico**: registro y verificación de huellas  
- 📈 **Reportes**: asistencia, actividad y control académico  
- 👥 **Gestión de roles**: administración con permisos diferenciados  
- 💰 **Control de deudas**: seguimiento de obligaciones económicas  

---

## ⚙️ Tecnologías Utilizadas

### 🧠 Backend
- **Laravel 10+** – Framework principal  
- **Eloquent ORM** – Mapeo de datos  
- **Python 3.8+** – Integración con sensores biométricos  
- **MySQL** – Base de datos relacional  

### 🎨 Frontend
- **Tailwind CSS** – Estilos minimalistas  
- **Vite** – Empaquetador moderno  
- **Alpine.js** – Interactividad ligera  
- **Font Awesome** – Iconografía visual  

### 🧩 Hardware
- **Dispositivos compatibles** con `FTRAPI`  
- DLLs utilizadas: `FTRAPI.dll`, `ftrScanAPI.dll`  

---

## 🗂️ Estructura del Proyecto

```bash
.
├── app
│   ├── Http
│   │   ├── Controllers     # Lógica del sistema
│   │   └── Middleware      # Control de accesos
│   ├── Models              # Modelos Eloquent
│   └── Providers           # Service Providers
├── resources
│   ├── python              # Scripts biométricos
│   └── views               # Vistas Blade
├── public
│   └── build               # Assets compilados
├── routes
│   └── web.php             # Rutas web
└── database
    ├── migrations          # Estructura de tablas
    └── seeders             # Datos semilla
🚀 Instalación
🔧 Requisitos Previos
PHP 8.1+

Composer

Node.js 16+

Python 3.8+

MySQL 5.7+

📦 Pasos de instalación
bash
Copiar
Editar
# Clona el repositorio
git clone https://github.com/tu-usuario/biosystem.git
cd biosystem

# Instala dependencias PHP
composer install

# Instala dependencias JavaScript
npm install

# Configura el entorno
cp .env.example .env
php artisan key:generate

# Configura rutas biométricas en .env
# .env
BIOMETRIC_DEVICE_PATH=C:\ruta\al\dispositivo
BIOMETRIC_SCRIPT_PATH=resources/python/

# Ejecuta migraciones
php artisan migrate --seed

# Compila assets
npm run build

# Inicia el servidor
php artisan serve
👆 Uso de la Biometría
🔄 Flujo de Registro de Huella
mermaid
Copiar
Editar
sequenceDiagram
    participant Usuario
    participant Sistema
    participant Dispositivo
    participant Python
    participant BaseDeDatos

    Usuario->>+Sistema: Selecciona "Registrar huella"
    Sistema->>+Dispositivo: Solicita captura
    Dispositivo-->>-Sistema: Envía datos biométricos
    Sistema->>+Python: Ejecuta script
    Python-->>-Sistema: Confirma registro
    Sistema->>+BaseDeDatos: Guarda la plantilla
    BaseDeDatos-->>-Sistema: Confirmación
    Sistema-->>-Usuario: Registro exitoso
🐍 Scripts Python Disponibles
Script	Función
agregarHuellaExistente.py	Asocia huella a estudiante existente
login.py	Autenticación biométrica
probarDispositivo.py	Verifica estado del lector
agregarEstudiante.py	Registro de estudiante con huella

📸 Capturas de Pantalla
Haz clic para ampliar si estás en GitHub


Panel de administración


Registro de huellas


Panel de control académico


📄 Licencia
Este proyecto está bajo la licencia MIT.

BioSystem — Gestión Estudiantil con Biometría
Desarrollado con ❤️ para transformar la educación con seguridad e innovación.
