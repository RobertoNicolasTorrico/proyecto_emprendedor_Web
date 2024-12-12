<?php

//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd//consultas_preguntas_respuesta.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../../config/funciones/funciones_verificaciones.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/config_define.php");


//Limite de caracteres para la respuesta 
$respuesta_datos = [
    'cant_min' => 1,
    'cant_max' => 255,
];

$respuesta = [];

// Campos esperados en la solicitud POST
$campo_esperados = ['id_respuesta_modificar', 'txt_respuesta_modificar', 'fecha_modificada'];

$estado = 'danger';


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
            throw new Exception("Debe iniciar sesion para poder modifcar la informacion de la respuesta");
        }

        //Se obtiene los datos de sesion
        $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
        $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario administrador es valido
        if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
            throw new Exception("No se puede modificar la respuesta por que no es usuario administrador valido");
        }

        //Verifica si la cuenta del usuario sigue existe
        if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
            throw new Exception("Esta cuenta fue eliminada previamente. Por favor regrese a la página anterior");
        }

        //Se obtiene los datos de la solicitud POST
        $id_respuesta_modificar = $_POST['id_respuesta_modificar'];
        $fecha_modificada = $_POST['fecha_modificada'];
        $txt_respuesta_modificar = $_POST['txt_respuesta_modificar'];

        //Verifica si la respuesta del emprendedor sigue disponible
        if (!laPreguntaFueRespondida($conexion, $id_respuesta_modificar)) {
            throw new Exception("Esta respuesta fue eliminada previamente. Por favor actualiza la pagina para ver los cambios");
        }


        //Se obtiene los datos de la respuesta obtenida por el id de la respuesta a modificar
        $respuesta = obtenerPreguntaHecha($conexion, $id_respuesta_modificar);


        //Verifica que los nuevos datos recibidos no sean iguales a la campos originales de la respuesta 
        if ($fecha_modificada == $respuesta['fecha_respuesta'] && $txt_respuesta_modificar == $respuesta['respuesta']) {
            $estado = 'info';
            throw new Exception("No hubo cambios en la respuesta");
        }


        //Verifica que la pregunta no tenga espacios al inicio o al final de la cadena
        if (tieneEspaciosInicioFinCadena($txt_respuesta_modificar)) {
            throw new Exception("La respuesta no puede tener espacios en blanco al inicio o al final");
        }

        //Verifica que la longitud de caracteres sea valida 
        if (verificarLongitudTexto($txt_respuesta_modificar, $respuesta_datos['cant_min'], $respuesta_datos['cant_max'])) {
            throw new Exception("El campo respuesta debe tener entre 1 y 255 caracteres");
        }

        //Se guarda las modificaciones hechas en la respuesta de la pregunta
        modificarRespuestaPorAdmin($conexion, $id_respuesta_modificar, $txt_respuesta_modificar, $fecha_modificada);

        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'La informacion de la respuesta fue modificada correctamente';
    } else {
        throw new Exception("No se recibio una solicitud POST.");
    }
} catch (Exception $e) {

    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['mensaje'] = $e->getMessage();
    $respuesta['estado'] = $estado;
}

//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
