<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_publicaciones.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_publicaciones.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_token.php");
include("../../config/config_define.php");


// Se obtiene la pagina de actual de publicaciones
$pagina_actual = isset($_GET['pagina_actual']) ? $_GET['pagina_actual'] : 1;

// Limite de publicaciones por pagina
$limite_publicacion = 8;


$respuesta = [];

// Campos esperados en la solicitud GET
$campo_esperados = array('id_usuario_emprendedor');


try {
    //Verifica si la solicitud es GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {


        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_GET);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }


        //Se obtiene los datos de la solicitud GET
        $id_usuario_emprendedor_perfil = $_GET['id_usuario_emprendedor'];


        //Se verifica que la pagina sea un valor numerico 
        if (!is_numeric($pagina_actual)) {
            throw new Exception("La pagina de publicacion debe ser un numero");
        }


        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y el limite de publicaciones
        $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_publicacion);

        //Se obtiene una lista de las ultimas publicaciones disponibles 
        $lista_publicaciones = obtenerPublicacionesDelPerfilUsuarioEmprendedor($conexion, $id_usuario_emprendedor_perfil, $condicion_limit);


        $lista_publicaciones_archivo = array();
        for ($i = 0; $i < count($lista_publicaciones); $i++) {
            $id_publicacion = $lista_publicaciones[$i]['id_publicacion_informacion'];

            $lista_archivos = array();
            // Se obtiene la lista de archivos de la publicacion 
            $lista_archivos_publicacion = obtenerListaArchivosPublicaciones($conexion, $id_publicacion);

            // Recorre la lista de archivos y las agrega a $lista_archivos_publicacion
            for ($j = 0; $j < count($lista_archivos_publicacion); $j++) {
                $lista_archivos[] = $lista_archivos_publicacion[$j];
            }

            // Agrega los detalles de la publicacion y sus archivos $lista_publicaciones_archivo
            $lista_publicaciones_archivo[] = array(
                'detalles_publicaciones' => $lista_publicaciones[$i],
                'archivos' => $lista_archivos
            );
        }
        $respuesta['lista_publicaciones'] = $lista_publicaciones_archivo;


        //Verifica si es necesario cargar mas elementos
        $cantidad_publicaciones = count($lista_publicaciones);
        $cargar_mas = cargarMasElementos($cantidad_publicaciones, $limite_publicacion);
        $respuesta['cargar_mas_publicaciones'] = $cargar_mas;


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
