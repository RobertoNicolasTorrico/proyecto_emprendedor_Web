<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");

// Limite de publicacion 
$condicion_limit_emprendedores = "LIMIT 10";


try {
    //Verifica si la solicitud es GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Se obtiene  lista de emprendedores
        $lista_emprendedores = obtenerListaEmprendedoresActivosParaIndex($conexion, $condicion_limit_emprendedores);
        $respuesta['lista_emprendedores'] = $lista_emprendedores;
        http_response_code(200);
    } else {
        http_response_code(405);
        throw new Exception("Metodo no permitido o datos no recibidos");
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    http_response_code(400);
    $respuesta['mensaje'] = $e->getMessage();
}
//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
