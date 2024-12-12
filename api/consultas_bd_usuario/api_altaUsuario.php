<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_token.php");
require("../../config/php_mailer_config.php");
include("../../config/config_define.php");
require('../../lib/php_mailer/src/PHPMailer.php');
require('../../lib/php_mailer/src/Exception.php');
require('../../lib/php_mailer/src/SMTP.php');


// Campos esperados en la solicitud POST
$campo_esperados_text = [
    'nombre_usuario' => ['label' => 'Nombre de usuario', 'length_minimo' => 5, 'length_maximo' => 20],
    'nombres' => ['label' => 'Nombres', 'length_minimo' => 1, 'length_maximo' => 100],
    'apellidos' => ['label' => 'Apellidos', 'length_minimo' => 1, 'length_maximo' => 100],
    'email' => ['label' => 'Email', 'length_minimo' => 1, 'length_maximo' => 320],
    'password' => ['label' => 'Contraseña', 'length_minimo' => 6, 'length_maximo' => 60],
    'confirmar_password' => ['label' => 'Confirmar contraseña', 'length_minimo' => 6, 'length_maximo' => 60]
];

$campo_esperados_password = [
    'password' => ['label' => 'Contraseña', 'length_minimo' => 6, 'length_maximo' => 60],
    'confirmar_password' => ['label' => 'Confirmar contraseña', 'length_minimo' => 6, 'length_maximo' => 60]
];


$campo_esperados = array("tipo_usuario");

$respuesta = [];

$campos_errores = [];

try {
    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {


        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }


        //Verifica la conexion con la base de datos
        $conexion = obtenerConexionBD();

        //Se verifica que el tipo de usuario seleccionado es valido
        $tipo_usuario = validarCampoSelectTipoUsuario($conexion, 'tipo_usuario');


        //En caso que sea un emprendedor se va agregar los campos necesarios para que un emprendedor se registre
        if ($tipo_usuario == 2) {
            $campo_esperados_text['nombre_emprendimiento'] = ['label' => 'Nombre del emprendimiento', 'length_minimo' => 1, 'length_maximo' => 50];
        }

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosMatriz($campo_esperados_text, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }


        $nombre_usuario = $_POST['nombre_usuario'];
        //Verifica que la longitud de caracteres sea valida 
        if (verificarLongitudTexto($nombre_usuario, $campo_esperados_text['nombre_usuario']['length_minimo'], $campo_esperados_text['nombre_usuario']['length_maximo'])) {
            throw new Exception("El campo nombre de usuario debe tener entre 5 y 20 caracteres");
        }

        //Verifica que los campos de texto no tenga espacios al inicio o al final de la cadena
        $campos_errores = listaConLongitudNoValida($campo_esperados_password);
        if (!empty($campos_errores)) {
            $errores_texto = implode(", ", $campos_errores);
            throw new Exception("Los siguientes campos no cumplen con la longitud mínima de 6 carácteres o el máximo de caracteres indicado: " . $errores_texto . ".");
        }


        //Verifica que los campos de texto no tenga espacios al inicio o al final de la cadena
        $campos_errores = listaCamposConEspacioInicioFin($campo_esperados_text, $_POST);
        if (!empty($campos_errores)) {
            $errores_texto = implode(", ", $campos_errores);
            throw new Exception("No se permite que los campos tengan espacios en blanco al inicio o al final. Los siguientes campos no cumplen con esto: " . $errores_texto . ".");
        }


        //Verifica que la longitud de los campos de textos de caracteres sea valida 
        $campos_errores = listaConLongitudNoValida($campo_esperados_text);
        if (!empty($campos_errores)) {
            $errores_texto = implode(", ", $campos_errores);
            throw new Exception("Los siguientes campos no cumplen con la longitud mínima de 1 carácter o el máximo de caracteres indicado: " . $errores_texto . ".");
        }

        //Se verifica que el email ingresado sea valido
        validarCampoEmail($_POST['email']);

        //Verifica que el password no tenga espacios al inicio o al final de la cadena
        validarCampoPassword($_POST['password']);


        //Verifica que la nueva contraseña sea igual a la confirmacion de la nueva contraseña
        validarIgualdadCamposPasswordsPost('password', 'confirmar_password');


        //Se verifica que el email ingresado no este registrado en el sistema por otro usuario
        if (verificarSiEmailEstaDisponible($conexion, $_POST['email'])) {
            throw new Exception("Ya hay otro usuario registrado con el E-mail ingresado.");
        }

        //Se verifica que el nuevo de usuario ingresado no este registrado en el sistema por otro usuario
        if (verificarSiNombreUsuarioEstaDisponible($conexion, $_POST['nombre_usuario'])) {
            throw new Exception("El nombre de usuario ingresado no esta disponible.");
        }

        $nombre_empredimiento = " ";

        //Se verifica si hay un nombre de empredimiento registrado
        if (array_key_exists('nombre_emprendimiento', $campo_esperados_text)) {

            //Se verifica que el nuevo del emprendimiento ingresado no este registrado en el sistema por otro usuario
            if (verificarSiNombreEmprendedorEstaDisponibles($conexion, $_POST['nombre_emprendimiento'])) {
                throw new Exception("Ya hay otro usuario emprendedor que esta usando el nombre del emprendimiento ingresado");
            }
            //Se obtiene el nombre del emprendimiento  de la solicitud POST
            $nombre_empredimiento = $_POST['nombre_emprendimiento'];
        }


        //Se obtiene los datos de la solicitud POST
        $nombre_usuario = $_POST['nombre_usuario'];
        $nombres = $_POST['nombres'];
        $apellidos = $_POST['apellidos'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        //Se obtiene un token para el nuevo usuario
        $token = generarToken();


        //Se guarda los datos del usuario
        altaUsuario($conexion, $nombre_usuario, $nombres, $apellidos, $email, $password, $token, $tipo_usuario, $nombre_empredimiento);

        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'En unos minutos recibirás un correo electrónico para finalizar el proceso de registro. Revisa tanto la bandeja de entrada como la de correo no deseado para confirmar tu registro e iniciar sesión.';
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
