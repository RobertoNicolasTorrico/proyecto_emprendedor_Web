<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_seguimiento.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_usuario.php");
include("../../config/funciones/funciones_emprendedores.php");
include("../../config/funciones/funciones_verificaciones.php");


// Se obtiene la pagina actual de seguidor y el campo busqueda del usuario
$campo_buscar_seguidor = isset($_GET['campo_buscar_seguidor']) ? $_GET['campo_buscar_seguidor'] : '';
$pagina_actual = isset($_GET['pagina_actual']) ? (int)$_GET['pagina_actual'] : 1;


// Limite de seguidores por pagina
$limite_seguidores = 8;

$respuesta = [];

// Campos esperados en la solicitud GET
$campo_esperados = array("id_usuario", "tipo_usuario");


try {
    //Verifica si la solicitud es GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_GET);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }


        //Se obtiene los datos de la solicitud GET
        $id_usuario = $_GET['id_usuario'];
        $tipo_usuario = $_GET['tipo_usuario'];


        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();


        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede ver la lista de seguidores debido a que su cuenta no esta activa o esta baneado");
        }


        //Verifica que el usuario sea un usuario emprendedor
        if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
            throw new Exception("No puede ver una lista de seguidores por que no es un usuario emprendedor");
        }


        //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
        $condicion_where =  obtenerCondicionWhereBuscadorUsuarioSeguidor($campo_buscar_seguidor);

        //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
        $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_seguidores);

        //Se obtiene lista de seguidores que cumplen con las condiciones de busqueda
        $lista_seguidores = obtenerListasSeguidoresDeEmprendedores($conexion, $id_usuario, $condicion_limit, $condicion_where);
        $respuesta["lista_seguidores"] =  $lista_seguidores;


        //Determina si la busqueda esta activa
        $busqueda_activa = !empty($campo_buscar_seguidor);
        $respuesta["busqueda_activa"] =  $busqueda_activa;


        //Se guarda la cantidad actual de seguidores
        $cantidad_seguidores = count($lista_seguidores);
        $respuesta["cant_seguidores"] = $cantidad_seguidores;

        //Verifica si es necesario cargar mas elementos
        $cargar_mas = cargarMasElementos($cantidad_seguidores, $limite_seguidores);
        $respuesta["cargar_mas_seguidores"] =  $cargar_mas;

        //Se guarda la cantidad total de seguidores
        $cant_total_seguidores = cantTotalSeguidoresUsuarioEmprendedor($conexion, $id_usuario);
        $respuesta["cant_total_seguidores"] =  $cant_total_seguidores;


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
