<?php

session_start();

//=====================================
// VALIDAR SESIÓN
//=====================================

if (!isset($_SESSION["id_usuario"])) {

    header("Location: ../../index.php");
    exit;

}

$nombreUsuario = $_SESSION["usuario"] ?? "Cliente";

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mis Vehículos | RONEM</title>

    <!-- ICONO -->
    <link
        rel="icon"
        href="../../favicoB.png?v=2"
    >

    <!-- BOOTSTRAP -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- BOOTSTRAP ICONS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <!-- GOOGLE FONTS -->
    <link
        href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@300;400;500;600;700&family=PT+Sans:wght@400;700&display=swap"
        rel="stylesheet"
    >

    <!-- CSS DEL MÓDULO -->
    <link
        rel="stylesheet"
        href="../../css/vehiculos.css?v=1.0"
    >

</head>

<body>

<!--=====================================
BARRA DE NAVEGACIÓN
======================================-->

<nav class="navbar navbar-expand-lg navbar-dark fixed-top panel-navbar">

    <div class="container-fluid px-3 px-lg-5">

        <!-- LOGO -->
        <a
            class="navbar-brand d-flex align-items-center gap-2"
            href="../../index.php"
        >

            <img
                src="../../favicoB.png?v=2"
                alt="Logo RONEM"
                class="panel-logo"
            >

            <div class="d-flex flex-column">

                <span class="panel-brand">
                    RONEMMA
                </span>

                <span class="panel-brand-sub">
                    ENTERPRISE
                </span>

            </div>

        </a>

        <!-- BOTÓN MÓVIL -->
        <button
            class="navbar-toggler border-0"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarCliente"
            aria-controls="navbarCliente"
            aria-expanded="false"
            aria-label="Abrir menú"
        >

            <span class="navbar-toggler-icon"></span>

        </button>

        <!-- CONTENIDO DEL MENÚ -->
        <div
            class="collapse navbar-collapse"
            id="navbarCliente"
        >

            <!-- ENLACES -->
            <ul class="navbar-nav mx-auto mb-3 mb-lg-0 gap-lg-2">

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="../../index.php"
                    >

                        <i class="bi bi-house-door me-1"></i>
                        Inicio

                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link active"
                        href="vehiculos.php"
                    >

                        <i class="bi bi-scooter me-1"></i>
                        Mis vehículos

                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="historial.php"
                    >

                        <i class="bi bi-clock-history me-1"></i>
                        Historial

                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="fidelizacion.php"
                    >

                        <i class="bi bi-star me-1"></i>
                        Fidelización

                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="motoescuela.php"
                    >

                        <i class="bi bi-mortarboard me-1"></i>
                        Moto Escuela

                    </a>

                </li>

            </ul>

            <!-- USUARIO -->
            <div class="dropdown">

                <button
                    class="btn btn-outline-light dropdown-toggle rounded-pill px-3"
                    type="button"
                    id="dropdownUsuario"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >

                    <i class="bi bi-person-circle me-1"></i>

                    <?= htmlspecialchars(
                        $nombreUsuario,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>

                </button>

                <ul
                    class="dropdown-menu dropdown-menu-end dropdown-menu-dark panel-dropdown"
                    aria-labelledby="dropdownUsuario"
                >

                    <li>

                        <a
                            class="dropdown-item"
                            href="../../index.php"
                        >

                            <i class="bi bi-house me-2"></i>
                            Página principal

                        </a>

                    </li>

                    <li>

                        <hr class="dropdown-divider">

                    </li>

                    <li>

                        <a
                            class="dropdown-item text-danger"
                            href="../../auth/logout.php"
                        >

                            <i class="bi bi-box-arrow-right me-2"></i>
                            Cerrar sesión

                        </a>

                    </li>

                </ul>

            </div>

        </div>

    </div>

</nav>

<!--=====================================
CONTENIDO PRINCIPAL
======================================-->

<main class="panel-contenido">

    <div class="container-fluid px-3 px-lg-5">

        <!-- ENCABEZADO -->
        <section class="panel-encabezado">

            <div class="row align-items-center g-4">

                <div class="col-12 col-lg-8">

                    <h1 class="panel-titulo">

                        MIS

                        <span>
                            VEHÍCULOS
                        </span>

                    </h1>

                    <p class="panel-descripcion">

                        Registra y administra los vehículos asociados
                        a tu cuenta de RONEM.

                    </p>

                </div>

                <div class="col-12 col-lg-4 text-lg-end">

                    <button
                        type="button"
                        class="btn btn-danger btn-nuevo-vehiculo"
                        id="btnNuevoVehiculo"
                        data-bs-toggle="modal"
                        data-bs-target="#modalVehiculo"
                    >

                        <i class="bi bi-plus-lg me-2"></i>
                        AGREGAR VEHÍCULO

                    </button>

                </div>

            </div>

        </section>

        <!--=====================================
        TARJETAS DE RESUMEN
        ======================================-->

        <section class="row g-3 mb-4">

            <!-- TOTAL -->
            <div class="col-12 col-md-4">

                <div class="resumen-card">

                    <div class="resumen-icono">

                        <i class="bi bi-scooter"></i>

                    </div>

                    <div>

                        <span
                            class="resumen-numero"
                            id="totalVehiculos"
                        >
                            0
                        </span>

                        <span class="resumen-texto">
                            Vehículos registrados
                        </span>

                    </div>

                </div>

            </div>

            <!-- ACTIVOS -->
            <div class="col-12 col-md-4">

                <div class="resumen-card">

                    <div class="resumen-icono resumen-icono-activo">

                        <i class="bi bi-check-circle"></i>

                    </div>

                    <div>

                        <span
                            class="resumen-numero"
                            id="totalActivos"
                        >
                            0
                        </span>

                        <span class="resumen-texto">
                            Vehículos activos
                        </span>

                    </div>

                </div>

            </div>

            <!-- INACTIVOS -->
            <div class="col-12 col-md-4">

                <div class="resumen-card">

                    <div class="resumen-icono resumen-icono-inactivo">

                        <i class="bi bi-x-circle"></i>

                    </div>

                    <div>

                        <span
                            class="resumen-numero"
                            id="totalInactivos"
                        >
                            0
                        </span>

                        <span class="resumen-texto">
                            Vehículos inactivos
                        </span>

                    </div>

                </div>

            </div>

        </section>

        <!--=====================================
        ALERTAS
        ======================================-->

        <div id="alertaVehiculos"></div>

        <!--=====================================
        BUSCADOR Y FILTROS
        ======================================-->

        <section class="filtros-vehiculos">

            <div class="row g-3 align-items-center">

                <!-- BUSCADOR -->
                <div class="col-12 col-lg-6">

                    <div class="input-group buscador-panel">

                        <span class="input-group-text">

                            <i class="bi bi-search"></i>

                        </span>

                        <input
                            type="search"
                            class="form-control"
                            id="buscarVehiculo"
                            placeholder="Buscar por marca, línea, placa o chasis..."
                            autocomplete="off"
                        >

                    </div>

                </div>

                <!-- FILTRO TIPO -->
                <div class="col-12 col-md-6 col-lg-3">

                    <select
                        class="form-select filtro-select"
                        id="filtroTipo"
                    >

                        <option value="">
                            Todos los tipos
                        </option>

                        <option value="Motocicleta">
                            Motocicleta
                        </option>

                        <option value="Automóvil">
                            Automóvil
                        </option>
                    </select>

                </div>

                <!-- FILTRO ESTADO -->
                <div class="col-12 col-md-6 col-lg-3">

                    <select
                        class="form-select filtro-select"
                        id="filtroEstado"
                    >

                        <option value="">
                            Todos los estados
                        </option>

                        <option value="1">
                            Activos
                        </option>

                        <option value="0">
                            Inactivos
                        </option>

                    </select>

                </div>

            </div>

        </section>

        <!--=====================================
        ESTADO DE CARGA
        ======================================-->

        <section
            class="estado-carga"
            id="estadoCarga"
        >

            <div
                class="spinner-border text-danger"
                role="status"
            >

                <span class="visually-hidden">
                    Cargando...
                </span>

            </div>

            <p>
                Cargando tus vehículos...
            </p>

        </section>

        <!--=====================================
        MENSAJE SIN VEHÍCULOS
        ======================================-->

        <section
            class="sin-vehiculos d-none"
            id="sinVehiculos"
        >

            <div class="sin-vehiculos-icono">

                <i class="bi bi-scooter"></i>

            </div>

            <h2>
                No tienes vehículos registrados
            </h2>

            <p>

                Registra tu primer vehículo para poder administrar
                su información y consultar posteriormente su historial.

            </p>

            <button
                type="button"
                class="btn btn-danger rounded-pill px-4 py-2"
                data-bs-toggle="modal"
                data-bs-target="#modalVehiculo"
            >

                <i class="bi bi-plus-lg me-2"></i>
                Registrar vehículo

            </button>

        </section>

        <!--=====================================
        RESULTADO SIN COINCIDENCIAS
        ======================================-->

        <section
            class="sin-resultados d-none"
            id="sinResultados"
        >

            <i class="bi bi-search"></i>

            <h2>
                No se encontraron resultados
            </h2>

            <p>
                Intenta utilizar otros términos o cambiar los filtros.
            </p>

        </section>

        <!--=====================================
        CONTENEDOR DE TARJETAS
        ======================================-->

        <section class="pb-5">

            <div
                class="row g-4"
                id="contenedorVehiculos"
            >

                <!--
                LAS TARJETAS SE GENERARÁN
                DINÁMICAMENTE DESDE vehiculos.js
                -->

            </div>

        </section>

    </div>

</main>

<!-- FIN DE LA PARTE 1 -->
<!--=====================================
MODAL REGISTRAR / EDITAR VEHÍCULO
======================================-->

<div
    class="modal fade"
    id="modalVehiculo"
    tabindex="-1"
    aria-labelledby="tituloModalVehiculo"
    aria-hidden="true"
>

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content modal-vehiculo">

            <!-- ENCABEZADO -->
            <div class="modal-header">

                <div>

                    <span class="modal-etiqueta">
                        GESTIÓN DE VEHÍCULOS
                    </span>

                    <h2
                        class="modal-title"
                        id="tituloModalVehiculo"
                    >
                        Registrar vehículo
                    </h2>

                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>

            <!-- FORMULARIO -->
            <form
                id="formVehiculo"
                enctype="multipart/form-data"
                novalidate
            >

                <div class="modal-body">

                    <!-- ID PARA EDICIÓN -->
                    <input
                        type="hidden"
                        id="idVehiculo"
                        name="id"
                    >

                    <div class="row g-4">

                        <!--=====================================
                        COLUMNA DE IMAGEN
                        ======================================-->

                        <div class="col-12 col-lg-4">

                            <div class="imagen-vehiculo-panel">

                                <div class="imagen-preview-contenedor">

                                    <img
                                        id="previewImagen"
                                        src="../../img/moto-default.png"
                                        alt="Vista previa del vehículo"
                                    >

                                    <div class="imagen-preview-overlay">

                                        <i class="bi bi-camera"></i>

                                        <span>
                                            Vista previa
                                        </span>

                                    </div>

                                </div>

                                <label
                                    for="imagen"
                                    class="btn btn-outline-light w-100 mt-3"
                                >

                                    <i class="bi bi-upload me-2"></i>
                                    Seleccionar imagen

                                </label>

                                <input
                                    type="file"
                                    id="imagen"
                                    name="imagen"
                                    accept="image/jpeg,image/png,image/webp"
                                    hidden
                                >

                                <small class="imagen-ayuda">

                                    Formatos permitidos: JPG, PNG o WEBP.

                                    <span>
                                        Tamaño máximo: 5 MB.
                                    </span>

                                </small>

                            </div>

                        </div>

                        <!--=====================================
                        COLUMNA DE DATOS
                        ======================================-->

                        <div class="col-12 col-lg-8">

                            <div class="row g-3">

                                <!-- MARCA -->
                                <div class="col-12 col-md-6">

                                    <label
                                        for="marca"
                                        class="form-label"
                                    >
                                        Marca *
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="marca"
                                        name="marca"
                                        maxlength="50"
                                        placeholder="Ejemplo: Yamaha"
                                        required
                                    >

                                    <div class="invalid-feedback">
                                        Ingresa la marca del vehículo.
                                    </div>

                                </div>

                                <!-- LÍNEA -->
                                <div class="col-12 col-md-6">

                                    <label
                                        for="linea"
                                        class="form-label"
                                    >
                                        Línea *
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="linea"
                                        name="linea"
                                        maxlength="60"
                                        placeholder="Ejemplo: FZ25"
                                        required
                                    >

                                    <div class="invalid-feedback">
                                        Ingresa la línea del vehículo.
                                    </div>

                                </div>

                                <!-- MODELO -->
                                <div class="col-12 col-md-4">

                                    <label
                                        for="modelo"
                                        class="form-label"
                                    >
                                        Modelo / Año *
                                    </label>

                                    <input
                                        type="number"
                                        class="form-control"
                                        id="modelo"
                                        name="modelo"
                                        min="1900"
                                        max="<?= date("Y") + 1 ?>"
                                        placeholder="<?= date("Y") ?>"
                                        required
                                    >

                                    <div class="invalid-feedback">
                                        Ingresa un año válido.
                                    </div>

                                </div>

                                <!-- COLOR -->
                                <div class="col-12 col-md-4">

                                    <label
                                        for="color"
                                        class="form-label"
                                    >
                                        Color *
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="color"
                                        name="color"
                                        maxlength="40"
                                        placeholder="Ejemplo: Negro"
                                        required
                                    >

                                    <div class="invalid-feedback">
                                        Ingresa el color.
                                    </div>

                                </div>

                                <!-- TIPO -->
                                <div class="col-12 col-md-4">

                                    <label
                                        for="tipoVehiculo"
                                        class="form-label"
                                    >
                                        Tipo de vehículo *
                                    </label>

                                    <select
                                        class="form-select"
                                        id="tipoVehiculo"
                                        name="tipo_vehiculo"
                                        required
                                    >

                                        <option value="">
                                            Seleccionar
                                        </option>

                                        <option value="Motocicleta">
                                            Motocicleta
                                        </option>

                                        <option value="Automóvil">
                                            Automóvil
                                        </option>

                                    </select>

                                    <div class="invalid-feedback">
                                        Selecciona el tipo de vehículo.
                                    </div>

                                </div>

                                <!-- PLACA -->
                                <div class="col-12 col-md-6">

                                    <label
                                        for="placa"
                                        class="form-label"
                                    >
                                        Placa *
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control text-uppercase"
                                        id="placa"
                                        name="placa"
                                        maxlength="20"
                                        placeholder="Ejemplo: M123ABC"
                                        autocomplete="off"
                                        required
                                    >

                                    <div class="invalid-feedback">
                                        Ingresa la placa.
                                    </div>

                                </div>

                                <!-- CHASIS -->
                                <div class="col-12 col-md-6">

                                    <label
                                        for="noChasis"
                                        class="form-label"
                                    >
                                        Número de chasis *
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control text-uppercase"
                                        id="noChasis"
                                        name="no_chasis"
                                        maxlength="60"
                                        placeholder="Ejemplo: CH001"
                                        autocomplete="off"
                                        required
                                    >

                                    <div class="invalid-feedback">
                                        Ingresa el número de chasis.
                                    </div>

                                </div>

                                <!-- CILINDRAJE -->
                                <div class="col-12 col-md-4">

                                    <label
                                        for="cilindraje"
                                        class="form-label"
                                    >
                                        Cilindraje *
                                    </label>

                                    <div class="input-group">

                                        <input
                                            type="number"
                                            class="form-control"
                                            id="cilindraje"
                                            name="cilindraje"
                                            min="0"
                                            max="10000"
                                            placeholder="250"
                                            required
                                        >

                                        <span class="input-group-text">
                                            cc
                                        </span>

                                    </div>

                                    <div class="invalid-feedback">
                                        Ingresa un cilindraje válido.
                                    </div>

                                </div>

                                <!-- COMBUSTIBLE -->
                                <div class="col-12 col-md-4">

                                    <label
                                        for="combustible"
                                        class="form-label"
                                    >
                                        Combustible *
                                    </label>

                                    <select
                                        class="form-select"
                                        id="combustible"
                                        name="combustible"
                                        required
                                    >

                                        <option value="">
                                            Seleccionar
                                        </option>

                                        <option value="Gasolina">
                                            Gasolina
                                        </option>

                                        <option value="Diésel">
                                            Diésel
                                        </option>

                                        <option value="Eléctrico">
                                            Eléctrico
                                        </option>

                                        <option value="Híbrido">
                                            Híbrido
                                        </option>

                                        <option value="Otro">
                                            Otro
                                        </option>

                                    </select>

                                    <div class="invalid-feedback">
                                        Selecciona el combustible.
                                    </div>

                                </div>

                                <!-- KILOMETRAJE -->
                                <div class="col-12 col-md-4">

                                    <label
                                        for="kilometraje"
                                        class="form-label"
                                    >
                                        Kilometraje *
                                    </label>

                                    <div class="input-group">

                                        <input
                                            type="number"
                                            class="form-control"
                                            id="kilometraje"
                                            name="kilometraje"
                                            min="0"
                                            placeholder="12000"
                                            required
                                        >

                                        <span class="input-group-text">
                                            km
                                        </span>

                                    </div>

                                    <div class="invalid-feedback">
                                        Ingresa un kilometraje válido.
                                    </div>

                                </div>

                                <!-- ESTADO -->
                                <div class="col-12">

                                    <label
                                        for="estado"
                                        class="form-label"
                                    >
                                        Estado *
                                    </label>

                                    <select
                                        class="form-select"
                                        id="estado"
                                        name="estado"
                                        required
                                    >

                                        <option value="1">
                                            Activo
                                        </option>

                                        <option value="0">
                                            Inactivo
                                        </option>

                                    </select>

                                    <div class="form-text text-secondary">

                                        Un vehículo inactivo seguirá apareciendo
                                        en tu cuenta, pero será identificado como
                                        fuera de uso.

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- PIE DEL MODAL -->
                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-outline-light"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-danger"
                        id="btnGuardarVehiculo"
                    >

                        <span class="texto-boton">

                            <i class="bi bi-floppy me-2"></i>
                            Guardar vehículo

                        </span>

                        <span class="cargando-boton d-none">

                            <span
                                class="spinner-border spinner-border-sm me-2"
                                role="status"
                            ></span>

                            Guardando...

                        </span>

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!--=====================================
MODAL CONFIRMAR ELIMINACIÓN
======================================-->

<div
    class="modal fade"
    id="modalEliminarVehiculo"
    tabindex="-1"
    aria-labelledby="tituloEliminarVehiculo"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content modal-eliminar">

            <div class="modal-body text-center">

                <div class="eliminar-icono">

                    <i class="bi bi-trash3"></i>

                </div>

                <h2 id="tituloEliminarVehiculo">
                    Eliminar vehículo
                </h2>

                <p>

                    ¿Estás seguro de eliminar el vehículo

                    <strong id="nombreVehiculoEliminar">
                        seleccionado
                    </strong>?

                </p>

                <div class="alert alert-warning text-start">

                    <i class="bi bi-exclamation-triangle me-2"></i>

                    Esta acción eliminará el registro y su imagen.
                    No se puede deshacer.

                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mt-4">

                    <button
                        type="button"
                        class="btn btn-outline-light px-4"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="btn btn-danger px-4"
                        id="btnConfirmarEliminar"
                    >

                        <i class="bi bi-trash3 me-2"></i>
                        Sí, eliminar

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

<!--=====================================
CONFIGURACIÓN DE RUTAS PARA JAVASCRIPT
======================================-->

<script>

    const RONEM_CONFIG = {

        apiVehiculos:
            "../../api/api_vehiculos.php",

        rutaRaiz:
            "../../",

        imagenPredeterminada:
            "../../img/moto-default.png"

    };

</script>

<!-- BOOTSTRAP -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
></script>

<!-- JAVASCRIPT DEL MÓDULO -->
<script
    src="../../js/vehiculos.js?v=1.0"
></script>

</body>

</html>