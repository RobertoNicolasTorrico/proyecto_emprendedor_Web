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
$campo_esperados = array("id_usuario", "tipo_usuario", "id_usuario_eliminar", "id_usuario_emprendedor");

$respuesta = [];
try {
    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST'  && !empty($_POST)) {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Se obtiene los datos de la solicitud POST
        $id_usuario_eliminar = $_POST['id_usuario_eliminar'];
        $id_usuario = $_POST['id_usuario'];
        $tipo_usuario = $_POST['tipo_usuario'];
        $id_usuario_emprendedor = $_POST['id_usuario_emprendedor'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Validar que los campos ocultos solo contengan numeros
        if (!is_numeric($id_usuario_eliminar)) {
            throw new Exception("Los campos ocultos solo deben contener numeros");
        }


        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede eliminar al seguidor debido a que su cuenta no esta activa o esta baneado");
        }


        //Se verifica que el seguidor sigue actualmente al emprendedor
        if (!verificarSiElUsuarioSigueAlEmprendedor($conexion, $id_usuario_emprendedor, $id_usuario_eliminar)) {
            throw new Exception("El usuario no te sigue actualmente");
        }

        //Se elimina al seguidor de los seguidores del usuario emprendedor
        bajaSeguimientoUsuario($conexion, $id_usuario_emprendedor, $id_usuario_eliminar);

        //Se obtiene la cantidad de seguidores del usuario emprendedor
        $cant_total_seguidores = cantTotalSeguidoresUsuarioEmprendedor($conexion, $id_usuario);
        $respuesta['num_seguidores'] = $cant_total_seguidores;

        //Se elimina la notificacion que el usuario lo sigue
        bajaNotificacionSeguirUsuario($conexion, $id_usuario, $id_usuario_eliminar);
        
        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Se elimino al seguidor correctamente';


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
