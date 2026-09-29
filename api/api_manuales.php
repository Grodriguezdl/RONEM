<?php

declare(strict_types=1);

/*==================================================
INICIAR SESIÓN
==================================================*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


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

function responderManuales(
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
VALIDAR MÉTODO HTTP
==================================================*/

function exigirMetodoManuales(
    string $metodo
): void {

    $metodoActual = strtoupper(
        (string)($_SERVER["REQUEST_METHOD"] ?? "")
    );

    if ($metodoActual !== strtoupper($metodo)) {

        responderManuales(
            false,
            "Método HTTP no permitido.",
            null,
            405
        );

    }
}


/*==================================================
OBTENER ID DEL USUARIO EN SESIÓN
==================================================*/

function obtenerIdUsuarioSesion(): ?int
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

    $idUsuario = (int)$idUsuario;

    return $idUsuario > 0
        ? $idUsuario
        : null;
}


/*==================================================
OBTENER VALOR POST
==================================================*/

function obtenerPostManual(
    array $nombres,
    string $valorPredeterminado = ""
): string {

    foreach ($nombres as $nombre) {

        if (!array_key_exists($nombre, $_POST)) {
            continue;
        }

        $valor = trim(
            (string)$_POST[$nombre]
        );

        return $valor;
    }

    return $valorPredeterminado;
}


/*==================================================
OBTENER ID DEL MANUAL
==================================================*/

function obtenerIdManualPeticion(): int
{
    $posiblesValores = [
        $_POST["id"] ?? null,
        $_POST["id_manual"] ?? null,
        $_POST["Id_manual"] ?? null,
        $_GET["id"] ?? null,
        $_GET["id_manual"] ?? null,
        $_GET["Id_manual"] ?? null
    ];

    foreach ($posiblesValores as $valor) {

        if ($valor === null) {
            continue;
        }

        $id = (int)$valor;

        if ($id > 0) {
            return $id;
        }
    }

    return 0;
}


/*==================================================
OBTENER ARCHIVO PDF ENVIADO
==================================================*/

function obtenerArchivoPDFManual(): ?array
{
    $posiblesNombres = [
        "archivo",
        "Archivo",
        "pdf",
        "PDF",
        "documento",
        "Documento"
    ];

    foreach ($posiblesNombres as $nombre) {

        if (
            isset($_FILES[$nombre]) &&
            is_array($_FILES[$nombre])
        ) {
            return $_FILES[$nombre];
        }
    }

    return null;
}


/*==================================================
VALIDAR ARCHIVO PDF
==================================================*/

