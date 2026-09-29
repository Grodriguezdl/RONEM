<?php

session_start();

//==================================================
// VALIDAR SESIÓN
//==================================================

if (
    !isset($_SESSION["id_usuario"]) ||
    !is_numeric($_SESSION["id_usuario"])
) {

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

    <title>Moto Escuela | RONEM</title>

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

    <!-- CSS -->
    <link
        rel="stylesheet"
        href="../../css/motoescuela.css?v=1.0"
    >

</head>

<body>

<!--==================================================
    NAVEGACIÓN
===================================================-->

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

        <!-- MENÚ -->
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
                        class="nav-link"
                        href="fidelizacion.php"
                    >

                        <i class="bi bi-star me-1"></i>
                        Fidelización

                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link active"
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

<!--==================================================
    CONTENIDO PRINCIPAL
===================================================-->

<main class="panel-contenido">

    <div class="container-fluid px-3 px-lg-5">

        <!--==================================================
            ENCABEZADO
        ===================================================-->

        <section class="panel-encabezado">

            <div class="row align-items-center g-4">

                <div class="col-12 col-lg-8">

                    <span class="panel-etiqueta">
                        FORMACIÓN Y PROGRESO
                    </span>

                    <h1 class="panel-titulo">

                        MOTO

                        <span>
                            ESCUELA
                        </span>

                    </h1>

                    <p class="panel-descripcion">

                        Consulta el estado de tu inscripción, revisa el
                        avance de tu formación y conoce cada actualización
                        registrada en tu proceso.

                    </p>

                </div>

                <div class="col-12 col-lg-4 text-lg-end">

                    <button
                        type="button"
                        class="btn btn-danger btn-actualizar"
                        id="btnActualizarMotoescuela"
                    >

                        <i class="bi bi-arrow-clockwise me-2"></i>
                        ACTUALIZAR PROGRESO

                    </button>

                </div>

            </div>

        </section>

        <!--==================================================
            ALERTAS
        ===================================================-->

        <div id="alertaMotoescuela"></div>

        <!--==================================================
            ESTADO DE CARGA GENERAL
        ===================================================-->

        <section
            class="estado-carga-principal"
            id="cargaMotoescuela"
        >

            <div
                class="spinner-border text-danger"
                role="status"
            >

                <span class="visually-hidden">
                    Cargando información...
                </span>

            </div>

            <h2>
                Cargando tu progreso
            </h2>

            <p>
                Estamos consultando la información de tu inscripción.
            </p>

        </section>

        <!--==================================================
            SIN SOLICITUD
        ===================================================-->

        <section
            class="sin-solicitud d-none"
            id="sinSolicitudMotoescuela"
        >

            <div class="sin-solicitud-icono">

                <i class="bi bi-mortarboard"></i>

            </div>

            <span class="sin-solicitud-etiqueta">
                SIN INSCRIPCIÓN RELACIONADA
            </span>

            <h2>
                Todavía no tienes un proceso activo
            </h2>

            <p>

                No encontramos una solicitud de Moto Escuela vinculada
                con tu usuario. Cuando tu inscripción sea registrada,
                podrás consultar aquí todo tu progreso.

            </p>

            <a
                href="../../index.php"
                class="btn btn-danger"
            >

                <i class="bi bi-house-door me-2"></i>
                VOLVER AL INICIO

            </a>

        </section>

        <!--==================================================
            CONTENIDO DE LA SOLICITUD
        ===================================================-->

        <div
            class="contenido-motoescuela d-none"
            id="contenidoMotoescuela"
        >

            <!--==================================================
                RESUMEN SUPERIOR
            ===================================================-->

            <section class="row g-3 mb-4">

                <!-- ESTADO ACTUAL -->
                <div class="col-12 col-md-6 col-xl-3">

                    <article class="resumen-card">

                        <div class="resumen-icono">

                            <i class="bi bi-flag"></i>

                        </div>

                        <div class="resumen-contenido">

                            <span class="resumen-etiqueta">
                                Estado actual
                            </span>

                            <strong
                                class="resumen-valor resumen-valor-texto"
                                id="resumenEstadoActual"
                            >
                                Sin información
                            </strong>

                        </div>

                    </article>

                </div>

                <!-- PROGRESO -->
                <div class="col-12 col-md-6 col-xl-3">

                    <article class="resumen-card">

                        <div class="resumen-icono resumen-icono-progreso">

                            <i class="bi bi-speedometer2"></i>

                        </div>

                        <div class="resumen-contenido">

                            <span class="resumen-etiqueta">
                                Progreso
                            </span>

                            <strong
                                class="resumen-valor"
                                id="resumenPorcentaje"
                            >
                                0%
                            </strong>

                        </div>

                    </article>

                </div>

                <!-- SOLICITUD -->
                <div class="col-12 col-md-6 col-xl-3">

                    <article class="resumen-card">

                        <div class="resumen-icono resumen-icono-solicitud">

                            <i class="bi bi-file-earmark-text"></i>

                        </div>

                        <div class="resumen-contenido">

                            <span class="resumen-etiqueta">
                                Solicitud
                            </span>

                            <strong
                                class="resumen-valor"
                                id="resumenIdSolicitud"
                            >
                                #0
                            </strong>

                        </div>

                    </article>

                </div>

                <!-- ACTUALIZACIÓN -->
                <div class="col-12 col-md-6 col-xl-3">

                    <article class="resumen-card">

                        <div class="resumen-icono resumen-icono-fecha">

                            <i class="bi bi-calendar-check"></i>

                        </div>

                        <div class="resumen-contenido">

                            <span class="resumen-etiqueta">
                                Última actualización
                            </span>

                            <strong
                                class="resumen-valor resumen-valor-fecha"
                                id="resumenFechaActualizacion"
                            >
                                Sin registro
                            </strong>

                        </div>

                    </article>

                </div>

            </section>

            <!--==================================================
                PROGRESO PRINCIPAL
            ===================================================-->

            <section class="progreso-panel">

                <div class="row align-items-center g-4">

                    <div class="col-12 col-lg-8">

                        <span class="seccion-etiqueta">
                            AVANCE DE FORMACIÓN
                        </span>

                        <h2 id="tituloEstadoActual">
                            Estado actual
                        </h2>

                        <p id="descripcionEstadoActual">

                            Aquí aparecerá la descripción correspondiente
                            al estado actual de tu formación.

                        </p>

                        <div class="progreso-informacion">

                            <div class="progreso-textos">

                                <span>
                                    Progreso general
                                </span>

                                <strong id="textoPorcentajeProgreso">
                                    0%
                                </strong>

                            </div>

                            <div
                                class="progress progreso-barra-contenedor"
                                role="progressbar"
                                aria-label="Progreso de Moto Escuela"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-valuenow="0"
                                id="barraProgresoAccesible"
                            >

                                <div
                                    class="progress-bar progreso-barra"
                                    id="barraProgresoMotoescuela"
                                    style="width: 0%;"
                                ></div>

                            </div>

                        </div>

                    </div>

                    <div class="col-12 col-lg-4">

                        <div class="progreso-circulo">

                            <svg
                                class="progreso-svg"
                                viewBox="0 0 160 160"
                                aria-hidden="true"
                            >

                                <circle
                                    class="progreso-circulo-fondo"
                                    cx="80"
                                    cy="80"
                                    r="66"
                                ></circle>

                                <circle
                                    class="progreso-circulo-valor"
                                    id="circuloProgresoMotoescuela"
                                    cx="80"
                                    cy="80"
                                    r="66"
                                ></circle>

                            </svg>

                            <div class="progreso-circulo-contenido">

                                <strong id="porcentajeCircular">
                                    0%
                                </strong>

                                <span>
                                    COMPLETADO
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </section>

            <!--==================================================
                INFORMACIÓN DE LA SOLICITUD
            ===================================================-->

            <section class="solicitud-panel">

                <div class="seccion-encabezado">

                    <div>

                        <span class="seccion-etiqueta">
                            DATOS REGISTRADOS
                        </span>

                        <h2>
                            Información de tu solicitud
                        </h2>

                        <p>
                            Estos son los datos asociados con tu inscripción
                            en Moto Escuela.
                        </p>

                    </div>

                    <div class="seccion-icono">

                        <i class="bi bi-person-vcard"></i>

                    </div>

                </div>

                <div class="datos-solicitud-grid">

                    <div class="dato-solicitud">

                        <span>
                            Nombre completo
                        </span>

                        <strong id="datoNombreCompleto">
                            Sin información
                        </strong>

                    </div>

                    <div class="dato-solicitud">

                        <span>
                            DPI
                        </span>

                        <strong id="datoDPI">
                            Sin información
                        </strong>

                    </div>

                    <div class="dato-solicitud">

                        <span>
                            Fecha de nacimiento
                        </span>

                        <strong id="datoFechaNacimiento">
                            Sin información
                        </strong>

                    </div>

                    <div class="dato-solicitud">

                        <span>
                            Género
                        </span>

                        <strong id="datoGenero">
                            Sin información
                        </strong>

                    </div>

                    <div class="dato-solicitud">

                        <span>
                            Teléfono
                        </span>

                        <strong id="datoTelefono">
                            Sin información
                        </strong>

                    </div>

                    <div class="dato-solicitud">

                        <span>
                            Correo
                        </span>

                        <strong id="datoCorreo">
                            Sin información
                        </strong>

                    </div>

                    <div class="dato-solicitud dato-solicitud-ancho">

                        <span>
                            Dirección
                        </span>

                        <strong id="datoDireccion">
                            Sin información
                        </strong>

                    </div>

                </div>

            </section>

            <!--==================================================
                HISTORIAL
            ===================================================-->

            <section class="historial-panel">

                <div class="seccion-encabezado">

                    <div>

                        <span class="seccion-etiqueta">
                            SEGUIMIENTO
                        </span>

                        <h2>
                            Historial de progreso
                        </h2>

                        <p>

                            Consulta cada cambio realizado durante tu
                            proceso de formación.

                        </p>

                    </div>

                    <div class="seccion-icono">

                        <i class="bi bi-clock-history"></i>

                    </div>

                </div>

                <!-- CARGANDO HISTORIAL -->
                <div
                    class="historial-cargando"
                    id="cargaHistorialMotoescuela"
                >

                    <div
                        class="spinner-border text-danger"
                        role="status"
                    >

                        <span class="visually-hidden">
                            Cargando historial...
                        </span>

                    </div>

                    <p>
                        Cargando historial de progreso...
                    </p>

                </div>

                <!-- SIN HISTORIAL -->
                <div
                    class="historial-vacio d-none"
                    id="sinHistorialMotoescuela"
                >

                    <div class="historial-vacio-icono">

                        <i class="bi bi-clock"></i>

                    </div>

                    <h3>
                        Aún no hay actualizaciones
                    </h3>

                    <p>

                        Cuando se registre un cambio en tu proceso,
                        aparecerá en esta sección.

                    </p>

                </div>

                <!-- LÍNEA DE TIEMPO -->
                <div
                    class="linea-tiempo d-none"
                    id="lineaTiempoMotoescuela"
                >

                    <!-- Generado desde motoescuela.js -->

                </div>

            </section>

            <!--==================================================
                MENSAJE CURSO COMPLETADO
            ===================================================-->

            <section
                class="curso-completado d-none"
                id="mensajeCursoCompletado"
            >

                <div class="curso-completado-icono">

                    <i class="bi bi-trophy"></i>

                </div>

                <div>

                    <span>
                        PROCESO FINALIZADO
                    </span>

                    <h2>
                        ¡Felicidades, completaste tu formación!
                    </h2>

                    <p>

                        Has alcanzado el 100% del progreso registrado
                        en Moto Escuela.

                    </p>

                </div>

            </section>

        </div>

    </div>

</main>

<!--==================================================
    CONFIGURACIÓN PARA JAVASCRIPT
===================================================-->

<script>

    const RONEM_MOTOESCUELA_CONFIG = {

        api:
            "../../api/api_motoescuelau.php",

        idUsuario:
            <?= $idUsuario ?>

    };

</script>

<!-- BOOTSTRAP -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
></script>

<!-- JAVASCRIPT -->
<script
    src="../../js/motoescuela.js?v=1.0"
></script>

</body>

</html>