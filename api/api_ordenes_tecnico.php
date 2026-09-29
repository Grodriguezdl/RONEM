<?php

declare(strict_types=1);

/*==================================================
CONFIGURACIÓN GENERAL
==================================================*/

header("Content-Type: application/json; charset=utf-8");

require_once "../config/conexion.php";

mysqli_report(MYSQLI_REPORT_OFF);

$action = trim(
    (string)($_GET["action"] ?? "")
);


/*==================================================
RESPUESTA JSON
==================================================*/

function responderOrdenes(
    bool $success,
    string $mensaje = "",
    mixed $data = null,
    int $codigoHTTP = 200
): never {

    http_response_code($codigoHTTP);

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
VERIFICAR MÉTODO HTTP
==================================================*/

function exigirMetodoOrdenes(
    string $metodo
): void {

    $metodoActual = strtoupper(
        (string)($_SERVER["REQUEST_METHOD"] ?? "")
    );

    if ($metodoActual !== strtoupper($metodo)) {

        responderOrdenes(
            false,
            "Método HTTP no permitido.",
            null,
            405
        );

    }
}


/*==================================================
VERIFICAR SI EXISTE UNA TABLA
==================================================*/

function existeTablaOrdenes(
    mysqli $conn,
    string $tabla
): bool {

    $sql = "
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "s",
        $tabla
    );

    if (!$stmt->execute()) {

        $stmt->close();

        return false;
    }

    $resultado = $stmt->get_result();

    $fila = $resultado->fetch_assoc();

    $stmt->close();

    return isset($fila["total"]) &&
        (int)$fila["total"] > 0;
}


/*==================================================
VERIFICAR SI EXISTE UNA COLUMNA
==================================================*/

function existeColumnaOrdenes(
    mysqli $conn,
    string $tabla,
    string $columna
): bool {

    $sql = "
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = ?
        AND COLUMN_NAME = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "ss",
        $tabla,
        $columna
    );

    if (!$stmt->execute()) {

        $stmt->close();

        return false;
    }

    $resultado = $stmt->get_result();

    $fila = $resultado->fetch_assoc();

    $stmt->close();

    return isset($fila["total"]) &&
        (int)$fila["total"] > 0;
}


/*==================================================
OBTENER PRIMERA COLUMNA EXISTENTE
==================================================*/

function obtenerColumnaOrdenes(
    mysqli $conn,
    string $tabla,
    array $posiblesColumnas
): ?string {

    foreach ($posiblesColumnas as $columna) {

        if (
            existeColumnaOrdenes(
                $conn,
                $tabla,
                $columna
            )
        ) {
            return $columna;
        }
    }

    return null;
}


/*==================================================
OBTENER VALOR POST
==================================================*/

function obtenerPostOrdenes(
    array $nombres,
    mixed $valorPredeterminado = ""
): mixed {

    foreach ($nombres as $nombre) {

        if (!array_key_exists($nombre, $_POST)) {
            continue;
        }

        $valor = trim(
            (string)$_POST[$nombre]
        );

        if ($valor !== "") {
            return $valor;
        }
    }

    return $valorPredeterminado;
}


/*==================================================
NORMALIZAR TEXTO DE ESTADO
==================================================*/

function normalizarTextoEstado(
    string $texto
): string {

    $texto = trim($texto);

    $texto = mb_strtolower(
        $texto,
        "UTF-8"
    );

    $reemplazos = [
        "á" => "a",
        "é" => "e",
        "í" => "i",
        "ó" => "o",
        "ú" => "u",
        "ü" => "u",
        "ñ" => "n"
    ];

    $texto = strtr(
        $texto,
        $reemplazos
    );

    $texto = preg_replace(
        '/[^a-z0-9]+/u',
        " ",
        $texto
    );

    return trim(
        preg_replace(
            '/\s+/',
            " ",
            (string)$texto
        )
    );
}


/*==================================================
NORMALIZAR FECHA MYSQL
==================================================*/

function normalizarFechaOrdenes(
    mixed $fecha
): ?string {

    $fecha = trim(
        (string)$fecha
    );

    if ($fecha === "") {
        return null;
    }

    $fecha = str_replace(
        "T",
        " ",
        $fecha
    );

    $timestamp = strtotime($fecha);

    if ($timestamp === false) {
        return null;
    }

    return date(
        "Y-m-d H:i:s",
        $timestamp
    );
}


/*==================================================
EJECUTAR SENTENCIA DINÁMICA
==================================================*/

