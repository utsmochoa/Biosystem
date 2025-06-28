<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>BioSystem | Ver dispositivo conectado</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet" />
</head>
<body class="bg-blue-200 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-md rounded-md p-10 w-full max-w-6xl mx-auto relative">
       <!-- Contenedor de botones -->
        <div class="flex justify-between items-center mb-6">
            <!-- Botón Volver -->
            <a href="{{ route('Configurar.dispositivo') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium inline-flex items-center transition">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>

            <!-- Botón Actualizar -->
            <button id="refresh-button"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium inline-flex items-center transition">
                <i class="fas fa-sync-alt mr-2" id="refresh-icon"></i> Actualizar
            </button>
        </div>


        <!-- Header con logo y título -->
        <div class="flex flex-col items-center mb-10">
            <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa" class="h-16 mb-4" />
            <h1 class="text-3xl font-extrabold text-blue-800 tracking-wide text-center mb-6">
                Ver dispositivo conectado
            </h1>
        </div>

        <!-- Indicador de estado general -->
        <div class="mb-6 p-4 rounded-lg border-l-4" id="status-indicator">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-circle text-gray-500" id="status-icon"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium" id="status-text">Verificando estado del dispositivo...</p>
                    <p class="text-xs text-gray-500 mt-1" id="status-details">Cargando información...</p>
                </div>
            </div>
        </div>

        <!-- Tabla de dispositivos -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-800">
                <thead>
                    <tr class="bg-blue-100">
                        <th class="border border-gray-800 p-4 text-center w-1/4">ID</th>
                        <th class="border border-gray-800 p-4 text-center w-1/2">Nombre del dispositivo</th>
                        <th class="border border-gray-800 p-4 text-center w-1/4">Estado</th>
                    </tr>
                </thead>
                <tbody id="devices-table-body">
                    <tr id="loading-row">
                        <td class="border border-gray-800 p-4 h-16 text-center" colspan="3">
                            <div class="flex items-center justify-center">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Cargando dispositivos...
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Panel de información detallada (inicialmente oculto) -->
        <div id="device-details" class="mt-8 bg-gray-50 p-6 rounded-lg hidden">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-info-circle mr-2"></i>
                Información detallada del dispositivo
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Información básica -->
                <div class="bg-white p-4 rounded border">
                    <h4 class="font-medium text-gray-700 mb-3">Información básica</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Modelo:</span>
                            <span id="detail-model" class="font-medium">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tipo:</span>
                            <span id="detail-type" class="font-medium">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Número de serie:</span>
                            <span id="detail-serial" class="font-medium">-</span>
                        </div>
                        
                    </div>
                </div>

                <!-- Información de versiones -->
                <div class="bg-white p-4 rounded border">
                    <h4 class="font-medium text-gray-700 mb-3">Versiones</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">FTRAPI:</span>
                            <span id="detail-api-version" class="font-medium">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">ftrScanApi:</span>
                            <span id="detail-hw-version" class="font-medium">-</span>
                        </div>
                       
                    </div>
                </div>

                <!-- Estado del sensor -->
                <div class="bg-white p-4 rounded border">
                    <h4 class="font-medium text-gray-700 mb-3">Estado del sensor</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Sensor activo:</span>
                            <span id="detail-sensor-active" class="font-medium">
                                <i class="fas fa-circle text-gray-400"></i> -
                            </span>
                        </div>
                       
                    </div>
                </div>

                <!-- Información del sistema -->
                <div class="bg-white p-4 rounded border">
                    <h4 class="font-medium text-gray-700 mb-3">Sistema</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Puerto:</span>
                            <span id="detail-port" class="font-medium">-</span>
                        </div>
                       
                        <div class="flex justify-between">
                            <span class="text-gray-600">SO:</span>
                            <span id="detail-os" class="font-medium">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Arquitectura:</span>
                            <span id="detail-arch" class="font-medium">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón para ocultar detalles -->
            <div class="mt-4 text-center">
                <button id="hide-details-button" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    <i class="fas fa-chevron-up mr-1"></i>
                    Ocultar detalles
                </button>
            </div>
        </div>

        <!-- Información de última actualización -->
        <div class="mt-6 text-center text-xs text-gray-500">
            <span id="last-update">Última actualización: -</span>
        </div>
    </div>

    <!-- Modal de inactividad (sin cambios) -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="inactive" id="inactive" value="0">
    </form>
    
    <div id="inactivity-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full text-center">
            <h2 class="text-xl font-bold text-gray-800 mb-2">⚠ Inactividad detectada</h2>
            <p class="text-gray-600 mb-4">Tu sesión se cerrará automáticamente en 1 minuto.</p>
            <button onclick="closeModal()" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Entendido
            </button>
        </div>
    </div>
    
    <script>
        // === VARIABLES GLOBALES ===
        let deviceData = null;
        let refreshInterval = null;
        const REFRESH_INTERVAL_MS = 10000; // 10 segundos

        // === CONFIGURACIÓN CSRF ===
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // === FUNCIONES DE ACTUALIZACIÓN DE UI ===
        
        function updateStatusIndicator(status, message, details = '') {
            const indicator = document.getElementById('status-indicator');
            const icon = document.getElementById('status-icon');
            const text = document.getElementById('status-text');
            const detailsElement = document.getElementById('status-details');

            // Remover clases anteriores
            indicator.className = 'mb-6 p-4 rounded-lg border-l-4';
            
            switch(status) {
                case 'conectado':
                    indicator.classList.add('bg-green-50', 'border-green-400');
                    icon.className = 'fas fa-circle text-green-500';
                    break;
                case 'desconectado':
                    indicator.classList.add('bg-red-50', 'border-red-400');
                    icon.className = 'fas fa-circle text-red-500';
                    break;
                case 'error':
                    indicator.classList.add('bg-yellow-50', 'border-yellow-400');
                    icon.className = 'fas fa-exclamation-triangle text-yellow-500';
                    break;
                default:
                    indicator.classList.add('bg-gray-50', 'border-gray-400');
                    icon.className = 'fas fa-circle text-gray-500';
            }

            text.textContent = message;
            detailsElement.textContent = details;
        }

        function updateDevicesTable(devices) {
            const tbody = document.getElementById('devices-table-body');
            tbody.innerHTML = '';

            if (!devices || devices.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td class="border border-gray-800 p-4 h-16 text-center text-gray-500" colspan="3">
                            No se encontraron dispositivos
                        </td>
                    </tr>
                `;
                return;
            }

            devices.forEach(device => {
                const statusClass = getStatusClass(device.status);
                const statusIcon = getStatusIcon(device.status);
                
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 cursor-pointer';
                row.onclick = () => toggleDeviceDetails();
                
                row.innerHTML = `
                    <td class="border border-gray-800 p-4 h-16 text-center font-medium">
                        ${device.id}
                    </td>
                    <td class="border border-gray-800 p-4 h-16 text-center">
                        ${device.name}
                    </td>
                    <td class="border border-gray-800 p-4 h-16 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                            <i class="fas ${statusIcon} mr-1"></i>
                            ${device.status}
                        </span>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
        }

        function updateDeviceDetails(detailsResponse) {
            console.log('Datos recibidos para detalles:', detailsResponse);
            
            if (!detailsResponse || !detailsResponse.success) {
                console.error('Respuesta de detalles no válida');
                return;
            }

            const details = detailsResponse.details || detailsResponse;
            
            // Verificar existencia de elementos antes de actualizarlos
            const safeUpdate = (elementId, value, defaultValue = 'N/A') => {
                const element = document.getElementById(elementId);
                if (element) {
                    element.textContent = value || defaultValue;
                }
            };

            const safeUpdateIcon = (elementId, active) => {
                const element = document.getElementById(elementId);
                if (element) {
                    element.innerHTML = active 
                        ? '<i class="fas fa-circle text-green-500"></i> Activo'
                        : '<i class="fas fa-circle text-red-500"></i> Inactivo';
                }
            };

            // Información básica
            safeUpdate('detail-model', details.device?.model || details.model);
            safeUpdate('detail-type', details.device?.name || details.name);
            safeUpdate('detail-serial', details.device?.serial || details.serial);
            safeUpdate('detail-resolution', details.device?.resolution || details.resolution);

            // Versiones
            safeUpdate('detail-api-version', details.versions?.api || details.api);
            safeUpdate('detail-hw-version', details.versions?.driver || details.driver);

            // Estado del sensor
            const sensorActive = details.status?.sensor || false;
            safeUpdateIcon('detail-sensor-active', sensorActive);

            // Sistema
            safeUpdate('detail-port', details.system?.port);
            safeUpdate('detail-driver', details.system?.driver);
            safeUpdate('detail-os', details.system?.os);
            safeUpdate('detail-arch', details.system?.arch);
        }

        function getStatusClass(status) {
            const classes = {
                'Conectado': 'bg-green-100 text-green-800',
                'Desconectado': 'bg-red-100 text-red-800',
                'Error': 'bg-yellow-100 text-yellow-800',
                'Ocupado': 'bg-blue-100 text-blue-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }

        function getStatusIcon(status) {
            const icons = {
                'Conectado': 'fa-check-circle',
                'Desconectado': 'fa-times-circle',
                'Error': 'fa-exclamation-triangle',
                'Ocupado': 'fa-clock'
            };
            return icons[status] || 'fa-question-circle';
        }

        // === FUNCIONES DE API ===

        async function fetchDevices() {
            try {
                const response = await fetch('/admin/configurar-sistema/configurar-dispositivo/ver-dispositivo-conectado/dispositivo', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Error fetching devices:', error);
                return null;
            }
        }

        async function fetchDeviceDetails() {
            try {
                const response = await fetch('/admin/configurar-sistema/configurar-dispositivo/ver-dispositivo-conectado/dispositivo/detalles', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Respuesta de detalles del dispositivo:', data);
                return data;
            } catch (error) {
                console.error('Error fetching device details:', error);
                return {
                    success: false,
                    error: error.message
                };
            }
        }

        // === FUNCIONES PRINCIPALES ===

        async function refreshDeviceData() {
            const refreshIcon = document.getElementById('refresh-icon');
            const refreshButton = document.getElementById('refresh-button');
            
            // Mostrar estado de carga
            refreshIcon.classList.add('fa-spin');
            refreshButton.disabled = true;

            try {
                // Obtener datos de dispositivos
                const devicesResponse = await fetchDevices();
                
                if (devicesResponse && devicesResponse.success) {
                    deviceData = devicesResponse;
                    updateDevicesTable(devicesResponse.devices);
                    
                    // Actualizar indicador de estado basado en el primer dispositivo
                    if (devicesResponse.devices.length > 0) {
                        const firstDevice = devicesResponse.devices[0];
                        const statusMap = {
                            'Conectado': 'conectado',
                            'Desconectado': 'desconectado',
                            'Error': 'error'
                        };
                        const status = statusMap[firstDevice.status] || 'desconectado';
                        updateStatusIndicator(status, `Estado: ${firstDevice.status}`, `Dispositivo: ${firstDevice.name}`);
                    }
                    
                    // Si hay detalles visibles, actualizarlos también
                    if (!document.getElementById('device-details').classList.contains('hidden')) {
                        const detailsResponse = await fetchDeviceDetails();
                        if (detailsResponse && detailsResponse.success) {
                            updateDeviceDetails(detailsResponse);
                        }
                    }
                } else {
                    updateStatusIndicator('error', 'Error al obtener información', 'No se pudo conectar con el sistema');
                    updateDevicesTable([]);
                }

                // Actualizar timestamp
                document.getElementById('last-update').textContent = 
                    `Última actualización: ${new Date().toLocaleString()}`;

            } catch (error) {
                console.error('Error refreshing device data:', error);
                updateStatusIndicator('error', 'Error de conexión', 'No se pudo obtener la información del dispositivo');
            } finally {
                // Restaurar botón
                refreshIcon.classList.remove('fa-spin');
                refreshButton.disabled = false;
            }
        }

        async function toggleDeviceDetails() {
            const detailsPanel = document.getElementById('device-details');
            
            if (!detailsPanel) {
                console.error('El panel de detalles no fue encontrado en el DOM');
                return;
            }
            
            if (detailsPanel.classList.contains('hidden')) {
                // Mostrar loading state
                const detailsButton = document.querySelector('#devices-table-body tr:first-child td:last-child');
                if (detailsButton) {
                    const originalContent = detailsButton.innerHTML;
                    detailsButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cargando...';
                    
                    try {
                        const detailsResponse = await fetchDeviceDetails();
                        if (detailsResponse && detailsResponse.success) {
                            updateDeviceDetails(detailsResponse);
                            detailsPanel.classList.remove('hidden');
                        } else {
                            throw new Error(detailsResponse?.error || 'Error al cargar detalles');
                        }
                    } catch (error) {
                        console.error('Error al cargar detalles:', error);
                        updateStatusIndicator('error', 'Error al cargar detalles', error.message);
                    } finally {
                        detailsButton.innerHTML = originalContent;
                    }
                }
            } else {
                // Ocultar detalles
                detailsPanel.classList.add('hidden');
            }
        }

        function startAutoRefresh() {
            refreshInterval = setInterval(refreshDeviceData, REFRESH_INTERVAL_MS);
        }

        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }

        // === EVENT LISTENERS ===

        document.getElementById('refresh-button')?.addEventListener('click', refreshDeviceData);
        
        document.getElementById('hide-details-button')?.addEventListener('click', () => {
            document.getElementById('device-details')?.classList.add('hidden');
        });

        // === INICIALIZACIÓN ===

        document.addEventListener('DOMContentLoaded', () => {
            // Cargar datos iniciales
            refreshDeviceData();
            
            // Iniciar actualización automática
            startAutoRefresh();
            
            // Pausar actualización automática cuando la ventana no está visible
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stopAutoRefresh();
                } else {
                    startAutoRefresh();
                }
            });
        });

        // === SCRIPT DE INACTIVIDAD ===
        let timeoutDuration = 2 * 60 * 1000; // 2 min
        let warningDuration = 1 * 60 * 1000; // 1 min
    
        let warningTimer, logoutTimer;
    
        function startTimers() {
            warningTimer = setTimeout(() => {
                document.getElementById('inactivity-modal')?.classList.remove('hidden');
            }, warningDuration);
    
            logoutTimer = setTimeout(() => {
                document.getElementById('inactive').value = '1';
                document.getElementById('logout-form').submit();
            }, timeoutDuration);
        }
    
        function resetTimers() {
            clearTimeout(warningTimer);
            clearTimeout(logoutTimer);
            startTimers();
        }
    
        function closeModal() {
            document.getElementById('inactivity-modal')?.classList.add('hidden');
        }
    
        window.addEventListener('DOMContentLoaded', () => {
            startTimers();
        });
    
        ['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
            window.addEventListener(evt, () => {
                resetTimers();
                closeModal();
            });
        });
    </script>
</body>
</html>