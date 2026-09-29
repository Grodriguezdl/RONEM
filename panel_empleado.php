<?php

declare(strict_types=1);

require_once __DIR__ . "/config/session.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/permisos.php";

if (!estaAutenticado()) {
    header("Location: index.php");
    exit;
}

if (
    !esSuperAdministrador() &&
    !tieneRol("administrador") &&
    !tieneRol("empleado")
) {
    header("Location: index.php");
    exit;
}

$nombreUsuario = $_SESSION["usuario"]["nombre"]
    ?? $_SESSION["nombre"]
    ?? "Empleado";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>RONEM – Panel de Empleado</title>

    <link rel="icon" type="image/png" href="favicoB.png?v=2">

    <link
        href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:wght@600;700&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="css/panel_empleado.css?v=2.0">
</head>

<body>

<nav class="navbar-ronem">

    <div class="nav-brand">
        <div class="nav-logo-box">
            <img
                src="favicoB.png?v=2"
                alt="RONEM"
                onerror="this.style.visibility='hidden'"
            >
        </div>

        <div class="nav-wordmark">
            <span class="nw-main">RONEM</span>
            <span class="nw-sub">RONEMMA INC</span>
        </div>
    </div>

    <div class="nav-links menu-empleado">

        <button
            class="nav-link-item menu-item active"
            data-seccion="inicio"
            type="button"
        >
            Inicio
        </button>

        <button
            class="nav-link-item menu-item"
            data-seccion="nueva-venta"
            type="button"
        >
            Nueva venta
        </button>

        <button
            class="nav-link-item menu-item"
            data-seccion="productos"
            type="button"
        >
            Productos
        </button>

        <button
            class="nav-link-item menu-item"
            data-seccion="clientes"
            type="button"
        >
            Clientes
        </button>

        <button
            class="nav-link-item menu-item"
            data-seccion="historial"
            type="button"
        >
            Historial
        </button>

        <button
            class="nav-link-item menu-item"
            data-seccion="canjes"
            type="button"
        >
            Canjes
        </button>

    </div>

    <div class="dropdown nav-user-dropdown">
        <button
            type="button"
            class="nav-user-button dropdown-toggle"
            id="menuUsuarioEmpleado"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >
            <span class="nav-user-avatar">
                <i class="bi bi-person-fill"></i>
            </span>

            <span class="nav-user-information">
                <strong class="nav-user-name">
                    <?= htmlspecialchars(
                        (string) $nombreUsuario,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </strong>

                <small class="nav-user-role">Empleado</small>
            </span>
        </button>

        <ul
            class="dropdown-menu dropdown-menu-end nav-user-menu"
            aria-labelledby="menuUsuarioEmpleado"
        >
            <li class="nav-user-menu-header">
                <span class="nav-user-menu-avatar">
                    <i class="bi bi-person-fill"></i>
                </span>

                <div>
                    <strong>
                        <?= htmlspecialchars(
                            (string) $nombreUsuario,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </strong>
                    <small>Empleado</small>
                </div>
            </li>

            <li><hr class="dropdown-divider"></li>

            <li>
                <a
                    class="dropdown-item nav-user-menu-link"
                    href="index.php?modo=sitio"
                >
                    <i class="bi bi-house-door"></i>
                    <span>Página principal</span>
                </a>
            </li>

            <li><hr class="dropdown-divider"></li>

            <li>
                <a
                    class="dropdown-item nav-user-menu-link nav-user-logout"
                    href="auth/logout.php"
                >
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Cerrar sesión</span>
                </a>
            </li>
        </ul>
    </div>

</nav>

<main class="contenido-empleado">

    <header class="encabezado-panel-empleado">
        <div>
            <span class="card-title-ronem" id="tituloSeccion">Inicio</span>
            <p class="dashboard-subtitle" id="descripcionSeccion">
                Resumen general de las operaciones.
            </p>
        </div>
    </header>

<!-- ==============================
     SECCIÓN INICIO
=============================== -->

<section
    class="seccion-panel activa"
    id="seccion-inicio"
>

    <div class="fila-tarjetas-resumen">

        <article class="tarjeta-resumen">

            <div class="tarjeta-icono">
                <i class="bi bi-cash-stack"></i>
            </div>

            <div>
                <span>Ventas registradas</span>

                <strong id="resumenTotalVentas">
                    0
                </strong>
            </div>

        </article>

        <article class="tarjeta-resumen">

            <div class="tarjeta-icono">
                <i class="bi bi-currency-dollar"></i>
            </div>

            <div>
                <span>Total vendido</span>

                <strong id="resumenMontoVentas">
                    Q0.00
                </strong>
            </div>

        </article>

        <article class="tarjeta-resumen">

            <div class="tarjeta-icono">
                <i class="bi bi-box-seam"></i>
            </div>

            <div>
                <span>Productos disponibles</span>

                <strong id="resumenProductos">
                    0
                </strong>
            </div>

        </article>

        <article class="tarjeta-resumen">

            <div class="tarjeta-icono">
                <i class="bi bi-gift"></i>
            </div>

            <div>
                <span>Canjes pendientes</span>

                <strong id="resumenCanjes">
                    0
                </strong>
            </div>

        </article>

    </div>


    <div class="panel-contenido">

        <div class="panel-encabezado">
            <div>
                <h3>Ventas recientes</h3>
                <p>
                    Últimas operaciones registradas.
                </p>
            </div>

            <button
                class="btn-principal"
                data-ir-seccion="nueva-venta"
                type="button"
            >
                <i class="bi bi-plus-lg"></i>
                Nueva venta
            </button>
        </div>

        <div class="tabla-responsive">

            <table class="tabla-panel">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Productos</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody id="tablaVentasRecientes">
                    <tr>
                        <td colspan="6">
                            Cargando ventas...
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

    </div>

</section>


<!-- ==============================
     SECCIÓN NUEVA VENTA
=============================== -->

<section
    class="seccion-panel"
    id="seccion-nueva-venta"
>

    <div class="venta-grid">

        <div class="panel-contenido">

            <div class="panel-encabezado">
                <div>
                    <h3>Seleccionar productos</h3>
                    <p>
                        Busca y agrega productos a la venta.
                    </p>
                </div>
            </div>

            <div class="campo-busqueda">
                <i class="bi bi-search"></i>

                <input
                    type="search"
                    id="buscarProductoVenta"
                    placeholder="Buscar producto..."
                >
            </div>

            <div
                class="lista-productos-venta"
                id="listaProductosVenta"
            >
                <p class="mensaje-vacio">
                    Cargando productos...
                </p>
            </div>

        </div>


        <div class="panel-contenido carrito-venta">

            <div class="panel-encabezado">
                <div>
                    <h3>Detalle de venta</h3>
                    <p>
                        Revisa los productos seleccionados.
                    </p>
                </div>
            </div>

            <div class="mb-3">

                <label
                    for="clienteVenta"
                    class="form-label"
                >
                    Cliente
                </label>

                <select
                    id="clienteVenta"
                    class="form-select"
                >
                    <option value="">
                        Consumidor final
                    </option>
                </select>

            </div>

            <div
                id="carritoVenta"
                class="carrito-lista"
            >
                <p class="mensaje-vacio">
                    No hay productos agregados.
                </p>
            </div>

            <div class="resumen-venta">

                <div>
                    <span>Total de productos</span>
                    <strong id="cantidadProductosVenta">
                        0
                    </strong>
                </div>

                <div class="total-final">
                    <span>Total</span>
                    <strong id="totalVenta">
                        Q0.00
                    </strong>
                </div>

            </div>

            <button
                class="btn-principal btn-procesar-venta"
                id="btnProcesarVenta"
                type="button"
                disabled
            >
                <i class="bi bi-check-circle"></i>
                Procesar venta
            </button>

        </div>

    </div>

</section>


<!-- ==============================
     SECCIÓN PRODUCTOS
=============================== -->

<section
    class="seccion-panel"
    id="seccion-productos"
>

    <div class="panel-contenido">

        <div class="panel-encabezado">
            <div>
                <h3>Productos</h3>
                <p>
                    Consulta precios, categoría y existencias.
                </p>
            </div>

            <div class="campo-busqueda campo-busqueda-corto">
                <i class="bi bi-search"></i>

                <input
                    type="search"
                    id="buscarProductos"
                    placeholder="Buscar producto..."
                >
            </div>
        </div>

        <div class="tabla-responsive">

            <table class="tabla-panel">

                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody id="tablaProductos">
                    <tr>
                        <td colspan="5">
                            Cargando productos...
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

    </div>

</section>


<!-- ==============================
     SECCIÓN CLIENTES
=============================== -->

<section
    class="seccion-panel"
    id="seccion-clientes"
>

    <div class="panel-contenido">

        <div class="panel-encabezado">
            <div>
                <h3>Clientes</h3>
                <p>
                    Consulta información y puntos disponibles.
                </p>
            </div>

            <div class="campo-busqueda campo-busqueda-corto">
                <i class="bi bi-search"></i>

                <input
                    type="search"
                    id="buscarClientes"
                    placeholder="Buscar cliente..."
                >
            </div>
        </div>

        <div class="tabla-responsive">

            <table class="tabla-panel">

                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Correo</th>
                        <th>Puntos</th>
                        <th>Verificado</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody id="tablaClientes">
                    <tr>
                        <td colspan="5">
                            Cargando clientes...
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

    </div>

</section>


<!-- ==============================
     SECCIÓN HISTORIAL
=============================== -->

<section
    class="seccion-panel"
    id="seccion-historial"
>

    <div class="panel-contenido">

        <div class="panel-encabezado">
            <div>
                <h3>Historial de ventas</h3>
                <p>
                    Consulta las ventas registradas.
                </p>
            </div>

            <button
                class="btn-secundario"
                id="btnActualizarHistorial"
                type="button"
            >
                <i class="bi bi-arrow-clockwise"></i>
                Actualizar
            </button>
        </div>

        <div class="tabla-responsive">

            <table class="tabla-panel">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Productos</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Detalle</th>
                    </tr>
                </thead>

                <tbody id="tablaHistorialVentas">
                    <tr>
                        <td colspan="7">
                            Cargando historial...
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

    </div>

</section>


<!-- ==============================
     SECCIÓN CANJES
=============================== -->

<section
    class="seccion-panel"
    id="seccion-canjes"
>

    <div class="panel-contenido">

        <div class="panel-encabezado">
            <div>
                <h3>Canjes</h3>
                <p>
                    Valida y administra los canjes de clientes.
                </p>
            </div>

            <div class="acciones-canjes">

                <select
                    id="filtroEstadoCanje"
                    class="form-select"
                >
                    <option value="">
                        Todos
                    </option>

                    <option value="Pendiente">
                        Pendientes
                    </option>

                    <option value="Disponible">
                        Disponibles
                    </option>

                    <option value="Utilizado">
                        Utilizados
                    </option>

                    <option value="Cancelado">
                        Cancelados
                    </option>
                </select>

                <div class="campo-busqueda campo-busqueda-corto">
                    <i class="bi bi-search"></i>

                    <input
                        type="search"
                        id="buscarCanjes"
                        placeholder="Código o cliente..."
                    >
                </div>

            </div>
        </div>

        <div class="tabla-responsive">

            <table class="tabla-panel">

                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th>Recompensa</th>
                        <th>Puntos</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody id="tablaCanjes">
                    <tr>
                        <td colspan="7">
                            Cargando canjes...
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

    </div>

</section>


</main>

<!-- ==============================
     MODAL DETALLE DE VENTA
=============================== -->

<div
    class="modal fade"
    id="modalDetalleVenta"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Detalle de venta
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>

            <div
                class="modal-body"
                id="contenidoDetalleVenta"
            >
                Cargando detalle...
            </div>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

<script src="js/panel_empleado.js"></script>

</body>
</html>