function ejecutarStatementOrdenes(
    mysqli_stmt $stmt,
    string $tipos,
    array &$valores
): bool {

    if ($tipos !== "") {

        $parametros = [
            $tipos
        ];

        foreach ($valores as $indice => $valor) {

            $parametros[] =
                &$valores[$indice];
        }

        call_user_func_array(
            [
                $stmt,
                "bind_param"
            ],
            $parametros
        );
    }

    return $stmt->execute();
}


/*==================================================
OBTENER DATOS DE LA TABLA DE ESTADOS
==================================================*/

function obtenerConfiguracionEstados(
    mysqli $conn
): ?array {

    if (
        !existeTablaOrdenes(
            $conn,
            "Estado_mantenimiento"
        )
    ) {
        return null;
    }

    $columnaId = obtenerColumnaOrdenes(
        $conn,
        "Estado_mantenimiento",
        [
            "Id_estado_mantenimiento",
            "Id_estado",
            "Id"
        ]
    );

    $columnaNombre = obtenerColumnaOrdenes(
        $conn,
        "Estado_mantenimiento",
        [
            "Nombre",
            "Estado",
            "Descripcion"
        ]
    );

    if (
        !$columnaId ||
        !$columnaNombre
    ) {
        return null;
    }

    return [
        "id" => $columnaId,
        "nombre" => $columnaNombre
    ];
}


/*==================================================
OBTENER TODOS LOS ESTADOS
==================================================*/

function listarEstadosDisponibles(
    mysqli $conn
): array {

    $configuracion = obtenerConfiguracionEstados(
        $conn
    );

    if ($configuracion === null) {
        return [];
    }

    $columnaId =
        $configuracion["id"];

    $columnaNombre =
        $configuracion["nombre"];

    $sql = "
        SELECT
            `$columnaId` AS Id_estado,
            `$columnaNombre` AS Nombre_estado
        FROM Estado_mantenimiento
        ORDER BY `$columnaId` ASC
    ";

    $resultado = $conn->query($sql);

    if (!$resultado) {
        return [];
    }

    $estados = [];

    while ($fila = $resultado->fetch_assoc()) {

        $estados[] = [
            "Id_estado" =>
                (int)$fila["Id_estado"],

            "Nombre_estado" =>
                trim(
                    (string)$fila["Nombre_estado"]
                )
        ];
    }

    return $estados;
}


/*==================================================
OBTENER ID DEL ESTADO
==================================================*/

function obtenerIdEstadoOrdenes(
    mysqli $conn,
    string $estado
): ?int {

    $estado = trim($estado);

    if ($estado === "") {
        return null;
    }

    /*
    Si el JS envía directamente el ID.
    */

    if (ctype_digit($estado)) {

        $idEstado = (int)$estado;

        foreach (
            listarEstadosDisponibles($conn)
            as $registro
        ) {

            if (
                (int)$registro["Id_estado"] ===
                $idEstado
            ) {
                return $idEstado;
            }
        }
    }

    /*
    Comparación sin importar:
    - mayúsculas
    - minúsculas
    - tildes
    - espacios
    */

    $estadoNormalizado =
        normalizarTextoEstado(
            $estado
        );

    foreach (
        listarEstadosDisponibles($conn)
        as $registro
    ) {

        $nombreNormalizado =
            normalizarTextoEstado(
                (string)$registro["Nombre_estado"]
            );

        if (
            $nombreNormalizado ===
            $estadoNormalizado
        ) {
            return (int)$registro["Id_estado"];
        }
    }

    return null;
}


/*==================================================
OBTENER NOMBRE DEL ESTADO POR ID
==================================================*/

function obtenerNombreEstadoOrdenes(
    mysqli $conn,
    mixed $estado
): string {

    $estado = trim(
        (string)$estado
    );

    if ($estado === "") {
        return "";
    }

    if (!ctype_digit($estado)) {
        return $estado;
    }

    $idEstado = (int)$estado;

    foreach (
        listarEstadosDisponibles($conn)
        as $registro
    ) {

        if (
            (int)$registro["Id_estado"] ===
            $idEstado
        ) {
            return (string)$registro["Nombre_estado"];
        }
    }

    return $estado;
}


/*==================================================
REGISTRAR CAMBIO EN HISTORIAL
==================================================*/

