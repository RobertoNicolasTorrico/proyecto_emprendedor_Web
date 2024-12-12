<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/funciones/funciones_session.php");
include("../../config/funciones/funciones_token.php");
include("../../config/config_define.php");

$id_producto = isset($_GET['id']) ? $_GET['id'] : '';
$id_producto_token = isset($_GET['token']) ? $_GET['token'] : '';
$respuesta['pregunta'] = "";
$respuesta['estado'] = "";
$respuesta['mensaje'] =  "";

$preguntasUsuario = [];
$lista_preguntas = [];
$usuario_valido = false;
$elUsuarioLoPublico = false;
$datos = "";



try {

    //Se verifica que los datos recibidos de la URL sean validos
    verificarUrlTokenId($id_producto, $id_producto_token);

    //Establecer la sesion
    session_start();

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica los datos de sesion del usuario
    $usuario_inicio_sesion = verificarEntradaDatosSession(['id_usuario', 'tipo_usuario']);

    //Verifica si el usuario inicio sesion
    if ($usuario_inicio_sesion) {

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION['id_usuario'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        $usuario_valido = verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario);
        //Verifica si el usuario es valido
        if ($usuario_valido) {
            //Se obtiene el valor si el usuario publico el producto
            $el_usuario_publico = elUsuarioLoPublico($conexion, $id_producto, $id_usuario);
        }
    }


    //Verifica si el usuario es valido
    if ($usuario_valido) {
        //Verifica si el usuario publico el producto
        if ($el_usuario_publico) {

            //Se obtiene las preguntas para la interfaz del emprendedor que publico el producto
            $preguntasGenerales = obtenerListaPreguntasRespuestaProducto($conexion, $id_producto);
            $datos .= obtenerModalListaPreguntasRespuestaMiProducto($preguntasGenerales);
        } else {
            //Se obtiene las preguntas para la interfaz de usuario que inicio sesion
            $preguntasUsuario = obtenerListaPreguntasRespuestaProductoUsuario($conexion, $id_producto, $id_usuario);
            $cantidad_preguntas_usuario = count($preguntasUsuario);
            if ($cantidad_preguntas_usuario > 0) {
                $datos .= obtenerDivPreguntasUsuario($preguntasUsuario);
            }
            $preguntasGenerales = obtenerListaPreguntasRespuestaProductoSinElUsuario($conexion, $id_producto, $id_usuario);
            $estado_producto_finalizado = verificarSiElProductoEstaFinalizado($conexion, $id_producto);
            $datos .= obtenerDivPreguntasGeneralesUsuario($preguntasGenerales, $cantidad_preguntas_usuario, $estado_producto_finalizado);
        }
    } else {
        $estado_producto_finalizado = verificarSiElProductoEstaFinalizado($conexion, $id_producto);
        //Se obtiene las preguntas para la interfaz de usuario que no inicio sesion
        $preguntasGenerales = obtenerListaPreguntasRespuestaProducto($conexion, $id_producto);
        $datos .= obtenerDivPreguntasGenerales($preguntasGenerales, $estado_producto_finalizado);
    }


    $respuesta['pregunta'] = $datos;
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}



