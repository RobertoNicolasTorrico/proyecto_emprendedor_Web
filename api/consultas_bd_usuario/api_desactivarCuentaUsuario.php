<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_token.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/config_define.php");



//Campos esperados en la solicitud POST
$campo_esperados = array("id_usuario", "tipo_usuario", "password");


//Limite de caracteres para la contraseña
$password_datos = [
    'cant_min' => 6,
    'cant_max' => 60,
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

        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No puede desactivar su cuenta debido a que ya no esta activa o se encuentra baneada");
        }


        //Se obtiene los datos de la solicitud POST
        $password = $_POST['password'];

        //Verifica que el password no tenga espacios al inicio o al final de la cadena
        validarCampoPassword($password);


        //Verifica que la longitud de caracteres sea valida 
        if (verificarLongitudTexto($password, $password_datos['cant_min'], $password_datos['cant_max'])) {
            throw new Exception("El campo contraseña no cumple con la longitud minima de 6 carácteres o el máximo de caracteres indicado");

        }

        //Se obtiene los datos del usuario
        $datos_usuario = obtenerDatosUsuarioPorIdUsuario($conexion, $id_usuario);

        //Se verifica que la contraseña recibida sea igual a la contraseña de la cuenta del usuario
        if (!password_verify($password, $datos_usuario["contrasenia"])) {
            throw new Exception("La contraseña ingresada es incorrecta");
        }

        //Se verifica que el usuario sea un emprendedor
        if (verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {

            //En caso que lo sea se busca los datos del usuario empprendedor
            $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
            if (empty($usuario_emprendedor)) {
                throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
            }
            $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];
            //Se modifica el estado de los productos del emprendedor a finalizado
            modificarEstadoPublicacionesProductosDeEmprendedorAFinalizar($conexion, $id_usuario_emprendedor);
        }


        //Se desactiva la cuenta del usuario
        desactivarCuentaUsuario($conexion, $id_usuario);
        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Su cuenta ha sido desactivada. La sesion se cerrara en algunos segundos';

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
