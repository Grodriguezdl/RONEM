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


function responderCliente(
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
    responderCliente(
        [
            "success" => false,
            "message" =>
                "Debes iniciar sesión para consultar los clientes."
        ],
        401
    );
}

if (
    !esSuperAdministrador() &&
    !tieneRol("administrador") &&
    !tieneRol("empleado")
) {
    responderCliente(
        [
            "success" => false,
            "message" =>
                "No tienes permiso para consultar los clientes."
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
    responderCliente(
        [
            "success" => false,
            "message" =>
                "No fue posible conectar con la base de datos."
        ],
        500
    );
}


$action = trim(
    (string) ($_GET["action"] ?? "listar")
);


try {

    switch ($action) {

        //========================================
        // LISTAR CLIENTES
        //========================================

        case "listar":

            $sql = "
                SELECT DISTINCT
                    u.Id_usuario,
                    u.Nombre,
                    u.Apellido,
                    u.Correo,
                    u.Activo,
                    u.Tipo_Login,
                    u.Correo_Verificado,
                    u.Fecha_Registro,

                    COALESCE(
                        f.Puntos_disponibles,
                        0
                    ) AS Puntos_disponibles,

                    COALESCE(
                        f.Total_puntos_ganados,
                        0
                    ) AS Total_puntos_ganados,

                    COALESCE(
                        f.Total_puntos_usados,
                        0
                    ) AS Total_puntos_usados

                FROM Usuarios AS u

                INNER JOIN Roles_usuarios AS ru
                    ON ru.Id_usuario = u.Id_usuario

                INNER JOIN Roles AS r
                    ON r.Id_rol = ru.Id_rol

                LEFT JOIN Fidelizacion AS f
                    ON f.Id_usuario = u.Id_usuario

                WHERE r.Nombre = 'cliente'

                ORDER BY
                    u.Nombre ASC,
                    u.Apellido ASC
            ";

            $resultado = $conn->query($sql);

            $clientes = [];

            while ($fila = $resultado->fetch_assoc()) {

                $nombreCompleto = trim(
                    ($fila["Nombre"] ?? "") .
                    " " .
                    ($fila["Apellido"] ?? "")
                );

                $clientes[] = [
                    "Id_usuario" =>
                        (int) $fila["Id_usuario"],

                    "Nombre" =>
                        $fila["Nombre"] ?? "",

                    "Apellido" =>
                        $fila["Apellido"] ?? "",

                    "Nombre_completo" =>
                        $nombreCompleto,

                    "Correo" =>
                        $fila["Correo"] ?? "",

                    "Activo" =>
                        (int) ($fila["Activo"] ?? 0),

                    "Tipo_Login" =>
                        $fila["Tipo_Login"] ?? "manual",

                    "Correo_Verificado" =>
                        (int) (
                            $fila["Correo_Verificado"] ?? 0
                        ),

                    "Fecha_Registro" =>
                        $fila["Fecha_Registro"],

                    "Puntos_disponibles" =>
                        (int) (
                            $fila["Puntos_disponibles"] ?? 0
                        ),

                    "Total_puntos_ganados" =>
                        (int) (
                            $fila["Total_puntos_ganados"] ?? 0
                        ),

                    "Total_puntos_usados" =>
                        (int) (
                            $fila["Total_puntos_usados"] ?? 0
                        )
                ];
            }

            responderCliente([
                "success" => true,
                "data" => $clientes
            ]);


        //========================================
        // BUSCAR CLIENTES
        //========================================

        case "buscar":

            $busqueda = trim(
                (string) ($_GET["q"] ?? "")
            );

            if ($busqueda === "") {
                responderCliente(
                    [
                        "success" => false,
                        "message" =>
                            "Debes ingresar un término de búsqueda."
                    ],
                    400
                );
            }

            $termino = "%" . $busqueda . "%";

            $stmt = $conn->prepare("
                SELECT DISTINCT
                    u.Id_usuario,
                    u.Nombre,
                    u.Apellido,
                    u.Correo,
                    u.Activo,
                    u.Tipo_Login,
                    u.Correo_Verificado,
                    u.Fecha_Registro,

                    COALESCE(
                        f.Puntos_disponibles,
                        0
                    ) AS Puntos_disponibles,

                    COALESCE(
                        f.Total_puntos_ganados,
                        0
                    ) AS Total_puntos_ganados,

                    COALESCE(
                        f.Total_puntos_usados,
                        0
                    ) AS Total_puntos_usados

                FROM Usuarios AS u

                INNER JOIN Roles_usuarios AS ru
                    ON ru.Id_usuario = u.Id_usuario

                INNER JOIN Roles AS r
                    ON r.Id_rol = ru.Id_rol

                LEFT JOIN Fidelizacion AS f
                    ON f.Id_usuario = u.Id_usuario

                WHERE
                    r.Nombre = 'cliente'

                    AND (
                        u.Nombre LIKE ?
                        OR u.Apellido LIKE ?
                        OR u.Correo LIKE ?
                        OR CONCAT(
                            u.Nombre,
                            ' ',
                            u.Apellido
                        ) LIKE ?
                    )

                ORDER BY
                    u.Nombre ASC,
                    u.Apellido ASC
            ");

            $stmt->bind_param(
                "ssss",
                $termino,
                $termino,
                $termino,
                $termino
            );

            $stmt->execute();

            $resultado = $stmt->get_result();

            $clientes = [];

            while ($fila = $resultado->fetch_assoc()) {

                $nombreCompleto = trim(
                    ($fila["Nombre"] ?? "") .
                    " " .
                    ($fila["Apellido"] ?? "")
                );

                $clientes[] = [
                    "Id_usuario" =>
                        (int) $fila["Id_usuario"],

                    "Nombre" =>
                        $fila["Nombre"] ?? "",

                    "Apellido" =>
                        $fila["Apellido"] ?? "",

                    "Nombre_completo" =>
                        $nombreCompleto,

                    "Correo" =>
                        $fila["Correo"] ?? "",

                    "Activo" =>
                        (int) ($fila["Activo"] ?? 0),

                    "Tipo_Login" =>
                        $fila["Tipo_Login"] ?? "manual",

                    "Correo_Verificado" =>
                        (int) (
                            $fila["Correo_Verificado"] ?? 0
                        ),

                    "Fecha_Registro" =>
                        $fila["Fecha_Registro"],

                    "Puntos_disponibles" =>
                        (int) (
                            $fila["Puntos_disponibles"] ?? 0
                        ),

                    "Total_puntos_ganados" =>
                        (int) (
                            $fila["Total_puntos_ganados"] ?? 0
                        ),

                    "Total_puntos_usados" =>
                        (int) (
                            $fila["Total_puntos_usados"] ?? 0
                        )
                ];
            }

            $stmt->close();

            responderCliente([
                "success" => true,
                "data" => $clientes
            ]);


        //========================================
        // DETALLE DE CLIENTE
        //========================================

        case "detalle":

            $idCliente = filter_input(
                INPUT_GET,
                "id",
                FILTER_VALIDATE_INT
            );

            if (!$idCliente || $idCliente <= 0) {
                responderCliente(
                    [
                        "success" => false,
                        "message" =>
                            "El ID del cliente no es válido."
                    ],
                    400
                );
            }

            $stmt = $conn->prepare("
                SELECT DISTINCT
                    u.Id_usuario,
                    u.Nombre,
                    u.Apellido,
                    u.Correo,
                    u.Activo,
                    u.Tipo_Login,
                    u.Correo_Verificado,
                    u.Fecha_Registro,

                    COALESCE(
                        f.Puntos_disponibles,
                        0
                    ) AS Puntos_disponibles,

                    COALESCE(
                        f.Total_puntos_ganados,
                        0
                    ) AS Total_puntos_ganados,

                    COALESCE(
                        f.Total_puntos_usados,
                        0
                    ) AS Total_puntos_usados

                FROM Usuarios AS u

                INNER JOIN Roles_usuarios AS ru
                    ON ru.Id_usuario = u.Id_usuario

                INNER JOIN Roles AS r
                    ON r.Id_rol = ru.Id_rol

                LEFT JOIN Fidelizacion AS f
                    ON f.Id_usuario = u.Id_usuario

                WHERE
                    u.Id_usuario = ?
                    AND r.Nombre = 'cliente'

                LIMIT 1
            ");

            $stmt->bind_param(
                "i",
                $idCliente
            );

            $stmt->execute();

            $resultado = $stmt->get_result();

            if ($resultado->num_rows === 0) {

                $stmt->close();

                responderCliente(
                    [
                        "success" => false,
                        "message" =>
                            "El cliente no existe o no tiene rol de cliente."
                    ],
                    404
                );
            }

            $fila = $resultado->fetch_assoc();

            $stmt->close();

            $nombreCompleto = trim(
                ($fila["Nombre"] ?? "") .
                " " .
                ($fila["Apellido"] ?? "")
            );

            responderCliente([
                "success" => true,

                "cliente" => [
                    "Id_usuario" =>
                        (int) $fila["Id_usuario"],

                    "Nombre" =>
                        $fila["Nombre"] ?? "",

                    "Apellido" =>
                        $fila["Apellido"] ?? "",

                    "Nombre_completo" =>
                        $nombreCompleto,

                    "Correo" =>
                        $fila["Correo"] ?? "",

                    "Activo" =>
                        (int) ($fila["Activo"] ?? 0),

                    "Tipo_Login" =>
                        $fila["Tipo_Login"] ?? "manual",

                    "Correo_Verificado" =>
                        (int) (
                            $fila["Correo_Verificado"] ?? 0
                        ),

                    "Fecha_Registro" =>
                        $fila["Fecha_Registro"],

                    "Puntos_disponibles" =>
                        (int) (
                            $fila["Puntos_disponibles"] ?? 0
                        ),

                    "Total_puntos_ganados" =>
                        (int) (
                            $fila["Total_puntos_ganados"] ?? 0
                        ),

                    "Total_puntos_usados" =>
                        (int) (
                            $fila["Total_puntos_usados"] ?? 0
                        )
                ]
            ]);


        default:

            responderCliente(
                [
                    "success" => false,
                    "message" =>
                        "Acción no válida."
                ],
                400
            );
    }

} catch (Throwable $error) {

    error_log(
        "Error en api_empleado_clientes.php: " .
        $error->getMessage()
    );

    responderCliente(
        [
            "success" => false,
            "message" =>
                "Ocurrió un error al consultar los clientes."
        ],
        500
    );

} finally {

    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}