function registrarHistorialOrdenes(
    mysqli $conn,
    int $idOrden,
    string $estadoAnterior,
    string $estadoNuevo,
    string $descripcion = ""
): void {

    if (
        !existeTablaOrdenes(
            $conn,
            "Historial"
        )
    ) {
        return;
    }

    $columnas = [];
    $valores = [];
    $tipos = "";

    if (
        existeColumnaOrdenes(
            $conn,
            "Historial",
            "Id_orden"
        )
    ) {

        $columnas[] = "Id_orden";
        $valores[] = $idOrden;
        $tipos .= "i";
    }

    $columnaAnterior = obtenerColumnaOrdenes(
        $conn,
        "Historial",
        [
            "Estado_anterior",
            "Anterior"
        ]
    );

    if ($columnaAnterior) {

        $columnas[] = $columnaAnterior;
        $valores[] = $estadoAnterior;
        $tipos .= "s";
    }

    $columnaNuevo = obtenerColumnaOrdenes(
        $conn,
        "Historial",
        [
            "Estado_nuevo",
            "Nuevo_estado",
            "Estado"
        ]
    );

    if ($columnaNuevo) {

        $columnas[] = $columnaNuevo;
        $valores[] = $estadoNuevo;
        $tipos .= "s";
    }

    $columnaDescripcion = obtenerColumnaOrdenes(
        $conn,
        "Historial",
        [
            "Descripcion",
            "Observaciones",
            "Detalle",
            "Comentario"
        ]
    );

    if ($columnaDescripcion) {

        $columnas[] = $columnaDescripcion;

        $valores[] =
            $descripcion !== ""
                ? $descripcion
                : "Cambio de estado de la orden.";

        $tipos .= "s";
    }

    $columnaFecha = obtenerColumnaOrdenes(
        $conn,
        "Historial",
        [
            "Fecha",
            "Fecha_registro",
            "Fecha_actualizacion"
        ]
    );

    if ($columnaFecha) {

        $columnas[] = $columnaFecha;

        $valores[] =
            date("Y-m-d H:i:s");

        $tipos .= "s";
    }

    if (count($columnas) === 0) {
        return;
    }

    $columnasSQL = implode(
        ", ",
        array_map(
            static fn(string $columna): string =>
                "`$columna`",
            $columnas
        )
    );

    $marcadores = implode(
        ", ",
        array_fill(
            0,
            count($columnas),
            "?"
        )
    );

    $sql = "
        INSERT INTO Historial
        ($columnasSQL)
        VALUES ($marcadores)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return;
    }

    ejecutarStatementOrdenes(
        $stmt,
        $tipos,
        $valores
    );

    $stmt->close();
}


/*==================================================
CONSTRUIR SELECT GENERAL
==================================================*/

function construirSelectOrdenes(
    mysqli $conn
): string {

    $campos = [
        "o.*"
    ];

    $joins = [];

    if (
        existeTablaOrdenes(
            $conn,
            "Vehiculos"
        ) &&
        existeColumnaOrdenes(
            $conn,
            "Ordenes",
            "Id_vehiculo"
        )
    ) {

        $joins[] = "
            LEFT JOIN Vehiculos v
                ON v.Id_vehiculo = o.Id_vehiculo
        ";

        $camposVehiculo = [
            "Marca",
            "Linea",
            "Modelo",
            "Color",
            "Placa",
            "No_chasis",
            "Tipo_vehiculo",
            "Kilometraje"
        ];

        foreach ($camposVehiculo as $campo) {

            if (
                existeColumnaOrdenes(
                    $conn,
                    "Vehiculos",
                    $campo
                )
            ) {
                $campos[] = "v.`$campo`";
            }
        }
    }

    if (
        existeTablaOrdenes(
            $conn,
            "Usuarios"
        ) &&
        existeColumnaOrdenes(
            $conn,
            "Ordenes",
            "Id_usuario"
        )
    ) {

        $joins[] = "
            LEFT JOIN Usuarios u
                ON u.Id_usuario = o.Id_usuario
        ";

        $camposUsuario = [
            "Nombre",
            "Apellido",
            "Correo",
            "Telefono",
            "DPI"
        ];

        foreach ($camposUsuario as $campo) {

            if (
                existeColumnaOrdenes(
                    $conn,
                    "Usuarios",
                    $campo
                )
            ) {
                $campos[] = "u.`$campo`";
            }
        }
    }

    $columnaEstadoOrden = obtenerColumnaOrdenes(
        $conn,
        "Ordenes",
        [
            "Id_estado_mantenimiento",
            "Id_estado"
        ]
    );

    $configuracionEstados =
        obtenerConfiguracionEstados(
            $conn
        );

    if (
        $columnaEstadoOrden &&
        $configuracionEstados !== null
    ) {

        $columnaIdEstado =
            $configuracionEstados["id"];

        $columnaNombreEstado =
            $configuracionEstados["nombre"];

        $joins[] = "
            LEFT JOIN Estado_mantenimiento em
                ON em.`$columnaIdEstado` =
                   o.`$columnaEstadoOrden`
        ";

        $campos[] = "
            em.`$columnaNombreEstado`
            AS Nombre_estado
        ";
    }

    return "
        SELECT
            " .
            implode(
                ",\n",
                $campos
            ) .
        "
        FROM Ordenes o
        " .
        implode(
            "\n",
            $joins
        );
}


