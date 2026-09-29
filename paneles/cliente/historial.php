<?php

session_start();

//=====================================
// VALIDAR SESIÓN
//=====================================

if (!isset($_SESSION["id_usuario"])) {

    header("Location: ../../index.php");
    exit;

}

$idUsuario = (int) $_SESSION["id_usuario"];

$nombreUsuario =
    $_SESSION["usuario"] ??
    $_SESSION["nombre"] ??
    "Cliente";

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mi Historial | RONEM</title>

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
        href="../../css/historial.css?v=1.0"
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
                        class="nav-link"
                        href="vehiculos.php"
                    >

                        <i class="bi bi-scooter me-1"></i>
                        Mis vehículos

                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link active"
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

        <!--=====================================
        ENCABEZADO
        ======================================-->

        <section class="panel-encabezado">

            <div class="row align-items-center g-4">

                <div class="col-12 col-lg-8">

                    <h1 class="panel-titulo">

                        MI

                        <span>
                            HISTORIAL
                        </span>

                    </h1>

                    <p class="panel-descripcion">

                        Consulta las compras realizadas y los servicios
                        de taller registrados para tu cuenta.

                    </p>

                </div>

                <div class="col-12 col-lg-4 text-lg-end">

                    <button
                        type="button"
                        class="btn btn-danger btn-actualizar-historial"
                        id="btnActualizarHistorial"
                    >

                        <i class="bi bi-arrow-clockwise me-2"></i>
                        ACTUALIZAR HISTORIAL

                    </button>

                </div>

            </div>

        </section>

        <!--=====================================
        TARJETAS DE RESUMEN
        ======================================-->

        <section class="row g-3 mb-4">

            <!-- TOTAL DE COMPRAS -->
            <div class="col-12 col-md-4">

                <div class="resumen-card">

                    <div class="resumen-icono">

                        <i class="bi bi-bag-check"></i>

                    </div>

                    <div>

                        <span
                            class="resumen-numero"
                            id="totalCompras"
                        >
                            0
                        </span>

                        <span class="resumen-texto">
                            Compras registradas
                        </span>

                    </div>

                </div>

            </div>

            <!-- TOTAL DE SERVICIOS -->
            <div class="col-12 col-md-4">

                <div class="resumen-card">

                    <div class="resumen-icono resumen-icono-servicios">

                        <i class="bi bi-wrench-adjustable"></i>

                    </div>

                    <div>

                        <span
                            class="resumen-numero"
                            id="totalServicios"
                        >
                            0
                        </span>

                        <span class="resumen-texto">
                            Servicios de taller
                        </span>

                    </div>

                </div>

            </div>

            <!-- SERVICIOS FINALIZADOS -->
            <div class="col-12 col-md-4">

                <div class="resumen-card">

                    <div class="resumen-icono resumen-icono-finalizados">

                        <i class="bi bi-check-circle"></i>

                    </div>

                    <div>

                        <span
                            class="resumen-numero"
                            id="totalFinalizados"
                        >
                            0
                        </span>

                        <span class="resumen-texto">
                            Servicios finalizados
                        </span>

                    </div>

                </div>

            </div>

        </section>

        <!--=====================================
        ALERTAS
        ======================================-->

        <div id="alertaHistorial"></div>

        <!--=====================================
        PESTAÑAS DEL HISTORIAL
        ======================================-->

        <section class="historial-tabs">

            <button
                type="button"
                class="historial-tab active"
                id="tabCompras"
                data-panel="compras"
            >

                <i class="bi bi-receipt me-2"></i>
                Mis compras

            </button>

            <button
                type="button"
                class="historial-tab"
                id="tabServicios"
                data-panel="servicios"
            >

                <i class="bi bi-tools me-2"></i>
                Servicios de taller

            </button>

        </section>

        <!--=====================================
        PANEL DE COMPRAS
        ======================================-->

        <section
            class="historial-panel"
            id="panelCompras"
        >

            <div class="historial-panel-encabezado">

                <div>

                    <span class="historial-etiqueta">
                        REGISTRO DE VENTAS
                    </span>

                    <h2>
                        Compras realizadas
                    </h2>

                    <p>
                        Compras asociadas a tu usuario dentro de RONEM.
                    </p>

                </div>

                <div class="historial-panel-icono">

                    <i class="bi bi-cart-check"></i>

                </div>

            </div>

            <!-- ESTADO DE CARGA -->
            <div
                class="estado-carga"
                id="cargaCompras"
            >

                <div
                    class="spinner-border text-danger"
                    role="status"
                >

                    <span class="visually-hidden">
                        Cargando compras...
                    </span>

                </div>

                <p>
                    Cargando tus compras...
                </p>

            </div>

            <!-- SIN COMPRAS -->
            <div
                class="estado-vacio d-none"
                id="sinCompras"
            >

                <div class="estado-vacio-icono">

                    <i class="bi bi-bag-x"></i>

                </div>

                <h3>
                    No tienes compras registradas
                </h3>

                <p>
                    Cuando realices una compra, aparecerá en este apartado.
                </p>

            </div>

            <!-- TABLA DE COMPRAS -->
            <div
                class="tabla-historial-contenedor d-none"
                id="contenedorTablaCompras"
            >

                <div class="table-responsive">

                    <table class="tabla-historial">

                        <thead>

                            <tr>

                                <th>
                                    Venta
                                </th>

                                <th>
                                    Fecha
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Estado
                                </th>

                                <th class="text-center">
                                    Acción
                                </th>

                            </tr>

                        </thead>

                        <tbody id="tbodyCompras">

                            <!--
                            LAS COMPRAS SE GENERAN
                            DESDE historial.js
                            -->

                        </tbody>

                    </table>

                </div>

            </div>

        </section>

        <!--=====================================
        PANEL DE SERVICIOS
        ======================================-->

        <section
            class="historial-panel d-none"
            id="panelServicios"
        >

            <div class="historial-panel-encabezado">

                <div>

                    <span class="historial-etiqueta">
                        SERVICIOS DE TALLER
                    </span>

                    <h2>
                        Seguimiento de servicios
                    </h2>

                    <p>
                        Consulta los registros creados por el técnico
                        y el estado actual de cada servicio.
                    </p>

                </div>

                <div class="historial-panel-icono">

                    <i class="bi bi-gear-wide-connected"></i>

                </div>

            </div>

            <!-- ESTADO DE CARGA -->
            <div
                class="estado-carga"
                id="cargaServicios"
            >

                <div
                    class="spinner-border text-danger"
                    role="status"
                >

                    <span class="visually-hidden">
                        Cargando servicios...
                    </span>

                </div>

                <p>
                    Cargando tus servicios de taller...
                </p>

            </div>

            <!-- SIN SERVICIOS -->
            <div
                class="estado-vacio d-none"
                id="sinServicios"
            >

                <div class="estado-vacio-icono">

                    <i class="bi bi-tools"></i>

                </div>

                <h3>
                    No tienes servicios registrados
                </h3>

                <p>
                    Los servicios asignados por el técnico aparecerán
                    en este apartado.
                </p>

            </div>

            <!-- CONTENEDOR DE SERVICIOS -->
            <div
                class="servicios-listado d-none"
                id="contenedorServicios"
            >

                <!--
                LOS SERVICIOS SE GENERAN
                DESDE historial.js
                -->

            </div>

        </section>

    </div>

