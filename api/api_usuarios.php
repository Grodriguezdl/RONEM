<?php

declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

require_once "../config/conexion.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn->set_charset("utf8mb4");

$action = $_GET["action"] ?? "";

function responder(
    bool $success,
    string $message,
    array $extra = [],
    int $codigo = 200
): void {

    http_response_code($codigo);

    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "message" => $message
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}

try {

    switch ($action) {

        //========================================
        // LISTAR USUARIOS
        //========================================

        case "listar":

            if ($_SERVER["REQUEST_METHOD"] !== "GET") {
                responder(
                    false,
                    "Método no permitido.",
                    [],
                    405
                );
            }

            $sql = "
                SELECT
                    u.Id_usuario,
                    u.Nombre,
                    u.Apellido,
                    u.Correo,
                    u.Activo,
                    r.Id_rol,
                    r.Nombre AS Rol
                FROM Usuarios AS u

                LEFT JOIN Roles_usuarios AS ru
                    ON ru.Id_usuario = u.Id_usuario

                LEFT JOIN Roles AS r
                    ON r.Id_rol = ru.Id_rol

                ORDER BY u.Id_usuario DESC
            ";

            $resultado = $conn->query($sql);

            $usuarios = [];

            while ($fila = $resultado->fetch_assoc()) {

                $usuarios[] = [
                    "Id_usuario" => (int) $fila["Id_usuario"],
                    "Nombre" => $fila["Nombre"] ?? "",
                    "Apellido" => $fila["Apellido"] ?? "",
                    "Correo" => $fila["Correo"] ?? "",
                    "Activo" => (int) ($fila["Activo"] ?? 0),
                    "Id_rol" => $fila["Id_rol"] !== null
                        ? (int) $fila["Id_rol"]
                        : null,
                    "Rol" => $fila["Rol"] ?? "Sin rol"
                ];
            }

            responder(
                true,
                "Usuarios cargados correctamente.",
                ["data" => $usuarios]
            );

        //========================================
        // LISTAR ROLES
        //========================================

        case "roles":

            if ($_SERVER["REQUEST_METHOD"] !== "GET") {
                responder(
                    false,
                    "Método no permitido.",
                    [],
                    405
                );
            }

            $resultado = $conn->query("
                SELECT
                    Id_rol,
                    Nombre
                FROM Roles
                ORDER BY Nombre ASC
            ");

            $roles = [];

            while ($fila = $resultado->fetch_assoc()) {

                $roles[] = [
                    "Id_rol" => (int) $fila["Id_rol"],
                    "Nombre" => $fila["Nombre"] ?? ""
                ];
            }

            responder(
                true,
                "Roles cargados correctamente.",
                ["data" => $roles]
            );

        //========================================
        // GUARDAR O EDITAR
        //========================================

        case "guardar":

            if ($_SERVER["REQUEST_METHOD"] !== "POST") {
                responder(
                    false,
                    "Método no permitido.",
                    [],
                    405
                );
            }

            $id = filter_input(
                INPUT_POST,
                "id",
                FILTER_VALIDATE_INT
            );

            $nombre = trim($_POST["nombre"] ?? "");
            $apellido = trim($_POST["apellido"] ?? "");
            $correo = strtolower(
                trim($_POST["correo"] ?? "")
            );

            $password = $_POST["password"] ?? "";

            $activo = filter_var(
                $_POST["estado"] ?? null,
                FILTER_VALIDATE_INT
            );

            $idRol = filter_var(
                $_POST["rol"] ?? null,
                FILTER_VALIDATE_INT
            );

            if (
                $nombre === "" ||
                $apellido === "" ||
                $correo === ""
            ) {
                responder(
                    false,
                    "Complete todos los campos obligatorios.",
                    [],
                    400
                );
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                responder(
                    false,
                    "El correo electrónico no es válido.",
                    [],
                    400
                );
            }

            if (!in_array($activo, [0, 1], true)) {
                responder(
                    false,
                    "El estado seleccionado no es válido.",
                    [],
                    400
                );
            }

            if (!$idRol || $idRol <= 0) {
                responder(
                    false,
                    "Seleccione un rol válido.",
                    [],
                    400
                );
            }

            if (!$id && strlen($password) < 8) {
                responder(
                    false,
                    "La contraseña debe contener al menos 8 caracteres.",
                    [],
                    400
                );
            }

            if (
                $id &&
                $password !== "" &&
                strlen($password) < 8
            ) {
                responder(
                    false,
                    "La nueva contraseña debe contener al menos 8 caracteres.",
                    [],
                    400
                );
            }

            // Comprobar que el rol exista
            $stmtRol = $conn->prepare("
                SELECT Id_rol
                FROM Roles
                WHERE Id_rol = ?
                LIMIT 1
            ");

            $stmtRol->bind_param("i", $idRol);
            $stmtRol->execute();

            if ($stmtRol->get_result()->num_rows === 0) {
                responder(
                    false,
                    "El rol seleccionado no existe.",
                    [],
                    400
                );
            }

            $stmtRol->close();

            // Comprobar correo duplicado
            if ($id) {

                $stmtCorreo = $conn->prepare("
                    SELECT Id_usuario
                    FROM Usuarios
                    WHERE Correo = ?
                    AND Id_usuario <> ?
                    LIMIT 1
                ");

                $stmtCorreo->bind_param(
                    "si",
                    $correo,
                    $id
                );

            } else {

                $stmtCorreo = $conn->prepare("
                    SELECT Id_usuario
                    FROM Usuarios
                    WHERE Correo = ?
                    LIMIT 1
                ");

                $stmtCorreo->bind_param(
                    "s",
                    $correo
                );
            }

            $stmtCorreo->execute();

            if ($stmtCorreo->get_result()->num_rows > 0) {

                responder(
                    false,
                    "El correo electrónico ya está registrado.",
                    [],
                    409
                );
            }

            $stmtCorreo->close();

            $conn->begin_transaction();

            //========================================
            // INSERTAR
            //========================================

            if (!$id) {

                $hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmtUsuario = $conn->prepare("
                    INSERT INTO Usuarios (
                        Nombre,
                        Apellido,
                        Correo,
                        Contrasena,
                        Activo
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmtUsuario->bind_param(
                    "ssssi",
                    $nombre,
                    $apellido,
                    $correo,
                    $hash,
                    $activo
                );

                $stmtUsuario->execute();

                $idUsuario = (int) $conn->insert_id;

                $stmtUsuario->close();

                $stmtRelacion = $conn->prepare("
                    INSERT INTO Roles_usuarios (
                        Id_usuario,
                        Id_rol
                    )
                    VALUES (?, ?)
                ");

                $stmtRelacion->bind_param(
                    "ii",
                    $idUsuario,
                    $idRol
                );

                $stmtRelacion->execute();
                $stmtRelacion->close();

                $conn->commit();

                responder(
                    true,
                    "Usuario registrado correctamente."
                );
            }

            //========================================
            // COMPROBAR USUARIO
            //========================================

            $stmtExiste = $conn->prepare("
                SELECT Id_usuario
                FROM Usuarios
                WHERE Id_usuario = ?
                LIMIT 1
            ");

            $stmtExiste->bind_param("i", $id);
            $stmtExiste->execute();

            if ($stmtExiste->get_result()->num_rows === 0) {

                $stmtExiste->close();
                $conn->rollback();

                responder(
                    false,
                    "El usuario no existe.",
                    [],
                    404
                );
            }

            $stmtExiste->close();

            //========================================
            // ACTUALIZAR
            //========================================

            if ($password !== "") {

                $hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmtUsuario = $conn->prepare("
                    UPDATE Usuarios
                    SET
                        Nombre = ?,
                        Apellido = ?,
                        Correo = ?,
                        Contrasena = ?,
                        Activo = ?
                    WHERE Id_usuario = ?
                ");

                $stmtUsuario->bind_param(
                    "ssssii",
                    $nombre,
                    $apellido,
                    $correo,
                    $hash,
                    $activo,
                    $id
                );

            } else {

                $stmtUsuario = $conn->prepare("
                    UPDATE Usuarios
                    SET
                        Nombre = ?,
                        Apellido = ?,
                        Correo = ?,
                        Activo = ?
                    WHERE Id_usuario = ?
                ");

                $stmtUsuario->bind_param(
                    "sssii",
                    $nombre,
                    $apellido,
                    $correo,
                    $activo,
                    $id
                );
            }

            $stmtUsuario->execute();
            $stmtUsuario->close();

            // Cambiar rol
            $stmtEliminarRol = $conn->prepare("
                DELETE FROM Roles_usuarios
                WHERE Id_usuario = ?
            ");

            $stmtEliminarRol->bind_param("i", $id);
            $stmtEliminarRol->execute();
            $stmtEliminarRol->close();

            $stmtRelacion = $conn->prepare("
                INSERT INTO Roles_usuarios (
                    Id_usuario,
                    Id_rol
                )
                VALUES (?, ?)
            ");

            $stmtRelacion->bind_param(
                "ii",
                $id,
                $idRol
            );

            $stmtRelacion->execute();
            $stmtRelacion->close();

            $conn->commit();

            responder(
                true,
                "Usuario actualizado correctamente."
            );

        //========================================
        // ELIMINAR
        //========================================

        case "eliminar":

            if ($_SERVER["REQUEST_METHOD"] !== "POST") {
                responder(
                    false,
                    "Método no permitido.",
                    [],
                    405
                );
            }

            $id = filter_input(
                INPUT_POST,
                "id",
                FILTER_VALIDATE_INT
            );

            if (!$id || $id <= 0) {
                responder(
                    false,
                    "El ID del usuario no es válido.",
                    [],
                    400
                );
            }

            $conn->begin_transaction();

            $stmtRol = $conn->prepare("
                DELETE FROM Roles_usuarios
                WHERE Id_usuario = ?
            ");

            $stmtRol->bind_param("i", $id);
            $stmtRol->execute();
            $stmtRol->close();

            $stmtUsuario = $conn->prepare("
                DELETE FROM Usuarios
                WHERE Id_usuario = ?
            ");

            $stmtUsuario->bind_param("i", $id);
            $stmtUsuario->execute();

            if ($stmtUsuario->affected_rows === 0) {

                $stmtUsuario->close();
                $conn->rollback();

                responder(
                    false,
                    "El usuario no existe.",
                    [],
                    404
                );
            }

            $stmtUsuario->close();
            $conn->commit();

            responder(
                true,
                "Usuario eliminado correctamente."
            );

        default:

            responder(
                false,
                "Acción no válida.",
                [],
                400
            );
    }

} catch (Throwable $error) {

    try {
        $conn->rollback();
    } catch (Throwable $ignorar) {
    }

    error_log($error->getMessage());

    responder(
        false,
        "Error del servidor: " . $error->getMessage(),
        [],
        500
    );

} finally {

    if (isset($conn)) {
        $conn->close();
    }
}