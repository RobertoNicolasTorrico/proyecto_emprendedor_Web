<?php
function obtenerConexionBD()
{
    try {
        $host = "localhost";
        $bd = "proyecto_emprendedor";
        $usuario = "root";
        $password = "root";
        $conexion = new PDO("mysql:host=$host;dbname=$bd", $usuario, $password);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;
    } catch (PDOException $e) {
        throw new Exception("No se pudo establecer la conexion con la base de datos.Por favor intentalo de nuevo mas tarde.",0,$e);
    }
}