<?php

//=====================================
// CONFIGURACIÓN GENERAL
//=====================================

session_start();

header("Content-Type: application/json; charset=utf-8");

// Durante las pruebas puedes dejarlo activo.
// Cuando todo funcione, cambia display_errors a 0.
ini_set("display_errors", "0");
ini_set("display_startup_errors", "0");
error_reporting(E_ALL);

//=====================================
// RESPUESTA DE ERROR FATAL
//=====================================

register_shutdown_function(function () {

    $error = error_get_last();

    if (
        $error !== null &&
        in_array(
            $error["type"],
            [
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_COMPILE_ERROR
            ],
            true
        )
    ) {

        if (!headers_sent()) {

            http_response_code(500);

            header(
                "Content-Type: application/json; charset=utf-8"
            );

        }

        echo json_encode(
            [
                "success" => false,
                "message" => "Ocurrió un error interno en la API.",
                "error" => $error["message"],
                "archivo" => basename($error["file"]),
                "linea" => $error["line"]
            ],
            JSON_UNESCAPED_UNICODE
        );

    }

});

//=====================================
// CONEXIÓN
//=====================================

$rutaConexion =
    __DIR__ . "/../config/conexion.php";

if (!file_exists($rutaConexion)) {

    responderError(
        "No se encontró el archivo de conexión.",
        500,
        [
            "ruta_buscada" => $rutaConexion
        ]
    );

}

require_once $rutaConexion;

if (!isset($conn) || !($conn instanceof mysqli)) {

    responderError(
        "La conexión a la base de datos no está disponible.",
        500
    );

}

$conn->set_charset("utf8mb4");

//=====================================
// VALIDAR SESIÓN
//=====================================

$idUsuario =
    (int) (
        $_SESSION["id_usuario"] ??
        $_SESSION["Id_usuario"] ??
        0
    );

if ($idUsuario <= 0) {

    responderError(
        "La sesión no es válida o ha expirado.",
        401
    );

}

//=====================================
// ACCIÓN SOLICITADA
//=====================================

$action =
    trim(
        $_GET["action"] ??
        ""
    );

try {

    switch ($action) {

        case "listar_compras":

            listarCompras(
                $conn,
                $idUsuario
            );

            break;

        case "detalle_compra":

            detalleCompra(
                $conn,
                $idUsuario
            );

            break;

        case "listar_servicios":

            listarServicios(
                $conn,
                $idUsuario
            );

            break;

        default:

            responderError(
                "La acción solicitada no existe.",
                400,
                [
                    "action" => $action
                ]
            );

    }

} catch (Throwable $error) {

    responderError(
        "Ocurrió un error al procesar la solicitud.",
        500,
        [
            "error" => $error->getMessage(),
            "archivo" => basename(
                $error->getFile()
            ),
            "linea" => $error->getLine()
        ]
    );

}

//=====================================
// LISTAR COMPRAS DEL CLIENTE
//=====================================

function listarCompras(
    mysqli $conn,
    int $idUsuario
): void {

    verificarTabla(
        $conn,
        "Ventas"
    );

    verificarColumnas(
        $conn,
        "Ventas",
        [
            "Id_venta",
            "Id_cliente",
            "Fecha",
            "Total",
            "Estado"
        ]
    );

    $sql = "
        SELECT
            Id_venta,
            Fecha,
            Total,
            Estado
        FROM Ventas
        WHERE Id_cliente = ?
        ORDER BY Fecha DESC, Id_venta DESC
    ";

    $stmt = prepararConsulta(
        $conn,
        $sql
    );

    $stmt->bind_param(
        "i",
        $idUsuario
    );

    ejecutarConsulta($stmt);

    $resultado =
        $stmt->get_result();

    $compras = [];

    while (
        $fila =
        $resultado->fetch_assoc()
    ) {

        $compras[] = [
            "Id_venta" =>
                (int) $fila["Id_venta"],

            "Fecha" =>
                $fila["Fecha"],

            "Total" =>
                (float) $fila["Total"],

            "Estado" =>
                $fila["Estado"] ??
                "Registrada"
        ];

    }

    $stmt->close();

    responderJSON(
        [
            "success" => true,
            "compras" => $compras,
            "total" => count($compras)
        ]
    );

}

