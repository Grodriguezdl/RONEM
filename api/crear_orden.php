<?php
// api/crear_orden.php
header('Content-Type: application/json');

// Ajusta la ruta a tu conexión PDO actual
require_once __DIR__ . "/../config/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        $pdo->beginTransaction();

        // 1. Recibir datos del vehículo
        $id_cliente      = !empty($_POST['Id_cliente']) ? (int)$_POST['Id_cliente'] : NULL;
        $marca           = $_POST['Marca'] ?? NULL;
        $linea           = $_POST['Linea'] ?? NULL;
        $modelo          = !empty($_POST['Modelo']) ? (int)$_POST['Modelo'] : NULL;
        $color           = $_POST['Color'] ?? NULL;
        $placa           = trim($_POST['Placa']);
        $no_chasis       = $_POST['No_chasis'] ?? NULL;
        $tipo_vehiculo   = $_POST['Tipo_vehiculo'] ?? NULL;
        $cilindraje      = !empty($_POST['Cilindraje']) ? (int)$_POST['Cilindraje'] : NULL;
        $combustible     = $_POST['Combustible'] ?? NULL;
        $kilometraje     = !empty($_POST['Kilometraje']) ? (int)$_POST['Kilometraje'] : NULL;
        $estado_vehiculo = 1; // 1 = Activo

        // Verificar si la placa ya existe para no duplicar el vehículo
        $stmtCheck = $pdo->prepare("SELECT Id_vehiculo FROM vehiculos WHERE Placa = :placa LIMIT 1");
        $stmtCheck->execute([':placa' => $placa]);
        $vehiculoExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($vehiculoExistente) {
            $id_vehiculo = $vehiculoExistente['Id_vehiculo'];

            // Actualizamos kilometraje u otros datos si ya existe
            $sqlUpdate = "UPDATE vehiculos SET 
                            Kilometraje = :kilometraje,
                            Color = COALESCE(:color, Color)
                          WHERE Id_vehiculo = :id_vehiculo";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':kilometraje' => $kilometraje,
                ':color'       => $color,
                ':id_vehiculo' => $id_vehiculo
            ]);
        } else {
            // Insertar vehículo nuevo
            $sqlVehiculo = "INSERT INTO vehiculos (
                Id_cliente, Marca, Linea, Modelo, Color, Placa, 
                No_chasis, Tipo_vehiculo, Cilindraje, Combustible, Kilometraje, Estado
            ) VALUES (
                :id_cliente, :marca, :linea, :modelo, :color, :placa, 
                :no_chasis, :tipo_vehiculo, :cilindraje, :combustible, :kilometraje, :estado
            )";

            $stmtVehiculo = $pdo->prepare($sqlVehiculo);
            $stmtVehiculo->execute([
                ':id_cliente'    => $id_cliente,
                ':marca'         => $marca,
                ':linea'         => $linea,
                ':modelo'        => $modelo,
                ':color'         => $color,
                ':placa'         => $placa,
                ':no_chasis'     => $no_chasis,
                ':tipo_vehiculo' => $tipo_vehiculo,
                ':cilindraje'    => $cilindraje,
                ':combustible'   => $combustible,
                ':kilometraje'   => $kilometraje,
                ':estado'        => $estado_vehiculo
            ]);

            $id_vehiculo = $pdo->lastInsertId();
        }

        // 2. Crear la Orden de Trabajo (CORREGIDO SEGÚN image_5ecd3d.png)
        $descripcion = $_POST['falla_reportada'] ?? 'Revisión General';
        
        // Al llamarse 'Id_estado' en tu BD, asumo que es un número entero. 
        // Cambié el texto 'Pendiente' a un ID (ej. 1). Ajusta este número según tu catálogo de estados.
        $id_estado = 1; 

        // Actualizado al nombre de tabla 'Ordenes' y a las columnas exactas de la imagen 1
        $sqlOrden = "INSERT INTO Ordenes (Id_cliente, Id_vehiculo, Descripcion, Fecha_ingreso, Id_estado) 
                     VALUES (:id_cliente, :id_vehiculo, :descripcion, NOW(), :id_estado)";
        
        $stmtOrden = $pdo->prepare($sqlOrden);
        $stmtOrden->execute([
            ':id_cliente'  => $id_cliente,
            ':id_vehiculo' => $id_vehiculo,
            ':descripcion' => $descripcion,
            ':id_estado'   => $id_estado
        ]);

        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'Orden de trabajo creada correctamente']);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}