/*==================================================
LISTAR ÓRDENES
==================================================*/

function listarOrdenesTecnico(
    mysqli $conn
): never {

    $sql = construirSelectOrdenes(
        $conn
    );

    $columnaFecha = obtenerColumnaOrdenes(
        $conn,
        "Ordenes",
        [
            "Fecha_ingreso",
            "Fecha",
            "Fecha_registro"
        ]
    );

    if ($columnaFecha) {

        $sql .= "
            ORDER BY
                o.`$columnaFecha` DESC,
                o.Id_orden DESC
        ";

    } else {

        $sql .= "
            ORDER BY o.Id_orden DESC
        ";
    }

    $resultado = $conn->query($sql);

    if (!$resultado) {

        responderOrdenes(
            false,
            "Error al consultar las órdenes: " .
            $conn->error,
            null,
            500
        );
    }

    $ordenes = [];

    while ($fila = $resultado->fetch_assoc()) {

        if (
            isset($fila["Nombre_estado"]) &&
            trim(
                (string)$fila["Nombre_estado"]
            ) !== ""
        ) {
            $fila["Estado"] =
                $fila["Nombre_estado"];
        }

        if (isset($fila["Id_orden"])) {
            $fila["Id_orden"] =
                (int)$fila["Id_orden"];
        }

        if (isset($fila["Id_usuario"])) {
            $fila["Id_usuario"] =
                (int)$fila["Id_usuario"];
        }

        if (isset($fila["Id_vehiculo"])) {
            $fila["Id_vehiculo"] =
                (int)$fila["Id_vehiculo"];
        }

        $ordenes[] = $fila;
    }

    responderOrdenes(
        true,
        "Órdenes cargadas correctamente.",
        $ordenes
    );
}


/*==================================================
OBTENER UNA ORDEN
==================================================*/

function obtenerOrdenTecnico(
    mysqli $conn
): never {

    $idOrden = (int)(
        $_GET["id"] ??
        $_GET["id_orden"] ??
        $_GET["Id_orden"] ??
        0
    );

    if ($idOrden <= 0) {

        responderOrdenes(
            false,
            "El ID de la orden no es válido.",
            null,
            422
        );
    }

    $sql = construirSelectOrdenes(
        $conn
    );

    $sql .= "
        WHERE o.Id_orden = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        responderOrdenes(
            false,
            "No se pudo preparar la consulta: " .
            $conn->error,
            null,
            500
        );
    }

    $stmt->bind_param(
        "i",
        $idOrden
    );

    if (!$stmt->execute()) {

        $error = $stmt->error;

        $stmt->close();

        responderOrdenes(
            false,
            "No se pudo consultar la orden: " .
            $error,
            null,
            500
        );
    }

    $resultado = $stmt->get_result();

    $orden = $resultado->fetch_assoc();

    $stmt->close();

    if (!$orden) {

        responderOrdenes(
            false,
            "No se encontró la orden solicitada.",
            null,
            404
        );
    }

    if (
        isset($orden["Nombre_estado"]) &&
        trim(
            (string)$orden["Nombre_estado"]
        ) !== ""
    ) {
        $orden["Estado"] =
            $orden["Nombre_estado"];
    }

    responderOrdenes(
        true,
        "Orden encontrada correctamente.",
        $orden
    );
}


/*==================================================
VALIDAR USUARIO
==================================================*/

function validarUsuarioOrdenes(
    mysqli $conn,
    int $idUsuario
): bool {

    if ($idUsuario <= 0) {
        return false;
    }

    if (
        !existeTablaOrdenes(
            $conn,
            "Usuarios"
        )
    ) {
        return false;
    }

    $sql = "
        SELECT Id_usuario
        FROM Usuarios
        WHERE Id_usuario = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "i",
        $idUsuario
    );

    if (!$stmt->execute()) {

        $stmt->close();

        return false;
    }

    $resultado = $stmt->get_result();

    $existe =
        $resultado->num_rows > 0;

    $stmt->close();

    return $existe;
}


/*==================================================
VALIDAR VEHÍCULO Y PROPIETARIO
==================================================*/

