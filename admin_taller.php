<?php

declare(strict_types=1);

require_once __DIR__ . "/config/session.php";
require_once __DIR__ . "/config/conexion.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/permisos.php";

/*=====================================
PROTEGER PANEL
=====================================*/

requiereAutenticacion("index.php");

$rolActivo = strtolower(
    trim((string) ($_SESSION["rol_activo"] ?? ""))
);

if (!in_array($rolActivo, ["admin_taller", "administrador"], true)) {
    redirigir("seleccionar_acceso.php");
}

/*=====================================
DATOS DEL USUARIO ACTUAL
=====================================*/

$nombreSesion = trim(
    (string) (
        $_SESSION["nombre"]
        ?? $_SESSION["Nombre"]
        ?? ""
    )
);

$apellidoSesion = trim(
    (string) (
        $_SESSION["apellido"]
        ?? $_SESSION["Apellido"]
        ?? ""
    )
);

$nombreCompletoSesion = trim(
    $nombreSesion . " " . $apellidoSesion
);

if ($nombreCompletoSesion === "") {
    $nombreCompletoSesion = "Administrador de Taller";
}

$rolVisible = $rolActivo === "administrador"
    ? "Administrador"
    : "Administrador de Taller";

function escapar(string $valor): string
{
    return htmlspecialchars(
        $valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador de Taller | RONEM</title>
    <link rel="icon" type="image/png" href="faviconR.png?v=1">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;600;700&family=PT+Sans:wght@400;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/panel_taller.css?v=3">
</head>
<body>

    <!-- ==================== ENCABEZADO SUPERIOR ==================== -->
    <header class="top-header">
        <a class="brand-logo" href="index.php?modo=sitio" aria-label="Ir a la página principal de RONEM">
            <img src="favicoB.png?v=2" alt="Logo RONEMMA" class="brand-logo-image" width="48" height="48">
            <span class="brand-logo-text">
                <strong>RONEMMA</strong>
                <small>ENTERPRISE</small>
            </span>
        </a>

        <!-- NAVEGACIÓN DEL ADMINISTRADOR DE TALLER -->
        <nav
            class="header-navigation"
            id="nav-jefe"
            aria-label="Navegación del administrador de taller"
        >
            <div class="nav-tabs-ronem">
                <button type="button" class="nav-item-menu active" onclick="switchView('jefe-inicio', this)">
                    <i class="bi bi-grid-1x2"></i><span>Dashboard</span>
                </button>

                <button type="button" class="nav-item-menu" onclick="switchView('jefe-clientes', this)">
                    <i class="bi bi-people"></i><span>Clientes</span>
                </button>

                <button type="button" class="nav-item-menu" onclick="switchView('jefe-vehiculos', this)">
                    <i class="bi bi-car-front"></i><span>Vehículos</span>
                </button>

                <button type="button" class="nav-item-menu" onclick="switchView('jefe-ordenes', this)">
                    <i class="bi bi-file-earmark-text"></i><span>Órdenes</span>
                </button>

                <button type="button" class="nav-item-menu" onclick="switchView('jefe-tecnicos', this)">
                    <i class="bi bi-tools"></i><span>Técnicos</span>
                </button>

                <button type="button" class="nav-item-menu" onclick="switchView('jefe-agenda', this)">
                    <i class="bi bi-calendar3"></i><span>Agenda</span>
                </button>

                <button type="button" class="nav-item-menu" onclick="switchView('jefe-historial', this)">
                    <i class="bi bi-clock-history"></i><span>Historial</span>
                </button>
            </div>
        </nav>

        <div class="user-menu">
            <button type="button" class="user-menu-button" id="userMenuButton" aria-expanded="false">
                <span class="user-avatar"><i class="bi bi-person-fill"></i></span>
                <span class="user-information">
                    <strong class="user-name"><?= escapar($nombreCompletoSesion) ?></strong>
                    <small id="userRoleText"><?= escapar($rolVisible) ?></small>
                </span>
                <i class="bi bi-chevron-down user-arrow"></i>
            </button>

            <div class="user-dropdown" id="userDropdown">
                <div class="user-dropdown-header">
                    <span class="user-dropdown-avatar"><i class="bi bi-person-fill"></i></span>
                    <div>
                        <strong><?= escapar($nombreCompletoSesion) ?></strong>
                        <small id="dropdownRoleText"><?= escapar($rolVisible) ?></small>
                    </div>
                </div>
                <button type="button" class="user-dropdown-link" onclick="switchView('jefe-perfil', null); closeUserMenu();">
                    <i class="bi bi-person-circle"></i> Mi perfil
                </button>
                <a href="index.php?modo=sitio" class="user-dropdown-link">
                    <i class="bi bi-house-door"></i> Página principal
                </a>
                <div class="user-dropdown-divider"></div>
                <a href="auth/logout.php" class="user-dropdown-link dropdown-logout">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </div>
        </div>
    </header>

    <!-- ==================== CONTENIDO PRINCIPAL ==================== -->
    <main class="main-content">
        <div class="panel-toolbar">
            <div class="panel-search">
                <i class="bi bi-search"></i>
                <input type="search" placeholder="Buscar en el sistema..." aria-label="Buscar en el sistema">
            </div>
            <button type="button" class="notification-button" aria-label="Notificaciones">
                <i class="bi bi-bell"></i>
                <span></span>
            </button>
        </div>

        <!-- CONTENEDOR DE VISTAS -->
        <div id="views-container">

            <!-- ==========================================
                 MÓDULO: JEFE DE TALLER 
                 ========================================== -->

            <!-- 1. INICIO (DASHBOARD) -->
            <section id="jefe-inicio" class="view-section active">
                <h1 class="panel-titulo">Dashboard <span>General</span></h1>
                <p class="panel-descripcion">Resumen de la actividad del taller al día de hoy.</p>
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card-moderna d-flex flex-row align-items-center gap-3">
                            <div class="p-3 rounded bg-primary bg-opacity-10 text-primary fs-3"><i class="bi bi-car-front-fill"></i></div>
                            <div><h3 class="m-0 fw-bold" id="dashIngresosHoy">0</h3><small class="text-muted">Ingresos hoy</small></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-moderna d-flex flex-row align-items-center gap-3">
                            <div class="p-3 rounded bg-info bg-opacity-10 text-info fs-3"><i class="bi bi-tools"></i></div>
                            <div><h3 class="m-0 fw-bold" id="dashEnReparacion">0</h3><small class="text-muted">En reparación</small></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-moderna d-flex flex-row align-items-center gap-3">
                            <div class="p-3 rounded bg-warning bg-opacity-10 text-warning fs-3"><i class="bi bi-hourglass-split"></i></div>
                            <div><h3 class="m-0 fw-bold" id="dashPendientes">0</h3><small class="text-muted">Pendientes</small></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-moderna d-flex flex-row align-items-center gap-3">
                            <div class="p-3 rounded bg-success bg-opacity-10 text-success fs-3"><i class="bi bi-check-circle-fill"></i></div>
                            <div><h3 class="m-0 fw-bold" id="dashFinalizadosSemana">0</h3><small class="text-muted">Finalizados esta semana</small></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold m-0">Trabajos Recientes</h5>
                            <button class="btn btn-danger btn-sm" onclick="switchView('jefe-ordenes', document.querySelector('.bi-file-earmark-text').parentNode)">Ver Todos</button>
                        </div>
                        <table class="table w-100 table-dark-modern">
                            <thead>
                                <tr><th>Orden</th><th>Cliente</th><th>Vehículo</th><th>Estado</th></tr>
                            </thead>
                            <tbody id="tbodyTrabajosRecientes">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Cargando trabajos recientes...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-lg-4">
                        <h5 class="fw-bold mb-3">Carga de Técnicos</h5>
                        <div class="card-moderna" id="contenedorCargaTecnicos">
                            <p class="text-muted m-0 text-center">
                                Cargando disponibilidad de técnicos...
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. CLIENTES -->
            <section id="jefe-clientes" class="view-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="panel-titulo">Gestión de <span>Clientes</span></h1>
                        <p class="panel-descripcion">
                            Consulta usuarios con rol cliente y los vehículos asociados.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="btn btn-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#modalCliente"
                    >
                        <i class="bi bi-plus-lg"></i>
                        Nuevo cliente
                    </button>
                </div>

                <div class="card-moderna">
                    <div class="d-flex gap-2 mb-4">
                        <input
                            type="search"
                            class="form-control"
                            id="buscarCliente"
                            placeholder="Buscar por nombre, apellido, correo, teléfono o DPI..."
                        >

                        <button type="button" class="btn btn-outline-secondary" id="btnActualizarClientes">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table w-100 table-dark-modern">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>DPI</th>
                                    <th>Teléfono</th>
                                    <th>Correo</th>
                                    <th>Vehículos</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>

                            <tbody id="tbodyClientesTaller">
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        Cargando clientes...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- 4. ÓRDENES DE TRABAJO (VISTA PRINCIPAL SOLICITADA) -->
            <section id="jefe-ordenes" class="view-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="panel-titulo">Órdenes de <span>Trabajo</span></h1>
                        <p class="panel-descripcion">Control total de reparaciones y mantenimientos.</p>
                    </div>
                    <button class="btn btn-danger px-4 py-2" data-bs-toggle="modal" data-bs-target="#wizardModal">
                        <i class="bi bi-plus-circle me-2"></i>Nueva Orden
                    </button>
                </div>

                <!-- Filtros -->
                <div class="row mb-4">
                    <div class="col-md-3"><select class="form-select" id="filtroEstadoOrden"><option value="">Estado: todos</option><option value="Pendiente">Pendiente</option><option value="Diagnóstico">Diagnóstico</option><option value="En proceso">En proceso</option><option value="Esperando repuestos">Esperando repuestos</option><option value="Finalizada">Finalizada</option><option value="Entregada">Entregada</option><option value="Cancelada">Cancelada</option></select></div>
                    <div class="col-md-3"><select class="form-select" id="filtroTecnicoOrden"><option value="">Técnico: todos</option></select></div>
                    <div class="col-md-6"><input type="search" class="form-control" id="buscarOrdenTaller" placeholder="Buscar por orden, cliente, placa, vehículo o servicio..."></div>
                </div>

                <table class="table w-100 table-dark-modern">
                    <thead><tr><th>ID</th><th>Cliente / Placa</th><th>Servicio</th><th>Técnico</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody id="tbodyOrdenesTaller">
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Cargando órdenes de trabajo...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- 3. VEHÍCULOS -->
            <section id="jefe-vehiculos" class="view-section">
                <h1 class="panel-titulo">Gestión de <span>Vehículos</span></h1>
                <p class="panel-descripcion">
                    Consulta vehículos registrados y su propietario.
                </p>

                <div class="card-moderna">
                    <div class="d-flex gap-2 mb-4">
                        <input
                            type="search"
                            class="form-control"
                            id="buscarVehiculoTaller"
                            placeholder="Buscar por placa, marca, línea, modelo, chasis o cliente..."
                        >
                        <button type="button" class="btn btn-outline-secondary" id="btnActualizarVehiculos">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table w-100 table-dark-modern">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Propietario</th>
                                    <th>Vehículo</th>
                                    <th>Tipo</th>
                                    <th>Placa</th>
                                    <th>Chasis</th>
                                    <th>Kilometraje</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyVehiculosTaller">
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        Cargando vehículos...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- 5. TÉCNICOS -->
            <section id="jefe-tecnicos" class="view-section">
                <h1 class="panel-titulo">Equipo <span>Técnico</span></h1>
                <p class="panel-descripcion">
                    Consulta técnicos registrados y asigna órdenes de trabajo.
                </p>

                <div class="card-moderna">
                    <div class="d-flex gap-2 mb-4">
                        <input
                            type="search"
                            class="form-control"
                            id="buscarTecnicoTaller"
                            placeholder="Buscar por nombre, correo, teléfono o estado..."
                        >
                        <button type="button" class="btn btn-outline-secondary" id="btnActualizarTecnicos">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table w-100 table-dark-modern">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Técnico</th>
                                    <th>Correo</th>
                                    <th>Teléfono</th>
                                    <th>Órdenes activas</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyTecnicosTaller">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        Cargando técnicos...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- 6. AGENDA -->
            <section id="jefe-agenda" class="view-section">
                <h1 class="panel-titulo">Agenda del <span>Taller</span></h1>
                <p class="panel-descripcion">
                    Fechas de ingreso y entrega de las órdenes.
                </p>

                <div class="card-moderna">
                    <div id="contenedorAgendaTaller" class="text-center text-muted p-4">
                        Cargando agenda...
                    </div>
                </div>
            </section>

            <!-- 7. HISTORIAL -->
            <section id="jefe-historial" class="view-section">
                <h1 class="panel-titulo">Historial del <span>Taller</span></h1>
                <p class="panel-descripcion">
                    Consulta órdenes finalizadas, entregadas o canceladas.
                </p>

                <div class="card-moderna">
                    <input
                        type="search"
                        class="form-control mb-4"
                        id="buscarHistorialTaller"
                        placeholder="Buscar por orden, cliente, vehículo, placa, técnico o estado..."
                    >

                    <div class="table-responsive">
                        <table class="table w-100 table-dark-modern">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Cliente</th>
                                    <th>Vehículo</th>
                                    <th>Servicio</th>
                                    <th>Técnico</th>
                                    <th>Ingreso</th>
                                    <th>Entrega</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyHistorialTaller">
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        Cargando historial...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- PERFIL -->
            <section id="jefe-perfil" class="view-section">
                <h1 class="panel-titulo">Mi <span>Perfil</span></h1>

                <div class="card-moderna">
                    <h4><?= escapar($nombreCompletoSesion) ?></h4>
                    <p class="text-muted mb-1"><?= escapar($rolVisible) ?></p>
                    <p class="text-muted mb-0">
                        ID de usuario:
                        <?= escapar((string) ($_SESSION["id_usuario"] ?? "")) ?>
                    </p>
                </div>
            </section>

        </div>
    </main>

    <!-- ==================== MODALES ==================== -->
    <!-- WIZARD NUEVA ORDEN (Asistente) -->
    <div class="modal fade" id="wizardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background: #101010; border: 1px solid var(--ronem-borde); border-radius: 20px;">
                <div class="modal-header border-0 pb-0 mt-3 mx-3">
                    <h5 class="modal-title fw-bold font-league-spartan fs-3">Asistente: <span class="text-danger">Nueva Orden</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <!-- Indicadores de pasos -->
                    <div class="wizard-header px-4">
                        <div class="step-indicator active" id="ind-1">1</div>
                        <div class="step-indicator" id="ind-2">2</div>
                        <div class="step-indicator" id="ind-3">3</div>
                        <div class="step-indicator" id="ind-4">4</div>
                    </div>

                    <!-- Paso 1: Cliente -->
                    <div class="wizard-step active" id="step-1">
                        <h5 class="text-danger mb-3">Paso 1: Selección de Cliente</h5>
                        <div class="mb-3">
                            <label class="form-label text-muted">Buscar o Seleccionar Cliente</label>
                            <select class="form-select" id="ordenCliente" name="Id_usuario" required>
                                <option value="">Seleccione un cliente registrado...</option>
                            </select>
                        </div>
                        <div class="text-end mt-4"><button class="btn btn-danger px-4" onclick="nextStep(2)">Siguiente <i class="bi bi-chevron-right"></i></button></div>
                    </div>

                    <!-- Paso 2: Vehículo -->
                    <div class="wizard-step" id="step-2">
                        <h5 class="text-danger mb-3">Paso 2: Información del Vehículo</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Vehículo del Cliente</label>
                                <select class="form-select" id="ordenVehiculo" name="Id_vehiculo" required disabled><option value="">Seleccione primero un cliente...</option></select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Kilometraje Actual</label>
                                <input type="number" class="form-control" id="ordenKilometraje" name="Kilometraje" min="0" placeholder="Ej. 45000">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button class="btn btn-outline-light px-4" onclick="nextStep(1)"><i class="bi bi-chevron-left"></i> Atrás</button>
                            <button class="btn btn-danger px-4" onclick="nextStep(3)">Siguiente <i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>

                    <!-- Paso 3: Información -->
                    <div class="wizard-step" id="step-3">
                        <h5 class="text-danger mb-3">Paso 3: Detalles de la Orden</h5>
                        <div class="mb-3">
                            <label class="form-label text-muted">Problema Reportado (Motivo de ingreso)</label>
                            <textarea class="form-control" id="ordenDescripcion" name="Descripcion" rows="3" required></textarea>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button class="btn btn-outline-light px-4" onclick="nextStep(2)"><i class="bi bi-chevron-left"></i> Atrás</button>
                            <button class="btn btn-danger px-4" onclick="nextStep(4)">Siguiente <i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>

                    <!-- Paso 4: Asignar Técnico & Fin -->
                    <div class="wizard-step" id="step-4">
                        <h5 class="text-danger mb-3">Paso 4: Asignar Técnico y Confirmar</h5>
                        <div class="mb-4">
                            <label class="form-label text-muted">Técnico Asignado</label>
                            <select class="form-select" id="ordenTecnico" name="Id_tecnico">
                                <option value="">Sin asignar</option>
                            </select>
                        </div>
                        <div class="alert alert-dark border-secondary">
                            <i class="bi bi-info-circle text-danger me-2"></i>Al confirmar, se generará el PDF de la orden y pasará al tablero principal.
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button class="btn btn-outline-light px-4" onclick="nextStep(3)"><i class="bi bi-chevron-left"></i> Atrás</button>
                            <button type="button" class="btn btn-success px-4" id="btnGuardarOrdenTaller"><i class="bi bi-check-lg"></i> Confirmar orden</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin_taller.js?v=1"></script>
    <script>
        // Lógica SPA (Navegación sin recargar página)
        function switchView(viewId, element) {
            // Ocultar todas las vistas
            document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
            // Mostrar la seleccionada
            document.getElementById(viewId).classList.add('active');
            
            // Actualizar menú activo
            document.querySelectorAll('.nav-item-menu').forEach(el => el.classList.remove('active'));
            if(element) element.classList.add('active');
        }

        const vistaInicial = "jefe-inicio";

        document.addEventListener("DOMContentLoaded", function () {
            switchView(
                vistaInicial,
                document.querySelector("#nav-jefe .nav-item-menu:first-child")
            );
        });

        // Menú del usuario
        const userMenuButton = document.getElementById('userMenuButton');
        const userDropdown = document.getElementById('userDropdown');

        function closeUserMenu() {
            userDropdown.classList.remove('active');
            userMenuButton.classList.remove('active');
            userMenuButton.setAttribute('aria-expanded', 'false');
        }

        userMenuButton.addEventListener('click', function (event) {
            event.stopPropagation();
            const abierto = userDropdown.classList.toggle('active');
            userMenuButton.classList.toggle('active', abierto);
            userMenuButton.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.user-menu')) closeUserMenu();
        });

        // Lógica del Wizard Modal
        function nextStep(stepNumber) {
            document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
            document.getElementById('step-' + stepNumber).classList.add('active');
            
            document.querySelectorAll('.step-indicator').forEach(el => {
                el.classList.remove('active');
                if (parseInt(el.innerText) < stepNumber) el.classList.add('completed');
                else el.classList.remove('completed');
            });
            document.getElementById('ind-' + stepNumber).classList.add('active');
        }

        // Reseteo de Wizard al cerrar
        document.getElementById('wizardModal').addEventListener('hidden.bs.modal', function () {
            nextStep(1);
        });
    </script>
</body>
</html>