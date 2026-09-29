<?php

declare(strict_types=1);

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../config/conexion.php";

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

$action = $_GET["action"] ?? "";

//=====================================
// RESPUESTA JSON
//=====================================

function responder(
    bool $success,
    string $message,
    array $extra = [],
    int $codigoHttp = 200
): void {

    http_response_code($codigoHttp);

    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "message" => $message
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;

}

//=====================================
// LIMPIAR TEXTO
//=====================================

function limpiarTexto(string $valor): string
{
    return trim(
        preg_replace(
            "/\s+/",
            " ",
            $valor
        )
    );
}

//=====================================
// GUARDAR IMAGEN
//=====================================

function guardarImagen(array $archivo): string
{
    if (
        !isset($archivo["error"]) ||
        $archivo["error"] === UPLOAD_ERR_NO_FILE
    ) {

        return "";

    }

    if ($archivo["error"] !== UPLOAD_ERR_OK) {

        responder(
            false,
            "No fue posible subir la imagen.",
            [],
            400
        );

    }

    $tamanoMaximo = 5 * 1024 * 1024;

    if (
        !isset($archivo["size"]) ||
        $archivo["size"] > $tamanoMaximo
    ) {

        responder(
            false,
            "La imagen no debe superar los 5 MB.",
            [],
            400
        );

    }

    if (
        !isset($archivo["tmp_name"]) ||
        !is_uploaded_file($archivo["tmp_name"])
    ) {

        responder(
            false,
            "El archivo recibido no es válido.",
            [],
            400
        );

    }

    $finfo = new finfo(
        FILEINFO_MIME_TYPE
    );

    $tipoMime = $finfo->file(
        $archivo["tmp_name"]
    );

    $extensionesPermitidas = [

        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"

    ];

    if (
        !isset(
            $extensionesPermitidas[$tipoMime]
        )
    ) {

        responder(
            false,
            "La imagen debe ser JPG, PNG o WEBP.",
            [],
            400
        );

    }

    $carpetaFisica =
        dirname(__DIR__) .
        "/uploads/vehiculos/";

    if (!is_dir($carpetaFisica)) {

        $carpetaCreada = mkdir(
            $carpetaFisica,
            0775,
            true
        );

        if (!$carpetaCreada) {

            responder(
                false,
                "No fue posible crear la carpeta de imágenes.",
                [],
                500
            );

        }

    }

    $nombreArchivo =
        bin2hex(
            random_bytes(16)
        ) .
        "." .
        $extensionesPermitidas[$tipoMime];

    $rutaDestino =
        $carpetaFisica .
        $nombreArchivo;

    if (
        !move_uploaded_file(
            $archivo["tmp_name"],
            $rutaDestino
        )
    ) {

        responder(
            false,
            "No fue posible guardar la imagen.",
            [],
            500
        );

    }

    return
        "uploads/vehiculos/" .
        $nombreArchivo;
}

//=====================================
// ELIMINAR IMAGEN
//=====================================

function eliminarImagen(
    ?string $rutaImagen
): void {

    if (!$rutaImagen) {
        return;
    }

    if (
        !str_starts_with(
            $rutaImagen,
            "uploads/vehiculos/"
        )
    ) {

        return;

    }

    $rutaFisica =
        dirname(__DIR__) .
        "/" .
        $rutaImagen;

    if (is_file($rutaFisica)) {

        @unlink($rutaFisica);

    }

}

//=====================================
// VALIDAR SESIÓN
//=====================================

if (!isset($_SESSION["id_usuario"])) {

    responder(
        false,
        "La sesión ha expirado. Inicia sesión nuevamente.",
        [],
        401
    );

}

$idUsuario = (int) $_SESSION["id_usuario"];

