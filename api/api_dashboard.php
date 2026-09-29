<?php

/**
 * api/api_dashboard.php
 */

declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

mysqli_report(MYSQLI_REPORT_OFF);

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/conexion.php";

require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/alertas.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/permisos.php";

/**
 * Envía una respuesta JSON y termina el script.
 */
function responder(
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

/**
 * Verifica que el usuario tenga acceso administrativo.
 *
 * Para una API no conviene usar una función que redirija con HTML,
 * porque JavaScript espera recibir JSON.
 */
if (!estaAutenticado()) {
    responder(
        [
            "success" => false,
            "error" => "Debes iniciar sesión."
        ],
        401
    );
}

if (!tieneRol("administrador")) {
    responder(
        [
            "success" => false,
            "error" => "No tienes permiso para consultar el dashboard."
        ],
        403
    );
}

/**
 * Validar conexión.
 */
if (!isset($conn) || !($conn instanceof mysqli)) {
    responder(
        [
            "success" => false,
            "error" => "No se pudo conectar con la base de datos."
        ],
        500
    );
}

if (!$conn->set_charset("utf8mb4")) {
    responder(
        [
            "success" => false,
            "error" => "No se pudo configurar la conexión."
        ],
        500
    );
}

/**
 * Comprueba si una tabla existe en la base de datos.
 *
 * No utiliza get_result(), para evitar problemas
 * en servidores que no tengan mysqlnd.
 */
function existeTabla(
    mysqli $conn,
    string $tabla
): bool {
    $sql = "
        SELECT COUNT(*)
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "s",
        $tabla
    );

    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }

    $total = 0;

    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();

    return (int) $total > 0;
}

/**
 * Ejecuta una consulta que devuelve un único valor.
 */
