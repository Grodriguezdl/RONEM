<?php

declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

require_once "../config/conexion.php";

$action = $_GET["action"] ?? "";

function responderRepuestosTecnico(
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

try {

    switch ($action) {

        //==================================================
        // LISTAR REPUESTOS
        //==================================================

        case "listar":

            $sql = "
                SELECT
                    Id_repuesto,
                    Nombre,
                    Descripcion,
                    Stock,
                    Precio,
                    Imagen_URL
                FROM Repuestos
                ORDER BY
                    Nombre ASC,
                    Id_repuesto ASC
            ";

            $resultado = $conn->query($sql);

            if (!$resultado) {
                throw new Exception(
                    "No se pudieron cargar los repuestos: " .
                    $conn->error
                );
            }

            $repuestos = [];

            while ($fila = $resultado->fetch_assoc()) {

                $fila["Id_repuesto"] =
                    (int) $fila["Id_repuesto"];

                $fila["Stock"] =
                    (int) $fila["Stock"];

                $fila["Precio"] =
                    (float) $fila["Precio"];

                $repuestos[] = $fila;
            }

            responderRepuestosTecnico(
                true,
                "",
                $repuestos
            );

        //==================================================
        // OBTENER REPUESTO
        //==================================================

        case "obtener":

            $id = isset($_GET["id"])
                ? (int) $_GET["id"]
                : 0;

            if ($id <= 0) {
                responderRepuestosTecnico(
                    false,
                    "El ID del repuesto no es válido.",
                    null,
                    422
                );
            }

            $sql = "
                SELECT
                    Id_repuesto,
                    Nombre,
                    Descripcion,
                    Stock,
                    Precio,
                    Imagen_URL
                FROM Repuestos
                WHERE Id_repuesto = ?
                LIMIT 1
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
                $id
            );

            if (!$stmt->execute()) {
                throw new Exception(
                    "No se pudo consultar el repuesto: " .
                    $stmt->error
                );
            }

            $resultado = $stmt->get_result();

            if ($resultado->num_rows === 0) {

                $stmt->close();

                responderRepuestosTecnico(
                    false,
                    "El repuesto no existe.",
                    null,
                    404
                );
            }

            $repuesto = $resultado->fetch_assoc();

            $stmt->close();

            $repuesto["Id_repuesto"] =
                (int) $repuesto["Id_repuesto"];

            $repuesto["Stock"] =
                (int) $repuesto["Stock"];

            $repuesto["Precio"] =
                (float) $repuesto["Precio"];

            responderRepuestosTecnico(
                true,
                "",
                $repuesto
            );

        default:

            responderRepuestosTecnico(
                false,
                "Acción no válida.",
                null,
                400
            );
    }

} catch (Throwable $error) {

    responderRepuestosTecnico(
        false,
        $error->getMessage(),
        null,
        500
    );
}