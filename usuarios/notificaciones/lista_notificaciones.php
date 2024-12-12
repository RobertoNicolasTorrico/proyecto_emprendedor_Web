<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_notificacion.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_session.php");
include("../../config/funciones/funciones_notificaciones.php");


//Inicializacion de variables obtenidas de la solitud POST
$estado = isset($_POST['estado']) ? $_POST['estado'] : 'todos';
$cant_dias = isset($_POST['cant_dias']) ? (int)$_POST['cant_dias'] : 0;
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;

// Limite de limite_notificacion por pagina
$limite_notificacion = 8;

$respuesta = [];

try {
    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario
    if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
        throw new Exception("Inicia sesion para poder ver la informacion de producto");
    }

    //Se obtiene los datos de sesion
    $id_usuario = $_SESSION["id_usuario"];
    $tipo_usuario = $_SESSION["tipo_usuario"];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario es valido
    if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
        throw new Exception("No se puede ver los datos de las notificaciones debido a que su cuenta no esta activa o esta baneado");
    }


    //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where = obtenerCondicionWhereNotificacion($estado, $cant_dias);

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y el limite de notificaciones
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_notificacion);

    //Se obtiene lista de las notificaciones disponibles
    $lista_notificaciones = obtenerListaDeTodasMisNotificacionesWWhereLimit($conexion, $id_usuario, $condicion_where, $condicion_limit);

    //Verifica si es necesario cargar mas elementos
    $cantidad_notificaciones = count($lista_notificaciones);
    $cargar_mas = cargarMasElementos($cantidad_notificaciones, $limite_notificacion);
    $respuesta["cargar_mas_notificaciones"] =  $cargar_mas;

    $buscar_notificacion = ($estado != 'todos') || ($cant_dias != 0);

    //Genera las cards HTML para las notificaciones y añadirlas a la respuesta
    $respuesta["cards_notificaciones"] = cargarCardNotificaciones($lista_notificaciones, $pagina_actual, $limite_notificacion, $cargar_mas, $buscar_notificacion);
} catch (Exception $e) {
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}


function cargarCardNotificaciones($lista_notificaciones, $pagina_actual, $limite_notificacion, $cargar_mas, $buscar_notificacion)
{
    $datos = '';
    $cant_notificacion = count($lista_notificaciones);
    $mensaje = "";
    $clase = "";
    $tipo_mensaje = "";

    //Verifica si hay notificaciones en la lista
    if ($cant_notificacion > 0) {
        for ($i = 0; $i < $cant_notificacion; $i++) {
            $leida = ($lista_notificaciones[$i]["leido"] != 0) ? true : false;
            $tiempo_transcurrido = obtenerTiempoTranscurrido($lista_notificaciones[$i]["fecha_notificacion"]);
            //Se verifica si la notificacion fue leida por el usuario
            if ($leida) {
                $clase = "notificacion_leida";
            } else {
                $clase = "notificacion_no_leida";
            }
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12' id='div_notificaciones-{$lista_notificaciones[$i]['id_notificacion']}'>";
            $datos .= "<div class='card mb-2 {$clase}' data-id={$lista_notificaciones[$i]['id_notificacion']}>"; //Card Inicio
            $datos .= "<div class='card-body pb-2 pt-2'>"; //Card Body Inicio

            //Contenedor que contiene el mensaje dependiendo del tipo de notificacion que sea y un boton para eliminar la notificacion
            $datos .= "<div class='d-flex justify-content-between'>";
            //Se verifica que tipo de mensaje mostrar dependiendo del tipo de notificacion
            switch ($lista_notificaciones[$i]['tipo_notificacion']) {
                case 'Seguimiento':
                    $tipo_mensaje = '<p><strong>' . $lista_notificaciones[$i]["nombre_usuario"] . ' </strong> comenzó a seguirte. <span class="text-secondary">' . $tiempo_transcurrido . '</span></p>';
                    break;

                case 'Pregunta':
                    $tipo_mensaje = '<p>Recibiste una nueva pregunta sobre tu producto <strong>' . $lista_notificaciones[$i]['nombre_producto'] . '</strong> del usuario <strong>' . $lista_notificaciones[$i]['nombre_usuario'] . '</strong>. <span class="text-secondary">' . $tiempo_transcurrido . '</span></p>';
                    break;

                case 'Respuesta':
                    $tipo_mensaje = '<p>Recibiste una respuesta sobre el producto <strong>' . $lista_notificaciones[$i]["nombre_producto"] . '</strong> del usuario <strong>' . $lista_notificaciones[$i]['nombre_usuario'] . '</strong>. <span class="text-secondary">' . $tiempo_transcurrido . '</span></p>';
                    break;
                default:
                    break;
            }
            $datos .= $tipo_mensaje;
            $datos .= "<small><button type='button' class='btn btn-outline-danger' data-bs-dismiss='modal' data-bs-toggle='modal'  data-bs-target='#eliminaModal' data-bs-id_notificacion='{$lista_notificaciones[$i]["id_notificacion"]}'  data-bs_mensaje='{$tipo_mensaje}' ><i class='fa-solid fa-trash'></i></button></small>";

            $datos .= "</div>";

            //Se verifica que la notificacion no este leida
            if (!$leida) {
                //Contenedor que muestra un circulo para mostrar que la notifacion no esta leida
                $datos .= "<div id='div_circulo_{$lista_notificaciones[$i]['id_notificacion']}'>";
                $datos .= "<span class='position-absolute top-0 start-100 translate-middle p-2 bg-secondary border border-light rounded-circle'>";
                $datos .= "<span class='visually-hidden'>alerts</span>";
                $datos .= "</span>";
                $datos .= "</div>";
            }
            $datos .= "</div>"; //Card Body Inicio
            $datos .= "</div>"; //Card Fin
            $datos .= "</div>";
        }
    }

    $tipo_mensaje=false;


    //Verifica si no hay mas notificaciones disponibles para cargar
    if (!$cargar_mas) {
        if ($cant_notificacion >= 0 && $cant_notificacion < $limite_notificacion) {
            $tipo_mensaje=true;
            $mensaje = "No hay mas notificaciones por el momento";
        }
    }


    //Verifica si no hay notificaciones disponibles para cargar
    if ($pagina_actual == 1 && $cant_notificacion == 0) {
        if ($buscar_notificacion) {
            $tipo_mensaje=false;
            $mensaje = "Sin resultados";
        } else {
            $mensaje = "No hay notificaciones por el momento";
        }
    }



    if (!empty($mensaje)) {
        $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
        if($tipo_mensaje){
            $datos .= "<p class='text-center'>{$mensaje}</p>";
        }else{
            $datos .= "<h3 class='text-center'>{$mensaje}</h3>";
        }
        $datos .= "</div>";
    }

    return $datos;
}



echo json_encode($respuesta);
