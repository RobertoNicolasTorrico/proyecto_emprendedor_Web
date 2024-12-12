<?php

//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/funciones/funciones_verificaciones.php");
include("../../../config/funciones/funciones_generales.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/funciones/funciones_token.php");
include("../../../config/config_define.php");


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

        //Verificar los datos de sesion del usuario administrador
        if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
            throw new Exception("Debe iniciar sesion para poder modificar la informacion del usuario administrador");
        }

        //Se obtiene los datos de sesion
        $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
        $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario administrador es valido
        if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
            throw new Exception("No se puede modificar los datos del usuario administrador por que no es usuario administrador valido");
        }

        //Se obtiene los datos de la solicitud POST
        $tipo_modificacion = $_POST['tipo_modificacion'];

        switch ($tipo_modificacion) {

                //Si el tipo de modificacion es usuario se va a modificar los datos personales del usuario administrador
            case 'usuario':

                //Campos esperado del usuario en la solicitud POST
                $campo_esperados_usuario = [
                    'nombre_usuario' => ['label' => 'Nombre de usuario', 'length_minimo' => 5, 'length_maximo' => 20],
                    'nombres' => ['label' => 'Nombres', 'length_minimo' => 1, 'length_maximo' => 100],
                    'apellidos' => ['label' => 'Apellidos', 'length_minimo' => 1, 'length_maximo' => 100],
                    'email' => ['label' => 'Email', 'length_minimo' => 1, 'length_maximo' => 320],
                ];

                //Verifica la entrada de datos esperados
                $mensaje = verificarEntradaDatosMatriz($campo_esperados_usuario, $_POST);
                if (!empty($mensaje)) {
                    throw new Exception($mensaje);
                }


                //Verifica que la longitud de los campos de textos de caracteres sea valida 
                $campos_errores = listaCamposConEspacioInicioFin($campo_esperados_usuario, $_POST);
                if (!empty($campos_errores)) {
                    throw new Exception("No se permite que los campos tengan espacios en blanco al inicio o al final. Los siguientes campos no cumplen con esto:");
                }



                //Verifica que la longitud de los campos de textos de caracteres sea valida 
                $campos_errores = listaConLongitudNoValida($campo_esperados_usuario);
                if (!empty($campos_errores)) {
                    throw new Exception("Los siguientes campos no cumplen con la longitud mínima de 1 carácter o el máximo de caracteres indicado:");
                }


                //Se obtiene los datos del usuario  administrador por el id de usuario administrador
                $datos_usuario = obtenerDatosUsuarioAdministrador($conexion, $id_usuario_administrador);


                //Obtener los datos de la solicitud POST
                $nombre_usuario = $_POST['nombre_usuario'];
                $nombres = $_POST['nombres'];
                $apellidos = $_POST['apellidos'];
                $email = $_POST['email'];

                //Verifica que los nuevos datos recibidos no sean iguales a los datos originales del usuario administrador 
                if ($email == $datos_usuario['email']  && $nombre_usuario == $datos_usuario['nombre_usuario'] && $nombres ==  $datos_usuario['nombres'] && $apellidos == $datos_usuario['apellidos']) {
                    $estado = 'info';
                    throw new Exception("No hubo cambios en los datos del usuario administrador");
                }


                //Verifica si se cambio el email del administrador
                if ($email != $datos_usuario['email']) {

                    //Se verifica que el email ingresado sea valido
                    validarCampoEmail($email);

                    //Se verifica que el email ingresado no este registrado en el sistema por otro usuario administrador
                    if (verificarSiEmailEstaDisponibleAdmin($conexion, $email)) {
                        throw new Exception("Ya hay otro usuario administrador registrado con el E-mail ingresado.");
                    }
                }

                //Verifica si se cambio el nombre de usuario del administrador
                if ($nombre_usuario != $datos_usuario['nombre_usuario']) {

                    //Se verifica que el nuevo de usuario ingresado no este registrado en el sistema por otro usuario administrador
                    if (verificarSiNombreUsuarioAdminEstaDisponible($conexion, $nombre_usuario)) {
                        throw new Exception("El nombre de usuario ingresado no esta disponible.");
                    }
                }

                //Se guarda los datos modificados del usuario administrador
                modificarDatosUsuarioAdmin($conexion, $id_usuario_administrador, $nombre_usuario, $nombres, $apellidos, $email);
                $respuesta['estado'] = 'success';
                $respuesta['mensaje'] = 'La informacion del usuario fue modificada correctamente';
                break;


                //Si el tipo de modificacion es el password va a modificar la contraseña del usuario administrador
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
                $datos_usuario = obtenerDatosUsuarioAdministrador($conexion, $id_usuario_administrador);

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
                    throw new Exception("La nueva contraseña es la misma que la contraseña actual");
                }

                //Convierte la nueva contraseña en un formato cifrado y seguro. 
                $nueva_password = password_hash($password_nueva, PASSWORD_DEFAULT);


                //Se guarda la nueva contraseña del usuario administrador
                modificarContraseniaUsuarioAdmin($conexion, $id_usuario_administrador, $nueva_password);

                $respuesta['estado'] = 'success';
                $respuesta['mensaje'] = 'Se cambio la contraseña del usuario, ahora el usuario podra iniciar sesion con la nueva contraseña ingresada';

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