function validarVehiculoOrdenes(
    mysqli $conn,
    int $idVehiculo,
    int $idUsuario
): bool {

    if (
        $idVehiculo <= 0 ||
        $idUsuario <= 0
    ) {
        return false;
    }

    if (
        !existeTablaOrdenes(
            $conn,
            "Vehiculos"
        )
    ) {
        return false;
    }

    $sql = "
        SELECT Id_vehiculo
        FROM Vehiculos
        WHERE Id_vehiculo = ?
        AND Id_usuario = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "ii",
        $idVehiculo,
        $idUsuario
    );

    if (!$stmt->execute()) {

        $stmt->close();

        return false;
    }

    $resultado = $stmt->get_result();

    $existe =
        $resultado->num_rows > 0;

    $stmt->close();

    return $existe;
}


/*==================================================
GUARDAR O EDITAR ORDEN
==================================================*/

function guardarOrdenTecnico(
    mysqli $conn
): never {

    exigirMetodoOrdenes(
        "POST"
    );

    $idOrden = (int)obtenerPostOrdenes(
        [
            "id_orden",
            "Id_orden",
            "id"
        ],
        0
    );

    $idUsuario = (int)obtenerPostOrdenes(
        [
            "Id_usuario",
            "id_usuario"
        ],
        0
    );

    $idVehiculo = (int)obtenerPostOrdenes(
        [
            "Id_vehiculo",
            "id_vehiculo"
        ],
        0
    );

    $servicio = (string)obtenerPostOrdenes(
        [
            "Servicio",
            "servicio",
            "Tipo_servicio",
            "tipo_servicio",
            "Motivo",
            "motivo"
        ]
    );

    $descripcion = (string)obtenerPostOrdenes(
        [
            "Descripcion",
            "descripcion",
            "Problema",
            "problema",
            "Observaciones",
            "observaciones"
        ]
    );

    $fechaIngreso = normalizarFechaOrdenes(
        obtenerPostOrdenes(
            [
                "Fecha_ingreso",
                "fecha_ingreso",
                "Fecha"
            ]
        )
    );

    $fechaEntrega = normalizarFechaOrdenes(
        obtenerPostOrdenes(
            [
                "Fecha_entrega",
                "fecha_entrega",
                "Fecha_salida"
            ]
        )
    );

    $estado = (string)obtenerPostOrdenes(
        [
            "Estado",
            "estado",
            "Nombre_estado",
            "nombre_estado"
        ],
        "Recibida"
    );

    $costo = obtenerPostOrdenes(
        [
            "Costo",
            "costo",
            "Costo_total",
            "costo_total",
            "Total",
            "total"
        ],
        ""
    );

    if ($idUsuario <= 0) {

        responderOrdenes(
            false,
            "Debes seleccionar un usuario válido.",
            null,
            422
        );
    }

    if ($idVehiculo <= 0) {

        responderOrdenes(
            false,
            "Debes seleccionar un vehículo válido.",
            null,
            422
        );
    }

    if (
        !validarUsuarioOrdenes(
            $conn,
            $idUsuario
        )
    ) {

        responderOrdenes(
            false,
            "El usuario seleccionado no existe.",
            null,
            404
        );
    }

    if (
        !validarVehiculoOrdenes(
            $conn,
            $idVehiculo,
            $idUsuario
        )
    ) {

        responderOrdenes(
            false,
            "El vehículo seleccionado no pertenece al usuario indicado.",
            null,
            422
        );
    }

    $datosDisponibles = [
        "Id_usuario" => [
            "valor" => $idUsuario,
            "tipo" => "i"
        ],

        "Id_vehiculo" => [
            "valor" => $idVehiculo,
            "tipo" => "i"
        ]
    ];

    $columnaServicio = obtenerColumnaOrdenes(
        $conn,
        "Ordenes",
        [
            "Servicio",
            "Tipo_servicio",
            "Motivo"
        ]
    );

    if (
        $columnaServicio &&
        $servicio !== ""
    ) {

        $datosDisponibles[$columnaServicio] = [
            "valor" => $servicio,
            "tipo" => "s"
        ];
    }

    $columnaDescripcion = obtenerColumnaOrdenes(
        $conn,
        "Ordenes",
        [
            "Descripcion",
            "Problema",
            "Observaciones"
        ]
    );

    if (
        $columnaDescripcion &&
        $descripcion !== ""
    ) {

        $datosDisponibles[$columnaDescripcion] = [
            "valor" => $descripcion,
            "tipo" => "s"
        ];
    }

    $columnaFechaIngreso = obtenerColumnaOrdenes(
        $conn,
        "Ordenes",
        [
            "Fecha_ingreso",
            "Fecha",
            "Fecha_registro"
        ]
    );

    if ($columnaFechaIngreso) {

        $datosDisponibles[$columnaFechaIngreso] = [
            "valor" =>
                $fechaIngreso ??
                date("Y-m-d H:i:s"),

            "tipo" => "s"
        ];
    }

    $columnaFechaEntrega = obtenerColumnaOrdenes(
        $conn,
        "Ordenes",
        [
            "Fecha_entrega",
            "Fecha_salida"
        ]
    );

    if (
        $columnaFechaEntrega &&
        $fechaEntrega !== null
    ) {

        $datosDisponibles[$columnaFechaEntrega] = [
            "valor" => $fechaEntrega,
            "tipo" => "s"
        ];
    }

    $columnaCosto = obtenerColumnaOrdenes(
        $conn,
        "Ordenes",
        [
            "Costo",
            "Costo_total",
            "Total"
        ]
    );

    if (
        $columnaCosto &&
        $costo !== ""
    ) {

        $datosDisponibles[$columnaCosto] = [
            "valor" => (float)$costo,
            "tipo" => "d"
        ];
    }

    $columnaEstadoTexto = obtenerColumnaOrdenes(
        $conn,
        "Ordenes",
        [
            "Estado"
        ]
    );

    $columnaIdEstado = obtenerColumnaOrdenes(
        $conn,
        "Ordenes",
        [
            "Id_estado_mantenimiento",
            "Id_estado"
        ]
    );

    if ($columnaEstadoTexto) {

        $datosDisponibles[$columnaEstadoTexto] = [
            "valor" => $estado,
            "tipo" => "s"
        ];

    } elseif ($columnaIdEstado) {

        $idEstado = obtenerIdEstadoOrdenes(
            $conn,
            $estado
        );

        if ($idEstado === null) {

            responderOrdenes(
                false,
                "El estado seleccionado no existe en Estado_mantenimiento.",
                [
                    "Estado_recibido" => $estado,
                    "Estados_disponibles" =>
                        listarEstadosDisponibles($conn)
                ],
                422
            );
        }

        $datosDisponibles[$columnaIdEstado] = [
            "valor" => $idEstado,
            "tipo" => "i"
        ];
    }

    if ($idOrden > 0) {

        actualizarOrdenCompleta(
            $conn,
            $idOrden,
            $datosDisponibles
        );

    } else {

        insertarOrdenCompleta(
            $conn,
            $datosDisponibles,
            $estado
        );
    }
}


