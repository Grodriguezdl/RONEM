<?php

declare(strict_types=1);

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../config/conexion.php";

//==================================================
// RESPUESTAS JSON
//==================================================

function responderJSON(
    bool $success,
    string $mensaje = "",
    mixed $data = null,
    int $codigoHTTP = 200
): never {

    http_response_code($codigoHTTP);

    $respuesta = [
        "success" => $success
    ];

    if ($mensaje !== "") {
        $respuesta["mensaje"] = $mensaje;
    }

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

//==================================================
// VALIDAR ENTERO
//==================================================

function obtenerEntero(
    mixed $valor,
    string $campo
): int {

    $numero = filter_var(
        $valor,
        FILTER_VALIDATE_INT
    );

    if ($numero === false || $numero <= 0) {

        responderJSON(
            false,
            "El campo {$campo} no es válido.",
            null,
            422
        );
    }

    return (int) $numero;
}

//==================================================
// OBTENER USUARIO DE SESIÓN
//==================================================

function obtenerUsuarioSesion(): int {

    $posiblesVariables = [
        $_SESSION["Id_usuario"] ?? null,
        $_SESSION["id_usuario"] ?? null,
        $_SESSION["usuario_id"] ?? null,
        $_SESSION["user_id"] ?? null
    ];

    foreach ($posiblesVariables as $valor) {

        if (
            $valor !== null &&
            filter_var(
                $valor,
                FILTER_VALIDATE_INT
            ) !== false &&
            (int) $valor > 0
        ) {
            return (int) $valor;
        }
    }

    /*
     * Temporalmente se permite recibir Id_usuario por POST.
     * Cuando tengas definido el login del administrador,
     * puedes eliminar este respaldo.
     */

    $usuarioPOST =
        $_POST["id_usuario"] ??
        $_POST["Id_usuario"] ??
        null;

    if (
        $usuarioPOST !== null &&
        filter_var(
            $usuarioPOST,
            FILTER_VALIDATE_INT
        ) !== false &&
        (int) $usuarioPOST > 0
    ) {
        return (int) $usuarioPOST;
    }

    responderJSON(
        false,
        "No se encontró el usuario autenticado.",
        null,
        401
    );
}

//==================================================
// VALIDAR MÉTODO
//==================================================

function validarMetodo(string $metodo): void {

    if ($_SERVER["REQUEST_METHOD"] !== $metodo) {

        responderJSON(
            false,
            "Método no permitido.",
            null,
            405
        );
    }
}

//==================================================
// ACCIÓN
//==================================================

$action = $_GET["action"] ?? "";

try {

    switch ($action) {

        //==================================================
        // LISTAR SOLICITUDES
        //==================================================

        case "listar":

            validarMetodo("GET");

            $sql = "
                SELECT
                    se.Id_solicitud,
                    se.Id_usuario,
                    se.Id_estado_motoescuela,
                    se.Nombre,
                    se.Apellido,
                    se.DPI,
                    se.Fecha_nacimiento,
                    se.Genero,
                    se.Altura_cm,
                    se.Peso_kg,
                    se.Telefono,
                    se.Correo,
                    se.Direccion,
                    se.Id_nivel,
                    se.Id_tipo_licencia,
                    se.Nombre_emergencia,
                    se.Contacto_emergencia,
                    se.Condiciones_medicas,
                    se.Acepta_terminos,
                    se.Fecha_solicitud,
                    se.Id_estado,
                    se.Edad,
                    se.Fecha_actualizacion,

                    n.Nombre AS Nivel,

                    tl.Nombre AS Tipo_licencia,

                    em.Nombre AS Estado_motoescuela,
                    em.Descripcion AS Descripcion_estado,
                    em.Porcentaje_progreso,
                    em.Orden_estado,

                    u.Nombre AS Nombre_usuario,
                    u.Apellido AS Apellido_usuario,
                    u.Correo AS Correo_usuario

                FROM Solicitudes_Escuela se

                LEFT JOIN Niveles n
                    ON n.Id_nivel = se.Id_nivel

                LEFT JOIN Tipos_licencia tl
                    ON tl.Id_tipo_licencia =
                       se.Id_tipo_licencia

                LEFT JOIN Estados_motoescuela em
                    ON em.Id_estado_motoescuela =
                       se.Id_estado_motoescuela

                LEFT JOIN Usuarios u
                    ON u.Id_usuario =
                       se.Id_usuario

                ORDER BY
                    se.Fecha_solicitud DESC,
                    se.Id_solicitud DESC
            ";

            $resultado = $conn->query($sql);

            if (!$resultado) {

                throw new Exception(
                    "No se pudieron consultar las solicitudes: " .
                    $conn->error
                );
            }

            $solicitudes = [];

            while ($fila = $resultado->fetch_assoc()) {

                $fila["Id_solicitud"] =
                    (int) $fila["Id_solicitud"];

                $fila["Id_usuario"] =
                    (int) $fila["Id_usuario"];

                $fila["Id_estado_motoescuela"] =
                    $fila["Id_estado_motoescuela"] !== null
                        ? (int) $fila["Id_estado_motoescuela"]
                        : null;

                $fila["Porcentaje_progreso"] =
                    $fila["Porcentaje_progreso"] !== null
                        ? (int) $fila["Porcentaje_progreso"]
                        : 0;

                $fila["Orden_estado"] =
                    $fila["Orden_estado"] !== null
                        ? (int) $fila["Orden_estado"]
                        : 0;

                $solicitudes[] = $fila;
            }

            responderJSON(
                true,
                "",
                $solicitudes
            );

        //==================================================
        // DETALLE DE SOLICITUD
        //==================================================

        case "detalle":

            validarMetodo("GET");

            $idSolicitud = obtenerEntero(
                $_GET["id"] ??
                $_GET["id_solicitud"] ??
                null,
                "Id_solicitud"
            );

            $sql = "
                SELECT
                    se.*,

                    n.Nombre AS Nivel,

                    tl.Nombre AS Tipo_licencia,

                    em.Nombre AS Estado_motoescuela,
                    em.Descripcion AS Descripcion_estado,
                    em.Porcentaje_progreso,
                    em.Orden_estado,

                    u.Nombre AS Nombre_usuario,
                    u.Apellido AS Apellido_usuario,
                    u.Correo AS Correo_usuario

                FROM Solicitudes_Escuela se

                LEFT JOIN Niveles n
                    ON n.Id_nivel =
                       se.Id_nivel

                LEFT JOIN Tipos_licencia tl
                    ON tl.Id_tipo_licencia =
                       se.Id_tipo_licencia

                LEFT JOIN Estados_motoescuela em
                    ON em.Id_estado_motoescuela =
                       se.Id_estado_motoescuela

                LEFT JOIN Usuarios u
                    ON u.Id_usuario =
                       se.Id_usuario

                WHERE se.Id_solicitud = ?

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
                $idSolicitud
            );

            $stmt->execute();

            $resultado = $stmt->get_result();

            $solicitud = $resultado->fetch_assoc();

            $stmt->close();

            if (!$solicitud) {

                responderJSON(
                    false,
                    "La solicitud no existe.",
                    null,
                    404
                );
            }

            $solicitud["Id_solicitud"] =
                (int) $solicitud["Id_solicitud"];

            $solicitud["Id_usuario"] =
                (int) $solicitud["Id_usuario"];

            $solicitud["Porcentaje_progreso"] =
                $solicitud["Porcentaje_progreso"] !== null
                    ? (int) $solicitud["Porcentaje_progreso"]
                    : 0;

            responderJSON(
                true,
                "",
                $solicitud
            );

        //==================================================
        // LISTAR ESTADOS DE MOTO ESCUELA
        //==================================================

        case "estados":

            validarMetodo("GET");

            $sql = "
                SELECT
                    Id_estado_motoescuela,
                    Nombre,
                    Descripcion,
                    Porcentaje_progreso,
                    Orden_estado,
                    Estado,
                    Fecha_creacion

                FROM Estados_motoescuela

                WHERE Estado = 1

                ORDER BY
                    Orden_estado ASC,
                    Id_estado_motoescuela ASC
            ";

            $resultado = $conn->query($sql);

            if (!$resultado) {

                throw new Exception(
                    "No se pudieron consultar los estados: " .
                    $conn->error
                );
            }

            $estados = [];

            while ($fila = $resultado->fetch_assoc()) {

                $fila["Id_estado_motoescuela"] =
                    (int) $fila["Id_estado_motoescuela"];

                $fila["Porcentaje_progreso"] =
                    (int) $fila["Porcentaje_progreso"];

                $fila["Orden_estado"] =
                    (int) $fila["Orden_estado"];

                $fila["Estado"] =
                    (int) $fila["Estado"];

                $estados[] = $fila;
            }

            responderJSON(
                true,
                "",
                $estados
            );

        //==================================================
        // HISTORIAL DE UNA SOLICITUD
        //==================================================

        case "historial":

            validarMetodo("GET");

            $idSolicitud = obtenerEntero(
                $_GET["id"] ??
                $_GET["id_solicitud"] ??
                null,
                "Id_solicitud"
            );

            $sql = "
                SELECT
                    he.Id_historial,
                    he.Id_solicitud,
                    he.Id_estado_anterior,
                    he.Id_estado_nuevo,
                    he.Comentario,
                    he.Fecha,
                    he.Id_usuario,

                    ea.Nombre AS Estado_anterior,
                    ea.Porcentaje_progreso
                        AS Progreso_anterior,

                    en.Nombre AS Estado_nuevo,
                    en.Descripcion
                        AS Descripcion_estado_nuevo,
                    en.Porcentaje_progreso
                        AS Progreso_nuevo,

                    u.Nombre AS Nombre_usuario,
                    u.Apellido AS Apellido_usuario,
                    u.Correo AS Correo_usuario

                FROM Historial_Escuela he

                LEFT JOIN Estados_motoescuela ea
                    ON ea.Id_estado_motoescuela =
                       he.Id_estado_anterior

                INNER JOIN Estados_motoescuela en
                    ON en.Id_estado_motoescuela =
                       he.Id_estado_nuevo

                INNER JOIN Usuarios u
                    ON u.Id_usuario =
                       he.Id_usuario

                WHERE he.Id_solicitud = ?

                ORDER BY
                    he.Fecha DESC,
                    he.Id_historial DESC
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {

                throw new Exception(
                    "No se pudo preparar el historial: " .
                    $conn->error
                );
            }

            $stmt->bind_param(
                "i",
                $idSolicitud
            );

            $stmt->execute();

            $resultado = $stmt->get_result();

            $historial = [];

            while ($fila = $resultado->fetch_assoc()) {

                $fila["Id_historial"] =
                    (int) $fila["Id_historial"];

                $fila["Id_solicitud"] =
                    (int) $fila["Id_solicitud"];

                $fila["Id_estado_anterior"] =
                    $fila["Id_estado_anterior"] !== null
                        ? (int) $fila["Id_estado_anterior"]
                        : null;

                $fila["Id_estado_nuevo"] =
                    (int) $fila["Id_estado_nuevo"];

                $fila["Id_usuario"] =
                    (int) $fila["Id_usuario"];

                $fila["Progreso_anterior"] =
                    $fila["Progreso_anterior"] !== null
                        ? (int) $fila["Progreso_anterior"]
                        : 0;

                $fila["Progreso_nuevo"] =
                    (int) $fila["Progreso_nuevo"];

                $historial[] = $fila;
            }

            $stmt->close();

            responderJSON(
                true,
                "",
                $historial
            );

        //==================================================
        // ACTUALIZAR ESTADO Y GUARDAR HISTORIAL
        //==================================================

        case "actualizar_estado":

            validarMetodo("POST");

            $idSolicitud = obtenerEntero(
                $_POST["id_solicitud"] ?? null,
                "Id_solicitud"
            );

            $idEstadoNuevo = obtenerEntero(
                $_POST["id_estado_nuevo"] ??
                $_POST["id_estado"] ??
                null,
                "Id_estado_nuevo"
            );

            $comentario = trim(
                (string) (
                    $_POST["comentario"] ??
                    $_POST["observacion"] ??
                    ""
                )
            );

            $idUsuario = obtenerUsuarioSesion();

            if (mb_strlen($comentario) > 5000) {

                responderJSON(
                    false,
                    "El comentario es demasiado largo.",
                    null,
                    422
                );
            }

            $conn->begin_transaction();

            try {

                //==========================================
                // BLOQUEAR Y OBTENER ESTADO ACTUAL
                //==========================================

                $sqlSolicitud = "
                    SELECT
                        Id_solicitud,
                        Id_usuario,
                        Id_estado_motoescuela

                    FROM Solicitudes_Escuela

                    WHERE Id_solicitud = ?

                    LIMIT 1

                    FOR UPDATE
                ";

                $stmtSolicitud = $conn->prepare(
                    $sqlSolicitud
                );

                if (!$stmtSolicitud) {

                    throw new Exception(
                        "No se pudo preparar la solicitud: " .
                        $conn->error
                    );
                }

                $stmtSolicitud->bind_param(
                    "i",
                    $idSolicitud
                );

                $stmtSolicitud->execute();

                $resultadoSolicitud =
                    $stmtSolicitud->get_result();

                $solicitud =
                    $resultadoSolicitud->fetch_assoc();

                $stmtSolicitud->close();

                if (!$solicitud) {

                    throw new Exception(
                        "La solicitud no existe."
                    );
                }

                $idEstadoAnterior =
                    $solicitud["Id_estado_motoescuela"] !== null
                        ? (int) $solicitud["Id_estado_motoescuela"]
                        : null;

                if (
                    $idEstadoAnterior !== null &&
                    $idEstadoAnterior === $idEstadoNuevo
                ) {

                    throw new Exception(
                        "El nuevo estado es igual al estado actual."
                    );
                }

                //==========================================
                // VALIDAR ESTADO NUEVO
                //==========================================

                $sqlEstado = "
                    SELECT
                        Id_estado_motoescuela,
                        Nombre,
                        Porcentaje_progreso,
                        Orden_estado,
                        Estado

                    FROM Estados_motoescuela

                    WHERE Id_estado_motoescuela = ?

                    LIMIT 1
                ";

                $stmtEstado = $conn->prepare(
                    $sqlEstado
                );

                if (!$stmtEstado) {

                    throw new Exception(
                        "No se pudo preparar el estado: " .
                        $conn->error
                    );
                }

                $stmtEstado->bind_param(
                    "i",
                    $idEstadoNuevo
                );

                $stmtEstado->execute();

                $resultadoEstado =
                    $stmtEstado->get_result();

                $estadoNuevo =
                    $resultadoEstado->fetch_assoc();

                $stmtEstado->close();

                if (!$estadoNuevo) {

                    throw new Exception(
                        "El estado seleccionado no existe."
                    );
                }

                if ((int) $estadoNuevo["Estado"] !== 1) {

                    throw new Exception(
                        "El estado seleccionado está inactivo."
                    );
                }

                //==========================================
                // INSERTAR HISTORIAL
                //==========================================

                if ($idEstadoAnterior === null) {

                    $sqlHistorial = "
                        INSERT INTO Historial_Escuela
                        (
                            Id_solicitud,
                            Id_estado_anterior,
                            Id_estado_nuevo,
                            Comentario,
                            Id_usuario
                        )
                        VALUES (?, NULL, ?, ?, ?)
                    ";

                    $stmtHistorial =
                        $conn->prepare($sqlHistorial);

                    if (!$stmtHistorial) {

                        throw new Exception(
                            "No se pudo preparar el historial: " .
                            $conn->error
                        );
                    }

                    $stmtHistorial->bind_param(
                        "iisi",
                        $idSolicitud,
                        $idEstadoNuevo,
                        $comentario,
                        $idUsuario
                    );

                } else {

                    $sqlHistorial = "
                        INSERT INTO Historial_Escuela
                        (
                            Id_solicitud,
                            Id_estado_anterior,
                            Id_estado_nuevo,
                            Comentario,
                            Id_usuario
                        )
                        VALUES (?, ?, ?, ?, ?)
                    ";

                    $stmtHistorial =
                        $conn->prepare($sqlHistorial);

                    if (!$stmtHistorial) {

                        throw new Exception(
                            "No se pudo preparar el historial: " .
                            $conn->error
                        );
                    }

                    $stmtHistorial->bind_param(
                        "iiisi",
                        $idSolicitud,
                        $idEstadoAnterior,
                        $idEstadoNuevo,
                        $comentario,
                        $idUsuario
                    );
                }

                if (!$stmtHistorial->execute()) {

                    throw new Exception(
                        "No se pudo guardar el historial: " .
                        $stmtHistorial->error
                    );
                }

                $idHistorial =
                    $stmtHistorial->insert_id;

                $stmtHistorial->close();

                //==========================================
                // ACTUALIZAR SOLICITUD
                //==========================================

                $sqlActualizar = "
                    UPDATE Solicitudes_Escuela

                    SET
                        Id_estado_motoescuela = ?,
                        Fecha_actualizacion =
                            CURRENT_TIMESTAMP

                    WHERE Id_solicitud = ?
                ";

                $stmtActualizar =
                    $conn->prepare($sqlActualizar);

                if (!$stmtActualizar) {

                    throw new Exception(
                        "No se pudo preparar la actualización: " .
                        $conn->error
                    );
                }

                $stmtActualizar->bind_param(
                    "ii",
                    $idEstadoNuevo,
                    $idSolicitud
                );

                if (!$stmtActualizar->execute()) {

                    throw new Exception(
                        "No se pudo actualizar la solicitud: " .
                        $stmtActualizar->error
                    );
                }

                $stmtActualizar->close();

                $conn->commit();

                responderJSON(
                    true,
                    "Estado actualizado correctamente.",
                    [
                        "Id_historial" =>
                            (int) $idHistorial,

                        "Id_solicitud" =>
                            $idSolicitud,

                        "Id_estado_anterior" =>
                            $idEstadoAnterior,

                        "Id_estado_nuevo" =>
                            $idEstadoNuevo,

                        "Estado_nuevo" =>
                            $estadoNuevo["Nombre"],

                        "Porcentaje_progreso" =>
                            (int) $estadoNuevo[
                                "Porcentaje_progreso"
                            ],

                        "Id_usuario_actualizacion" =>
                            $idUsuario
                    ]
                );

            } catch (Throwable $error) {

                $conn->rollback();

                throw $error;
            }

        //==================================================
        // PROGRESO ACTUAL
        //==================================================

        case "progreso":

            validarMetodo("GET");

            $idSolicitud = obtenerEntero(
                $_GET["id"] ??
                $_GET["id_solicitud"] ??
                null,
                "Id_solicitud"
            );

            $sql = "
                SELECT
                    se.Id_solicitud,
                    se.Id_usuario,
                    se.Id_estado_motoescuela,

                    em.Nombre AS Estado_actual,
                    em.Descripcion,
                    em.Porcentaje_progreso,
                    em.Orden_estado

                FROM Solicitudes_Escuela se

                LEFT JOIN Estados_motoescuela em
                    ON em.Id_estado_motoescuela =
                       se.Id_estado_motoescuela

                WHERE se.Id_solicitud = ?

                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {

                throw new Exception(
                    "No se pudo preparar el progreso: " .
                    $conn->error
                );
            }

            $stmt->bind_param(
                "i",
                $idSolicitud
            );

            $stmt->execute();

            $resultado = $stmt->get_result();

            $progreso = $resultado->fetch_assoc();

            $stmt->close();

            if (!$progreso) {

                responderJSON(
                    false,
                    "La solicitud no existe.",
                    null,
                    404
                );
            }

            $progreso["Porcentaje_progreso"] =
                $progreso["Porcentaje_progreso"] !== null
                    ? (int) $progreso["Porcentaje_progreso"]
                    : 0;

            responderJSON(
                true,
                "",
                $progreso
            );

        //==================================================
        // ACCIÓN NO VÁLIDA
        //==================================================

        default:

            responderJSON(
                false,
                "Acción no válida.",
                null,
                400
            );
    }

} catch (Throwable $error) {

    responderJSON(
        false,
        $error->getMessage(),
        null,
        500
    );
}