function validarArchivoPDFManual(
    array $archivo
): void {

    $error = (int)(
        $archivo["error"] ??
        UPLOAD_ERR_NO_FILE
    );

    if ($error === UPLOAD_ERR_NO_FILE) {

        responderManuales(
            false,
            "Debes seleccionar un archivo PDF.",
            null,
            422
        );

    }

    if ($error !== UPLOAD_ERR_OK) {

        $mensaje = match ($error) {

            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE =>
                "El archivo supera el tamaño permitido.",

            UPLOAD_ERR_PARTIAL =>
                "El archivo se subió parcialmente.",

            UPLOAD_ERR_NO_TMP_DIR =>
                "No existe una carpeta temporal para la carga.",

            UPLOAD_ERR_CANT_WRITE =>
                "No se pudo escribir el archivo en el servidor.",

            UPLOAD_ERR_EXTENSION =>
                "Una extensión del servidor bloqueó la carga.",

            default =>
                "Ocurrió un error al subir el archivo."
        };

        responderManuales(
            false,
            $mensaje,
            null,
            422
        );

    }

    $nombreOriginal = trim(
        (string)($archivo["name"] ?? "")
    );

    $rutaTemporal = trim(
        (string)($archivo["tmp_name"] ?? "")
    );

    $tamano = (int)(
        $archivo["size"] ?? 0
    );

    if (
        $nombreOriginal === "" ||
        $rutaTemporal === "" ||
        !is_uploaded_file($rutaTemporal)
    ) {

        responderManuales(
            false,
            "El archivo recibido no es válido.",
            null,
            422
        );

    }

    $extension = strtolower(
        pathinfo(
            $nombreOriginal,
            PATHINFO_EXTENSION
        )
    );

    if ($extension !== "pdf") {

        responderManuales(
            false,
            "Solo se permiten archivos PDF.",
            null,
            422
        );

    }

    $tamanoMaximo = 15 * 1024 * 1024;

    if ($tamano <= 0) {

        responderManuales(
            false,
            "El archivo PDF está vacío.",
            null,
            422
        );

    }

    if ($tamano > $tamanoMaximo) {

        responderManuales(
            false,
            "El PDF no puede superar los 15 MB.",
            null,
            422
        );

    }

    if (class_exists("finfo")) {

        $finfo = new finfo(
            FILEINFO_MIME_TYPE
        );

        $tipoMime = $finfo->file(
            $rutaTemporal
        );

        $tiposPermitidos = [
            "application/pdf",
            "application/x-pdf"
        ];

        if (
            $tipoMime !== false &&
            !in_array(
                $tipoMime,
                $tiposPermitidos,
                true
            )
        ) {

            responderManuales(
                false,
                "El archivo seleccionado no es un PDF válido.",
                null,
                422
            );

        }
    }
}


/*==================================================
CREAR CARPETA DE MANUALES
==================================================*/

function obtenerCarpetaManuales(): string
{
    $carpeta = dirname(__DIR__) .
        DIRECTORY_SEPARATOR .
        "uploads" .
        DIRECTORY_SEPARATOR .
        "manuales" .
        DIRECTORY_SEPARATOR;

    if (!is_dir($carpeta)) {

        $creada = mkdir(
            $carpeta,
            0775,
            true
        );

        if (!$creada && !is_dir($carpeta)) {

            throw new RuntimeException(
                "No se pudo crear la carpeta de manuales."
            );

        }
    }

    if (!is_writable($carpeta)) {

        throw new RuntimeException(
            "La carpeta de manuales no tiene permisos de escritura."
        );

    }

    return $carpeta;
}


/*==================================================
GUARDAR ARCHIVO PDF
==================================================*/

function guardarArchivoPDFManual(
    array $archivo
): array {

    validarArchivoPDFManual(
        $archivo
    );

    $carpetaFisica =
        obtenerCarpetaManuales();

    $nombreSeguro =
        "manual_" .
        date("Ymd_His") .
        "_" .
        bin2hex(
            random_bytes(6)
        ) .
        ".pdf";

    $rutaFisica =
        $carpetaFisica .
        $nombreSeguro;

    $rutaPublica =
        "uploads/manuales/" .
        $nombreSeguro;

    if (
        !move_uploaded_file(
            (string)$archivo["tmp_name"],
            $rutaFisica
        )
    ) {

        throw new RuntimeException(
            "No se pudo guardar el archivo PDF."
        );

    }

    return [
        "ruta_fisica" => $rutaFisica,
        "ruta_publica" => $rutaPublica
    ];
}


/*==================================================
CONVERTIR RUTA PÚBLICA A RUTA FÍSICA
==================================================*/

function obtenerRutaFisicaManual(
    string $rutaPublica
): string {

    $rutaPublica = str_replace(
        "\\",
        "/",
        trim($rutaPublica)
    );

    $rutaPublica = ltrim(
        $rutaPublica,
        "/"
    );

    return dirname(__DIR__) .
        DIRECTORY_SEPARATOR .
        str_replace(
            "/",
            DIRECTORY_SEPARATOR,
            $rutaPublica
        );
}


/*==================================================
ELIMINAR ARCHIVO FÍSICO
==================================================*/

