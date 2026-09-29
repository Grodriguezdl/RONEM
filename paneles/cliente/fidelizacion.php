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

    <title>Fidelización | RONEM</title>

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
        href="../../css/fidelizacion.css?v=1.0"
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
                        class="nav-link"
                        href="historial.php"
                    >

                        <i class="bi bi-clock-history me-1"></i>
                        Historial

                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link active"
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

                        MIS

                        <span>
                            PUNTOS
                        </span>

                    </h1>

                    <p class="panel-descripcion">

                        Acumula puntos por tus compras y cámbialos por
                        descuentos o servicios gratuitos en RONEM.

                    </p>

                </div>

                <div class="col-12 col-lg-4 text-lg-end">

                    <button
                        type="button"
                        class="btn btn-danger btn-actualizar-fidelizacion"
                        id="btnActualizarFidelizacion"
                    >

                        <i class="bi bi-arrow-clockwise me-2"></i>
                        ACTUALIZAR PUNTOS

                    </button>

                </div>

            </div>

        </section>

        <!--=====================================
        TARJETAS DE RESUMEN
        ======================================-->

        <section class="row g-3 mb-4">

            <!-- PUNTOS DISPONIBLES -->
            <div class="col-12 col-md-4">

                <div class="resumen-card">

                    <div class="resumen-icono">

                        <i class="bi bi-coin"></i>

                    </div>

                    <div>

                        <span
                            class="resumen-numero"
                            id="puntosDisponibles"
                        >
                            0
                        </span>

                        <span class="resumen-texto">
                            Puntos disponibles
                        </span>

                    </div>

                </div>

            </div>

            <!-- TOTAL GANADOS -->
            <div class="col-12 col-md-4">

                <div class="resumen-card">

                    <div class="resumen-icono resumen-icono-ganados">

                        <i class="bi bi-graph-up-arrow"></i>

                    </div>

                    <div>

                        <span
                            class="resumen-numero"
                            id="totalPuntosGanados"
                        >
                            0
                        </span>

                        <span class="resumen-texto">
                            Puntos ganados
                        </span>

                    </div>

                </div>

            </div>

            <!-- TOTAL USADOS -->
            <div class="col-12 col-md-4">

                <div class="resumen-card">

                    <div class="resumen-icono resumen-icono-usados">

                        <i class="bi bi-gift"></i>

                    </div>

                    <div>

                        <span
                            class="resumen-numero"
                            id="totalPuntosUsados"
                        >
                            0
                        </span>

                        <span class="resumen-texto">
                            Puntos utilizados
                        </span>

                    </div>

                </div>

            </div>

        </section>

        <!--=====================================
        REGLA DE ACUMULACIÓN
        ======================================-->

        <section class="regla-puntos">

            <div class="regla-puntos-icono">

                <i class="bi bi-info-circle"></i>

            </div>

            <div>

                <span class="regla-puntos-etiqueta">
                    CÓMO GANAS PUNTOS
                </span>

                <h2>
                    Por cada Q10 en compras obtienes 1 punto
                </h2>

                <p>
                    Los puntos se acreditan cuando la compra queda
                    registrada como completada.
                </p>

            </div>

        </section>

        <!--=====================================
        ALERTAS
        ======================================-->

        <div id="alertaFidelizacion"></div>

        <!--=====================================
        PESTAÑAS
        ======================================-->

        <section class="fidelizacion-tabs">

            <button
                type="button"
                class="fidelizacion-tab active"
                data-panel="movimientos"
            >

                <i class="bi bi-clock-history me-2"></i>
                Movimientos

            </button>

            <button
                type="button"
                class="fidelizacion-tab"
                data-panel="recompensas"
            >

                <i class="bi bi-gift me-2"></i>
                Recompensas

            </button>

            <button
                type="button"
                class="fidelizacion-tab"
                data-panel="canjes"
            >

                <i class="bi bi-arrow-left-right me-2"></i>
                Mis canjes

            </button>

        </section>

        <!--=====================================
        PANEL DE MOVIMIENTOS
        ======================================-->

        <section
            class="fidelizacion-panel"
            id="panelMovimientos"
        >

            <div class="fidelizacion-panel-encabezado">

                <div>

                    <span class="fidelizacion-etiqueta">
                        HISTORIAL DE PUNTOS
                    </span>

                    <h2>
                        Movimientos recientes
                    </h2>

                    <p>
                        Revisa los puntos ganados por compras y los
                        puntos descontados por canjes.
                    </p>

                </div>

                <div class="fidelizacion-panel-icono">

                    <i class="bi bi-list-ul"></i>

                </div>

            </div>

            <!-- CARGA -->
            <div
                class="estado-carga"
                id="cargaMovimientos"
            >

                <div
                    class="spinner-border text-danger"
                    role="status"
                >

                    <span class="visually-hidden">
                        Cargando movimientos...
                    </span>

                </div>

                <p>
                    Cargando movimientos de puntos...
                </p>

            </div>

            <!-- VACÍO -->
            <div
                class="estado-vacio d-none"
                id="sinMovimientos"
            >

                <div class="estado-vacio-icono">

                    <i class="bi bi-coin"></i>

                </div>

                <h3>
                    No tienes movimientos
                </h3>

                <p>
                    Cuando ganes o utilices puntos, los movimientos
                    aparecerán en este apartado.
                </p>

            </div>

            <!-- TABLA -->
            <div
                class="tabla-fidelizacion-contenedor d-none"
                id="contenedorMovimientos"
            >

                <div class="table-responsive">

                    <table class="tabla-fidelizacion">

                        <thead>

                            <tr>

                                <th>
                                    Fecha
                                </th>

                                <th>
                                    Tipo
                                </th>

                                <th>
                                    Motivo
                                </th>

                                <th>
                                    Puntos
                                </th>

                            </tr>

                        </thead>

                        <tbody id="tbodyMovimientos">

                            <!-- Generado desde fidelizacion.js -->

                        </tbody>

                    </table>

                </div>

            </div>

        </section>

        <!--=====================================
        PANEL DE RECOMPENSAS
        ======================================-->

        <section
            class="fidelizacion-panel d-none"
            id="panelRecompensas"
        >

            <div class="fidelizacion-panel-encabezado">

                <div>

                    <span class="fidelizacion-etiqueta">
                        CATÁLOGO
                    </span>

                    <h2>
                        Recompensas disponibles
                    </h2>

                    <p>
                        Canjea tus puntos por descuentos y servicios
                        disponibles.
                    </p>

                </div>

                <div class="fidelizacion-panel-icono">

                    <i class="bi bi-stars"></i>

                </div>

            </div>

            <!-- CARGA -->
            <div
                class="estado-carga"
                id="cargaRecompensas"
            >

                <div
                    class="spinner-border text-danger"
                    role="status"
                >

                    <span class="visually-hidden">
                        Cargando recompensas...
                    </span>

                </div>

                <p>
                    Cargando recompensas...
                </p>

            </div>

            <!-- VACÍO -->
            <div
                class="estado-vacio d-none"
                id="sinRecompensas"
            >

                <div class="estado-vacio-icono">

                    <i class="bi bi-gift"></i>

                </div>

                <h3>
                    No hay recompensas disponibles
                </h3>

                <p>
                    Actualmente no hay descuentos o servicios
                    disponibles para canjear.
                </p>

            </div>

            <!-- GRID -->
            <div
                class="recompensas-grid d-none"
                id="contenedorRecompensas"
            >

                <!-- Generado desde fidelizacion.js -->

            </div>

        </section>

        <!--=====================================
        PANEL DE CANJES
        ======================================-->

        <section
            class="fidelizacion-panel d-none"
            id="panelCanjes"
        >

            <div class="fidelizacion-panel-encabezado">

                <div>

                    <span class="fidelizacion-etiqueta">
                        MIS BENEFICIOS
                    </span>

                    <h2>
                        Historial de canjes
                    </h2>

                    <p>
                        Consulta las recompensas solicitadas y el estado
                        de cada canje.
                    </p>

                </div>

                <div class="fidelizacion-panel-icono">

                    <i class="bi bi-ticket-perforated"></i>

                </div>

            </div>

            <!-- CARGA -->
            <div
                class="estado-carga"
                id="cargaCanjes"
            >

                <div
                    class="spinner-border text-danger"
                    role="status"
                >

                    <span class="visually-hidden">
                        Cargando canjes...
                    </span>

                </div>

                <p>
                    Cargando tus canjes...
                </p>

            </div>

            <!-- VACÍO -->
            <div
                class="estado-vacio d-none"
                id="sinCanjes"
            >

                <div class="estado-vacio-icono">

                    <i class="bi bi-ticket"></i>

                </div>

                <h3>
                    No has realizado canjes
                </h3>

                <p>
                    Cuando canjees una recompensa, aparecerá
                    en este apartado.
                </p>

            </div>

            <!-- TABLA -->
            <div
                class="tabla-fidelizacion-contenedor d-none"
                id="contenedorCanjes"
            >

                <div class="table-responsive">

                    <table class="tabla-fidelizacion">

                        <thead>

                            <tr>

                                <th>
                                    Código
                                </th>

                                <th>
                                    Recompensa
                                </th>

                                <th>
                                    Puntos usados
                                </th>

                                <th>
                                    Fecha
                                </th>

                                <th>
                                    Estado
                                </th>

                            </tr>

                        </thead>

                        <tbody id="tbodyCanjes">

                            <!-- Generado desde fidelizacion.js -->

                        </tbody>

                    </table>

                </div>

            </div>

        </section>

    </div>

