<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_publicaciones.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/funciones/funciones_publicaciones.php");
include("../../../../config/config_define.php");


//Inicializacion de variables obtenidas de la solicitud POST
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$cant_registro = isset($_POST['cant_registro']) ? (int)$_POST['cant_registro'] : 5;

$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';
try {

    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario
    if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
        throw new Exception("Debe iniciar sesion para poder ver las publicaciones");
    }

    //Obtener los datos de sesion
    $id_usuario = $_SESSION['id_usuario'];
    $tipo_usuario = $_SESSION['tipo_usuario'];

    //Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario es valido
    if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
        throw new Exception("No se puede mostrar la informacion debido a que su cuenta no esta activa o esta baneado");
    }

    //Verifica que el usuario sea un usuario emprendedor
    if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
        throw new Exception("No se puede ver la informacion de las publicaciones ya que no es un usuario emprendedor");
    }


    //Obtener datos del usuario emprendedor por el id del usuario
    $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
    if (empty($usuario_emprendedor)) {
        throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
    }
    $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];



    //Obtener la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where = obtenerCondicionWhereBuscadorPublicacion($fecha);

    //Obtener la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

    //Obtener lista de publicaciones que cumplen con las condiciones de busqueda
    $lista_publicacion = obtenerListaPublicacionWhereLimit($conexion, $condicion_where, $condicion_limit, $id_usuario_emprendedor);

    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($fecha));

    //Genera las cards HTML para las publicaciones 
    $respuesta['card'] = cargarListaPublicacionesEmprendedor($conexion, $lista_publicacion, $busqueda_activa);


    //Se obtiene la cantidad actual de publicaciones segun las condiciones de busqueda
    $cantidad_publicaciones = count($lista_publicacion);

    //Se guarda la cantidad actual de publicaciones
    $respuesta['cantidad_actual'] = $cantidad_publicaciones;

    if ($cantidad_publicaciones >= 1) {


        //Se obtiene la cantidad total de publicaciones segun las condiciones de busqueda
        $cantTotalPublicaciones  = cantTotalPublicacionesWheUsuarioEmprendedor($conexion, $condicion_where, $id_usuario_emprendedor);

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantTotalPublicaciones, $cant_registro, $pagina_actual, "nextPagePublicacion");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantTotalPublicaciones / $cant_registro);

        //Se obtiene la cantidad de publicaciones que se va a mostrar en la interfaz del usuario
        $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantTotalPublicaciones, $pagina_actual, $cantidad_publicaciones);

        //Se guarda en respuesta un mensaje indicando la cantidad de publicaciones que se ve en la interfaz del usuario
        $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantTotalPublicaciones} publicaciones";

        //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
        $respuesta['pagina'] = "Pagina " . $pagina_actual  . ' de ' .  $totalPaginas;
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}


//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
