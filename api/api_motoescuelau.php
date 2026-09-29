<?php

//==================================================
// CONFIGURACIÓN GENERAL
//==================================================

header(
    "Content-Type: application/json; charset=utf-8"
);

ini_set("display_errors", 0);
error_reporting(E_ALL);

session_start();

require_once "../config/conexion.php";


//==================================================
// RESPUESTA JSON
//==================================================

function responderJSON(
    bool $success,
    string $message = "",
    array $datos = [],
    int $codigoHTTP = 200
): void {

    http_response_code($codigoHTTP);

    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "message" => $message
            ],
            $datos
        ),
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;

}


//==================================================
// VALIDAR CONEXIÓN
//==================================================

if (
    !isset($conn) ||
    !($conn instanceof mysqli)
) {

    responderJSON(
        false,
        "No se pudo establecer la conexión con la base de datos.",
        [],
        500
    );

}

$conn->set_charset("utf8mb4");


//==================================================
// VALIDAR SESIÓN
//==================================================

if (
    !isset($_SESSION["id_usuario"]) ||
    !is_numeric($_SESSION["id_usuario"])
) {

    responderJSON(
        false,
        "La sesión del usuario no es válida.",
        [],
        401
    );

}

$idUsuario =
    (int) $_SESSION["id_usuario"];

$action =
    $_GET["action"] ?? "";


//==================================================
// ACCIONES
//==================================================

switch ($action) {

    case "obtener_progreso":

        obtenerProgreso(
            $conn,
            $idUsuario
        );

        break;


    default:

        responderJSON(
            false,
            "La acción solicitada no existe.",
            [],
            400
        );

}


//==================================================
// OBTENER PROGRESO COMPLETO
//==================================================

