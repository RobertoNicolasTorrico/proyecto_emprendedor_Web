<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/config_define.php");


//Inicializacion de variables obtenidas de la URL
$id_producto = isset($_GET['id']) ? $_GET['id'] : '';
$id_producto_token = isset($_GET['token']) ? $_GET['token'] : '';

$respuesta['lista'] = '';
$respuesta['estado'] = "";
$respuesta["mensaje"] = "";

try {


    //Se verifica que los datos recibidos de la URL sean validos
    verificarUrlTokenId($id_producto, $id_producto_token);

    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario administrador
    if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
        throw new Exception("Debe iniciar sesion para poder la lista de preguntas");
    }

    //Se obtiene los datos de sesion
    $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
    $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario administrador es valido
    if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
        throw new Exception("No se puede ver la lista de preguntas por que no es usuario administrador valido");
    }

    //Se obtiene las preguntas de un producto
    $preguntasGenerales = obtenerListaPreguntasRespuestaProducto($conexion, $id_producto);

    //Genera el Modal body HTML para las preguntas 
    $respuesta['pregunta'] = obtenerModalListaPreguntasRespuestaProductoEmprendedor($preguntasGenerales);
} catch (Exception $e) {
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}


function obtenerModalListaPreguntasRespuestaProductoEmprendedor($preguntas)
{
    $datos = '';
    $cant_preguntas_total = count($preguntas);
    if ($cant_preguntas_total > 0) {
        $datos .= "<button type='button' class='btn btn-outline-primary btn-sm' data-bs-toggle='modal' data-bs-target='#preguntasRespuestaModal'>Ver todas las preguntas y respuestas del producto</button>";
        $datos .= "<div class='modal fade' id='preguntasRespuestaModal' tabindex='-1' aria-labelledby='preguntasRespuestaModalLabel' aria-hidden='true'>";
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
            $fecha = date('d/m/Y H:i:s', strtotime($preguntas[$i]['fecha_pregunta']));
            if ($preguntas[$i]["respuesta"] != null && $preguntas[$i]["fecha_respuesta"]  != null) {
                $datos .= "<div class='card-footer'>";
                $datos .= "<button type='button' class='btn btn-outline-danger' data-bs-dismiss='modal' data-bs-toggle='modal'  data-bs-target='#eliminarModalPreguntasRecibidas'  data-bs-id_pregunta_producto='{$preguntas[$i]["id_producto"]}' data-bs-id_pregunta_recibida='{$preguntas[$i]["id_pregunta_respuesta"]}' data-bs-fecha='{$fecha}' data-bs-respuesta='{$preguntas[$i]["respuesta"]}'> <i class='fa-solid fa-trash'></i> Eliminar Respuesta</button>";
                $datos .= "</div>";
            }
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



echo json_encode($respuesta);
