<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$nombreSesion = trim((string) ($_SESSION["nombre"] ?? $_SESSION["Nombre"] ?? ""));
$apellidoSesion = trim((string) ($_SESSION["apellido"] ?? $_SESSION["Apellido"] ?? ""));
$nombreCompletoSesion = trim($nombreSesion . " " . $apellidoSesion);

if ($nombreCompletoSesion === "") {
    $nombreCompletoSesion = "Técnico";
}

$rolSesion = trim((string) (
    $_SESSION["rol_activo"]
    ?? $_SESSION["rol"]
    ?? $_SESSION["Rol"]
    ?? "Técnico"
));

if ($rolSesion === "") {
    $rolSesion = "Técnico";
}

$rolSesion = ucfirst($rolSesion);

?>
<!DOCTYPE html>
 <!-- panel_tecnico.php -->
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Panel de Técnico - RONEM</title>

    <!-- Imagen de la pestaña -->
    <link
        rel="icon"
        type="image/png"
        href="faviconR.png"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <!-- CSS -->
    <link rel="stylesheet" href="css/tecnico.css?v=11">

</head>

<body>

    <!--=====================================
    ENCABEZADO
    ======================================-->

    <header class="top-header">

        <a
            href="index.php?modo=sitio"
            class="brand-logo"
            aria-label="Ir a la página principal de RONEM"
        >
            <img
                src="favicoB.png?v=2"
                alt="Logo RONEMMA"
                class="brand-logo-image"
                width="48"
                height="48"
                onerror="this.style.visibility='hidden'"
            >

            <span class="brand-logo-text">
                <strong>RONEMMA</strong>
                <small>ENTERPRISE</small>
            </span>
        </a>

        <nav
            class="header-navigation"
            aria-label="Navegación del panel técnico"
        >
            <ul class="nav-tabs">
                <li>
                    <a href="#tab-ordenes" class="nav-link active" data-tab="ordenes">
                        <i class="bi bi-clipboard2-check nav-icon"></i>
                        <span>Órdenes</span>
                    </a>
                </li>

                <li>
                    <a href="#tab-vehiculos" class="nav-link" data-tab="vehiculos">
                        <i class="bi bi-car-front-fill nav-icon"></i>
                        <span>Vehículos</span>
                    </a>
                </li>

                <li>
                    <a href="#tab-repuestos" class="nav-link" data-tab="repuestos">
                        <i class="bi bi-gear-fill nav-icon"></i>
                        <span>Repuestos</span>
                    </a>
                </li>

                <li>
                    <a href="#tab-manuales" class="nav-link" data-tab="manuales">
                        <i class="bi bi-journal-text nav-icon"></i>
                        <span>Manuales</span>
                    </a>
                </li>

                <li>
                    <a href="#tab-historial" class="nav-link" data-tab="historial">
                        <i class="bi bi-clock-history nav-icon"></i>
                        <span>Mi Historial</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="user-menu">
            <button
                type="button"
                class="user-menu-button"
                id="btn-user-menu"
                aria-expanded="false"
                aria-controls="user-dropdown"
            >
                <span class="user-avatar">
                    <i class="bi bi-person-fill"></i>
                </span>

                <span class="user-information">
                    <strong class="user-name">
                        <?= htmlspecialchars($nombreCompletoSesion, ENT_QUOTES, "UTF-8") ?>
                    </strong>

                    <small class="user-role">
                        <?= htmlspecialchars($rolSesion, ENT_QUOTES, "UTF-8") ?>
                    </small>
                </span>

                <i class="bi bi-chevron-down user-arrow"></i>
            </button>

            <div class="user-dropdown" id="user-dropdown">
                <div class="user-dropdown-header">
                    <span class="user-dropdown-avatar">
                        <i class="bi bi-person-fill"></i>
                    </span>

                    <div>
                        <strong>
                            <?= htmlspecialchars($nombreCompletoSesion, ENT_QUOTES, "UTF-8") ?>
                        </strong>

                        <small>
                            <?= htmlspecialchars($rolSesion, ENT_QUOTES, "UTF-8") ?>
                        </small>
                    </div>
                </div>

                <div class="user-dropdown-divider"></div>

                <a href="index.php?modo=sitio">
                    <i class="bi bi-house-door"></i>
                    <span>Página principal</span>
                </a>

                <div class="user-dropdown-divider"></div>

                <a href="auth/logout.php" class="dropdown-logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Cerrar sesión</span>
                </a>
            </div>
        </div>

    </header>

    <!--=====================================
    CONTENIDO PRINCIPAL
    ======================================-->

    <main class="dashboard-container">

        <!--=====================================
        PESTAÑA 1: ÓRDENES DE TRABAJO
        ======================================-->

        <section
            id="tab-ordenes"
            class="tab-content active"
        >

            <div class="section-header">

                <div>

                    <span class="section-label">
                        GESTIÓN DE TALLER
                    </span>

                    <h1 class="section-title">

                        MIS

                        <span>
                            TRABAJOS
                        </span>

                    </h1>

                    <p class="section-description">

                        Consulta y administra las órdenes de trabajo
                        que tienes asignadas.

                    </p>

                </div>

                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="openModal('modal-nueva-orden')"
                >

                    <span class="button-plus">
                        +
                    </span>

                    NUEVA ORDEN

                </button>

            </div>

            <div class="filters-container">

                <div class="search-box">

                    <span class="search-icon">
                        ⌕
                    </span>

                    <input
                        type="text"
                        id="buscar-orden"
                        class="form-input search-input"
                        placeholder="Buscar por orden, cliente, vehículo, placa o servicio..."
                    >

                </div>

                <div class="form-group">

                    <select
                        id="filtro-estado-orden"
                        class="form-input"
                        aria-label="Filtrar órdenes por estado"
                    >

                        <option value="todos">
                            Todos los estados
                        </option>

                        <option value="Recibida">
                            Recibida
                        </option>

                        <option value="En Diagnostico">
                            En Diagnóstico
                        </option>

                        <option value="Esperando Repuesto">
                            Esperando Repuesto
                        </option>

                        <option value="En Reparacion">
                            En Reparación
                        </option>

                        <option value="Prueba de Ruta">
                            Prueba de Ruta
                        </option>

                        <option value="Lista para Entrega">
                            Lista para Entrega
                        </option>

                        <option value="Entregada">
                            Entregada
                        </option>

                        <option value="Cancelada">
                            Cancelada
                        </option>

                        <option value="Garantia">
                            Garantía
                        </option>

                        <option value="Finalizada">
                            Finalizada
                        </option>

                    </select>

                </div>

            </div>

            <div id="container-ordenes">

                <p class="text-muted">

                    Cargando órdenes de trabajo...

                </p>

            </div>

        </section>

        <!--=====================================
        PESTAÑA 2: VEHÍCULOS
        ======================================-->

        <section
            id="tab-vehiculos"
            class="tab-content"
        >

            <div class="section-header">

                <div>

                    <span class="section-label">
                        VEHÍCULOS DEL TALLER
                    </span>

                    <h1 class="section-title">

                        GESTIÓN DE

                        <span>
                            VEHÍCULOS
                        </span>

                    </h1>

                    <p class="section-description">

                        Consulta los vehículos relacionados con las
                        órdenes de servicio.

                    </p>

                </div>

            </div>

            <div class="filters-container">

                <div class="search-box">

                    <span class="search-icon">
                        ⌕
                    </span>

                    <input
                        type="text"
                        id="buscar-vehiculo"
                        class="form-input search-input"
                        placeholder="Buscar por placa, marca, línea o chasis..."
                    >

                </div>

            </div>

            <div id="container-vehiculos">

                <p class="text-muted">

                    Cargando vehículos del taller...

                </p>

            </div>

        </section>

        <!--=====================================
        PESTAÑA 3: REPUESTOS
        ======================================-->

        <section
            id="tab-repuestos"
            class="tab-content"
        >

            <div class="section-header">

                <div>

                    <span class="section-label">
                        INVENTARIO
                    </span>

                    <h1 class="section-title">

                        STOCK DE

                        <span>
                            REPUESTOS
                        </span>

                    </h1>

                    <p class="section-description">

                        Consulta la disponibilidad de repuestos
                        utilizados en los servicios.

                    </p>

                </div>

            </div>

            <div class="filters-container">

                <div class="search-box">

                    <span class="search-icon">
                        ⌕
                    </span>

                    <input
                        type="text"
                        id="buscar-repuesto"
                        class="form-input search-input"
                        placeholder="Buscar por nombre, descripción o stock..."
                    >

                </div>

            </div>

            <div id="container-repuestos">

                <p class="text-muted">

                    Cargando inventario de repuestos...

                </p>

            </div>

        </section>

        <!--=====================================
        PESTAÑA 4: MANUALES
        ======================================-->

        <section
            id="tab-manuales"
            class="tab-content"
        >

            <div class="section-header">

                <div>

                    <span class="section-label">
                        DOCUMENTACIÓN
                    </span>

                    <h1 class="section-title">

                        MANUALES

                        <span>
                            TÉCNICOS
                        </span>

                    </h1>

                    <p class="section-description">

                        Consulta documentación y material técnico
                        para realizar los servicios.

                    </p>

                </div>

                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="openModal('modal-manual-tecnico')"
                >

                    <span class="button-plus">
                        +
                    </span>

                    SUBIR MANUAL

                </button>

            </div>

            <div class="filters-container">

                <div class="search-box">

                    <span class="search-icon">
                        ⌕
                    </span>

                    <input
                        type="text"
                        id="buscar-manual"
                        class="form-input search-input"
                        placeholder="Buscar por título, marca, modelo o categoría..."
                    >

                </div>

            </div>

            <div id="container-manuales">

                <p class="text-muted">

                    Cargando catálogo de manuales...

                </p>

            </div>

        </section>

        <!--=====================================
        PESTAÑA 5: HISTORIAL
        ======================================-->

        <section
            id="tab-historial"
            class="tab-content"
        >

            <div class="section-header">

                <div>

                    <span class="section-label">
                        REGISTRO DE ACTIVIDAD
                    </span>

                    <h1 class="section-title">

                        MI

                        <span>
                            HISTORIAL
                        </span>

                    </h1>

                    <p class="section-description">

                        Consulta el registro completo de trabajos
                        y servicios realizados.

                    </p>

                </div>

            </div>

            <div class="filters-container">

                <div class="search-box">

                    <span class="search-icon">
                        ⌕
                    </span>

                    <input
                        type="text"
                        id="buscar-historial"
                        class="form-input search-input"
                        placeholder="Buscar por orden, estado, vehículo, placa o técnico..."
                    >

                </div>

            </div>

            <div id="container-historial">

                <p class="text-muted">

                    Cargando historial de trabajos...

                </p>

            </div>

        </section>

    </main>

    <!--=====================================
    MODAL: NUEVA ORDEN
    ======================================-->

    <div
        class="modal-overlay"
        id="modal-nueva-orden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-modal-nueva-orden"
        aria-hidden="true"
    >

        <div class="modal-container modal-lg">

            <div class="modal-header">

                <div>

                    <span class="modal-label">
                        GESTIÓN DEL TALLER
                    </span>

                    <h2 id="titulo-modal-nueva-orden">

                        Crear Orden de Trabajo

                    </h2>

                </div>

                <button
                    type="button"
                    class="btn-close-modal"
                    onclick="closeModal('modal-nueva-orden')"
                    aria-label="Cerrar ventana"
                >

                    &times;

                </button>

            </div>

            <form
                id="form-nueva-orden"
                method="POST"
            >

                <div class="modal-body">

                    <h3 class="form-section-title">

                        Relación de la Orden

                    </h3>

                    <div class="form-grid-2">

                        <div class="form-group">

                            <label for="id_usuario">

                                ID Usuario *

                            </label>

                            <input
                                type="number"
                                id="id_usuario"
                                name="Id_usuario"
                                class="form-input"
                                min="1"
                                required
                                placeholder="ID del propietario"
                            >

                        </div>

                        <div class="form-group">

                            <label for="id_vehiculo">

                                ID Vehículo *

                            </label>

                            <input
                                type="number"
                                id="id_vehiculo"
                                name="Id_vehiculo"
                                class="form-input"
                                min="1"
                                required
                                placeholder="ID del vehículo registrado"
                            >

                        </div>

                    </div>

                    <hr class="form-divider">

                    <h3 class="form-section-title">

                        Detalles del Servicio

                    </h3>

                    <div class="form-grid-2">

                        <div class="form-group">

                            <label for="servicio">

                                Servicio / Trabajo solicitado *

                            </label>

                            <input
                                type="text"
                                id="servicio"
                                name="Servicio"
                                class="form-input"
                                maxlength="150"
                                required
                                placeholder="Ej. Mantenimiento general"
                            >

                        </div>

                        <div class="form-group">

                            <label for="estado_orden">

                                Estado inicial

                            </label>

                            <select
                                id="estado_orden"
                                name="Estado"
                                class="form-input"
                            >

                                <option value="Recibida">
                                    Recibida
                                </option>

                                <option value="En Diagnostico">
                                    En Diagnóstico
                                </option>

                                <option value="Esperando Repuesto">
                                    Esperando Repuesto
                                </option>

                                <option value="En Reparacion">
                                    En Reparación
                                </option>

                                <option value="Prueba de Ruta">
                                    Prueba de Ruta
                                </option>

                                <option value="Lista para Entrega">
                                    Lista para Entrega
                                </option>

                                <option value="Entregada">
                                    Entregada
                                </option>

                                <option value="Cancelada">
                                    Cancelada
                                </option>

                                <option value="Garantia">
                                    Garantía
                                </option>

                                <option value="Finalizada">
                                    Finalizada
                                </option>

                            </select>

                        </div>

                    </div>

                    <div class="form-grid-3">

                        <div class="form-group">

                            <label for="fecha_ingreso">

                                Fecha de ingreso

                            </label>

                            <input
                                type="datetime-local"
                                id="fecha_ingreso"
                                name="Fecha_ingreso"
                                class="form-input"
                            >

                        </div>

                        <div class="form-group">

                            <label for="fecha_entrega">

                                Fecha estimada de entrega

                            </label>

                            <input
                                type="datetime-local"
                                id="fecha_entrega"
                                name="Fecha_entrega"
                                class="form-input"
                            >

                        </div>

                        <div class="form-group">

                            <label for="costo_orden">

                                Costo estimado (Q)

                            </label>

                            <input
                                type="number"
                                id="costo_orden"
                                name="Costo"
                                class="form-input"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                            >

                        </div>

                    </div>

                    <div class="form-group">

                        <label for="descripcion_orden">

                            Descripción / Falla reportada *

                        </label>

                        <textarea
                            id="descripcion_orden"
                            name="Descripcion"
                            class="form-input"
                            rows="4"
                            required
                            placeholder="Describe las fallas, observaciones y el trabajo solicitado..."
                        ></textarea>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="closeModal('modal-nueva-orden')"
                    >

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        Registrar Orden

                    </button>

                </div>

            </form>

        </div>

    </div>

    <!--=====================================
    MODAL: SUBIR MANUAL
    ======================================-->

    <div
        class="modal-overlay"
        id="modal-manual-tecnico"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-modal-manual"
        aria-hidden="true"
    >

        <div class="modal-container">

            <div class="modal-header">

                <div>

                    <span class="modal-label">
                        DOCUMENTACIÓN TÉCNICA
                    </span>

                    <h2 id="titulo-modal-manual">

                        Subir Manual Técnico

                    </h2>

                </div>

                <button
                    type="button"
                    class="btn-close-modal"
                    onclick="closeModal('modal-manual-tecnico')"
                    aria-label="Cerrar ventana"
                >

                    &times;

                </button>

            </div>

            <form
                id="form-manual-tecnico"
                method="POST"
                enctype="multipart/form-data"
            >

                <div class="modal-body">

                    <div class="form-group">

                        <label for="titulo-manual">

                            Título *

                        </label>

                        <input
                            type="text"
                            id="titulo-manual"
                            name="titulo"
                            class="form-input"
                            maxlength="150"
                            required
                            placeholder="Ej. Manual de servicio Honda CBR 600"
                        >

                    </div>

                    <div class="form-grid-2">

                        <div class="form-group">

                            <label for="marca-manual">

                                Marca

                            </label>

                            <input
                                type="text"
                                id="marca-manual"
                                name="marca"
                                class="form-input"
                                maxlength="100"
                                placeholder="Ej. Honda"
                            >

                        </div>

                        <div class="form-group">

                            <label for="modelo-manual">

                                Modelo

                            </label>

                            <input
                                type="text"
                                id="modelo-manual"
                                name="modelo"
                                class="form-input"
                                maxlength="100"
                                placeholder="Ej. CBR 600"
                            >

                        </div>

                    </div>

                    <div class="form-group">

                        <label for="categoria-manual">

                            Categoría

                        </label>

                        <input
                            type="text"
                            id="categoria-manual"
                            name="categoria"
                            class="form-input"
                            maxlength="100"
                            placeholder="Ej. Motor, electricidad o mantenimiento"
                        >

                    </div>

                    <div class="form-group">

                        <label for="descripcion-manual">

                            Descripción

                        </label>

                        <textarea
                            id="descripcion-manual"
                            name="descripcion"
                            class="form-input"
                            rows="3"
                            placeholder="Describe brevemente el contenido del manual..."
                        ></textarea>

                    </div>

                    <div class="form-group">

                        <label for="archivo-manual">

                            Archivo PDF *

                        </label>

                        <input
                            type="file"
                            id="archivo-manual"
                            name="archivo"
                            class="form-input"
                            accept=".pdf,application/pdf"
                            required
                        >

                        <small
                            id="nombre-archivo-manual"
                            class="text-muted"
                        >

                            Ningún archivo seleccionado

                        </small>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="closeModal('modal-manual-tecnico')"
                    >

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        Guardar Manual

                    </button>

                </div>

            </form>

        </div>

    </div>

    <!-- JAVASCRIPT -->
    <script src="js/tecnico.js?v=5"></script>
    <script src="js/tecnico_ordenes.js?v=2"></script>
    <script src="js/tecnico_vehiculos.js?v=1"></script>
    <script src="js/tecnico_repuestos.js?v=999"></script>
    <script src="js/tecnico_manuales.js?v=2"></script>
    <script src="js/tecnico_historial.js?v=1"></script>

</body>

</html>