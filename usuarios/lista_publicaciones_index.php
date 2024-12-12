<?php
//Archivos de configuracion y funciones necesarias
include("../config/consultas_bd/conexion_bd.php");
include("../config/consultas_bd/consultas_usuario.php");
include("../config/consultas_bd/consultas_seguimiento.php");
include("../config/consultas_bd/consultas_publicaciones.php");
include("../config/funciones/funciones_generales.php");
include("../config/funciones/funciones_publicaciones.php");
include("../config/funciones/funciones_token.php");
include("../config/funciones/funciones_session.php");
include("../config/config_define.php");

// Obtener la pagina de actual de publicaciones
$pagina_actual = isset($_GET['pagina_publicacion']) ? $_GET['pagina_publicacion'] : 1;

// Limite de publicaciones por pagina
$limite_publicacion = 9;

$respuesta= [];



try {

    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario
    if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
        throw new Exception("Inicia sesion para poder ver la informacion de producto");
    }

    //Se obtiene los datos de sesion
    $id_usuario = $_SESSION["id_usuario"];
    $tipo_usuario = $_SESSION["tipo_usuario"];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario es valido
    if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
        throw new Exception("No se puede ver los datos de las publicaciones debido a que su cuenta no esta activa o esta baneado");
    }

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y el limite de publicaciones
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_publicacion);

    //Se obtiene una lista de las ultimas publicaciones
    $lista_publicaciones = obtenerPuInformacionDeLosEmprendedoresQueSiSegue($conexion, $id_usuario, $condicion_limit);

    //Verifica si es necesario cargar mas elementos
    $cantidad_publicaciones = count($lista_publicaciones);
    $cargar_mas = cargarMasElementos($cantidad_publicaciones, $limite_publicacion);
    $respuesta["cargar_mas_publicaciones_informacion"] =  $cargar_mas;

    //Si es la primera pagina de productos se agrega un titulo para la seccion de producto
    $card_datos = "";
    if ($pagina_actual == 1) {
        if ($cantidad_publicaciones > 0) {
            $card_datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12 mb-2'>";
            $card_datos .= "<h4 class='mb-0 text-center'>Ultimas publicaciones</h4>";
            $card_datos .= "</div>";
        }
    }

    //Genera las cards HTML para las publicaciones y añadirlas a la respuesta
    $card_datos .= cargarListaCardPublicacionesInicio($conexion, $lista_publicaciones, $pagina_actual, $limite_publicacion, $cargar_mas);
    $respuesta["cards"] = $card_datos;
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}



//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
