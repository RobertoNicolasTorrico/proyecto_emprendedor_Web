<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_publicaciones.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_verificaciones.php");


// Campos esperados en la solicitud GET
$campo_esperados = array("id_usuario", "tipo_usuario", "id_publicacion");
$respuesta = [];


try {
    //Verifica si la solicitud es GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {


        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_GET);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }


        //Se obtiene los datos de la solicitud GET
        $id_publicacion = $_GET['id_publicacion'];
        $id_usuario = $_GET['id_usuario'];
        $tipo_usuario = $_GET['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();


        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede ver la informacion de la publicacion debido a que su cuenta no esta activa o esta baneado");
        }


        //Verifica que el usuario sea un usuario emprendedor
        if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
            throw new Exception("No puede obtener la informacion de la publicacion ya que no es un usuario emprendedor");
        }


        //Verifica si el usuario hizo la publicacion
        if (!elUsuarioHizoLaPublicacion($conexion, $id_usuario, $id_publicacion)) {
            throw new Exception("No se puede obtener la informacion de la publicacion debido que no le pertenecen al mismo usuario");
        }

        //Se obtiene los datos del usuario emprendedor por el id del usuario
        $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
        if (empty($usuario_emprendedor)) {
            throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
        }

        $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];

        //Se obtiene la informacion de la publicacion
        $detalles_publicacion = obtenerPublicacionDelUsuarioEmprendedor($conexion, $id_publicacion, $id_usuario_emprendedor);

        //Se obtiene los archivos de la publicacion
        $archivos_publicacion =  obtenerListaArchivosPublicaciones($conexion, $id_publicacion);

        $respuesta['detalles_publicacion'] = $detalles_publicacion;
        $respuesta['archivos'] = $archivos_publicacion;

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
