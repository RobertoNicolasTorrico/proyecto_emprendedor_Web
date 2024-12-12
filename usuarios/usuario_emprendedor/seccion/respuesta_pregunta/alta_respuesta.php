<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/consultas_bd/consultas_notificacion.php");
include("../../../../config/funciones/funciones_verificaciones.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/config_define.php");



//Limite de caracteres para la respuesta 
$respuesta_datos = [
    'cant_min' => 1,
    'cant_max' => 255,
];

// Campos esperados en la solicitud POST
$campo_esperados = array("id_pregunta", "id_producto", "respuesta_pregunta");

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
            throw new Exception("Debe iniciar sesion para poder responder las preguntas recibidas");
        }

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION['id_usuario'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No puede responder la pregunta debido a que su cuenta no esta activa o esta baneado");
        }

        //Verifica que el usuario sea un usuario emprendedor
        if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
            throw new Exception("No se pudo responder la pregunta debido a que no es un usuario emprendedor");
        }


        //Se obtiene los datos de la solicitud POST
        $id_pregunta = $_POST['id_pregunta'];
        $id_producto = $_POST['id_producto'];
        $respuesta_pregunta = $_POST['respuesta_pregunta'];


        //Validar que los campos ocultos solo contengan numeros
        if (!is_numeric($id_pregunta) ||  !is_numeric($id_producto)) {
            throw new Exception("Los campos ocultos solo deben contener numeros");
        }


        //Verifica si la pregunta del usuario sigue disponible
        if (!laPreguntaSigueDisponible($conexion, $id_pregunta)) {
            throw new Exception("Esta pregunta fue eliminada por el usuario que la realizo. Por favor actualiza la pagina para ver los cambios");
        }

        //Verifica que la respuesta no tenga espacios al inicio o al final de la cadena
        if (tieneEspaciosInicioFinCadena($respuesta_pregunta)) {
            throw new Exception("La respuesta no puede tener espacios en el inicio o al final");
        }

        //Verifica que la longitud de caracteres sea valida 
        if (verificarLongitudTexto($respuesta_pregunta, $respuesta_datos['cant_min'], $respuesta_datos['cant_max'])) {
            throw new Exception("El campo respuesta debe tener entre 1 y 255 caracteres");
        }

        //Verifica si el usuario publico el producto
        if (!elUsuarioLoPublico($conexion, $id_producto, $id_usuario)) {
            throw new Exception("Un usuario emprendedor no puede responder preguntas de productos que el no halla publicado");
        }

        //Guarda la respuesta 
        altaRespuestaModificarPreguntaProducto($conexion, $respuesta_pregunta, $id_pregunta);

        //Se obtiene informacion sobre el usuario que hizo la pregunta
        $pregunta = obtenerIdUsuarioPreguntaRespuesta($conexion, $id_pregunta);
        $id_usuario_notificar = $pregunta['id_usuario'];
        $id_respuesta = $id_pregunta;

        //Se guarda una nueva notificacion para el usuario que hizo la pregunta
        altaNotificacionRespuestaPregunta($conexion, $id_usuario_notificar, $id_usuario, $id_respuesta, $id_producto);


        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Se envio la respuesta correctamente';
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
