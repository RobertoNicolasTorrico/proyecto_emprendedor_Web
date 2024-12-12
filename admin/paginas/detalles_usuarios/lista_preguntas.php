<?php

//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/consultas_bd/consultas_usuario.php");
include("../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../config/funciones/funciones_generales.php");
include("../../../config/funciones/funciones_preguntas_respuestas.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/funciones/funciones_token.php");
include("../../../config/config_define.php");


//Inicializacion de variables obtenidas de la solicitud POST
$campo_buscar = isset($_POST['campo_buscar']) ? $_POST['campo_buscar'] : '';
$cant_registro = isset($_POST['cant_registro']) ? (int)$_POST['cant_registro'] : 5;
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$cant_dias = isset($_POST['cant_dias']) ? (int)$_POST['cant_dias'] : null;
$estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 0;


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
        throw new Exception("Debe iniciar sesion para poder la lista de las preguntas");
    }

    //Se obtiene los datos de sesion
    $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
    $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario administrador es valido
    if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
        throw new Exception("No se puede ver la lista de las preguntas por que no es usuario administrador valido");
    }


    //Verifica si la cuenta del usuario sigue existe
    if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
        throw new Exception("Esta cuenta fue eliminada previamente. Por favor regrese a la página anterior");
    }

    //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where = obtenerCondicionWhereBuscadorProductoPregunta($campo_buscar, $cant_dias, $estado);

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

    //Se obtiene lista de preguntas y respuestas que cumplen con las condiciones de busqueda
    $lista_pregunta = obtenerListaMisPreguntasWhereLimit($conexion, $condicion_where, $condicion_limit, $id_usuario);


    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($campo_buscar) ||  $estado != 0);
    $respuesta['busqueda_activa'] = $busqueda_activa;


    //Se obtiene la cantidad actual de preguntas y respuesta segun las condiciones de busqueda
    $cantidad_preguntas = count($lista_pregunta);
    $respuesta['cantidad_actual'] = $cantidad_preguntas;


    //Genera las cards HTML para las preguntas y respuestas de los productos
    $respuesta['cards_pregunas'] = cargarListaCardPreguntasEmprendedorParaAdmin($lista_pregunta, $busqueda_activa);
    if ($cantidad_preguntas >= 1) {

        //Se obtiene la cantidad total de preguntas y respuesta segun las condiciones de busqueda
        $cantTotalPreguntas  = cantTotalMisPreguntasProductoWhe($conexion, $condicion_where, $id_usuario);

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantTotalPreguntas, $cant_registro, $pagina_actual, "nextPagePregunta");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantTotalPreguntas / $cant_registro);

        //Se obtiene la cantidad de preguntas que se va a mostrar en la interfaz del usuario
        $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantTotalPreguntas, $pagina_actual, $cantidad_preguntas);

        //Se guarda en respuesta un mensaje indicando la cantidad de preguntas que se ve en la interfaz del usuario
        $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantTotalPreguntas} preguntas";

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
