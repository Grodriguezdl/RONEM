<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../includes/alertas.php";
require_once __DIR__ . "/../includes/correos.php";


/*
|--------------------------------------------------------------------------
| VALIDAR CONEXIÓN
|--------------------------------------------------------------------------
*/

if (
    !isset($conn) ||
    !($conn instanceof mysqli)
) {
    error_log(
        "reenviar_codigo.php: no existe una conexión mysqli válida."
    );

    guardarAlerta(
        "error",
        "Error de conexión",
        "No fue posible conectar con la base de datos."
    );

    header("Location: ../index.php");
    exit;
}

$conn->set_charset("utf8mb4");


/*
|--------------------------------------------------------------------------
| VALIDAR SESIÓN PENDIENTE
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["verificacion_pendiente"]) ||
    !is_array($_SESSION["verificacion_pendiente"])
) {
    guardarAlerta(
        "warning",
        "Verificación no disponible",
        "No existe una cuenta pendiente de verificación."
    );

    header("Location: ../index.php?login=1");
    exit;
}


$idUsuario = (int) (
    $_SESSION["verificacion_pendiente"]["id_usuario"] ?? 0
);

$correoSesion = strtolower(
    trim(
        (string) (
            $_SESSION["verificacion_pendiente"]["correo"] ?? ""
        )
    )
);

$tipoVerificacion = trim(
    (string) (
        $_SESSION["verificacion_pendiente"]["tipo"]
        ?? "verificar_correo"
    )
);


if (
    $idUsuario <= 0 ||
    $correoSesion === "" ||
    $tipoVerificacion !== "verificar_correo"
) {
    unset($_SESSION["verificacion_pendiente"]);

    guardarAlerta(
        "warning",
        "Sesión inválida",
        "La solicitud de verificación ya no es válida."
    );

    header("Location: ../index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| BUSCAR USUARIO
|--------------------------------------------------------------------------
*/

$sqlUsuario = "
    SELECT
        Nombre,
        Apellido,
        Correo,
        Correo_Verificado,
        Activo
    FROM Usuarios
    WHERE Id_usuario = ?
      AND LOWER(Correo) = LOWER(?)
    LIMIT 1
";

$stmtUsuario = $conn->prepare(
    $sqlUsuario
);

