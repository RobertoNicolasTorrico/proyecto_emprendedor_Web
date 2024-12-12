<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_session.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_token.php");
require("../../config/php_mailer_config.php");
include("../../config/config_define.php");
require('../../lib/php_mailer/src/PHPMailer.php');
require('../../lib/php_mailer/src/Exception.php');
require('../../lib/php_mailer/src/SMTP.php');

$respuesta = [];

//Campos esperados en la solicitud POST
$campo_esperados = array("email");

//Limite de caracteres para el email 
$email_datos = [
    'length_minimo' => 1,
    'length_maximo' => 320,
];
$estado = 'danger';

try {
    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }


        //Se verifica que el email ingresado sea valido
        validarCampoEmail($_POST['email']);

        //Obtener los datos de la solicitud POST
        $email = $_POST['email'];


        //Verifica que la longitud de caracteres sea valida 
        if (verificarLongitudTexto($email, $email_datos['length_minimo'], $email_datos['length_maximo'])) {
            throw new Exception("El campo email no cumple con la longitud mínima de 1 carácter o el máximo caracteres permitido");
        }


        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();


        //Se verifica que el nuevo email ingresado no este registrado en el sistema por otro usuario
        if (!verificarSiEmailEstaDisponible($conexion, $email)) {
            throw new Exception("No hay un usuario registrado con el email ingresado");
        }


        //Verifica si la cuenta ya esta activa
        if (cuentaEstaActivada($conexion, $email)) {
            throw new Exception("La cuenta del usuario ya se encuentra activa");
        }
        //Se obtiene los datos del usuario por email
        $datos_usuario = obtenerDatosUsuarioPorEmail($conexion, $email);
        $id_usuario =  $datos_usuario['id_usuario'];


        //Se obtiene un nuevo token para el usuario 
        $token = generarToken();

        //Se envia el email para que el usuario active su cuenta 
        volverEnviarEmailActivarCuentaUsuario($conexion,  $id_usuario, $email, $token);
        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Se ha enviado un correo electronico al email ingresado, por favor revise su bandeja de entrada y haga clic en el enlace para activar su cuenta';
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
