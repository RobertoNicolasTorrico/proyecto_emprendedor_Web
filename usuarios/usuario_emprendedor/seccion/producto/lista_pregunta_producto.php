<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/funciones/funciones_verificaciones.php");
include("../../../../config/funciones/funciones_session.php");


// Campos esperados en la solicitud POST
$campo_esperados = array("id_producto");

$respuesta['lista'] = '';
$respuesta['estado'] = "";
$respuesta["mensaje"] = "";
try {


    //Verifica la entrada de datos esperados
    $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
    if (!empty($mensaje)) {
        throw new Exception($mensaje);
    }

    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario
    if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
        throw new Exception("Debe iniciar sesion para poder ver las preguntas");
    }

    //Obtener los datos de sesion
    $id_usuario = $_SESSION['id_usuario'];
    $tipo_usuario = $_SESSION['tipo_usuario'];

    //Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario es valido
    if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
        throw new Exception("No se puede mostrar la informacion de las pregunas debido a que su cuenta no esta activa o esta baneado");
    }


    //Se obtiene los datos de la solicitud POST
    $id_producto = $_POST['id_producto'];

    //Valida que los campos ocultos solo contengan numeros
    if (!is_numeric($id_producto)) {
        throw new Exception("Los campos ocultos solo deben contener numeros");
    }

    //Verifica si el usuario hizo la publicacion
    if (!elUsuarioLoPublico($conexion, $id_producto, $id_usuario)) {
        throw new Exception("Un usuario no puede ver las preguntas y respuestas del producto que el no halla publicado");
    }

    //Se obtiene una lista de actualizada de preguntas del producto 
    $preguntas = obtenerListaPreguntasRespuestaProducto($conexion, $id_producto);
    if (empty($preguntas)) {
        throw new Exception("No se pudo obtener las preguntas y respuestas actualizadas del producto");
    }

    //Genera el Modal body HTML para las preguntas 
    $respuesta["lista"] = obtenerModalBodyListaPreguntasRespuestaUsuario($preguntas);
    $respuesta['estado'] = 'success';
} catch (Exception $e) {

    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}



function obtenerModalBodyListaPreguntasRespuestaUsuario($preguntas)
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


        $datos .= "<div class='card-footer'>"; //Card Footer Inicio
        $fecha = date('d/m/Y H:i:s', strtotime($preguntas[$i]['fecha_pregunta']));

        //Verifica si la pregunta fue respondida para agregar un boton
        if ($preguntas[$i]["respuesta"] != null && $preguntas[$i]["fecha_respuesta"]  != null) {
            $datos .= "<button type='button' class='btn btn-outline-danger' data-bs-dismiss='modal' data-bs-toggle='modal'  data-bs-target='#eliminaModal'  data-bs-id_producto='{$preguntas[$i]["id_producto"]}' data-bs-id_pregunta='{$preguntas[$i]["id_pregunta_respuesta"]}' data-bs-fecha='{$fecha}' data-bs-respuesta='{$preguntas[$i]["respuesta"]}'> <i class='fa-solid fa-trash'></i> Eliminar Respuesta</button>";
        } else {
            $datos .= "<button type='button' class='btn btn-outline-success' data-bs-dismiss='modal' data-bs-toggle='modal' data-bs-target='#agregarModal' data-bs-fecha='{$fecha}' data-bs-nombre='{$preguntas[$i]["nombre_usuario"]}'data-bs-id_producto='{$preguntas[$i]["id_producto"]}' data-bs-id_pregunta='{$preguntas[$i]["id_pregunta_respuesta"]}' data-bs-pregunta='{$preguntas[$i]["pregunta"]}'><i class='fa-solid fa-circle-plus'></i> Agregar Respuesta</button>";
        }
        $datos .= "</div>";//Card Footer Fin

        $datos .= "</div> "; //Card Fin

    }
    return  $datos;
}


//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