function eliminarArchivoManual(
    ?string $rutaPublica
): void {

    if (
        $rutaPublica === null ||
        trim($rutaPublica) === ""
    ) {
        return;
    }

    $rutaFisica =
        obtenerRutaFisicaManual(
            $rutaPublica
        );

    $carpetaPermitida = realpath(
        dirname(__DIR__) .
        DIRECTORY_SEPARATOR .
        "uploads" .
        DIRECTORY_SEPARATOR .
        "manuales"
    );

    $archivoReal = realpath(
        $rutaFisica
    );

    if (
        $carpetaPermitida === false ||
        $archivoReal === false
    ) {
        return;
    }

    $carpetaPermitida =
        rtrim(
            $carpetaPermitida,
            DIRECTORY_SEPARATOR
        ) .
        DIRECTORY_SEPARATOR;

    if (
        !str_starts_with(
            $archivoReal,
            $carpetaPermitida
        )
    ) {
        return;
    }

    if (is_file($archivoReal)) {
        @unlink($archivoReal);
    }
}


/*==================================================
VALIDAR CAMPOS DEL MANUAL
==================================================*/

function validarDatosManual(
    string $titulo,
    string $descripcion
): void {

    if ($titulo === "") {

        responderManuales(
            false,
            "El título del manual es obligatorio.",
            null,
            422
        );

    }

    if (mb_strlen($titulo) > 150) {

        responderManuales(
            false,
            "El título no puede superar los 150 caracteres.",
            null,
            422
        );

    }

    if (mb_strlen($descripcion) > 255) {

        responderManuales(
            false,
            "La descripción no puede superar los 255 caracteres.",
            null,
            422
        );

    }
}


/*==================================================
OBTENER MANUAL POR ID
==================================================*/

function obtenerManualPorId(
    mysqli $conn,
    int $idManual
): ?array {

    $sql = "
        SELECT
            Id_manual,
            Titulo,
            Descripcion,
            Archivo_URL,
            Id_usuario,
            Fecha,
            Estado
        FROM Manuales
        WHERE Id_manual = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        throw new RuntimeException(
            "No se pudo preparar la consulta del manual: " .
            $conn->error
        );

    }

    $stmt->bind_param(
        "i",
        $idManual
    );

    if (!$stmt->execute()) {

        $error = $stmt->error;

        $stmt->close();

        throw new RuntimeException(
            "No se pudo consultar el manual: " .
            $error
        );

    }

    $resultado = $stmt->get_result();

    $manual = $resultado->fetch_assoc();

    $stmt->close();

    return $manual ?: null;
}


/*==================================================
LISTAR MANUALES
==================================================*/

function listarManuales(
    mysqli $conn
): never {

    $sql = "
        SELECT
            m.Id_manual,
            m.Titulo,
            m.Descripcion,
            m.Archivo_URL,
            m.Id_usuario,
            m.Fecha,
            m.Estado,

            CONCAT(
                COALESCE(u.Nombre, ''),
                ' ',
                COALESCE(u.Apellido, '')
            ) AS Usuario

        FROM Manuales m

        LEFT JOIN Usuarios u
            ON u.Id_usuario = m.Id_usuario

        ORDER BY
            m.Fecha DESC,
            m.Id_manual DESC
    ";

    $resultado = $conn->query($sql);

    if (!$resultado) {

        throw new RuntimeException(
            "No se pudieron cargar los manuales: " .
            $conn->error
        );

    }

    $manuales = [];

    while (
        $fila = $resultado->fetch_assoc()
    ) {

        $fila["Id_manual"] =
            (int)$fila["Id_manual"];

        $fila["Id_usuario"] =
            $fila["Id_usuario"] !== null
                ? (int)$fila["Id_usuario"]
                : null;

        $fila["Estado"] =
            (int)$fila["Estado"];

        $fila["Usuario"] = trim(
            (string)$fila["Usuario"]
        );

        $manuales[] = $fila;
    }

    responderManuales(
        true,
        "Manuales cargados correctamente.",
        $manuales
    );
}


