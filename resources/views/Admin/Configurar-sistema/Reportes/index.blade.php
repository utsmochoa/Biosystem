<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reportes del Sistema</title>
    <link href="{{ asset('fontawesome/css/all.min.css') }}" rel="stylesheet"> <!-- Local FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Agregar bibliotecas para PDF y Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-100 to-blue-300 animate-fade-in">
    <div class="container mx-auto px-4 py-8 animate-fade-in">
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
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-600"></i>
                    Filtros de Búsqueda
                </h2>
                
                <!-- Botones de Descarga -->
                <div class="flex space-x-2">
                    <button onclick="descargarPDF()" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Descargar PDF
                    </button>
                    <button onclick="descargarExcel()" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center">
                        <i class="fas fa-file-excel mr-2"></i>
                        Descargar Excel
                    </button>
                </div>
            </div>
            
            <!-- Filtros para Estudiantes -->
            <div id="filtrosEstudiantes" class="space-y-4">
                <!-- Primera fila de filtros -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Búsqueda por Nombre -->
                    <div class="relative">
                        <input type="text" 
                               id="filtroNombreEst" 
                               placeholder="Buscar por nombre..." 
                               class="w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500">
                        <i class="fas fa-user absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    
                    <!-- Búsqueda por Apellido -->
                    <div class="relative">
                        <input type="text" 
                               id="filtroApellidoEst" 
                               placeholder="Buscar por apellido..." 
                               class="w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500">
                        <i class="fas fa-user-tag absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    
                    <!-- Búsqueda por Cédula -->
                    <div class="relative">
                        <input type="text" 
                               id="filtroCedula" 
                               placeholder="Buscar por cédula..." 
                               class="w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500">
                        <i class="fas fa-id-card absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    
                    <!-- Búsqueda por Carrera -->
                    <div class="relative">
                        <input type="text" 
                               id="filtroCarrera" 
                               placeholder="Buscar por carrera..." 
                               class="w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500">
                        <i class="fas fa-graduation-cap absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Segunda fila de filtros -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Filtro por Semestre -->
                    <div class="relative">
                        <input type="text" 
                               id="filtroSemestre" 
                               placeholder="Buscar por semestre..." 
                               class="w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500">
                        <i class="fas fa-layer-group absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    
                    <!-- Filtro por Fecha -->
                    <input type="date" 
                           id="filtroFechaEst" 
                           class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
                    
                    <!-- Búsqueda General -->
                    <div class="relative">
                        <input type="text" 
                               id="busquedaGeneralEst" 
                               placeholder="Búsqueda general..." 
                               class="w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>
            
            <!-- Filtros para Usuarios -->
            <div id="filtrosUsuarios" class="space-y-4 hidden">
                <!-- Primera fila de filtros -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Búsqueda por Nombre de Usuario -->
                    <div class="relative">
                        <input type="text" 
                               id="filtroNombreUsuario" 
                               placeholder="Buscar por nombre..." 
                               class="w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500">
                        <i class="fas fa-user absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    
                    <!-- Filtro por Rol -->
                    <select id="filtroRolUsuarios" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
                        <option value="">Todos los roles</option>
                        <option value="admin">Administrador</option>
                        <option value="operador">Operador</option>
                    </select>
                    
                    <!-- Filtro por Tipo de Acción -->
                    <select id="filtroAccionUsuarios" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
                        <option value="">Todos los tipos de acción</option>
                        <option value="registro">Registro</option>
                        <option value="actualizacion">Actualización</option>
                        <option value="inicio_sesion">Inicio de Sesión</option>
                        <option value="cierre_sesion">Cierre de Sesión</option>
                        <option value="verificacion">Verificación</option>
                        <option value="eliminacion">Eliminación</option>
                        <option value="creacion">Creación</option>
                    </select>
                    
                    <!-- Filtro por Fecha -->
                    <input type="date" 
                           id="filtroFechaUsuarios" 
                           class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
                </div>
                
                <!-- Segunda fila de filtros -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Búsqueda General -->
                    <div class="relative">
                        <input type="text" 
                               id="busquedaGeneralUsuarios" 
                               placeholder="Búsqueda general..." 
                               class="w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    
                    <!-- Filtro por ID de Usuario -->
                    <div class="relative">
                        <input type="number" 
                               id="filtroIdUsuario" 
                               placeholder="Buscar por ID de usuario..." 
                               class="w-full border-2 border-gray-300 rounded-lg pl-10 py-2 focus:outline-none focus:border-blue-500">
                        <i class="fas fa-hashtag absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>
            
            <!-- Botón Limpiar Filtros -->
            <div class="flex justify-end mt-4">
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
            <div id="paginacionContainer" class="w-full flex justify-center mt-1"></div>

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
                document.getElementById('logout-form').submit();
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
                closeModal();
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
            
            // Event listeners para filtros de estudiantes
            document.getElementById('filtroNombreEst').addEventListener('input', aplicarFiltros);
            document.getElementById('filtroApellidoEst').addEventListener('input', aplicarFiltros);
            document.getElementById('filtroCedula').addEventListener('input', aplicarFiltros);
            document.getElementById('filtroCarrera').addEventListener('input', aplicarFiltros);
            document.getElementById('filtroSemestre').addEventListener('input', aplicarFiltros);
            document.getElementById('filtroFechaEst').addEventListener('change', aplicarFiltros);
            document.getElementById('busquedaGeneralEst').addEventListener('input', aplicarFiltros);
            
            // Event listeners para filtros de usuarios
            document.getElementById('filtroNombreUsuario').addEventListener('input', aplicarFiltros);
            document.getElementById('filtroRolUsuarios').addEventListener('change', aplicarFiltros);
            document.getElementById('filtroAccionUsuarios').addEventListener('change', aplicarFiltros);
            document.getElementById('filtroFechaUsuarios').addEventListener('change', aplicarFiltros);
            document.getElementById('busquedaGeneralUsuarios').addEventListener('input', aplicarFiltros);
            document.getElementById('filtroIdUsuario').addEventListener('input', aplicarFiltros);
        });

        function cambiarTipoReporte(tipo) {
            tipoReporteActual = tipo;
            paginaActual = 1;

            // Cambiar estilos de botones
            document.getElementById('btnEstudiantes').className = tipo === 'estudiantes'
                ? 'px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center active-tab'
                : 'px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center';

            document.getElementById('btnUsuarios').className = tipo === 'usuarios'
                ? 'px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center active-tab'
                : 'px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center';

            // Cambiar título
            document.getElementById('tituloTabla').textContent = tipo === 'estudiantes'
                ? 'Historial de Accesos de Estudiantes'
                : 'Historial de Usuarios';

            // Mostrar/ocultar filtros apropiados
            if (tipo === 'estudiantes') {
                document.getElementById('filtrosEstudiantes').classList.remove('hidden');
                document.getElementById('filtrosUsuarios').classList.add('hidden');
            } else {
                document.getElementById('filtrosEstudiantes').classList.add('hidden');
                document.getElementById('filtrosUsuarios').classList.remove('hidden');
            }

            // Limpiar filtros antes de cambiar
            limpiarFiltros();
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

            // Definir columnas según el tipo de reporte
            let columnasFinales;
            if (tipoReporteActual === 'estudiantes') {
                columnasFinales = ['id', 'nombre_completo', 'cedula', 'carrera', 'semestre', 'tipo_accion', 'descripcion', 'fecha_hora'];
            } else {
                columnasFinales = ['id', 'nombre_usuario', 'rol', 'tipo_accion', 'descripcion', 'fecha_hora'];
            }

            // Generar headers
            columnasFinales.forEach(columna => {
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

                columnasFinales.forEach(columna => {
                    const td = document.createElement('td');
                    td.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';

                    let valor = fila[columna];
                    
                    if (columna === 'fecha_hora' && valor) {
                        valor = new Date(valor).toLocaleString('es-ES', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit'
                        });
                    }

                    if (columna === 'tipo_accion' && valor) {
                        td.innerHTML = crearBadgeTipoAccion(valor);
                    } else if (columna === 'cedula' && valor) {
                        td.textContent = valor;
                        td.className += ' font-mono';
                    } else if (columna === 'rol' && valor) {
                        td.innerHTML = crearBadgeRol(valor);
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
            const contenedor = document.getElementById('paginacionContainer');

            // Limpiar paginacion previa
            contenedor.innerHTML = '';

            if (totalPaginas <= 1) return;

            const paginacion = document.createElement('div');
            paginacion.id = 'paginacion';
            paginacion.className = 'flex justify-center gap-2 py-2';

            const btnAnterior = document.createElement('button');
            btnAnterior.innerHTML = '<i class="fas fa-chevron-left mr-1"></i> Anterior';
            btnAnterior.className = 'px-4 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition';
            btnAnterior.disabled = paginaActual === 1;
            btnAnterior.onclick = () => {
                if (paginaActual > 1) {
                    paginaActual--;
                    generarTabla();
                }
            };

            const btnSiguiente = document.createElement('button');
            btnSiguiente.innerHTML = 'Siguiente <i class="fas fa-chevron-right ml-1"></i>';
            btnSiguiente.className = 'px-4 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition';
            btnSiguiente.disabled = paginaActual === totalPaginas;
            btnSiguiente.onclick = () => {
                if (paginaActual < totalPaginas) {
                    paginaActual++;
                    generarTabla();
                }
            };

            paginacion.appendChild(btnAnterior);

            // Números de página con ventana dinámica
            const inicioVentana = Math.max(1, paginaActual - 2);
            const finVentana = Math.min(totalPaginas, paginaActual + 2);

            for (let i = inicioVentana; i <= finVentana; i++) {
                const btnPagina = document.createElement('button');
                btnPagina.textContent = i;
                btnPagina.className = i === paginaActual
                    ? 'px-3 py-1.5 text-sm bg-blue-800 text-white rounded-md'
                    : 'px-3 py-1.5 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition cursor-pointer';

                btnPagina.onclick = () => {
                    paginaActual = i;
                    generarTabla();
                };
                paginacion.appendChild(btnPagina);
            }

            paginacion.appendChild(btnSiguiente);
            contenedor.appendChild(paginacion);
        }


        function aplicarFiltros() {
            let filtrados = [...datosOriginales];

            if (tipoReporteActual === 'estudiantes') {
                // Filtros específicos para estudiantes
                const nombre = document.getElementById('filtroNombreEst').value.toLowerCase();
                const apellido = document.getElementById('filtroApellidoEst').value.toLowerCase();
                const cedula = document.getElementById('filtroCedula').value.toLowerCase();
                const carrera = document.getElementById('filtroCarrera').value.toLowerCase();
                const semestre = document.getElementById('filtroSemestre').value.toLowerCase();
                const fecha = document.getElementById('filtroFechaEst').value;
                const busquedaGeneral = document.getElementById('busquedaGeneralEst').value.toLowerCase();

                filtrados = filtrados.filter(item => {
                    const matchNombre = !nombre || (item.nombres && item.nombres.toLowerCase().includes(nombre));
                    const matchApellido = !apellido || (item.apellidos && item.apellidos.toLowerCase().includes(apellido));
                    const matchCedula = !cedula || (item.cedula && item.cedula.toLowerCase().includes(cedula));
                    const matchCarrera = !carrera || (item.carrera && item.carrera.toLowerCase().includes(carrera));
                    const matchSemestre = !semestre || (item.semestre && item.semestre.toLowerCase().includes(semestre));
                    
                    let matchFecha = true;
                    if (fecha) {
                        const fechaItem = new Date(item.fecha_hora).toISOString().split('T')[0];
                        matchFecha = fechaItem === fecha;
                    }

                    let matchGeneral = true;
                    if (busquedaGeneral) {
                        const textoBusqueda = `${item.nombre_completo || ''} ${item.cedula || ''} ${item.carrera || ''} ${item.semestre || ''} ${item.tipo_accion || ''} ${item.descripcion || ''}`.toLowerCase();
                        matchGeneral = textoBusqueda.includes(busquedaGeneral);
                    }

                    return matchNombre && matchApellido && matchCedula && matchCarrera && matchSemestre && matchFecha && matchGeneral;
                });
            } else {
                // Filtros específicos para usuarios
                const nombreUsuario = document.getElementById('filtroNombreUsuario').value.toLowerCase();
                const rol = document.getElementById('filtroRolUsuarios').value;
                const accion = document.getElementById('filtroAccionUsuarios').value;
                const fecha = document.getElementById('filtroFechaUsuarios').value;
                const busquedaGeneral = document.getElementById('busquedaGeneralUsuarios').value.toLowerCase();
                const idUsuario = document.getElementById('filtroIdUsuario').value;

                filtrados = filtrados.filter(item => {
                    const matchNombre = !nombreUsuario || (item.nombre_usuario && item.nombre_usuario.toLowerCase().includes(nombreUsuario));
                    const matchRol = !rol || item.rol === rol;
                    const matchAccion = !accion || item.tipo_accion === accion;
                    const matchId = !idUsuario || item.user_id == idUsuario;
                    
                    let matchFecha = true;
                    if (fecha) {
                        const fechaItem = new Date(item.fecha_hora).toISOString().split('T')[0];
                        matchFecha = fechaItem === fecha;
                    }

                    let matchGeneral = true;
                    if (busquedaGeneral) {
                        const textoBusqueda = `${item.nombre_usuario || ''} ${item.rol || ''} ${item.tipo_accion || ''} ${item.descripcion || ''}`.toLowerCase();
                        matchGeneral = textoBusqueda.includes(busquedaGeneral);
                    }

                    return matchNombre && matchRol && matchAccion && matchId && matchFecha && matchGeneral;
                });
            }

            datosFiltrados = filtrados;
            paginaActual = 1;
            generarTabla();
            actualizarEstadisticas();
        }

        function limpiarFiltros() {
            if (tipoReporteActual === 'estudiantes') {
                document.getElementById('filtroNombreEst').value = '';
                document.getElementById('filtroApellidoEst').value = '';
                document.getElementById('filtroCedula').value = '';
                document.getElementById('filtroCarrera').value = '';
                document.getElementById('filtroSemestre').value = '';
                document.getElementById('filtroFechaEst').value = '';
                document.getElementById('busquedaGeneralEst').value = '';
            } else {
                document.getElementById('filtroNombreUsuario').value = '';
                document.getElementById('filtroRolUsuarios').value = '';
                document.getElementById('filtroAccionUsuarios').value = '';
                document.getElementById('filtroFechaUsuarios').value = '';
                document.getElementById('busquedaGeneralUsuarios').value = '';
                document.getElementById('filtroIdUsuario').value = '';
            }
            
            datosFiltrados = [...datosOriginales];
            paginaActual = 1;
            generarTabla();
            actualizarEstadisticas();
        }

        function formatearNombreColumna(columna) {
            const nombres = {
                'id': 'ID',
                'nombre_completo': 'Nombre Completo',
                'cedula': 'Cédula',
                'carrera': 'Carrera',
                'semestre': 'Semestre',
                'tipo_accion': 'Tipo de Acción',
                'descripcion': 'Descripción',
                'fecha_hora': 'Fecha y Hora',
                'nombre_usuario': 'Usuario',
                'rol': 'Rol'
            };
            return nombres[columna] || columna.replace('_', ' ').toUpperCase();
        }

        function crearBadgeTipoAccion(tipo) {
            const colores = {
                'habilitacion': 'bg-green-100 text-green-800',
                'deshabilitacion': 'bg-red-100 text-red-800',
                'verificacion': 'bg-blue-100 text-blue-800',
                'registro': 'bg-purple-100 text-purple-800',
                'actualizacion': 'bg-yellow-100 text-yellow-800',
                'inicio_sesion': 'bg-green-100 text-green-800',
                'cierre_sesion': 'bg-red-100 text-red-800',
                'eliminacion': 'bg-red-100 text-red-800',
                'creacion': 'bg-blue-100 text-blue-800'
            };
            
            const color = colores[tipo] || 'bg-gray-100 text-gray-800';
            return `<span class="px-2 py-1 rounded-full text-xs font-medium ${color}">${tipo.replace('_', ' ').toUpperCase()}</span>`;
        }

        function crearBadgeRol(rol) {
            const colores = {
                'admin': 'bg-red-100 text-red-800',
                'operador': 'bg-blue-100 text-blue-800'
            };
            
            const color = colores[rol] || 'bg-gray-100 text-gray-800';
            return `<span class="px-2 py-1 rounded-full text-xs font-medium ${color}">${rol.toUpperCase()}</span>`;
        }

        function actualizarEstadisticas() {
            const total = datosOriginales.length;
            const visibles = datosFiltrados.length;
            
            const hoy = new Date();
            const inicioHoy = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
            const inicioSemana = new Date(hoy);
            inicioSemana.setDate(hoy.getDate() - hoy.getDay());
            inicioSemana.setHours(0, 0, 0, 0);
            
            const registrosHoy = datosOriginales.filter(item => {
                const fechaItem = new Date(item.fecha_hora);
                return fechaItem >= inicioHoy;
            }).length;
            
            const registrosSemana = datosOriginales.filter(item => {
                const fechaItem = new Date(item.fecha_hora);
                return fechaItem >= inicioSemana;
            }).length;
            
            document.getElementById('totalRegistros').textContent = total;
            document.getElementById('registrosVisibles').textContent = visibles;
            document.getElementById('registrosHoy').textContent = registrosHoy;
            document.getElementById('registrosSemana').textContent = registrosSemana;
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
        }

        
          // FUNCIONES DE DESCARGA
          function descargarPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4'); // Orientación horizontal para mejor ajuste

            // Título del reporte
            const titulo = tipoReporteActual === 'estudiantes' 
                ? 'Historial de Accesos de Estudiantes' 
                : 'Historial de Usuarios';
            
            doc.setFontSize(18);
            doc.setTextColor(40, 116, 166); // Color azul
            doc.text(titulo, doc.internal.pageSize.width / 2, 20, { align: 'center' });

            // Fecha de generación
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text(`Generado el: ${new Date().toLocaleString('es-ES')}`, 14, 30);
            doc.text(`Total de registros: ${datosFiltrados.length}`, 14, 35);

            // Preparar datos para la tabla
            let columnas, filas;
            
            if (tipoReporteActual === 'estudiantes') {
                columnas = ['ID', 'Nombre Completo', 'Cédula', 'Carrera', 'Semestre', 'Tipo Acción', 'Descripción', 'Fecha/Hora'];
                filas = datosFiltrados.map(item => [
                    item.id || '',
                    item.nombre_completo || '',
                    item.cedula || '',
                    item.carrera || '',
                    item.semestre || '',
                    item.tipo_accion || '',
                    item.descripcion || '',
                    item.fecha_hora ? new Date(item.fecha_hora).toLocaleString('es-ES') : ''
                ]);
            } else {
                columnas = ['ID', 'Usuario', 'Rol', 'Tipo Acción', 'Descripción', 'Fecha/Hora'];
                filas = datosFiltrados.map(item => [
                    item.id || '',
                    item.nombre_usuario || '',
                    item.rol || '',
                    item.tipo_accion || '',
                    item.descripcion || '',
                    item.fecha_hora ? new Date(item.fecha_hora).toLocaleString('es-ES') : ''
                ]);
            }

            // Generar tabla con autoTable
            doc.autoTable({
                head: [columnas],
                body: filas,
                startY: 45,
                styles: {
                    fontSize: 8,
                    cellPadding: 2
                },
                headStyles: {
                    fillColor: [59, 130, 246], // Color azul
                    textColor: 255
                },
                alternateRowStyles: {
                    fillColor: [248, 250, 252] // Color gris claro alternado
                },
                margin: { top: 45, left: 14, right: 14 }
            });

            // Descargar el archivo
            const nombreArchivo = `reporte_${tipoReporteActual}_${new Date().toISOString().split('T')[0]}.pdf`;
            doc.save(nombreArchivo);
        }

        function descargarExcel() {
            // Preparar datos para Excel
            let datosExcel;
            
            if (tipoReporteActual === 'estudiantes') {
                datosExcel = datosFiltrados.map(item => ({
                    'ID': item.id || '',
                    'Nombre Completo': item.nombre_completo || '',
                    'Cédula': item.cedula || '',
                    'Carrera': item.carrera || '',
                    'Semestre': item.semestre || '',
                    'Tipo de Acción': item.tipo_accion || '',
                    'Descripción': item.descripcion || '',
                    'Fecha y Hora': item.fecha_hora ? new Date(item.fecha_hora).toLocaleString('es-ES') : ''
                }));
            } else {
                datosExcel = datosFiltrados.map(item => ({
                    'ID': item.id || '',
                    'Usuario': item.nombre_usuario || '',
                    'Rol': item.rol || '',
                    'Tipo de Acción': item.tipo_accion || '',
                    'Descripción': item.descripcion || '',
                    'Fecha y Hora': item.fecha_hora ? new Date(item.fecha_hora).toLocaleString('es-ES') : ''
                }));
            }

            // Crear workbook y worksheet
            const workbook = XLSX.utils.book_new();
            const worksheet = XLSX.utils.json_to_sheet(datosExcel);

            // Configurar anchos de columna
            const columnWidths = [];
            if (tipoReporteActual === 'estudiantes') {
                columnWidths.push(
                    { wch: 8 },  // ID
                    { wch: 25 }, // Nombre Completo
                    { wch: 15 }, // Cédula
                    { wch: 20 }, // Carrera
                    { wch: 10 }, // Semestre
                    { wch: 15 }, // Tipo de Acción
                    { wch: 30 }, // Descripción
                    { wch: 20 }  // Fecha y Hora
                );
            } else {
                columnWidths.push(
                    { wch: 8 },  // ID
                    { wch: 20 }, // Usuario
                    { wch: 12 }, // Rol
                    { wch: 15 }, // Tipo de Acción
                    { wch: 30 }, // Descripción
                    { wch: 20 }  // Fecha y Hora
                );
            }
            worksheet['!cols'] = columnWidths;

            // Agregar información adicional en las primeras filas
            const titulo = tipoReporteActual === 'estudiantes' 
                ? 'Historial de Accesos de Estudiantes' 
                : 'Historial de Usuarios';
            
            XLSX.utils.sheet_add_aoa(worksheet, [
                [titulo],
                [`Generado el: ${new Date().toLocaleString('es-ES')}`],
                [`Total de registros: ${datosFiltrados.length}`],
                [] // Fila vacía
            ], { origin: 'A1' });

            // Agregar el worksheet al workbook
            XLSX.utils.book_append_sheet(workbook, worksheet, tipoReporteActual === 'estudiantes' ? 'Estudiantes' : 'Usuarios');

            // Descargar el archivo
            const nombreArchivo = `reporte_${tipoReporteActual}_${new Date().toISOString().split('T')[0]}.xlsx`;
            XLSX.writeFile(workbook, nombreArchivo);
        }
    </script>
</body>
</html>