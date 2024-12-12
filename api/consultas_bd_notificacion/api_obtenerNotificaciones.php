<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_notificacion.php");
include("../../config/funciones/funciones_notificaciones.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_generales.php");


//Inicializacion de variables obtenidas de la solitud GET
$estado = isset($_GET['estado']) ? $_GET['estado'] : null;
$cant_dias = isset($_GET['cant_dias']) ? (int)$_GET['cant_dias'] : null;
$pagina_actual = isset($_GET['numero_pagina']) ? (int)$_GET['numero_pagina'] : 1;


//Campos esperados en la solicitud GET
$campo_esperados = array("id_usuario", "tipo_usuario");
$respuesta = [];


// Limite de limite_notificacion por pagina
$limite_notificacion = 8;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {


        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_GET);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Se obtiene los datos de la solicitud GET
        $id_usuario =  $_GET['id_usuario'];
        $tipo_usuario =  $_GET['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede ver las notificaciones debido a que su cuenta no esta activa o esta baneado");
        }

        //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
        $condicion_where = obtenerCondicionWhereNotificacion($estado, $cant_dias);

        //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y el limite de notificaciones
        $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_notificacion);

        //Se obtiene lista de las notificaciones disponibles
        $lista_notificaciones = obtenerListaDeTodasMisNotificacionesWWhereLimit($conexion, $id_usuario, $condicion_where, $condicion_limit);
        $respuesta['lista_notificaciones'] = $lista_notificaciones;


        //Verifica si es necesario cargar mas elementos
        $cantidad_notificaciones = count($lista_notificaciones);
        $cargar_mas = cargarMasElementos($cantidad_notificaciones, $limite_notificacion);
        $respuesta["cargar_mas_notificaciones"] =  $cargar_mas;

        //Determina si la busqueda esta activa
        $busqueda_activa = ($estado != 'todos') || ($cant_dias != 0);
        $respuesta["busqueda_activa"] =  $busqueda_activa;
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