/*==================================================
GUARDAR MANUAL
==================================================*/

function guardarManual(
    mysqli $conn
): never {

    exigirMetodoManuales(
        "POST"
    );

    $titulo = obtenerPostManual(
        [
            "titulo",
            "Titulo",
            "nombre",
            "Nombre"
        ]
    );

    $descripcion = obtenerPostManual(
        [
            "descripcion",
            "Descripcion"
        ]
    );

    $estadoRecibido = obtenerPostManual(
        [
            "estado",
            "Estado"
        ],
        "1"
    );

    $estado =
        (int)$estadoRecibido === 1
            ? 1
            : 0;

    validarDatosManual(
        $titulo,
        $descripcion
    );

    $archivo =
        obtenerArchivoPDFManual();

    if ($archivo === null) {

        responderManuales(
            false,
            "Debes seleccionar un archivo PDF.",
            null,
            422
        );

    }

    $archivoGuardado =
        guardarArchivoPDFManual(
            $archivo
        );

    $rutaFisica =
        $archivoGuardado["ruta_fisica"];

    $rutaPublica =
        $archivoGuardado["ruta_publica"];

    $idUsuario =
        obtenerIdUsuarioSesion();

    $sql = "
        INSERT INTO Manuales
        (
            Titulo,
            Descripcion,
            Archivo_URL,
            Id_usuario,
            Estado
        )
        VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        if (is_file($rutaFisica)) {
            @unlink($rutaFisica);
        }

        throw new RuntimeException(
            "No se pudo preparar el registro: " .
            $conn->error
        );

    }

    $stmt->bind_param(
        "sssii",
        $titulo,
        $descripcion,
        $rutaPublica,
        $idUsuario,
        $estado
    );

    if (!$stmt->execute()) {

        $error = $stmt->error;

        $stmt->close();

        if (is_file($rutaFisica)) {
            @unlink($rutaFisica);
        }

        throw new RuntimeException(
            "No se pudo guardar el manual: " .
            $error
        );

    }

    $idNuevo = (int)$stmt->insert_id;

    $stmt->close();

    responderManuales(
        true,
        "Manual agregado correctamente.",
        [
            "Id_manual" => $idNuevo,
            "Titulo" => $titulo,
            "Descripcion" => $descripcion,
            "Archivo_URL" => $rutaPublica,
            "Id_usuario" => $idUsuario,
            "Estado" => $estado
        ],
        201
    );
}


/*==================================================
EDITAR MANUAL
==================================================*/

