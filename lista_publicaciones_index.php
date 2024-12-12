<?php
//Archivos de configuracion y funciones necesarias
include("config/consultas_bd/conexion_bd.php");
include("config/consultas_bd/consultas_usuario.php");
include("config/consultas_bd/consultas_seguimiento.php");
include("config/consultas_bd/consultas_publicaciones.php");
include("config/funciones/funciones_generales.php");
include("config/funciones/funciones_token.php");
include("config/funciones/funciones_publicaciones.php");
include("config/funciones/funciones_session.php");
include("config/config_define.php");

// Se obtiene la pagina de actual de publicaciones
$pagina_actual = isset($_GET['pagina_publicacion']) ? $_GET['pagina_publicacion'] : 1;

// Limite de publicaciones por pagina
$limite_publicacion = 9;
$respuesta= [];

try {
    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y el limite de publicaciones
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_publicacion);

    //Se obtiene una lista de las ultimas publicaciones disponibles 
    $lista_publicaciones = obtenerListaUltimasPublicacionesGeneral($conexion, $condicion_limit);

    //Verifica si es necesario cargar mas elementos
    $cantidad_publicaciones = count($lista_publicaciones);
    $cargar_mas = cargarMasElementos($cantidad_publicaciones, $limite_publicacion);
    $respuesta["cargar_mas_publicaciones_informacion"] =  $cargar_mas;

    //Genera las cards HTML para las publicaciones y añadirlas a la respuesta
    $respuesta["cards"] = cargarListaCardPublicacionesInicio($conexion, $lista_publicaciones, $pagina_actual, $limite_publicacion, $cargar_mas);
    
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}
//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
