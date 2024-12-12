<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_seguimiento.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_emprendedores.php");
include("../../config/funciones/funciones_verificaciones.php");


// Se obtiene la pagina actual de seguidor y el campo busqueda de emprendedor
$campo_buscar_emprendedor = isset($_GET['campo_buscar_emprendedor']) ? $_GET['campo_buscar_emprendedor'] : '';
$pagina_actual = isset($_GET['pagina_actual']) ? $_GET['pagina_actual'] : 1;

// Limite de seguidos por pagina
$limite_seguidos = 8;

$respuesta = [];

//Campos esperados en la solicitud GET
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


        //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
        $condicion_where =  obtenerCondicionWhereBuscadorEmprendedorSeguidos($campo_buscar_emprendedor);

        //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
        $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_seguidos);

        //Se obtiene lista de seguidores que cumplen con las condiciones de busqueda
        $lista_seguidos = obtenerListaEmprendedoresSeguidos($conexion, $id_usuario, $condicion_limit, $condicion_where);
        foreach ($lista_seguidos as &$seguido) {
            $seguido["lo_sigue"] = true;
        }
        $respuesta['lista_seguidos'] = $lista_seguidos;


        //Determina si la busqueda esta activa
        $busqueda_activa = !empty($campo_buscar_emprendedor);
        $respuesta["busqueda_activa"] =  $busqueda_activa;


        //Verifica si es necesario cargar mas elementos
        $cantidad_seguidores = count($lista_seguidos);
        $cargar_mas = cargarMasElementos($cantidad_seguidores, $limite_seguidos);
        $respuesta["cargar_mas_seguidos"] =  $cargar_mas;


        //Se guarda la cantidad total de seguidos
        $cant_total_seguidos = cantTotalSeguimientoUsuario($conexion, $id_usuario);
        $respuesta["cant_total_seguidos"] = $cant_total_seguidos;

        //Se guarda la cantidad actual de seguidos
        $respuesta["cant_seguidos"] = $cantidad_seguidores;


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
