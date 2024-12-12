<?php

//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/consultas_bd/consultas_notificacion.php");
include("../../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../../config/funciones/funciones_verificaciones.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/config_define.php");

//Campos esperados en la solicitud POST
$campo_esperados = array("id_pregunta");

$respuesta = [];


//Inicializacion de variables obtenidas de la URL
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';
$id_usuario_token = isset($_GET['token']) ? $_GET['token'] : '';

try {

    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {

        //Se verifica que los datos recibidos de la URL sean validos
        verificarUrlTokenId($id_usuario, $id_usuario_token);

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }


        //Establecer la sesion
        session_start();

        //Verificar los datos de sesion del usuario administrador
        if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
            throw new Exception("Debe iniciar sesion para poder eliminar una pregunta");
        }

        //Se obtiene los datos de sesion
        $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
        $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario administrador es valido
        if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
            throw new Exception("No se puede eliminar la pregunta por que no es usuario administrador valido");
        }

        //Verifica si la cuenta del usuario sigue existe
        if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
            throw new Exception("Esta cuenta fue eliminada previamente. Por favor regrese a la página anterior");
        }

        //Se obtiene los datos de la solicitud POST
        $id_pregunta = $_POST['id_pregunta'];

        //Valida que los campos ocultos solo contengan numeros
        if (!is_numeric($id_pregunta)) {
            throw new Exception("Los campos ocultos solo deben contener numeros");
        }


        //Verifica si la pregunta del usuario sigue disponible
        if (!laPreguntaSigueDisponible($conexion, $id_pregunta)) {
            throw new Exception("Esta pregunta fue eliminada previamente. Por favor actualiza la pagina para ver los cambios");
        }


        //Verifica si el usuario hizo la pregunta
        if (!elUsuarioHizoLaPregunta($conexion, $id_pregunta, $id_usuario)) {
            throw new Exception("Un usuario admininstrador no puede eliminar las preguntas que no halla hecho el usuario");
        }


        //Verifica si la pregunta del usuario fue respondida
        if (laPreguntaFueRespondida($conexion, $id_pregunta)) {
            throw new Exception("No se pueden eliminar preguntas que fueron respondidas");
        }


        //Se obtiene informacion sobre el usuario que hizo la pregunta
        $preguntas = obtenerIdUsuarioPreguntaProducto($conexion, $id_pregunta);
        $id_usuario_notificar = $preguntas['id_usuario'];


        //Se elimina la pregunta
        bajaPreguntaProducto($conexion, $id_pregunta);

        //Se elimina la notificacion de pregunta al emprendedor que la recibio
        bajaNotificacionPreguntaProducto($conexion, $id_usuario_notificar, $id_usuario, $id_pregunta);

        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Se elimino la pregunta correctamente';
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
