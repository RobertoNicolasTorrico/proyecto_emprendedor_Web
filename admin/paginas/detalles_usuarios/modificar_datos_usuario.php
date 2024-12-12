<?php

//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/consultas_bd/consultas_usuario.php");
include("../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../config/funciones/funciones_verificaciones.php");
include("../../../config/funciones/funciones_generales.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/funciones/funciones_token.php");
include("../../../config/config_define.php");


$respuesta['lista'] = "";
$respuesta["mensaje"] = "";
$respuesta["estado"] = "";
$campos_errores = [];
$estado = 'danger';


//Inicializacion de variables obtenidas de la URL
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';
$id_usuario_token = isset($_GET['token']) ? $_GET['token'] : '';

try {

    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {

        //Se verifica que los datos recibidos de la URL sean validos
        verificarUrlTokenId($id_usuario, $id_usuario_token);


        //Establecer la sesion
        session_start();

        //Verificar los datos de sesion del usuario administrador
        if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
            throw new Exception("Debe iniciar sesion para poder modificar los datos de un usuario");
        }

        //Se obtiene los datos de sesion
        $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
        $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario administrador es valido
        if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
            throw new Exception("No se puede modificar los datos de un usuario por que no es usuario administrador valido");
        }


        //Verifica si la cuenta del usuario sigue disponible
        if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
            throw new Exception("Esta cuenta fue eliminada previamente. Por favor regrese a la página anterior");
        }

        //Se obtiene los datos de la solicitud POST
        $tipo_modificacion = $_POST['tipo_modificacion'];

        switch ($tipo_modificacion) {
                //Si el tipo de modificacion es usuario se va a modificar los datos personales del usuario
            case 'usuario':

                //Campos esperado del usuario en la solicitud POST
                $campo_esperados = [
                    'nombre_usuario' => ['label' => 'Nombre de usuario', 'length_minimo' => 5, 'length_maximo' => 20],
                    'nombres' => ['label' => 'Nombres', 'length_minimo' => 1, 'length_maximo' => 50],
                    'apellidos' => ['label' => 'Apellidos', 'length_minimo' => 1, 'length_maximo' => 50],
                    'email' => ['label' => 'Email', 'length_minimo' => 1, 'length_maximo' => 320],
                    'estado_baneado' => ['label' => 'Estado Baneado', 'length_minimo' => 1, 'length_maximo' => 1],
                    'fecha_registro' => ['label' => 'Fecha de registro', 'length_minimo' => 10, 'length_maximo' => 20],
                ];

                //Verifica la entrada de datos esperados
                $mensaje = verificarEntradaDatosMatriz($campo_esperados, $_POST);
                if (!empty($mensaje)) {
                    throw new Exception($mensaje);
                }


                $nombre_usuario = $_POST['nombre_usuario'];

                //Verifica que la longitud de caracteres sea valida 
                if (verificarLongitudTexto($nombre_usuario, $campo_esperados['nombre_usuario']['length_minimo'], $campo_esperados['nombre_usuario']['length_maximo'])) {
                    throw new Exception("El campo nombre de usuario debe tener entre 5 y 20 caracteres");
                }


                //Verifica que la longitud de los campos de textos de caracteres sea valida 
                $campos_errores = listaCamposConEspacioInicioFin($campo_esperados, $_POST);
                if (!empty($campos_errores)) {
                    throw new Exception("No se permite que los campos tengan espacios en blanco al inicio o al final. Los siguientes campos no cumplen con esto:");
                }

                //Verifica que la longitud de los campos de textos de caracteres sea valida 
                $campos_errores = listaConLongitudNoValida($campo_esperados);
                if (!empty($campos_errores)) {
                    throw new Exception("Los siguientes campos no cumplen con la longitud mínima de 1 carácter o el máximo de caracteres indicado:");
                }



                //Se obtiene los datos del usuario por el id de usuario 
                $datos_usuario = obtenerDatosUsuarioPorIdUsuario($conexion, $id_usuario);

                //Se obtiene los datos de la solicitud POST
                $nombres = $_POST['nombres'];
                $apellidos = $_POST['apellidos'];
                $email = $_POST['email'];
                $fecha = $_POST['fecha_registro'];
                $baneado = $_POST['estado_baneado'];

                //Verifica que los nuevos datos recibidos no sean iguales a los datos originales del usuario
                if ($fecha == $datos_usuario['fecha']  && $email == $datos_usuario['email'] && $baneado == $datos_usuario['baneado'] && $nombre_usuario == $datos_usuario['nombre_usuario'] && $nombres ==  $datos_usuario['nombres'] && $apellidos == $datos_usuario['apellidos']) {
                    $estado = 'info';
                    throw new Exception("No hubo cambios en los datos del usuario");
                }


                //Verifica si se cambio el email del usuario
                if ($email != $datos_usuario['email']) {

                    //Se verifica que el email ingresado sea valido
                    validarCampoEmail($email);

                    //Se verifica que el email ingresado no este registrado en el sistema por otro usuario 
                    if (verificarSiEmailEstaDisponible($conexion, $email)) {
                        throw new Exception("Ya hay otro usuario registrado con el E-mail ingresado.");
                    }
                }

                //Verifica si se cambio el nombre de usuario 
                if ($nombre_usuario != $datos_usuario['nombre_usuario']) {

                    //Se verifica que el nuevo de usuario ingresado no este registrado en el sistema por otro usuario
                    if (verificarSiNombreUsuarioEstaDisponible($conexion, $nombre_usuario)) {
                        throw new Exception("El nombre de usuario ingresado no esta disponible.");
                    }
                }

                //Se guarda los datos modificados del usuario
                modificarDatosUsuario($conexion, $id_usuario, $nombre_usuario, $nombres, $apellidos, $email, $fecha, $baneado);
                $respuesta['estado'] = 'success';
                $respuesta['mensaje'] = 'La informacion del usuario fue modificada correctamente';
                break;

            case 'emprendedor':

                //Limite de caracteres para la descripcion 
                $descripcion_datos = [
                    'cant_min' => 1,
                    'cant_max' => 150,
                ];

                //Limite de caracteres para el nombre del emprendedor 
                $emprendimiento_datos = [
                    'cant_min' => 1,
                    'cant_max' => 50,
                ];

                $nombre_emprendimiento = isset($_POST['nombre_emprendimiento']) ? $_POST['nombre_emprendimiento'] : "";
                if (empty($nombre_emprendimiento)) {
                    throw new Exception("El campo Nombre del emprendimiento no puede estar vacio");
                }


                //Configracion de archivos permitidos
                $archivo_extensiones_imagen =  ['png', 'jpeg', 'jpg'];

                //Se obtiene los datos de la solicitud POST
                $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";
                $select_calificacion = isset($_POST['select_calificacion']) ? $_POST['select_calificacion'] : null;
                $file = (isset($_FILES['file'])) ? $_FILES['file'] : "";

                //Se obtiene los datos del emprendimiento por el ID del usuario
                $datos_usuario = obtenerDatosUsuarioYUsuarioEmprendedorPorIdUsuario($conexion, $id_usuario);
                $id_usuario_emprendedor = $datos_usuario['id_usuario_emprendedor'];
                $foto_perfil_actual = $datos_usuario['foto_perfil_nombre'];


                //Verifica que los nuevos datos recibidos no sean iguales a la descripcion del emprendedor
                if ($datos_usuario['nombre_emprendimiento'] == $nombre_emprendimiento && $descripcion ==  $datos_usuario['descripcion'] && empty($file) &&                 ($datos_usuario['calificacion_emprendedor'] == $select_calificacion || (is_null($datos_usuario['calificacion_emprendedor']) && $select_calificacion == 'sin_califcacion'))) {
                    $estado = 'info';
                    throw new Exception("No hubo cambios en los datos del emprendimiento");
                }



                //Verifica si se cambio el nombre del emprendimiento del usuario
                if ($datos_usuario['nombre_emprendimiento'] != $nombre_emprendimiento) {


                    //Se verifica que el nuevo del emprendimiento ingresado no este registrado en el sistema por otro usuario administrador
                    if (verificarSiNombreEmprendedorEstaDisponibles($conexion, $nombre_emprendimiento)) {
                        throw new Exception("Ya hay otro usuario emprendedor que esta usando el nombre del emprendimiento ingresado");
                    }


                    //Verifica que la descripcion no tenga espacios al inicio o al final de la cadena
                    if (tieneEspaciosInicioFinCadena($nombre_emprendimiento)) {
                        throw new Exception("El campo nombre del emprendimiento no puede tener espacios en blanco al inicio o al final");
                    }


                    //Verifica que la longitud de caracteres sea valida 
                    if (verificarLongitudTexto($nombre_emprendimiento, $emprendimiento_datos['cant_min'], $emprendimiento_datos['cant_max'])) {
                        throw new Exception("El campo nombre del emprendimiento debe tener entre 1 y 50 caracteres");
                    }
                }

                //Verifica que el campo select de la calificacion del emprendedor sea valido
                if (!validarCampoSelectCalificacionEmprendedor($select_calificacion)) {
                    throw new Exception("Los valores en el select no son validos.");
                }
                //En caso que el valor sea  "null" en formato texto se pasa a NULL la calificacion del emprendedor
                if ($select_calificacion == "null") {
                    $calificacion_emprendedor = NULL;
                } else {
                    $calificacion_emprendedor = $select_calificacion;
                }

                //Verifica que la descripcion del usuario este vacia
                if (!empty($descripcion)) {
                    //Verifica que la descripcion no tenga espacios al inicio o al final de la cadena
                    if (tieneEspaciosInicioFinCadena($descripcion)) {
                        throw new Exception("El campo descripcion no debe tener espacios en blanco al inicio o al final");
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
                        throw new Exception("Error al subir archivos.");
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
                        throw new Exception("El formato del archivo " . $_FILES['file']['name'] . " no es valido. Formatos permitidos: JPEG, JPG y PNG");
                    }
                }


                //Se guarda las modificaciones del emprendimiento de la cuenta del usuario
                modificarDatosEmprendedorPorAdministrador($conexion, $id_usuario_emprendedor, $nombre_emprendimiento, $descripcion, $file, $calificacion_emprendedor, $foto_perfil_actual);
                $respuesta['estado'] = 'success';
                $respuesta['mensaje'] = 'La informacion del emprendimiento fue modificada correctamente';
                break;


                //Si el tipo de modificacion es el password va a modificar la contraseña del usuario
            case 'password':

                // Campos esperados en la solicitud POST
                $campo_esperados = [
                    'password_nueva' => ['label' => 'Nueva contraseña', 'length_minimo' => 6, 'length_maximo' => 60],
                    'password_nueva_confirmacion' => ['label' => 'Confirmar nueva contraseña', 'length_minimo' => 6, 'length_maximo' => 60]
                ];

                //Verifica la entrada de datos esperados
                $mensaje = verificarEntradaDatosMatriz($campo_esperados, $_POST);
                if (!empty($mensaje)) {
                    throw new Exception($mensaje);
                }

                //Verifica que los campos de texto no tenga espacios al inicio o al final de la cadena
                $campos_errores = listaCamposConEspacioInicioFin($campo_esperados, $_POST);
                if (!empty($campos_errores)) {
                    throw new Exception("No se permite que los campos tengan espacios en blanco. Los siguientes campos no cumplen con esto:");
                }

                //Verifica que la longitud de caracteres sea valida 
                $campos_errores = listaConLongitudNoValida($campo_esperados);
                if (!empty($campos_errores)) {
                    throw new Exception("Los siguientes campos no cumplen con la longitud mínima de 6 carácteres o el máximo de caracteres indicado:");
                }


                //Verifica que el nuevo password no tenga espacios al inicio o al final de la cadena
                validarCampoPassword($_POST['password_nueva']);

                //Verifica que la nueva contraseña sea igual a la confirmacion de la nueva contraseña 
                validarIgualdadCamposPasswordsPost('password_nueva', 'password_nueva_confirmacion');

                //Se obtiene los datos de la solicitud POST
                $password_nueva = $_POST['password_nueva'];

                //Convierte la nueva contraseña en un formato cifrado y seguro. 
                $nueva_password = password_hash($password_nueva, PASSWORD_DEFAULT);

                //Se guarda la nueva contraseña del usuario 
                modificarContraseniaUsuario($conexion, $id_usuario, $nueva_password);
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
