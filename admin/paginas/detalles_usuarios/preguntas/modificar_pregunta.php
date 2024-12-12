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


//Limite de caracteres para la pregunta 
$pregunta_datos = [
    'cant_min' => 1,
    'cant_max' => 255,
];

$respuesta = [];


// Campos esperados en la solicitud POST
$campo_esperados = ['id_pregunta_modificar', 'txt_pregunta_modificar', 'fecha_modificada'];

$estado = 'danger';


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
            throw new Exception("Debe iniciar sesion para poder modifcar la informacion de la pregunta");
        }

        //Se obtiene los datos de sesion
        $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
        $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario administrador es valido
        if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
            throw new Exception("No se puede modificar la pregunta por que no es usuario administrador valido");
        }

        //Verifica si la cuenta del usuario sigue existe
        if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
            throw new Exception("Esta cuenta fue eliminada previamente. Por favor regrese a la página anterior");
        }

        //Se obtiene los datos de la solicitud POST
        $id_pregunta_modificar = $_POST['id_pregunta_modificar'];
        $fecha_modificada = $_POST['fecha_modificada'];
        $txt_pregunta_modificar = $_POST['txt_pregunta_modificar'];



        //Verifica si la pregunta del usuario sigue disponible
        if (!laPreguntaSigueDisponible($conexion, $id_pregunta_modificar)) {
            throw new Exception("Esta pregunta fue eliminada previamente. Por favor actualiza la pagina para ver los cambios");
        }

        //Verifica que el usuario hizo la pregunta
        if (!elUsuarioHizoLaPregunta($conexion, $id_pregunta_modificar, $id_usuario)) {
            throw new Exception("Un usuario administrador no puede eliminar las preguntas que no corresponde al usuario");
        }

        //Se obtiene los datos de la pregunta del usuario obtenida por el id de la pregunta a modificar
        $pregunta = obtenerPreguntaHecha($conexion, $id_pregunta_modificar);


        //Verifica que los nuevos datos recibidos no sean iguales a la campos originales de las preguntas 
        if ($fecha_modificada == $pregunta['fecha_pregunta'] && $txt_pregunta_modificar == $pregunta['pregunta']) {
            $estado = 'info';
            throw new Exception("No hubo cambios en la pregunta");
        }

        //Verifica que la pregunta no tenga espacios al inicio o al final de la cadena
        if (tieneEspaciosInicioFinCadena($txt_pregunta_modificar)) {
            throw new Exception("La pregunta no puede tener espacios en blanco al inicio o al final");
        }

        //Verifica que la longitud de caracteres sea valida 
        if (verificarLongitudTexto($txt_pregunta_modificar, $pregunta_datos['cant_min'], $pregunta_datos['cant_max'])) {
            throw new Exception("El campo pregunta debe tener entre 1 y 255 caracteres");
        }

        //Se guarda las modificaciones hechas en la pregunta del usuario
        modificarPreguntaPorAdmin($conexion, $id_pregunta_modificar, $txt_pregunta_modificar, $fecha_modificada);

        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'La informacion de la pregunta fue modificada correctamente';
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