function obtenerValor(
    mysqli $conn,
    string $sql,
    string $campo,
    mixed $valorPredeterminado = 0
): mixed {
    $resultado = $conn->query($sql);

    if (!$resultado) {
        return $valorPredeterminado;
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    if (
        !$fila ||
        !array_key_exists($campo, $fila)
    ) {
        return $valorPredeterminado;
    }

    return $fila[$campo] ?? $valorPredeterminado;
}

$action = trim(
    (string) ($_GET["action"] ?? "resumen")
);

try {
    switch ($action) {
        /*
        |--------------------------------------------------------------------------
        | RESUMEN
        |--------------------------------------------------------------------------
        */

        case "resumen":
            $ventasHoy = 0;
            $totalVentasHoy = 0.0;

            if (existeTabla($conn, "Ventas")) {
                $ventasHoy = (int) obtenerValor(
                    $conn,
                    "
                        SELECT COUNT(*) AS total
                        FROM Ventas
                        WHERE DATE(Fecha) = CURDATE()
                    ",
                    "total"
                );

                $totalVentasHoy = (float) obtenerValor(
                    $conn,
                    "
                        SELECT COALESCE(SUM(Total), 0) AS total
                        FROM Ventas
                        WHERE DATE(Fecha) = CURDATE()
                    ",
                    "total"
                );
            }

            /*
             * Compras de hoy.
             */
            $comprasHoy = 0;
            $totalComprasHoy = 0.0;

            if (existeTabla($conn, "Compras")) {
                $comprasHoy = (int) obtenerValor(
                    $conn,
                    "
                        SELECT COUNT(*) AS total
                        FROM Compras
                        WHERE DATE(Fecha) = CURDATE()
                    ",
                    "total"
                );

                $totalComprasHoy = (float) obtenerValor(
                    $conn,
                    "
                        SELECT COALESCE(SUM(Total), 0) AS total
                        FROM Compras
                        WHERE DATE(Fecha) = CURDATE()
                    ",
                    "total"
                );
            }

            /*
             * Clientes registrados.
             */
            $clientes = 0;

            if (
                existeTabla($conn, "Usuarios") &&
                existeTabla($conn, "Roles") &&
                existeTabla($conn, "Roles_usuarios")
            ) {
                $clientes = (int) obtenerValor(
                    $conn,
                    "
                        SELECT COUNT(DISTINCT u.Id_usuario) AS total
                        FROM Usuarios AS u

                        INNER JOIN Roles_usuarios AS ru
                            ON ru.Id_usuario = u.Id_usuario

                        INNER JOIN Roles AS r
                            ON r.Id_rol = ru.Id_rol

                        WHERE LOWER(TRIM(r.Nombre)) = 'cliente'
                    ",
                    "total"
                );
            }

            /*
             * Vehículos registrados.
             */
            $vehiculos = 0;

            if (existeTabla($conn, "Vehiculos")) {
                $vehiculos = (int) obtenerValor(
                    $conn,
                    "
                        SELECT COUNT(*) AS total
                        FROM Vehiculos
                    ",
                    "total"
                );
            }

            /*
             * Técnicos activos.
             */
            $tecnicosActivos = 0;

            if (
                existeTabla($conn, "Usuarios") &&
                existeTabla($conn, "Roles") &&
                existeTabla($conn, "Roles_usuarios")
            ) {
                $tecnicosActivos = (int) obtenerValor(
                    $conn,
                    "
                        SELECT COUNT(DISTINCT u.Id_usuario) AS total
                        FROM Usuarios AS u

                        INNER JOIN Roles_usuarios AS ru
                            ON ru.Id_usuario = u.Id_usuario

                        INNER JOIN Roles AS r
                            ON r.Id_rol = ru.Id_rol

                        WHERE LOWER(TRIM(r.Nombre)) = 'tecnico'
                          AND u.Activo = 1
                    ",
                    "total"
                );
            }

            /*
             * Productos con stock bajo.
             */
            $productosBajos = 0;

            if (existeTabla($conn, "Productos")) {
                $productosBajos = (int) obtenerValor(
                    $conn,
                    "
                        SELECT COUNT(*) AS total
                        FROM Productos
                        WHERE Stock <= 10
                          AND Estado = 1
                    ",
                    "total"
                );
            }

            /*
             * Ventas del mes actual y anterior.
             */
            $ventasMesActual = 0.0;
            $ventasMesAnterior = 0.0;

            if (existeTabla($conn, "Ventas")) {
                $ventasMesActual = (float) obtenerValor(
                    $conn,
                    "
                        SELECT COALESCE(SUM(Total), 0) AS total
                        FROM Ventas
                        WHERE YEAR(Fecha) = YEAR(CURDATE())
                          AND MONTH(Fecha) = MONTH(CURDATE())
                    ",
                    "total"
                );

                $ventasMesAnterior = (float) obtenerValor(
                    $conn,
                    "
                        SELECT COALESCE(SUM(Total), 0) AS total
                        FROM Ventas
                        WHERE YEAR(Fecha) =
                            YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                          AND MONTH(Fecha) =
                            MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                    ",
                    "total"
                );
            }

            $comparacion = 0.0;

            if ($ventasMesAnterior > 0) {
                $comparacion = (
                    (
                        $ventasMesActual -
                        $ventasMesAnterior
                    ) /
                    $ventasMesAnterior
                ) * 100;
            } elseif ($ventasMesActual > 0) {
                $comparacion = 100;
            }

            responder([
                "success" => true,

                "tarjetas" => [
                    "ventas_hoy" => $ventasHoy,
                    "total_ventas_hoy" => $totalVentasHoy,

                    "compras_hoy" => $comprasHoy,
                    "total_compras_hoy" => $totalComprasHoy,

                    "clientes" => $clientes,
                    "vehiculos" => $vehiculos,
                    "tecnicos_activos" => $tecnicosActivos,
                    "productos_bajos" => $productosBajos
                ],

                "resumen_mensual" => [
                    "ventas_mes_actual" => $ventasMesActual,
                    "ventas_mes_anterior" => $ventasMesAnterior,
                    "comparacion_porcentaje" => round(
                        $comparacion,
                        2
                    )
                ]
            ]);

        /*
        |--------------------------------------------------------------------------
        | GRÁFICA DE VENTAS
        |--------------------------------------------------------------------------
        */

        case "graficaVentas":
            if (!existeTabla($conn, "Ventas")) {
                responder([
                    "success" => true,
                    "labels" => [],
                    "valores" => []
                ]);
            }

            $sqlGrafica = "
                SELECT
                    DATE_FORMAT(Fecha, '%Y-%m') AS Mes,
                    COALESCE(SUM(Total), 0) AS Total
                FROM Ventas
                WHERE Fecha >= DATE_SUB(
                    DATE_FORMAT(CURDATE(), '%Y-%m-01'),
                    INTERVAL 11 MONTH
                )
                GROUP BY
                    YEAR(Fecha),
                    MONTH(Fecha)
                ORDER BY
                    YEAR(Fecha) ASC,
                    MONTH(Fecha) ASC
            ";

            $resultadoGrafica = $conn->query(
                $sqlGrafica
            );

            if (!$resultadoGrafica) {
                responder(
                    [
                        "success" => false,
                        "error" => "No fue posible cargar la gráfica de ventas."
                    ],
                    500
                );
            }

            $labels = [];
            $valores = [];

            while (
                $fila = $resultadoGrafica->fetch_assoc()
            ) {
                $labels[] = (string) $fila["Mes"];
                $valores[] = (float) $fila["Total"];
            }

            $resultadoGrafica->free();

            responder([
                "success" => true,
                "labels" => $labels,
                "valores" => $valores
            ]);

        /*
        |--------------------------------------------------------------------------
        | PRODUCTOS CON STOCK BAJO
        |--------------------------------------------------------------------------
        */

        case "productosBajos":
            if (!existeTabla($conn, "Productos")) {
                responder([
                    "success" => true,
                    "productos" => []
                ]);
            }

            $sqlProductos = "
                SELECT
                    Id_producto,
                    Nombre,
                    Stock,
                    Precio,
                    Estado
                FROM Productos
                WHERE Stock <= 10
                  AND Estado = 1
                ORDER BY
                    Stock ASC,
                    Nombre ASC
                LIMIT 10
            ";

            $resultadoProductos = $conn->query(
                $sqlProductos
            );

            if (!$resultadoProductos) {
                responder(
                    [
                        "success" => false,
                        "error" => "No fue posible cargar los productos con stock bajo."
                    ],
                    500
                );
            }

            $productos = [];

            while (
                $fila = $resultadoProductos->fetch_assoc()
            ) {
                $productos[] = [
                    "Id_producto" => (int) $fila["Id_producto"],
                    "Nombre" => (string) $fila["Nombre"],
                    "Stock" => (int) $fila["Stock"],
                    "Precio" => (float) $fila["Precio"],
                    "Estado" => (int) $fila["Estado"]
                ];
            }

            $resultadoProductos->free();

            responder([
                "success" => true,
                "productos" => $productos
            ]);

        default:
            responder(
                [
                    "success" => false,
                    "error" => "La acción solicitada no es válida."
                ],
                400
            );
    }
} catch (Throwable $error) {
    error_log(
        "Error en api_dashboard.php: " .
        $error->getMessage()
    );

    responder(
        [
            "success" => false,
            "error" => "Ocurrió un error al cargar el dashboard."
        ],
        500
    );
}