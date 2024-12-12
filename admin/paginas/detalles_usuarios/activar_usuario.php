<?php

//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_usuario.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/funciones/funciones_token.php");
include("../../../config/funciones/funciones_generales.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/config_define.php");

$respuesta = [];


//Inicializacion de variables obtenidas de la URL
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';
$id_usuario_token = isset($_GET['token']) ? $_GET['token'] : '';

try {
    //Verifica si la solicitud es POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        //Se verifica que los datos recibidos de la URL sean validos
        verificarUrlTokenId($id_usuario, $id_usuario_token);

        //Establecer la sesion
        session_start();

        //Verificar los datos de sesion del usuario administrador
        if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
            throw new Exception("Debe iniciar sesion para poder activar la cuenta de un usuario");
        }

        //Se obtiene los datos de sesion
        $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
        $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario administrador es valido
        if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
            throw new Exception("No se puede activar la cuenta de usuario por que no es usuario administrador valido");
        }

        //Verifica si la cuenta del usuario sigue existe
        if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
            throw new Exception("Esta cuenta fue eliminada previamente. Por favor regrese a la página anterior");
        }

        //Se activa la cuenta del usuario por medio del usuario administrador
        activarCuentaUsuario($conexion, $id_usuario);
        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Se activo la cuenta del usuario se actualizada la pagina en algunos segundos';
    } else {

        //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
        throw new Exception("No se recibio una solicitud POST.");
    }
} catch (Exception $e) {
    $respuesta['mensaje'] = $e->getMessage();
    $respuesta["estado"] = "danger";
}

//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
