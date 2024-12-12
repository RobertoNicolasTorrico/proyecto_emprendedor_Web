<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/funciones/funciones_preguntas_respuestas.php");
include("../../../../config/config_define.php");


//Inicializacion de variables obtenidas de la solicitud POST
$campo_buscar_producto = isset($_POST['campo_buscar_producto']) ? $_POST['campo_buscar_producto'] : '';
$campo_buscar_usuario = isset($_POST['campo_buscar_usuario']) ? $_POST['campo_buscar_usuario'] : '';
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$cant_registro = isset($_POST['cant_registro']) ? (int)$_POST['cant_registro'] : 10;
$estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 0;
$filtro_preguntas = isset($_POST['filtro_preguntas']) ? $_POST['filtro_preguntas'] : 'Todas';



$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';



try {

    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario
    if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
        throw new Exception("Debe iniciar sesion para poder ver las preguntas recibidas de los productos");
    }

    //Se obtiene los datos de sesion
    $id_usuario = $_SESSION['id_usuario'];
    $tipo_usuario = $_SESSION['tipo_usuario'];


    //Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario es valido
    if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
        throw new Exception("No se puede mostrar las preguntas recibidas debido a que su cuenta no esta activa o esta baneado");
    }

    //Verifica que el usuario sea un usuario emprendedor
    if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
        throw new Exception("No se puede ver las preguntas recibidas ya que no es un usuario emprendedor");
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
    $respuesta['cards'] = cargarListaCardRespuestaProductoEmprendedor($lista_preguntas, $busqueda_activa);

    //Se obtiene la cantidad actual de preguntas y respuesta segun las condiciones de busqueda
    $cantidad_preguntas = count($lista_preguntas);
    if ($cantidad_preguntas >= 1) {
        //Se obtiene la cantidad total de preguntas y respuesta segun las condiciones de busqueda
        $cantTotalPreguntas  = cantTotalListaPreguntasRespuestaWhere($conexion, $condicion_where, $id_usuario_emprendedor);

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantTotalPreguntas, $cant_registro, $pagina_actual, "nextPagePreguntasRespuesta");

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
