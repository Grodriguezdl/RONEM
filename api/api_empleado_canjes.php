<?php

declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/conexion.php";

require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/alertas.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/permisos.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn->set_charset("utf8mb4");


/*
|--------------------------------------------------------------------------
| RESPUESTA JSON
|--------------------------------------------------------------------------
*/

function responderCanje(
    array $datos,
    int $codigo = 200
): void {

    http_response_code($codigo);

    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| PROTEGER API
|--------------------------------------------------------------------------
*/

if (!estaAutenticado()) {
    responderCanje(
        [
            "success" => false,
            "message" =>
                "Debes iniciar sesión para consultar los canjes."
        ],
        401
    );
}

if (
    !esSuperAdministrador() &&
    !tieneRol("administrador") &&
    !tieneRol("empleado")
) {
    responderCanje(
        [
            "success" => false,
            "message" =>
                "No tienes permiso para administrar los canjes."
        ],
        403
    );
}


/*
|--------------------------------------------------------------------------
| VALIDAR CONEXIÓN
|--------------------------------------------------------------------------
*/

if (!isset($conn) || !($conn instanceof mysqli)) {
    responderCanje(
        [
            "success" => false,
            "message" =>
                "No fue posible conectar con la base de datos."
        ],
        500
    );
}


/*
|--------------------------------------------------------------------------
| OBTENER DATOS POST
|--------------------------------------------------------------------------
*/

function obtenerDatosCanje(): array
{
    $tipoContenido =
        $_SERVER["CONTENT_TYPE"] ?? "";

    if (
        stripos(
            $tipoContenido,
            "application/json"
        ) !== false
    ) {

        $contenido =
            file_get_contents("php://input");

        $datos = json_decode(
            $contenido ?: "",
            true
        );

        return is_array($datos)
            ? $datos
            : [];
    }

    return $_POST;
}


/*
|--------------------------------------------------------------------------
| CONVERTIR FILA
|--------------------------------------------------------------------------
*/

function formatearCanje(array $fila): array
{
    $nombreCliente = trim(
        (string) ($fila["Nombre"] ?? "") .
        " " .
        (string) ($fila["Apellido"] ?? "")
    );

    if ($nombreCliente === "") {
        $nombreCliente = "Cliente sin nombre";
    }

    return [
        "Id_canje" =>
            (int) $fila["Id_canje"],

        "Id_usuario" =>
            (int) $fila["Id_usuario"],

        "Cliente" =>
            $nombreCliente,

        "Correo" =>
            $fila["Correo"] ?? "",

        "Id_recompensa" =>
            (int) $fila["Id_recompensa"],

        "Recompensa" =>
            $fila["Recompensa"] ?? "",

        "Descripcion" =>
            $fila["Descripcion"] ?? "",

        "Tipo" =>
            $fila["Tipo"] ?? "",

        "Puntos_utilizados" =>
            (int) (
                $fila["Puntos_utilizados"] ?? 0
            ),

        "Porcentaje_descuento" =>
            $fila["Porcentaje_descuento"] !== null
                ? (float) $fila["Porcentaje_descuento"]
                : null,

        "Nombre_servicio" =>
            $fila["Nombre_servicio"] ?? null,

        "Codigo_canje" =>
            $fila["Codigo_canje"] ?? "",

        "Estado" =>
            $fila["Estado"] ?? "Pendiente",

        "Fecha" =>
            $fila["Fecha"] ?? null,

        "Fecha_utilizacion" =>
            $fila["Fecha_utilizacion"] ?? null
    ];
}


$action = trim(
    (string) ($_GET["action"] ?? "listar")
);


try {

    switch ($action) {

        /*
        |--------------------------------------------------------------------------
        | LISTAR CANJES
        |--------------------------------------------------------------------------
        */

        case "listar":

            $estado = trim(
                (string) ($_GET["estado"] ?? "")
            );

            $estadosValidos = [
                "Pendiente",
                "Disponible",
                "Utilizado",
                "Cancelado"
            ];

            $sqlBase = "
                SELECT
                    c.Id_canje,
                    c.Id_usuario,
                    c.Id_recompensa,
                    c.Puntos_utilizados,
                    c.Fecha,
                    c.Estado,
                    c.Codigo_canje,
                    c.Fecha_utilizacion,

                    u.Nombre,
                    u.Apellido,
                    u.Correo,

                    r.Nombre AS Recompensa,
                    r.Descripcion,
                    r.Tipo,
                    r.Porcentaje_descuento,
                    r.Nombre_servicio

                FROM Canjes AS c

                INNER JOIN Usuarios AS u
                    ON u.Id_usuario = c.Id_usuario

                INNER JOIN Recompensas AS r
                    ON r.Id_recompensa =
                       c.Id_recompensa
            ";

            if (
                $estado !== "" &&
                in_array(
                    $estado,
                    $estadosValidos,
                    true
                )
            ) {

                $stmt = $conn->prepare(
                    $sqlBase . "
                    WHERE c.Estado = ?
                    ORDER BY
                        c.Fecha DESC,
                        c.Id_canje DESC
                    "
                );

                $stmt->bind_param(
                    "s",
                    $estado
                );

                $stmt->execute();

                $resultado =
                    $stmt->get_result();

            } else {

                $resultado = $conn->query(
                    $sqlBase . "
                    ORDER BY
                        c.Fecha DESC,
                        c.Id_canje DESC
                    "
                );
            }

            $canjes = [];

            while (
                $fila =
                $resultado->fetch_assoc()
            ) {
                $canjes[] =
                    formatearCanje($fila);
            }

            if (isset($stmt)) {
                $stmt->close();
            }

            responderCanje([
                "success" => true,
                "data" => $canjes
            ]);


        /*
        |--------------------------------------------------------------------------
        | BUSCAR CANJES
        |--------------------------------------------------------------------------
        */

        case "buscar":

            $busqueda = trim(
                (string) ($_GET["q"] ?? "")
            );

            if ($busqueda === "") {
                responderCanje(
                    [
                        "success" => false,
                        "message" =>
                            "Debes ingresar un código, nombre o correo."
                    ],
                    400
                );
            }

            $termino =
                "%" . $busqueda . "%";

            $stmt = $conn->prepare("
                SELECT
                    c.Id_canje,
                    c.Id_usuario,
                    c.Id_recompensa,
                    c.Puntos_utilizados,
                    c.Fecha,
                    c.Estado,
                    c.Codigo_canje,
                    c.Fecha_utilizacion,

                    u.Nombre,
                    u.Apellido,
                    u.Correo,

                    r.Nombre AS Recompensa,
                    r.Descripcion,
                    r.Tipo,
                    r.Porcentaje_descuento,
                    r.Nombre_servicio

                FROM Canjes AS c

                INNER JOIN Usuarios AS u
                    ON u.Id_usuario = c.Id_usuario

                INNER JOIN Recompensas AS r
                    ON r.Id_recompensa =
                       c.Id_recompensa

                WHERE
                    c.Codigo_canje LIKE ?
                    OR u.Nombre LIKE ?
                    OR u.Apellido LIKE ?
                    OR u.Correo LIKE ?
                    OR CONCAT(
                        u.Nombre,
                        ' ',
                        u.Apellido
                    ) LIKE ?
                    OR r.Nombre LIKE ?

                ORDER BY
                    c.Fecha DESC,
                    c.Id_canje DESC
            ");

            $stmt->bind_param(
                "ssssss",
                $termino,
                $termino,
                $termino,
                $termino,
                $termino,
                $termino
            );

            $stmt->execute();

            $resultado =
                $stmt->get_result();

            $canjes = [];

            while (
                $fila =
                $resultado->fetch_assoc()
            ) {
                $canjes[] =
                    formatearCanje($fila);
            }

            $stmt->close();

            responderCanje([
                "success" => true,
                "data" => $canjes
            ]);


        /*
        |--------------------------------------------------------------------------
        | BUSCAR POR CÓDIGO EXACTO
        |--------------------------------------------------------------------------
        */

        case "codigo":

            $codigoCanje = strtoupper(
                trim(
                    (string) (
                        $_GET["codigo"] ?? ""
                    )
                )
            );

            if ($codigoCanje === "") {
                responderCanje(
                    [
                        "success" => false,
                        "message" =>
                            "Debes ingresar el código del canje."
                    ],
                    400
                );
            }

            $stmt = $conn->prepare("
                SELECT
                    c.Id_canje,
                    c.Id_usuario,
                    c.Id_recompensa,
                    c.Puntos_utilizados,
                    c.Fecha,
                    c.Estado,
                    c.Codigo_canje,
                    c.Fecha_utilizacion,

                    u.Nombre,
                    u.Apellido,
                    u.Correo,

                    r.Nombre AS Recompensa,
                    r.Descripcion,
                    r.Tipo,
                    r.Porcentaje_descuento,
                    r.Nombre_servicio

                FROM Canjes AS c

                INNER JOIN Usuarios AS u
                    ON u.Id_usuario = c.Id_usuario

                INNER JOIN Recompensas AS r
                    ON r.Id_recompensa =
                       c.Id_recompensa

                WHERE UPPER(c.Codigo_canje) = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "s",
                $codigoCanje
            );

            $stmt->execute();

            $resultado =
                $stmt->get_result();

            if ($resultado->num_rows === 0) {

                $stmt->close();

                responderCanje(
                    [
                        "success" => false,
                        "message" =>
                            "No se encontró ningún canje con ese código."
                    ],
                    404
                );
            }

            $fila =
                $resultado->fetch_assoc();

            $stmt->close();

            responderCanje([
                "success" => true,
                "canje" =>
                    formatearCanje($fila)
            ]);


        /*
        |--------------------------------------------------------------------------
        | DETALLE POR ID
        |--------------------------------------------------------------------------
        */

        case "detalle":

            $idCanje = filter_input(
                INPUT_GET,
                "id",
                FILTER_VALIDATE_INT
            );

            if (
                !$idCanje ||
                $idCanje <= 0
            ) {
                responderCanje(
                    [
                        "success" => false,
                        "message" =>
                            "El ID del canje no es válido."
                    ],
                    400
                );
            }

            $stmt = $conn->prepare("
                SELECT
                    c.Id_canje,
                    c.Id_usuario,
                    c.Id_recompensa,
                    c.Puntos_utilizados,
                    c.Fecha,
                    c.Estado,
                    c.Codigo_canje,
                    c.Fecha_utilizacion,

                    u.Nombre,
                    u.Apellido,
                    u.Correo,

                    r.Nombre AS Recompensa,
                    r.Descripcion,
                    r.Tipo,
                    r.Porcentaje_descuento,
                    r.Nombre_servicio

                FROM Canjes AS c

                INNER JOIN Usuarios AS u
                    ON u.Id_usuario = c.Id_usuario

                INNER JOIN Recompensas AS r
                    ON r.Id_recompensa =
                       c.Id_recompensa

                WHERE c.Id_canje = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "i",
                $idCanje
            );

            $stmt->execute();

            $resultado =
                $stmt->get_result();

            if ($resultado->num_rows === 0) {

                $stmt->close();

                responderCanje(
                    [
                        "success" => false,
                        "message" =>
                            "El canje solicitado no existe."
                    ],
                    404
                );
            }

            $fila =
                $resultado->fetch_assoc();

            $stmt->close();

            responderCanje([
                "success" => true,
                "canje" =>
                    formatearCanje($fila)
            ]);


        /*
        |--------------------------------------------------------------------------
        | MARCAR COMO DISPONIBLE
        |--------------------------------------------------------------------------
        */

        case "disponible":

            if (
                $_SERVER["REQUEST_METHOD"] !==
                "POST"
            ) {
                responderCanje(
                    [
                        "success" => false,
                        "message" =>
                            "Método no permitido."
                    ],
                    405
                );
            }

            $datos = obtenerDatosCanje();

            $idCanje = filter_var(
                $datos["idCanje"] ??
                $datos["Id_canje"] ??
                null,
                FILTER_VALIDATE_INT
            );

            if (
                $idCanje === false ||
                $idCanje <= 0
            ) {
                responderCanje(
                    [
                        "success" => false,
                        "message" =>
                            "El ID del canje no es válido."
                    ],
                    400
                );
            }

            $stmt = $conn->prepare("
                UPDATE Canjes
                SET Estado = 'Disponible'
                WHERE
                    Id_canje = ?
                    AND Estado = 'Pendiente'
            ");

            $stmt->bind_param(
                "i",
                $idCanje
            );

            $stmt->execute();

            if ($stmt->affected_rows !== 1) {

                $stmt->close();

                responderCanje(
                    [
                        "success" => false,
                        "message" =>
                            "El canje no existe o ya no se encuentra pendiente."
                    ],
                    400
                );
            }

            $stmt->close();

            responderCanje([
                "success" => true,
                "message" =>
                    "El canje quedó disponible para ser utilizado."
            ]);


        /*
        |--------------------------------------------------------------------------
        | MARCAR COMO UTILIZADO
        |--------------------------------------------------------------------------
        */

        case "utilizar":

            if (
                $_SERVER["REQUEST_METHOD"] !==
                "POST"
            ) {
                responderCanje(
                    [
                        "success" => false,
                        "message" =>
                            "Método no permitido."
                    ],
                    405
                );
            }

            $datos = obtenerDatosCanje();

            $idCanje = filter_var(
                $datos["idCanje"] ??
                $datos["Id_canje"] ??
                null,
                FILTER_VALIDATE_INT
            );

            $codigoCanje = strtoupper(
                trim(
                    (string) (
                        $datos["codigo"] ??
                        $datos["Codigo_canje"] ??
                        ""
                    )
                )
            );

            if (
                (
                    $idCanje === false ||
                    $idCanje <= 0
                ) &&
                $codigoCanje === ""
            ) {
                responderCanje(
                    [
                        "success" => false,
                        "message" =>
                            "Debes indicar el ID o código del canje."
                    ],
                    400
                );
            }

            $conn->begin_transaction();

            if (
                $idCanje !== false &&
                $idCanje > 0
            ) {

                $stmtCanje = $conn->prepare("
                    SELECT
                        Id_canje,
                        Codigo_canje,
                        Estado
                    FROM Canjes
                    WHERE Id_canje = ?
                    LIMIT 1
                    FOR UPDATE
                ");

                $stmtCanje->bind_param(
                    "i",
                    $idCanje
                );

            } else {

                $stmtCanje = $conn->prepare("
                    SELECT
                        Id_canje,
                        Codigo_canje,
                        Estado
                    FROM Canjes
                    WHERE UPPER(Codigo_canje) = ?
                    LIMIT 1
                    FOR UPDATE
                ");

                $stmtCanje->bind_param(
                    "s",
                    $codigoCanje
                );
            }

            $stmtCanje->execute();

            $resultadoCanje =
                $stmtCanje->get_result();

            if (
                $resultadoCanje->num_rows === 0
            ) {
                $stmtCanje->close();

                throw new RuntimeException(
                    "CANJE_NO_ENCONTRADO"
                );
            }

            $canje =
                $resultadoCanje->fetch_assoc();

            $stmtCanje->close();

            $estadoActual =
                (string) $canje["Estado"];

            if ($estadoActual === "Utilizado") {
                throw new RuntimeException(
                    "CANJE_YA_UTILIZADO"
                );
            }

            if ($estadoActual === "Cancelado") {
                throw new RuntimeException(
                    "CANJE_CANCELADO"
                );
            }

            if (
                $estadoActual !== "Disponible"
            ) {
                throw new RuntimeException(
                    "CANJE_NO_DISPONIBLE"
                );
            }

            $idCanjeReal =
                (int) $canje["Id_canje"];

            $stmtActualizar =
                $conn->prepare("
                    UPDATE Canjes
                    SET
                        Estado = 'Utilizado',
                        Fecha_utilizacion = NOW()
                    WHERE
                        Id_canje = ?
                        AND Estado = 'Disponible'
                ");

            $stmtActualizar->bind_param(
                "i",
                $idCanjeReal
            );

            $stmtActualizar->execute();

            if (
                $stmtActualizar->affected_rows
                !== 1
            ) {
                $stmtActualizar->close();

                throw new RuntimeException(
                    "NO_ACTUALIZADO"
                );
            }

            $stmtActualizar->close();

            $conn->commit();

            responderCanje([
                "success" => true,
                "message" =>
                    "Canje utilizado correctamente.",
                "Id_canje" =>
                    $idCanjeReal
            ]);


        default:

            responderCanje(
                [
                    "success" => false,
                    "message" =>
                        "Acción no válida."
                ],
                400
            );
    }

} catch (Throwable $error) {

    try {
        $conn->rollback();
    } catch (Throwable $ignorar) {
    }

    $mensajeError =
        $error->getMessage();

    if (
        $mensajeError ===
        "CANJE_NO_ENCONTRADO"
    ) {
        responderCanje(
            [
                "success" => false,
                "message" =>
                    "El canje indicado no existe."
            ],
            404
        );
    }

    if (
        $mensajeError ===
        "CANJE_YA_UTILIZADO"
    ) {
        responderCanje(
            [
                "success" => false,
                "message" =>
                    "Este canje ya fue utilizado anteriormente."
            ],
            400
        );
    }

    if (
        $mensajeError ===
        "CANJE_CANCELADO"
    ) {
        responderCanje(
            [
                "success" => false,
                "message" =>
                    "Este canje fue cancelado y no puede utilizarse."
            ],
            400
        );
    }

    if (
        $mensajeError ===
        "CANJE_NO_DISPONIBLE"
    ) {
        responderCanje(
            [
                "success" => false,
                "message" =>
                    "El canje todavía no está disponible."
            ],
            400
        );
    }

    error_log(
        "Error en api_empleado_canjes.php: " .
        $mensajeError
    );

    responderCanje(
        [
            "success" => false,
            "message" =>
                "Ocurrió un error al procesar el canje."
        ],
        500
    );

} finally {

    if (
        isset($conn) &&
        $conn instanceof mysqli
    ) {
        $conn->close();
    }
}