/*==================================================
INSERTAR ORDEN
==================================================*/

function insertarOrdenCompleta(
    mysqli $conn,
    array $datos,
    string $estado
): never {

    $columnas = [];
    $marcadores = [];
    $valores = [];
    $tipos = "";

    foreach (
        $datos as $columna => $configuracion
    ) {

        if (
            !existeColumnaOrdenes(
                $conn,
                "Ordenes",
                $columna
            )
        ) {
            continue;
        }

        $columnas[] = "`$columna`";
        $marcadores[] = "?";
        $valores[] = $configuracion["valor"];
        $tipos .= $configuracion["tipo"];
    }

    if (count($columnas) === 0) {

        responderOrdenes(
            false,
            "No se encontraron columnas válidas para registrar la orden.",
            null,
            500
        );
    }

    $sql = "
        INSERT INTO Ordenes
        (
            " .
            implode(
                ", ",
                $columnas
            ) .
        "
        )
        VALUES
        (
            " .
            implode(
                ", ",
                $marcadores
            ) .
        "
        )
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        responderOrdenes(
            false,
            "No se pudo preparar el registro: " .
            $conn->error,
            null,
            500
        );
    }

    if (
        !ejecutarStatementOrdenes(
            $stmt,
            $tipos,
            $valores
        )
    ) {

        $error = $stmt->error;

        $stmt->close();

        responderOrdenes(
            false,
            "No se pudo registrar la orden: " .
            $error,
            null,
            500
        );
    }

    $idOrden =
        (int)$conn->insert_id;

    $stmt->close();

    registrarHistorialOrdenes(
        $conn,
        $idOrden,
        "",
        $estado,
        "Orden de trabajo registrada."
    );

    responderOrdenes(
        true,
        "Orden registrada correctamente.",
        [
            "Id_orden" => $idOrden
        ],
        201
    );
}


/*==================================================
ACTUALIZAR ORDEN COMPLETA
==================================================*/