try {

    switch ($action) {

        //=====================================
        // LISTAR VEHÍCULOS
        //=====================================

        case "listar":

            $stmt = $conn->prepare("
                SELECT
                    Id_vehiculo,
                    Marca,
                    Linea,
                    Modelo,
                    Color,
                    Placa,
                    No_chasis,
                    Tipo_vehiculo,
                    Cilindraje,
                    Combustible,
                    Kilometraje,
                    Estado,
                    Imagen_URL
                FROM Vehiculos
                WHERE Id_usuario = ?
                ORDER BY Id_vehiculo DESC
            ");

            $stmt->bind_param(
                "i",
                $idUsuario
            );

            $stmt->execute();

            $resultado =
                $stmt->get_result();

            $vehiculos = [];

            while (
                $fila =
                $resultado->fetch_assoc()
            ) {

                $fila["Id_vehiculo"] =
                    (int) $fila["Id_vehiculo"];

                $fila["Modelo"] =
                    (int) $fila["Modelo"];

                $fila["Cilindraje"] =
                    (int) $fila["Cilindraje"];

                $fila["Kilometraje"] =
                    (int) $fila["Kilometraje"];

                $fila["Estado"] =
                    (int) $fila["Estado"];

                $vehiculos[] = $fila;

            }

            responder(
                true,
                "Vehículos cargados correctamente.",
                [
                    "data" => $vehiculos
                ]
            );

        //=====================================
        // GUARDAR O EDITAR VEHÍCULO
        //=====================================

        case "guardar":

            $idVehiculo = filter_input(
                INPUT_POST,
                "id",
                FILTER_VALIDATE_INT
            );

            $marca = limpiarTexto(
                $_POST["marca"] ?? ""
            );

            $linea = limpiarTexto(
                $_POST["linea"] ?? ""
            );

            $modelo = filter_var(
                $_POST["modelo"] ?? null,
                FILTER_VALIDATE_INT
            );

            $color = limpiarTexto(
                $_POST["color"] ?? ""
            );

            $placa = strtoupper(
                limpiarTexto(
                    $_POST["placa"] ?? ""
                )
            );

            $noChasis = strtoupper(
                limpiarTexto(
                    $_POST["no_chasis"] ?? ""
                )
            );

            $tipoVehiculo = limpiarTexto(
                $_POST["tipo_vehiculo"] ?? ""
            );

            $cilindraje = filter_var(
                $_POST["cilindraje"] ?? null,
                FILTER_VALIDATE_INT
            );

            $combustible = limpiarTexto(
                $_POST["combustible"] ?? ""
            );

            $kilometraje = filter_var(
                $_POST["kilometraje"] ?? null,
                FILTER_VALIDATE_INT
            );

            $estado = filter_var(
                $_POST["estado"] ?? null,
                FILTER_VALIDATE_INT
            );

            //=====================================
            // VALIDACIONES GENERALES
            //=====================================

            if (
                $marca === "" ||
                $linea === "" ||
                $modelo === false ||
                $color === "" ||
                $placa === "" ||
                $noChasis === "" ||
                $tipoVehiculo === "" ||
                $cilindraje === false ||
                $combustible === "" ||
                $kilometraje === false
            ) {

                responder(
                    false,
                    "Completa todos los campos obligatorios.",
                    [],
                    422
                );

            }

            if (
                mb_strlen($marca) > 50
            ) {

                responder(
                    false,
                    "La marca no debe superar los 50 caracteres.",
                    [],
                    422
                );

            }

            if (
                mb_strlen($linea) > 60
            ) {

                responder(
                    false,
                    "La línea no debe superar los 60 caracteres.",
                    [],
                    422
                );

            }

            if (
                mb_strlen($color) > 40
            ) {

                responder(
                    false,
                    "El color no debe superar los 40 caracteres.",
                    [],
                    422
                );

            }

            if (
                mb_strlen($placa) > 20
            ) {

                responder(
                    false,
                    "La placa no debe superar los 20 caracteres.",
                    [],
                    422
                );

            }

            if (
                mb_strlen($noChasis) > 60
            ) {

                responder(
                    false,
                    "El número de chasis no debe superar los 60 caracteres.",
                    [],
                    422
                );

            }

            $anioMaximo =
                (int) date("Y") + 1;

            if (
                $modelo < 1900 ||
                $modelo > $anioMaximo
            ) {

                responder(
                    false,
                    "El modelo o año del vehículo no es válido.",
                    [],
                    422
                );

            }

            if (
                $cilindraje < 0 ||
                $cilindraje > 10000
            ) {

                responder(
                    false,
                    "El cilindraje no es válido.",
                    [],
                    422
                );

            }

            if ($kilometraje < 0) {

                responder(
                    false,
                    "El kilometraje no puede ser negativo.",
                    [],
                    422
                );

            }

            if (
                !in_array(
                    $estado,
                    [0, 1],
                    true
                )
            ) {

                responder(
                    false,
                    "El estado seleccionado no es válido.",
                    [],
                    422
                );

            }

            $tiposPermitidos = [

                "Motocicleta",
                "Automóvil"
            ];

            if (
                !in_array(
                    $tipoVehiculo,
                    $tiposPermitidos,
                    true
                )
            ) {

                responder(
                    false,
                    "El tipo de vehículo seleccionado no es válido.",
                    [],
                    422
                );

            }

            $combustiblesPermitidos = [

                "Gasolina",
                "Diésel",
                "Eléctrico",
                "Híbrido",
                "Otro"

            ];

            if (
                !in_array(
                    $combustible,
                    $combustiblesPermitidos,
                    true
                )
            ) {

                responder(
                    false,
                    "El combustible seleccionado no es válido.",
                    [],
                    422
                );

            }

            //=====================================
            // VALIDAR PLACA DUPLICADA
            //=====================================

            if ($idVehiculo) {

                $stmtPlaca = $conn->prepare("
                    SELECT Id_vehiculo
                    FROM Vehiculos
                    WHERE Placa = ?
                    AND Id_vehiculo <> ?
                    LIMIT 1
                ");

                $stmtPlaca->bind_param(
                    "si",
                    $placa,
                    $idVehiculo
                );

            } else {

                $stmtPlaca = $conn->prepare("
                    SELECT Id_vehiculo
                    FROM Vehiculos
                    WHERE Placa = ?
                    LIMIT 1
                ");

                $stmtPlaca->bind_param(
                    "s",
                    $placa
                );

            }

            $stmtPlaca->execute();

            if (
                $stmtPlaca
                    ->get_result()
                    ->num_rows > 0
            ) {

                responder(
                    false,
                    "La placa ya está registrada.",
                    [],
                    409
                );

            }

            //=====================================
            // VALIDAR CHASIS DUPLICADO
            //=====================================

            if ($idVehiculo) {

                $stmtChasis = $conn->prepare("
                    SELECT Id_vehiculo
                    FROM Vehiculos
                    WHERE No_chasis = ?
                    AND Id_vehiculo <> ?
                    LIMIT 1
                ");

                $stmtChasis->bind_param(
                    "si",
                    $noChasis,
                    $idVehiculo
                );

            } else {

                $stmtChasis = $conn->prepare("
                    SELECT Id_vehiculo
                    FROM Vehiculos
                    WHERE No_chasis = ?
                    LIMIT 1
                ");

                $stmtChasis->bind_param(
                    "s",
                    $noChasis
                );

            }

            $stmtChasis->execute();

            if (
                $stmtChasis
                    ->get_result()
                    ->num_rows > 0
            ) {

                responder(
                    false,
                    "El número de chasis ya está registrado.",
                    [],
                    409
                );

            }

            //=====================================
            // PROCESAR IMAGEN
            //=====================================

            $imagenNueva = "";

            if (isset($_FILES["imagen"])) {

                $imagenNueva =
                    guardarImagen(
                        $_FILES["imagen"]
                    );

            }

            //=====================================
            // INSERTAR VEHÍCULO
            //=====================================

            if (!$idVehiculo) {

                $stmt = $conn->prepare("
                    INSERT INTO Vehiculos (
                        Id_usuario,
                        Marca,
                        Linea,
                        Modelo,
                        Color,
                        Placa,
                        No_chasis,
                        Tipo_vehiculo,
                        Cilindraje,
                        Combustible,
                        Kilometraje,
                        Estado,
                        Imagen_URL
                    )
                    VALUES (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?
                    )
                ");

                $stmt->bind_param(
                    "ississssisiis",
                    $idUsuario,
                    $marca,
                    $linea,
                    $modelo,
                    $color,
                    $placa,
                    $noChasis,
                    $tipoVehiculo,
                    $cilindraje,
                    $combustible,
                    $kilometraje,
                    $estado,
                    $imagenNueva
                );

                $stmt->execute();

                responder(
                    true,
                    "Vehículo registrado correctamente.",
                    [
                        "id" =>
                            $conn->insert_id
                    ]
                );

            }

            //=====================================
            // COMPROBAR PROPIEDAD DEL VEHÍCULO
            //=====================================

            $stmtExiste = $conn->prepare("
                SELECT
                    Id_vehiculo,
                    Imagen_URL
                FROM Vehiculos
                WHERE Id_vehiculo = ?
                AND Id_usuario = ?
                LIMIT 1
            ");

            $stmtExiste->bind_param(
                "ii",
                $idVehiculo,
                $idUsuario
            );

            $stmtExiste->execute();

            $vehiculoActual =
                $stmtExiste
                    ->get_result()
                    ->fetch_assoc();

            if (!$vehiculoActual) {

                if ($imagenNueva !== "") {

                    eliminarImagen(
                        $imagenNueva
                    );

                }

                responder(
                    false,
                    "El vehículo no existe o no pertenece a tu cuenta.",
                    [],
                    404
                );

            }

            //=====================================
            // ACTUALIZAR CON IMAGEN
            //=====================================

            if ($imagenNueva !== "") {

                $stmt = $conn->prepare("
                    UPDATE Vehiculos
                    SET
                        Marca = ?,
                        Linea = ?,
                        Modelo = ?,
                        Color = ?,
                        Placa = ?,
                        No_chasis = ?,
                        Tipo_vehiculo = ?,
                        Cilindraje = ?,
                        Combustible = ?,
                        Kilometraje = ?,
                        Estado = ?,
                        Imagen_URL = ?
                    WHERE Id_vehiculo = ?
                    AND Id_usuario = ?
                ");

                $stmt->bind_param(
                    "ssissssisiisii",
                    $marca,
                    $linea,
                    $modelo,
                    $color,
                    $placa,
                    $noChasis,
                    $tipoVehiculo,
                    $cilindraje,
                    $combustible,
                    $kilometraje,
                    $estado,
                    $imagenNueva,
                    $idVehiculo,
                    $idUsuario
                );

                $stmt->execute();

                eliminarImagen(
                    $vehiculoActual["Imagen_URL"]
                    ?? null
                );

            } else {

                //=====================================
                // ACTUALIZAR SIN CAMBIAR IMAGEN
                //=====================================

                $stmt = $conn->prepare("
                    UPDATE Vehiculos
                    SET
                        Marca = ?,
                        Linea = ?,
                        Modelo = ?,
                        Color = ?,
                        Placa = ?,
                        No_chasis = ?,
                        Tipo_vehiculo = ?,
                        Cilindraje = ?,
                        Combustible = ?,
                        Kilometraje = ?,
                        Estado = ?
                    WHERE Id_vehiculo = ?
                    AND Id_usuario = ?
                ");

                $stmt->bind_param(
                    "ssissssisiisi",
                    $marca,
                    $linea,
                    $modelo,
                    $color,
                    $placa,
                    $noChasis,
                    $tipoVehiculo,
                    $cilindraje,
                    $combustible,
                    $kilometraje,
                    $estado,
                    $idVehiculo,
                    $idUsuario
                );

                $stmt->execute();

            }

            responder(
                true,
                "Vehículo actualizado correctamente."
            );

        //=====================================
        // ELIMINAR VEHÍCULO
        //=====================================

        case "eliminar":

            $idVehiculo = filter_input(
                INPUT_POST,
                "id",
                FILTER_VALIDATE_INT
            );

            if (
                !$idVehiculo ||
                $idVehiculo <= 0
            ) {

                responder(
                    false,
                    "El identificador del vehículo no es válido.",
                    [],
                    422
                );

            }

            //=====================================
            // COMPROBAR VEHÍCULO Y PROPIEDAD
            //=====================================

            $stmtBuscar = $conn->prepare("
                SELECT Imagen_URL
                FROM Vehiculos
                WHERE Id_vehiculo = ?
                AND Id_usuario = ?
                LIMIT 1
            ");

            $stmtBuscar->bind_param(
                "ii",
                $idVehiculo,
                $idUsuario
            );

            $stmtBuscar->execute();

            $vehiculo =
                $stmtBuscar
                    ->get_result()
                    ->fetch_assoc();

            if (!$vehiculo) {

                responder(
                    false,
                    "El vehículo no existe o no pertenece a tu cuenta.",
                    [],
                    404
                );

            }

            //=====================================
            // ELIMINAR REGISTRO
            //=====================================

            $stmtEliminar = $conn->prepare("
                DELETE FROM Vehiculos
                WHERE Id_vehiculo = ?
                AND Id_usuario = ?
            ");

            $stmtEliminar->bind_param(
                "ii",
                $idVehiculo,
                $idUsuario
            );

            $stmtEliminar->execute();

            if (
                $stmtEliminar->affected_rows === 0
            ) {

                responder(
                    false,
                    "No fue posible eliminar el vehículo.",
                    [],
                    500
                );

            }

            eliminarImagen(
                $vehiculo["Imagen_URL"]
                ?? null
            );

            responder(
                true,
                "Vehículo eliminado correctamente."
            );

        //=====================================
        // ACCIÓN NO VÁLIDA
        //=====================================

        default:

            responder(
                false,
                "Acción no válida.",
                [],
                400
            );

    }

} catch (Throwable $e) {

    error_log(
        "API Vehículos: " .
        $e->getMessage()
    );

    responder(
        false,
        "Ocurrió un error interno al procesar la solicitud.",
        [],
        500
    );

} finally {

    if (isset($conn)) {

        $conn->close();

    }

}