//=====================================
// DETALLE DE UNA COMPRA
//=====================================

function detalleCompra(
    mysqli $conn,
    int $idUsuario
): void {

    $idVenta =
        filter_input(
            INPUT_GET,
            "id",
            FILTER_VALIDATE_INT
        );

    if (!$idVenta || $idVenta <= 0) {

        responderError(
            "El ID de la venta no es válido.",
            400
        );

    }

    verificarTabla(
        $conn,
        "Ventas"
    );

    verificarTabla(
        $conn,
        "DetalleVentas"
    );

    //=====================================
    // VERIFICAR QUE LA VENTA SEA DEL CLIENTE
    //=====================================

    $sqlVenta = "
        SELECT
            Id_venta,
            Total
        FROM Ventas
        WHERE
            Id_venta = ?
            AND Id_cliente = ?
        LIMIT 1
    ";

    $stmtVenta =
        prepararConsulta(
            $conn,
            $sqlVenta
        );

    $stmtVenta->bind_param(
        "ii",
        $idVenta,
        $idUsuario
    );

    ejecutarConsulta(
        $stmtVenta
    );

    $resultadoVenta =
        $stmtVenta->get_result();

    $venta =
        $resultadoVenta->fetch_assoc();

    $stmtVenta->close();

    if (!$venta) {

        responderError(
            "La compra no existe o no pertenece al usuario autenticado.",
            404
        );

    }

    //=====================================
    // DETECTAR COLUMNAS DEL DETALLE
    //=====================================

    $columnaPrecio =
        primeraColumnaExistente(
            $conn,
            "DetalleVentas",
            [
                "Precio_unidad",
                "Precio_unitario",
                "Precio_Unitario",
                "Precio"
            ]
        );

    $columnaSubtotal =
        primeraColumnaExistente(
            $conn,
            "DetalleVentas",
            [
                "Subtotal",
                "Sub_total",
                "Total"
            ],
            false
        );

    if ($columnaPrecio === null) {

        responderError(
            "No se encontró la columna del precio unitario en DetalleVentas.",
            500,
            [
                "columnas_probadas" => [
                    "Precio_unidad",
                    "Precio_unitario",
                    "Precio_Unitario",
                    "Precio"
                ]
            ]
        );

    }

    verificarColumnas(
        $conn,
        "DetalleVentas",
        [
            "Id_venta",
            "Cantidad"
        ]
    );

    $tieneIdProducto =
        existeColumna(
            $conn,
            "DetalleVentas",
            "Id_producto"
        );

    $tieneTablaProductos =
        existeTabla(
            $conn,
            "Productos"
        );

    $nombreProducto = "'Producto'";

    $joinProducto = "";

    if (
        $tieneIdProducto &&
        $tieneTablaProductos
    ) {

        $columnaNombreProducto =
            primeraColumnaExistente(
                $conn,
                "Productos",
                [
                    "Nombre",
                    "Nombre_producto",
                    "Producto"
                ],
                false
            );

        $columnaIdProducto =
            primeraColumnaExistente(
                $conn,
                "Productos",
                [
                    "Id_producto",
                    "id_producto"
                ],
                false
            );

        if (
            $columnaNombreProducto !== null &&
            $columnaIdProducto !== null
        ) {

            $nombreProducto = "
                COALESCE(
                    p.`{$columnaNombreProducto}`,
                    'Producto eliminado'
                )
            ";

            $joinProducto = "
                LEFT JOIN Productos p
                    ON p.`{$columnaIdProducto}` =
                       dv.Id_producto
            ";

        }

    }

    if ($columnaSubtotal !== null) {

        $expresionSubtotal = "
            COALESCE(
                dv.`{$columnaSubtotal}`,
                dv.Cantidad *
                dv.`{$columnaPrecio}`
            )
        ";

    } else {

        $expresionSubtotal = "
            dv.Cantidad *
            dv.`{$columnaPrecio}`
        ";

    }

    $sqlDetalle = "
        SELECT
            {$nombreProducto} AS Nombre,
            dv.Cantidad,
            dv.`{$columnaPrecio}` AS Precio_unidad,
            {$expresionSubtotal} AS Subtotal
        FROM DetalleVentas dv
        {$joinProducto}
        WHERE dv.Id_venta = ?
    ";

    $stmtDetalle =
        prepararConsulta(
            $conn,
            $sqlDetalle
        );

    $stmtDetalle->bind_param(
        "i",
        $idVenta
    );

    ejecutarConsulta(
        $stmtDetalle
    );

    $resultadoDetalle =
        $stmtDetalle->get_result();

    $detalle = [];

    while (
        $fila =
        $resultadoDetalle->fetch_assoc()
    ) {

        $detalle[] = [
            "Nombre" =>
                $fila["Nombre"] ??
                "Producto",

            "Cantidad" =>
                (int) $fila["Cantidad"],

            "Precio_unidad" =>
                (float) $fila["Precio_unidad"],

            "Subtotal" =>
                (float) $fila["Subtotal"]
        ];

    }

    $stmtDetalle->close();

    responderJSON(
        [
            "success" => true,
            "id_venta" => $idVenta,
            "total" =>
                (float) $venta["Total"],
            "detalle" => $detalle
        ]
    );

}

