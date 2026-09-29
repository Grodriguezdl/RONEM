<?php

declare(strict_types=1);

require_once __DIR__ . "/config/session.php";
require_once __DIR__ . "/config/conexion.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/alertas.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/permisos.php";

/*
|--------------------------------------------------------------------------
| PROTEGER EL SELECTOR
|--------------------------------------------------------------------------
*/

requiereAutenticacion("index.php");

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN DE ACCESOS
|--------------------------------------------------------------------------
*/

$accesosDisponibles = [
    "administrador" => [
        "titulo" => "Administrador",
        "descripcion" => "Gestiona usuarios, empleados, servicios, productos y configuraciones del sistema.",
        "icono" => "bi-shield-lock-fill",
        "ruta" => "panel_admin.php"
    ],

    "empleado" => [
        "titulo" => "Empleado",
        "descripcion" => "Accede a las funciones operativas, ventas y atención de los procesos internos.",
        "icono" => "bi-person-workspace",
        "ruta" => "panel_empleado.php"
    ],

    "tecnico" => [
        "titulo" => "Técnico",
        "descripcion" => "Administra diagnósticos, reparaciones, servicios técnicos y órdenes asignadas.",
        "icono" => "bi-tools",
        "ruta" => "panel_tecnico.php"
    ],

    "cliente" => [
        "titulo" => "Cliente",
        "descripcion" => "Consulta tus vehículos, historial, fidelización y servicios personales.",
        "icono" => "bi-person-circle",
        "ruta" => "index.php"
    ], 
    
    
    "admin_taller" => [
        "titulo" => "admin_taller",
        "descripcion" => "subadministrar vehículos, subadministrar clientes, generar ordenes , asignar tecnicos.",
        "icono" => "bi-person-workspace",
        "ruta" => "admin_taller.php"
    ]
];

/*
|--------------------------------------------------------------------------
| PROCESAR LA SELECCIÓN
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rolSeleccionado = strtolower(
        trim((string) ($_POST["rol"] ?? ""))
    );

    if (!isset($accesosDisponibles[$rolSeleccionado])) {
        guardarAlerta(
            "danger",
            "Acceso inválido",
            "La opción seleccionada no es válida."
        );

        redirigir("seleccionar_acceso.php");
    }

    if (!tieneRol($rolSeleccionado)) {
        guardarAlerta(
            "danger",
            "Acceso denegado",
            "No tienes asignado el rol seleccionado."
        );

        redirigir("seleccionar_acceso.php");
    }

    $_SESSION["rol_activo"] = $rolSeleccionado;

    guardarAlerta(
        "success",
        "Acceso seleccionado",
        "Ingresaste como "
        . $accesosDisponibles[$rolSeleccionado]["titulo"]
        . "."
    );

    redirigir(
        $accesosDisponibles[$rolSeleccionado]["ruta"]
    );
}

/*
|--------------------------------------------------------------------------
| OBTENER ACCESOS DISPONIBLES
|--------------------------------------------------------------------------
*/

$accesosUsuario = [];

foreach ($accesosDisponibles as $rol => $informacion) {
    if (tieneRol($rol)) {
        $accesosUsuario[$rol] = $informacion;
    }
}

if (count($accesosUsuario) === 0) {
    guardarAlerta(
        "danger",
        "Cuenta sin acceso",
        "Tu cuenta no tiene accesos disponibles."
    );

    redirigir("index.php");
}

$nombreCompleto = trim(
    (string) ($_SESSION["nombre"] ?? "")
    . " "
    . (string) ($_SESSION["apellido"] ?? "")
);

if ($nombreCompleto === "") {
    $nombreCompleto = "Usuario";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Seleccionar acceso | RONEM</title>

    <link
        rel="icon"
        href="favicoB.png"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700&family=PT+Sans:wght@400;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="css/seleccionar_acceso.css?v=1.0"
    >
</head>

<body>

<div class="access-page">

    <header class="topbar">
        <div class="topbar-content">

            <a
                href="index.php"
                class="brand-link"
            >
                <img
                    src="favicoB.png"
                    alt="RONEM"
                    class="brand-logo"
                >

                <div>
                    <div class="brand-name">
                        RONEMMA
                    </div>

                    <div class="brand-subtitle">
                        ENTERPRISE
                    </div>
                </div>
            </a>

            <a
                href="auth/logout.php"
                class="logout-link"
            >
                <i class="bi bi-box-arrow-right"></i>

                <span class="logout-text">
                    Cerrar sesión
                </span>
            </a>

        </div>
    </header>

    <main class="main-content">

        <div class="page-header">

            <div class="welcome-badge">
                <i class="bi bi-person-check-fill"></i>

                <?= htmlspecialchars($nombreCompleto) ?>
            </div>

            <h1 class="page-title">
                Selecciona tu <span>acceso</span>
            </h1>

            <p class="page-description">
                Tu cuenta posee acceso a diferentes áreas del sistema.
                Selecciona el entorno en el que deseas trabajar.
            </p>

        </div>

        <div class="row g-4 justify-content-center">

            <?php foreach ($accesosUsuario as $rol => $acceso): ?>

                <div class="col-12 col-md-6 col-xl-3">

                    <article class="access-card">

                        <div class="access-icon">
                            <i
                                class="bi <?= htmlspecialchars($acceso["icono"]) ?>"
                            ></i>
                        </div>

                        <h2 class="access-title">
                            <?= htmlspecialchars($acceso["titulo"]) ?>
                        </h2>

                        <p class="access-description">
                            <?= htmlspecialchars($acceso["descripcion"]) ?>
                        </p>

                        <form method="POST">

                            <input
                                type="hidden"
                                name="rol"
                                value="<?= htmlspecialchars($rol) ?>"
                            >

                            <button
                                type="submit"
                                class="access-button"
                            >
                                Ingresar como
                                <?= htmlspecialchars($acceso["titulo"]) ?>

                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>

                        </form>

                    </article>

                </div>

            <?php endforeach; ?>

        </div>

        <?php if (esSuperAdministrador()): ?>

            <div class="super-admin-note">
                <i class="bi bi-star-fill"></i>

                Tu cuenta de Super Administrador tiene acceso completo
                a todas las áreas de RONEM.
            </div>

        <?php endif; ?>

    </main>

    <footer class="footer-text">
        © <?= date("Y") ?> RONEMMA ENTERPRISE
    </footer>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php mostrarAlerta(); ?>

</body>
</html>