<?php

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/obtener_productos.php";

/**
 * Obtiene los cuatro servicios activos y los primeros productos de cada uno.
 *
 * @param mysqli $conn Conexión activa a MySQL.
 * @param int $limiteProductos Cantidad de productos mostrados en el index.
 *
 * @return array<int, array<string, mixed>>
 */
function obtenerServicios(mysqli $conn, int $limiteProductos = 6): array
{
    $sql = "
        SELECT
            Id_servicio,
            Nombre,
            Descripcion,
            Icono,
            Estado,
            Orden
        FROM Servicios
        WHERE Estado = 1
          AND Id_servicio BETWEEN 1 AND 4
        ORDER BY Orden ASC, Id_servicio ASC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Error al preparar la consulta de servicios: " . $conn->error);
        return [];
    }

    if (!$stmt->execute()) {
        error_log("Error al consultar servicios: " . $stmt->error);
        $stmt->close();
        return [];
    }

    $resultado = $stmt->get_result();
    $servicios = [];

    while ($servicio = $resultado->fetch_assoc()) {
        $servicio["productos"] = obtenerProductosServicio(
            $conn,
            (int) $servicio["Id_servicio"],
            $limiteProductos
        );

        $servicios[] = $servicio;
    }

    $stmt->close();

    return $servicios;
}

$serviciosCatalogo = obtenerServicios($conn, 6);
?>
