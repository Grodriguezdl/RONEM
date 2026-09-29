<?php

declare(strict_types=1);

require_once __DIR__ . "/config/session.php";
require_once __DIR__ . "/config/conexion.php";


require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/alertas.php";
/*
|--------------------------------------------------------------------------
| REDIRECCIÓN SEGÚN EL TIPO DE USUARIO
|--------------------------------------------------------------------------
|
| Cliente:
|     Permanece en index.php.
|
| Administrador, empleado o técnico:
|     Se redirige a su panel.
|
| Super Administrador:
|     Se redirige al selector de accesos.
|
| Usuario con varios roles:
|     Se redirige al selector de accesos.
|
*/
/*
|--------------------------------------------------------------------------
| CONTROL DE ACCESO AL INDEX
|--------------------------------------------------------------------------
|
| Cuando se utiliza ?modo=sitio, el usuario puede ver la página pública
| aunque tenga rol de administrador, empleado o técnico.
|
*/

$modoSitio = (
    isset($_GET["modo"])
    && $_GET["modo"] === "sitio"
);

if (
    isset($_SESSION["autenticado"])
    && $_SESSION["autenticado"] === true
    && !$modoSitio
) {
    $roles = $_SESSION["roles"] ?? [];

    /*
     * Si ya existe un rol activo, se respeta ese acceso.
     */
    $rolActivo = $_SESSION["rol_activo"] ?? null;

    /*
     * Un cliente permanece en index.php.
     */
    if ($rolActivo === "cliente") {
        // No redirigir.
    }

    /*
     * Administrador.
     */
    elseif ($rolActivo === "administrador") {
        redirigir("panel_admin.php");
    }

    /*
     * Empleado.
     */
    elseif ($rolActivo === "empleado") {
        redirigir("panel_empleado.php");
    }

    /*
     * Técnico.
     */
    elseif ($rolActivo === "tecnico") {
        redirigir("panel_tecnico.php");
    }

    /*
     * Si todavía no se eligió un rol activo,
     * se determina el acceso según sus roles.
     */
    else {

        if (esSuperAdministrador()) {
            redirigir("seleccionar_acceso.php");
        }

        if (count($roles) > 1) {
            redirigir("seleccionar_acceso.php");
        }

        if (in_array("administrador", $roles, true)) {
            $_SESSION["rol_activo"] = "administrador";

            redirigir("panel_admin.php");
        }

        if (in_array("empleado", $roles, true)) {
            $_SESSION["rol_activo"] = "empleado";

            redirigir("panel_empleado.php");
        }

        if (in_array("tecnico", $roles, true)) {
            $_SESSION["rol_activo"] = "tecnico";

            redirigir("panel_tecnico.php");
        }

        /*
         * El cliente permanece en index.php.
         */
        if (in_array("cliente", $roles, true)) {
            $_SESSION["rol_activo"] = "cliente";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="icon" href="favicoB.png?123">
  <title>RONEM - RONEMMA</title>
  
  <!-- CSS Frameworks & Fonts -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@300;400;500;600;700&family=PT+Sans:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Estilos Personalizados -->
  <link rel="stylesheet" href="css/styles.css?v=99.0"/>
  <link rel="stylesheet" href="css/perfil_modal.css?v=1.0">
  
  <link
    rel="stylesheet"
    href="css/login_modal.css?v=<?= filemtime(__DIR__ . '/css/login_modal.css') ?>"
  >
</head>
<body>

<!-- BARRA DE NAVEGACIÓN BOOTSTRAP ADAPTABLE CON GLASSMORPHISM -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top custom-navbar navbar-custom">
  <div class="container-fluid px-4">
    
    <!-- Marca / Logo -->
    <a href="#inicio" class="navbar-brand d-flex align-items-center me-4">
     <img src="favicoB.png?v=2" alt="RONEM" style="height:40px; width:auto; margin-right:12px;">
      <div class="brand-text-box d-flex flex-column text-start">
        <span class="brand-name fw-bold" style="letter-spacing: 2px; line-height:1;">RONEMMA</span>
        <span class="brand-sub text-danger fs-7" style="font-size: 0.75rem; letter-spacing: 3px;">ENTERPRISE</span>
      </div>
    </a>

    <!-- Botón Hamburguesa para Móviles -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menú e Interacciones Colapsables -->
    <div class="collapse navbar-collapse" id="navbarNav">
      
      <!-- Menú Centrado -->
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-2 text-uppercase fw-semibold">
        <li class="nav-item"><a href="#inicio" class="nav-link active">Inicio</a></li>
        <li class="nav-item"><a href="#experiencia" class="nav-link">Experiencia</a></li>
        <li class="nav-item"><a href="#servicios" class="nav-link">Servicios</a></li>
        <li class="nav-item"><a href="#repuestos" class="nav-link">Repuestos</a></li>
        <li class="nav-item"><a href="#agencias" class="nav-link">Agencias</a></li>
        <li class="nav-item"><a href="#motoescuela" class="nav-link">Motoescuela</a></li>
        <li class="nav-item"><a href="#garantias" class="nav-link">Garantías</a></li>
      </ul>

      <!-- Acciones Derecha (Carrito & Sesión / Login) -->
      <div class="d-flex align-items-center justify-content-center gap-3 mt-3 mt-lg-0">
        
        <!-- Icono Carrito -->
        <a href="ventas.php" class="nav-cart text-white position-relative fs-5 p-2 rounded-circle hover-red" title="Comprar">
          <i class="bi bi-cart3"></i>
        </a>

        <!-- Validación de Sesión / Login Modal -->
        <?php if (
            isset($_SESSION["autenticado"])
            && $_SESSION["autenticado"] === true
        ): ?>
          <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3 py-1" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle fs-5"></i>
              <span>
                  <?= htmlspecialchars(
                      trim(
                          ($_SESSION["nombre"] ?? "")
                          . " "
                          . ($_SESSION["apellido"] ?? "")
                      )
                  ) ?>
              </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg mt-2 border-secondary" aria-labelledby="userDropdown">
              <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#perfilModal"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
              <li><a class="dropdown-item" href="paneles/cliente/vehiculos.php"><i class="bi bi-scooter me-2"></i>Mis Vehículos</a></li>
              <li><a class="dropdown-item" href="paneles/cliente/historial.php"><i class="bi bi-clock-history me-2"></i>Historial</a></li>
              <li><a class="dropdown-item" href="paneles/cliente/fidelizacion.php"><i class="bi bi-star me-2"></i>Fidelización</a></li>
              <li><a class="dropdown-item" href="paneles/cliente/motoescuela.php"><i class="bi bi-mortarboard me-2"></i>Moto Escuela</a></li>
              <li><hr class="dropdown-divider border-secondary"></li>
              <li><a class="dropdown-item text-danger" href="auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
            </ul>
          </div>
        <?php else: ?>
          <button class="btn btn-danger font-weight-bold px-4 rounded-pill shadow-sm text-uppercase" data-bs-toggle="modal" data-bs-target="#loginModal">
            LOGIN
          </button>
        <?php endif; ?>

      </div>
    </div>

  </div>
</nav>

<!-- INICIO -->
<section id="inicio" class="d-flex flex-column align-items-center justify-content-center min-vh-100 text-center text-white px-3" style="padding-top: 80px;">
    <div class="hero-title display-1 fw-bold">RONEM</div>
    <div class="hero-sub fs-3 text-uppercase text-danger mb-4" style="letter-spacing: 4px;">RONEMMA</div>

    <?php if (
    !isset($_SESSION["autenticado"])
    || $_SESSION["autenticado"] !== true
): ?>

    <button
        class="btn btn-outline-light btn-lg rounded-pill px-5 py-2 shadow"
        data-bs-toggle="modal"
        data-bs-target="#loginModal"
    >
        INGRESAR AHORA
    </button>

<?php endif; ?>

    <div class="hero-scroll-line mt-5"></div>
</section>

<!-- NOTICIAS -->
<section id="noticias" class="py-5">
    <div class="container">

        <div class="section-header mb-4 text-center">
            <h2 class="section-title display-5 fw-bold">
                <span class="gt-white text-white">N</span><span class="gt-white text-white">O</span><span class="gt-red text-danger">T</span><span class="gt-white text-white">ICIAS</span>
            </h2>
            <p class="section-subtitle text-secondary">
                MANTENTE INFORMADO CON LAS ÚLTIMAS NOVEDADES
            </p>
        </div>

        <?php
        $sqlNoticias = "
            SELECT
                Id_noticia,
                Imagen_URL,
                Descripcion,
                Fecha
            FROM Noticias
            ORDER BY Fecha DESC, Id_noticia DESC
        ";

        $resultadoNoticias = $conn->query($sqlNoticias);
        $cantidadNoticias = ($resultadoNoticias) ? $resultadoNoticias->num_rows : 0;
        ?>

        <?php if ($resultadoNoticias && $cantidadNoticias > 0): ?>

            <div
                id="carouselNoticias"
                class="carousel slide carousel-fade"
                data-bs-ride="carousel"
                data-bs-interval="6000"
                data-bs-pause="hover"
            >

                <?php if ($cantidadNoticias > 1): ?>
                    <div class="carousel-indicators">
                        <?php for ($i = 0; $i < $cantidadNoticias; $i++): ?>
                            <button
                                type="button"
                                data-bs-target="#carouselNoticias"
                                data-bs-slide-to="<?= $i ?>"
                                class="<?= $i === 0 ? 'active' : '' ?>"
                                <?= $i === 0 ? 'aria-current="true"' : '' ?>
                                aria-label="Noticia <?= $i + 1 ?>"
                            ></button>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

                <div class="carousel-inner">

                    <?php
                    $indiceNoticia = 0;

                    while ($noticia = $resultadoNoticias->fetch_assoc()):

                        $rutaImagen = trim((string) $noticia["Imagen_URL"]);

                        // Limpia rutas como ../uploads/ para que funcionen desde index.php.
                        $rutaImagen = preg_replace('#^(\.\./)+#', '', $rutaImagen);
                        $rutaImagen = preg_replace('#^(\./)+#', '', $rutaImagen);
                        $rutaImagen = ltrim($rutaImagen, '/');

                        if ($rutaImagen === '') {
                            $rutaImagen = 'favicoB.png';
                        }

                        $descripcionNoticia = trim((string) $noticia["Descripcion"]);

                        if ($descripcionNoticia === '') {
                            $descripcionNoticia = 'Noticia RONEM';
                        }

                        $fechaNoticia = 'Fecha no disponible';

                        if (!empty($noticia["Fecha"])) {
                            $timestampNoticia = strtotime($noticia["Fecha"]);

                            if ($timestampNoticia !== false) {
                                $meses = [
                                    1 => 'enero',
                                    2 => 'febrero',
                                    3 => 'marzo',
                                    4 => 'abril',
                                    5 => 'mayo',
                                    6 => 'junio',
                                    7 => 'julio',
                                    8 => 'agosto',
                                    9 => 'septiembre',
                                    10 => 'octubre',
                                    11 => 'noviembre',
                                    12 => 'diciembre'
                                ];

                                $dia = date('d', $timestampNoticia);
                                $mes = $meses[(int) date('n', $timestampNoticia)];
                                $anio = date('Y', $timestampNoticia);

                                $fechaNoticia = $dia . ' de ' . $mes . ' de ' . $anio;
                            }
                        }
                    ?>

                        <div class="carousel-item <?= $indiceNoticia === 0 ? 'active' : '' ?>">

                            <div class="noticia-slide">

                                <img
                                    src="<?= htmlspecialchars($rutaImagen) ?>"
                                    class="d-block w-100 noticia-imagen"
                                    alt="<?= htmlspecialchars($descripcionNoticia) ?>"
                                    loading="<?= $indiceNoticia === 0 ? 'eager' : 'lazy' ?>"
                                    onerror="this.onerror=null; this.src='favicoB.png'; this.classList.add('noticia-imagen-error');"
                                >

                                <div class="noticia-filtro"></div>

                                <div class="carousel-caption noticia-caption">

                                    <div class="noticia-contenido">

                                        <span class="noticia-etiqueta">
                                            <i class="bi bi-newspaper"></i>
                                            NOTICIAS RONEM
                                        </span>

                                        <h3 class="noticia-descripcion">
                                            <?= htmlspecialchars($descripcionNoticia) ?>
                                        </h3>

                                        <div class="noticia-fecha">
                                            <i class="bi bi-calendar-event"></i>
                                            <?= htmlspecialchars($fechaNoticia) ?>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php
                        $indiceNoticia++;
                    endwhile;
                    ?>

                </div>

                <?php if ($cantidadNoticias > 1): ?>

                    <button
                        class="carousel-control-prev"
                        type="button"
                        data-bs-target="#carouselNoticias"
                        data-bs-slide="prev"
                    >
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>

                    <button
                        class="carousel-control-next"
                        type="button"
                        data-bs-target="#carouselNoticias"
                        data-bs-slide="next"
                    >
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>

                <?php endif; ?>

            </div>

        <?php else: ?>

            <div class="noticias-vacio">
                <i class="bi bi-newspaper"></i>
                <h3>No hay noticias disponibles</h3>
                <p>
                    Las noticias registradas desde el panel administrativo
                    aparecerán automáticamente en esta sección.
                </p>
            </div>

        <?php endif; ?>

    </div>
</section>

<!-- EXPERIENCIA -->
<section id="experiencia" class="py-5">
    <div class="section-header text-center mb-5">
        <h2 class="exp-title display-5 fw-bold text-white">EXPERIENCIA RONEM</h2>
        <p class="section-subtitle text-secondary">EXPLORA NUESTROS SERVICIOS</p>
    </div>

    <div class="container">
        <div class="row justify-content-center g-4">

           <div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <a href="#inicio" class="text-decoration-none">
        <div class="service-card text-center p-4 rounded-3 h-100">
            <div class="card-bg bg-inicio"></div>

            <div class="card-content">
                <i class="bi bi-house card-icon fs-1 text-danger mb-3"></i>

                <div class="card-name fw-bold text-white">
                    INICIO
                </div>

                <div class="card-desc text-secondary fs-7">
                    Descubre nuestro mundo
                </div>
            </div>
        </div>
    </a>
</div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <a href="#servicios" class="text-decoration-none">
                    <div class="service-card text-center p-4 rounded-3 h-100">
                        <div class="card-bg bg-taller"></div>
                        <div class="card-content">
                            <i class="bi bi-gear card-icon fs-1 text-danger mb-3"></i>
                            <div class="card-name fw-bold text-white">SERVICIOS</div>
                            <div class="card-desc text-secondary fs-7">Kits de mantenimiento disponibles</div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <a href="#repuestos" class="text-decoration-none">
                    <div class="service-card text-center p-4 rounded-3 h-100">
                        <div class="card-bg bg-repuestos"></div>
                        <div class="card-content">
                            <i class="bi bi-wrench-adjustable-circle card-icon fs-1 text-danger mb-3"></i>
                            <div class="card-name fw-bold text-white">REPUESTOS</div>
                            <div class="card-desc text-secondary fs-7">Piezas originales premium</div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <a href="#agencias" class="text-decoration-none">
                    <div class="service-card text-center p-4 rounded-3 h-100">
                        <div class="card-bg bg-agencias"></div>
                        <div class="card-content">
                            <i class="bi bi-geo-alt card-icon fs-1 text-danger mb-3"></i>
                            <div class="card-name fw-bold text-white">AGENCIAS</div>
                            <div class="card-desc text-secondary fs-7">Encuéntranos cerca de ti</div>
                        </div>
                    </div>
                </a>
            </div>

           <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                 <a href="ventas.php" class="text-decoration-none">
                    <div class="service-card text-center p-4 rounded-3 h-100">
                     <div class="card-bg bg-carrito"></div>
                        <div class="card-content">
                     <i class="bi bi-cart card-icon fs-1 text-danger mb-3"></i>
                     <div class="card-name fw-bold text-white">CARRITO</div>
                <div class="card-desc text-secondary fs-7">Tu selección de productos</div>
            </div>
        </div>
    </a>
</div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <a href="#motoescuela" class="text-decoration-none">
                    <div class="service-card text-center p-4 rounded-3 h-100">
                        <div class="card-bg bg-moto"></div>
                        <div class="card-content">
                            <i class="bi bi-mortarboard card-icon fs-1 text-danger mb-3"></i>
                            <div class="card-name fw-bold text-white">MOTO ESCUELA</div>
                            <div class="card-desc text-secondary fs-7">Aprende a conducir seguro</div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <a href="#garantias" class="text-decoration-none">
                    <div class="service-card text-center p-4 rounded-3 h-100">
                        <div class="card-bg bg-garantias"></div>
                        <div class="card-content">
                            <i class="bi bi-shield-check card-icon fs-1 text-danger mb-3"></i>
                            <div class="card-name fw-bold text-white">GARANTÍAS</div>
                            <div class="card-desc text-secondary fs-7">Protección y respaldo total</div>
                        </div>
                    </div>
                </a>
            </div>

        </div>
    </div>
</section>

<!-- =========================================
SERVICIOS
========================================= -->

<section id="servicios" class="py-5">

    <div class="section-header text-center mb-5">
        <h2 class="section-title display-5 fw-bold">
            SE<span class="gt-red text-danger">R</span>VICIOS
        </h2>

        <p class="section-subtitle">
            PRODUCTOS PREMIUM PARA EL CUIDADO DE TU MOTO
        </p>
    </div>

    <div class="container-fluid px-4 pb-5">

        <div class="row g-4">

            <?php

            $sqlServicios = "
                SELECT
                    Id_servicio,
                    Nombre,
                    Icono,
                    Orden
                FROM Servicios
                WHERE Estado = 1
                ORDER BY Orden ASC
            ";

            $resultadoServicios = $conn->query($sqlServicios);

            if ($resultadoServicios && $resultadoServicios->num_rows > 0):

                while ($servicio = $resultadoServicios->fetch_assoc()):

                    $idServicio = (int)$servicio["Id_servicio"];

                    $sqlProductos = "
                        SELECT
                            Nombre,
                            Destacado
                        FROM Productos_servicio
                        WHERE Id_servicio = ?
                        AND Estado = 1
                        ORDER BY Orden ASC
                        LIMIT 6
                    ";

                    $stmtProductos = $conn->prepare($sqlProductos);
                    $stmtProductos->bind_param("i", $idServicio);
                    $stmtProductos->execute();

                    $resultadoProductos = $stmtProductos->get_result();

                    $claseIcono = in_array($idServicio,[2,3])
                        ? "pc-icon-red"
                        : "pc-icon-blue";

            ?>

            <div class="col-12 col-lg-6">

                <div class="producto-card">

                    <div class="pc-header">

                        <i class="<?= htmlspecialchars($servicio["Icono"]) ?> pc-header-icon <?= $claseIcono ?>"></i>

                        <h3 class="pc-header-title mb-0">
                            <?= htmlspecialchars($servicio["Nombre"]) ?>
                        </h3>

                    </div>

                    <ul class="pc-list">

                        <?php if($resultadoProductos->num_rows>0): ?>

                            <?php while($producto=$resultadoProductos->fetch_assoc()): ?>

                                <li class="<?= (int)$producto["Destacado"]===1 ? "highlight" : "" ?>">

                                    <span class="pc-dot"></span>

                                    <span>

                                        <?= htmlspecialchars($producto["Nombre"]) ?>

                                    </span>

                                </li>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <li>

                                <span class="pc-dot"></span>

                                <span>

                                    No hay productos disponibles.

                                </span>

                            </li>

                        <?php endif; ?>

                    </ul>

                    <button
                        type="button"
                        class="btn-catalogo"
                        data-bs-toggle="modal"
                        data-bs-target="#modalServicio<?= $idServicio ?>"
                    >

                        <i class="bi bi-box-arrow-up-right me-2"></i>

                        VER CATÁLOGO COMPLETO

                    </button>

                </div>

            </div>

            <?php

                $stmtProductos->close();

                endwhile;

            else:

            ?>

            <div class="col-12">

                <div class="alert alert-dark text-center">

                    No hay servicios disponibles.

                </div>

            </div>

            <?php endif; ?>

        </div>

    </div>

</section>

<!-- REPUESTOS (CON TODOS LOS 18 COMPONENTES) -->
<!-- REPUESTOS -->
<section id="repuestos" class="py-5">

    <div class="section-header text-center mb-5">
        <h2 class="section-title display-5 fw-bold repuestos-titulo">
    RE<span class="text-danger">P</span>UESTOS
</h2>

<p class="section-subtitle text-secondary repuestos-subtitulo">
    REPUESTOS ORIGINALES Y DE CALIDAD PREMIUM
</p>
    </div>

    <div class="container-fluid px-4 pb-5">
        <div class="row g-4">

            <?php
            $sqlRepuestos = "
                SELECT
                    Id_repuesto,
                    Nombre,
                    Descripcion,
                    Imagen_URL,
                    Stock,
                    Precio
                FROM Repuestos
                ORDER BY Id_repuesto ASC
            ";

            $resultadoRepuestos = $conn->query($sqlRepuestos);
            ?>

            <?php if ($resultadoRepuestos && $resultadoRepuestos->num_rows > 0): ?>

                <?php while ($repuesto = $resultadoRepuestos->fetch_assoc()): ?>

                    <?php
                    $imagenRepuesto = trim((string) $repuesto["Imagen_URL"]);

                    $imagenRepuesto = preg_replace(
                        '#^(\.\./)+#',
                        '',
                        $imagenRepuesto
                    );

                    $imagenRepuesto = preg_replace(
                        '#^(\./)+#',
                        '',
                        $imagenRepuesto
                    );

                    $imagenRepuesto = ltrim($imagenRepuesto, '/');

                    if ($imagenRepuesto === '') {
                        $imagenRepuesto =
                            'img/Repuestos/repuesto-generico.jpg';
                    }

                    $stockRepuesto = (int) $repuesto["Stock"];
                    ?>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="repuesto-card h-100 rounded-3 overflow-hidden">

                            <div class="repuesto-imagen-contenedor">
                                <img
                                    src="<?= htmlspecialchars($imagenRepuesto) ?>"
                                    alt="<?= htmlspecialchars($repuesto["Nombre"]) ?>"
                                    class="repuesto-imagen"
                                    loading="lazy"
                                    onerror="
                                        this.onerror = null;
                                        this.src = 'img/Repuestos/repuesto-generico.jpg';
                                    "
                                >
                            </div>

                            <div class="rc-info p-4">

                                <div class="rc-cat text-secondary">
                                    REPUESTO
                                </div>

                                <h3 class="rc-name fw-bold text-white">
                                    <?= htmlspecialchars($repuesto["Nombre"]) ?>
                                </h3>

                                <p class="rc-descripcion text-secondary">
                                    <?= htmlspecialchars($repuesto["Descripcion"]) ?>
                                </p>

                                <div class="d-flex justify-content-between align-items-center gap-3 mt-3">

                                    <span class="rc-precio text-danger fw-bold">
                                        Q<?= number_format(
                                            (float) $repuesto["Precio"],
                                            2
                                        ) ?>
                                    </span>

                                    <?php if ($stockRepuesto > 0): ?>

                                        <span class="rc-stock disponible">
                                            Stock: <?= $stockRepuesto ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="rc-stock agotado">
                                            Agotado
                                        </span>

                                    <?php endif; ?>

                                </div>

                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="col-12">
                    <div class="alert alert-dark text-center">
                        No hay repuestos disponibles.
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>
</section>
<!-- AGENCIAS -->
<section id="agencias" class="py-5">
  <div class="section-header text-center mb-5">
    <h2 class="section-title display-5 fw-bold">
      <span class="gt-white text-white">A</span><span class="gt-white text-white">G</span><span class="gt-red text-danger">E</span><span class="gt-white text-white">NCIAS</span>
    </h2>
    <p class="section-subtitle agencias-sub">
      <span class="sub-normal text-white">VISÍTANOS EN NUESTRA </span>
      <span class="sub-blue text-info">UBICACIÓN </span>
      <span class="sub-red text-danger">PREMIUM</span>
    </p>
  </div>

  <div class="container-fluid px-4 pb-5">
    <div class="row g-4 align-items-stretch">

      <!-- MAPA -->
      <div class="col-12 col-lg-7">
        <div class="map-container rounded-3 overflow-hidden shadow">
          <iframe
            src="https://www.google.com/maps?q=14.683335, -90.612453&hl=es&z=16&output=embed"
            width="100%"
            height="100%"
            style="border:0; min-height: 380px;"
            allowfullscreen=""
            loading="lazy">
          </iframe>
        </div>
      </div>

      <!-- PANEL DERECHO -->
      <div class="col-12 col-lg-5">
        <div class="agencia-info-panel h-100 p-4 rounded-3 d-flex flex-column justify-content-between">

          <div class="agencia-info-block d-flex gap-3 mb-3">
            <i class="bi bi-geo-alt aib-icon fs-3 text-danger"></i>
            <div>
              <div class="aib-title fw-bold text-white">Dirección</div>
              <div class="aib-line text-secondary">Avenida Principal #123</div>
              <div class="aib-line text-secondary">Zona Premium, Ciudad</div>
              <div class="aib-line text-secondary">Código Postal: 12345</div>
            </div>
          </div>

          <div class="agencia-info-block d-flex gap-3 mb-3">
            <i class="bi bi-telephone aib-icon fs-3 text-danger"></i>
            <div>
              <div class="aib-title fw-bold text-white">Teléfonos</div>
              <div class="aib-line text-info">+502 2345-6789</div>
              <div class="aib-line text-info">+502 2345-6790</div>
              <div class="aib-line text-info">WhatsApp: +502 5555-5555</div>
            </div>
          </div>

          <div class="agencia-info-block d-flex gap-3">
            <i class="bi bi-clock aib-icon fs-3 text-danger"></i>
            <div>
              <div class="aib-title fw-bold text-white">Horarios</div>
              <div class="aib-line text-info">Lunes - Viernes: 9:00 AM - 7:00 PM</div>
              <div class="aib-line text-info">Sábados: 9:00 AM - 5:00 PM</div>
              <div class="aib-line text-info">Domingos: 10:00 AM - 2:00 PM</div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- CÓMO LLEGAR -->
    <div class="row mt-4">
      <div class="col-12">
        <div class="como-llegar-card p-4 rounded-3 text-center">
          <div class="aib-title fs-5 fw-bold text-white mb-2">Cómo llegar</div>
          <p class="text-secondary">
            Estamos ubicados en el corazón de la zona premium, con fácil acceso desde las principales avenidas.
            <span class="d-block text-white mt-1">Contamos con amplio estacionamiento para tu comodidad.</span>
          </p>

          <a href="https://www.google.com/maps?q=14.683335, -90.612453" target="_blank" class="btn btn-danger px-4 mt-2">
            OBTENER DIRECCIONES
          </a>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- MOTO ESCUELA -->
<section id="motoescuela" class="py-5">
  <div class="section-header text-center mb-4">
    <div class="moto-icon-header mb-2">
      <i class="bi bi-mortarboard fs-1 text-danger"></i>
    </div>
    <h2 class="section-title display-5 fw-bold text-white" style="letter-spacing:8px;">MOTO ESCUELA</h2>
    <p class="section-subtitle">
      <span class="text-white">FICHA DE REGISTRO</span>
      <span class="text-secondary"> - APRENDE A CONDUCIR CON PROFESIONALES</span>
    </p>
  </div>

  <div class="container-fluid px-4 pb-5">
    <div class="form-card p-4 rounded-3 max-width-800 mx-auto">

      <form id="formSolicitud">

        <!-- INFORMACIÓN PERSONAL -->
        <div class="form-section-title fw-bold fs-5 text-white border-bottom border-danger pb-1 mb-3">
          <span>INFORMACIÓN PERSONAL</span>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label text-secondary small">NOMBRE *</label>
            <input type="text" id="nombre" class="form-control bg-dark text-white border-secondary" required>
          </div>
          <div class="col-md-6">
            <label class="form-label text-secondary small">APELLIDO *</label>
            <input type="text" id="apellido" class="form-control bg-dark text-white border-secondary" required>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label text-secondary small">DPI / IDENTIFICACIÓN *</label>
            <input type="text" id="dpi" class="form-control bg-dark text-white border-secondary" required>
          </div>
          <div class="col-md-6">
            <label class="form-label text-secondary small">FECHA DE NACIMIENTO *</label>
            <input type="date" id="fecha_nacimiento" class="form-control bg-dark text-white border-secondary" required>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label text-secondary small">EDAD *</label>
            <input type="number" id="edad" class="form-control bg-dark text-white border-secondary" required>
          </div>
          <div class="col-md-6">
            <label class="form-label text-secondary small">GÉNERO *</label>
            <select id="genero" class="form-select bg-dark text-white border-secondary" required>
              <option value="">Seleccionar</option>
              <option value="Masculino">Masculino</option>
              <option value="Femenino">Femenino</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
        </div>

        <!-- INFORMACIÓN DE CONTACTO -->
        <div class="form-section-title fw-bold fs-5 text-white border-bottom border-danger pb-1 mb-3 mt-4">
          <span>INFORMACIÓN DE CONTACTO</span>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label text-secondary small">TELÉFONO *</label>
            <input type="tel" id="telefono" class="form-control bg-dark text-white border-secondary" required>
          </div>
          <div class="col-md-6">
            <label class="form-label text-secondary small">EMAIL *</label>
            <input type="email" id="correo" class="form-control bg-dark text-white border-secondary" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label text-secondary small">DIRECCIÓN *</label>
          <input type="text" id="direccion" class="form-control bg-dark text-white border-secondary" required>
        </div>

        <!-- EXPERIENCIA EN CONDUCCIÓN -->
        <div class="form-section-title fw-bold fs-5 text-white border-bottom border-danger pb-1 mb-3 mt-4">
          <span>EXPERIENCIA EN CONDUCCIÓN</span>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label text-secondary small">NIVEL DE EXPERIENCIA *</label>
            <select id="id_nivel" name="id_nivel" class="form-select bg-dark text-white border-secondary" required>
              <option value="">Seleccionar</option>
              <option value="1">Sin experiencia</option>
              <option value="2">Principiante</option>
              <option value="3">Intermedio</option>
              <option value="4">Avanzado</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label text-secondary small">TIPO DE LICENCIA DESEADA *</label>
            <select id="id_tipo_licencia" name="id_tipo_licencia" class="form-select bg-dark text-white border-secondary" required>
              <option value="">Seleccionar</option>
              <option value="1">Tipo A</option>
              <option value="2">Tipo B</option>
              <option value="3">Tipo C</option>
            </select>
          </div>
        </div>

        <!-- CONTACTO DE EMERGENCIA -->
        <div class="form-section-title fw-bold fs-5 text-white border-bottom border-danger pb-1 mb-3 mt-4">
          <span>CONTACTO DE EMERGENCIA</span>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label text-secondary small">NOMBRE COMPLETO *</label>
            <input type="text" id="nombre_emergencia" class="form-control bg-dark text-white border-secondary" required>
          </div>
          <div class="col-md-6">
            <label class="form-label text-secondary small">TELÉFONO *</label>
            <input type="tel" id="contacto_emergencia" class="form-control bg-dark text-white border-secondary" required>
          </div>
        </div>

        <!-- INFORMACIÓN MÉDICA -->
        <div class="form-section-title fw-bold fs-5 text-white border-bottom border-danger pb-1 mb-3 mt-4">
          <span>INFORMACIÓN MÉDICA</span>
        </div>

        <div class="mb-3">
          <label class="form-label text-secondary small">CONDICIONES MÉDICAS O ALERGIAS</label>
          <textarea id="condiciones_medicas" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Indique cualquier condición médica relevante o alergias..."></textarea>
        </div>

        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="terminos" required>
          <label class="form-check-label text-secondary small" for="terminos">
            Acepto los <a href="#" class="text-danger">términos y condiciones</a>. Confirmo que la información proporcionada es correcta y completa.
          </label>
        </div>

        <button type="submit" class="btn btn-danger w-100 py-2 fw-bold text-uppercase">ENVIAR SOLICITUD</button>

        <p class="form-note text-center text-secondary small mt-3">
          * Campos obligatorios. <span class="d-block">Nos pondremos en contacto contigo en un plazo de 24-48 horas.</span>
        </p>

      </form>

    </div>
  </div>
</section>

<!-- GARANTÍAS -->
<section id="garantias" class="py-5">
  <div class="section-header text-center mb-5">
    <h2 class="section-title display-5 fw-bold">
      <span class="gt-white text-white">G</span><span class="gt-white text-white">A</span><span class="gt-red text-danger">R</span><span class="gt-white text-white">ANTÍAS</span>
    </h2>
    <p class="section-subtitle">
      <span class="text-secondary">TU INVERSIÓN PROTEGIDA CON </span>
      <span class="text-white">GARANTÍAS </span>
      <span class="text-danger">PREMIUM</span>
    </p>
  </div>

  <div class="container-fluid px-4 pb-5">
    <div class="row g-3 mb-4">
      <div class="col-12 col-md-4">
        <div class="garantia-card p-4 rounded-3 text-center">
          <div class="gc-icon fs-1 text-danger mb-2"><i class="bi bi-wrench-adjustable"></i></div>
          <div class="gc-name fw-bold text-white">Garantía Servicios</div>
          <p class="text-secondary small mt-2">Cobertura completa en mantenimiento y mano de obra garantizada.</p>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="garantia-card p-4 rounded-3 text-center">
          <div class="gc-icon fs-1 text-danger mb-2"><i class="bi bi-shield-check"></i></div>
          <div class="gc-name fw-bold text-white">Repuestos Originales</div>
          <p class="text-secondary small mt-2">Piezas respaldadas directamente por la fábrica con certificación de calidad.</p>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="garantia-card p-4 rounded-3 text-center">
          <div class="gc-icon fs-1 text-danger mb-2"><i class="bi bi-gear"></i></div>
          <div class="gc-name fw-bold text-white">Garantía Herramientas</div>
          <p class="text-secondary small mt-2">Protección en herramientas profesionales y accesorios con el sello RONEM.</p>
        </div>
      </div>
    </div>

    <!-- BANNER DE PROTECCIÓN PREMIUM -->
    <div class="proteccion-banner p-4 rounded-3 text-center border border-secondary" style="background: rgba(20,20,20,0.8);">
      <div class="pb-icon fs-2 text-danger mb-2"><i class="bi bi-shield-lock"></i></div>
      <div class="pb-title fs-5 fw-bold text-white mb-2">Protección Premium RONEM</div>
      <p class="pb-text text-secondary small mb-0">
        En RONEM respaldamos la calidad de nuestros productos y servicios con garantías integrales diseñadas para tu tranquilidad.
      </p>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="pt-5 pb-4 text-white border-top border-secondary" style="background: rgba(10, 10, 10, 0.95); backdrop-filter: blur(10px);">
  <div class="container">
    <div class="row g-4 mb-4">
      
      <!-- Columna 1: Info Marca -->
      <div class="col-12 col-md-4 text-center text-md-start">
    <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-3">
        <img src="favicoB.png?v=2" alt="RONEM Logo" style="height:40px; margin-right:12px;">
        <div>
            <h5 class="fw-bold mb-0 text-white" style="letter-spacing:2px;">RONEMMA</h5>
            <small class="text-danger fw-bold" style="letter-spacing:2px;">ENTERPRISE</small>
        </div>
    </div>

    <p class="text-secondary small">
        Especialistas en motocicletas, repuestos de alta gama, servicios técnicos calificados y formación profesional para conductores.
    </p>
</div>

      <!-- Columna 2: Enlaces Rápidos -->
      <div class="col-6 col-md-4 text-center">
        <h6 class="text-uppercase fw-bold mb-3 text-danger" style="letter-spacing:1px;">Navegación</h6>
        <ul class="list-unstyled small d-flex flex-column gap-2">
          <li><a href="#inicio" class="text-secondary text-decoration-none hover-white">Inicio</a></li>
          <li><a href="#productos" class="text-secondary text-decoration-none hover-white">Servicios & Productos</a></li>
          <li><a href="#repuestos" class="text-secondary text-decoration-none hover-white">Repuestos Originales</a></li>
          <li><a href="#motoescuela" class="text-secondary text-decoration-none hover-white">Moto Escuela</a></li>
          <li><a href="#agencias" class="text-secondary text-decoration-none hover-white">Agencias & Ubicación</a></li>
        </ul>
      </div>

      <!-- Columna 3: Redes Sociales y Contacto -->
      <div class="col-6 col-md-4 text-center text-md-end">
        <h6 class="text-uppercase fw-bold mb-3 text-danger" style="letter-spacing:1px;">Síguenos</h6>
        <div class="d-flex justify-content-center justify-content-md-end gap-3 mb-3">
          <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2" title="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2" title="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
        </div>
        <span class="d-block text-secondary small">Soporte: info@ronemma.com</span>
      </div>

    </div>

    <hr class="border-secondary my-3">

    <div class="row align-items-center">
      <div class="col-md-12 text-center text-secondary small">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> <span class="text-white">RONEMMA ENTERPRISE</span>. Todos los derechos reservados.</p>
      </div>
    </div>
  </div>
</footer>

<!-- SCRIPT DE DESPLAZAMIENTO SUAVE -->
<script>
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
      const href = link.getAttribute('href');
      if (href && href.startsWith('#') && href.length > 1) {
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
          window.scrollTo({ top: target.offsetTop - 70, behavior: 'smooth' });
        }
      }
    });
  });