if ($stmtUsuario === false) {
    error_log(
        "Error preparando búsqueda del usuario: " .
        $conn->error
    );

    guardarAlerta(
        "error",
        "Error del sistema",
        "No fue posible localizar la cuenta."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


$stmtUsuario->bind_param(
    "is",
    $idUsuario,
    $correoSesion
);


if (!$stmtUsuario->execute()) {
    error_log(
        "Error buscando usuario para reenvío: " .
        $stmtUsuario->error
    );

    $stmtUsuario->close();

    guardarAlerta(
        "error",
        "Error del sistema",
        "No fue posible localizar la cuenta."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


$stmtUsuario->bind_result(
    $nombre,
    $apellido,
    $correo,
    $correoVerificado,
    $activo
);

$usuarioEncontrado = $stmtUsuario->fetch();

$stmtUsuario->close();


if (!$usuarioEncontrado) {
    unset($_SESSION["verificacion_pendiente"]);

    guardarAlerta(
        "error",
        "Cuenta no encontrada",
        "No se encontró la cuenta pendiente de verificación."
    );

    header("Location: ../index.php");
    exit;
}


$correoVerificado = (int) $correoVerificado;
$activo = (int) $activo;


/*
|--------------------------------------------------------------------------
| COMPROBAR SI YA FUE VERIFICADO
|--------------------------------------------------------------------------
*/

if ($correoVerificado === 1) {
    unset($_SESSION["verificacion_pendiente"]);

    guardarAlerta(
        "info",
        "Correo ya verificado",
        "Esta cuenta ya fue verificada. Puedes iniciar sesión."
    );

    header("Location: ../index.php?login=1");
    exit;
}


if ($activo !== 1) {
    guardarAlerta(
        "error",
        "Cuenta inactiva",
        "Esta cuenta no se encuentra activa."
    );

    header("Location: ../index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| REVISAR TIEMPO DE REENVÍO
|--------------------------------------------------------------------------
*/

$sqlTiempoReenvio = "
    SELECT
        GREATEST(
            TIMESTAMPDIFF(
                SECOND,
                NOW(),
                Reenvio_disponible_en
            ),
            0
        ) AS Segundos_restantes
    FROM Verificaciones
    WHERE Id_usuario = ?
      AND Tipo = ?
    ORDER BY Id_verificacion DESC
    LIMIT 1
";

$stmtTiempo = $conn->prepare(
    $sqlTiempoReenvio
);

if ($stmtTiempo === false) {
    error_log(
        "Error preparando tiempo de reenvío: " .
        $conn->error
    );

    guardarAlerta(
        "error",
        "Error del sistema",
        "No fue posible comprobar el tiempo de reenvío."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


$stmtTiempo->bind_param(
    "is",
    $idUsuario,
    $tipoVerificacion
);


if (!$stmtTiempo->execute()) {
    error_log(
        "Error consultando tiempo de reenvío: " .
        $stmtTiempo->error
    );

    $stmtTiempo->close();

    guardarAlerta(
        "error",
        "Error del sistema",
        "No fue posible comprobar el tiempo de reenvío."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


$stmtTiempo->bind_result(
    $segundosRestantes
);

$verificacionAnteriorEncontrada =
    $stmtTiempo->fetch();

$stmtTiempo->close();


$segundosRestantes =
    (int) ($segundosRestantes ?? 0);


if (
    $verificacionAnteriorEncontrada &&
    $segundosRestantes > 0
) {
    guardarAlerta(
        "warning",
        "Espera un momento",
        "Podrás solicitar otro código en " .
        $segundosRestantes .
        " segundos."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


/*
|--------------------------------------------------------------------------
| GENERAR NUEVO CÓDIGO
|--------------------------------------------------------------------------
*/

try {
    $codigo = (string) random_int(
        100000,
        999999
    );
} catch (Throwable $error) {
    error_log(
        "Error generando código de reenvío: " .
        $error->getMessage()
    );

    guardarAlerta(
        "error",
        "Error del sistema",
        "No fue posible generar un código nuevo."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


$codigoHash = password_hash(
    $codigo,
    PASSWORD_DEFAULT
);


if ($codigoHash === false) {
    guardarAlerta(
        "error",
        "Error del sistema",
        "No fue posible proteger el código nuevo."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


/*
|--------------------------------------------------------------------------
| GUARDAR NUEVO CÓDIGO
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

$stmtInvalidar = null;
$stmtInsertar = null;

try {

    /*
    |--------------------------------------------------------------------------
    | INVALIDAR CÓDIGOS ANTERIORES
    |--------------------------------------------------------------------------
    */

    $sqlInvalidar = "
        UPDATE Verificaciones
        SET Utilizado = 1
        WHERE Id_usuario = ?
          AND Tipo = ?
          AND Utilizado = 0
    ";

    $stmtInvalidar = $conn->prepare(
        $sqlInvalidar
    );

    if ($stmtInvalidar === false) {
        throw new RuntimeException(
            "No se pudo preparar la invalidación: " .
            $conn->error
        );
    }

    $stmtInvalidar->bind_param(
        "is",
        $idUsuario,
        $tipoVerificacion
    );

    if (!$stmtInvalidar->execute()) {
        throw new RuntimeException(
            "No se pudieron invalidar los códigos anteriores: " .
            $stmtInvalidar->error
        );
    }

    $stmtInvalidar->close();
    $stmtInvalidar = null;


    /*
    |--------------------------------------------------------------------------
    | INSERTAR NUEVO CÓDIGO
    |--------------------------------------------------------------------------
    */

    $sqlInsertar = "
        INSERT INTO Verificaciones (
            Id_usuario,
            Tipo,
            Codigo_hash,
            Expira_en,
            Reenvio_disponible_en,
            Intentos,
            Max_intentos,
            Utilizado
        )
        VALUES (
            ?,
            ?,
            ?,
            DATE_ADD(NOW(), INTERVAL 5 MINUTE),
            DATE_ADD(NOW(), INTERVAL 60 SECOND),
            0,
            5,
            0
        )
    ";

    $stmtInsertar = $conn->prepare(
        $sqlInsertar
    );

    if ($stmtInsertar === false) {
        throw new RuntimeException(
            "No se pudo preparar el código nuevo: " .
            $conn->error
        );
    }

    $stmtInsertar->bind_param(
        "iss",
        $idUsuario,
        $tipoVerificacion,
        $codigoHash
    );

    if (!$stmtInsertar->execute()) {
        throw new RuntimeException(
            "No se pudo guardar el código nuevo: " .
            $stmtInsertar->error
        );
    }

    $stmtInsertar->close();
    $stmtInsertar = null;


    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR CAMBIOS
    |--------------------------------------------------------------------------
    */

    $conn->commit();

} catch (Throwable $error) {

    $conn->rollback();


    if ($stmtInvalidar instanceof mysqli_stmt) {
        $stmtInvalidar->close();
    }

    if ($stmtInsertar instanceof mysqli_stmt) {
        $stmtInsertar->close();
    }


    error_log(
        "Error completo en reenviar_codigo.php: " .
        $error->getMessage()
    );


    guardarAlerta(
        "error",
        "No se pudo reenviar",
        "Ocurrió un error al generar el código nuevo."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


/*
|--------------------------------------------------------------------------
| ACTUALIZAR SESIÓN
|--------------------------------------------------------------------------
*/

$_SESSION["verificacion_pendiente"] = [
    "id_usuario" => $idUsuario,
    "correo" => $correo,
    "tipo" => "verificar_correo",
    "creado_en" => time()
];


/*
|--------------------------------------------------------------------------
| ENVIAR CORREO
|--------------------------------------------------------------------------
*/

$nombreCompleto = trim(
    (string) $nombre . " " . (string) $apellido
);

$correoEnviado = enviarCodigoVerificacion(
    (string) $correo,
    $nombreCompleto,
    $codigo
);


if (!$correoEnviado) {
    error_log(
        "No se pudo reenviar el código al correo: " .
        $correo
    );

    guardarAlerta(
        "warning",
        "Código generado",
        "Se generó un código nuevo, pero no fue posible enviarlo. " .
        "Inténtalo nuevamente después de 60 segundos."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


guardarAlerta(
    "success",
    "Código reenviado",
    "Enviamos un código nuevo a tu correo electrónico."
);

header("Location: ../index.php?verificar=1");
exit;