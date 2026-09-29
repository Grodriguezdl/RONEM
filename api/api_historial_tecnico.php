<?php

declare(strict_types=1);

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../config/conexion.php";

$action = $_GET["action"] ?? "";

function responderHistorialTecnico(
    bool $success,
    string $mensaje = "",
    mixed $data = null,
    int $codigo = 200
): never {

    http_response_code($codigo);

    $respuesta = [
        "success" => $success,
        "mensaje" => $mensaje
    ];

    if ($data !== null) {
        $respuesta["data"] = $data;
    }

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}

function obtenerIdUsuarioTecnico(): ?int
{
    $idUsuario =
        $_SESSION["Id_usuario"] ??
        $_SESSION["id_usuario"] ??
        $_SESSION["usuario_id"] ??
        $_SESSION["user_id"] ??
        null;

    if ($idUsuario === null) {
        return null;
    }

    $idUsuario = (int) $idUsuario;

    return $idUsuario > 0
        ? $idUsuario
        : null;
}

try {

    switch ($action) {

        //==================================================
        // LISTAR HISTORIAL DEL TÉCNICO
        //==================================================

        case "listar":

            $idUsuario = obtenerIdUsuarioTecnico();

            /*
             * Por ahora se listará todo si no existe una sesión válida.
             * Cuando confirmemos el nombre exacto de la sesión,
             * se puede hacer obligatorio.
             */

            $sql = "
                SELECT
                    h.Id_historial,
                    h.Id_orden,
                    h.Id_estado,
                    h.Comentario,
                    h.Fecha,
                    h.Id_usuario,

                    CONCAT(
                        COALESCE(u.Nombre, ''),
                        ' ',
                        COALESCE(u.Apellido, '')
                    ) AS Usuario,

                    o.Id_vehiculo,

                    v.Placa,
                    v.Marca,
                    v.Linea,
                    v.Modelo,
                    v.Tipo_vehiculo

                FROM Historial h

                LEFT JOIN Usuarios u
                    ON u.Id_usuario = h.Id_usuario

                LEFT JOIN Ordenes o
                    ON o.Id_orden = h.Id_orden

                LEFT JOIN Vehiculos v
                    ON v.Id_vehiculo = o.Id_vehiculo
            ";

            if ($idUsuario !== null) {

                $sql .= "
                    WHERE h.Id_usuario = ?
                ";
            }

            $sql .= "
                ORDER BY
                    h.Fecha DESC,
                    h.Id_historial DESC
            ";

            if ($idUsuario !== null) {

                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    throw new Exception(
                        "No se pudo preparar la consulta: " .
                        $conn->error
                    );
                }

                $stmt->bind_param(
                    "i",
                    $idUsuario
                );

                if (!$stmt->execute()) {
                    throw new Exception(
                        "No se pudo cargar el historial: " .
                        $stmt->error
                    );
                }

                $resultado = $stmt->get_result();

            } else {

                $resultado = $conn->query($sql);

                if (!$resultado) {
                    throw new Exception(
                        "No se pudo cargar el historial: " .
                        $conn->error
                    );
                }
            }

            $historial = [];

            while ($fila = $resultado->fetch_assoc()) {

                $fila["Id_historial"] =
                    (int) $fila["Id_historial"];

                $fila["Id_orden"] =
                    (int) $fila["Id_orden"];

                $fila["Id_estado"] =
                    (int) $fila["Id_estado"];

                $fila["Id_usuario"] =
                    (int) $fila["Id_usuario"];

                $fila["Id_vehiculo"] =
                    $fila["Id_vehiculo"] !== null
                        ? (int) $fila["Id_vehiculo"]
                        : null;

                $fila["Modelo"] =
                    $fila["Modelo"] !== null
                        ? (int) $fila["Modelo"]
                        : null;

                $historial[] = $fila;
            }

            if (isset($stmt)) {
                $stmt->close();
            }

            responderHistorialTecnico(
                true,
                "",
                $historial
            );

        //==================================================
        // OBTENER HISTORIAL DE UNA ORDEN
        //==================================================

        case "por_orden":

            $idOrden = isset($_GET["id_orden"])
                ? (int) $_GET["id_orden"]
                : 0;

            if ($idOrden <= 0) {
                responderHistorialTecnico(
                    false,
                    "El ID de la orden no es válido.",
                    null,
                    422
                );
            }

            $sql = "
                SELECT
                    h.Id_historial,
                    h.Id_orden,
                    h.Id_estado,
                    h.Comentario,
                    h.Fecha,
                    h.Id_usuario,

                    CONCAT(
                        COALESCE(u.Nombre, ''),
                        ' ',
                        COALESCE(u.Apellido, '')
                    ) AS Usuario

                FROM Historial h

                LEFT JOIN Usuarios u
                    ON u.Id_usuario = h.Id_usuario

                WHERE h.Id_orden = ?

                ORDER BY
                    h.Fecha DESC,
                    h.Id_historial DESC
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception(
                    "No se pudo preparar la consulta: " .
                    $conn->error
                );
            }

            $stmt->bind_param(
                "i",
                $idOrden
            );

            if (!$stmt->execute()) {
                throw new Exception(
                    "No se pudo consultar el historial de la orden: " .
                    $stmt->error
                );
            }

            $resultado = $stmt->get_result();

            $historial = [];

            while ($fila = $resultado->fetch_assoc()) {

                $fila["Id_historial"] =
                    (int) $fila["Id_historial"];

                $fila["Id_orden"] =
                    (int) $fila["Id_orden"];

                $fila["Id_estado"] =
                    (int) $fila["Id_estado"];

                $fila["Id_usuario"] =
                    (int) $fila["Id_usuario"];

                $historial[] = $fila;
            }

            $stmt->close();

            responderHistorialTecnico(
                true,
                "",
                $historial
            );

        default:

            responderHistorialTecnico(
                false,
                "Acción no válida.",
                null,
                400
            );
    }

} catch (Throwable $error) {

    responderHistorialTecnico(
        false,
        $error->getMessage(),
        null,
        500
    );
}