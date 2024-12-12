<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_notificacion.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_seguimiento.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/config_define.php");


//Campos esperados en la solicitud POST
$campo_esperados = array("id_usuario", "tipo_usuario", "id_usuario_emprendedor");

$respuesta = [];
try {
    //Verifica si la solicitud es POST 
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        //Se verifica que los datos recibidos de la URL sean validos
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Se obtiene los datos de la solicitud POST
        $id_usuario = $_POST['id_usuario'];
        $tipo_usuario = $_POST['tipo_usuario'];
        $id_usuario_emprendedor_perfil = $_POST['id_usuario_emprendedor'];

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
            throw new Exception("Ya no sigues a este emprendedor. Por favor actualiza la pantalla para ver los cambios");
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
