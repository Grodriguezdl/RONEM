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


function responderVenta(
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
| PROTEGER API DE VENTAS
|--------------------------------------------------------------------------
*/

if (!estaAutenticado()) {
    responderVenta(
        [
            "success" => false,
            "message" => "Debes iniciar sesión para consultar las ventas."
        ],
        401
    );
}

if (
    !esSuperAdministrador() &&
    !esSuperAdministrador() &&
    !tieneRol("administrador") &&
    !tieneRol("empleado")
) {
    responderVenta(
        [
            "success" => false,
            "message" => "No tienes permiso para administrar las ventas."
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
    responderVenta(
        [
            "success" => false,
            "message" => "No fue posible conectar con la base de datos."
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
        // LISTAR VENTAS
        //========================================

        case "listarVentas":

            $sql = "
                SELECT
                    v.Id_venta,
                    v.Id_cliente,

                    CONCAT(
                        COALESCE(u.Nombre, ''),
                        ' ',
                        COALESCE(u.Apellido, '')
                    ) AS Cliente,

                    u.Correo AS Correo_cliente,
                    v.Fecha,
                    v.Total,
                    v.Estado,

                    (
                        SELECT COUNT(*)
                        FROM Detalle_venta AS dv
                        WHERE dv.Id_venta = v.Id_venta
                    ) AS Total_detalles,

                    (
                        SELECT COALESCE(
                            SUM(dv.Cantidad),
                            0
                        )
                        FROM Detalle_venta AS dv
                        WHERE dv.Id_venta = v.Id_venta
                    ) AS Total_productos

                FROM Ventas AS v

                LEFT JOIN Usuarios AS u
                    ON u.Id_usuario = v.Id_cliente

                ORDER BY
                    v.Fecha DESC,
                    v.Id_venta DESC
            ";

            $resultado = $conn->query($sql);

            $ventas = [];

            while ($fila = $resultado->fetch_assoc()) {

                $nombreCliente = trim(
                    (string) ($fila["Cliente"] ?? "")
                );

                if ($nombreCliente === "") {
                    $nombreCliente =
                        "Consumidor final";
                }

                $ventas[] = [
                    "Id_venta" =>
                        (int) $fila["Id_venta"],

                    "Id_cliente" =>
                        $fila["Id_cliente"] !== null
                            ? (int) $fila["Id_cliente"]
                            : null,

                    "Cliente" =>
                        $nombreCliente,

                    "Correo_cliente" =>
                        $fila["Correo_cliente"] ?? null,

                    "Fecha" =>
                        $fila["Fecha"],

                    "Total" =>
                        (float) $fila["Total"],

                    "Estado" =>
                        $fila["Estado"] ??
                        "Sin estado",

                    "Total_detalles" =>
                        (int) $fila["Total_detalles"],

                    "Total_productos" =>
                        (int) $fila["Total_productos"]
                ];
            }

            responderVenta([
                "success" => true,
                "data" => $ventas
            ]);

        //========================================
        // LISTAR DETALLES
        //========================================

        case "listar":

            $sql = "
                SELECT
                    d.Id_detalle,
                    d.Id_venta,
                    d.Id_producto,
                    p.Nombre AS Producto,
                    d.Cantidad,
                    d.Precio_unidad,
                    (
                        d.Cantidad * d.Precio_unidad
                    ) AS Subtotal
                FROM Detalle_venta AS d

                LEFT JOIN Productos AS p
                    ON p.Id_producto = d.Id_producto

                ORDER BY
                    d.Id_venta DESC,
                    d.Id_detalle DESC
            ";

            $resultado = $conn->query($sql);

            $ventas = [];

            while ($fila = $resultado->fetch_assoc()) {

                $ventas[] = [
                    "Id_detalle" =>
                        (int) $fila["Id_detalle"],

                    "Id_venta" =>
                        (int) $fila["Id_venta"],

                    "Id_producto" =>
                        $fila["Id_producto"] !== null
                            ? (int) $fila["Id_producto"]
                            : null,

                    "Producto" =>
                        $fila["Producto"] ??
                        "Producto eliminado",

                    "Cantidad" =>
                        (int) $fila["Cantidad"],

                    "Precio_unidad" =>
                        (float) $fila["Precio_unidad"],

                    "Subtotal" =>
                        (float) $fila["Subtotal"]
                ];
            }

            responderVenta([
                "success" => true,
                "data" => $ventas
            ]);

        //========================================
        // DETALLE INDIVIDUAL
        //========================================

        case "detalle":

            $idDetalle = filter_input(
                INPUT_GET,
                "id",
                FILTER_VALIDATE_INT
            );

            if (!$idDetalle || $idDetalle <= 0) {
                responderVenta([
                    "success" => false,
                    "message" =>
                        "El ID del detalle no es válido."
                ], 400);
            }

            $stmt = $conn->prepare("
                SELECT
                    d.Id_detalle,
                    d.Id_venta,
                    d.Id_producto,
                    p.Nombre AS Producto,
                    d.Cantidad,
                    d.Precio_unidad,
                    (
                        d.Cantidad * d.Precio_unidad
                    ) AS Subtotal
                FROM Detalle_venta AS d

                LEFT JOIN Productos AS p
                    ON p.Id_producto = d.Id_producto

                WHERE d.Id_detalle = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "i",
                $idDetalle
            );

            $stmt->execute();

            $resultado = $stmt->get_result();

            if ($resultado->num_rows === 0) {

                $stmt->close();

                responderVenta([
                    "success" => false,
                    "message" =>
                        "El detalle de venta no existe."
                ], 404);
            }

            $fila = $resultado->fetch_assoc();

            $stmt->close();

            responderVenta([
                "success" => true,

                "detalle" => [
                    "Id_detalle" =>
                        (int) $fila["Id_detalle"],

                    "Id_venta" =>
                        (int) $fila["Id_venta"],

                    "Id_producto" =>
                        $fila["Id_producto"] !== null
                            ? (int) $fila["Id_producto"]
                            : null,

                    "Producto" =>
                        $fila["Producto"] ??
                        "Producto eliminado",

                    "Cantidad" =>
                        (int) $fila["Cantidad"],

                    "Precio_unidad" =>
                        (float) $fila["Precio_unidad"],

                    "Subtotal" =>
                        (float) $fila["Subtotal"]
                ]
            ]);

        //========================================
        // DETALLES DE UNA VENTA
        //========================================

        case "porVenta":

            $idVenta = filter_input(
                INPUT_GET,
                "id",
                FILTER_VALIDATE_INT
            );

            if (!$idVenta || $idVenta <= 0) {
                responderVenta([
                    "success" => false,
                    "message" =>
                        "El ID de la venta no es válido."
                ], 400);
            }

            $stmtVenta = $conn->prepare("
                SELECT
                    Id_venta,
                    Id_cliente,
                    Fecha,
                    Total,
                    Estado
                FROM Ventas
                WHERE Id_venta = ?
                LIMIT 1
            ");

            $stmtVenta->bind_param(
                "i",
                $idVenta
            );

            $stmtVenta->execute();

            $resultadoVenta =
                $stmtVenta->get_result();

            if ($resultadoVenta->num_rows === 0) {

                $stmtVenta->close();

                responderVenta([
                    "success" => false,
                    "message" =>
                        "La venta no existe."
                ], 404);
            }

            $venta = $resultadoVenta->fetch_assoc();

            $stmtVenta->close();

            $stmtDetalles = $conn->prepare("
                SELECT
                    d.Id_detalle,
                    d.Id_venta,
                    d.Id_producto,
                    p.Nombre AS Producto,
                    d.Cantidad,
                    d.Precio_unidad,
                    (
                        d.Cantidad * d.Precio_unidad
                    ) AS Subtotal
                FROM Detalle_venta AS d

                LEFT JOIN Productos AS p
                    ON p.Id_producto = d.Id_producto

                WHERE d.Id_venta = ?

                ORDER BY d.Id_detalle ASC
            ");

            $stmtDetalles->bind_param(
                "i",
                $idVenta
            );

            $stmtDetalles->execute();

            $resultadoDetalles =
                $stmtDetalles->get_result();

            $detalles = [];

            while (
                $fila = $resultadoDetalles->fetch_assoc()
            ) {

                $detalles[] = [
                    "Id_detalle" =>
                        (int) $fila["Id_detalle"],

                    "Id_venta" =>
                        (int) $fila["Id_venta"],

                    "Id_producto" =>
                        $fila["Id_producto"] !== null
                            ? (int) $fila["Id_producto"]
                            : null,

                    "Producto" =>
                        $fila["Producto"] ??
                        "Producto eliminado",

                    "Cantidad" =>
                        (int) $fila["Cantidad"],

                    "Precio_unidad" =>
                        (float) $fila["Precio_unidad"],

                    "Subtotal" =>
                        (float) $fila["Subtotal"]
                ];
            }

            $stmtDetalles->close();

            responderVenta([
                "success" => true,
                "idVenta" => (int) $venta["Id_venta"],
                "idCliente" => $venta["Id_cliente"] !== null
                    ? (int) $venta["Id_cliente"]
                    : null,
                "fecha" => $venta["Fecha"],
                "estado" => $venta["Estado"],
                "total" => (float) $venta["Total"],
                "detalles" => $detalles
            ]);

        //========================================
        // RESUMEN
        //========================================

        case "resumen":

            $sql = "
                SELECT
                    (
                        SELECT COUNT(*)
                        FROM Ventas
                    ) AS Total_ventas,

                    (
                        SELECT COUNT(*)
                        FROM Detalle_venta
                    ) AS Total_detalles,

                    (
                        SELECT
                            COALESCE(
                                SUM(Cantidad),
                                0
                            )
                        FROM Detalle_venta
                    ) AS Productos_vendidos,

                    (
                        SELECT
                            COALESCE(
                                SUM(Total),
                                0
                            )
                        FROM Ventas
                    ) AS Total_ingresos
            ";

            $resultado = $conn->query($sql);

            $resumen = $resultado->fetch_assoc();

            responderVenta([
                "success" => true,

                "resumen" => [
                    "Total_ventas" =>
                        (int) $resumen["Total_ventas"],

                    "Total_detalles" =>
                        (int) $resumen["Total_detalles"],

                    "Productos_vendidos" =>
                        (int) $resumen["Productos_vendidos"],

                    "Total_ingresos" =>
                        (float) $resumen["Total_ingresos"]
                ]
            ]);

        //========================================
        // ELIMINAR DETALLE
        //========================================

        case "eliminar":
             if (
        !esSuperAdministrador() &&
        !tieneRol("administrador")
    ) {
        responderVenta(
            [
                "success" => false,
                "message" =>
                    "Solo un administrador puede eliminar detalles de venta."
            ],
            403
        );
    }

            if ($_SERVER["REQUEST_METHOD"] !== "POST") {
                responderVenta([
                    "success" => false,
                    "message" =>
                        "Método no permitido."
                ], 405);
            }

            $idDetalle = filter_input(
                INPUT_POST,
                "id",
                FILTER_VALIDATE_INT
            );

            if (!$idDetalle || $idDetalle <= 0) {
                responderVenta([
                    "success" => false,
                    "message" =>
                        "El ID del detalle no es válido."
                ], 400);
            }

            $conn->begin_transaction();

            $stmtBuscar = $conn->prepare("
                SELECT
                    Id_venta,
                    Id_producto,
                    Cantidad
                FROM Detalle_venta
                WHERE Id_detalle = ?
                FOR UPDATE
            ");

            $stmtBuscar->bind_param(
                "i",
                $idDetalle
            );

            $stmtBuscar->execute();

            $resultadoBuscar =
                $stmtBuscar->get_result();

            if ($resultadoBuscar->num_rows === 0) {

                $stmtBuscar->close();
                $conn->rollback();

                responderVenta([
                    "success" => false,
                    "message" =>
                        "El detalle no existe."
                ], 404);
            }

            $detalle =
                $resultadoBuscar->fetch_assoc();

            $stmtBuscar->close();

            $idVenta =
                (int) $detalle["Id_venta"];

            $idProducto =
                $detalle["Id_producto"] !== null
                    ? (int) $detalle["Id_producto"]
                    : null;

            $cantidad =
                (int) $detalle["Cantidad"];

            // Devolver el producto al stock
            if ($idProducto !== null) {

                $stmtStock = $conn->prepare("
                    UPDATE Productos
                    SET Stock = Stock + ?
                    WHERE Id_producto = ?
                ");

                $stmtStock->bind_param(
                    "ii",
                    $cantidad,
                    $idProducto
                );

                $stmtStock->execute();
                $stmtStock->close();
            }

            $stmtEliminar = $conn->prepare("
                DELETE FROM Detalle_venta
                WHERE Id_detalle = ?
            ");

            $stmtEliminar->bind_param(
                "i",
                $idDetalle
            );

            $stmtEliminar->execute();
            $stmtEliminar->close();

            // Recalcular total
            $stmtTotal = $conn->prepare("
                SELECT
                    COALESCE(
                        SUM(
                            Cantidad * Precio_unidad
                        ),
                        0
                    ) AS Total
                FROM Detalle_venta
                WHERE Id_venta = ?
            ");

            $stmtTotal->bind_param(
                "i",
                $idVenta
            );

            $stmtTotal->execute();

            $filaTotal = $stmtTotal
                ->get_result()
                ->fetch_assoc();

            $nuevoTotal =
                (float) $filaTotal["Total"];

            $stmtTotal->close();

            $stmtActualizar = $conn->prepare("
                UPDATE Ventas
                SET Total = ?
                WHERE Id_venta = ?
            ");

            $stmtActualizar->bind_param(
                "di",
                $nuevoTotal,
                $idVenta
            );

            $stmtActualizar->execute();
            $stmtActualizar->close();

            $conn->commit();

            responderVenta([
                "success" => true,
                "message" =>
                    "Detalle eliminado correctamente.",
                "idVenta" => $idVenta,
                "nuevoTotal" => $nuevoTotal
            ]);

        default:

            responderVenta([
                "success" => false,
                "message" => "Acción no válida."
            ], 400);
    }

} catch (Throwable $error) {

    try {
        $conn->rollback();
    } catch (Throwable $ignorar) {
    }

    error_log($error->getMessage());

    responderVenta([
        "success" => false,
        "message" =>
            "Error del servidor: " .
            $error->getMessage()
    ], 500);

} finally {

    if (isset($conn)) {
        $conn->close();
    }
}