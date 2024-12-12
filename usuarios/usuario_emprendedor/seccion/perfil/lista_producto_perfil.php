<?php
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/funciones/funciones_productos.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/config_define.php");


//Inicializacion de variables obtenidas de la solicitud POST
$campo_buscar_producto = isset($_POST['campo_buscar_producto']) ? $_POST['campo_buscar_producto'] : '';
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$num_ordenamiento = isset($_POST['num_ordenamiento']) ? (int)$_POST['num_ordenamiento'] : 0;
$precio_minimo = isset($_POST['precio_minimo']) ? (float)$_POST['precio_minimo'] : null;
$precio_maximo = isset($_POST['precio_maximo']) ? (float)$_POST['precio_maximo'] : null;
$categoria = isset($_POST['num_categoria']) ? (int)$_POST['num_categoria'] : null;
$estado = isset($_POST['estado']) ? $_POST['estado'] : null;
$calificacion = isset($_POST['calificacion']) ? $_POST['calificacion'] : null;

//Inicializacion de variables obtenidas de la URL
$id_usuario_emprendedor = isset($_GET['id']) ? $_GET['id'] : '';
$id_usuario_emprendedor_token = isset($_GET['token']) ? $_GET['token'] : '';

// Limite de productos por pagina
$limite_producto = 6;

$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';




try {
    //Se verifica que los datos recibidos de la URL sean validos
    verificarUrlTokenId($id_usuario_emprendedor, $id_usuario_emprendedor_token);


    //Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();


    //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where =  obtenerCondicionWhereBuscadorProductoPerfilUsuario($campo_buscar_producto, $categoria, $calificacion, $precio_minimo, $precio_maximo, $estado);

    //Se obtiene la condicion ASC y DESC para la consulta en funcion del numero de ordenamiento 
    $condicion_ASC_DESC = obtenerCondicionASCDESCFiltroProducto($num_ordenamiento);

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_producto);


    //Se obtiene una lista de productos que cumplen con las condiciones de busqueda
    $lista_productos =  obtenerListaBusquedaProductoWhereASCDESCLimitPerfil($conexion, $id_usuario_emprendedor, $condicion_where, $condicion_ASC_DESC, $condicion_limit);

    //Se obtiene la cantidad actual de productos segun las condiciones de busqueda
    $cantidad_productos = count($lista_productos);

    //Se obtiene la cantidad total de productos segun las condiciones de busqueda
    $cantidad_total_productos = cantTotalProductoDelEmprendedorPerfilWhere($conexion, $id_usuario_emprendedor, $condicion_where);

    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($campo_buscar_producto) || !empty($calificacion) || $categoria != 0 || $precio_minimo != 0  || $precio_maximo != 0 || $estado != 'todos');


    //Genera las cards HTML para las publicaciones de productos 
    $respuesta["cards"] = obtenerListaCardProductosPerfilEmprendedor($conexion, $lista_productos, $cantidad_total_productos, $busqueda_activa);


    if ($cantidad_productos >= 1) {

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantidad_total_productos, $limite_producto, $pagina_actual, "nextPageProducto");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantidad_total_productos / $limite_producto);

        //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
        $respuesta['pagina'] = "<p>Paginas {$pagina_actual} de {$totalPaginas} </p>";
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}

//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
