<?php
//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/consultas_bd/consultas_publicaciones.php");
include("../../../config/consultas_bd/consultas_usuario.php");
include("../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../config/funciones/funciones_generales.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/funciones/funciones_publicaciones.php");
include("../../../config/config_define.php");
include("../../../config/funciones/funciones_token.php");


//Inicializacion de variables obtenidas de la solicitud POST
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$cant_registro = isset($_POST['cant_registro']) ? (int)$_POST['cant_registro'] : 10;

$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';


//Inicializacion de variables obtenidas de la URL
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';
$id_usuario_token = isset($_GET['token']) ? $_GET['token'] : '';


try {

    //Se verifica que los datos recibidos de la URL sean validos
    verificarUrlTokenId($id_usuario, $id_usuario_token);

    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario administrador
    if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
        throw new Exception("Debe iniciar sesion para poder ver la lista de publicaciones del usuario");
    }

    //Se obtiene los datos de sesion
    $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
    $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario administrador es valido
    if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
        throw new Exception("No se puede ver la lista de publicaciones del usuario por que no es usuario administrador valido");
    }

    //Verifica si la cuenta del usuario sigue disponible
    if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
        throw new Exception("Esta cuenta fue eliminada previamente. Por favor regrese a la página anterior");
    }


    //Se obtiene la informacion del usuario emprendedor por medio de la Id del usuario
    $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
    if (empty($usuario_emprendedor)) {
        throw new Exception("No se pudo obtener la informacion del usuario emprendedor. Por favor intente mas tarde");
    }
    $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];


    //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where = obtenerCondicionWhereBuscadorPublicacion($fecha);

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

    //Se obtiene lista de publicaciones que cumplen con las condiciones de busqueda
    $lista_publicacion = obtenerListaPublicacionWhereLimit($conexion, $condicion_where, $condicion_limit, $id_usuario_emprendedor);

    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($fecha));

    //Genera las cards HTML para las publicaciones 
    $respuesta['card_publicaciones'] = cargarListaPublicacionesEmprendedor($conexion, $lista_publicacion, $busqueda_activa);


    //Se guarda la cantidad actual de publicaciones
    $cantidad_publicaciones = count($lista_publicacion);
    $respuesta['cantidad_actual'] = $cantidad_publicaciones;


    if ($cantidad_publicaciones >= 1) {

        //Se obtiene la cantidad total de publicaciones segun las condiciones de busqueda
        $cantTotalPublicaciones  = cantTotalPublicacionesWheUsuarioEmprendedor($conexion, $condicion_where, $id_usuario_emprendedor);

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantTotalPublicaciones, $cant_registro, $pagina_actual, "nextPagePublicaciones");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantTotalPublicaciones / $cant_registro);

        //Se obtiene la cantidad de publicaciones que se va a mostrar en la interfaz del usuario
        $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantTotalPublicaciones, $pagina_actual, $cantidad_publicaciones);

        //Se guarda en respuesta un mensaje indicando la cantidad de publicaciones que se ve en la interfaz del usuario
        $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantTotalPublicaciones} publicaciones";

        //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
        $respuesta['pagina'] = "Pagina {$pagina_actual} de {$totalPaginas}";
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}


//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
