<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/alertas.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/permisos.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("../index.php?login=1");
}


$longitudSolicitud = (int) ($_SERVER["CONTENT_LENGTH"] ?? 0);

if ($longitudSolicitud > 4096) {
    guardarAlerta(
        "warning",
        "Solicitud inválida",
        "Los datos enviados superan el tamaño permitido."
    );

    redirigir("../index.php?login=1");
}



$tokenSesion = isset($_SESSION["csrf_login"])
    ? (string) $_SESSION["csrf_login"]
    : "";

$tokenRecibido = isset($_POST["csrf_token"])
    ? (string) $_POST["csrf_token"]
    : "";

if (
    $tokenSesion === "" ||
    $tokenRecibido === "" ||
    !hash_equals($tokenSesion, $tokenRecibido)
) {
    unset($_SESSION["csrf_login"]);

    guardarAlerta(
        "warning",
        "Formulario expirado",
        "Actualiza la página e intenta iniciar sesión nuevamente."
    );

    redirigir("../index.php?login=1");
}

/*
 RECIBIR Y NORMALIZAR
*/

$correo = normalizarCorreo((string) ($_POST["correo"] ?? ""));
$password = (string) ($_POST["password"] ?? "");

$_SESSION["login_correo"] = substr($correo, 0, 150);

/*VALIDACIONES
*/

if ($correo === "" || $password === "") {
    guardarAlerta(
        "warning",
        "Datos incompletos",
        "Debes ingresar tu correo electrónico y contraseña."
    );

    redirigir("../index.php?login=1");
}

if (
    strlen($correo) > 150 ||
    !filter_var($correo, FILTER_VALIDATE_EMAIL)
) {
    guardarAlerta(
        "warning",
        "Correo inválido",
        "Ingresa un correo electrónico válido de hasta 150 caracteres."
    );

    redirigir("../index.php?login=1");
}

if (strlen($password) < 8 || strlen($password) > 72) {
    guardarAlerta(
        "danger",
        "Credenciales incorrectas",
        "El correo electrónico o la contraseña son incorrectos."
    );

    redirigir("../index.php?login=1");
}



$sqlUsuario = "
    SELECT
        Id_usuario,
        Nombre,
        Apellido,
        Correo,
        Contrasena,
        Activo,
        Tipo_Login,
        Correo_Verificado,
        Intentos_Login
    FROM Usuarios
    WHERE Correo = ?
    LIMIT 1
";

$stmtUsuario = $conn->prepare($sqlUsuario);

if (!$stmtUsuario) {
    guardarAlerta(
        "danger",
        "Error del sistema",
        "No fue posible procesar el inicio de sesión."
    );

    redirigir("../index.php?login=1");
}

$stmtUsuario->bind_param("s", $correo);

if (!$stmtUsuario->execute()) {
    $stmtUsuario->close();

    guardarAlerta(
        "danger",
        "Error del sistema",
        "No fue posible procesar el inicio de sesión."
    );

    redirigir("../index.php?login=1");
}

$stmtUsuario->store_result();

if ($stmtUsuario->num_rows !== 1) {
    $stmtUsuario->close();

    guardarAlerta(
        "danger",
        "Credenciales incorrectas",
        "El correo electrónico o la contraseña son incorrectos."
    );

    redirigir("../index.php?login=1");
}

$stmtUsuario->bind_result(
    $idUsuario,
    $nombre,
    $apellido,
    $correoBaseDatos,
    $contrasenaHash,
    $activo,
    $tipoLogin,
    $correoVerificado,
    $intentosLogin
);

$stmtUsuario->fetch();
$stmtUsuario->close();

$usuario = [
    "Id_usuario" => (int) $idUsuario,
    "Nombre" => (string) $nombre,
    "Apellido" => (string) $apellido,
    "Correo" => (string) $correoBaseDatos,
    "Contrasena" => (string) $contrasenaHash,
    "Activo" => (int) $activo,
    "Tipo_Login" => strtolower(trim((string) $tipoLogin)),
    "Correo_Verificado" => (int) $correoVerificado,
    "Intentos_Login" => (int) $intentosLogin
];

/*
 CONTRASEÑA
*/

if (!password_verify($password, $usuario["Contrasena"])) {
    $sqlIntentos = "
        UPDATE Usuarios
        SET Intentos_Login = Intentos_Login + 1
        WHERE Id_usuario = ?
    ";

    $stmtIntentos = $conn->prepare($sqlIntentos);

    if ($stmtIntentos) {
        $stmtIntentos->bind_param("i", $usuario["Id_usuario"]);
        $stmtIntentos->execute();
        $stmtIntentos->close();
    }

    guardarAlerta(
        "danger",
        "Credenciales incorrectas",
        "El correo electrónico o la contraseña son incorrectos."
    );

    redirigir("../index.php?login=1");
}

/*
 ESTADO Y TIPO DE CUENTA
*/

if ($usuario["Activo"] !== 1) {
    guardarAlerta(
        "danger",
        "Cuenta desactivada",
        "Tu cuenta está desactivada. Comunícate con un administrador."
    );

    redirigir("../index.php?login=1");
}

if ($usuario["Correo_Verificado"] !== 1) {
    $_SESSION["verificacion_pendiente"] = [
        "id_usuario" => $usuario["Id_usuario"],
        "correo" => $usuario["Correo"],
        "tipo" => "verificar_correo",
        "creado_en" => time()
    ];

    guardarAlerta(
        "warning",
        "Correo no verificado",
        "Debes verificar tu correo electrónico antes de iniciar sesión."
    );

    redirigir("../index.php?verificar=1");
}

if ($usuario["Tipo_Login"] === "google") {
    guardarAlerta(
        "info",
        "Inicio de sesión con Google",
        "Esta cuenta utiliza Google. Ingresa mediante el botón de Google."
    );

    redirigir("../index.php?login=1");
}



$roles = obtenerRolesUsuario($conn, $usuario["Id_usuario"]);

if (count($roles) === 0) {
    guardarAlerta(
        "danger",
        "Cuenta sin acceso",
        "Tu cuenta no tiene ningún rol asignado. Comunícate con un administrador."
    );

    redirigir("../index.php?login=1");
}



$stmtReiniciar = $conn->prepare(
    "UPDATE Usuarios SET Intentos_Login = 0 WHERE Id_usuario = ?"
);

if ($stmtReiniciar) {
    $stmtReiniciar->bind_param("i", $usuario["Id_usuario"]);
    $stmtReiniciar->execute();
    $stmtReiniciar->close();
}

unset(
    $_SESSION["login_correo"],
    $_SESSION["csrf_login"],
    $_SESSION["verificacion_pendiente"]
);

session_regenerate_id(true);

guardarAlerta(
    "success",
    "Inicio de sesión exitoso",
    "Bienvenido, " . $usuario["Nombre"] . "."
);

iniciarSesionUsuario($usuario, $roles);