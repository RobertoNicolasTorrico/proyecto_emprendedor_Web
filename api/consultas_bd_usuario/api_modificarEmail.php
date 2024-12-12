<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/funciones/funciones_token.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_generales.php");
require("../../config/php_mailer_config.php");
include("../../config/config_define.php");
require('../../lib/php_mailer/src/PHPMailer.php');
require('../../lib/php_mailer/src/Exception.php');
require('../../lib/php_mailer/src/SMTP.php');

// Campos esperados en la solicitud POST
$campo_esperados = array("id_usuario", "tipo_usuario", "email", "password");
$campos_modificar = [
    'email' => ['label' => 'Email', 'length_minimo' => 2, 'length_maximo' => 320],
    'password' => ['label' => 'Contraseña', 'length_minimo' => 6, 'length_maximo' => 60],
];

$respuesta = [];


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


        //Se obtiene los datos de la solicitud POST
        $id_usuario = $_POST['id_usuario'];
        $tipo_usuario = $_POST['tipo_usuario'];
        $nuevo_email = $_POST['email'];
        $password = $_POST['password'];


        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No puede modificar los datos del usuario debido a que su cuenta no esta activa o esta baneado");
        }

        //Verifica que los campos de texto no tenga espacios al inicio o al final de la cadena
        $campos_errores = listaCamposConEspacioInicioFin($campos_modificar, $_POST);
        if (!empty($campos_errores)) {
            $errores_texto = implode(", ", $campos_errores);
            throw new Exception("No se permite que los campos tengan espacios en blanco al inicio o al final. Los siguientes campos no cumplen con esto: " . $errores_texto . ".");
        }

        //Verifica que la longitud de los campos de textos de caracteres sea valida 
        $campos_errores = listaConLongitudNoValida($campos_modificar);
        if (!empty($campos_errores)) {
            $errores_texto = implode(", ", $campos_errores);
            throw new Exception("Los siguientes campos no cumplen con la longitud mínima de 1 carácter o el máximo de caracteres indicado: " . $errores_texto . ".");
        }


        //Se verifica que el email ingresado sea valido
        validarCampoEmail($nuevo_email);

        //Verifica que el password no tenga espacios al inicio o al final de la cadena
        validarCampoPassword($password);

        //Se obtiene un token para el nuevo email
        $token_email = generarToken();

        //Se obtiene los datos del usuario por el id de usuario 
        $datos_usuario = obtenerDatosUsuarioPorIdUsuario($conexion, $id_usuario);


        //Se verifica que la contraseña recibida sea igual a la contraseña de la cuenta del usuario
        if (!password_verify($password, $datos_usuario["contrasenia"])) {
            throw new Exception("La contraseña ingresada es incorrecta");
        }

        //Verifica que el nuevo email no sea igual al email original del usuario
        if ($nuevo_email ==  $datos_usuario['email']) {
            throw new Exception("El email ingresado es el mismo con el que esta registrado su cuenta");
        }

        //Se verifica que el nuevo email ingresado no este registrado en el sistema por otro usuario
        if (verificarSiEmailEstaDisponible($conexion, $nuevo_email)) {
            throw new Exception("Ya hay otro usuario registrado con el E-mail ingresado.");
        }

        //Se envia un email al nuevo email ingresado para confirmar que el usuario tenga acceso a el
        verificarNuevoEmailIngresado($conexion, $id_usuario, $nuevo_email, $token_email);
        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Se ha enviado un correo de confirmacion a tu nuevo Email por favor revise su bandeja de entrada y haga click en el enlace de confirmacion para terminar el proceso';

        http_response_code(200);
    } else {
        http_response_code(405);
        throw new Exception("Metodo no permitido o datos no recibidos");
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    http_response_code(400);
    $respuesta['mensaje'] = $e->getMessage();
}
//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
