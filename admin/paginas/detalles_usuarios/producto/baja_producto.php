<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/consultas_bd/consultas_notificacion.php");
include("../../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/funciones/funciones_verificaciones.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/config_define.php");


//Campos esperados en la solicitud POST
$campo_esperados = array("id_producto_eliminar");

$respuesta = [];


//Inicializacion de variables obtenidas de la URL
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';
$id_usuario_token = isset($_GET['token']) ? $_GET['token'] : '';

try {
    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {

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
            throw new Exception("Debe iniciar sesion para poder eliminar una publicacion del producto");
        }

        //Se obtiene los datos de sesion
        $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
        $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario administrador es valido
        if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
            throw new Exception("No se puede eliminar el producto por que no es usuario administrador valido");
        }


        //Verifica si la cuenta del usuario sigue existe
        if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
            throw new Exception("Esta cuenta fue eliminada previamente. Por favor regrese a la página anterior");
        }

        //Se obtiene los datos de la solicitud POST
        $id_producto = $_POST['id_producto_eliminar'];

        //Valida que los campos ocultos solo contengan numeros
        if (!is_numeric($id_producto)) {
            throw new Exception("Los campos ocultos solo deben contener numeros");
        }


        //Verifica si producto existe
        if (!verificarSiElProductoExiste($conexion, $id_producto)) {
            throw new Exception("El producto ya fue eliminado previamente. Por favor actualiza la pagina para ver los cambios");
        }


        //Verifica si el usuario hizo la publicacion
        if (!elUsuarioLoPublico($conexion, $id_producto, $id_usuario)) {
            throw new Exception("Un usuario no puede eliminar un producto que no halla publicado");
        }


        //Se elimina todas las notificaciones relacionado con el producto
        bajaNotificacionProducto($conexion, $id_producto);

        //Se elimina todas las preguntas y respuesta del producto
        bajaTodasPreguntasDelProducto($conexion, $id_producto);

        //Se elimina la publicacion de la cuenta del usuario emprendedor
        bajaPublicacionProducto($conexion, $id_producto);

        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Se elimino la publicacion del producto correctamente';
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
