<?php

/*
 * Copia este archivo como conexion.php y coloca los datos de tu base de datos.
 * conexion.php está excluido de Git para no publicar credenciales.
 */

$host = "localhost";
$usuario = "root";
$password = "";
$database = "ronem";

$conn = new mysqli($host, $usuario, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
