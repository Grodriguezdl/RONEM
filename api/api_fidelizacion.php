<?php

//==================================================
// CONFIGURACIÓN INICIAL
//==================================================

session_start();

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");

require_once __DIR__ . "/../config/conexion.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn->set_charset("utf8mb4");


//==================================================
// RESPUESTAS JSON
//==================================================

function responderJSON(
    bool $success,
    array $datos = [],
    int $codigoHTTP = 200
): void {

    http_response_code($codigoHTTP);

    echo json_encode(
        array_merge(
            [
                "success" => $success
            ],
            $datos
        ),
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;

}


function responderError(
    string $mensaje,
    int $codigoHTTP = 400
): void {

    responderJSON(
        false,
        [
            "error" => $mensaje,
            "message" => $mensaje
        ],
        $codigoHTTP
    );

}


//==================================================
// VALIDAR SESIÓN
//==================================================

if (
    !isset($_SESSION["id_usuario"]) ||
    !is_numeric($_SESSION["id_usuario"])
) {

    responderError(
        "Tu sesión ha expirado. Inicia sesión nuevamente.",
        401
    );

}

$idUsuario = (int) $_SESSION["id_usuario"];

if ($idUsuario <= 0) {

    responderError(
        "El usuario de la sesión no es válido.",
        401
    );

}


//==================================================
// LEER ACCIÓN
//==================================================

$action = trim(
    $_GET["action"] ?? ""
);

if ($action === "") {

    responderError(
        "No se indicó ninguna acción.",
        400
    );

}


//==================================================
// FUNCIONES AUXILIARES
//==================================================

function obtenerDatosJSON(): array {

    $contenido = file_get_contents(
        "php://input"
    );

    if (
        $contenido === false ||
        trim($contenido) === ""
    ) {

        return $_POST;

    }

    $datos = json_decode(
        $contenido,
        true
    );

    if (
        json_last_error() !== JSON_ERROR_NONE
    ) {

        responderError(
            "Los datos enviados no contienen un JSON válido.",
            400
        );

    }

    return is_array($datos)
        ? $datos
        : [];

}


function crearCuentaFidelizacion(
    mysqli $conn,
    int $idUsuario
): void {

    $sql = "
        INSERT INTO Fidelizacion
        (
            Id_usuario,
            Puntos_disponibles,
            Total_puntos_ganados,
            Total_puntos_usados
        )
        VALUES
        (
            ?,
            0,
            0,
            0
        )
        ON DUPLICATE KEY UPDATE
            Id_usuario = VALUES(Id_usuario)
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $idUsuario
    );

    $stmt->execute();
    $stmt->close();

}


function generarCodigoCanje(
    mysqli $conn
): string {

    $intentos = 0;

    do {

        $codigo =
            "RONEM-" .
            strtoupper(
                bin2hex(
                    random_bytes(4)
                )
            );

        $sql = "
            SELECT Id_canje
            FROM Canjes
            WHERE Codigo_canje = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "s",
            $codigo
        );

        $stmt->execute();

        $resultado =
            $stmt->get_result();

        $existe =
            $resultado->num_rows > 0;

        $stmt->close();

        $intentos++;

    } while (
        $existe &&
        $intentos < 10
    );

    if ($existe) {

        throw new Exception(
            "No se pudo generar un código de canje único."
        );

    }

    return $codigo;

}


//==================================================
// EJECUTAR ACCIÓN
//==================================================

