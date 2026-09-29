<?php

declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

require_once "../config/conexion.php";

$action = $_GET["action"] ?? "";


/*==================================================
RESPUESTA JSON
==================================================*/

function responderVehiculos(
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


/*==================================================
CONVERTIR DATOS DEL VEHÍCULO
==================================================*/

function prepararVehiculo(
    array $fila
): array {

    $fila["Id_vehiculo"] =
        isset($fila["Id_vehiculo"])
            ? (int) $fila["Id_vehiculo"]
            : 0;

    $fila["Id_usuario"] =
        isset($fila["Id_usuario"])
            ? (int) $fila["Id_usuario"]
            : 0;

    if (array_key_exists("Id_orden", $fila)) {

        $fila["Id_orden"] =
            $fila["Id_orden"] !== null
                ? (int) $fila["Id_orden"]
                : null;
    }

    $fila["Modelo"] =
        isset($fila["Modelo"]) &&
        $fila["Modelo"] !== null &&
        $fila["Modelo"] !== ""
            ? (int) $fila["Modelo"]
            : null;

    $fila["Cilindraje"] =
        isset($fila["Cilindraje"]) &&
        $fila["Cilindraje"] !== null &&
        $fila["Cilindraje"] !== ""
            ? (int) $fila["Cilindraje"]
            : null;

    $fila["Kilometraje"] =
        isset($fila["Kilometraje"]) &&
        $fila["Kilometraje"] !== null &&
        $fila["Kilometraje"] !== ""
            ? (int) $fila["Kilometraje"]
            : 0;

    /*
     * Estado puede ser entero, texto o NULL,
     * dependiendo de cómo esté definida la tabla.
     */
    if (
        isset($fila["Estado"]) &&
        is_numeric($fila["Estado"])
    ) {

        $fila["Estado"] =
            (int) $fila["Estado"];
    }

    $fila["Propietario"] =
        trim(
            (string) (
                $fila["Propietario"] ?? ""
            )
        );

    return $fila;
}


/*==================================================
CONTROLADOR
==================================================*/

try {

    switch ($action) {

        /*==================================================
        LISTAR VEHÍCULOS RELACIONADOS CON ÓRDENES
        ==================================================*/

        case "listar":

            $sql = "
                SELECT DISTINCT
                    v.Id_vehiculo,
                    v.Id_usuario,
                    v.Marca,
                    v.Linea,
                    v.Modelo,
                    v.Color,
                    v.Placa,
                    v.No_chasis,
                    v.Tipo_vehiculo,
                    v.Cilindraje,
                    v.Combustible,
                    v.Kilometraje,
                    v.Estado,
                    v.Imagen_URL,

                    TRIM(
                        CONCAT(
                            COALESCE(u.Nombre, ''),
                            ' ',
                            COALESCE(u.Apellido, '')
                        )
                    ) AS Propietario,

                    u.Correo,

                    o.Id_orden

                FROM Vehiculos v

                INNER JOIN Ordenes o
                    ON o.Id_vehiculo = v.Id_vehiculo

                LEFT JOIN Usuarios u
                    ON u.Id_usuario = v.Id_usuario

                ORDER BY
                    v.Id_vehiculo DESC
            ";

            $resultado =
                $conn->query($sql);

            if (!$resultado) {

                throw new Exception(
                    "No se pudieron cargar los vehículos: " .
                    $conn->error
                );
            }

            $vehiculos = [];

            while (
                $fila =
                    $resultado->fetch_assoc()
            ) {

                $vehiculos[] =
                    prepararVehiculo(
                        $fila
                    );
            }

            responderVehiculos(
                true,
                "",
                $vehiculos
            );


        /*==================================================
        OBTENER VEHÍCULO
        ==================================================*/

        case "obtener":

            $id =
                isset($_GET["id"])
                    ? (int) $_GET["id"]
                    : (
                        isset($_GET["id_vehiculo"])
                            ? (int) $_GET["id_vehiculo"]
                            : 0
                    );

            if ($id <= 0) {

                responderVehiculos(
                    false,
                    "El ID del vehículo no es válido.",
                    null,
                    422
                );
            }

            $sql = "
                SELECT
                    v.Id_vehiculo,
                    v.Id_usuario,
                    v.Marca,
                    v.Linea,
                    v.Modelo,
                    v.Color,
                    v.Placa,
                    v.No_chasis,
                    v.Tipo_vehiculo,
                    v.Cilindraje,
                    v.Combustible,
                    v.Kilometraje,
                    v.Estado,
                    v.Imagen_URL,

                    TRIM(
                        CONCAT(
                            COALESCE(u.Nombre, ''),
                            ' ',
                            COALESCE(u.Apellido, '')
                        )
                    ) AS Propietario,

                    u.Correo

                FROM Vehiculos v

                LEFT JOIN Usuarios u
                    ON u.Id_usuario = v.Id_usuario

                WHERE v.Id_vehiculo = ?

                LIMIT 1
            ";

            $stmt =
                $conn->prepare($sql);

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
                    "No se pudo consultar el vehículo: " .
                    $stmt->error
                );
            }

            $resultado =
                $stmt->get_result();

            if (
                $resultado->num_rows === 0
            ) {

                $stmt->close();

                responderVehiculos(
                    false,
                    "El vehículo no existe.",
                    null,
                    404
                );
            }

            $vehiculo =
                $resultado->fetch_assoc();

            $stmt->close();

            $vehiculo =
                prepararVehiculo(
                    $vehiculo
                );

            responderVehiculos(
                true,
                "",
                $vehiculo
            );


        /*==================================================
        ACCIÓN NO VÁLIDA
        ==================================================*/

        default:

            responderVehiculos(
                false,
                "Acción no válida.",
                null,
                400
            );
    }

} catch (Throwable $error) {

    responderVehiculos(
        false,
        $error->getMessage(),
        null,
        500
    );
}