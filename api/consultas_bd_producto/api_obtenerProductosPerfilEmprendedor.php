<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_productos.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_token.php");
include("../../config/config_define.php");


//Inicializacion de variables obtenidas de la solicitud GET
$campo_buscar_producto = isset($_GET['campo_buscar']) ? $_GET['campo_buscar'] : '';
$pagina_actual = isset($_GET['numero_pagina']) ? (int)$_GET['numero_pagina'] : 1;
$num_ordenamiento = isset($_GET['num_ordenamiento']) ? (int)$_GET['num_ordenamiento'] : 0;
$precio_minimo = isset($_GET['precio_minimo']) ? (float)$_GET['precio_minimo'] : null;
$precio_maximo = isset($_GET['precio_maximo']) ? (float)$_GET['precio_maximo'] : null;
$categoria = isset($_GET['num_categoria']) ? (int)$_GET['num_categoria'] : null;
$calificacion = isset($_GET['calificacion']) ? $_GET['calificacion'] : null;
$estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';

// Limite de productos por pagina
$limite_producto = 6;
$respuesta = [];


// Campos esperados en la solicitud 
$campo_esperados = array('id_usuario_emprendedor');

try {
    //Verifica si la solicitud es GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {


        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_GET);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Obtener los datos de la solicitud GET
        $id_usuario_emprendedor_perfil = $_GET['id_usuario_emprendedor'];


        //Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();


        //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
        $condicion_where =  obtenerCondicionWhereBuscadorProductoPerfilUsuario($campo_buscar_producto, $categoria, $calificacion, $precio_minimo, $precio_maximo, $estado);

        //Se obtiene la condicion ASC y DESC para la consulta en funcion del numero de ordenamiento 
        $condicion_ASC_DESC = obtenerCondicionASCDESCFiltroProducto($num_ordenamiento);

        //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
        $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_producto);


        //Se obtiene una lista de productos que cumplen con las condiciones de busqueda
        $lista_productos =  obtenerListaBusquedaProductoWhereASCDESCLimitPerfil($conexion, $id_usuario_emprendedor_perfil, $condicion_where, $condicion_ASC_DESC, $condicion_limit);


        //Determina si la busqueda esta activa
        $busqueda_activa = (!empty($campo_buscar_producto) || !empty($calificacion) || $categoria != 0 || $precio_minimo != null  || $precio_maximo != null || $estado != 'todos');
        $respuesta['busqueda_activa'] = $busqueda_activa;

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

        //Se obtiene la cantidad actual de productos segun las condiciones de busqueda
        $cantidad_productos = count($lista_productos);
        if ($cantidad_productos >= 1) {

            //Se obtiene la cantidad total de productos segun las condiciones de busqueda
            $cantidad_total_productos = cantTotalProductoDelEmprendedorPerfilWhere($conexion, $id_usuario_emprendedor_perfil, $condicion_where);
            $respuesta['cant_total_productos'] = $cantidad_total_productos;


            //Calcula el total de paginas
            $totalPaginas = ceil($cantidad_total_productos / $limite_producto);
            $respuesta['totalPaginas'] = $totalPaginas;

            //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
            $respuesta['pagina'] = "Pagina " . $pagina_actual  . " de " .  $totalPaginas;
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
