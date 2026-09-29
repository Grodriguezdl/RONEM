<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../includes/alertas.php";


/*
| VALIDAR MÉTODO
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}


/*
| VALIDAR CONEXIÓN
*/

if (
    !isset($conn) ||
    !($conn instanceof mysqli)
) {
    error_log(
        "verificar.php: no existe una conexión mysqli válida."
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
VALIDAR SESIÓN DE VERIFICACIÓN
*/

if (
    !isset($_SESSION["verificacion_pendiente"]) ||
    !is_array($_SESSION["verificacion_pendiente"])
) {
    guardarAlerta(
        "warning",
        "Verificación no disponible",
        "No existe una verificación pendiente."
    );

    header("Location: ../index.php");
    exit;
}


$idUsuario = (int) (
    $_SESSION["verificacion_pendiente"]["id_usuario"] ?? 0
);

$correo = trim(
    (string) (
        $_SESSION["verificacion_pendiente"]["correo"] ?? ""
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
    $correo === "" ||
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



$codigo = trim(
    (string) ($_POST["codigo"] ?? "")
);

if (!preg_match("/^[0-9]{6}$/", $codigo)) {
    guardarAlerta(
        "warning",
        "Código inválido",
        "Debes ingresar los seis dígitos del código."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}



$sqlVerificacion = "
    SELECT
        Id_verificacion,
        Codigo_hash,
        Expira_en,
        Intentos,
        Max_intentos,
        Utilizado,
        CASE
            WHEN Expira_en <= NOW() THEN 1
            ELSE 0
        END AS Expirado
    FROM Verificaciones
    WHERE Id_usuario = ?
      AND Tipo = ?
      AND Utilizado = 0
    ORDER BY Id_verificacion DESC
    LIMIT 1
";

$stmtVerificacion = $conn->prepare(
    $sqlVerificacion
);

if ($stmtVerificacion === false) {
    error_log(
        "Error preparando verificación: " .
        $conn->error
    );

    guardarAlerta(
        "error",
        "Error del sistema",
        "No fue posible comprobar el código."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


$stmtVerificacion->bind_param(
    "is",
    $idUsuario,
    $tipoVerificacion
);


if (!$stmtVerificacion->execute()) {
    error_log(
        "Error ejecutando verificación: " .
        $stmtVerificacion->error
    );

    $stmtVerificacion->close();

    guardarAlerta(
        "error",
        "Error del sistema",
        "No fue posible comprobar el código."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


$stmtVerificacion->bind_result(
    $idVerificacion,
    $codigoHash,
    $expiraEn,
    $intentos,
    $maxIntentos,
    $utilizado,
    $expirado
);

$verificacionEncontrada =
    $stmtVerificacion->fetch();

$stmtVerificacion->close();




if (!$verificacionEncontrada) {
    guardarAlerta(
        "warning",
        "Código no disponible",
        "No existe un código activo. Solicita uno nuevo."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


$idVerificacion = (int) $idVerificacion;
$intentos = (int) $intentos;
$maxIntentos = (int) $maxIntentos;
$utilizado = (int) $utilizado;
$expirado = (int) $expirado;

/*
| VALIDAR ESTADO
*/

if ($utilizado === 1) {
    guardarAlerta(
        "warning",
        "Código utilizado",
        "Este código ya fue utilizado."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


if ($intentos >= $maxIntentos) {
    guardarAlerta(
        "error",
        "Intentos agotados",
        "Superaste el máximo de intentos. Solicita un código nuevo."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}


/*
| VALIDAR EXPIRACIÓN
*/

if ($expirado === 1) {
    $sqlMarcarExpirado = "
        UPDATE Verificaciones
        SET Utilizado = 1
        WHERE Id_verificacion = ?
    ";

    $stmtExpirado = $conn->prepare(
        $sqlMarcarExpirado
    );

    if ($stmtExpirado !== false) {
        $stmtExpirado->bind_param(
            "i",
            $idVerificacion
        );

        $stmtExpirado->execute();
        $stmtExpirado->close();
    }

    guardarAlerta(
        "warning",
        "Código vencido",
        "El código expiró. Solicita uno nuevo."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}




$codigoCorrecto = password_verify(
    $codigo,
    (string) $codigoHash
);




if (!$codigoCorrecto) {
    $nuevosIntentos = $intentos + 1;

    $marcarUtilizado =
        $nuevosIntentos >= $maxIntentos
            ? 1
            : 0;

    $sqlIntentoFallido = "
        UPDATE Verificaciones
        SET
            Intentos = ?,
            Utilizado = ?
        WHERE Id_verificacion = ?
    ";

    $stmtIntento = $conn->prepare(
        $sqlIntentoFallido
    );

    if ($stmtIntento === false) {
        error_log(
            "Error preparando intento fallido: " .
            $conn->error
        );

        guardarAlerta(
            "error",
            "Error del sistema",
            "No fue posible registrar el intento."
        );

        header("Location: ../index.php?verificar=1");
        exit;
    }

    $stmtIntento->bind_param(
        "iii",
        $nuevosIntentos,
        $marcarUtilizado,
        $idVerificacion
    );

    if (!$stmtIntento->execute()) {
        error_log(
            "Error actualizando intentos: " .
            $stmtIntento->error
        );

        $stmtIntento->close();

        guardarAlerta(
            "error",
            "Error del sistema",
            "No fue posible registrar el intento."
        );

        header("Location: ../index.php?verificar=1");
        exit;
    }

    $stmtIntento->close();

    $intentosRestantes =
        max(
            0,
            $maxIntentos - $nuevosIntentos
        );

    if ($intentosRestantes === 0) {
        guardarAlerta(
            "error",
            "Intentos agotados",
            "El código fue bloqueado. Solicita uno nuevo."
        );
    } else {
        guardarAlerta(
            "warning",
            "Código incorrecto",
            "El código no es correcto. Te quedan " .
            $intentosRestantes .
            " intentos."
        );
    }

    header("Location: ../index.php?verificar=1");
    exit;
}



$conn->begin_transaction();

$stmtUsuario = null;
$stmtUtilizarCodigo = null;

try {

    /*
    | VERIFICAR EL CORREO DEL USUARIO
    */

    $sqlVerificarUsuario = "
        UPDATE Usuarios
        SET
            Correo_Verificado = 1,
            Activo = 1
        WHERE Id_usuario = ?
          AND LOWER(Correo) = LOWER(?)
    ";

    $stmtUsuario = $conn->prepare(
        $sqlVerificarUsuario
    );

    if ($stmtUsuario === false) {
        throw new RuntimeException(
            "No se pudo preparar la actualización del usuario: " .
            $conn->error
        );
    }

    $stmtUsuario->bind_param(
        "is",
        $idUsuario,
        $correo
    );

    if (!$stmtUsuario->execute()) {
        throw new RuntimeException(
            "No se pudo verificar el usuario: " .
            $stmtUsuario->error
        );
    }

    if ($stmtUsuario->affected_rows < 1) {

        /*
         * Puede devolver cero si ya estaba verificado.
         * Por seguridad comprobamos si el usuario existe.
         */

        $sqlComprobarUsuario = "
            SELECT Id_usuario
            FROM Usuarios
            WHERE Id_usuario = ?
              AND LOWER(Correo) = LOWER(?)
              AND Correo_Verificado = 1
            LIMIT 1
        ";

        $stmtComprobar = $conn->prepare(
            $sqlComprobarUsuario
        );

        if ($stmtComprobar === false) {
            throw new RuntimeException(
                "No se pudo comprobar el usuario."
            );
        }

        $stmtComprobar->bind_param(
            "is",
            $idUsuario,
            $correo
        );

        if (!$stmtComprobar->execute()) {
            throw new RuntimeException(
                "No se pudo comprobar el estado del usuario."
            );
        }

        $stmtComprobar->store_result();

        $usuarioValido =
            $stmtComprobar->num_rows === 1;

        $stmtComprobar->close();

        if (!$usuarioValido) {
            throw new RuntimeException(
                "No se encontró el usuario correspondiente."
            );
        }
    }

    $stmtUsuario->close();
    $stmtUsuario = null;


    /*
    | MARCAR CÓDIGO COMO UTILIZADO
    */

    $sqlUtilizarCodigo = "
        UPDATE Verificaciones
        SET Utilizado = 1
        WHERE Id_verificacion = ?
          AND Utilizado = 0
    ";

    $stmtUtilizarCodigo = $conn->prepare(
        $sqlUtilizarCodigo
    );

    if ($stmtUtilizarCodigo === false) {
        throw new RuntimeException(
            "No se pudo preparar el cierre de la verificación: " .
            $conn->error
        );
    }

    $stmtUtilizarCodigo->bind_param(
        "i",
        $idVerificacion
    );

    if (!$stmtUtilizarCodigo->execute()) {
        throw new RuntimeException(
            "No se pudo finalizar la verificación: " .
            $stmtUtilizarCodigo->error
        );
    }

    $stmtUtilizarCodigo->close();
    $stmtUtilizarCodigo = null;


    /*
    | CONFIRMAR CAMBIOS
    */

    $conn->commit();


    /*
    | LIMPIAR SESIÓN PENDIENTE
    */

    unset($_SESSION["verificacion_pendiente"]);


    guardarAlerta(
        "success",
        "Correo verificado",
        "Tu cuenta fue verificada correctamente. Ya puedes iniciar sesión."
    );




    header("Location: ../index.php?login=1");
    exit;

} catch (Throwable $error) {

    $conn->rollback();


    if ($stmtUsuario instanceof mysqli_stmt) {
        $stmtUsuario->close();
    }

    if (
        $stmtUtilizarCodigo instanceof mysqli_stmt
    ) {
        $stmtUtilizarCodigo->close();
    }


    error_log(
        "Error completo en verificar.php: " .
        $error->getMessage()
    );


    guardarAlerta(
        "error",
        "No se pudo verificar",
        "Ocurrió un error al verificar la cuenta."
    );

    header("Location: ../index.php?verificar=1");
    exit;
}