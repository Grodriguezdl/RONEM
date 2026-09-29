<?php

header("Content-Type: application/json; charset=utf-8");

mysqli_report(MYSQLI_REPORT_OFF);

require_once "../config/conexion.php";

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

if (!isset($conn) || !($conn instanceof mysqli)) {
    responder([
        "success" => false,
        "message" => "No se pudo establecer la conexión con la base de datos."
    ], 500);
}

$action = $_GET["action"] ?? "";

try {

    if ($action === "listar") {

        $sql = "
            SELECT
                p.Id_producto,
                p.Nombre,
                p.Descripcion,
                p.Precio,
                p.Stock,
                p.Id_categoria,
                c.Nombre AS Categoria,
                p.Estado
            FROM Productos AS p
            LEFT JOIN Categorias AS c
                ON p.Id_categoria = c.Id_categoria
            ORDER BY p.Id_producto DESC
        ";

        $resultado = $conn->query($sql);

        if (!$resultado) {
            responder([
                "success" => false,
                "message" => "Error al listar productos: " . $conn->error
            ], 500);
        }

        $productos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $fila["Id_producto"] = (int)$fila["Id_producto"];
            $fila["Precio"] = (float)$fila["Precio"];
            $fila["Stock"] = (int)$fila["Stock"];
            $fila["Id_categoria"] = $fila["Id_categoria"] !== null
                ? (int)$fila["Id_categoria"]
                : null;
            $fila["Estado"] = (int)$fila["Estado"];

            $productos[] = $fila;
        }

        responder($productos);
    }

    if ($action === "guardar") {

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            responder([
                "success" => false,
                "message" => "Método no permitido."
            ], 405);
        }

        $id = isset($_POST["id"])
            ? (int)$_POST["id"]
            : 0;

        $nombre = trim($_POST["nombre"] ?? "");
        $descripcion = trim($_POST["descripcion"] ?? "");
        $precio = isset($_POST["precio"])
            ? (float)$_POST["precio"]
            : -1;
        $stock = isset($_POST["stock"])
            ? (int)$_POST["stock"]
            : -1;
        $estado = isset($_POST["estado"])
            ? (int)$_POST["estado"]
            : 1;
        $categoria = trim($_POST["categoria"] ?? "");

        if ($nombre === "") {
            responder([
                "success" => false,
                "message" => "El nombre del producto es obligatorio."
            ], 400);
        }

        if ($precio < 0) {
            responder([
                "success" => false,
                "message" => "El precio no es válido."
            ], 400);
        }

        if ($stock < 0) {
            responder([
                "success" => false,
                "message" => "El stock no es válido."
            ], 400);
        }

        if ($categoria === "") {
            responder([
                "success" => false,
                "message" => "La categoría es obligatoria."
            ], 400);
        }

        $idCategoria = 0;

        if (ctype_digit($categoria)) {
            $idCategoria = (int)$categoria;
        } else {
            $stmtCategoria = $conn->prepare("
                SELECT Id_categoria
                FROM Categorias
                WHERE Nombre = ?
                LIMIT 1
            ");

            if (!$stmtCategoria) {
                responder([
                    "success" => false,
                    "message" => "Error al preparar la categoría: " . $conn->error
                ], 500);
            }

            $stmtCategoria->bind_param("s", $categoria);
            $stmtCategoria->execute();

            $resultadoCategoria = $stmtCategoria->get_result();

            if ($filaCategoria = $resultadoCategoria->fetch_assoc()) {
                $idCategoria = (int)$filaCategoria["Id_categoria"];
            }

            $stmtCategoria->close();
        }

        if ($idCategoria <= 0) {
            responder([
                "success" => false,
                "message" => "La categoría seleccionada no existe."
            ], 400);
        }

        $rutaImagen = "";

        if (
            isset($_FILES["imagen"]) &&
            $_FILES["imagen"]["error"] === UPLOAD_ERR_OK
        ) {
            $directorioFisico = __DIR__ . "/../uploads/";
            $directorioPublico = "uploads/";

            if (!is_dir($directorioFisico)) {
                mkdir($directorioFisico, 0777, true);
            }

            $extension = strtolower(
                pathinfo(
                    $_FILES["imagen"]["name"],
                    PATHINFO_EXTENSION
                )
            );

            $extensionesPermitidas = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];

            if (!in_array($extension, $extensionesPermitidas, true)) {
                responder([
                    "success" => false,
                    "message" => "El formato de imagen no es válido."
                ], 400);
            }

            $nombreArchivo =
                "prod_" .
                time() .
                "_" .
                uniqid() .
                "." .
                $extension;

            $rutaDestino =
                $directorioFisico .
                $nombreArchivo;

            if (
                !move_uploaded_file(
                    $_FILES["imagen"]["tmp_name"],
                    $rutaDestino
                )
            ) {
                responder([
                    "success" => false,
                    "message" => "No se pudo guardar la imagen."
                ], 500);
            }

            $rutaImagen =
                $directorioPublico .
                $nombreArchivo;
        }

        $resultadoColumna = $conn->query(
            "SHOW COLUMNS FROM Productos LIKE 'Imagen'"
        );

        $tieneImagen =
            $resultadoColumna &&
            $resultadoColumna->num_rows > 0;

        if ($id > 0) {

            if ($tieneImagen && $rutaImagen !== "") {
                $sql = "
                    UPDATE Productos
                    SET
                        Nombre = ?,
                        Descripcion = ?,
                        Precio = ?,
                        Stock = ?,
                        Id_categoria = ?,
                        Estado = ?,
                        Imagen = ?
                    WHERE Id_producto = ?
                ";

                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    responder([
                        "success" => false,
                        "message" => "Error al preparar la actualización: " . $conn->error
                    ], 500);
                }

                $stmt->bind_param(
                    "ssdiiisi",
                    $nombre,
                    $descripcion,
                    $precio,
                    $stock,
                    $idCategoria,
                    $estado,
                    $rutaImagen,
                    $id
                );
            } else {
                $sql = "
                    UPDATE Productos
                    SET
                        Nombre = ?,
                        Descripcion = ?,
                        Precio = ?,
                        Stock = ?,
                        Id_categoria = ?,
                        Estado = ?
                    WHERE Id_producto = ?
                ";

                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    responder([
                        "success" => false,
                        "message" => "Error al preparar la actualización: " . $conn->error
                    ], 500);
                }

                $stmt->bind_param(
                    "ssdiiii",
                    $nombre,
                    $descripcion,
                    $precio,
                    $stock,
                    $idCategoria,
                    $estado,
                    $id
                );
            }

            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();

                responder([
                    "success" => false,
                    "message" => "Error al actualizar el producto: " . $error
                ], 500);
            }

            $stmt->close();

            responder([
                "success" => true,
                "message" => "Producto actualizado correctamente."
            ]);
        }

        if ($tieneImagen) {
            $sql = "
                INSERT INTO Productos (
                    Nombre,
                    Descripcion,
                    Precio,
                    Stock,
                    Id_categoria,
                    Estado,
                    Imagen
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                responder([
                    "success" => false,
                    "message" => "Error al preparar el registro: " . $conn->error
                ], 500);
            }

            $stmt->bind_param(
                "ssdiiis",
                $nombre,
                $descripcion,
                $precio,
                $stock,
                $idCategoria,
                $estado,
                $rutaImagen
            );
        } else {
            $sql = "
                INSERT INTO Productos (
                    Nombre,
                    Descripcion,
                    Precio,
                    Stock,
                    Id_categoria,
                    Estado
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                responder([
                    "success" => false,
                    "message" => "Error al preparar el registro: " . $conn->error
                ], 500);
            }

            $stmt->bind_param(
                "ssdiii",
                $nombre,
                $descripcion,
                $precio,
                $stock,
                $idCategoria,
                $estado
            );
        }

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();

            responder([
                "success" => false,
                "message" => "Error al guardar el producto: " . $error
            ], 500);
        }

        $nuevoId = $stmt->insert_id;

        $stmt->close();

        responder([
            "success" => true,
            "message" => "Producto guardado correctamente.",
            "id" => $nuevoId
        ]);
    }

    if ($action === "eliminar") {

        $id = filter_input(
            INPUT_GET,
            "id",
            FILTER_VALIDATE_INT
        );

        if (!$id) {
            responder([
                "success" => false,
                "message" => "El ID del producto no es válido."
            ], 400);
        }

        $stmt = $conn->prepare("
            DELETE FROM Productos
            WHERE Id_producto = ?
        ");

        if (!$stmt) {
            responder([
                "success" => false,
                "message" => "Error al preparar la eliminación: " . $conn->error
            ], 500);
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();

            responder([
                "success" => false,
                "message" => "Error al eliminar el producto: " . $error
            ], 500);
        }

        if ($stmt->affected_rows === 0) {
            $stmt->close();

            responder([
                "success" => false,
                "message" => "El producto no existe o ya fue eliminado."
            ], 404);
        }

        $stmt->close();

        responder([
            "success" => true,
            "message" => "Producto eliminado correctamente."
        ]);
    }

    responder([
        "success" => false,
        "message" => "Acción inválida."
    ], 400);

} catch (Throwable $error) {

    responder([
        "success" => false,
        "message" => "Error del sistema: " . $error->getMessage()
    ], 500);

} finally {

    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}