function actualizarOrdenCompleta(
    mysqli $conn,
    int $idOrden,
    array $datos
): never {

    $asignaciones = [];
    $valores = [];
    $tipos = "";

    foreach (
        $datos as $columna => $configuracion
    ) {

        if (
            !existeColumnaOrdenes(
                $conn,
                "Ordenes",
                $columna
            )
        ) {
            continue;
        }

        $asignaciones[] =
            "`$columna` = ?";

        $valores[] =
            $configuracion["valor"];

        $tipos .=
            $configuracion["tipo"];
    }

    if (count($asignaciones) === 0) {

        responderOrdenes(
            false,
            "No existen datos válidos para actualizar.",
            null,
            422
        );
    }

    $valores[] = $idOrden;
    $tipos .= "i";

    $sql = "
        UPDATE Ordenes
        SET " .
        implode(
            ", ",
            $asignaciones
        ) .
        "
        WHERE Id_orden = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        responderOrdenes(
            false,
            "No se pudo preparar la actualización: " .
            $conn->error,
            null,
            500
        );
    }

    if (
        !ejecutarStatementOrdenes(
            $stmt,
            $tipos,
            $valores
        )
    ) {

        $error = $stmt->error;

        $stmt->close();

        responderOrdenes(
            false,
            "No se pudo actualizar la orden: " .
            $error,
            null,
            500
        );
    }

    $stmt->close();

    $sqlExiste = "
        SELECT Id_orden
        FROM Ordenes
        WHERE Id_orden = ?
        LIMIT 1
    ";

    $stmtExiste = $conn->prepare(
        $sqlExiste
    );

    if (!$stmtExiste) {

        responderOrdenes(
            false,
            "No se pudo verificar la orden actualizada.",
            null,
            500
        );
    }

    $stmtExiste->bind_param(
        "i",
        $idOrden
    );

    $stmtExiste->execute();

    $resultado =
        $stmtExiste->get_result();

    $existe =
        $resultado->num_rows > 0;

    $stmtExiste->close();

    if (!$existe) {

        responderOrdenes(
            false,
            "La orden que intentas actualizar no existe.",
            null,
            404
        );
    }

    responderOrdenes(
        true,
        "Orden actualizada correctamente.",
        [
            "Id_orden" => $idOrden
        ]
    );
}


/*==================================================
OBTENER ESTADO ACTUAL
==================================================*/

function obtenerEstadoActualOrdenes(
    mysqli $conn,
    int $idOrden
): string {

    $columnaEstado = obtenerColumnaOrdenes(
        $conn,
        "Ordenes",
        [
            "Estado",
            "Id_estado_mantenimiento",
            "Id_estado"
        ]
    );

    if (!$columnaEstado) {
        return "";
    }

    $sql = "
        SELECT
            `$columnaEstado`
            AS Estado_actual
        FROM Ordenes
        WHERE Id_orden = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return "";
    }

    $stmt->bind_param(
        "i",
        $idOrden
    );

    if (!$stmt->execute()) {

        $stmt->close();

        return "";
    }

    $resultado = $stmt->get_result();

    $fila = $resultado->fetch_assoc();

    $stmt->close();

    if (!$fila) {
        return "";
    }

    return obtenerNombreEstadoOrdenes(
        $conn,
        $fila["Estado_actual"] ?? ""
    );
}


/*==================================================
ACTUALIZAR ESTADO DE LA ORDEN
==================================================*/