//Interfaz vista de contenedor de las preguntas si el usuario publico el producto
function obtenerModalListaPreguntasRespuestaMiProducto($preguntas)
{
    $datos = '';
    $cant_preguntas_total = count($preguntas);
    if ($cant_preguntas_total > 0) {
        $datos .= "<button type='button' class='btn btn-outline-primary btn-sm' data-bs-toggle='modal' data-bs-target='#preguntasRespuestaModal'>Ver todas las preguntas y respuestas de mi producto</button>";
        $datos .= "<div class='modal fade' id='preguntasRespuestaModal' tabindex='-1' aria-labelledby='preguntasRespuestaModalLabel' aria-hidden='false'>";
        $datos .= "<div class='modal-dialog modal-dialog-scrollable modal-lg'>";
        $datos .= "<div class='modal-content'>";
        $datos .= "<div class='modal-header'>";
        $datos .= "<h1 class='modal-title fs-5' id='preguntasRespuestaModalLabel'>Lista de preguntas recibidas del producto</h1>";
        $datos .= "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
        $datos .= "</div>";
        $datos .= "<div class='modal-body'>";
        $datos .= "<div id='alert_preguntas_respuesta'></div>";
        for ($i = 0; $i < $cant_preguntas_total; ++$i) {
            $datos .= "<div class='card mb-4'>";
            $datos .= "<div class='card-body'>";
            $datos .= "<p class='card-text text-break'><strong>Nombre de usuario:</strong>{$preguntas[$i]["nombre_usuario"]}</p>";
            $fecha_pregunta = date('d/m/Y H:i:s', strtotime($preguntas[$i]['fecha_pregunta']));
            $datos .= "<p class='card-text text-break'><strong>Pregunta:</strong>{$preguntas[$i]["pregunta"]}<span style='color: #8a8a8a;'>({$fecha_pregunta})</span></p>";

            if ($preguntas[$i]["respuesta"] != null && $preguntas[$i]["fecha_respuesta"]  != null) {
                $fecha_respuesta = date('d/m/Y H:i:s', strtotime($preguntas[$i]['fecha_respuesta']));
                $datos .= "<p class='card-text text-break'><strong>Respuesta:</strong>{$preguntas[$i]["respuesta"]}<span style='color: #8a8a8a;'>({$fecha_respuesta})</span></p>";
            }
            $datos .= "</div>";
            $datos .= "<div class='card-footer'>";
            $fecha = date('d/m/Y H:i:s', strtotime($preguntas[$i]['fecha_pregunta']));
            if ($preguntas[$i]["respuesta"] != null && $preguntas[$i]["fecha_respuesta"]  != null) {
                $datos .= "<button type='button' class='btn btn-outline-danger' data-bs-dismiss='modal' data-bs-toggle='modal'  data-bs-target='#eliminaModal'  data-bs-id_producto='{$preguntas[$i]["id_producto"]}' data-bs-id_pregunta='{$preguntas[$i]["id_pregunta_respuesta"]}' data-bs-fecha='{$fecha}' data-bs-respuesta='{$preguntas[$i]["respuesta"]}'> <i class='fa-solid fa-trash'></i> Eliminar Respuesta</button>";
            } else {
                $datos .= "<button type='button' class='btn btn-outline-success' data-bs-dismiss='modal' data-bs-toggle='modal' data-bs-target='#agregarModal' data-bs-fecha='{$fecha}' data-bs-nombre='{$preguntas[$i]["nombre_usuario"]}'data-bs-id_producto='{$preguntas[$i]["id_producto"]}' data-bs-id_pregunta='{$preguntas[$i]["id_pregunta_respuesta"]}' data-bs-pregunta='{$preguntas[$i]["pregunta"]}'><i class='fa-solid fa-circle-plus'></i> Agregar Respuesta</button>";
            }
            $datos .= "</div>";
            $datos .= "</div> ";
        }
        $datos .= "</div>";
        $datos .= "</div>";
        $datos .= "</div>";
        $datos .= "</div>";
    } else {
        $datos .= '<p>Este producto aun no recibio preguntas de otros usuarios</p>';
    }
    $datos .= '</div>';

    return  $datos;
}


//Interfaz vista de contenedor de las preguntas si es usuario 
function obtenerDivPreguntasUsuario($preguntasUsuario)
{
    $cant_max = 3;
    $datos = '';
    $cant_preguntas_total = count($preguntasUsuario);
    $cant_preguntas = $cant_preguntas_total > $cant_max ? $cant_max :  $cant_preguntas_total;
    $datos .= '<div id="preguntas_usuario">';
    $datos .= '<p>Tus preguntas</p>';
    $datos .=  obtenerCajaPregunta($preguntasUsuario, $cant_preguntas);
    if ($cant_preguntas_total > $cant_max) {
        $datos .= '<button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario" style="margin-left: 10px;">';
        $datos .= 'Ver todas mis preguntas';
        $datos .= '</button>';
        $datos .= obtenerModalListaPreguntas($preguntasUsuario, "Tus preguntas", "modalUsuario");
    }
    $datos .= '</div>';
    $datos .= '<br />';
    return $datos;
}

