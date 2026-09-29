<?php

/**
 * auth/google/google_callback.php
 */

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| DEPENDENCIAS
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/../../config/session.php";
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/../../config/conexion.php";

require_once __DIR__ . "/../../includes/helpers.php";
require_once __DIR__ . "/../../includes/alertas.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/permisos.php";

/*
|--------------------------------------------------------------------------
| REDIRECCIÓN CON ALERTA
|--------------------------------------------------------------------------
*/

/**
 * Guarda una alerta temporal y redirige al usuario.
 */
function redirigirConAlerta(
    string $tipo,
    string $titulo,
    string $texto,
    string $destino = "/RONEM/index.php"
): void {
    guardarAlerta(
        $tipo,
        $titulo,
        $texto
    );

    redirigir($destino);
}

/*
|--------------------------------------------------------------------------
| VALIDAR CONEXIÓN
|--------------------------------------------------------------------------
*/

if (!isset($conn) || !($conn instanceof mysqli)) {
    redirigirConAlerta(
        "danger",
        "Error del sistema",
        "No fue posible conectar con la base de datos."
    );
}

if (!$conn->set_charset("utf8mb4")) {
    redirigirConAlerta(
        "danger",
        "Error del sistema",
        "No fue posible configurar la conexión con la base de datos."
    );
}

/*
|--------------------------------------------------------------------------
| VALIDAR CÓDIGO DE GOOGLE
|--------------------------------------------------------------------------
*/

if (empty($_GET["code"])) {
    redirigirConAlerta(
        "danger",
        "Acceso no válido",
        "No se recibió el código de autenticación de Google."
    );
}

/*
|--------------------------------------------------------------------------
| OBTENER TOKEN DE ACCESO
|--------------------------------------------------------------------------
*/

try {
    $token = $client->fetchAccessTokenWithAuthCode(
        (string) $_GET["code"]
    );
} catch (Throwable $error) {
    redirigirConAlerta(
        "danger",
        "Error con Google",
        "No fue posible procesar la autenticación con Google."
    );
}

if (
    !is_array($token) ||
    isset($token["error"]) ||
    empty($token["access_token"])
) {
    redirigirConAlerta(
        "danger",
        "Error con Google",
        "No fue posible autenticar tu cuenta con Google."
    );
}

$client->setAccessToken(
    (string) $token["access_token"]
);

/*
|--------------------------------------------------------------------------
| OBTENER INFORMACIÓN DE GOOGLE
|--------------------------------------------------------------------------
*/

try {
    $googleOauth = new Google\Service\Oauth2($client);
    $userInfo = $googleOauth->userinfo->get();
} catch (Throwable $error) {
    redirigirConAlerta(
        "danger",
        "Error con Google",
        "No fue posible obtener la información de tu cuenta de Google."
    );
}

$nombre = limpiarTexto(
    (string) ($userInfo->givenName ?? "")
);

$apellido = limpiarTexto(
    (string) ($userInfo->familyName ?? "")
);

$correo = normalizarCorreo(
    (string) ($userInfo->email ?? "")
);

/*
|--------------------------------------------------------------------------
| VALIDAR DATOS DEVUELTOS POR GOOGLE
|--------------------------------------------------------------------------
*/

if (
    $nombre === "" ||
    $correo === "" ||
    !filter_var($correo, FILTER_VALIDATE_EMAIL)
) {
    redirigirConAlerta(
        "danger",
        "Datos incompletos",
        "Google no devolvió la información necesaria para iniciar sesión."
    );
}

/*
|--------------------------------------------------------------------------
| BUSCAR USUARIO EXISTENTE
|--------------------------------------------------------------------------
*/

$sqlUsuario = "
    SELECT
        Id_usuario,
        Nombre,
        Apellido,
        Correo,
        Activo,
        Correo_Verificado,
        Tipo_Login
    FROM Usuarios
    WHERE LOWER(TRIM(Correo)) = ?
    LIMIT 1
";

$stmtUsuario = $conn->prepare($sqlUsuario);

if (!$stmtUsuario) {
    redirigirConAlerta(
        "danger",
        "Error del sistema",
        "No fue posible buscar la cuenta."
    );
}

$stmtUsuario->bind_param(
    "s",
    $correo
);