</main>

<!--=====================================
MODAL CONFIRMAR CANJE
======================================-->

<div
    class="modal fade"
    id="modalConfirmarCanje"
    tabindex="-1"
    aria-labelledby="tituloConfirmarCanje"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content modal-fidelizacion">

            <div class="modal-header">

                <div>

                    <span class="modal-etiqueta">
                        CONFIRMAR RECOMPENSA
                    </span>

                    <h2
                        class="modal-title"
                        id="tituloConfirmarCanje"
                    >
                        Canjear recompensa
                    </h2>

                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>

            <div class="modal-body">

                <div class="canje-resumen">

                    <div class="canje-resumen-icono">

                        <i class="bi bi-gift"></i>

                    </div>

                    <h3 id="nombreRecompensaCanje">
                        Recompensa
                    </h3>

                    <p>
                        Este canje utilizará
                        <strong id="puntosRecompensaCanje">
                            0
                        </strong>
                        puntos de tu saldo.
                    </p>

                    <div class="alert alert-warning mb-0">

                        <i class="bi bi-exclamation-triangle me-2"></i>

                        Los puntos serán descontados inmediatamente.
                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-outline-light"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    class="btn btn-danger"
                    id="btnConfirmarCanje"
                >

                    <span class="texto-boton">

                        <i class="bi bi-gift me-2"></i>
                        Confirmar canje

                    </span>

                    <span class="cargando-boton d-none">

                        <span
                            class="spinner-border spinner-border-sm me-2"
                            role="status"
                        ></span>

                        Procesando...

                    </span>

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

        apiFidelizacion:
            "../../api/api_fidelizacion.php",

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
    src="../../js/fidelizacion.js?v=1.0"
></script>

</body>

</html>