function obtenerDivPreguntasGeneralesUsuario($preguntasGenerales, $cantPreguntasUsuario, $estado_producto_finalizado)
{
    $cant_max = 5;
    $datos = '';
    $cant_preguntas_general = count($preguntasGenerales);
    $cant_preguntas_total = $cant_preguntas_general + $cantPreguntasUsuario;
    $cant_preguntas = $cant_preguntas_general > $cant_max ? $cant_max :  $cant_preguntas_general;
    $datos .= '<div id="preguntas_general">';
    if ($cant_preguntas_total > 0) {
        $datos .= '<p><b>Últimas preguntas realizadas</b></p>';
        if ($cant_preguntas_general > 0) {
            $datos .=  obtenerCajaPregunta($preguntasGenerales, $cant_preguntas);
            if ($cant_preguntas_general > $cant_max) {
                $datos .= '<button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPreguntasSinUsuario">';
                $datos .= 'Ver todas las preguntas';
                $datos .= '</button>';
                $datos .= obtenerModalListaPreguntas($preguntasGenerales, "Últimas preguntas realizadas", "modalPreguntasSinUsuario");
            }
        }
    } else {
        if ($estado_producto_finalizado) {
            $datos .= '<p>Este producto no recibio preguntas</p>';
        } else {
            $datos .= '<p>Nadie hizo preguntas todavía.¡Hacé la primera!</p>';
        }
    }
    $datos .= '</div>';
    return $datos;
}



//Interfaz vista de contenedor de las preguntas si no usuario 
function obtenerDivPreguntasGenerales($preguntasGenerales, $estado_producto_finalizado)
{
    $cant_max = 5;
    $datos = '';
    $cant_preguntas_total = count($preguntasGenerales);
    $datos .= '<div id="preguntas_generales" style="padding-top: 18px;">';
    if ($cant_preguntas_total != 0) {
        $cant_preguntas = $cant_preguntas_total > $cant_max ? $cant_max :  $cant_preguntas_total;
        $datos .= '<p><b>Últimas preguntas realizadas</b></p>';
        $datos .=  obtenerCajaPregunta($preguntasGenerales, $cant_preguntas);
        if ($cant_preguntas_total > $cant_max) {
            $datos .= '<button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPreguntasGeneral" style="margin-left: 10px;">';
            $datos .= 'Ver todas las preguntas';
            $datos .= '</button>';
            $datos .= obtenerModalListaPreguntas($preguntasGenerales, "Últimas preguntas realizadas", "modalPreguntasGeneral");
        }
    } else {
        if ($estado_producto_finalizado) {
            $datos .= '<p>Este producto no recibio preguntas</p>';
        } else {
            $datos .= '<p>Nadie hizo preguntas todavía.¡Hacé la primera!</p>';
        }
    }
    $datos .= '</div>';

    return $datos;
}


//Interfaz usados para los contenedores de preguntas
function obtenerModalListaPreguntas($preguntas, $titulo, $id)
{
    $datos = '';
    $datos .= '<div class="modal fade" id="' . $id . '" tabindex="-1" aria-labelledby="' . $id . 'Label" aria-hidden="true" style="display: none;">';
    $datos .= '<div class="modal-dialog modal-dialog-scrollable modal-lg">';
    $datos .= '<div class="modal-content">';
    $datos .= '<div class="modal-header">';
    $datos .= '<h1 class="modal-title fs-5" id="' . $id . 'Label">' . $titulo . '</h1>';
    $datos .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
    $datos .= '</div>';
    $datos .= '<div class="modal-body">';
    $datos .= obtenerCajaPregunta($preguntas,  count($preguntas));
    $datos .= '</div>';

    $datos .= '</div>';
    $datos .= '</div>';
    $datos .= '</div>';
    return  $datos;
}

function obtenerCajaPregunta($pregunta, $cantidad)
{
    $datos = "";
    for ($i = 0; $i < $cantidad; ++$i) {
        $datos .= '<ul class="list-group text-break">';
        $datos .= '<li class="list-group-item">';
        $datos .= $pregunta[$i]['pregunta'] . '<span style="color: #8a8a8a;">(' . date("d/m/Y H:i:s", strtotime($pregunta[$i]['fecha_pregunta'])) . ')</span>';
        if ($pregunta[$i]['respuesta'] != null) {
            $datos .= '<ul>';
            $datos .= '<li>' . $pregunta[$i]['respuesta'] . '<span style="color: #8a8a8a;">(' .  date("d/m/Y H:i:s", strtotime($pregunta[$i]['fecha_respuesta'])) . ')</span></li>';
            $datos .= '</ul>';
        }
        $datos .= '</li>';
        $datos .= '</ul>';
        $datos .= '<br />';
    }
    return $datos;
}


//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