if (!$stmtUsuario->execute()) {
    $stmtUsuario->close();

    redirigirConAlerta(
        "danger",
        "Error del sistema",
        "No fue posible buscar la cuenta."
    );
}

$stmtUsuario->store_result();

$usuarioExiste = $stmtUsuario->num_rows === 1;

$idUsuario = 0;
$nombreFinal = $nombre;
$apellidoFinal = $apellido;
$correoFinal = $correo;

if ($usuarioExiste) {
    $stmtUsuario->bind_result(
        $idUsuarioBD,
        $nombreBD,
        $apellidoBD,
        $correoBD,
        $activoBD,
        $correoVerificadoBD,
        $tipoLoginBD
    );

    $stmtUsuario->fetch();
    $stmtUsuario->close();

    $idUsuario = (int) $idUsuarioBD;
    $nombreFinal = limpiarTexto((string) $nombreBD);
    $apellidoFinal = limpiarTexto((string) $apellidoBD);
    $correoFinal = normalizarCorreo((string) $correoBD);

    /*
     * Verificar que la cuenta esté activa.
     */
    if ((int) $activoBD !== 1) {
        redirigirConAlerta(
            "danger",
            "Cuenta desactivada",
            "Tu cuenta está desactivada. Comunícate con un administrador."
        );
    }

    /*
     * Google confirma la propiedad del correo.
     * Si todavía no estaba verificado, se actualiza.
     */
    if ((int) $correoVerificadoBD !== 1) {
        $sqlVerificar = "
            UPDATE Usuarios
            SET Correo_Verificado = 1
            WHERE Id_usuario = ?
        ";

        $stmtVerificar = $conn->prepare($sqlVerificar);

        if ($stmtVerificar) {
            $stmtVerificar->bind_param(
                "i",
                $idUsuario
            );

            $stmtVerificar->execute();
            $stmtVerificar->close();
        }
    }
} else {
    $stmtUsuario->close();

    /*
    |--------------------------------------------------------------------------
    | BUSCAR ROL CLIENTE
    |--------------------------------------------------------------------------
    */

    $sqlRolCliente = "
        SELECT Id_rol
        FROM Roles
        WHERE LOWER(TRIM(Nombre)) = 'cliente'
        LIMIT 1
    ";

    $stmtRolCliente = $conn->prepare($sqlRolCliente);

    if (!$stmtRolCliente) {
        redirigirConAlerta(
            "danger",
            "Error del sistema",
            "No fue posible configurar el rol del usuario."
        );
    }

    if (!$stmtRolCliente->execute()) {
        $stmtRolCliente->close();

        redirigirConAlerta(
            "danger",
            "Error del sistema",
            "No fue posible configurar el rol del usuario."
        );
    }

    $stmtRolCliente->bind_result(
        $idRolClienteBD
    );

    if (!$stmtRolCliente->fetch()) {
        $stmtRolCliente->close();

        redirigirConAlerta(
            "danger",
            "Rol no configurado",
            "El rol cliente no existe en el sistema."
        );
    }

    $stmtRolCliente->close();

    $idRolCliente = (int) $idRolClienteBD;

    if ($idRolCliente <= 0) {
        redirigirConAlerta(
            "danger",
            "Rol inválido",
            "El rol cliente no está configurado correctamente."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREAR NUEVO USUARIO DE GOOGLE
    |--------------------------------------------------------------------------
    */

    $tipoLogin = "google";
    $contrasenaGoogle = "";

    $conn->begin_transaction();

    $stmtInsertarUsuario = null;
    $stmtInsertarRol = null;

    try {
        $sqlInsertarUsuario = "
            INSERT INTO Usuarios (
                Nombre,
                Apellido,
                Correo,
                Contrasena,
                Activo,
                Correo_Verificado,
                Tipo_Login,
                Intentos_Login,
                Bloqueado_Hasta
            )
            VALUES (
                ?, ?, ?, ?, 1, 1, ?, 0, NULL
            )
        ";

        $stmtInsertarUsuario = $conn->prepare(
            $sqlInsertarUsuario
        );

        if (!$stmtInsertarUsuario) {
            throw new RuntimeException(
                "No se pudo preparar el registro del usuario."
            );
        }

        $stmtInsertarUsuario->bind_param(
            "sssss",
            $nombre,
            $apellido,
            $correo,
            $contrasenaGoogle,
            $tipoLogin
        );

        if (!$stmtInsertarUsuario->execute()) {
            throw new RuntimeException(
                "No se pudo crear el usuario de Google."
            );
        }

        $idUsuario = (int) $conn->insert_id;

        $stmtInsertarUsuario->close();
        $stmtInsertarUsuario = null;

        if ($idUsuario <= 0) {
            throw new RuntimeException(
                "No se pudo obtener el identificador del usuario."
            );
        }

        /*
         * Asignar rol cliente únicamente
         * a las cuentas nuevas de Google.
         */
        $sqlInsertarRol = "
            INSERT INTO Roles_usuarios (
                Id_usuario,
                Id_rol
            )
            VALUES (?, ?)
        ";

        $stmtInsertarRol = $conn->prepare(
            $sqlInsertarRol
        );

        if (!$stmtInsertarRol) {
            throw new RuntimeException(
                "No se pudo preparar la asignación del rol."
            );
        }

        $stmtInsertarRol->bind_param(
            "ii",
            $idUsuario,
            $idRolCliente
        );

        if (!$stmtInsertarRol->execute()) {
            throw new RuntimeException(
                "No se pudo asignar el rol cliente."
            );
        }

        $stmtInsertarRol->close();
        $stmtInsertarRol = null;

        $conn->commit();
    } catch (Throwable $error) {
        $conn->rollback();

        if ($stmtInsertarUsuario instanceof mysqli_stmt) {
            $stmtInsertarUsuario->close();
        }

        if ($stmtInsertarRol instanceof mysqli_stmt) {
            $stmtInsertarRol->close();
        }

        redirigirConAlerta(
            "danger",
            "Error del sistema",
            "No fue posible registrar la cuenta con Google."
        );
    }
}

/*
|--------------------------------------------------------------------------
| VALIDAR IDENTIFICADOR DEL USUARIO
|--------------------------------------------------------------------------
*/

if ($idUsuario <= 0) {
    redirigirConAlerta(
        "danger",
        "Error del sistema",
        "No fue posible identificar la cuenta del usuario."
    );
}

/*
|--------------------------------------------------------------------------
| OBTENER ROLES CON LA FUNCIÓN CENTRAL
|--------------------------------------------------------------------------
*/

$roles = obtenerRolesUsuario(
    $conn,
    $idUsuario
);

if (empty($roles)) {
    redirigirConAlerta(
        "danger",
        "Sin roles asignados",
        "Tu cuenta no tiene roles configurados."
    );
}

/*
|--------------------------------------------------------------------------
| REGISTRAR ÚLTIMO ACCESO
|--------------------------------------------------------------------------
*/

$sqlUltimoLogin = "
    UPDATE Usuarios
    SET
        Intentos_Login = 0,
        Bloqueado_Hasta = NULL,
        Ultimo_Login = NOW()
    WHERE Id_usuario = ?
";

$stmtUltimoLogin = $conn->prepare(
    $sqlUltimoLogin
);

if ($stmtUltimoLogin) {
    $stmtUltimoLogin->bind_param(
        "i",
        $idUsuario
    );

    $stmtUltimoLogin->execute();
    $stmtUltimoLogin->close();
}

/*
|--------------------------------------------------------------------------
| PREPARAR DATOS OFICIALES DEL USUARIO
|--------------------------------------------------------------------------
*/

$usuario = [
    "Id_usuario" => $idUsuario,
    "Nombre" => $nombreFinal,
    "Apellido" => $apellidoFinal,
    "Correo" => $correoFinal
];

/*
|--------------------------------------------------------------------------
| MENSAJE DE BIENVENIDA
|--------------------------------------------------------------------------
*/

guardarAlerta(
    "success",
    "Inicio de sesión exitoso",
    "Bienvenido, " . $usuario["Nombre"] . "."
);

/*
|--------------------------------------------------------------------------
| CREAR SESIÓN Y REDIRIGIR
|--------------------------------------------------------------------------
|
| Esta función central:
|
| - crea la sesión;
| - guarda los roles;
| - detecta al Super Administrador;
| - envía al selector si existen varios roles;
| - establece el rol activo;
| - redirige al panel correspondiente.
|
*/

iniciarSesionUsuario(
    $usuario,
    $roles
);