//=====================================
// LISTAR SERVICIOS DEL CLIENTE
//=====================================

function listarServicios(
    mysqli $conn,
    int $idUsuario
): void {

    verificarTabla(
        $conn,
        "Historial"
    );

    verificarColumnas(
        $conn,
        "Historial",
        [
            "Id_historial",
            "Id_orden",
            "Id_estado",
            "Comentario",
            "Fecha",
            "Id_usuario"
        ]
    );

    //=====================================
    // ESTADO DE MANTENIMIENTO
    //=====================================

    $joinEstado = "";

    $campoEstado =
        "'Sin estado'";

    if (
        existeTabla(
            $conn,
            "Estado_mantenimiento"
        )
    ) {

        $idEstadoMantenimiento =
            primeraColumnaExistente(
                $conn,
                "Estado_mantenimiento",
                [
                    "Id_estado",
                    "Id_estado_mantenimiento"
                ],
                false
            );

        $nombreEstado =
            primeraColumnaExistente(
                $conn,
                "Estado_mantenimiento",
                [
                    "Nombre",
                    "Estado",
                    "Nombre_estado"
                ],
                false
            );

        if (
            $idEstadoMantenimiento !== null &&
            $nombreEstado !== null
        ) {

            $joinEstado = "
                LEFT JOIN Estado_mantenimiento em
                    ON em.`{$idEstadoMantenimiento}` =
                       h.Id_estado
            ";

            $campoEstado = "
                COALESCE(
                    em.`{$nombreEstado}`,
                    'Sin estado'
                )
            ";

        }

    }

    //=====================================
    // DATOS DE LA ORDEN
    //=====================================

    $joinOrden = "";

    $campoProblema =
        "'Sin descripción registrada.'";

    $joinVehiculo = "";

    $campoMarca = "NULL";
    $campoLinea = "NULL";
    $campoPlaca = "NULL";

    if (
        existeTabla(
            $conn,
            "Ordenes"
        )
    ) {

        $idOrdenTabla =
            primeraColumnaExistente(
                $conn,
                "Ordenes",
                [
                    "Id_orden",
                    "id_orden"
                ],
                false
            );

        if ($idOrdenTabla !== null) {

            $joinOrden = "
                LEFT JOIN Ordenes o
                    ON o.`{$idOrdenTabla}` =
                       h.Id_orden
            ";

            $columnaProblema =
                primeraColumnaExistente(
                    $conn,
                    "Ordenes",
                    [
                        "Descripcion_problema",
                        "Problema",
                        "Descripcion",
                        "Diagnostico",
                        "Servicio"
                    ],
                    false
                );

            if (
                $columnaProblema !== null
            ) {

                $campoProblema = "
                    COALESCE(
                        o.`{$columnaProblema}`,
                        'Sin descripción registrada.'
                    )
                ";

            }

            $columnaIdVehiculo =
                primeraColumnaExistente(
                    $conn,
                    "Ordenes",
                    [
                        "Id_vehiculo",
                        "id_vehiculo"
                    ],
                    false
                );

            if (
                $columnaIdVehiculo !== null &&
                existeTabla(
                    $conn,
                    "Vehiculos"
                )
            ) {

                $idVehiculoTabla =
                    primeraColumnaExistente(
                        $conn,
                        "Vehiculos",
                        [
                            "Id_vehiculo",
                            "id_vehiculo"
                        ],
                        false
                    );

                if (
                    $idVehiculoTabla !== null
                ) {

                    $joinVehiculo = "
                        LEFT JOIN Vehiculos v
                            ON v.`{$idVehiculoTabla}` =
                               o.`{$columnaIdVehiculo}`
                    ";

                    $columnaMarca =
                        primeraColumnaExistente(
                            $conn,
                            "Vehiculos",
                            [
                                "Marca",
                                "marca"
                            ],
                            false
                        );

                    $columnaLinea =
                        primeraColumnaExistente(
                            $conn,
                            "Vehiculos",
                            [
                                "Linea",
                                "Línea",
                                "linea"
                            ],
                            false
                        );

                    $columnaPlaca =
                        primeraColumnaExistente(
                            $conn,
                            "Vehiculos",
                            [
                                "Placa",
                                "placa"
                            ],
                            false
                        );

                    if (
                        $columnaMarca !== null
                    ) {

                        $campoMarca =
                            "v.`{$columnaMarca}`";

                    }

                    if (
                        $columnaLinea !== null
                    ) {

                        $campoLinea =
                            "v.`{$columnaLinea}`";

                    }

                    if (
                        $columnaPlaca !== null
                    ) {

                        $campoPlaca =
                            "v.`{$columnaPlaca}`";

                    }

                }

            }

        }

    }

    //=====================================
    // CONSULTA FINAL
    //=====================================

    $sql = "
        SELECT
            h.Id_historial,
            h.Id_orden,
            h.Id_estado,
            h.Comentario,
            h.Fecha,

            {$campoEstado}
                AS Estado,

            {$campoProblema}
                AS Descripcion_problema,

            {$campoMarca}
                AS Marca,

            {$campoLinea}
                AS Linea,

            {$campoPlaca}
                AS Placa

        FROM Historial h

        {$joinEstado}
        {$joinOrden}
        {$joinVehiculo}

        WHERE h.Id_usuario = ?

        ORDER BY
            h.Fecha DESC,
            h.Id_historial DESC
    ";

    $stmt =
        prepararConsulta(
            $conn,
            $sql
        );

    $stmt->bind_param(
        "i",
        $idUsuario
    );

    ejecutarConsulta(
        $stmt
    );

    $resultado =
        $stmt->get_result();

    $servicios = [];

    while (
        $fila =
        $resultado->fetch_assoc()
    ) {

        $servicios[] = [
            "Id_historial" =>
                (int) $fila["Id_historial"],

            "Id_orden" =>
                (int) $fila["Id_orden"],

            "Id_estado" =>
                (int) $fila["Id_estado"],

            "Estado" =>
                $fila["Estado"] ??
                "Sin estado",

            "Comentario" =>
                $fila["Comentario"] ??
                "Sin comentarios registrados.",

            "Fecha" =>
                $fila["Fecha"],

            "Descripcion_problema" =>
                $fila["Descripcion_problema"] ??
                "Sin descripción registrada.",

            "Marca" =>
                $fila["Marca"],

            "Linea" =>
                $fila["Linea"],

            "Placa" =>
                $fila["Placa"]
        ];

    }

    $stmt->close();

    responderJSON(
        [
            "success" => true,
            "servicios" => $servicios,
            "total" => count($servicios)
        ]
    );

}

