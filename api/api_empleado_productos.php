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


function responderProducto(
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
    responderProducto(
        [
            "success" => false,
            "message" =>
                "Debes iniciar sesión para consultar los productos."
        ],
        401
    );
}

if (
    !esSuperAdministrador() &&
    !tieneRol("administrador") &&
    !tieneRol("empleado")
) {
    responderProducto(
        [
            "success" => false,
            "message" =>
                "No tienes permiso para consultar los productos."
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
    responderProducto(
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
        // LISTAR PRODUCTOS
        //========================================

        case "listar":

            $sql = "
                SELECT
                    p.Id_producto,
                    p.Nombre,
                    p.Descripcion,
                    p.Precio,
                    p.Stock,
                    p.Id_categoria,
                    c.Nombre AS Categoria,
                    p.Estado
                FROM Productos AS p

                LEFT JOIN Categorias AS c
                    ON c.Id_categoria = p.Id_categoria

                ORDER BY
                    p.Nombre ASC
            ";

            $resultado = $conn->query($sql);

            $productos = [];

            while ($fila = $resultado->fetch_assoc()) {

                $productos[] = [
                    "Id_producto" =>
                        (int) $fila["Id_producto"],

                    "Nombre" =>
                        $fila["Nombre"] ?? "",

                    "Descripcion" =>
                        $fila["Descripcion"] ?? "",

                    "Precio" =>
                        (float) ($fila["Precio"] ?? 0),

                    "Stock" =>
                        (int) ($fila["Stock"] ?? 0),

                    "Id_categoria" =>
                        $fila["Id_categoria"] !== null
                            ? (int) $fila["Id_categoria"]
                            : null,

                    "Categoria" =>
                        $fila["Categoria"] ??
                        "Sin categoría",

                    "Estado" =>
                        (int) ($fila["Estado"] ?? 0)
                ];
            }

            responderProducto([
                "success" => true,
                "data" => $productos
            ]);


        //========================================
        // BUSCAR PRODUCTOS
        //========================================

        case "buscar":

            $busqueda = trim(
                (string) ($_GET["q"] ?? "")
            );

            if ($busqueda === "") {
                responderProducto(
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
                SELECT
                    p.Id_producto,
                    p.Nombre,
                    p.Descripcion,
                    p.Precio,
                    p.Stock,
                    p.Id_categoria,
                    c.Nombre AS Categoria,
                    p.Estado
                FROM Productos AS p

                LEFT JOIN Categorias AS c
                    ON c.Id_categoria = p.Id_categoria

                WHERE
                    p.Nombre LIKE ?
                    OR p.Descripcion LIKE ?
                    OR c.Nombre LIKE ?

                ORDER BY
                    p.Nombre ASC
            ");

            $stmt->bind_param(
                "sss",
                $termino,
                $termino,
                $termino
            );

            $stmt->execute();

            $resultado = $stmt->get_result();

            $productos = [];

            while ($fila = $resultado->fetch_assoc()) {

                $productos[] = [
                    "Id_producto" =>
                        (int) $fila["Id_producto"],

                    "Nombre" =>
                        $fila["Nombre"] ?? "",

                    "Descripcion" =>
                        $fila["Descripcion"] ?? "",

                    "Precio" =>
                        (float) ($fila["Precio"] ?? 0),

                    "Stock" =>
                        (int) ($fila["Stock"] ?? 0),

                    "Id_categoria" =>
                        $fila["Id_categoria"] !== null
                            ? (int) $fila["Id_categoria"]
                            : null,

                    "Categoria" =>
                        $fila["Categoria"] ??
                        "Sin categoría",

                    "Estado" =>
                        (int) ($fila["Estado"] ?? 0)
                ];
            }

            $stmt->close();

            responderProducto([
                "success" => true,
                "data" => $productos
            ]);


        //========================================
        // DETALLE DE PRODUCTO
        //========================================

        case "detalle":

            $idProducto = filter_input(
                INPUT_GET,
                "id",
                FILTER_VALIDATE_INT
            );

            if (!$idProducto || $idProducto <= 0) {
                responderProducto(
                    [
                        "success" => false,
                        "message" =>
                            "El ID del producto no es válido."
                    ],
                    400
                );
            }

            $stmt = $conn->prepare("
                SELECT
                    p.Id_producto,
                    p.Nombre,
                    p.Descripcion,
                    p.Precio,
                    p.Stock,
                    p.Id_categoria,
                    c.Nombre AS Categoria,
                    p.Estado
                FROM Productos AS p

                LEFT JOIN Categorias AS c
                    ON c.Id_categoria = p.Id_categoria

                WHERE p.Id_producto = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "i",
                $idProducto
            );

            $stmt->execute();

            $resultado = $stmt->get_result();

            if ($resultado->num_rows === 0) {

                $stmt->close();

                responderProducto(
                    [
                        "success" => false,
                        "message" =>
                            "El producto no existe."
                    ],
                    404
                );
            }

            $fila = $resultado->fetch_assoc();

            $stmt->close();

            responderProducto([
                "success" => true,

                "producto" => [
                    "Id_producto" =>
                        (int) $fila["Id_producto"],

                    "Nombre" =>
                        $fila["Nombre"] ?? "",

                    "Descripcion" =>
                        $fila["Descripcion"] ?? "",

                    "Precio" =>
                        (float) ($fila["Precio"] ?? 0),

                    "Stock" =>
                        (int) ($fila["Stock"] ?? 0),

                    "Id_categoria" =>
                        $fila["Id_categoria"] !== null
                            ? (int) $fila["Id_categoria"]
                            : null,

                    "Categoria" =>
                        $fila["Categoria"] ??
                        "Sin categoría",

                    "Estado" =>
                        (int) ($fila["Estado"] ?? 0)
                ]
            ]);


        default:

            responderProducto(
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
        "Error en api_empleado_productos.php: " .
        $error->getMessage()
    );

    responderProducto(
        [
            "success" => false,
            "message" =>
                "Ocurrió un error al consultar los productos."
        ],
        500
    );

} finally {

    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}