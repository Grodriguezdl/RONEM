<?php

header("Content-Type: application/json; charset=utf-8");

mysqli_report(MYSQLI_REPORT_OFF);

require_once "../config/conexion.php";

//==================================================
// FUNCIÓN PARA RESPONDER EN JSON
//==================================================

function responder($datos, int $codigo = 200): void
{
    http_response_code($codigo);

    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}

//==================================================
// VALIDAR CONEXIÓN
//==================================================

if (!isset($conn) || !($conn instanceof mysqli)) {

    responder([
        "success" => false,
        "error" => "No se pudo establecer la conexión con la base de datos."
    ], 500);
}

$conn->set_charset("utf8mb4");

$action = $_GET["action"] ?? "listar";

try {

    switch ($action) {

        //==================================================
        // LISTAR SOLICITUDES
        //==================================================

        case "listar":

            $sql = "
                SELECT
                    s.Id_solicitud,
                    s.Id_usuario,
                    s.Nombre,
                    s.Apellido,
                    s.DPI,
                    s.Fecha_nacimiento,
                    s.Genero,
                    s.Altura_cm,
                    s.Peso_kg,
                    s.Telefono,
                    s.Correo,
                    s.Direccion,
                    s.Id_nivel,

                    CONCAT(
                        'Nivel ',
                        s.Id_nivel
                    ) AS Nivel,

                    s.Id_tipo_licencia,

                    CONCAT(
                        'Tipo ',
                        s.Id_tipo_licencia
                    ) AS TipoLicencia,

                    s.Nombre_emergencia,
                    s.Contacto_emergencia,
                    s.Condiciones_medicas,
                    s.Acepta_terminos,
                    s.Fecha_solicitud,
                    s.Id_estado,

                    COALESCE(
                        e.Nombre,
                        CONCAT(
                            'Estado ',
                            s.Id_estado
                        )
                    ) AS Estado,

                    s.Edad

                FROM Solicitudes_Escuela AS s

                LEFT JOIN Estados AS e
                    ON s.Id_estado = e.Id_estado

                ORDER BY
                    s.Fecha_solicitud DESC,
                    s.Id_solicitud DESC
            ";

            $resultado = $conn->query($sql);

            if (!$resultado) {

                responder([
                    "success" => false,
                    "error" => "Error al listar solicitudes: " .
                               $conn->error
                ], 500);
            }

            $solicitudes = [];

            while ($fila = $resultado->fetch_assoc()) {

                $fila["Id_solicitud"] =
                    (int)$fila["Id_solicitud"];

                $fila["Id_usuario"] =
                    $fila["Id_usuario"] !== null
                        ? (int)$fila["Id_usuario"]
                        : null;

                $fila["Id_nivel"] =
                    $fila["Id_nivel"] !== null
                        ? (int)$fila["Id_nivel"]
                        : null;

                $fila["Id_tipo_licencia"] =
                    $fila["Id_tipo_licencia"] !== null
                        ? (int)$fila["Id_tipo_licencia"]
                        : null;

                $fila["Id_estado"] =
                    $fila["Id_estado"] !== null
                        ? (int)$fila["Id_estado"]
                        : null;

                $fila["Edad"] =
                    $fila["Edad"] !== null
                        ? (int)$fila["Edad"]
                        : null;

                $solicitudes[] = $fila;
            }

            responder($solicitudes);

        //==================================================
        // OBTENER DETALLE
        //==================================================

        case "detalle":

            $id = filter_input(
                INPUT_GET,
                "id",
                FILTER_VALIDATE_INT
            );

            if (!$id) {

                responder([
                    "success" => false,
                    "error" => "El ID de la solicitud no es válido."
                ], 400);
            }

            $sql = "
                SELECT
                    s.Id_solicitud,
                    s.Id_usuario,
                    s.Nombre,
                    s.Apellido,
                    s.DPI,
                    s.Fecha_nacimiento,
                    s.Genero,
                    s.Altura_cm,
                    s.Peso_kg,
                    s.Telefono,
                    s.Correo,
                    s.Direccion,
                    s.Id_nivel,

                    CONCAT(
                        'Nivel ',
                        s.Id_nivel
                    ) AS Nivel,

                    s.Id_tipo_licencia,

                    CONCAT(
                        'Tipo ',
                        s.Id_tipo_licencia
                    ) AS TipoLicencia,

                    s.Nombre_emergencia,
                    s.Contacto_emergencia,
                    s.Condiciones_medicas,
                    s.Acepta_terminos,
                    s.Fecha_solicitud,
                    s.Id_estado,

                    COALESCE(
                        e.Nombre,
                        CONCAT(
                            'Estado ',
                            s.Id_estado
                        )
                    ) AS Estado,

                    s.Edad

                FROM Solicitudes_Escuela AS s

                LEFT JOIN Estados AS e
                    ON s.Id_estado = e.Id_estado

                WHERE s.Id_solicitud = ?
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {

                responder([
                    "success" => false,
                    "error" => "Error al preparar la consulta: " .
                               $conn->error
                ], 500);
            }

            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {

                $error = $stmt->error;

                $stmt->close();

                responder([
                    "success" => false,
                    "error" => "Error al consultar la solicitud: " .
                               $error
                ], 500);
            }

            $resultado = $stmt->get_result();

            if ($resultado->num_rows === 0) {

                $stmt->close();

                responder([
                    "success" => false,
                    "error" => "La solicitud no existe."
                ], 404);
            }

            $solicitud = $resultado->fetch_assoc();

            $stmt->close();

            responder([
                "success" => true,
                "solicitud" => $solicitud
            ]);

        //==================================================
        // LISTAR ESTADOS
        //==================================================

        case "estados":

            $resultado = $conn->query("
                SELECT
                    Id_estado,
                    Nombre
                FROM Estados
                ORDER BY Id_estado ASC
            ");

            if (!$resultado) {

                responder([
                    "success" => false,
                    "error" => "Error al listar estados: " .
                               $conn->error
                ], 500);
            }

            $estados = [];

            while ($fila = $resultado->fetch_assoc()) {

                $fila["Id_estado"] =
                    (int)$fila["Id_estado"];

                $estados[] = $fila;
            }

            responder($estados);

        //==================================================
        // ACTUALIZAR ESTADO
        //==================================================

        case "actualizarEstado":

            if ($_SERVER["REQUEST_METHOD"] !== "POST") {

                responder([
                    "success" => false,
                    "error" => "Método no permitido."
                ], 405);
            }

            $idSolicitud = filter_input(
                INPUT_POST,
                "id",
                FILTER_VALIDATE_INT
            );

            $idEstado = filter_input(
                INPUT_POST,
                "id_estado",
                FILTER_VALIDATE_INT
            );

            $observacion = trim(
                $_POST["observacion"] ?? ""
            );

            if (!$idSolicitud || !$idEstado) {

                responder([
                    "success" => false,
                    "error" => "La solicitud y el estado son obligatorios."
                ], 400);
            }

            // Verificar que la solicitud exista

            $verificarSolicitud = $conn->prepare("
                SELECT Id_solicitud
                FROM Solicitudes_Escuela
                WHERE Id_solicitud = ?
                LIMIT 1
            ");

            if (!$verificarSolicitud) {

                responder([
                    "success" => false,
                    "error" => "Error al verificar la solicitud: " .
                               $conn->error
                ], 500);
            }

            $verificarSolicitud->bind_param(
                "i",
                $idSolicitud
            );

            $verificarSolicitud->execute();

            $resultadoSolicitud =
                $verificarSolicitud->get_result();

            $solicitudExiste =
                $resultadoSolicitud->num_rows > 0;

            $verificarSolicitud->close();

            if (!$solicitudExiste) {

                responder([
                    "success" => false,
                    "error" => "La solicitud no existe."
                ], 404);
            }

            // Verificar que el estado exista

            $verificarEstado = $conn->prepare("
                SELECT Id_estado
                FROM Estados
                WHERE Id_estado = ?
                LIMIT 1
            ");

            if (!$verificarEstado) {

                responder([
                    "success" => false,
                    "error" => "Error al verificar el estado: " .
                               $conn->error
                ], 500);
            }

            $verificarEstado->bind_param(
                "i",
                $idEstado
            );

            $verificarEstado->execute();

            $resultadoEstado =
                $verificarEstado->get_result();

            $estadoExiste =
                $resultadoEstado->num_rows > 0;

            $verificarEstado->close();

            if (!$estadoExiste) {

                responder([
                    "success" => false,
                    "error" => "El estado seleccionado no existe."
                ], 404);
            }

            // Actualizar solicitud

            $stmt = $conn->prepare("
                UPDATE Solicitudes_Escuela
                SET Id_estado = ?
                WHERE Id_solicitud = ?
            ");

            if (!$stmt) {

                responder([
                    "success" => false,
                    "error" => "Error al preparar la actualización: " .
                               $conn->error
                ], 500);
            }

            $stmt->bind_param(
                "ii",
                $idEstado,
                $idSolicitud
            );

            if (!$stmt->execute()) {

                $error = $stmt->error;

                $stmt->close();

                responder([
                    "success" => false,
                    "error" => "No se pudo actualizar el estado: " .
                               $error
                ], 500);
            }

            $stmt->close();

            /*
             * La observación se recibe correctamente,
             * pero todavía no se guarda porque en el código
             * proporcionado no aparecen los campos exactos de
             * la tabla Historial_Escuela.
             */

            responder([
                "success" => true,
                "mensaje" => "Estado actualizado correctamente."
            ]);

        //==================================================
        // HISTORIAL
        //==================================================

        case "historial":

            $id = filter_input(
                INPUT_GET,
                "id",
                FILTER_VALIDATE_INT
            );

            if (!$id) {

                responder([
                    "success" => false,
                    "error" => "El ID de la solicitud no es válido."
                ], 400);
            }

            responder([
                "success" => true,
                "historial" => []
            ]);

        //==================================================
        // ELIMINAR SOLICITUD
        //==================================================

        case "eliminar":

            $id = filter_input(
                INPUT_GET,
                "id",
                FILTER_VALIDATE_INT
            );

            if (!$id) {

                responder([
                    "success" => false,
                    "error" => "El ID de la solicitud no es válido."
                ], 400);
            }

            $stmt = $conn->prepare("
                DELETE FROM Solicitudes_Escuela
                WHERE Id_solicitud = ?
            ");

            if (!$stmt) {

                responder([
                    "success" => false,
                    "error" => "Error al preparar la eliminación: " .
                               $conn->error
                ], 500);
            }

            $stmt->bind_param(
                "i",
                $id
            );

            if (!$stmt->execute()) {

                $error = $stmt->error;

                $stmt->close();

                responder([
                    "success" => false,
                    "error" => "No se pudo eliminar la solicitud: " .
                               $error
                ], 500);
            }

            if ($stmt->affected_rows === 0) {

                $stmt->close();

                responder([
                    "success" => false,
                    "error" => "La solicitud no existe o ya fue eliminada."
                ], 404);
            }

            $stmt->close();

            responder([
                "success" => true,
                "mensaje" => "Solicitud eliminada correctamente."
            ]);

        //==================================================
        // ACCIÓN NO VÁLIDA
        //==================================================

        default:

            responder([
                "success" => false,
                "error" => "Acción no válida."
            ], 400);
    }

} catch (Throwable $error) {

    responder([
        "success" => false,
        "error" => "Error del sistema: " .
                   $error->getMessage()
    ], 500);

} finally {

    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}