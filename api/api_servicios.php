<?php

header("Content-Type: application/json; charset=utf-8");

require_once "../config/conexion.php";

$action = $_GET["action"] ?? "";

function responder(
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

        //==============================================
        // LISTAR
        //==============================================

        case "listar":

            $sql = "
                SELECT
                    Id_servicio,
                    Nombre,
                    Descripcion,
                    Icono,
                    Estado,
                    Orden,
                    Fecha
                FROM Servicios
                ORDER BY
                    Orden ASC,
                    Id_servicio ASC
            ";

            $resultado = $conn->query($sql);

            if (!$resultado) {
                throw new Exception(
                    "No se pudieron cargar los servicios: " .
                    $conn->error
                );
            }

            $servicios = [];

            while ($fila = $resultado->fetch_assoc()) {

                $fila["Id_servicio"] =
                    (int) $fila["Id_servicio"];

                $fila["Estado"] =
                    (int) $fila["Estado"];

                $fila["Orden"] =
                    (int) $fila["Orden"];

                $servicios[] = $fila;
            }

            responder(
                true,
                "",
                $servicios
            );

        //==============================================
        // GUARDAR O EDITAR
        //==============================================

        case "guardar":

            if ($_SERVER["REQUEST_METHOD"] !== "POST") {
                responder(
                    false,
                    "Método no permitido.",
                    null,
                    405
                );
            }

            $id = isset($_POST["id"])
                ? (int) $_POST["id"]
                : 0;

            $nombre = trim(
                $_POST["nombre"] ?? ""
            );

            $descripcion = trim(
                $_POST["descripcion"] ?? ""
            );

            $icono = trim(
                $_POST["icono"] ?? ""
            );

            $estado = isset($_POST["estado"])
                ? (int) $_POST["estado"]
                : 1;

            $orden = isset($_POST["orden"])
                ? (int) $_POST["orden"]
                : 0;

            if ($nombre === "") {
                responder(
                    false,
                    "El nombre del servicio es obligatorio.",
                    null,
                    422
                );
            }

            if (mb_strlen($nombre) > 100) {
                responder(
                    false,
                    "El nombre no puede superar los 100 caracteres.",
                    null,
                    422
                );
            }

            if (mb_strlen($descripcion) > 255) {
                responder(
                    false,
                    "La descripción no puede superar los 255 caracteres.",
                    null,
                    422
                );
            }

            if (mb_strlen($icono) > 100) {
                responder(
                    false,
                    "El icono no puede superar los 100 caracteres.",
                    null,
                    422
                );
            }

            $estado = $estado === 1 ? 1 : 0;

            if ($id > 0) {

                $sql = "
                    UPDATE Servicios
                    SET
                        Nombre = ?,
                        Descripcion = ?,
                        Icono = ?,
                        Estado = ?,
                        Orden = ?
                    WHERE Id_servicio = ?
                ";

                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    throw new Exception(
                        "No se pudo preparar la actualización: " .
                        $conn->error
                    );
                }

                $stmt->bind_param(
                    "sssiii",
                    $nombre,
                    $descripcion,
                    $icono,
                    $estado,
                    $orden,
                    $id
                );

                if (!$stmt->execute()) {
                    throw new Exception(
                        "No se pudo actualizar el servicio: " .
                        $stmt->error
                    );
                }

                $stmt->close();

                responder(
                    true,
                    "Servicio actualizado correctamente."
                );

            } else {

                $sql = "
                    INSERT INTO Servicios
                    (
                        Nombre,
                        Descripcion,
                        Icono,
                        Estado,
                        Orden
                    )
                    VALUES (?, ?, ?, ?, ?)
                ";

                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    throw new Exception(
                        "No se pudo preparar el registro: " .
                        $conn->error
                    );
                }

                $stmt->bind_param(
                    "sssii",
                    $nombre,
                    $descripcion,
                    $icono,
                    $estado,
                    $orden
                );

                if (!$stmt->execute()) {
                    throw new Exception(
                        "No se pudo guardar el servicio: " .
                        $stmt->error
                    );
                }

                $idNuevo = $stmt->insert_id;

                $stmt->close();

                responder(
                    true,
                    "Servicio agregado correctamente.",
                    [
                        "Id_servicio" => (int) $idNuevo
                    ]
                );
            }

        //==============================================
        // ELIMINAR
        //==============================================

        case "eliminar":

            $id = isset($_GET["id"])
                ? (int) $_GET["id"]
                : 0;

            if ($id <= 0) {
                responder(
                    false,
                    "El ID del servicio no es válido.",
                    null,
                    422
                );
            }

            $sql = "
                DELETE FROM Servicios
                WHERE Id_servicio = ?
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception(
                    "No se pudo preparar la eliminación: " .
                    $conn->error
                );
            }

            $stmt->bind_param(
                "i",
                $id
            );

            if (!$stmt->execute()) {
                throw new Exception(
                    "No se pudo eliminar el servicio: " .
                    $stmt->error
                );
            }

            if ($stmt->affected_rows === 0) {

                $stmt->close();

                responder(
                    false,
                    "El servicio no existe.",
                    null,
                    404
                );
            }

            $stmt->close();

            responder(
                true,
                "Servicio eliminado correctamente."
            );

        default:

            responder(
                false,
                "Acción no válida.",
                null,
                400
            );
    }

} catch (Throwable $error) {

    responder(
        false,
        $error->getMessage(),
        null,
        500
    );
}