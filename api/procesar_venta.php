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

function responderVentaProcesada(
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
    responderVentaProcesada(
        [
            "success" => false,
            "message" =>
                "Debes iniciar sesión para registrar una venta."
        ],
        401
    );
}

if (
    !esSuperAdministrador() &&
    !tieneRol("administrador") &&
    !tieneRol("empleado")
) {
    responderVentaProcesada(
        [
            "success" => false,
            "message" =>
                "No tienes permiso para registrar ventas."
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
    responderVentaProcesada(
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
| VALIDAR MÉTODO
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responderVentaProcesada(
        [
            "success" => false,
            "message" =>
                "Método no permitido. Debes utilizar POST."
        ],
        405
    );
}


/*
|--------------------------------------------------------------------------
| RECIBIR JSON O FORMULARIO
|--------------------------------------------------------------------------
*/

$tipoContenido =
    $_SERVER["CONTENT_TYPE"] ?? "";

$datos = [];

if (
    stripos(
        $tipoContenido,
        "application/json"
    ) !== false
) {

    $contenido = file_get_contents("php://input");

    $datosDecodificados = json_decode(
        $contenido ?: "",
        true
    );

    if (!is_array($datosDecodificados)) {
        responderVentaProcesada(
            [
                "success" => false,
                "message" =>
                    "El contenido JSON enviado no es válido."
            ],
            400
        );
    }

    $datos = $datosDecodificados;

} else {

    $datos = $_POST;

    /*
     * Si productos llega como texto JSON desde FormData,
     * lo convertimos en arreglo.
     */
    if (
        isset($datos["productos"]) &&
        is_string($datos["productos"])
    ) {

        $productosDecodificados = json_decode(
            $datos["productos"],
            true
        );

        if (is_array($productosDecodificados)) {
            $datos["productos"] =
                $productosDecodificados;
        }
    }
}


/*
|--------------------------------------------------------------------------
| VALIDAR CLIENTE
|--------------------------------------------------------------------------
*/

$idCliente = null;

if (
    isset($datos["idCliente"]) &&
    $datos["idCliente"] !== "" &&
    $datos["idCliente"] !== null
) {

    $idCliente = filter_var(
        $datos["idCliente"],
        FILTER_VALIDATE_INT
    );

    if ($idCliente === false || $idCliente <= 0) {
        responderVentaProcesada(
            [
                "success" => false,
                "message" =>
                    "El cliente seleccionado no es válido."
            ],
            400
        );
    }

    $idCliente = (int) $idCliente;
}


/*
|--------------------------------------------------------------------------
| VALIDAR PRODUCTOS
|--------------------------------------------------------------------------
*/

$productosRecibidos =
    $datos["productos"] ?? null;

if (
    !is_array($productosRecibidos) ||
    count($productosRecibidos) === 0
) {
    responderVentaProcesada(
        [
            "success" => false,
            "message" =>
                "Debes agregar al menos un producto a la venta."
        ],
        400
    );
}

if (count($productosRecibidos) > 100) {
    responderVentaProcesada(
        [
            "success" => false,
            "message" =>
                "La venta contiene demasiados productos."
        ],
        400
    );
}


/*
|--------------------------------------------------------------------------
| NORMALIZAR PRODUCTOS
|--------------------------------------------------------------------------
|
| Si el mismo producto viene dos veces, sumamos sus cantidades.
|
*/

$productosNormalizados = [];

foreach ($productosRecibidos as $producto) {

    if (!is_array($producto)) {
        responderVentaProcesada(
            [
                "success" => false,
                "message" =>
                    "Uno de los productos enviados no es válido."
            ],
            400
        );
    }

    $idProducto = filter_var(
        $producto["idProducto"] ??
        $producto["Id_producto"] ??
        null,
        FILTER_VALIDATE_INT
    );

    $cantidad = filter_var(
        $producto["cantidad"] ??
        $producto["Cantidad"] ??
        null,
        FILTER_VALIDATE_INT
    );

    if (
        $idProducto === false ||
        $idProducto <= 0
    ) {
        responderVentaProcesada(
            [
                "success" => false,
                "message" =>
                    "Uno de los productos tiene un ID inválido."
            ],
            400
        );
    }

    if (
        $cantidad === false ||
        $cantidad <= 0
    ) {
        responderVentaProcesada(
            [
                "success" => false,
                "message" =>
                    "La cantidad de cada producto debe ser mayor que cero."
            ],
            400
        );
    }

    $idProducto = (int) $idProducto;
    $cantidad = (int) $cantidad;

    if (isset($productosNormalizados[$idProducto])) {

        $productosNormalizados[$idProducto] +=
            $cantidad;

    } else {

        $productosNormalizados[$idProducto] =
            $cantidad;
    }
}


/*
|--------------------------------------------------------------------------
| PROCESAR VENTA
|--------------------------------------------------------------------------
*/

try {

    $conn->begin_transaction();


    /*
    |--------------------------------------------------------------------------
    | VALIDAR CLIENTE
    |--------------------------------------------------------------------------
    */

    if ($idCliente !== null) {

        $stmtCliente = $conn->prepare("
            SELECT
                u.Id_usuario
            FROM Usuarios AS u

            INNER JOIN Roles_usuarios AS ru
                ON ru.Id_usuario = u.Id_usuario

            INNER JOIN Roles AS r
                ON r.Id_rol = ru.Id_rol

            WHERE
                u.Id_usuario = ?
                AND u.Activo = 1
                AND r.Nombre = 'cliente'

            LIMIT 1
        ");

        $stmtCliente->bind_param(
            "i",
            $idCliente
        );

        $stmtCliente->execute();

        $resultadoCliente =
            $stmtCliente->get_result();

        if ($resultadoCliente->num_rows === 0) {

            $stmtCliente->close();
            $conn->rollback();

            responderVentaProcesada(
                [
                    "success" => false,
                    "message" =>
                        "El cliente no existe, está inactivo o no tiene rol de cliente."
                ],
                400
            );
        }

        $stmtCliente->close();
    }


    /*
    |--------------------------------------------------------------------------
    | CONSULTAR Y BLOQUEAR PRODUCTOS
    |--------------------------------------------------------------------------
    */

    $stmtProducto = $conn->prepare("
        SELECT
            Id_producto,
            Nombre,
            Precio,
            Stock,
            Estado
        FROM Productos
        WHERE Id_producto = ?
        LIMIT 1
        FOR UPDATE
    ");

    $productosVenta = [];
    $totalVenta = 0.00;

    foreach (
        $productosNormalizados
        as $idProducto => $cantidad
    ) {

        $stmtProducto->bind_param(
            "i",
            $idProducto
        );

        $stmtProducto->execute();

        $resultadoProducto =
            $stmtProducto->get_result();

        if ($resultadoProducto->num_rows === 0) {

            $stmtProducto->close();
            $conn->rollback();

            responderVentaProcesada(
                [
                    "success" => false,
                    "message" =>
                        "Uno de los productos seleccionados no existe."
                ],
                404
            );
        }

        $filaProducto =
            $resultadoProducto->fetch_assoc();

        $estadoProducto =
            (int) ($filaProducto["Estado"] ?? 0);

        $stockDisponible =
            (int) ($filaProducto["Stock"] ?? 0);

        $precioUnidad =
            (float) ($filaProducto["Precio"] ?? 0);

        $nombreProducto =
            $filaProducto["Nombre"] ??
            "Producto";

        if ($estadoProducto !== 1) {

            $stmtProducto->close();
            $conn->rollback();

            responderVentaProcesada(
                [
                    "success" => false,
                    "message" =>
                        "El producto \"{$nombreProducto}\" no está disponible para la venta."
                ],
                400
            );
        }

        if ($precioUnidad < 0) {

            $stmtProducto->close();
            $conn->rollback();

            responderVentaProcesada(
                [
                    "success" => false,
                    "message" =>
                        "El producto \"{$nombreProducto}\" tiene un precio inválido."
                ],
                500
            );
        }

        if ($stockDisponible < $cantidad) {

            $stmtProducto->close();
            $conn->rollback();

            responderVentaProcesada(
                [
                    "success" => false,
                    "message" =>
                        "No hay suficiente stock de \"{$nombreProducto}\". Disponible: {$stockDisponible}."
                ],
                400
            );
        }

        $subtotal =
            round(
                $precioUnidad * $cantidad,
                2
            );

        $totalVenta += $subtotal;

        $productosVenta[] = [
            "idProducto" => (int) $idProducto,
            "nombre" => $nombreProducto,
            "cantidad" => (int) $cantidad,
            "precioUnidad" => $precioUnidad,
            "subtotal" => $subtotal
        ];
    }

    $stmtProducto->close();

    $totalVenta = round($totalVenta, 2);


    /*
    |--------------------------------------------------------------------------
    | CREAR VENTA
    |--------------------------------------------------------------------------
    */

    $estadoVenta = "Completada";

    $stmtVenta = $conn->prepare("
        INSERT INTO Ventas (
            Id_cliente,
            Fecha,
            Total,
            Estado
        )
        VALUES (
            ?,
            NOW(),
            ?,
            ?
        )
    ");

    $stmtVenta->bind_param(
        "ids",
        $idCliente,
        $totalVenta,
        $estadoVenta
    );

    $stmtVenta->execute();

    $idVenta = (int) $conn->insert_id;

    $stmtVenta->close();


    /*
    |--------------------------------------------------------------------------
    | CREAR DETALLES
    |--------------------------------------------------------------------------
    */

    $stmtDetalle = $conn->prepare("
        INSERT INTO Detalle_venta (
            Id_venta,
            Id_producto,
            Cantidad,
            Precio_unidad
        )
        VALUES (?, ?, ?, ?)
    ");

    $stmtDescontarStock = $conn->prepare("
        UPDATE Productos
        SET Stock = Stock - ?
        WHERE
            Id_producto = ?
            AND Stock >= ?
    ");

    foreach ($productosVenta as $productoVenta) {

        $idProducto =
            (int) $productoVenta["idProducto"];

        $cantidad =
            (int) $productoVenta["cantidad"];

        $precioUnidad =
            (float) $productoVenta["precioUnidad"];

        $stmtDetalle->bind_param(
            "iiid",
            $idVenta,
            $idProducto,
            $cantidad,
            $precioUnidad
        );

        $stmtDetalle->execute();

        $stmtDescontarStock->bind_param(
            "iii",
            $cantidad,
            $idProducto,
            $cantidad
        );

        $stmtDescontarStock->execute();

        if ($stmtDescontarStock->affected_rows !== 1) {
            throw new RuntimeException(
                "No fue posible descontar el stock del producto ID {$idProducto}."
            );
        }
    }

    $stmtDetalle->close();
    $stmtDescontarStock->close();


    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    responderVentaProcesada(
        [
            "success" => true,
            "message" =>
                "Venta registrada correctamente.",

            "venta" => [
                "Id_venta" => $idVenta,
                "Id_cliente" => $idCliente,
                "Total" => $totalVenta,
                "Estado" => $estadoVenta,
                "Productos" => $productosVenta
            ]
        ],
        201
    );

} catch (Throwable $error) {

    try {

        $conn->rollback();

    } catch (Throwable $ignorar) {
    }

    error_log(
        "Error en procesar_venta.php: " .
        $error->getMessage()
    );

    responderVentaProcesada(
        [
            "success" => false,
            "message" =>
                "No fue posible registrar la venta."
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