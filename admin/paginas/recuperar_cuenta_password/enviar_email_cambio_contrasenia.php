<?php

//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/funciones/funciones_verificaciones.php");
include("../../../config/funciones/funciones_generales.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/funciones/funciones_token.php");
require("../../../config/php_mailer_config.php");
include("../../../config/config_define.php");
require('../../../lib/php_mailer/src/PHPMailer.php');
require('../../../lib/php_mailer/src/Exception.php');
require('../../../lib/php_mailer/src/SMTP.php');

$respuesta['lista'] = "";
$respuesta["mensaje"] = "";
$respuesta["estado"] = "";


//Limite de caracteres por el email 
$email_datos = [
    'length_minimo' => 1,
    'length_maximo' => 320,
];

//Campos esperados en la solicitud POST
$campo_esperados = array("email");


try {

    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {


        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Se verifica que el email ingresado sea valido
        validarCampoEmail($_POST['email']);

        //Obtener los datos de la solicitud POST
        $email = $_POST['email'];


        //Verifica que el email no tenga espacios al inicio o al final de la cadena
        if (tieneEspaciosInicioFinCadena($email)) {
            throw new Exception("No se permiten espacios en blanco antes o despues del texto");
        }

        //Verifica que la longitud de caracteres sea valida 
        if (verificarLongitudTexto($email, $email_datos['length_minimo'], $email_datos['length_maximo'])) {
            throw new Exception("El campo no cumple con una longitud de caracteres valida");
        }

        //Se verifica que el email ingresado este registrado en el sistema por un usuario administrador
        if (!verificarSiEmailEstaDisponibleAdmin($conexion, $email)) {
            throw new Exception("No hay un usuario administrador registrado con el email ingresado.");
        }

        //Se obtiene los datos del usuario por el id del usuario
        $datos_usuario = obtenerDatosUsuarioAdminPorEmail($conexion, $email);
        $id_usuario_admin =  $datos_usuario['id_usuario_administrador'];

        //Se obtiene un token para el envio del email
        $token_password = generarToken();


        //Se envia un email al usuario admnistrador para que cambie su contraseña
        enviarEmailPasswordOlvidadoAdmin($conexion, $email, $token_password, $id_usuario_admin);

        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Se ha enviado un correo a tu Email por favor revise su bandeja de entrada y haga click en el enlace para hacer una nueva contraseña';
    } else {
        throw new Exception("No se recibio una solicitud POST.");
    }
} catch (Exception $e) {

    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['mensaje'] = $e->getMessage();
    $respuesta["estado"] = "danger";
}

//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