function actualizarEstadoOrdenTecnicoAPI(
    mysqli $conn
): never {

    exigirMetodoOrdenes(
        "POST"
    );

    $idOrden = (int)obtenerPostOrdenes(
        [
            "id_orden",
            "Id_orden",
            "id"
        ],
        0
    );

    $nuevoEstado = (string)obtenerPostOrdenes(
        [
            "estado",
            "Estado",
            "nuevo_estado",
            "nombre_estado",
            "Nombre_estado"
        ]
    );

    $descripcion = (string)obtenerPostOrdenes(
        [
            "descripcion",
            "Descripcion",
            "observaciones",
            "Observaciones"
        ],
        "Cambio de estado de la orden."
    );

    if ($idOrden <= 0) {

        responderOrdenes(
            false,
            "El ID de la orden no es válido.",
            null,
            422
        );
    }

    if ($nuevoEstado === "") {

        responderOrdenes(
            false,
            "Debes seleccionar el nuevo estado.",
            null,
            422
        );
    }

    $estadoAnterior = obtenerEstadoActualOrdenes(
        $conn,
        $idOrden
    );

    if ($estadoAnterior === "") {

        responderOrdenes(
            false,
            "No se encontró la orden indicada.",
            null,
            404
        );
    }

    $columnaEstadoTexto = obtenerColumnaOrdenes(
        $conn,
        "Ordenes",
        [
            "Estado"
        ]
    );

    $columnaIdEstado = obtenerColumnaOrdenes(
        $conn,
        "Ordenes",
        [
            "Id_estado_mantenimiento",
            "Id_estado"
        ]
    );

    $estadoGuardado = $nuevoEstado;

    if ($columnaEstadoTexto) {

        /*
        Si la tabla guarda el nombre del estado como texto,
        se intenta recuperar el nombre exacto de la tabla de estados.
        */

        $idEstado = obtenerIdEstadoOrdenes(
            $conn,
            $nuevoEstado
        );

        if ($idEstado !== null) {

            $estadoGuardado =
                obtenerNombreEstadoOrdenes(
                    $conn,
                    $idEstado
                );
        }

        $sql = "
            UPDATE Ordenes
            SET `$columnaEstadoTexto` = ?
            WHERE Id_orden = ?
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            responderOrdenes(
                false,
                "No se pudo preparar la actualización: " .
                $conn->error,
                null,
                500
            );
        }

        $stmt->bind_param(
            "si",
            $estadoGuardado,
            $idOrden
        );

    } elseif ($columnaIdEstado) {

        $idEstado = obtenerIdEstadoOrdenes(
            $conn,
            $nuevoEstado
        );

        if ($idEstado === null) {

            responderOrdenes(
                false,
                "El estado seleccionado no existe en Estado_mantenimiento.",
                [
                    "Estado_recibido" => $nuevoEstado,
                    "Estados_disponibles" =>
                        listarEstadosDisponibles($conn)
                ],
                422
            );
        }

        $estadoGuardado =
            obtenerNombreEstadoOrdenes(
                $conn,
                $idEstado
            );

        $sql = "
            UPDATE Ordenes
            SET `$columnaIdEstado` = ?
            WHERE Id_orden = ?
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            responderOrdenes(
                false,
                "No se pudo preparar la actualización: " .
                $conn->error,
                null,
                500
            );
        }

        $stmt->bind_param(
            "ii",
            $idEstado,
            $idOrden
        );

    } else {

        responderOrdenes(
            false,
            "La tabla Ordenes no tiene una columna para almacenar el estado.",
            null,
            500
        );
    }

    if (!$stmt->execute()) {

        $error = $stmt->error;

        $stmt->close();

        responderOrdenes(
            false,
            "No se pudo actualizar el estado: " .
            $error,
            null,
            500
        );
    }

    $filasAfectadas =
        $stmt->affected_rows;

    $stmt->close();

    if ($filasAfectadas <= 0) {

        $sqlExiste = "
            SELECT Id_orden
            FROM Ordenes
            WHERE Id_orden = ?
            LIMIT 1
        ";

        $stmtExiste =
            $conn->prepare($sqlExiste);

        if (!$stmtExiste) {

            responderOrdenes(
                false,
                "No se pudo verificar la orden.",
                null,
                500
            );
        }

        $stmtExiste->bind_param(
            "i",
            $idOrden
        );

        $stmtExiste->execute();

        $resultado =
            $stmtExiste->get_result();

        $existe =
            $resultado->num_rows > 0;

        $stmtExiste->close();

        if (!$existe) {

            responderOrdenes(
                false,
                "La orden indicada no existe.",
                null,
                404
            );
        }
    }

    registrarHistorialOrdenes(
        $conn,
        $idOrden,
        $estadoAnterior,
        $estadoGuardado,
        $descripcion
    );

    responderOrdenes(
        true,
        "Estado actualizado correctamente.",
        [
            "Id_orden" => $idOrden,
            "Estado_anterior" =>
                $estadoAnterior,
            "Estado_nuevo" =>
                $estadoGuardado
        ]
    );
}


/*==================================================
LISTAR ESTADOS PARA EL SELECT
==================================================*/

function listarEstadosTecnico(
    mysqli $conn
): never {

    $estados =
        listarEstadosDisponibles(
            $conn
        );

    if (count($estados) === 0) {

        responderOrdenes(
            false,
            "No se encontraron estados en Estado_mantenimiento.",
            [],
            404
        );
    }

    responderOrdenes(
        true,
        "Estados cargados correctamente.",
        $estados
    );
}


/*==================================================
CONTROLADOR PRINCIPAL
==================================================*/

if (
    !existeTablaOrdenes(
        $conn,
        "Ordenes"
    )
) {

    responderOrdenes(
        false,
        "La tabla Ordenes no existe en la base de datos.",
        null,
        500
    );
}


switch ($action) {

    case "listar":

        listarOrdenesTecnico(
            $conn
        );


    case "obtener":

        obtenerOrdenTecnico(
            $conn
        );


    case "guardar":

        guardarOrdenTecnico(
            $conn
        );


    case "actualizar_estado":

        actualizarEstadoOrdenTecnicoAPI(
            $conn
        );


    case "estados":

        listarEstadosTecnico(
            $conn
        );


    default:

        responderOrdenes(
            false,
            "Acción no válida.",
            null,
            400
        );
}