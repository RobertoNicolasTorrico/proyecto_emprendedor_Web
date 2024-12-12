<?php
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_categoria.php");
include("../../config/funciones/funciones_verificaciones.php");


// Campos esperados en la solicitud GET
$campo_esperados = array("id_usuario");

try {
    //Verifica si la solicitud es GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_GET);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Se obtiene los datos de la solicitud GET
        $id_usuario = $_GET['id_usuario'];

        //Se obtiene los datos de la lista de categorias de los productos publicados por el emprendedor 
        $categorias_producto = obtenerCategoriasDeProductosEmprendedor($conexion, $id_usuario);
        $respuesta['categorias_producto'] = $categorias_producto;
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
