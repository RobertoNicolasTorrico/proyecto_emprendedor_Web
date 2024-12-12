<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../../config/consultas_bd/consultas_seguimiento.php");
include("../../../../config/consultas_bd/consultas_notificacion.php");
include("../../../../config/funciones/funciones_verificaciones.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/config_define.php");


//Campos esperados en la solicitud POST
$campo_esperados = array("id_usuario_eliminar_seguidor_emprendedor");

$respuesta = [];


//Inicializacion de variables obtenidas de la URL
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';
$id_usuario_token = isset($_GET['token']) ? $_GET['token'] : '';


try {

    //Verifica si la solicitud es POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {


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
            throw new Exception("Debe iniciar sesion para poder dejar de seguir aun usuario emprendedor");
        }

        //Se obtiene los datos de sesion
        $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
        $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario administrador es valido
        if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
            throw new Exception("No se puede dejar de seguir al usuario emprendedor por que no es usuario administrador valido");
        }


        //Verifica si la cuenta del usuario sigue existe
        if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
            throw new Exception("Esta cuenta fue eliminada previamente. Por favor regrese a la página anterior");
        }


        //Se obtiene los datos de la solicitud POST
        $id_usuario_emprendedor = $_POST['id_usuario_eliminar_seguidor_emprendedor'];

        //Valida que los campos ocultos solo contengan numeros
        if (!is_numeric($id_usuario_emprendedor)) {
            throw new Exception("Los campos ocultos solo deben contener numeros");
        }

        //Se obtiene los datos del usuario emprendedor por el id del usuario emprendedor
        $datos_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuarioEmprendedor($conexion, $id_usuario_emprendedor);
        if (empty($datos_emprendedor)) {
            throw new Exception("No se pudo conseguir la informacion necesaria del usuario emprendedor");
        }

        //Se verifica que el seguidor sigue actualmente al emprendedor
        if (!verificarSiElUsuarioSigueAlEmprendedor($conexion, $id_usuario_emprendedor, $id_usuario)) {
            throw new Exception("Ya no sigue al emprendedor seleccionado. Por favor actualiza la página para ver los cambios");
        }


        //Se elimina al seguidor de los seguidores del usuario emprendedor
        bajaSeguimientoUsuario($conexion, $id_usuario_emprendedor, $id_usuario);

        //Se elimina la notificacion que el usuario lo sigue
        bajaNotificacionSeguirUsuario($conexion, $datos_emprendedor['id_usuario'], $id_usuario);


        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'El usuario ya no sigue al emprendedor';
    } else {
        throw new Exception("No se recibio una solicitud POST.");
    }
} catch (Exception $e) {

    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}

//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
