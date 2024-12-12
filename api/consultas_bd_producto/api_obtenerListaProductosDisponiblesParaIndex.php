<?php

//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_producto.php");


// Limite de productos 
$condicion_limit_producto = "LIMIT 10";


try {
    //Verifica si la solicitud es GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Se obtiene lista de productos
        $lista_productos = obtenerListaProductosDisponiblesParaIndex($conexion, $condicion_limit_producto);


        $lista_productos_imagenes = array();

        for ($i = 0; $i < count($lista_productos); $i++) {
            $id_producto = $lista_productos[$i]['id_publicacion_producto'];
            $lista_archivos = array();

            // Se obtiene la lista de imágenes del producto 
            $lista_imagenes = obtenerListaImgProducto($conexion, $id_producto);

            // Recorre la lista de imágenes y las agrega a $lista_archivos
            for ($j = 0; $j < count($lista_imagenes); $j++) {
                $lista_archivos[] = $lista_imagenes[$j];
            }

            // Agrega los detalles del producto y sus archivos $lista_productos_imagenes
            $lista_productos_imagenes[] = array(
                'detalles_producto' => $lista_productos[$i],
                'archivos' => $lista_archivos
            );
        }

        $respuesta['lista_productos'] = $lista_productos_imagenes;


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
