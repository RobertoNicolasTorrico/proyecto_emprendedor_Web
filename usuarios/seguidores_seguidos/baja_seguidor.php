<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_seguimiento.php");
include("../../config/consultas_bd/consultas_notificacion.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_session.php");

//Campos esperados en la solicitud POST
$campo_esperados = array("id_usuario_eliminar");

$respuesta = [];



try {
    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST'  && !empty($_POST)) {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }


        //Establecer la sesion
        session_start();

        //Verificar los datos de sesion del usuario
        if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
            throw new Exception("Debe iniciar sesion para poder eliminar a un seguidor");
        }

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION['id_usuario'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();


        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede eliminar al seguidor debido a que su cuenta no esta activa o esta baneado");
        }

        //Se obtiene los datos de la solicitud POST
        $id_usuario_eliminar = $_POST['id_usuario_eliminar'];

        //Valida que los campos ocultos solo contengan numeros
        if (!is_numeric($id_usuario_eliminar)) {
            throw new Exception("Los campos ocultos solo deben contener numeros");
        }

        //Se obtiene los datos del usuario emprendedor por el id del usuario
        $usuario_emprendedor = obtenerDatosUsuarioYUsuarioEmprendedorPorIdUsuario($conexion, $id_usuario);
        $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];

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
