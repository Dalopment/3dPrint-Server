<?php
// conexion.php

$host = "localhost";
$usuario = "root";      // usuario de XAMPP
$contrasena = "";       // contraseña 
$base_datos = "3dprint";
$puerto = 3307;

// Crear la conexión
$conexion = new mysqli($host, $usuario, $contrasena, $base_datos, $puerto);

// Comprobar la conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} 

?>