//=====================================
// PREPARAR CONSULTA
//=====================================

function prepararConsulta(
    mysqli $conn,
    string $sql
): mysqli_stmt {

    $stmt =
        $conn->prepare($sql);

    if (!$stmt) {

        throw new RuntimeException(
            "Error al preparar la consulta: " .
            $conn->error
        );

    }

    return $stmt;

}

//=====================================
// EJECUTAR CONSULTA
//=====================================

function ejecutarConsulta(
    mysqli_stmt $stmt
): void {

    if (!$stmt->execute()) {

        throw new RuntimeException(
            "Error al ejecutar la consulta: " .
            $stmt->error
        );

    }

}

//=====================================
// VERIFICAR TABLA
//=====================================

function verificarTabla(
    mysqli $conn,
    string $tabla
): void {

    if (!existeTabla($conn, $tabla)) {

        throw new RuntimeException(
            "No existe la tabla {$tabla}."
        );

    }

}

//=====================================
// COMPROBAR SI EXISTE UNA TABLA
//=====================================

function existeTabla(
    mysqli $conn,
    string $tabla
): bool {

    $sql = "
        SELECT COUNT(*) AS total
        FROM information_schema.TABLES
        WHERE
            TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
    ";

    $stmt =
        prepararConsulta(
            $conn,
            $sql
        );

    $stmt->bind_param(
        "s",
        $tabla
    );

    ejecutarConsulta(
        $stmt
    );

    $resultado =
        $stmt->get_result();

    $fila =
        $resultado->fetch_assoc();

    $stmt->close();

    return (
        (int) (
            $fila["total"] ??
            0
        ) > 0
    );

}

