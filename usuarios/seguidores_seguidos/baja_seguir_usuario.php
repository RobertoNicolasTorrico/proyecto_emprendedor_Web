<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_seguimiento.php");
include("../../config/consultas_bd/consultas_notificacion.php");
include("../../config/funciones/funciones_token.php");
include("../../config/funciones/funciones_session.php");
include("../../config/config_define.php");



$id_usuario_emprendedor_perfil = isset($_GET['id']) ? $_GET['id'] : '';
$id_usuario_emprendedor_perfil_token = isset($_GET['token']) ? $_GET['token'] : '';

try {
    //Verifica si la solicitud es POST 
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        //Se verifica que los datos recibidos de la URL sean validos
        verificarUrlTokenId($id_usuario_emprendedor_perfil, $id_usuario_emprendedor_perfil_token);


        //Establecer la sesion
        session_start();

        //Verificar los datos de sesion del usuario
        if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
            throw new Exception("Debe haber iniciar sesion para poder dejar seguir a un emprendedor");
        }

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION['id_usuario'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede dejar de seguir al emprendedor debido a que su cuenta no esta activa o esta baneado");
        }

        //Verifica que el usuario emprendedor no pueda dejar de seguirse
        if (esPerfilDelUsuario($conexion, $id_usuario, $id_usuario_emprendedor_perfil)) {
            throw new Exception("Un usuario emprendedor no puede dejar de seguirse asi mismo");
        }

        //Se verifica que el seguidor sigue actualmente al emprendedor
        if (!verificarSiElUsuarioSigueAlEmprendedor($conexion, $id_usuario_emprendedor_perfil, $id_usuario)) {
            throw new Exception("Ya no sigues a este emprendedor. Por favor actualiza la página para ver los cambios");
        }

        //Se elimina al seguidor de los seguidores del usuario emprendedor
        bajaSeguimientoUsuario($conexion, $id_usuario_emprendedor_perfil, $id_usuario);


        //Se obtiene los datos del emprendedor 
        $usuario_emprendedor_perfil = obtenerDatosUsuarioEmprendedorPorIDUsuarioEmprendedor($conexion, $id_usuario_emprendedor_perfil);
        if (empty($usuario_emprendedor_perfil)) {
            throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
        }

        //Se obtiene la cantidad de seguidores del usuario emprendedor
        $cant_total_seguidores = cantTotalSeguidoresUsuarioEmprendedor($conexion, $usuario_emprendedor_perfil['id_usuario']);
        $respuesta['num_seguidores'] = $cant_total_seguidores;



        //Se obtiene la cantidad de emprendedores que sigue el usuario
        $cant_total_seguidos = cantTotalSeguimientoUsuario($conexion, $usuario_emprendedor_perfil['id_usuario']);
        $respuesta['num_seguidos'] = $cant_total_seguidos;


        //Se obtiene los datos a que usuario eliminar la notificacion
        $id_usuario_notificar = $usuario_emprendedor_perfil['id_usuario'];
        $respuesta['id_usuario_notificar'] = $id_usuario_notificar;
        $respuesta['id_usuario_interaccion'] = $id_usuario;


        //Se elimina la notificacion que el usuario lo sigue
        bajaNotificacionSeguirUsuario($conexion, $id_usuario_notificar, $id_usuario);

        //Se obtiene la cantidad de emprendedores que sigue el usuario en su cuenta
        $numero_seguidos_usuario = cantTotalSeguimientoUsuario($conexion, $id_usuario);
        $respuesta['numero_seguidos_usuario'] = $numero_seguidos_usuario;
        $respuesta['estado'] = 'success';
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
