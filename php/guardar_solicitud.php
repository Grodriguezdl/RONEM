<?php

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/conexion.php';

try {

    if (!isset($conn)) {
        throw new Exception("La conexión a la base de datos no existe.");
    }

    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dpi = trim($_POST['dpi'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $edad = intval($_POST['edad'] ?? 0);
    $genero = trim($_POST['genero'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    $id_nivel = intval($_POST['id_nivel'] ?? 0);
    $id_tipo_licencia = intval($_POST['id_tipo_licencia'] ?? 0);

    $nombre_emergencia = trim($_POST['nombre_emergencia'] ?? '');
    $contacto_emergencia = trim($_POST['contacto_emergencia'] ?? '');
    $condiciones_medicas = trim($_POST['condiciones_medicas'] ?? '');

    $acepta_terminos = intval($_POST['acepta_terminos'] ?? 0);

    $sql = "INSERT INTO Solicitudes_Escuela (
        Nombre,
        Apellido,
        DPI,
        Fecha_nacimiento,
        Edad,
        Genero,
        Telefono,
        Correo,
        Direccion,
        Id_nivel,
        Id_tipo_licencia,
        Nombre_emergencia,
        Contacto_emergencia,
        Condiciones_medicas,
        Acepta_terminos
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conn->error);
    }

    $stmt->bind_param(
        "ssssissssiisssi",
        $nombre,
        $apellido,
        $dpi,
        $fecha_nacimiento,
        $edad,
        $genero,
        $telefono,
        $correo,
        $direccion,
        $id_nivel,
        $id_tipo_licencia,
        $nombre_emergencia,
        $contacto_emergencia,
        $condiciones_medicas,
        $acepta_terminos
    );

    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }

    echo json_encode([
        "success" => true,
        "message" => "Solicitud enviada correctamente."
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}