</main>

<!--=====================================
MODAL DETALLE DE COMPRA
======================================-->

<div
    class="modal fade"
    id="modalDetalleCompra"
    tabindex="-1"
    aria-labelledby="tituloDetalleCompra"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content modal-historial">

            <!-- ENCABEZADO -->
            <div class="modal-header">

                <div>

                    <span class="modal-etiqueta">
                        DETALLE DE VENTA
                    </span>

                    <h2
                        class="modal-title"
                        id="tituloDetalleCompra"
                    >

                        Compra #

                        <span id="idVentaDetalle">
                            0
                        </span>

                    </h2>

                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>

            <!-- CUERPO -->
            <div class="modal-body">

                <div
                    class="detalle-compra-carga"
                    id="cargaDetalleCompra"
                >

                    <div
                        class="spinner-border text-danger"
                        role="status"
                    >

                        <span class="visually-hidden">
                            Cargando detalle...
                        </span>

                    </div>

                    <p>
                        Cargando detalle de la compra...
                    </p>

                </div>

                <div
                    class="tabla-historial-contenedor d-none"
                    id="contenedorDetalleCompra"
                >

                    <div class="table-responsive">

                        <table class="tabla-historial">

                            <thead>

                                <tr>

                                    <th>
                                        Producto
                                    </th>

                                    <th class="text-center">
                                        Cantidad
                                    </th>

                                    <th>
                                        Precio unitario
                                    </th>

                                    <th>
                                        Subtotal
                                    </th>

                                </tr>

                            </thead>

                            <tbody id="tbodyDetalleCompra">

                                <!--
                                EL DETALLE SE GENERA
                                DESDE historial.js
                                -->

                            </tbody>

                        </table>

                    </div>

                    <div class="detalle-total">

                        <span>
                            Total de la compra
                        </span>

                        <strong id="totalDetalleCompra">
                            Q0.00
                        </strong>

                    </div>

                </div>

                <div
                    class="estado-vacio d-none"
                    id="sinDetalleCompra"
                >

                    <div class="estado-vacio-icono">

                        <i class="bi bi-receipt-cutoff"></i>

                    </div>

                    <h3>
                        Sin productos registrados
                    </h3>

                    <p>
                        No se encontraron productos asociados
                        a esta compra.
                    </p>

                </div>

            </div>

            <!-- PIE -->
            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-outline-light"
                    data-bs-dismiss="modal"
                >

                    Cerrar

                </button>

            </div>

        </div>

    </div>

</div>

<!--=====================================
CONFIGURACIÓN PARA JAVASCRIPT
======================================-->

<script>

    const RONEM_CONFIG = {

        apiHistorial:
            "../../api/api_historialu.php",

        idUsuario:
            <?= $idUsuario ?>,

        rutaRaiz:
            "../../"

    };

</script>

<!-- BOOTSTRAP -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
></script>

<!-- JAVASCRIPT DEL MÓDULO -->
<script
    src="../../js/historial.js?v=1.0"
></script>

</body>

</html>