</script>

<!-- CARGA DE MODALES OBLIGATORIAS PARA QUE FUNCIONEN LOS BOTONES DE LOGIN Y PERFIL -->
<?php require_once __DIR__ . "/login_modal.php"; ?>
<?php require_once __DIR__ . "/php/perfil_modal.php"; ?>

<?php

$sqlModales = "
    SELECT
        Id_servicio,
        Nombre,
        Icono,
        Orden
    FROM Servicios
    WHERE Estado = 1
    ORDER BY Orden ASC
";

$resultadoModales = $conn->query($sqlModales);

if ($resultadoModales && $resultadoModales->num_rows > 0):

    while ($servicioModal = $resultadoModales->fetch_assoc()):

        $idServicioModal = (int) $servicioModal["Id_servicio"];

      $sqlCatalogo = "
    SELECT
        Nombre,
        Descripcion,
        Precio,
        Destacado
    FROM Productos_servicio
    WHERE Id_servicio = ?
    AND Estado = 1
    ORDER BY Orden ASC
";

        $stmtCatalogo = $conn->prepare($sqlCatalogo);
        $stmtCatalogo->bind_param("i", $idServicioModal);
        $stmtCatalogo->execute();

        $resultadoCatalogo = $stmtCatalogo->get_result();
?>