function editarManual(
    mysqli $conn
): never {

    exigirMetodoManuales(
        "POST"
    );

    $idManual =
        obtenerIdManualPeticion();

    if ($idManual <= 0) {

        responderManuales(
            false,
            "El ID del manual no es válido.",
            null,
            422
        );

    }

    $manualActual =
        obtenerManualPorId(
            $conn,
            $idManual
        );

    if ($manualActual === null) {

        responderManuales(
            false,
            "El manual que intentas editar no existe.",
            null,
            404
        );

    }

    $titulo = obtenerPostManual(
        [
            "titulo",
            "Titulo",
            "nombre",
            "Nombre"
        ],
        (string)$manualActual["Titulo"]
    );

    $descripcion = obtenerPostManual(
        [
            "descripcion",
            "Descripcion"
        ],
        (string)$manualActual["Descripcion"]
    );

    $estadoRecibido = obtenerPostManual(
        [
            "estado",
            "Estado"
        ],
        (string)$manualActual["Estado"]
    );

    $estado =
        (int)$estadoRecibido === 1
            ? 1
            : 0;

    validarDatosManual(
        $titulo,
        $descripcion
    );

    $archivoNuevo =
        obtenerArchivoPDFManual();

    $hayArchivoNuevo =
        $archivoNuevo !== null &&
        (int)(
            $archivoNuevo["error"] ??
            UPLOAD_ERR_NO_FILE
        ) !== UPLOAD_ERR_NO_FILE;

    $rutaPublicaNueva =
        (string)$manualActual["Archivo_URL"];

    $rutaFisicaNueva = null;

    if ($hayArchivoNuevo) {

        $archivoGuardado =
            guardarArchivoPDFManual(
                $archivoNuevo
            );

        $rutaFisicaNueva =
            $archivoGuardado["ruta_fisica"];

        $rutaPublicaNueva =
            $archivoGuardado["ruta_publica"];
    }

    $sql = "
        UPDATE Manuales
        SET
            Titulo = ?,
            Descripcion = ?,
            Archivo_URL = ?,
            Estado = ?
        WHERE Id_manual = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        if (
            $rutaFisicaNueva !== null &&
            is_file($rutaFisicaNueva)
        ) {
            @unlink($rutaFisicaNueva);
        }

        throw new RuntimeException(
            "No se pudo preparar la actualización: " .
            $conn->error
        );

    }

    $stmt->bind_param(
        "sssii",
        $titulo,
        $descripcion,
        $rutaPublicaNueva,
        $estado,
        $idManual
    );

    if (!$stmt->execute()) {

        $error = $stmt->error;

        $stmt->close();

        if (
            $rutaFisicaNueva !== null &&
            is_file($rutaFisicaNueva)
        ) {
            @unlink($rutaFisicaNueva);
        }

        throw new RuntimeException(
            "No se pudo actualizar el manual: " .
            $error
        );

    }

    $stmt->close();

    if ($hayArchivoNuevo) {

        eliminarArchivoManual(
            (string)$manualActual["Archivo_URL"]
        );

    }

    responderManuales(
        true,
        "Manual actualizado correctamente.",
        [
            "Id_manual" => $idManual,
            "Titulo" => $titulo,
            "Descripcion" => $descripcion,
            "Archivo_URL" => $rutaPublicaNueva,
            "Estado" => $estado
        ]
    );
}


/*==================================================
ELIMINAR MANUAL
==================================================*/

function eliminarManual(
    mysqli $conn
): never {

    exigirMetodoManuales(
        "POST"
    );

    $idManual =
        obtenerIdManualPeticion();

    if ($idManual <= 0) {

        responderManuales(
            false,
            "El ID del manual no es válido.",
            null,
            422
        );

    }

    $manual =
        obtenerManualPorId(
            $conn,
            $idManual
        );

    if ($manual === null) {

        responderManuales(
            false,
            "El manual que intentas eliminar no existe.",
            null,
            404
        );

    }

    $sql = "
        DELETE FROM Manuales
        WHERE Id_manual = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        throw new RuntimeException(
            "No se pudo preparar la eliminación: " .
            $conn->error
        );

    }

    $stmt->bind_param(
        "i",
        $idManual
    );

    if (!$stmt->execute()) {

        $error = $stmt->error;

        $stmt->close();

        throw new RuntimeException(
            "No se pudo eliminar el manual: " .
            $error
        );

    }

    $filasAfectadas =
        $stmt->affected_rows;

    $stmt->close();

    if ($filasAfectadas <= 0) {

        responderManuales(
            false,
            "No se pudo eliminar el manual.",
            null,
            404
        );

    }

    eliminarArchivoManual(
        (string)$manual["Archivo_URL"]
    );

    responderManuales(
        true,
        "Manual eliminado correctamente.",
        [
            "Id_manual" => $idManual
        ]
    );
}


/*==================================================
CONTROLADOR PRINCIPAL
==================================================*/

try {

    switch ($action) {

        case "listar":

            listarManuales(
                $conn
            );

        case "guardar":

            guardarManual(
                $conn
            );

        case "editar":

            editarManual(
                $conn
            );

        case "eliminar":

            eliminarManual(
                $conn
            );

        default:

            responderManuales(
                false,
                "Acción no válida.",
                null,
                400
            );
    }

} catch (Throwable $error) {

    responderManuales(
        false,
        $error->getMessage(),
        null,
        500
    );
}