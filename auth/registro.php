<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/alertas.php";
require_once __DIR__ . "/../includes/correos.php";



if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}




$nombre = trim((string) ($_POST["nombre"] ?? ""));
$apellido = trim((string) ($_POST["apellido"] ?? ""));
$correo = strtolower(
    trim((string) ($_POST["correo"] ?? ""))
);
$password = (string) ($_POST["password"] ?? "");



if (
    $nombre === "" ||
    $apellido === "" ||
    $correo === "" ||
    $password === ""
) {
    guardarAlerta(
        "warning",
        "Campos incompletos",
        "Debes completar todos los campos."
    );

    header("Location: ../index.php");
    exit;
}


if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    guardarAlerta(
        "warning",
        "Correo inválido",
        "Ingresa un correo electrónico válido."
    );

    header("Location: ../index.php");
    exit;
}


if (
    mb_strlen($nombre, "UTF-8") < 2 ||
    mb_strlen($nombre, "UTF-8") > 100
) {
    guardarAlerta(
        "warning",
        "Nombre inválido",
        "El nombre debe tener entre 2 y 100 caracteres."
    );

    header("Location: ../index.php");
    exit;
}


if (
    mb_strlen($apellido, "UTF-8") < 2 ||
    mb_strlen($apellido, "UTF-8") > 100
) {
    guardarAlerta(
        "warning",
        "Apellido inválido",
        "El apellido debe tener entre 2 y 100 caracteres."
    );

    header("Location: ../index.php");
    exit;
}


if (
    strlen($password) < 8 ||
    strlen($password) > 72
) {
    guardarAlerta(
        "warning",
        "Contraseña inválida",
        "La contraseña debe tener entre 8 y 72 caracteres."
    );

    header("Location: ../index.php");
    exit;
}