<div class="modal fade" id="modalServicio<?= $idServicioModal ?>" tabindex="-1" aria-labelledby="tituloModalServicio<?= $idServicioModal ?>" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="
            background: rgba(18, 18, 18, 0.97);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            color: #ffffff;
            backdrop-filter: blur(16px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.75);
            overflow: hidden;
        ">
            <div class="modal-header" style="
                background: rgba(255, 255, 255, 0.03);
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                padding: 24px 28px;
            ">
                <div class="d-flex align-items-center gap-3">
                    <i class="<?= htmlspecialchars($servicioModal["Icono"]) ?>" style="
                        color: #cc1f1f;
                        font-size: 1.8rem;
                    "></i>

                    <h2 class="modal-title" id="tituloModalServicio<?= $idServicioModal ?>" style="
                        font-family: 'League Spartan', sans-serif;
                        font-size: 1.7rem;
                        font-weight: 600;
                        color: #ffffff;
                        margin: 0;
                    ">
                        <?= htmlspecialchars($servicioModal["Nombre"]) ?>
                    </h2>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body" style="
                background: rgba(10, 10, 10, 0.98);
                padding: 28px;
            ">
                <div class="row g-4">

                    <?php if ($resultadoCatalogo->num_rows > 0): ?>

                        <?php while ($producto = $resultadoCatalogo->fetch_assoc()): ?>

                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="h-100" style="
                                    position: relative;
                                    background: rgba(255, 255, 255, 0.035);
                                    border: 1px solid rgba(255, 255, 255, 0.08);
                                    border-radius: 12px;
                                    padding: 24px;
                                ">

                                    <?php if ((int) $producto["Destacado"] === 1): ?>

                                        <span style="
                                            display: inline-block;
                                            margin-bottom: 14px;
                                            padding: 5px 11px;
                                            background: rgba(204, 31, 31, 0.18);
                                            border: 1px solid rgba(204, 31, 31, 0.5);
                                            border-radius: 20px;
                                            color: #ffffff;
                                            font-size: 0.65rem;
                                            font-weight: 700;
                                            letter-spacing: 1.5px;
                                        ">
                                            DESTACADO
                                        </span>

                                    <?php endif; ?>

                                    <h4 style="
                                        color: #ffffff;
                                        font-family: 'League Spartan', sans-serif;
                                        font-size: 1.25rem;
                                        font-weight: 600;
                                        margin-bottom: 12px;
                                    ">
                                        <?= htmlspecialchars($producto["Nombre"]) ?>
                                    </h4>

                                    <p style="
                                        color: #aaaaaa;
                                        font-size: 0.9rem;
                                        line-height: 1.6;
                                        min-height: 70px;
                                    ">
                                        <?= htmlspecialchars($producto["Descripcion"]) ?>
                                    </p>

                                    <div style="
                                        display: flex;
                                        justify-content: space-between;
                                        align-items: center;
                                        gap: 15px;
                                        padding-top: 16px;
                                        margin-top: 18px;
                                        border-top: 1px solid rgba(255, 255, 255, 0.08);
                                    ">
                                        <span style="
                                            color: #cc1f1f;
                                            font-family: 'League Spartan', sans-serif;
                                            font-size: 1.35rem;
                                            font-weight: 700;
                                        ">
                                            Q<?= number_format((float) $producto["Precio"], 2) ?>
                                        </span>

                                        <small style="
                                        color: #aaaaaa;
                                        text-align: right;
                                        ">
                                        Consultar disponibilidad
                                        </small>
                                    </div>

                                </div>
                            </div>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <div class="col-12">
                            <p class="text-center text-secondary mb-0">
                                No hay productos disponibles para este servicio.
                            </p>
                        </div>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php

        $stmtCatalogo->close();

    endwhile;

endif;

?>

<!-- Modal de confirmación -->
<div class="modal fade" id="modalSolicitud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-solicitud">

            <div class="modal-body text-center">
                <div class="modal-solicitud-icono">
                    <i class="bi bi-check-lg"></i>
                </div>

                <h3 id="tituloSolicitud">Solicitud enviada</h3>

                <p id="mensajeSolicitud">
                    Tu solicitud fue enviada correctamente.
                </p>

                <button
                    type="button"
                    class="btn-modal-solicitud"
                    data-bs-dismiss="modal"
                >
                    ACEPTAR
                </button>
            </div>

        </div>
    </div>
</div>

<!-- JavaScript / Bootstrap Bundle & Formulario -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/formulario.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<?php mostrarAlerta(); ?>
</body>
</html>