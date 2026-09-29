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

    <!-- CSS -->
    <link
        rel="stylesheet"
        href="../../css/tecnico.css?v=4"
    >

</head>

<body>

    <!--=====================================
    ENCABEZADO
    ======================================-->

    <header class="top-header">

        <!-- MARCA -->
        <a
            href="../../index.php"
            class="brand-logo"
            aria-label="Ir al inicio de RONEM"
        >

            <img
                src="favicoB.png"
                alt="Logo RONEMMA"
                class="brand-logo-image"
                width="48"
                height="48"
            >

            <span class="brand-logo-text">

                <strong>
                    RONEMMA
                </strong>

                <small>
                    ENTERPRISE
                </small>

            </span>

        </a>

        <!-- NAVEGACIÓN -->
        <nav
            class="header-navigation"
            aria-label="Navegación del panel técnico"
        >

            <ul class="nav-tabs">

                <li>

                    <a
                        href="#tab-ordenes"
                        class="nav-link active"
                        data-tab="ordenes"
                    >

                        <span class="nav-icon">
                            ◫
                        </span>

                        Órdenes

                    </a>

                </li>

                <li>

                    <a
                        href="#tab-vehiculos"
                        class="nav-link"
                        data-tab="vehiculos"
                    >

                        <span class="nav-icon">
                            ◆
                        </span>

                        Vehículos

                    </a>

                </li>

                <li>

                    <a
                        href="#tab-repuestos"
                        class="nav-link"
                        data-tab="repuestos"
                    >

                        <span class="nav-icon">
                            ⚙
                        </span>

                        Repuestos

                    </a>

                </li>

                <li>

                    <a
                        href="#tab-manuales"
                        class="nav-link"
                        data-tab="manuales"
                    >

                        <span class="nav-icon">
                            ▤
                        </span>

                        Manuales

                    </a>

                </li>

                <li>

                    <a
                        href="#tab-historial"
                        class="nav-link"
                        data-tab="historial"
                    >

                        <span class="nav-icon">
                            ◷
                        </span>

                        Mi Historial

                    </a>

                </li>

            </ul>

        </nav>

        <!-- MENÚ DEL USUARIO -->
        <div class="user-menu">

            <button
                type="button"
                class="user-menu-button"
                id="btn-user-menu"
                aria-expanded="false"
                aria-controls="user-dropdown"
            >

                <span class="user-avatar">
                    ●
                </span>

                <span class="user-name">
                    Técnico
                </span>

                <span class="user-arrow">
                    ▾
                </span>

            </button>

            <div
                class="user-dropdown"
                id="user-dropdown"
            >

                <div class="user-dropdown-status">

                    <span class="status-dot"></span>

                    Estado:

                    <strong>
                        Activo
                    </strong>

                </div>

                <a href="../../index.php">

                    Página principal

                </a>

                <a
                    href="#tab-historial"
                    data-user-tab="historial"
                >

                    Mi historial

                </a>

                <a
                    href="../../auth/logout.php"
                    class="dropdown-logout"
                >

                    Cerrar sesión

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

            <div
                class="orders-grid"
                id="container-ordenes"
            >

                <!-- Tarjeta de ejemplo -->
                <article class="work-order-card">

                    <div class="order-card-header">

                        <h2 class="order-title">

                            Orden #1024 - Honda CBR 600

                        </h2>

                        <span class="badge badge-repair">

                            En Reparación

                        </span>

                    </div>

                    <div class="order-body">

                        <p>

                            <strong>
                                Falla reportada:
                            </strong>

                            Problema de inyección.

                        </p>

                        <p>

                            <strong>
                                Tiempo trabajado:
                            </strong>

                            2h 30m

                        </p>

                    </div>

                    <div class="order-actions">

                        <button
                            type="button"
                            class="btn btn-secondary"
                        >

                            Editar Estado

                        </button>

                        <button
                            type="button"
                            class="btn btn-secondary"
                        >

                            Solicitar Repuesto

                        </button>

                        <button
                            type="button"
                            class="btn btn-secondary"
                        >

                            Subir Fotografías

                        </button>

                    </div>

                </article>

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
                action="../../api/crear_orden.php"
                method="POST"
            >

                <div class="modal-body">

                    <h3 class="form-section-title">

                        Datos del Vehículo

                    </h3>

                    <!-- FILA 1 -->
                    <div class="form-grid-3">

                        <div class="form-group">

                            <label for="placa">

                                Placa *

                            </label>

                            <input
                                type="text"
                                id="placa"
                                name="Placa"
                                class="form-input"
                                required
                                maxlength="20"
                                placeholder="Ej. M123XYZ"
                            >

                        </div>

                        <div class="form-group">

                            <label for="tipo_vehiculo">

                                Tipo de Vehículo

                            </label>

                            <select
                                id="tipo_vehiculo"
                                name="Tipo_vehiculo"
                                class="form-input"
                            >

                                <option value="Motocicleta">

                                    Motocicleta

                                </option>

                                <option value="Automóvil">

                                    Automóvil

                                </option>

                                <option value="Pickup">

                                    Pickup

                                </option>

                                <option value="Camioneta">

                                    Camioneta

                                </option>

                            </select>

                        </div>

                        <div class="form-group">

                            <label for="combustible">

                                Combustible

                            </label>

                            <select
                                id="combustible"
                                name="Combustible"
                                class="form-input"
                            >

                                <option value="Gasolina">

                                    Gasolina

                                </option>

                                <option value="Diésel">

                                    Diésel

                                </option>

                                <option value="Eléctrico">

                                    Eléctrico

                                </option>

                            </select>

                        </div>

                    </div>

                    <!-- FILA 2 -->
                    <div class="form-grid-3">

                        <div class="form-group">

                            <label for="marca">

                                Marca *

                            </label>

                            <input
                                type="text"
                                id="marca"
                                name="Marca"
                                class="form-input"
                                required
                                maxlength="80"
                                placeholder="Ej. Honda"
                            >

                        </div>

                        <div class="form-group">

                            <label for="linea">

                                Línea / Categoría

                            </label>

                            <input
                                type="text"
                                id="linea"
                                name="Linea"
                                class="form-input"
                                maxlength="100"
                                placeholder="Ej. CBR 600 / Civic"
                            >

                        </div>

                        <div class="form-group">

                            <label for="modelo">

                                Modelo (Año)

                            </label>

                            <input
                                type="number"
                                id="modelo"
                                name="Modelo"
                                class="form-input"
                                min="1900"
                                max="2100"
                                placeholder="Ej. 2022"
                            >

                        </div>

                    </div>

                    <!-- FILA 3 -->
                    <div class="form-grid-3">

                        <div class="form-group">

                            <label for="color">

                                Color

                            </label>

                            <input
                                type="text"
                                id="color"
                                name="Color"
                                class="form-input"
                                maxlength="50"
                                placeholder="Ej. Negro / Rojo"
                            >

                        </div>

                        <div class="form-group">

                            <label for="cilindraje">

                                Cilindraje (cc)

                            </label>

                            <input
                                type="number"
                                id="cilindraje"
                                name="Cilindraje"
                                class="form-input"
                                min="0"
                                placeholder="Ej. 600"
                            >

                        </div>

                        <div class="form-group">

                            <label for="kilometraje">

                                Kilometraje

                            </label>

                            <input
                                type="number"
                                id="kilometraje"
                                name="Kilometraje"
                                class="form-input"
                                min="0"
                                placeholder="Ej. 15000"
                            >

                        </div>

                    </div>

                    <!-- FILA 4 -->
                    <div class="form-grid-2">

                        <div class="form-group">

                            <label for="no_chasis">

                                No. Chasis / VIN

                            </label>

                            <input
                                type="text"
                                id="no_chasis"
                                name="No_chasis"
                                class="form-input"
                                maxlength="100"
                                placeholder="Número de serie"
                            >

                        </div>

                        <div class="form-group">

                            <label for="id_cliente">

                                ID Cliente (Opcional)

                            </label>

                            <input
                                type="number"
                                id="id_cliente"
                                name="Id_cliente"
                                class="form-input"
                                min="1"
                                placeholder="ID en sistema"
                            >

                        </div>

                    </div>

                    <hr class="form-divider">

                    <h3 class="form-section-title">

                        Detalles de la Recepción

                    </h3>

                    <div class="form-group">

                        <label for="falla">

                            Falla Reportada /
                            Trabajo Solicitado *

                        </label>

                        <textarea
                            id="falla"
                            name="falla_reportada"
                            class="form-input"
                            rows="3"
                            required
                            placeholder="Describa el servicio requerido o las fallas mencionadas por el cliente..."
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

                        Registrar e Iniciar Orden

                    </button>

                </div>

            </form>

        </div>

    </div>

    <!-- JAVASCRIPT -->
    <script src="../../js/tecnico.js?v=4"></script>

</body>

</html>