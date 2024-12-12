<?php
//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/consultas_producto.php");
include("../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/consultas_bd/consultas_usuario.php");
include("../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../config/funciones/funciones_preguntas_respuestas.php");
include("../../../config/funciones/funciones_generales.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/funciones/funciones_token.php");
include("../../../config/config_define.php");


//Inicializacion de variables obtenidas de la solicitud POST
$campo_buscar_producto = isset($_POST['campo_buscar_producto']) ? $_POST['campo_buscar_producto'] : "";
$campo_buscar_usuario = isset($_POST['campo_buscar_usuario']) ? $_POST['campo_buscar_usuario'] : "";
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : null;
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$cant_registro = isset($_POST['cant_registro']) ? (int)$_POST['cant_registro'] : 10;
$estado = isset($_POST['estado']) ? (int)$_POST['estado'] : null;
$filtro_preguntas = isset($_POST['filtro_preguntas']) ? $_POST['filtro_preguntas'] : 'Todas';

//Inicializacion de variables obtenidas de la URL
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';
$id_usuario_token = isset($_GET['token']) ? $_GET['token'] : '';

$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';



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

    //Se obtiene los datos del usuario emprendedor por el id del usuario
    $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
    if (empty($usuario_emprendedor)) {
        throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
    }
    $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];


    //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where = obtenerCondicionWhereBuscadorPreguntasRespuestasEmprendedor($campo_buscar_producto, $campo_buscar_usuario, $fecha, $estado, $filtro_preguntas);

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

    //Se obtiene lista de preguntas y respuestas que cumplen con las condiciones de busqueda
    $lista_preguntas = obtenerListaPreguntasRespuestaWhereLimit($conexion, $condicion_where, $condicion_limit, $id_usuario_emprendedor);

    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($campo_buscar_producto) || !empty($campo_buscar_usuario) || ($estado != 0) || ($filtro_preguntas != "Todas")  || !empty($fecha));

    //Genera las cards HTML para las preguntas y respuestas de los productos
    $respuesta['cards_respuesta'] = cargarListaCardRespuestaProductoEmprendedorAdmin($lista_preguntas, $busqueda_activa);


    //Se obtiene la cantidad actual de preguntas y respuesta segun las condiciones de busqueda
    $cantidad_preguntas = count($lista_preguntas);

    if ($cantidad_preguntas >= 1) {


        //Se obtiene la cantidad total de preguntas y respuesta segun las condiciones de busqueda
        $cantTotalPreguntas  = cantTotalListaPreguntasRespuestaWhere($conexion, $condicion_where, $id_usuario_emprendedor);

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantTotalPreguntas, $cant_registro, $pagina_actual, "nextPagePreguntasRecibidas");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantTotalPreguntas / $cant_registro);

        //Se obtiene la cantidad de preguntas que se va a mostrar en la interfaz del usuario
        $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantTotalPreguntas, $pagina_actual, $cantidad_preguntas);

        //Se guarda en respuesta un mensaje indicando la cantidad de preguntas que se ve en la interfaz del usuario
        $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantTotalPreguntas} preguntas";

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
