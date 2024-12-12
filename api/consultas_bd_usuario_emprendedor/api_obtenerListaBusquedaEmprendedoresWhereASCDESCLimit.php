<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_seguimiento.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_emprendedores.php");


//Inicializacion de variables obtenidas de la solicitud GET
$campo_buscar_emprendedor = isset($_GET['campo_buscar_emprendedor']) ? $_GET['campo_buscar_emprendedor'] : '';
$pagina_actual = isset($_GET['numero_pagina']) ? (int)$_GET['numero_pagina'] : 1;
$num_ordenamiento = isset($_GET['num_ordenamiento']) ? (int)$_GET['num_ordenamiento'] : 1;
$calificacion = isset($_GET['calificacion']) ? $_GET['calificacion'] : null;

$id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : null;
$tipo_usuario = isset($_GET['tipo_usuario']) ? $_GET['tipo_usuario'] : null;

// Limite de emprendedores por pagina
$limite_emprendedor = 9;

$respuesta = [];


try {
    //Verifica si la solicitud es GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {



        //Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
        $condicion_where =  obtenerCondicionWhereBuscadorEmprendedor($campo_buscar_emprendedor, $calificacion);

        //Se obtiene la condicion ASC y DESC para la consulta en funcion del numero de ordenamiento 
        $condicion_ASC_DESC = obtenerCondicionASCDESCFiltroEmprendedor($num_ordenamiento);

        //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
        $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_emprendedor);

        //Se obtiene lista de publicaciones que cumplen con las condiciones de busqueda
        $lista_emprendedores = obtenerListaBusquedaEmprendedoresWhereASCDESCLimit($conexion, $condicion_where, $condicion_ASC_DESC, $condicion_limit);

        //Determina si la busqueda esta activa
        $busqueda_activa = (!empty($campo_buscar_emprendedor) || $calificacion !== "todos_calificacion");
        $respuesta['busqueda_activa'] = $busqueda_activa;



        $usuario_valido = false;
        //Verifica si se recibio datos de usuario
        if ((!is_null($id_usuario)) && (!is_null($tipo_usuario))) {
            //Se obtiene el valor si el usuario es valido o no
            $usuario_valido = verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario);
        }

        foreach ($lista_emprendedores as &$emprendedor) {
            //Se verifica si el usuario es valido en caso que lo sea se verifica si sigue al emprendedor o no
            if ($usuario_valido) {
                $emprendedor["lo_sigue"] = verificarSiElUsuarioSigueAlEmprendedor($conexion, $emprendedor['id_usuario_emprendedor'], $id_usuario);
            } else {
                $emprendedor["lo_sigue"] = false;
            }
        }

        $respuesta['lista_emprendedores'] = $lista_emprendedores;

        $cantidad_emprendedores = count( $lista_emprendedores);

        if ($cantidad_emprendedores >= 1) {
            //Se guarda la cantidad total de emprendedores
            $cantidad_total_emprendedores = cantTotalListaBusquedaEmprendedoresWhere($conexion, $condicion_where);
            $respuesta['cantidad_total_emprendedores'] = $cantidad_total_emprendedores;

            //Calcula el total de paginas
            $totalPaginas = ceil($cantidad_total_emprendedores / $limite_emprendedor);
            $respuesta['total_paginas'] = $totalPaginas;


            //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
            $respuesta['pagina'] = "Pagina {$pagina_actual} de {$totalPaginas}";
        }


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
