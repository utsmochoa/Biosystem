<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reportes del Sistema</title>
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet"> <!-- Local FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-100 to-blue-300">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6 relative">
            <!-- Botón Volver en la esquina -->
            <a href="{{ url()->previous() }}"
            class="absolute top-4 left-4 px-4 text-blue-700 hover:text-blue-900 rounded-lg transition duration-200 z-10">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>

            <!-- Contenido centrado -->
            <div class="flex flex-col items-center">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-16 h-16 mb-2">
                <h1 class="text-3xl font-bold text-blue-700 flex items-center">
                    <i class="fas fa-chart-line mr-2"></i>
                    Reportes del Sistema
                </h1>
            </div>

            <!-- Selector de Tipo de Reporte -->
            <div class="flex justify-center space-x-4 mt-6">
                <button onclick="cambiarTipoReporte('estudiantes')" 
                        id="btnEstudiantes"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center active-tab">
                    <i class="fas fa-user-graduate mr-2"></i>
                    Reportes de Estudiantes
                </button>
                <button onclick="cambiarTipoReporte('usuarios')" 
                        id="btnUsuarios"
                        class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center">
                    <i class="fas fa-users mr-2"></i>
                    Reportes de Usuarios
                </button>
            </div>
        </div>




        <!-- Filtros y Búsqueda -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-filter mr-2 text-blue-600"></i>
                Filtros de Búsqueda
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Búsqueda General -->
                <div class="relative">
                    <input type="text" 
                           id="searchInput" 
                           placeholder="Buscar en todos los campos..." 
                           class="w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                
                <!-- Filtro por Tipo de Acción -->
                <select id="filtroAccion" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
                    <option value="">Todos los tipos de acción</option>
                    <option value="registro">Registro</option>
                    <option value="actualizacion">Actualización</option>
                    <option value="inicio_sesion">Inicio de Sesión</option>
                    <option value="cierre_sesion">Cierre de Sesión</option>
                    <option value="verificacion">Verificación</option>
                </select>
                
                <!-- Filtro por Fecha -->
                <input type="date" 
                       id="filtroFecha" 
                       class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
            </div>
            
            <!-- Botón Limpiar Filtros -->
            <div class="flex justify-end">
                <button onclick="limpiarFiltros()" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center">
                    <i class="fas fa-eraser mr-2"></i>
                    Limpiar Filtros
                </button>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <i class="fas fa-database text-blue-500 text-2xl mr-4"></i>
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Registros</p>
                        <p class="text-2xl font-bold text-blue-700" id="totalRegistros">0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <i class="fas fa-eye text-green-500 text-2xl mr-4"></i>
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Registros Visibles</p>
                        <p class="text-2xl font-bold text-green-700" id="registrosVisibles">0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <i class="fas fa-clock text-yellow-500 text-2xl mr-4"></i>
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Hoy</p>
                        <p class="text-2xl font-bold text-yellow-700" id="registrosHoy">0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <i class="fas fa-calendar-week text-purple-500 text-2xl mr-4"></i>
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Esta Semana</p>
                        <p class="text-2xl font-bold text-purple-700" id="registrosSemana">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Datos -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-table mr-2 text-blue-600"></i>
                    <span id="tituloTabla">Historial de Accesos de Estudiantes</span>
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-500 text-white">
                        <tr id="tablaHeader">
                            <!-- Headers se generan dinámicamente -->
                        </tr>
                    </thead>
                    <tbody id="tablaBody">
                        <!-- Datos se cargan dinámicamente -->
                    </tbody>
                </table>
            </div>
            
            <!-- Mensaje cuando no hay datos -->
            <div id="sinDatos" class="p-8 text-center text-gray-500 hidden">
                <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                <p class="text-lg">No se encontraron registros</p>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            <span class="text-lg">Cargando datos...</span>
        </div>
    </div>

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
        let timeoutDuration = 2 * 60 * 1000; // 2 min
        let warningDuration = 1 * 60 * 1000; // 1 min
    
        let warningTimer, logoutTimer;
    
        function startTimers() {
            warningTimer = setTimeout(() => {
                document.getElementById('inactivity-modal').classList.remove('hidden');
            }, warningDuration);
    
            logoutTimer = setTimeout(() => {
                document.getElementById('inactive').value = '1';
                document.getElementById('logout-form').submit(); // se hace POST correctamente
            }, timeoutDuration);
    
    
        }
    
        function resetTimers() {
            clearTimeout(warningTimer);
            clearTimeout(logoutTimer);
            startTimers();
        }
    
        function closeModal() {
            document.getElementById('inactivity-modal').classList.add('hidden');
        }
    
        // Inicia los temporizadores al cargar la página
        window.addEventListener('DOMContentLoaded', () => {
            startTimers();
        });
    
        // Resetea los temporizadores con actividad del usuario
        ['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
            window.addEventListener(evt, () => {
                resetTimers();
                closeModal(); // Solo se cierra si el usuario se mueve
            });
        });
    

    // Variables globales
    let datosOriginales = [];
    let datosFiltrados = [];
    let tipoReporteActual = 'estudiantes';

    let paginaActual = 1;
    const registrosPorPagina = 10;

    document.addEventListener('DOMContentLoaded', function () {
        cargarDatos();
        document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
        document.getElementById('filtroAccion').addEventListener('change', aplicarFiltros);
        document.getElementById('filtroFecha').addEventListener('change', aplicarFiltros);
    });

    function cambiarTipoReporte(tipo) {
        tipoReporteActual = tipo;
        paginaActual = 1;

        document.getElementById('btnEstudiantes').className = tipo === 'estudiantes'
            ? 'px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center active-tab'
            : 'px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center';

        document.getElementById('btnUsuarios').className = tipo === 'usuarios'
            ? 'px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center active-tab'
            : 'px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center';

        document.getElementById('tituloTabla').textContent = tipo === 'estudiantes'
            ? 'Historial de Accesos de Estudiantes'
            : 'Historial de Usuarios';

        cargarDatos();
    }

    function cargarDatos() {
        mostrarLoading(true);
        const endpoint = tipoReporteActual === 'estudiantes'
            ? '{{ route("reportes.estudiantes") }}'
            : '{{ route("reportes.usuarios") }}';

        fetch(endpoint, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                datosOriginales = data.data;
                datosFiltrados = [...datosOriginales];
                paginaActual = 1;
                generarTabla();
                actualizarEstadisticas();
            } else {
                console.error('Error al cargar datos:', data.message);
                mostrarSinDatos();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarSinDatos();
        })
        .finally(() => {
            mostrarLoading(false);
        });
    }

    function generarTabla() {
        const header = document.getElementById('tablaHeader');
        const body = document.getElementById('tablaBody');
        const sinDatos = document.getElementById('sinDatos');

        header.innerHTML = '';
        body.innerHTML = '';

        if (datosFiltrados.length === 0) {
            sinDatos.classList.remove('hidden');
            return;
        }

        sinDatos.classList.add('hidden');
        const columnas = Object.keys(datosFiltrados[0]);

        columnas.forEach(columna => {
            const th = document.createElement('th');
            th.className = 'px-6 py-3 text-left text-xs font-medium uppercase tracking-wider';
            th.textContent = formatearNombreColumna(columna);
            header.appendChild(th);
        });

        const inicio = (paginaActual - 1) * registrosPorPagina;
        const fin = inicio + registrosPorPagina;
        const datosPagina = datosFiltrados.slice(inicio, fin);

        datosPagina.forEach((fila, index) => {
            const tr = document.createElement('tr');
            tr.className = index % 2 === 0
                ? 'bg-white hover:bg-blue-50 transition-colors duration-150'
                : 'bg-blue-50 hover:bg-blue-100 transition-colors duration-150';

            columnas.forEach(columna => {
                const td = document.createElement('td');
                td.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';

                let valor = fila[columna];
                if (columna === 'fecha_hora' && valor) {
                    valor = new Date(valor).toLocaleString('es-ES');
                }

                if (columna === 'tipo_accion') {
                    td.innerHTML = crearBadgeTipoAccion(valor);
                } else {
                    td.textContent = valor || 'N/A';
                }

                tr.appendChild(td);
            });

            body.appendChild(tr);
        });

        generarControlesPaginacion();
    }

    function generarControlesPaginacion() {
        const totalPaginas = Math.ceil(datosFiltrados.length / registrosPorPagina);
        const contenedor = document.getElementById('tablaBody').parentElement;

        const paginacionExistente = document.getElementById('paginacion');
        if (paginacionExistente) paginacionExistente.remove();

        const paginacion = document.createElement('div');
        paginacion.id = 'paginacion';
        paginacion.className = 'flex justify-center items-center py-4 space-x-2';

        const btnAnterior = document.createElement('button');
        btnAnterior.textContent = 'Anterior';
        btnAnterior.className = 'px-4 py-2 bg-blue-500 text-white rounded disabled:opacity-50';
        btnAnterior.disabled = paginaActual === 1;
        btnAnterior.onclick = () => {
            if (paginaActual > 1) {
                paginaActual--;
                generarTabla();
            }
        };

        const info = document.createElement('span');
        info.className = 'text-sm text-gray-700';
        info.textContent = `Página ${paginaActual} de ${totalPaginas}`;

        const btnSiguiente = document.createElement('button');
        btnSiguiente.textContent = 'Siguiente';
        btnSiguiente.className = 'px-4 py-2 bg-blue-500 text-white rounded disabled:opacity-50';
        btnSiguiente.disabled = paginaActual >= totalPaginas;
        btnSiguiente.onclick = () => {
            if (paginaActual < totalPaginas) {
                paginaActual++;
                generarTabla();
            }
        };

        paginacion.appendChild(btnAnterior);
        paginacion.appendChild(info);
        paginacion.appendChild(btnSiguiente);

        contenedor.after(paginacion);
    }

    function crearBadgeTipoAccion(tipo) {
        const colores = {
            'registro': 'bg-green-100 text-green-800',
            'actualizacion': 'bg-yellow-100 text-yellow-800',
            'inicio_sesion': 'bg-blue-100 text-blue-800',
            'cierre_sesion': 'bg-red-100 text-red-800',
            'verificacion': 'bg-purple-100 text-purple-800'
        };
        const color = colores[tipo] || 'bg-gray-100 text-gray-800';
        return `<span class="px-2 py-1 text-xs font-semibold rounded-full ${color}">${tipo}</span>`;
    }

    function formatearNombreColumna(nombre) {
        const mapeo = {
            'id': 'ID',
            'users_id': 'Usuario ID',
            'estudiante_id': 'Estudiante ID',
            'tipo_accion': 'Tipo de Acción',
            'descripcion': 'Descripción',
            'fecha_hora': 'Fecha y Hora'
        };
        return mapeo[nombre] || nombre.replace('_', ' ').toUpperCase();
    }

    function aplicarFiltros() {
        const busqueda = document.getElementById('searchInput').value.toLowerCase();
        const filtroAccion = document.getElementById('filtroAccion').value;
        const filtroFecha = document.getElementById('filtroFecha').value;

        datosFiltrados = datosOriginales.filter(fila => {
            if (busqueda) {
                const textoFila = Object.values(fila).join(' ').toLowerCase();
                if (!textoFila.includes(busqueda)) return false;
            }

            if (filtroAccion && fila.tipo_accion !== filtroAccion) return false;

            if (filtroFecha && fila.fecha_hora) {
                const fechaRegistro = new Date(fila.fecha_hora).toISOString().split('T')[0];
                if (fechaRegistro !== filtroFecha) return false;
            }

            return true;
        });

        paginaActual = 1;
        generarTabla();
        actualizarEstadisticas();
    }

    function actualizarEstadisticas() {
        document.getElementById('totalRegistros').textContent = datosOriginales.length;
        document.getElementById('registrosVisibles').textContent = datosFiltrados.length;

        const hoy = new Date().toISOString().split('T')[0];
        const registrosHoy = datosOriginales.filter(fila => {
            if (!fila.fecha_hora) return false;
            return new Date(fila.fecha_hora).toISOString().split('T')[0] === hoy;
        }).length;

        const haceUnaSemana = new Date();
        haceUnaSemana.setDate(haceUnaSemana.getDate() - 7);
        const registrosSemana = datosOriginales.filter(fila => {
            if (!fila.fecha_hora) return false;
            return new Date(fila.fecha_hora) >= haceUnaSemana;
        }).length;

        document.getElementById('registrosHoy').textContent = registrosHoy;
        document.getElementById('registrosSemana').textContent = registrosSemana;
    }

    function limpiarFiltros() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filtroAccion').value = '';
        document.getElementById('filtroFecha').value = '';
        datosFiltrados = [...datosOriginales];
        paginaActual = 1;
        generarTabla();
        actualizarEstadisticas();
    }

    function mostrarLoading(mostrar) {
        const overlay = document.getElementById('loadingOverlay');
        if (mostrar) {
            overlay.classList.remove('hidden');
        } else {
            overlay.classList.add('hidden');
        }
    }

    function mostrarSinDatos() {
        document.getElementById('sinDatos').classList.remove('hidden');
        document.getElementById('tablaHeader').innerHTML = '';
        document.getElementById('tablaBody').innerHTML = '';
        const paginacion = document.getElementById('paginacion');
        if (paginacion) paginacion.remove();
    }
</script>

</body>
</html>