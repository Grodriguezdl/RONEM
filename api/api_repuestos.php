<?php
header("Content-Type: application/json");
require_once "../config/conexion.php";

$action = $_GET['action'] ?? '';

switch($action){

//======================
// LISTAR
//======================
case "listar":

$sql="SELECT *
FROM Repuestos
ORDER BY Id_repuesto DESC";

$res=$conn->query($sql);

$data=[];

while($fila=$res->fetch_assoc()){
    $data[]=$fila;
}

echo json_encode($data);

break;


//======================
// GUARDAR / EDITAR
//======================
case "guardar":

$id=$_POST['id'] ?? '';

$nombre=$_POST['nombre'];
$descripcion=$_POST['descripcion'];
$stock=$_POST['stock'];
$precio=$_POST['precio'];

if($id==""){

$stmt=$conn->prepare("
INSERT INTO Repuestos
(
Nombre,
Descripcion,
Stock,
Precio
)
VALUES
(
?,
?,
?,
?
)
");

$stmt->bind_param(
"ssid",
$nombre,
$descripcion,
$stock,
$precio
);

}else{

$stmt=$conn->prepare("
UPDATE Repuestos
SET
Nombre=?,
Descripcion=?,
Stock=?,
Precio=?
WHERE Id_repuesto=?
");

$stmt->bind_param(
"ssidi",
$nombre,
$descripcion,
$stock,
$precio,
$id
);

}

if($stmt->execute()){

echo json_encode([
"success"=>true
]);

}else{

echo json_encode([
"success"=>false,
"error"=>$stmt->error
]);

}

$stmt->close();

break;


//======================
// ELIMINAR
//======================
case "eliminar":

$id=$_GET['id'];

$stmt=$conn->prepare("
DELETE FROM Repuestos
WHERE Id_repuesto=?
");

$stmt->bind_param("i",$id);

if($stmt->execute()){

echo json_encode([
"success"=>true
]);

}else{

echo json_encode([
"success"=>false
]);

}

$stmt->close();

break;

}

$conn->close();
?>