function obtenerProgreso(
    mysqli $conn,
    int $idUsuario
): void {

    /*
        Se obtiene la solicitud más reciente
        relacionada con el usuario conectado.

        Solicitudes_Escuela.Id_usuario
        identifica al cliente o estudiante.
    */

    $sqlSolicitud = "
        SELECT
            se.Id_solicitud,
            se.Id_usuario,
            se.DPI,
            se.Fecha_nacimiento,
            se.Genero,
            se.Altura_cm,
            se.Peso_kg,
            se.Telefono,
            se.Direccion,
            se.Fecha_actualizacion,
            se.Id_estado_motoescuela,

            u.Nombre,
            u.Apellido,
            u.Correo,

            em.Nombre AS Estado_actual,
            em.Descripcion AS Descripcion_estado,
            em.Porcentaje_progreso,
            em.Orden_estado

        FROM Solicitudes_Escuela AS se

        INNER JOIN Usuarios AS u
            ON u.Id_usuario = se.Id_usuario

        LEFT JOIN Estados_motoescuela AS em
            ON em.Id_estado_motoescuela =
               se.Id_estado_motoescuela

        WHERE se.Id_usuario = ?

        ORDER BY
            se.Id_solicitud DESC

        LIMIT 1
    ";

    $stmtSolicitud =
        $conn->prepare(
            $sqlSolicitud
        );

    if (!$stmtSolicitud) {

        responderJSON(
            false,
            "No se pudo preparar la consulta de la solicitud.",
            [
                "error_sql" =>
                    $conn->error
            ],
            500
        );

    }

    $stmtSolicitud->bind_param(
        "i",
        $idUsuario
    );

    if (
        !$stmtSolicitud->execute()
    ) {

        responderJSON(
            false,
            "No se pudo consultar la solicitud del usuario.",
            [
                "error_sql" =>
                    $stmtSolicitud->error
            ],
            500
        );

    }

    $resultadoSolicitud =
        $stmtSolicitud->get_result();

    $solicitud =
        $resultadoSolicitud->fetch_assoc();

    $stmtSolicitud->close();


    //==================================================
    // USUARIO SIN SOLICITUD
    //==================================================

    if (!$solicitud) {

        responderJSON(
            true,
            "El usuario no tiene una solicitud de Moto Escuela.",
            [
                "solicitud" => null,
                "historial" => []
            ]
        );

    }


    //==================================================
    // NORMALIZAR DATOS DE SOLICITUD
    //==================================================

    $solicitud["Id_solicitud"] =
        (int) $solicitud["Id_solicitud"];

    $solicitud["Id_usuario"] =
        (int) $solicitud["Id_usuario"];

    $solicitud["Id_estado_motoescuela"] =
        $solicitud["Id_estado_motoescuela"] !== null
            ? (int) $solicitud["Id_estado_motoescuela"]
            : null;

    $solicitud["Porcentaje_progreso"] =
        $solicitud["Porcentaje_progreso"] !== null
            ? (int) $solicitud["Porcentaje_progreso"]
            : 0;

    $solicitud["Orden_estado"] =
        $solicitud["Orden_estado"] !== null
            ? (int) $solicitud["Orden_estado"]
            : 0;

    $solicitud["Estado_actual"] =
        $solicitud["Estado_actual"] ??
        "Solicitud recibida";

    $solicitud["Descripcion_estado"] =
        $solicitud["Descripcion_estado"] ??
        "Tu solicitud de Moto Escuela se encuentra registrada.";


    //==================================================
    // CONSULTAR HISTORIAL
    //==================================================

    $idSolicitud =
        (int) $solicitud["Id_solicitud"];

    /*
        Historial_escuela.Id_usuario
        también identifica al cliente.

        Estado_nuevo contiene el nombre del estado.
        Se relaciona con Estados_motoescuela.Nombre
        para obtener el porcentaje de cada etapa.
    */

    $sqlHistorial = "
        SELECT
            he.Id_historial,
            he.Id_solicitud,
            he.Id_usuario,
            he.Estado_anterior,
            he.Estado_nuevo,
            he.Comentario,
            he.Fecha,

            COALESCE(
                em.Porcentaje_progreso,
                0
            ) AS Porcentaje_progreso,

            em.Descripcion AS Descripcion_estado,
            em.Orden_estado

        FROM Historial_escuela AS he

        LEFT JOIN Estados_motoescuela AS em
            ON em.Nombre = he.Estado_nuevo

        WHERE
            he.Id_usuario = ?
            AND he.Id_solicitud = ?

        ORDER BY
            he.Fecha DESC,
            he.Id_historial DESC
    ";

    $stmtHistorial =
        $conn->prepare(
            $sqlHistorial
        );

    if (!$stmtHistorial) {

        responderJSON(
            false,
            "No se pudo preparar la consulta del historial.",
            [
                "error_sql" =>
                    $conn->error
            ],
            500
        );

    }

    $stmtHistorial->bind_param(
        "ii",
        $idUsuario,
        $idSolicitud
    );

    if (
        !$stmtHistorial->execute()
    ) {

        responderJSON(
            false,
            "No se pudo consultar el historial de Moto Escuela.",
            [
                "error_sql" =>
                    $stmtHistorial->error
            ],
            500
        );

    }

    $resultadoHistorial =
        $stmtHistorial->get_result();

    $historial = [];

    while (
        $fila =
            $resultadoHistorial->fetch_assoc()
    ) {

        $fila["Id_historial"] =
            (int) $fila["Id_historial"];

        $fila["Id_solicitud"] =
            (int) $fila["Id_solicitud"];

        $fila["Id_usuario"] =
            (int) $fila["Id_usuario"];

        $fila["Porcentaje_progreso"] =
            $fila["Porcentaje_progreso"] !== null
                ? (int) $fila["Porcentaje_progreso"]
                : 0;

        $fila["Orden_estado"] =
            $fila["Orden_estado"] !== null
                ? (int) $fila["Orden_estado"]
                : 0;

        $historial[] =
            $fila;

    }

    $stmtHistorial->close();


    //==================================================
    // FECHA DE ACTUALIZACIÓN DE RESPALDO
    //==================================================

    if (
        empty(
            $solicitud["Fecha_actualizacion"]
        ) &&
        !empty($historial)
    ) {

        $solicitud["Fecha_actualizacion"] =
            $historial[0]["Fecha"] ??
            null;

    }


    //==================================================
    // RESPUESTA FINAL
    //==================================================

    responderJSON(
        true,
        "Información de Moto Escuela obtenida correctamente.",
        [
            "solicitud" =>
                $solicitud,

            "historial" =>
                $historial
        ]
    );

}