//=====================================
// VERIFICAR COLUMNAS OBLIGATORIAS
//=====================================

function verificarColumnas(
    mysqli $conn,
    string $tabla,
    array $columnas
): void {

    $faltantes = [];

    foreach ($columnas as $columna) {

        if (
            !existeColumna(
                $conn,
                $tabla,
                $columna
            )
        ) {

            $faltantes[] =
                $columna;

        }

    }

    if (
        count($faltantes) > 0
    ) {

        throw new RuntimeException(
            "Faltan columnas en {$tabla}: " .
            implode(
                ", ",
                $faltantes
            )
        );

    }

}

//=====================================
// COMPROBAR SI EXISTE UNA COLUMNA
//=====================================

function existeColumna(
    mysqli $conn,
    string $tabla,
    string $columna
): bool {

    $sql = "
        SELECT COUNT(*) AS total
        FROM information_schema.COLUMNS
        WHERE
            TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
    ";

    $stmt =
        prepararConsulta(
            $conn,
            $sql
        );

    $stmt->bind_param(
        "ss",
        $tabla,
        $columna
    );

    ejecutarConsulta(
        $stmt
    );

    $resultado =
        $stmt->get_result();

    $fila =
        $resultado->fetch_assoc();

    $stmt->close();

    return (
        (int) (
            $fila["total"] ??
            0
        ) > 0
    );

}

//=====================================
// PRIMERA COLUMNA DISPONIBLE
//=====================================

function primeraColumnaExistente(
    mysqli $conn,
    string $tabla,
    array $columnas,
    bool $obligatoria = true
): ?string {

    foreach ($columnas as $columna) {

        if (
            existeColumna(
                $conn,
                $tabla,
                $columna
            )
        ) {

            return $columna;

        }

    }

    if ($obligatoria) {

        throw new RuntimeException(
            "No se encontró ninguna de estas columnas en {$tabla}: " .
            implode(
                ", ",
                $columnas
            )
        );

    }

    return null;

}

//=====================================
// RESPONDER JSON
//=====================================

function responderJSON(
    array $datos,
    int $codigo = 200
): void {

    http_response_code(
        $codigo
    );

    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;

}

//=====================================
// RESPONDER ERROR
//=====================================

function responderError(
    string $mensaje,
    int $codigo = 400,
    array $datosAdicionales = []
): void {

    responderJSON(
        array_merge(
            [
                "success" => false,
                "message" => $mensaje
            ],
            $datosAdicionales
        ),
        $codigo
    );

}