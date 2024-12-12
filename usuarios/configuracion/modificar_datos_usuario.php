<?php

//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_session.php");
include("../../config/funciones/funciones_token.php");
require("../../config/php_mailer_config.php");
include("../../config/config_define.php");

require('../../lib/php_mailer/src/PHPMailer.php');
require('../../lib/php_mailer/src/Exception.php');
require('../../lib/php_mailer/src/SMTP.php');



//Campos esperados en la solicitud POST
$campo_esperados = array("tipo_modificacion");

$respuesta['lista'] = "";
$respuesta["mensaje"] = "";
$respuesta["estado"] = "";
$campos_errores = [];
$estado = 'danger';


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
            throw new Exception("Debe haber iniciar sesion para modificar la informacion de su cuenta");
        }

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION['id_usuario'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();


        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No puede modificar los datos del usuario debido a que su cuenta no esta activa o esta baneado");
        }

        //Se obtiene los datos de la solicitud POST
        $tipo_modificacion = $_POST['tipo_modificacion'];


        switch ($tipo_modificacion) {

                //Si el tipo de modificacion es usuario se va a modificar los datos personales del usuario
            case 'usuario':

                //Campos esperado del usuario en la solicitud POST
                $campo_esperados_usuario = [
                    'nombres' => ['label' => 'Nombres', 'length_minimo' => 1, 'length_maximo' => 100],
                    'apellidos' => ['label' => 'Apellidos', 'length_minimo' => 1, 'length_maximo' => 100],
                ];


                //Verifica la entrada de datos esperados
                $mensaje = verificarEntradaDatosMatriz($campo_esperados_usuario, $_POST);
                if (!empty($mensaje)) {
                    throw new Exception($mensaje);
                }


                //Verifica que la longitud de los campos de textos de caracteres sea valida 
                $campos_errores = listaCamposConEspacioInicioFin($campo_esperados_usuario, $_POST);
                if (!empty($campos_errores)) {
                    throw new Exception("Los siguientes campos tienen espacios en blanco al inicio o al final:");

                }


                //Verifica que la longitud de los campos de textos de caracteres sea valida 
                $campos_errores = listaConLongitudNoValida($campo_esperados_usuario);
                if (!empty($campos_errores)) {
                    throw new Exception("Los siguientes campos no cumplen con la longitud mínima de 1 carácter o el máximo de caracteres indicado:");
                }

                //Se obtiene los datos del usuario por el id de usuario 
                $datos_usuario = obtenerDatosUsuarioPorIdUsuario($conexion, $id_usuario);


                //Se obtiene los datos de la solicitud POST
                $nombres = $_POST['nombres'];
                $apellidos = $_POST['apellidos'];

                //Verifica que los nuevos datos recibidos no sean iguales a los datos originales del usuario
                if ($nombres ==  $datos_usuario['nombres'] && $apellidos ==  $datos_usuario['apellidos']) {
                    $estado = 'info';
                    throw new Exception("No hubo cambios en los datos personales del usuario");
                }


                //Se guarda los datos modificados del usuario
                modificarNombreYApellidoUsuario($conexion, $id_usuario, $nombres, $apellidos);


                $respuesta['estado'] = 'success';
                $respuesta['mensaje'] = 'La informacion del usuario fue modificada correctamente';

                break;

                //Si el tipo de modificacion es emprendedor se va a modificar los datos del emprendimiento
            case 'emprendedor':

                //Limite de caracteres para la descripcion 
                $descripcion_datos = [
                    'cant_min' => 1,
                    'cant_max' => 150,
                ];

                //Configuracion de archivos permitidos
                $archivo_extensiones_imagen =  ['png', 'jpeg', 'jpg'];

                //Se obtiene los datos de la solicitud POST
                $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";
                $file = (isset($_FILES['file'])) ? $_FILES['file'] : "";


                //Verifica que la descripcion del usuario este vacia
                if (!empty($descripcion)) {
                    //Verifica que la descripcion no tenga espacios al inicio o al final de la cadena
                    if (tieneEspaciosInicioFinCadena($descripcion)) {
                        throw new Exception("El campo descripcion no puede tener espacios en blanco al inicio o al final");
                    }

                    //Verifica que la longitud de caracteres sea valida 
                    if (verificarLongitudTexto($descripcion, $descripcion_datos['cant_min'], $descripcion_datos['cant_max'])) {
                        throw new Exception("El campo descripcion debe tener entre 1 y 150 caracteres");

                    }
                }


                //Verifica y maneja la carga de archivos en caso que se reciba uno o se halla eliminado un archivo
                if (!empty($file)) {

                    //Verifica que la subida de archivo por parte del usuario sea correcta
                    if (verificarSubidaArchivos('file')) {
                        throw new Exception("Error al subir archivos");
                    }

                    $extencion_archivo = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                    $tamanio_archivo = $_FILES['file']['size'];

                    //Se verifica si la extension del archivo sea un imagen
                    if (verificarExtensionesValidasArchivos($extencion_archivo, $archivo_extensiones_imagen)) {

                        //Se verifica que la imagen tenga un tamaño valido
                        if (!validarTamanioImagen($tamanio_archivo)) {
                            throw new Exception("La imagen " . $_FILES['files']['name'] . " excede el tamaño maximo permitido de 10MB");
                        }
                    } else {
                        throw new Exception("El formato del archivo " . $_FILES['files']['name'] . " no es valido. Formatos permitidos: JPEG, JPG y PNG");
                    }

                }


                //Se obtiene los datos del emprendimiento por el ID del usuario
                $datos_usuario = obtenerDatosUsuarioYUsuarioEmprendedorPorIdUsuario($conexion, $id_usuario);
                $id_usuario_emprendedor = $datos_usuario['id_usuario_emprendedor'];
                $foto_perfil_actual = $datos_usuario['foto_perfil_nombre'];


                //Verifica que los nuevos datos recibidos no sean iguales a la descripcion del emprendedor
                if ($descripcion ==  $datos_usuario['descripcion'] && empty($file)) {
                    $estado = 'info';
                    throw new Exception("No hubo cambios en los datos del emprendimiento");
                }

                //Se guarda las modificaciones del emprendimiento de la cuenta del usuario
                modificarDatosEmprendedor($conexion, $id_usuario_emprendedor, $descripcion, $file, $foto_perfil_actual);


                $respuesta['estado'] = 'success';
                $respuesta['mensaje'] = 'La informacion del emprendimiento fue modificada correctamente';

                break;

                //Si el tipo de modificacion es el email va a modificar el email del usuario
            case 'email':

                // Campos esperados en la solicitud POST
                $campo_esperados_email = [
                    'email' => ['label' => 'Email', 'length_minimo' => 1, 'length_maximo' => 320],
                    'password' => ['label' => 'Contraseña', 'length_minimo' => 6, 'length_maximo' => 60],
                ];


                //Verifica la entrada de datos esperados
                $mensaje = verificarEntradaDatosMatriz($campo_esperados_email, $_POST);
                if (!empty($mensaje)) {
                    throw new Exception($mensaje);
                }

                //Verifica que los campos de texto no tenga espacios al inicio o al final de la cadena
                $campos_errores = listaCamposConEspacioInicioFin($campo_esperados_email, $_POST);
                if (!empty($campos_errores)) {                
                    throw new Exception("No se permite que los campos tengan espacios en blanco al inicio o al final. Los siguientes campos no cumplen con esto:");
                }

                //Verifica que la longitud de los campos de textos de caracteres sea valida 
                $campos_errores = listaConLongitudNoValida($campo_esperados_email);
                if (!empty($campos_errores)) {
                    throw new Exception("Los siguientes campos no cumplen con la longitud mínima de 1 carácter o el máximo de caracteres indicado:");
                }


                //Se verifica que el email ingresado sea valido
                validarCampoEmail($_POST['email']);

                //Verifica que el password no tenga espacios al inicio o al final de la cadena
                validarCampoPassword($_POST['password']);


                //Se obtiene los datos de la solicitud POST
                $nuevo_email = $_POST['email'];
                $password = $_POST['password'];

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
                    $estado = 'info';
                    throw new Exception("El email ingresado es el mismo con el que estas registrado");
                }

                //Se verifica que el nuevo email ingresado no este registrado en el sistema por otro usuario
                if (verificarSiEmailEstaDisponible($conexion, $nuevo_email)) {
                    throw new Exception("Ya hay otro usuario registrado con el E-mail ingresado.");
                }

                //Se envia un email al nuevo email ingresado para confirmar que el usuario tenga acceso a el
                verificarNuevoEmailIngresado($conexion, $id_usuario, $nuevo_email, $token_email);


                $respuesta['estado'] = 'success';
                $respuesta['mensaje'] = 'Se ha enviado un correo de confirmacion a tu nuevo Email por favor revise su bandeja de entrada y haga clic en el enlace de confirmacion para terminar el proceso';

                break;

                //Si el tipo de modificacion es el password va a modificar la contraseña del usuario
            case 'password':


                // Campos esperados en la solicitud POST
                $campo_esperados_password = [
                    'password_actual' => ['label' => 'Contraseña actual', 'length_minimo' => 6, 'length_maximo' => 60],
                    'password_nueva' => ['label' => 'Nueva contraseña', 'length_minimo' => 6, 'length_maximo' => 60],
                    'password_nueva_confirmacion' => ['label' => 'Confirmar nueva contraseña', 'length_minimo' => 6, 'length_maximo' => 60]
                ];

                //Verifica la entrada de datos esperados
                $mensaje = verificarEntradaDatosMatriz($campo_esperados_password, $_POST);
                if (!empty($mensaje)) {
                    throw new Exception($mensaje);
                }


                //Verifica que los campos de texto no tenga espacios al inicio o al final de la cadena
                $campos_errores = listaCamposConEspacioInicioFin($campo_esperados_password, $_POST);
                if (!empty($campos_errores)) {
                    throw new Exception("No se permite que los campos tengan espacios en blanco. Los siguientes campos no cumplen con esto:");
                }


                //Verifica que la longitud de caracteres sea valida 
                $campos_errores = listaConLongitudNoValida($campo_esperados_password);
                if (!empty($campos_errores)) {
                    throw new Exception("Los siguientes campos no cumplen con la longitud mínima de 6 carácteres o el máximo de caracteres indicado:");
                }

                //Verifica que el password actual no tenga espacios al inicio o al final de la cadena
                validarCampoPassword($_POST['password_actual']);

                //Verifica que el nuevo password no tenga espacios al inicio o al final de la cadena
                validarCampoPassword($_POST['password_nueva']);

                //Verifica que la nueva contraseña sea igual a la confirmacion de la nueva contraseña 
                validarIgualdadCamposPasswordsPost('password_nueva', 'password_nueva_confirmacion');

                //Se obtiene los datos del usuario por el id de usuario 
                $datos_usuario = obtenerDatosUsuarioPorIdUsuario($conexion, $id_usuario);

                //Se obtiene los datos de la solicitud POST
                $password_actual = $_POST['password_actual'];
                $password_nueva = $_POST['password_nueva'];


                //Se verifica que la contraseña recibida sea igual a la contraseña de la cuenta del usuario
                if (!password_verify($password_actual, $datos_usuario["contrasenia"])) {
                    throw new Exception("La contraseña ingresada es incorrecta");
                }


                //Verifica que la nueva contraseña no sea igual a la contraseña anterior
                if ($password_actual == $password_nueva) {
                    $estado = 'info';
                    throw new Exception("La nueva contraseña no puede ser la misma que la contraseña actual. Por favor ingrese una contraseña diferente");
                }

                //Convierte la nueva contraseña en un formato cifrado y seguro. 
                $nueva_password = password_hash($password_nueva, PASSWORD_DEFAULT);


                //Se guarda la nueva contraseña del usuario 
                modificarContraseniaUsuario($conexion, $id_usuario, $nueva_password);

                $respuesta['estado'] = 'success';
                $respuesta['mensaje'] = 'La contraseña se ha cambiado correctamente. Ahora se puede iniciar sesión con la nueva contraseña.';

                break;

            default:
                throw new Exception("El tipo de modificacion de datos no es valido");
        }
    } else {
        throw new Exception("No se recibio una solicitud POST.");
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['mensaje'] = $e->getMessage();
    $respuesta['lista'] = $campos_errores;
    $respuesta["estado"] = $estado;
}

//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