try {

    switch ($action) {

        //==========================================
        // RESUMEN DE PUNTOS
        //==========================================

        case "resumen_puntos":

            crearCuentaFidelizacion(
                $conn,
                $idUsuario
            );

            $sql = "
                SELECT
                    Id_fidelizacion,
                    Id_usuario,
                    Puntos_disponibles,
                    Total_puntos_ganados,
                    Total_puntos_usados,
                    Fecha_actualizacion
                FROM Fidelizacion
                WHERE Id_usuario = ?
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "i",
                $idUsuario
            );

            $stmt->execute();

            $resultado =
                $stmt->get_result();

            $fila =
                $resultado->fetch_assoc();

            $stmt->close();

            if (!$fila) {

                responderError(
                    "No se pudo obtener la cuenta de fidelización.",
                    500
                );

            }

            responderJSON(
                true,
                [
                    "saldo" =>
                        (int) $fila["Puntos_disponibles"],

                    "total_ganado" =>
                        (int) $fila["Total_puntos_ganados"],

                    "total_usado" =>
                        (int) $fila["Total_puntos_usados"],

                    "fecha_actualizacion" =>
                        $fila["Fecha_actualizacion"]
                ]
            );

            break;


        //==========================================
        // LISTAR MOVIMIENTOS
        //==========================================

        case "listar_historial":

            crearCuentaFidelizacion(
                $conn,
                $idUsuario
            );

            $sql = "
                SELECT
                    Id_movimiento,
                    Id_venta,
                    Tipo,
                    Puntos,
                    Motivo,
                    DATE_FORMAT(
                        Fecha,
                        '%Y-%m-%d %H:%i:%s'
                    ) AS Fecha
                FROM Movimientos_puntos
                WHERE Id_usuario = ?
                ORDER BY
                    Fecha DESC,
                    Id_movimiento DESC
            ";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "i",
                $idUsuario
            );

            $stmt->execute();

            $resultado =
                $stmt->get_result();

            $movimientos = [];

            while (
                $fila = $resultado->fetch_assoc()
            ) {

                $fila["Id_movimiento"] =
                    (int) $fila["Id_movimiento"];

                $fila["Id_venta"] =
                    $fila["Id_venta"] !== null
                        ? (int) $fila["Id_venta"]
                        : null;

                $fila["Puntos"] =
                    (int) $fila["Puntos"];

                $movimientos[] = $fila;

            }

            $stmt->close();

            responderJSON(
                true,
                [
                    "historial" => $movimientos,
                    "movimientos" => $movimientos
                ]
            );

            break;


        //==========================================
        // LISTAR RECOMPENSAS
        //==========================================

        case "listar_recompensas":

            crearCuentaFidelizacion(
                $conn,
                $idUsuario
            );

            $sqlSaldo = "
                SELECT Puntos_disponibles
                FROM Fidelizacion
                WHERE Id_usuario = ?
                LIMIT 1
            ";

            $stmtSaldo =
                $conn->prepare($sqlSaldo);

            $stmtSaldo->bind_param(
                "i",
                $idUsuario
            );

            $stmtSaldo->execute();

            $resultadoSaldo =
                $stmtSaldo->get_result();

            $filaSaldo =
                $resultadoSaldo->fetch_assoc();

            $saldo =
                (int) (
                    $filaSaldo["Puntos_disponibles"] ??
                    0
                );

            $stmtSaldo->close();

            $sql = "
                SELECT
                    Id_recompensa,
                    Nombre,
                    Descripcion,
                    Tipo,
                    Puntos_requeridos,
                    Porcentaje_descuento,
                    Nombre_servicio,
                    Imagen_URL,
                    Stock,
                    Estado,
                    Fecha_creacion
                FROM Recompensas
                WHERE Estado = 1
                ORDER BY
                    Puntos_requeridos ASC,
                    Id_recompensa DESC
            ";

            $resultado =
                $conn->query($sql);

            $recompensas = [];

            while (
                $fila = $resultado->fetch_assoc()
            ) {

                $fila["Id_recompensa"] =
                    (int) $fila["Id_recompensa"];

                $fila["Puntos_requeridos"] =
                    (int) $fila["Puntos_requeridos"];

                $fila["Porcentaje_descuento"] =
                    $fila["Porcentaje_descuento"] !== null
                        ? (float) $fila["Porcentaje_descuento"]
                        : null;

                $fila["Stock"] =
                    $fila["Stock"] !== null
                        ? (int) $fila["Stock"]
                        : null;

                $fila["Estado"] =
                    (int) $fila["Estado"];

                $fila["Puede_canjear"] =
                    (
                        $saldo >=
                        $fila["Puntos_requeridos"]
                    ) &&
                    (
                        $fila["Stock"] === null ||
                        $fila["Stock"] > 0
                    );

                $recompensas[] = $fila;

            }

            responderJSON(
                true,
                [
                    "saldo" => $saldo,
                    "recompensas" => $recompensas
                ]
            );

            break;


        //==========================================
        // LISTAR CANJES DEL USUARIO
        //==========================================

        case "listar_canjes":

            $sql = "
                SELECT
                    c.Id_canje,
                    c.Id_recompensa,
                    c.Puntos_utilizados,
                    c.Fecha,
                    c.Estado,
                    c.Codigo_canje,
                    c.Fecha_utilizacion,

                    r.Nombre AS Nombre_recompensa,
                    r.Descripcion,
                    r.Tipo,
                    r.Porcentaje_descuento,
                    r.Nombre_servicio

                FROM Canjes AS c

                INNER JOIN Recompensas AS r
                    ON r.Id_recompensa =
                       c.Id_recompensa

                WHERE c.Id_usuario = ?

                ORDER BY
                    c.Fecha DESC,
                    c.Id_canje DESC
            ";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "i",
                $idUsuario
            );

            $stmt->execute();

            $resultado =
                $stmt->get_result();

            $canjes = [];

            while (
                $fila = $resultado->fetch_assoc()
            ) {

                $fila["Id_canje"] =
                    (int) $fila["Id_canje"];

                $fila["Id_recompensa"] =
                    (int) $fila["Id_recompensa"];

                $fila["Puntos_utilizados"] =
                    (int) $fila["Puntos_utilizados"];

                $fila["Porcentaje_descuento"] =
                    $fila["Porcentaje_descuento"] !== null
                        ? (float) $fila["Porcentaje_descuento"]
                        : null;

                $canjes[] = $fila;

            }

            $stmt->close();

            responderJSON(
                true,
                [
                    "canjes" => $canjes
                ]
            );

            break;


        //==========================================
        // CANJEAR RECOMPENSA
        //==========================================

        case "canjear":

            if (
                strtoupper(
                    $_SERVER["REQUEST_METHOD"]
                ) !== "POST"
            ) {

                responderError(
                    "Esta acción solamente acepta solicitudes POST.",
                    405
                );

            }

            $datos =
                obtenerDatosJSON();

            $idRecompensa =
                filter_var(
                    $datos["id_recompensa"] ?? null,
                    FILTER_VALIDATE_INT
                );

            if (
                $idRecompensa === false ||
                $idRecompensa <= 0
            ) {

                responderError(
                    "La recompensa seleccionada no es válida.",
                    422
                );

            }

            $conn->begin_transaction();

            try {

                crearCuentaFidelizacion(
                    $conn,
                    $idUsuario
                );

                //----------------------------------
                // BLOQUEAR CUENTA DEL CLIENTE
                //----------------------------------

                $sqlCuenta = "
                    SELECT
                        Id_fidelizacion,
                        Puntos_disponibles,
                        Total_puntos_usados
                    FROM Fidelizacion
                    WHERE Id_usuario = ?
                    LIMIT 1
                    FOR UPDATE
                ";

                $stmtCuenta =
                    $conn->prepare($sqlCuenta);

                $stmtCuenta->bind_param(
                    "i",
                    $idUsuario
                );

                $stmtCuenta->execute();

                $resultadoCuenta =
                    $stmtCuenta->get_result();

                $cuenta =
                    $resultadoCuenta->fetch_assoc();

                $stmtCuenta->close();

                if (!$cuenta) {

                    throw new Exception(
                        "No se encontró la cuenta de fidelización."
                    );

                }

                //----------------------------------
                // BLOQUEAR RECOMPENSA
                //----------------------------------

                $sqlRecompensa = "
                    SELECT
                        Id_recompensa,
                        Nombre,
                        Descripcion,
                        Tipo,
                        Puntos_requeridos,
                        Porcentaje_descuento,
                        Nombre_servicio,
                        Stock,
                        Estado
                    FROM Recompensas
                    WHERE Id_recompensa = ?
                    LIMIT 1
                    FOR UPDATE
                ";

                $stmtRecompensa =
                    $conn->prepare(
                        $sqlRecompensa
                    );

                $stmtRecompensa->bind_param(
                    "i",
                    $idRecompensa
                );

                $stmtRecompensa->execute();

                $resultadoRecompensa =
                    $stmtRecompensa->get_result();

                $recompensa =
                    $resultadoRecompensa->fetch_assoc();

                $stmtRecompensa->close();

                if (!$recompensa) {

                    throw new Exception(
                        "La recompensa seleccionada no existe."
                    );

                }

                if (
                    (int) $recompensa["Estado"] !== 1
                ) {

                    throw new Exception(
                        "La recompensa ya no está disponible."
                    );

                }

                $puntosRequeridos =
                    (int) $recompensa[
                        "Puntos_requeridos"
                    ];

                $saldoActual =
                    (int) $cuenta[
                        "Puntos_disponibles"
                    ];

                if ($puntosRequeridos <= 0) {

                    throw new Exception(
                        "La recompensa tiene una cantidad de puntos inválida."
                    );

                }

                if (
                    $saldoActual <
                    $puntosRequeridos
                ) {

                    throw new Exception(
                        "No tienes suficientes puntos para realizar este canje."
                    );

                }

                $stock =
                    $recompensa["Stock"] !== null
                        ? (int) $recompensa["Stock"]
                        : null;

                if (
                    $stock !== null &&
                    $stock <= 0
                ) {

                    throw new Exception(
                        "La recompensa se encuentra agotada."
                    );

                }

                //----------------------------------
                // GENERAR CÓDIGO
                //----------------------------------

                $codigoCanje =
                    generarCodigoCanje($conn);

                //----------------------------------
                // REGISTRAR CANJE
                //----------------------------------

                $sqlCanje = "
                    INSERT INTO Canjes
                    (
                        Id_usuario,
                        Id_recompensa,
                        Puntos_utilizados,
                        Estado,
                        Codigo_canje
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        'Pendiente',
                        ?
                    )
                ";

                $stmtCanje =
                    $conn->prepare($sqlCanje);

                $stmtCanje->bind_param(
                    "iiis",
                    $idUsuario,
                    $idRecompensa,
                    $puntosRequeridos,
                    $codigoCanje
                );

                $stmtCanje->execute();

                $idCanje =
                    $conn->insert_id;

                $stmtCanje->close();

                //----------------------------------
                // DESCONTAR PUNTOS
                //----------------------------------

                $sqlActualizarCuenta = "
                    UPDATE Fidelizacion
                    SET
                        Puntos_disponibles =
                            Puntos_disponibles - ?,

                        Total_puntos_usados =
                            Total_puntos_usados + ?

                    WHERE Id_usuario = ?
                ";

                $stmtActualizarCuenta =
                    $conn->prepare(
                        $sqlActualizarCuenta
                    );

                $stmtActualizarCuenta->bind_param(
                    "iii",
                    $puntosRequeridos,
                    $puntosRequeridos,
                    $idUsuario
                );

                $stmtActualizarCuenta->execute();
                $stmtActualizarCuenta->close();

                //----------------------------------
                // REGISTRAR MOVIMIENTO
                //----------------------------------

                $puntosMovimiento =
                    $puntosRequeridos * -1;

                $motivo =
                    "Canje de recompensa: " .
                    $recompensa["Nombre"] .
                    " (" .
                    $codigoCanje .
                    ")";

                $sqlMovimiento = "
                    INSERT INTO Movimientos_puntos
                    (
                        Id_usuario,
                        Id_venta,
                        Tipo,
                        Puntos,
                        Motivo
                    )
                    VALUES
                    (
                        ?,
                        NULL,
                        'Canjeado',
                        ?,
                        ?
                    )
                ";

                $stmtMovimiento =
                    $conn->prepare(
                        $sqlMovimiento
                    );

                $stmtMovimiento->bind_param(
                    "iis",
                    $idUsuario,
                    $puntosMovimiento,
                    $motivo
                );

                $stmtMovimiento->execute();
                $stmtMovimiento->close();

                //----------------------------------
                // DESCONTAR STOCK
                //----------------------------------

                if ($stock !== null) {

                    $sqlStock = "
                        UPDATE Recompensas
                        SET Stock = Stock - 1
                        WHERE Id_recompensa = ?
                          AND Stock > 0
                    ";

                    $stmtStock =
                        $conn->prepare($sqlStock);

                    $stmtStock->bind_param(
                        "i",
                        $idRecompensa
                    );

                    $stmtStock->execute();

                    if (
                        $stmtStock->affected_rows !== 1
                    ) {

                        $stmtStock->close();

                        throw new Exception(
                            "No fue posible reservar la recompensa porque ya no tiene existencias."
                        );

                    }

                    $stmtStock->close();

                }

                //----------------------------------
                // CONFIRMAR TRANSACCIÓN
                //----------------------------------

                $conn->commit();

                $nuevoSaldo =
                    $saldoActual -
                    $puntosRequeridos;

                responderJSON(
                    true,
                    [
                        "message" =>
                            "La recompensa fue canjeada correctamente.",

                        "mensaje" =>
                            "La recompensa fue canjeada correctamente.",

                        "canje" => [
                            "Id_canje" =>
                                (int) $idCanje,

                            "Codigo_canje" =>
                                $codigoCanje,

                            "Nombre_recompensa" =>
                                $recompensa["Nombre"],

                            "Tipo" =>
                                $recompensa["Tipo"],

                            "Puntos_utilizados" =>
                                $puntosRequeridos,

                            "Estado" =>
                                "Pendiente",

                            "Saldo_restante" =>
                                $nuevoSaldo
                        ]
                    ],
                    201
                );

            } catch (Throwable $error) {

                $conn->rollback();

                responderError(
                    $error->getMessage(),
                    400
                );

            }

            break;


        //==========================================
        // ACCIÓN DESCONOCIDA
        //==========================================

        default:

            responderError(
                "La acción solicitada no existe.",
                404
            );

    }

} catch (mysqli_sql_exception $error) {

    error_log(
        "Error MySQL en api_fidelizacion.php: " .
        $error->getMessage()
    );

    responderError(
        "Ocurrió un error al consultar la base de datos.",
        500
    );

} catch (Throwable $error) {

    error_log(
        "Error general en api_fidelizacion.php: " .
        $error->getMessage()
    );

    responderError(
        "Ocurrió un error interno en el servidor.",
        500
    );

} finally {

    if (
        isset($conn) &&
        $conn instanceof mysqli
    ) {

        $conn->close();

    }

}