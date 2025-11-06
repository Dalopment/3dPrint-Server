<?php
include "cors.php";     //Carga configuracion para permitir peticiones desde el frontend
include "conexion.php";     //Carga la conexion a la base de datos
header('Content-Type:application/json; charset=utf-8');     //Reglas al navegador


$datos = json_decode(file_get_contents("php://input"), true);       //Lee los datos del frontend y los guarda en un array

// Validación básica - Verifica si algun campo esta vacio - "die" detiene el escript y responde con un error
if(empty($datos['nombre']) || empty($datos['correo']) || empty($datos['asunto'])){
    die(json_encode(["status" => "error", "mensaje" => "Faltan datos"]));
}


// Verificacion del email - FILTER_VALIDATE_EMAIL verifica el formato del email
if(!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)){
    die(json_encode(["status" => "error", "mensaje" => "Email inválido"]));
}


// Prepared Statement - Prepara una consula segura (evita inyecciones SQL)
$stmt = $conn->prepare("INSERT INTO mensajes_contacto (nombre, correo, asunto, fecha_envio) VALUES (?,?,?, NOW())");
$stmt -> bind_param("sss", $datos['nombre'], $datos['correo'], $datos['asunto']); // Vincula las variables PHP a los ? de la consulta


// Ejecuta y responde utilizando un operador ternario
echo json_encode($stmt->execute()
    ?["status" => "success"]
    :["status" => "error", "mensaje" => $stmt -> error]
);


// Limpiar y cerrar
$stmt->close(); // Cierra el prepared statement (libera memoria)
$conn->close(); // Cierra la conexión a la base de datos

?>

