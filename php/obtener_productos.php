<?php

/**
 * Obtiene los productos activos pertenecientes a un servicio.
 *
 * @param mysqli $conn Conexión activa a MySQL.
 * @param int $idServicio Identificador del servicio.
 * @param int $limite Cantidad máxima de registros. Cero devuelve todos.
 *
 * @return array<int, array<string, mixed>>
 */
function obtenerProductosServicio(mysqli $conn, int $idServicio, int $limite = 0): array
{
    if ($idServicio < 1 || $idServicio > 4) {
        return [];
    }

    if ($limite > 0) {
        $sql = "
            SELECT
                Id_producto_servicio,
                Id_servicio,
                Nombre,
                Descripcion,
                Precio,
                Stock,
                Destacado,
                Orden,
                Estado
            FROM Productos_servicio
            WHERE Id_servicio = ?
              AND Estado = 1
            ORDER BY Orden ASC, Id_producto_servicio ASC
            LIMIT ?
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            error_log("Error al preparar productos limitados: " . $conn->error);
            return [];
        }

        $stmt->bind_param("ii", $idServicio, $limite);
    } else {
        $sql = "
            SELECT
                Id_producto_servicio,
                Id_servicio,
                Nombre,
                Descripcion,
                Precio,
                Stock,
                Destacado,
                Orden,
                Estado
            FROM Productos_servicio
            WHERE Id_servicio = ?
              AND Estado = 1
            ORDER BY Orden ASC, Id_producto_servicio ASC
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            error_log("Error al preparar productos: " . $conn->error);
            return [];
        }

        $stmt->bind_param("i", $idServicio);
    }

    if (!$stmt->execute()) {
        error_log("Error al consultar productos del servicio: " . $stmt->error);
        $stmt->close();
        return [];
    }

    $resultado = $stmt->get_result();
    $productos = [];

    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }

    $stmt->close();

    return $productos;
}
?>
