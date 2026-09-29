<?php

header("Content-Type: application/json; charset=utf-8");

require_once "../config/conexion.php";

$action = $_GET["action"] ?? "";

switch ($action) {

    //==================================================
    // LISTAR NOTICIAS
    //==================================================
    case "listar":

        $sql = "
            SELECT
                Id_noticia,
                Imagen_URL,
                Descripcion,
                Fecha
            FROM Noticias
            ORDER BY Fecha DESC, Id_noticia DESC
        ";

        $resultado = $conn->query($sql);

        if (!$resultado) {
            http_response_code(500);

            echo json_encode([
                "error" => true,
                "mensaje" => "No se pudieron obtener las noticias."
            ]);

            exit;
        }

        $noticias = [];

        while ($fila = $resultado->fetch_assoc()) {
            $noticias[] = [
                "Id_noticia" => (int) $fila["Id_noticia"],
                "Imagen_URL" => $fila["Imagen_URL"],
                "Descripcion" => $fila["Descripcion"],
                "Fecha" => $fila["Fecha"]
            ];
        }

        echo json_encode(
            $noticias,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        break;

    //==================================================
    // GUARDAR O EDITAR NOTICIA
    //==================================================
    case "guardar":

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405);

            echo json_encode([
                "success" => false,
                "mensaje" => "Método no permitido."
            ]);

            exit;
        }

        $id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;
        $descripcion = trim($_POST["descripcion"] ?? "");
        $rutaImagen = "";

        if ($descripcion === "") {
            echo json_encode([
                "success" => false,
                "mensaje" => "La descripción es obligatoria."
            ]);

            exit;
        }

        //==================================================
        // SUBIR IMAGEN
        //==================================================
        if (
            isset($_FILES["imagen"]) &&
            $_FILES["imagen"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES["imagen"]["error"] !== UPLOAD_ERR_OK) {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Ocurrió un error al subir la imagen."
                ]);

                exit;
            }

            $tiposPermitidos = [
                "image/jpeg" => "jpg",
                "image/png" => "png",
                "image/webp" => "webp"
            ];

            $tipoMime = mime_content_type($_FILES["imagen"]["tmp_name"]);

            if (!isset($tiposPermitidos[$tipoMime])) {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Solo se permiten imágenes JPG, PNG o WEBP."
                ]);

                exit;
            }

            if ($_FILES["imagen"]["size"] > 5 * 1024 * 1024) {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "La imagen no debe superar los 5 MB."
                ]);

                exit;
            }

            $carpetaFisica = "../uploads/";

            if (!is_dir($carpetaFisica)) {
                mkdir($carpetaFisica, 0775, true);
            }

            $extension = $tiposPermitidos[$tipoMime];

            $nombreArchivo =
                "noticia_" .
                date("Ymd_His") .
                "_" .
                bin2hex(random_bytes(4)) .
                "." .
                $extension;

            $destinoFisico = $carpetaFisica . $nombreArchivo;

            if (
                !move_uploaded_file(
                    $_FILES["imagen"]["tmp_name"],
                    $destinoFisico
                )
            ) {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "No se pudo guardar la imagen."
                ]);

                exit;
            }

            // Ruta que se guardará en MySQL.
            // Funciona porque index.php se encuentra en la raíz.
            $rutaImagen = "uploads/" . $nombreArchivo;
        }

        //==================================================
        // INSERTAR
        //==================================================
        if ($id === 0) {

            if ($rutaImagen === "") {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Debe seleccionar una imagen."
                ]);

                exit;
            }

            $sql = "
                INSERT INTO Noticias (
                    Imagen_URL,
                    Descripcion,
                    Fecha
                )
                VALUES (?, ?, NOW())
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "No se pudo preparar el registro."
                ]);

                exit;
            }

            $stmt->bind_param(
                "ss",
                $rutaImagen,
                $descripcion
            );

            if ($stmt->execute()) {
                echo json_encode([
                    "success" => true,
                    "mensaje" => "Noticia registrada correctamente."
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "No se pudo registrar la noticia."
                ]);
            }

            $stmt->close();
            break;
        }

        //==================================================
        // EDITAR CON NUEVA IMAGEN
        //==================================================
        if ($rutaImagen !== "") {

            $sqlAnterior = "
                SELECT Imagen_URL
                FROM Noticias
                WHERE Id_noticia = ?
            ";

            $stmtAnterior = $conn->prepare($sqlAnterior);
            $stmtAnterior->bind_param("i", $id);
            $stmtAnterior->execute();

            $resultadoAnterior = $stmtAnterior->get_result();
            $noticiaAnterior = $resultadoAnterior->fetch_assoc();

            $stmtAnterior->close();

            $sql = "
                UPDATE Noticias
                SET
                    Imagen_URL = ?,
                    Descripcion = ?,
                    Fecha = NOW()
                WHERE Id_noticia = ?
            ";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "ssi",
                $rutaImagen,
                $descripcion,
                $id
            );

            if ($stmt->execute()) {

                if (
                    $noticiaAnterior &&
                    !empty($noticiaAnterior["Imagen_URL"])
                ) {
                    $archivoAnterior =
                        "../" . $noticiaAnterior["Imagen_URL"];

                    if (
                        is_file($archivoAnterior) &&
                        file_exists($archivoAnterior)
                    ) {
                        unlink($archivoAnterior);
                    }
                }

                echo json_encode([
                    "success" => true,
                    "mensaje" => "Noticia actualizada correctamente."
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "No se pudo actualizar la noticia."
                ]);
            }

            $stmt->close();
            break;
        }

        //==================================================
        // EDITAR SIN CAMBIAR IMAGEN
        //==================================================
        $sql = "
            UPDATE Noticias
            SET
                Descripcion = ?,
                Fecha = NOW()
            WHERE Id_noticia = ?
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "si",
            $descripcion,
            $id
        );

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "mensaje" => "Noticia actualizada correctamente."
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "mensaje" => "No se pudo actualizar la noticia."
            ]);
        }

        $stmt->close();

        break;

    //==================================================
    // ELIMINAR NOTICIA
    //==================================================
    case "eliminar":

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405);

            echo json_encode([
                "success" => false,
                "mensaje" => "Método no permitido."
            ]);

            exit;
        }

        $id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

        if ($id <= 0) {
            echo json_encode([
                "success" => false,
                "mensaje" => "ID de noticia inválido."
            ]);

            exit;
        }

        $sqlBuscar = "
            SELECT Imagen_URL
            FROM Noticias
            WHERE Id_noticia = ?
        ";

        $stmtBuscar = $conn->prepare($sqlBuscar);
        $stmtBuscar->bind_param("i", $id);
        $stmtBuscar->execute();

        $resultadoBuscar = $stmtBuscar->get_result();
        $noticia = $resultadoBuscar->fetch_assoc();

        $stmtBuscar->close();

        $sqlEliminar = "
            DELETE FROM Noticias
            WHERE Id_noticia = ?
        ";

        $stmtEliminar = $conn->prepare($sqlEliminar);
        $stmtEliminar->bind_param("i", $id);

        if ($stmtEliminar->execute()) {

            if ($noticia && !empty($noticia["Imagen_URL"])) {
                $archivo = "../" . $noticia["Imagen_URL"];

                if (is_file($archivo) && file_exists($archivo)) {
                    unlink($archivo);
                }
            }

            echo json_encode([
                "success" => true,
                "mensaje" => "Noticia eliminada correctamente."
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "mensaje" => "No se pudo eliminar la noticia."
            ]);
        }

        $stmtEliminar->close();

        break;

    //==================================================
    // ACCIÓN INVÁLIDA
    //==================================================
    default:

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "mensaje" => "Acción no válida."
        ]);

        break;
}

$conn->close();