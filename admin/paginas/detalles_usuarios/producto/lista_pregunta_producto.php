<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/funciones/funciones_verificaciones.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/config_define.php");

// Campos esperados en la solicitud POST
$campo_esperados = array("id_producto");


//Inicializacion de variables obtenidas de la URL
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';
$id_usuario_token = isset($_GET['token']) ? $_GET['token'] : '';


$respuesta['lista'] = '';
$respuesta['estado'] = "";
$respuesta["mensaje"] = "";
try {

    //Verifica la entrada de datos esperados
    $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
    if (!empty($mensaje)) {
        throw new Exception($mensaje);
    }

    //Se verifica que los datos recibidos de la URL sean validos
    verificarUrlTokenId($id_usuario, $id_usuario_token);

    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario administrador
    if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
        throw new Exception("Debe iniciar sesion para poder ver la lista de preguntas del productos");
    }

    //Se obtiene los datos de sesion
    $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
    $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario administrador es valido
    if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
        throw new Exception("No se puede ver la lista de preguntas del producto por que no es usuario administrador valido");
    }

    //Verifica si la cuenta del usuario sigue existe
    if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
        throw new Exception("Esta cuenta fue eliminada previamente. Por favor regrese a la página anterior");
    }

    //Se obtiene los datos de la solicitud POST
    $id_producto = $_POST['id_producto'];

    //Valida que los campos ocultos solo contengan numeros
    if (!is_numeric($id_producto)) {
        throw new Exception("Los campos ocultos solo deben contener numeros");
    }

    //Se obtiene una lista de actualizada de preguntas del producto 
    $preguntas = obtenerListaPreguntasRespuestaProducto($conexion, $id_producto);
    if (empty($preguntas)) {
        throw new Exception("No se pudo obtener las preguntas y respuestas actualizadas del producto");
    }

    //Genera el Modal body HTML para las preguntas 
    $respuesta["lista"] = obtenerModalBodyListaPreguntasRespuesta($preguntas);
    $respuesta['estado'] = 'success';
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}



function obtenerModalBodyListaPreguntasRespuesta($preguntas)
{
    $datos = '';

    $datos .= "<div id='alert_preguntas_respuesta'></div>";
    for ($i = 0; $i < count($preguntas); ++$i) {

        $datos .= "<div class='card mb-4'>"; //Card Inicio


        $datos .= "<div class='card-body'>"; //Card Body Inicio
        $datos .= "<p class='card-text text-break'><strong>Nombre de usuario:</strong>{$preguntas[$i]["nombre_usuario"]}</p>";
        $fecha_pregunta = date('d/m/Y H:i:s', strtotime($preguntas[$i]['fecha_pregunta']));

        //Se muestra la pregunta del producto
        $datos .= "<p class='card-text text-break'><strong>Pregunta:</strong>{$preguntas[$i]["pregunta"]}<span style='color: #8a8a8a;'>({$fecha_pregunta})</span></p>";

        //Se muestra la respuesta de la pregunta si es que fue respondida
        if ($preguntas[$i]["respuesta"] != null && $preguntas[$i]["fecha_respuesta"]  != null) {
            $fecha_respuesta = date('d/m/Y H:i:s', strtotime($preguntas[$i]['fecha_respuesta']));
            $datos .= "<p class='card-text text-break'><strong>Respuesta:</strong>{$preguntas[$i]["respuesta"]}<span style='color: #8a8a8a;'>({$fecha_respuesta})</span></p>";
        }
        $datos .= "</div>";  //Card Body Fin


        if ($preguntas[$i]["respuesta"] != null && $preguntas[$i]["fecha_respuesta"]  != null) {
            $datos .= "<div class='card-footer'>"; //Card Footer Inicio
            $fecha = date('d/m/Y H:i:s', strtotime($preguntas[$i]['fecha_pregunta']));

            //Verifica si la pregunta fue respondida para agregar un boton para eliminar la respuesta
            $datos .= "<button type='button' class='btn btn-outline-danger' data-bs-dismiss='modal' data-bs-toggle='modal'  data-bs-target='#eliminarModalPreguntasRecibidas'  data-bs-id_pregunta_producto='{$preguntas[$i]["id_producto"]}' data-bs-id_pregunta_recibida='{$preguntas[$i]["id_pregunta_respuesta"]}' data-bs-fecha='{$fecha}' data-bs-respuesta='{$preguntas[$i]["respuesta"]}'> <i class='fa-solid fa-trash'></i> Eliminar Respuesta</button>";

            $datos .= "</div>"; //Card Footer Fin
        }
        $datos .= "</div> "; //Card Fin
    }
    return  $datos;
}


//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
