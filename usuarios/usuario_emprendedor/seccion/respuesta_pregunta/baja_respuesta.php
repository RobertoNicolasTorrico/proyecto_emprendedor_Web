<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/consultas_bd/consultas_notificacion.php");
include("../../../../config/funciones/funciones_verificaciones.php");
include("../../../../config/funciones/funciones_session.php");

//Campos esperados en la solicitud POST
$campo_esperados = array("id_pregunta", "id_producto");

$respuesta = [];
try {
    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Establecer la sesion
        session_start();

        //Verificar los datos de sesion del usuario
        if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
            throw new Exception("Debe iniciar sesion para poder eliminar las respuesta de los productos");
        }

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION['id_usuario'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede eliminar la respuesta debido a que su cuenta no esta activa o esta baneado");
        }

        //Verifica que el usuario sea un usuario emprendedor
        if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
            throw new Exception("No se pudo eliminar la respuesta debido a que no es un usuario emprendedor");
        }

        //Se obtiene los datos de la solicitud POST
        $id_pregunta = $_POST['id_pregunta'];
        $id_producto = $_POST['id_producto'];

        //Valida que los campos ocultos solo contengan numeros
        if (!is_numeric($id_pregunta)  ||  !is_numeric($id_producto)) {
            throw new Exception("Los campos ocultos solo deben contener numeros");
        }


        //Verifica si la respuesta del emprendedor sigue disponible
        if (!laPreguntaFueRespondida($conexion, $id_pregunta)) {
            throw new Exception("Esta respuesta fue eliminada previamente. Por favor actualiza la pagina para ver los cambios");
        }


        //Verifica si el usuario publico el producto
        if (!elUsuarioLoPublico($conexion, $id_producto, $id_usuario)) {
            throw new Exception("Un usuario no puede eliminar respuestas de los productos que el no halla publicado");
        }


        //Se elimina la respuesta
        bajaRespuestaModificarPreguntaProducto($conexion, $id_pregunta);

        //Se obtiene informacion sobre el usuario que hizo la pregunta
        $pregunta = obtenerIdUsuarioPreguntaRespuesta($conexion, $id_pregunta);
        $id_usuario_notificar = $pregunta['id_usuario'];
        $id_respuesta = $id_pregunta;

        //Se elimina la notificacion de respuesta para el usuario que hizo la pregunta
        bajaNotificacionRespuestaPregunta($conexion, $id_usuario_notificar, $id_usuario, $id_respuesta);

        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Se elimino la respuesta correctamente';
    } else {
        throw new Exception("No se recibio una solicitud POST.");
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}

//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
