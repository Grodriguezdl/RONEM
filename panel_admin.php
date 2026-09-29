<?php

declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| DATOS DEL USUARIO EN SESIÓN
|--------------------------------------------------------------------------
*/

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
    $nombreCompletoSesion = "Administrador";
}

$rolSesion = trim(
    (string) (
        $_SESSION["rol_activo"]
        ?? $_SESSION["rol"]
        ?? $_SESSION["Rol"]
        ?? "Administrador"
    )
);

$rolSesion = ucfirst($rolSesion);

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8"/>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    />

    <title>
        RONEM – Panel Administrativo
    </title>

    <link
        rel="icon"
        type="image/png"
        href="favicoB.png?v=2"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:wght@600;700&display=swap"
        rel="stylesheet"
    />

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    />

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    />

    <link
        rel="stylesheet"
        href="css/stylesadmin.css?v=2.0"
    />

    <style>

        .input-noticia-oculto {
            position: absolute;

            width: 1px;
            height: 1px;

            opacity: 0;
            overflow: hidden;

            pointer-events: none;
        }

        #btnSeleccionarNoticia {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            margin: 0;

            cursor: pointer;
        }

    </style>

</head>

<body>

<nav class="navbar-ronem">

    <!-- LOGO Y MARCA -->
    <div class="nav-brand">

        <div class="nav-logo-box">

            <img
                src="favicoB.png?v=2"
                alt="RONEM"
                onerror="this.style.visibility='hidden'"
            />

        </div>

        <div class="nav-wordmark">

            <span class="nw-main">
                RONEM
            </span>

            <span class="nw-sub">
                RONEMMA INC
            </span>

        </div>

    </div>


    <!-- ENLACES DEL PANEL -->
    <div class="nav-links">

        <div
            class="nav-link-item active"
            onclick="showPage('dashboard', this)"
        >
            Dashboard
        </div>

        <div
            class="nav-link-item"
            onclick="showPage('usuarios', this)"
        >
            Usuarios
        </div>

        <div
            class="nav-link-item"
            onclick="showPage('noticias', this)"
        >
            Noticias
        </div>

        <div
            class="nav-link-item"
            onclick="showPage('solicitudes', this)"
        >
            Solicitudes
        </div>

        <div
            class="nav-link-item"
            onclick="showPage('seguimiento-motoescuela', this)"
        >
            Seguimiento
        </div>

        <div
            class="nav-link-item"
            onclick="showPage('productos', this)"
        >
            Productos
        </div>

        <div
            class="nav-link-item"
            onclick="showPage('servicios', this)"
        >
            Servicios
        </div>

        <div
            class="nav-link-item"
            onclick="showPage('ventas', this)"
        >
            Ventas
        </div>

        <div
            class="nav-link-item"
            onclick="showPage('repuestos', this)"
        >
            Repuestos
        </div>

    </div>


    <!-- MENÚ DEL USUARIO -->
    <div class="dropdown nav-user-dropdown">

        <button
            type="button"
            class="nav-user-button dropdown-toggle"
            id="menuUsuarioAdministrador"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >

            <span class="nav-user-avatar">

                <i class="bi bi-person-fill"></i>

            </span>

            <span class="nav-user-information">

                <strong class="nav-user-name">

                    <?= htmlspecialchars(
                        $nombreCompletoSesion,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>

                </strong>

                <small class="nav-user-role">

                    <?= htmlspecialchars(
                        $rolSesion,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>

                </small>

            </span>

        </button>


        <ul
            class="dropdown-menu dropdown-menu-end nav-user-menu"
            aria-labelledby="menuUsuarioAdministrador"
        >

            <li class="nav-user-menu-header">

                <span class="nav-user-menu-avatar">

                    <i class="bi bi-person-fill"></i>

                </span>

                <div>

                    <strong>

                        <?= htmlspecialchars(
                            $nombreCompletoSesion,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </strong>

                    <small>

                        <?= htmlspecialchars(
                            $rolSesion,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </small>

                </div>

            </li>


            <li>

                <hr class="dropdown-divider">

            </li>


            <li>

                <a
                    class="dropdown-item nav-user-menu-link"
                    href="index.php?modo=sitio"
                >

                    <i class="bi bi-house-door"></i>

                    <span>
                        Página principal
                    </span>

                </a>

            </li>

            <li>

                <hr class="dropdown-divider">

            </li>


            <li>

                <a
                    class="dropdown-item nav-user-menu-link nav-user-logout"
                    href="auth/logout.php"
                >

                    <i class="bi bi-box-arrow-right"></i>

                    <span>
                        Cerrar sesión
                    </span>

                </a>

            </li>

        </ul>

    </div>

</nav>


<!-- ==================================================
     DASHBOARD
     ================================================== -->

<div class="page active" id="page-dashboard">

  <div id="mensajeDashboard"></div>

  <div class="dashboard-header">
    <div>
      <span class="card-title-ronem">Dashboard Administrativo</span>
      <p class="dashboard-subtitle">
        Resumen general de las operaciones de RONEM
      </p>
    </div>

    <button
      type="button"
      class="btn-ronem dashboard-update-btn"
      id="btnActualizarDashboard"
      onclick="actualizarDashboard()"
    >
      <i class="bi bi-arrow-clockwise"></i>
      Actualizar
    </button>
  </div>

  <div class="dashboard-grid">

    <div class="dashboard-card">
      <div class="dashboard-card-icon">
        <i class="bi bi-cart-check"></i>
      </div>
      <div class="dashboard-card-content">
        <span class="dashboard-card-title">Ventas hoy</span>
        <strong class="dashboard-card-value" id="dashVentasHoy">0</strong>
        <span class="dashboard-card-detail" id="dashTotalVentasHoy">Q0.00</span>
      </div>
    </div>

    <div class="dashboard-card">
      <div class="dashboard-card-icon">
        <i class="bi bi-bag-check"></i>
      </div>
      <div class="dashboard-card-content">
        <span class="dashboard-card-title">Compras hoy</span>
        <strong class="dashboard-card-value" id="dashCompras">0</strong>
        <span class="dashboard-card-detail" id="dashTotalCompras">Q0.00</span>
      </div>
    </div>

    <div class="dashboard-card">
      <div class="dashboard-card-icon">
        <i class="bi bi-people"></i>
      </div>
      <div class="dashboard-card-content">
        <span class="dashboard-card-title">Clientes</span>
        <strong class="dashboard-card-value" id="dashClientes">0</strong>
        <span class="dashboard-card-detail">Clientes registrados</span>
      </div>
    </div>

    <div class="dashboard-card">
      <div class="dashboard-card-icon">
        <i class="bi bi-bicycle"></i>
      </div>
      <div class="dashboard-card-content">
        <span class="dashboard-card-title">Vehículos</span>
        <strong class="dashboard-card-value" id="dashVehiculos">0</strong>
        <span class="dashboard-card-detail">Vehículos registrados</span>
      </div>
    </div>

    <div class="dashboard-card">
      <div class="dashboard-card-icon">
        <i class="bi bi-tools"></i>
      </div>
      <div class="dashboard-card-content">
        <span class="dashboard-card-title">Técnicos activos</span>
        <strong class="dashboard-card-value" id="dashTecnicos">0</strong>
        <span class="dashboard-card-detail">Personal disponible</span>
      </div>
    </div>

    <div class="dashboard-card dashboard-card-warning">
      <div class="dashboard-card-icon">
        <i class="bi bi-exclamation-triangle"></i>
      </div>
      <div class="dashboard-card-content">
        <span class="dashboard-card-title">Productos bajos</span>
        <strong class="dashboard-card-value" id="dashProductosBajos">0</strong>
        <span class="dashboard-card-detail">Stock igual o menor a 10</span>
      </div>
    </div>

  </div>

  <div class="dashboard-summary">

    <div class="card-ronem dashboard-summary-card">
      <span class="card-title-ronem">Resumen mensual</span>

      <div class="monthly-summary">
        <div class="monthly-summary-item">
          <span class="monthly-summary-label">Ventas del mes actual</span>
          <strong class="monthly-summary-value" id="dashVentasMesActual">Q0.00</strong>
        </div>

        <div class="monthly-summary-divider"></div>

        <div class="monthly-summary-item">
          <span class="monthly-summary-label">Ventas del mes anterior</span>
          <strong class="monthly-summary-value" id="dashVentasMesAnterior">Q0.00</strong>
        </div>
      </div>

      <div
        class="dashboard-comparison comparacion-neutral"
        id="dashComparacionMensual"
      >
        <i class="bi bi-dash-lg"></i>
        Sin variación respecto al mes anterior
      </div>
    </div>

  </div>

  <div class="card-ronem dashboard-chart-card">
    <div class="dashboard-card-header">
      <div>
        <span class="card-title-ronem">Ventas mensuales</span>
        <p class="dashboard-section-description">
          Comportamiento de las ventas durante los últimos meses
        </p>
      </div>
    </div>

    <div class="dashboard-chart-container">
      <canvas id="graficaVentasDashboard"></canvas>
    </div>
  </div>

  <div class="card-ronem dashboard-table-card">
    <div class="dashboard-card-header">
      <div>
        <span class="card-title-ronem">Productos con stock bajo</span>
        <p class="dashboard-section-description">
          Productos que deben ser reabastecidos
        </p>
      </div>

      <button
        type="button"
        class="btn-ronem dashboard-small-btn"
        onclick="abrirPaginaDashboard('productos')"
      >
        <i class="bi bi-box-seam"></i>
        Ver productos
      </button>
    </div>

    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Stock</th>
            <th>Precio</th>
            <th>Estado</th>
          </tr>
        </thead>

        <tbody id="tbodyProductosBajosDashboard">
          <tr class="empty-row">
            <td colspan="5">Cargando productos...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<div class="page" id="page-usuarios">
  <div class="card-ronem">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <span class="card-title-ronem">Gestión de Usuarios</span>
      <div class="d-flex gap-2">
        <button class="btn-ronem btn-pdf" type="button" onclick="descargarTablaPDF('usuarios')">
          <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
        </button>
        <button class="btn-ronem" data-bs-toggle="modal" data-bs-target="#modalUsuario">
          <i class="bi bi-plus-lg"></i> Agregar Usuario
        </button>
      </div>
    </div>
    <div class="search-wrap">
      <i class="bi bi-search si"></i>
      <input class="search-input" id="searchUsuario" type="text" placeholder="Buscar por nombre o email…"/>
    </div>
    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tbodyUsuarios">
          <tr class="empty-row">
            <td colspan="7">No hay usuarios registrados</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="page" id="page-noticias">
  <div class="card-ronem">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h2 style="font-family:'Barlow Condensed',sans-serif;font-size:22px;font-weight:700;margin:0;">Agregar Nueva Imagen al Banner</h2>
      <button class="btn-ronem btn-pdf" type="button" onclick="descargarTablaPDF('noticias')">
        <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
      </button>
    </div>
    <div class="form-section-bg">
      <div class="mb-3">
        <label class="fw-semibold" style="font-size:14px;">Seleccionar Imagen:</label>
        <div class="file-drop mt-2">
          <input
            type="file"
            id="inputImgNoticia"
            name="imagen"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            class="input-noticia-oculto"
          />
          <label
            for="inputImgNoticia"
            class="btn-ronem btn-ronem-sm"
            id="btnSeleccionarNoticia"
          >
            <i class="bi bi-image"></i>
            Seleccionar archivo
          </label>
          <span id="nombreImgNoticia" style="font-size:14px;color:var(--text-muted);">
            Sin archivos seleccionados
          </span>
        </div>
      </div>
      <div class="mb-3">
        <label class="fw-semibold" style="font-size:14px;">Descripción:</label>
        <textarea class="ronem-textarea mt-2" id="descNoticia" placeholder="Escribe una descripción…"></textarea>
      </div>
      <button
        type="button"
        class="btn-ronem"
        id="btnGuardarNoticia"
        onclick="agregarNoticia()"
      >
        <i class="bi bi-plus-lg"></i>
        Agregar Imagen
      </button>
    </div>
    <div class="section-gap">
      <div style="font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:18px;margin-bottom:14px;">Imágenes en Noticias / Novedades</div>
      <div class="tbl-wrap">
        <table>
          <thead><tr><th>Imagen</th><th>Descripción</th><th>Fecha de Subida</th><th>Acción</th></tr></thead>
          <tbody id="tbodyNoticias"><tr class="empty-row"><td colspan="4">No hay imágenes registradas</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<div class="page" id="page-solicitudes">
  <div class="card-ronem">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <span class="card-title-ronem">Solicitudes Moto Escuela</span>
      <div class="d-flex align-items-center gap-2">
        <span class="summary-chip" id="chipSolicitudes">0 solicitudes</span>
        <button class="btn-ronem btn-pdf" type="button" onclick="descargarTablaPDF('solicitudes')">
          <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
        </button>
      </div>
    </div>

    <div class="search-wrap">
      <i class="bi bi-search si"></i>
      <input class="search-input" id="buscarSolicitud" type="text" placeholder="Buscar solicitud…"/>
    </div>

    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>DPI</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Nivel</th>
            <th>Tipo Lic.</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>

        <tbody id="tbodySolicitudes">
          <tr class="empty-row">
            <td colspan="10">No hay solicitudes registradas</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>


<!-- ==================================================
     SEGUIMIENTO MOTO ESCUELA
     ================================================== -->
<div class="page" id="page-seguimiento-motoescuela">
  <div class="card-ronem">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <span class="card-title-ronem">Seguimiento Moto Escuela</span>
        <p class="text-muted mb-0 mt-1">Consulta todos los registros y actualiza el progreso de cada alumno.</p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="summary-chip" id="seguimientoChipSolicitudes">0 registros</span>
        <button class="btn-ronem" type="button" onclick="recargarSeguimientoMotoescuela()"><i class="bi bi-arrow-clockwise"></i> Actualizar</button>
      </div>
    </div>
    <div class="search-wrap">
      <i class="bi bi-search si"></i>
      <input class="search-input" id="seguimientoBuscarSolicitud" type="text" placeholder="Buscar alumno, DPI, correo, nivel o estado…"/>
    </div>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>ID</th><th>Alumno</th><th>DPI</th><th>Teléfono</th><th>Correo</th><th>Nivel</th><th>Tipo Lic.</th><th>Fecha</th><th>Progreso</th><th>Acciones</th></tr></thead>
        <tbody id="seguimientoTbodySolicitudes"><tr class="empty-row"><td colspan="10">Cargando registros de seguimiento…</td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<div class="page" id="page-productos">
  <div class="card-ronem">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <span class="card-title-ronem">Gestión de Productos</span>

      <div class="d-flex gap-2">
        <button class="btn-ronem btn-pdf" type="button" onclick="descargarTablaPDF('productos')">
          <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
        </button>

        <button class="btn-ronem" data-bs-toggle="modal" data-bs-target="#modalProducto">
          <i class="bi bi-plus-lg"></i>
          Agregar Producto
        </button>
      </div>
    </div>

    <div class="search-wrap">
      <i class="bi bi-search si"></i>
      <input
        class="search-input"
        id="searchProducto"
        type="text"
        placeholder="Buscar producto…"
      />
    </div>

    <div class="tbl-wrap">
      <table>

        <thead>
          <tr>
            <th>ID</th>
            <th>Imagen</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Categoría</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>

        <tbody id="tbodyProductos">
          <tr class="empty-row">
            <td colspan="9">
              Cargando productos…
            </td>
          </tr>
        </tbody>

      </table>
    </div>

  </div>
</div>

<!-- ==================================================
     SERVICIOS
     ================================================== -->

<div class="page" id="page-servicios">

  <div class="card-ronem">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <span class="card-title-ronem">Gestión de Servicios</span>

      <div class="d-flex align-items-center gap-2">
        <span class="summary-chip" id="chipServicios">0 servicios</span>

        <button
          class="btn-ronem btn-pdf"
          type="button"
          onclick="descargarTablaPDF('servicios')"
        >
          <i class="bi bi-file-earmark-pdf"></i>
          Descargar PDF
        </button>

        <button
          type="button"
          class="btn-ronem"
          data-bs-toggle="modal"
          data-bs-target="#modalServicio"
        >
          <i class="bi bi-plus-lg"></i>
          Agregar Servicio
        </button>
      </div>
    </div>

    <div class="search-wrap">
      <i class="bi bi-search si"></i>

      <input
        class="search-input"
        id="buscarServicio"
        type="text"
        placeholder="Buscar servicio..."
      />
    </div>

    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Imagen</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Duración</th>
            <th>Vehículo</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>

        <tbody id="tbodyServicios">
          <tr class="empty-row">
            <td colspan="9">
              No hay servicios registrados
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>

</div>


<div class="page" id="page-ventas">

  <div class="card-ronem">

    <div class="d-flex align-items-center justify-content-between mb-3">

      <span class="card-title-ronem">
        Detalles de Ventas
      </span>

      <div class="d-flex align-items-center gap-2">

        <span class="summary-chip" id="chipVentas">
          0 detalles
        </span>

        <button
          class="btn-ronem btn-pdf"
          type="button"
          onclick="descargarTablaPDF('ventas')"
        >
          <i class="bi bi-file-earmark-pdf"></i>
          Descargar PDF
        </button>

      </div>

    </div>

    <div class="search-wrap">

      <i class="bi bi-search si"></i>

      <input
        class="search-input"
        id="buscarVenta"
        type="text"
        placeholder="Buscar venta…"
      />

    </div>

    <div class="tbl-wrap">

      <table>

        <thead>

          <tr>
            <th>ID Detalle</th>
            <th>ID Venta</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unidad</th>
            <th>Subtotal</th>
            <th>Acciones</th>
          </tr>

        </thead>

        <tbody id="tbodyVentas">

          <tr class="empty-row">
            <td colspan="7">
              No hay ventas registradas
            </td>
          </tr>

        </tbody>

      </table>

    </div>

  </div>

</div>


<!-- ==================================================
     REPUESTOS
     ================================================== -->

<div class="page" id="page-repuestos">

  <div class="card-ronem">

    <div class="d-flex align-items-center justify-content-between mb-3">

      <span class="card-title-ronem">
        Gestión de Repuestos
      </span>

      <div class="d-flex gap-2">

        <button
          class="btn-ronem btn-pdf"
          type="button"
          onclick="descargarTablaPDF('repuestos')"
        >
          <i class="bi bi-file-earmark-pdf"></i>
          Descargar PDF
        </button>

        <button
          type="button"
          class="btn-ronem"
          data-bs-toggle="modal"
          data-bs-target="#modalRepuesto"
        >
          <i class="bi bi-plus-lg"></i>
          Agregar Repuesto
        </button>

      </div>

    </div>

    <div class="search-wrap">

      <i class="bi bi-search si"></i>

      <input
        class="search-input"
        id="buscarRepuesto"
        type="text"
        placeholder="Buscar repuesto..."
      />

    </div>

    <div class="tbl-wrap">

      <table>

        <thead>
          <tr>
            <th>ID</th>
            <th>Imagen</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>URL</th>
            <th>Stock</th>
            <th>Precio</th>
            <th>Acciones</th>
          </tr>
        </thead>

        <tbody id="tbodyRepuestos">

          <tr class="empty-row">
            <td colspan="8">
              No hay repuestos registrados
            </td>
          </tr>

        </tbody>

      </table>

    </div>

  </div>

</div>

<div class="modal fade"
     id="modalUsuario"
     tabindex="-1"
     aria-labelledby="tituloModalUsuario"
     aria-hidden="true">

  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">

    <div class="modal-content">

      <div class="modal-header">

        <h5 class="modal-title" id="tituloModalUsuario">
          Agregar Nuevo Usuario
        </h5>

        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Cerrar">
        </button>

      </div>

      <div class="modal-body">

        <form id="formUsuario" novalidate>

          <input type="hidden" id="usuarioId"/>

          <div class="row g-3">

            <div class="col-md-6">

              <label class="form-label" for="uNombre">
                Nombre
              </label>

              <input
                type="text"
                class="form-control"
                id="uNombre"
                placeholder="Ej. Ana"
                minlength="2"
                maxlength="50"
                required
              />

            </div>

            <div class="col-md-6">

              <label class="form-label" for="uApellido">
                Apellido
              </label>

              <input
                type="text"
                class="form-control"
                id="uApellido"
                placeholder="Ej. García"
                minlength="2"
                maxlength="50"
                required
              />

            </div>

            <div class="col-md-6">

              <label class="form-label" for="uEmail">
                Correo electrónico
              </label>

              <input
                type="email"
                class="form-control"
                id="uEmail"
                placeholder="usuario@email.com"
                maxlength="100"
                required
              />

            </div>

            <div class="col-md-6">

              <label class="form-label" for="uPassword">
                Contraseña
              </label>

              <input
                type="password"
                class="form-control"
                id="uPassword"
                placeholder="Mínimo 8 caracteres"
                minlength="8"
                maxlength="72"
                required
              />

              <small class="text-muted" id="ayudaPassword">
                Obligatoria al crear el usuario.
              </small>

            </div>

            <div class="col-md-6">

              <label class="form-label" for="uRol">
                Rol
              </label>

              <select
                class="form-select"
                id="uRol"
                required>

                <option value="">
                  Seleccionar rol
                </option>

              </select>

            </div>

            <div class="col-md-6">

              <label class="form-label" for="uEstado">
                Estado
              </label>

              <select
                class="form-select"
                id="uEstado"
                required>

                <option value="1">
                  Activo
                </option>

                <option value="0">
                  Inactivo
                </option>

              </select>

            </div>

          </div>

        </form>

      </div>

      <div class="modal-footer">

        <button
          type="button"
          class="modal-save-btn"
          id="btnGuardarUsuario"
          onclick="guardarUsuario()"
        >

          <i class="bi bi-floppy me-2"></i>

          Guardar Usuario

        </button>

      </div>

    </div>

  </div>

</div>
<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="tituloModalProducto" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalProducto">Agregar Nuevo Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formProducto">
          <input type="hidden" id="productoId"/>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Nombre del producto</label>
              <input type="text" class="form-control" id="pNombre" placeholder="Ej. Casco Integral XR" required/>
            </div>
            <div class="col-12">
              <label class="form-label">Descripción</label>
              <textarea class="form-control" id="pDescripcion" rows="3" placeholder="Descripción del producto…"></textarea>
            </div>
            <div class="col-6">
              <label class="form-label">Precio (Q)</label>
              <input type="number" class="form-control" id="pPrecio" placeholder="0.00" min="0" step="0.01" required/>
            </div>
            <div class="col-6">
              <label class="form-label">Stock</label>
              <input type="number" class="form-control" id="pStock" placeholder="0" min="0" required/>
            </div>
            <div class="col-6">
              <label class="form-label">Categoría</label>
              <select class="form-select" id="pCategoria" required>
                <option value="">Seleccionar categoría</option>
                <option value="Accesorios">Accesorios</option>
                <option value="Repuestos">Repuestos</option>
                <option value="Indumentaria">Indumentaria</option>
                <option value="Lubricantes">Lubricantes</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Estado</label>
              <select class="form-select" id="pEstado">
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Imagen del producto</label>
              <input type="file" class="form-control" id="pImagen" accept="image/*"/>
              <div class="mt-2 text-center" id="previsualizacionContenedor" style="display:none;">
                <p class="text-muted small mb-1">Vista previa:</p>
                <img id="pImpVg" src="" alt="Vista previa" style="max-height: 120px; border-radius: 8px; border: 1px solid #ddd; padding: 4px;"/>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="modal-save-btn" onclick="guardarProducto()">
          <i class="bi bi-floppy me-2"></i>Guardar Producto
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ==================================================
     MODAL REPUESTOS
     ================================================== -->

<div
  class="modal fade"
  id="modalRepuesto"
  tabindex="-1"
  aria-labelledby="tituloModalRepuesto"
  aria-hidden="true"
>
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalRepuesto">
          Agregar Nuevo Repuesto
        </h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Cerrar"
        ></button>
      </div>

      <div class="modal-body">
        <form
          id="formRepuesto"
          enctype="multipart/form-data"
          novalidate
        >
          <input type="hidden" id="repuestoId"/>

          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="rNombre">
                Nombre del repuesto
              </label>
              <input
                type="text"
                class="form-control"
                id="rNombre"
                placeholder="Ej. Filtro de aire Yamaha"
                maxlength="100"
                required
              />
            </div>

            <div class="col-12">
              <label class="form-label" for="rDescripcion">
                Descripción
              </label>
              <textarea
                class="form-control"
                id="rDescripcion"
                rows="3"
                placeholder="Descripción del repuesto..."
                maxlength="255"
              ></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label" for="rStock">Stock</label>
              <input
                type="number"
                class="form-control"
                id="rStock"
                min="0"
                step="1"
                required
              />
            </div>

            <div class="col-md-6">
              <label class="form-label" for="rPrecio">Precio (Q)</label>
              <input
                type="number"
                class="form-control"
                id="rPrecio"
                min="0"
                step="0.01"
                required
              />
            </div>

            <div class="col-12">
              <label class="form-label" for="rImagen">
                Imagen del repuesto
              </label>
              <input
                type="file"
                class="form-control"
                id="rImagen"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
              />
              <small class="text-muted">
                Formatos permitidos: JPG, JPEG, PNG y WEBP.
              </small>
            </div>

            <div
              class="col-12 text-center"
              id="contenedorVistaRepuesto"
              style="display:none;"
            >
              <p class="text-muted small mb-2">Vista previa</p>
              <img
                id="vistaImagenRepuesto"
                src=""
                alt="Vista previa"
                style="width:140px;height:110px;object-fit:contain;border:1px solid #ddd;border-radius:8px;padding:5px;background:#fff;"
              />
            </div>

            <div
              class="col-12"
              id="contenedorRutaRepuesto"
              style="display:none;"
            >
              <label class="form-label" for="rImagenActual">
                URL actual
              </label>
              <input
                type="text"
                class="form-control"
                id="rImagenActual"
                readonly
              />
              <small class="text-muted">
                Selecciona otra imagen solamente para reemplazar la actual.
              </small>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button
          type="button"
          class="modal-save-btn"
          id="btnGuardarRepuesto"
          onclick="guardarRepuesto()"
        >
          <i class="bi bi-floppy me-2"></i>
          Guardar Repuesto
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalSolicitud" tabindex="-1" aria-labelledby="tituloModalSolicitud" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalSolicitud">Detalle de Solicitud — Moto Escuela</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" id="sNombre" readonly/>
          </div>
          <div class="col-md-6">
            <label class="form-label">Apellido</label>
            <input type="text" class="form-control" id="sApellido" readonly/>
          </div>
          <div class="col-md-6">
            <label class="form-label">DPI</label>
            <input type="text" class="form-control" id="sDpi" readonly/>
          </div>
          <div class="col-md-6">
            <label class="form-label">Teléfono</label>
            <input type="text" class="form-control" id="sTelefono" readonly/>
          </div>
          <div class="col-md-6">
            <label class="form-label">Correo</label>
            <input type="email" class="form-control" id="sCorreo" readonly/>
          </div>
          <div class="col-md-6">
            <label class="form-label">Fecha de solicitud</label>
            <input type="text" class="form-control" id="sFecha" readonly/>
          </div>
          <div class="col-md-6">
            <label class="form-label">Nivel</label>
            <input type="text" class="form-control" id="sNivel" readonly/>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tipo de licencia</label>
            <input type="text" class="form-control" id="sTipoLic" readonly/>
          </div>
          <div class="col-12">
            <label class="form-label">Actualizar estado</label>
            <select class="form-select" id="sEstadoSelect">
              <option value="">Seleccione un estado</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Observación</label>
            <textarea class="form-control" id="sObservacion" rows="3" placeholder="Observación del cambio de estado…"></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Historial</label>
            <div id="historialSolicitud" class="border rounded p-3"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button
          type="button"
          id="btnActualizarSolicitud"
          class="modal-save-btn"
          onclick="actualizarEstadoSolicitud()"
        >
          <i class="bi bi-check2-circle me-2"></i>Actualizar Estado
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ==================================================
     MODAL SEGUIMIENTO MOTO ESCUELA
     ================================================== -->

<div
  class="modal fade"
  id="modalSeguimientoMotoescuela"
  tabindex="-1"
  aria-labelledby="tituloModalSeguimientoMotoescuela"
  aria-hidden="true"
>
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1" id="tituloModalSeguimientoMotoescuela">
            Seguimiento de Solicitud — Moto Escuela
          </h5>
          <small class="text-muted">
            Consulta los datos, el progreso y el historial del aspirante.
          </small>
        </div>

        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Cerrar"
        ></button>
      </div>

      <form id="formSeguimientoMotoescuela">
        <div class="modal-body">

          <input
            type="hidden"
            id="seguimientoIdSolicitud"
          />

          <?php
          $idUsuarioSesion =
              $_SESSION["Id_usuario"] ??
              $_SESSION["id_usuario"] ??
              $_SESSION["usuario_id"] ??
              $_SESSION["user_id"] ??
              "";
          ?>

          <input
            type="hidden"
            id="idUsuarioSesion"
            value="<?= htmlspecialchars((string) $idUsuarioSesion, ENT_QUOTES, 'UTF-8') ?>"
          />

          <div class="row g-4">

            <div class="col-lg-7">

              <div class="border rounded-3 p-3 h-100">
                <div class="d-flex align-items-center gap-2 mb-3">
                  <i class="bi bi-person-vcard fs-5"></i>
                  <h6 class="mb-0 fw-bold">Información del aspirante</h6>
                </div>

                <div id="seguimientoDatosAlumno">
                  <div class="text-center text-muted py-4">
                    Selecciona una solicitud para consultar su información.
                  </div>
                </div>
              </div>

            </div>

            <div class="col-lg-5">

              <div class="border rounded-3 p-3 mb-4">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                  <div>
                    <small class="text-muted d-block">
                      Estado actual
                    </small>

                    <strong id="seguimientoEstadoActual">
                      Sin estado
                    </strong>
                  </div>

                  <span
                    class="summary-chip"
                    id="seguimientoPorcentaje"
                  >
                    0%
                  </span>
                </div>

                <p
                  class="small text-muted mb-3"
                  id="seguimientoDescripcionEstado"
                ></p>

                <div
                  class="progress"
                  style="height:22px;"
                >
                  <div
                    class="progress-bar progress-bar-striped progress-bar-animated"
                    id="seguimientoBarraProgreso"
                    role="progressbar"
                    style="width:0%;"
                    aria-valuenow="0"
                    aria-valuemin="0"
                    aria-valuemax="100"
                  >
                    0%
                  </div>
                </div>
              </div>

              <div class="form-section-bg">
                <h6 class="fw-bold mb-3">
                  <i class="bi bi-arrow-repeat me-1"></i>
                  Actualizar seguimiento
                </h6>

                <div class="mb-3">
                  <label
                    class="form-label"
                    for="seguimientoEstadoNuevo"
                  >
                    Nuevo estado
                  </label>

                  <select
                    class="form-select"
                    id="seguimientoEstadoNuevo"
                    required
                  >
                    <option value="">
                      Seleccione un estado
                    </option>
                  </select>

                  <div id="seguimientoVistaPreviaEstado"></div>
                </div>

                <div>
                  <label
                    class="form-label"
                    for="seguimientoComentario"
                  >
                    Comentario u observación
                  </label>

                  <textarea
                    class="form-control"
                    id="seguimientoComentario"
                    rows="4"
                    maxlength="5000"
                    placeholder="Describe el avance, resultado o información importante del cambio..."
                  ></textarea>
                </div>
              </div>

            </div>

            <div class="col-12">
              <div class="border rounded-3 p-3">
                <div class="d-flex align-items-center gap-2 mb-3">
                  <i class="bi bi-clock-history fs-5"></i>
                  <h6 class="mb-0 fw-bold">Historial de seguimiento</h6>
                </div>

                <div id="seguimientoHistorial">
                  <div class="text-center text-muted py-4">
                    Todavía no se ha cargado el historial.
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            Cerrar
          </button>

          <button
            type="submit"
            class="modal-save-btn"
            id="btnActualizarSeguimiento"
          >
            <i class="bi bi-check2-circle me-2"></i>
            Actualizar estado
          </button>
        </div>
      </form>

    </div>
  </div>
</div>


<!-- ==================================================
     MODAL SERVICIOS
     ================================================== -->

<div
  class="modal fade"
  id="modalServicio"
  tabindex="-1"
  aria-labelledby="tituloModalServicio"
  aria-hidden="true"
>
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalServicio">
          Agregar Nuevo Servicio
        </h5>

        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Cerrar"
        ></button>
      </div>

      <div class="modal-body">
        <form
          id="formServicio"
          enctype="multipart/form-data"
          novalidate
        >
          <input type="hidden" id="servicioId"/>

          <div class="row g-3">

            <div class="col-md-8">
              <label class="form-label" for="svNombre">
                Nombre del servicio
              </label>

              <input
                type="text"
                class="form-control"
                id="svNombre"
                maxlength="120"
                placeholder="Ej. Mantenimiento preventivo"
                required
              />
            </div>

            <div class="col-md-4">
              <label class="form-label" for="svPrecio">
                Precio (Q)
              </label>

              <input
                type="number"
                class="form-control"
                id="svPrecio"
                min="0"
                step="0.01"
                placeholder="0.00"
                required
              />
            </div>

            <div class="col-12">
              <label class="form-label" for="svDescripcion">
                Descripción
              </label>

              <textarea
                class="form-control"
                id="svDescripcion"
                rows="4"
                maxlength="1000"
                placeholder="Describe lo que incluye el servicio..."
                required
              ></textarea>
            </div>

            <div class="col-md-4">
              <label class="form-label" for="svDuracion">
                Duración aproximada
              </label>

              <input
                type="text"
                class="form-control"
                id="svDuracion"
                maxlength="80"
                placeholder="Ej. 2 horas"
                required
              />
            </div>

            <div class="col-md-4">
              <label class="form-label" for="svTipoVehiculo">
                Tipo de vehículo
              </label>

              <select
                class="form-select"
                id="svTipoVehiculo"
                required
              >
                <option value="">Seleccionar</option>
                <option value="Moto">Moto</option>
                <option value="Auto">Auto</option>
                <option value="Ambos">Ambos</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label" for="svEstado">
                Estado
              </label>

              <select
                class="form-select"
                id="svEstado"
                required
              >
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label" for="svImagen">
                Imagen del servicio
              </label>

              <input
                type="file"
                class="form-control"
                id="svImagen"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
              />

              <small class="text-muted">
                Formatos permitidos: JPG, JPEG, PNG y WEBP.
              </small>
            </div>

            <div
              class="col-12 text-center"
              id="contenedorVistaServicio"
              style="display:none;"
            >
              <p class="text-muted small mb-2">
                Vista previa
              </p>

              <img
                id="vistaImagenServicio"
                src=""
                alt="Vista previa del servicio"
                style="width:180px;height:120px;object-fit:cover;border:1px solid #ddd;border-radius:8px;padding:5px;background:#fff;"
              />
            </div>

            <div
              class="col-12"
              id="contenedorRutaServicio"
              style="display:none;"
            >
              <label class="form-label" for="svImagenActual">
                URL actual
              </label>

              <input
                type="text"
                class="form-control"
                id="svImagenActual"
                readonly
              />
            </div>

          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button
          type="button"
          class="modal-save-btn"
          id="btnGuardarServicio"
        >
          <i class="bi bi-floppy me-2"></i>
          Guardar Servicio
        </button>
      </div>

    </div>
  </div>
</div>


<div class="modal fade" id="modalDetalleVenta" tabindex="-1" aria-labelledby="tituloModalDetalleVenta" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalDetalleVenta">Detalle de Venta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-6">
            <label class="form-label">ID detalle</label>
            <input id="vIdDetalle" class="form-control" readonly/>
          </div>
          <div class="col-6">
            <label class="form-label">ID venta</label>
            <input id="vIdVenta" class="form-control" readonly/>
          </div>
          <div class="col-12">
            <label class="form-label">Producto</label>
            <input id="vProducto" class="form-control" readonly/>
          </div>
          <div class="col-4">
            <label class="form-label">Cantidad</label>
            <input id="vCantidad" class="form-control" readonly/>
          </div>
          <div class="col-4">
            <label class="form-label">Precio unidad</label>
            <input id="vPrecioUnidad" class="form-control" readonly/>
          </div>
          <div class="col-4">
            <label class="form-label">Subtotal</label>
            <input id="vSubtotal" class="form-control" readonly/>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalVentaCompleta" tabindex="-1" aria-labelledby="tituloModalVentaCompleta" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalVentaCompleta">
          Venta #<span id="numeroVentaCompleta"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="tbl-wrap">
          <table>
            <thead>
              <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio unidad</th>
                <th>Subtotal</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody id="tbodyDetalleVentaCompleta"></tbody>
          </table>
        </div>
        <div class="text-end mt-3">
          <strong>Total: <span id="totalVentaCompleta">Q0.00</span></strong>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showPage(id, el) {
  document.querySelectorAll(".page").forEach(page => {
    page.classList.remove("active");
  });

  document.querySelectorAll(".nav-link-item").forEach(link => {
    link.classList.remove("active");
  });

  const pagina = document.getElementById("page-" + id);

  if (pagina) {
    pagina.classList.add("active");
  }

  if (el) {
    el.classList.add("active");
  }
}

function abrirPaginaDashboard(id) {
  const boton = document.querySelector(
    `.nav-link-item[onclick*="'${id}'"]`
  );

  showPage(id, boton);

  window.scrollTo({
    top: 0,
    behavior: "smooth"
  });
}

// ==================================================
// PRODUCTOS
// ==================================================

let productosCache = [];
let editandoProductoId = null;

async function cargarProductos() {
  const tbody = document.getElementById("tbodyProductos");

  try {
    const respuesta = await fetch("api/api_productos.php?action=listar");
    const datos = await respuesta.json();

    if (!respuesta.ok || datos.success === false) {
      throw new Error(datos.error || datos.message || "No se pudieron cargar los productos.");
    }

    productosCache = Array.isArray(datos) ? datos : [];
    renderProductos();
  } catch (error) {
    console.error("Error al cargar productos:", error);

    if (tbody) {
      tbody.innerHTML =
        '<tr class="empty-row"><td colspan="9">Error al conectar con el servidor</td></tr>';
    }
  }
}

function renderProductos(filtro = "") {
  const tbody = document.getElementById("tbodyProductos");

  if (!tbody) return;

  const termino = filtro.trim().toLowerCase();

  const listaFiltrada = productosCache.filter(producto => {
    const nombre = String(producto.Nombre ?? producto.nombre ?? "").toLowerCase();
    const descripcion = String(producto.Descripcion ?? producto.descripcion ?? "").toLowerCase();

    return nombre.includes(termino) || descripcion.includes(termino);
  });

  if (listaFiltrada.length === 0) {
    tbody.innerHTML =
      '<tr class="empty-row"><td colspan="9">No se encontraron productos</td></tr>';
    return;
  }

  tbody.innerHTML = listaFiltrada.map(producto => {
    const id = Number(producto.Id_producto ?? producto.id ?? 0);
    const nombre = String(producto.Nombre ?? producto.nombre ?? "");
    const descripcion = String(producto.Descripcion ?? producto.descripcion ?? "");
    const precio = Number(producto.Precio ?? producto.precio ?? 0);
    const stock = Number(producto.Stock ?? producto.stock ?? 0);
    const categoria = String(
      producto.Categoria ??
      producto.Categoría ??
      producto.categoria ??
      ""
    );
    const valorEstado = Number(producto.Estado ?? producto.estado ?? 0);
    const estadoTexto = valorEstado === 1 ? "Activo" : "Inactivo";
    const rutaImagen = String(producto.Imagen ?? producto.imagen ?? "");

    const celdaImagen = rutaImagen
      ? `<img src="${escaparHTMLProducto(rutaImagen)}"
              alt="${escaparHTMLProducto(nombre)}"
              style="width:45px;height:45px;object-fit:cover;border-radius:4px;border:1px solid #eee;">`
      : '<i class="bi bi-image text-muted" style="font-size:24px;"></i>';

    return `
      <tr>
        <td>${id}</td>
        <td class="text-center">${celdaImagen}</td>
        <td><strong>${escaparHTMLProducto(nombre)}</strong></td>
        <td><small class="text-muted">${escaparHTMLProducto(descripcion)}</small></td>
        <td>${formatearMonedaProducto(precio)}</td>
        <td>${stock}</td>
        <td><span class="badge bg-secondary">${escaparHTMLProducto(categoria)}</span></td>
        <td>
          <span class="badge ${valorEstado === 1 ? "bg-success" : "bg-danger"}">
            ${estadoTexto}
          </span>
        </td>
        <td>
          <button
            type="button"
            class="btn btn-sm btn-warning me-1"
            onclick="abrirEditarProducto(${id})"
            title="Editar producto"
          >
            <i class="bi bi-pencil"></i>
          </button>

          <button
            type="button"
            class="btn btn-sm btn-danger"
            onclick="eliminarProducto(${id})"
            title="Eliminar producto"
          >
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `;
  }).join("");
}

async function guardarProducto() {
  const form = document.getElementById("formProducto");

  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const datos = new FormData();

  if (editandoProductoId !== null) {
    datos.append("id", editandoProductoId);
  }

  datos.append("nombre", document.getElementById("pNombre").value.trim());
  datos.append("descripcion", document.getElementById("pDescripcion").value.trim());
  datos.append("precio", document.getElementById("pPrecio").value);
  datos.append("stock", document.getElementById("pStock").value);
  datos.append("categoria", document.getElementById("pCategoria").value);
  datos.append("estado", document.getElementById("pEstado").value);

  const archivo = document.getElementById("pImagen").files[0];

  if (archivo) {
    datos.append("imagen", archivo);
  }

  try {
    const respuesta = await fetch("api/api_productos.php?action=guardar", {
      method: "POST",
      body: datos
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || resultado.success === false) {
      throw new Error(resultado.error || resultado.message || "No se pudo guardar el producto.");
    }

    alert(resultado.mensaje || "Producto guardado correctamente.");

    limpiarProducto();

    const modalElemento = document.getElementById("modalProducto");
    bootstrap.Modal.getOrCreateInstance(modalElemento).hide();

    await cargarProductos();
  } catch (error) {
    console.error("Error al guardar producto:", error);
    alert(error.message);
  }
}

function abrirEditarProducto(id) {
  const producto = productosCache.find(item =>
    Number(item.Id_producto ?? item.id) === Number(id)
  );

  if (!producto) return;

  editandoProductoId = Number(id);

  document.getElementById("productoId").value = editandoProductoId;
  document.getElementById("pNombre").value = producto.Nombre ?? producto.nombre ?? "";
  document.getElementById("pDescripcion").value = producto.Descripcion ?? producto.descripcion ?? "";
  document.getElementById("pPrecio").value = producto.Precio ?? producto.precio ?? "";
  document.getElementById("pStock").value = producto.Stock ?? producto.stock ?? "";
  document.getElementById("pCategoria").value =
    producto.Categoria ?? producto.Categoría ?? producto.categoria ?? "";
  document.getElementById("pEstado").value =
    producto.Estado ?? producto.estado ?? "1";

  document.getElementById("tituloModalProducto").textContent = "Editar Producto";

  const rutaImagen = producto.Imagen ?? producto.imagen ?? "";
  const contenedor = document.getElementById("previsualizacionContenedor");
  const imagen = document.getElementById("pImpVg");

  if (rutaImagen) {
    imagen.src = rutaImagen;
    contenedor.style.display = "block";
  } else {
    contenedor.style.display = "none";
  }

  bootstrap.Modal.getOrCreateInstance(
    document.getElementById("modalProducto")
  ).show();
}

async function eliminarProducto(id) {
  if (!confirm("¿Eliminar este producto?")) return;

  try {
    const respuesta = await fetch(
      "api/api_productos.php?action=eliminar&id=" + encodeURIComponent(id)
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || resultado.success === false) {
      throw new Error(resultado.error || resultado.message || "No se pudo eliminar el producto.");
    }

    alert(resultado.mensaje || "Producto eliminado correctamente.");
    await cargarProductos();
  } catch (error) {
    console.error("Error al eliminar producto:", error);
    alert(error.message);
  }
}

function limpiarProducto() {
  const form = document.getElementById("formProducto");

  if (form) form.reset();

  editandoProductoId = null;

  document.getElementById("productoId").value = "";
  document.getElementById("tituloModalProducto").textContent = "Agregar Nuevo Producto";
  document.getElementById("previsualizacionContenedor").style.display = "none";
  document.getElementById("pImpVg").src = "";
}

function formatearMonedaProducto(valor) {
  const numero = Number(valor);

  if (Number.isNaN(numero)) return "Q0.00";

  return numero.toLocaleString("es-GT", {
    style: "currency",
    currency: "GTQ",
    minimumFractionDigits: 2
  });
}

function escaparHTMLProducto(valor) {
  return String(valor ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

document.addEventListener("DOMContentLoaded", () => {
  cargarProductos();

  const buscador = document.getElementById("searchProducto");

  if (buscador) {
    buscador.addEventListener("input", event => {
      renderProductos(event.target.value);
    });
  }

  const inputImagen = document.getElementById("pImagen");

  if (inputImagen) {
    inputImagen.addEventListener("change", function () {
      const archivo = this.files[0];
      const contenedor = document.getElementById("previsualizacionContenedor");
      const imagen = document.getElementById("pImpVg");

      if (!archivo) {
        contenedor.style.display = "none";
        imagen.src = "";
        return;
      }

      const lector = new FileReader();

      lector.onload = event => {
        imagen.src = event.target.result;
        contenedor.style.display = "block";
      };

      lector.readAsDataURL(archivo);
    });
  }

  const modalProducto = document.getElementById("modalProducto");

  if (modalProducto) {
    modalProducto.addEventListener("hidden.bs.modal", limpiarProducto);
  }
});
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="js/reportes.js"></script>

<script src="js/usuarios.js"></script>
<script src="js/noticias.js"></script>
<script src="js/solicitudes.js?v="></script>
<script src="js/seguimiento_motoescuela.js?v=1.1"></script>
<script src="js/ventas.js"></script>
<script src="js/repuestos.js?v=2.0"></script>
<script src="js/servicios.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/dashboard.js"></script>
</body>
</html>