<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_generales.php");

// Campos esperados en la solicitud POST
$campo_esperados = array("id_usuario", "tipo_usuario", "password_actual", "password_nueva", "password_nueva_confirmacion");

$campos_modificar = [
    'password_actual' => ['label' => 'Contraseña actual', 'length_minimo' => 6, 'length_maximo' => 60],
    'password_nueva' => ['label' => 'Nueva contraseña', 'length_minimo' => 6, 'length_maximo' => 60],
    'password_nueva_confirmacion' => ['label' => 'Confirmar nueva contraseña', 'length_minimo' => 6, 'length_maximo' => 60]
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
        $password_actual = $_POST['password_actual'];
        $password_nueva = $_POST['password_nueva'];


        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No puede modificar los datos del usuario debido a que su cuenta no esta activa o esta baneado");
        }


        //Verifica que los campos de texto no tenga espacios al inicio o al final de la cadena
        $campos_errores = listaCamposConEspacioInicioFin($campos_modificar, $_POST);
        if (!empty($campos_errores)) {
            $errores_texto = implode(", ", $campos_errores);
            throw new Exception("No se permite que los campos tengan espacios en blanco. Los siguientes campos no cumplen con esto: " . $errores_texto . ".");
        }


        //Verifica que la longitud de caracteres sea valida 
        $campos_errores = listaConLongitudNoValida($campos_modificar);
        if (!empty($campos_errores)) {
            $errores_texto = implode(", ", $campos_errores);
            throw new Exception("Los siguientes campos no cumplen con la longitud mínima de 6 carácteres o el máximo de caracteres indicado:: " . $errores_texto . ".");
        }


        //Verifica que el password actual no tenga espacios al inicio o al final de la cadena
        validarCampoPassword($password_actual);

        //Verifica que el nuevo password no tenga espacios al inicio o al final de la cadena
        validarCampoPassword($password_nueva);

        //Verifica que la nueva contraseña sea igual a la confirmacion de la nueva contraseña 
        validarIgualdadCamposPasswordsPost('password_nueva', 'password_nueva_confirmacion');

        //Se obtiene los datos del usuario por el id de usuario 
        $datos_usuario = obtenerDatosUsuarioPorIdUsuario($conexion, $id_usuario);

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
        $respuesta['mensaje'] = 'Tu contraseña se ha restablecido correctamente.Ahora puedes iniciar sesion con tu nueva contraseña';


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
