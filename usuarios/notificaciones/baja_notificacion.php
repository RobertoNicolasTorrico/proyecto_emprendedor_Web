<?php

//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_notificacion.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_session.php");


//Campos esperados en la solicitud POST
$campo_esperados = array("id_notificacion");

$respuesta = [];

try {

    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {


        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Establecer la sesion
        session_start();

        //Verificar los datos de sesion del usuario
        if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
            throw new Exception("Debe iniciar sesion para poder eliminar las notificaciones");
        }

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION['id_usuario'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede eliminar las notificaciones debido a que su cuenta no esta activa o esta baneado");
        }


        //Se obtiene los datos de la solicitud POST
        $id_notificacion = $_POST['id_notificacion'];

        //Valida que los campos ocultos solo contengan numeros
        if (!is_numeric($id_notificacion)) {
            throw new Exception("Los campos ocultos solo deben contener numeros");
        }

        //Se elimina la notificacion de la cuenta del usuario
        bajaNotificacion($conexion, $id_notificacion, $id_usuario);
        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Se elimino la notificacion correctamente';
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