if (
    !isset($conn) ||
    !($conn instanceof mysqli)
) {
    error_log(
        "registro.php: no existe una conexión mysqli válida."
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
BUSCAR USUARIO POR CORREO
*/

$sqlBuscarUsuario = "
    SELECT
        Id_usuario,
        Correo_Verificado
    FROM Usuarios
    WHERE LOWER(Correo) = ?
    LIMIT 1
";

$stmtBuscarUsuario = $conn->prepare(
    $sqlBuscarUsuario
);

if ($stmtBuscarUsuario === false) {
    error_log(
        "Error preparando búsqueda de usuario: " .
        $conn->error
    );

    guardarAlerta(
        "error",
        "Error del sistema",
        "No fue posible comprobar el correo."
    );

    header("Location: ../index.php");
    exit;
}

$stmtBuscarUsuario->bind_param(
    "s",
    $correo
);

if (!$stmtBuscarUsuario->execute()) {
    error_log(
        "Error buscando usuario: " .
        $stmtBuscarUsuario->error
    );

    $stmtBuscarUsuario->close();

    guardarAlerta(
        "error",
        "Error del sistema",
        "No fue posible comprobar el correo."
    );

    header("Location: ../index.php");
    exit;
}

$stmtBuscarUsuario->bind_result(
    $idUsuarioExistente,
    $correoVerificadoExistente
);

$usuarioExiste = $stmtBuscarUsuario->fetch();

$stmtBuscarUsuario->close();


/*
CORREO YA VERIFICADO
*/

if (
    $usuarioExiste &&
    (int) $correoVerificadoExistente === 1
) {
    guardarAlerta(
        "warning",
        "Correo ya registrado",
        "Ya existe una cuenta verificada con este correo."
    );

    header("Location: ../index.php");
    exit;
}




$contrasenaHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

if ($contrasenaHash === false) {
    guardarAlerta(
        "error",
        "Error del sistema",
        "No fue posible proteger la contraseña."
    );

    header("Location: ../index.php");
    exit;
}


try {
    $codigo = (string) random_int(
        100000,
        999999
    );
} catch (Throwable $error) {
    error_log(
        "Error generando código: " .
        $error->getMessage()
    );

    guardarAlerta(
        "error",
        "Error del sistema",
        "No fue posible generar el código de verificación."
    );

    header("Location: ../index.php");
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
        "No fue posible proteger el código."
    );

    header("Location: ../index.php");
    exit;
}



$sqlRolCliente = "
    SELECT Id_rol
    FROM Roles
    WHERE LOWER(TRIM(Nombre)) = 'cliente'
    LIMIT 1
";

$stmtRolCliente = $conn->prepare(
    $sqlRolCliente
);

if ($stmtRolCliente === false) {
    error_log(
        "Error preparando búsqueda del rol cliente: " .
        $conn->error
    );

    guardarAlerta(
        "error",
        "Error de configuración",
        "El rol cliente no está configurado."
    );

    header("Location: ../index.php");
    exit;
}


if (!$stmtRolCliente->execute()) {
    error_log(
        "Error buscando el rol cliente: " .
        $stmtRolCliente->error
    );

    $stmtRolCliente->close();

    guardarAlerta(
        "error",
        "Error de configuración",
        "No fue posible localizar el rol cliente."
    );

    header("Location: ../index.php");
    exit;
}


$stmtRolCliente->bind_result(
    $idRolCliente
);

$rolEncontrado = $stmtRolCliente->fetch();

$stmtRolCliente->close();


if (!$rolEncontrado) {
    guardarAlerta(
        "error",
        "Rol no encontrado",
        "No existe el rol cliente en la tabla Roles."
    );

    header("Location: ../index.php");
    exit;
}


$idRolCliente = (int) $idRolCliente;


/*
REGISTRAR O ACTUALIZAR USUARIO
*/

$stmtUsuario = null;
$stmtRol = null;
$stmtInvalidar = null;
$stmtVerificacion = null;
$transaccionActiva = false;

try {
    if (!$conn->begin_transaction()) {
        throw new RuntimeException(
            "No se pudo iniciar la transacción: " .
            $conn->error
        );
    }

    $transaccionActiva = true;

 

    if ($usuarioExiste) {
        $idUsuario = (int) $idUsuarioExistente;

        $sqlActualizarUsuario = "
            UPDATE Usuarios
            SET
                Nombre = ?,
                Apellido = ?,
                Contrasena = ?,
                Activo = 1,
                Correo_Verificado = 0,
                Tipo_Login = 'manual'
            WHERE Id_usuario = ?
        ";

        $stmtUsuario = $conn->prepare(
            $sqlActualizarUsuario
        );

        if ($stmtUsuario === false) {
            throw new RuntimeException(
                "No se pudo preparar la actualización: " .
                $conn->error
            );
        }

        $stmtUsuario->bind_param(
            "sssi",
            $nombre,
            $apellido,
            $contrasenaHash,
            $idUsuario
        );

        if (!$stmtUsuario->execute()) {
            throw new RuntimeException(
                "No se pudo actualizar el usuario: " .
                $stmtUsuario->error
            );
        }

        $stmtUsuario->close();
        $stmtUsuario = null;
    }

  

    else {
        $sqlInsertarUsuario = "
            INSERT INTO Usuarios (
                Nombre,
                Apellido,
                Correo,
                Contrasena,
                Activo,
                Correo_Verificado,
                Tipo_Login
            )
            VALUES (
                ?,
                ?,
                ?,
                ?,
                1,
                0,
                'manual'
            )
        ";

        $stmtUsuario = $conn->prepare(
            $sqlInsertarUsuario
        );

        if ($stmtUsuario === false) {
            throw new RuntimeException(
                "No se pudo preparar el registro: " .
                $conn->error
            );
        }

        $stmtUsuario->bind_param(
            "ssss",
            $nombre,
            $apellido,
            $correo,
            $contrasenaHash
        );

        if (!$stmtUsuario->execute()) {
            throw new RuntimeException(
                "No se pudo registrar el usuario: " .
                $stmtUsuario->error
            );
        }

        $idUsuario = (int) $conn->insert_id;

        $stmtUsuario->close();
        $stmtUsuario = null;
    }


    if ($idUsuario <= 0) {
        throw new RuntimeException(
            "No se obtuvo un ID de usuario válido."
        );
    }




    $sqlAsignarRol = "
        INSERT INTO Roles_usuarios (
            Id_usuario,
            Id_rol
        )
        SELECT ?, ?
        WHERE NOT EXISTS (
            SELECT 1
            FROM Roles_usuarios
            WHERE Id_usuario = ?
            AND Id_rol = ?
        )
    ";

    $stmtRol = $conn->prepare(
        $sqlAsignarRol
    );

    if ($stmtRol === false) {
        throw new RuntimeException(
            "No se pudo preparar la asignación del rol: " .
            $conn->error
        );
    }

    $stmtRol->bind_param(
        "iiii",
        $idUsuario,
        $idRolCliente,
        $idUsuario,
        $idRolCliente
    );

    if (!$stmtRol->execute()) {
        throw new RuntimeException(
            "No se pudo asignar el rol cliente: " .
            $stmtRol->error
        );
    }

    $stmtRol->close();
    $stmtRol = null;




    $tipoVerificacion = "verificar_correo";

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



    $sqlVerificacion = "
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

    $stmtVerificacion = $conn->prepare(
        $sqlVerificacion
    );

    if ($stmtVerificacion === false) {
        throw new RuntimeException(
            "No se pudo preparar la verificación: " .
            $conn->error
        );
    }

    $stmtVerificacion->bind_param(
        "iss",
        $idUsuario,
        $tipoVerificacion,
        $codigoHash
    );

    if (!$stmtVerificacion->execute()) {
        throw new RuntimeException(
            "No se pudo guardar la verificación: " .
            $stmtVerificacion->error
        );
    }

    $stmtVerificacion->close();
    $stmtVerificacion = null;



    if (!$conn->commit()) {
        throw new RuntimeException(
            "No se pudo confirmar el registro: " .
            $conn->error
        );
    }

    $transaccionActiva = false;
} catch (Throwable $error) {
    if ($stmtUsuario instanceof mysqli_stmt) {
        $stmtUsuario->close();
    }

    if ($stmtRol instanceof mysqli_stmt) {
        $stmtRol->close();
    }

    if ($stmtInvalidar instanceof mysqli_stmt) {
        $stmtInvalidar->close();
    }

    if ($stmtVerificacion instanceof mysqli_stmt) {
        $stmtVerificacion->close();
    }

    if ($transaccionActiva) {
        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {
            error_log(
                "registro.php: error al revertir la transacción: " .
                $rollbackError->getMessage()
            );
        }
    }

    error_log(
        "registro.php: error durante el registro: " .
        $error->getMessage()
    );

    guardarAlerta(
        "error",
        "No se pudo crear la cuenta",
        "Ocurrió un error al registrar tu cuenta. Inténtalo nuevamente."
    );

    header("Location: ../index.php");
    exit;
}



$_SESSION["verificacion_pendiente"] = [
    "id_usuario" => $idUsuario,
    "correo" => $correo,
    "tipo" => "verificar_correo",
    "creado_en" => time()
];




$nombreCompleto = trim(
    $nombre . " " . $apellido
);

$correoEnviado = enviarCodigoVerificacion(
    $correo,
    $nombreCompleto,
    $codigo
);


if (!$correoEnviado) {
    error_log(
        "La cuenta fue creada, pero no se pudo enviar " .
        "el código al correo: " .
        $correo
    );

    guardarAlerta(
        "warning",
        "Cuenta creada",
        "Tu cuenta fue creada, pero no pudimos enviar el código. " .
        "Puedes solicitar uno nuevo."
    );

    header(
        "Location: ../index.php?verificar=1"
    );
    exit;
}


guardarAlerta(
    "success",
    "Código enviado",
    "Revisa tu correo e ingresa el código de seis dígitos."
);

header(
    "Location: ../index.php?verificar=1"
);
exit;