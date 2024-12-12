<?php

//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_publicaciones.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/config_define.php");

//Campos esperados en la solicitud POST
$campo_esperados = array("id_usuario", "tipo_usuario", "id_publicacion",);
$respuesta = [];

try {
    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Se obtiene los datos de la solicitud POST
        $id_publicacion = $_POST['id_publicacion'];
        $id_usuario = $_POST['id_usuario'];
        $tipo_usuario = $_POST['tipo_usuario'];


        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede eliminar la publicacion debido a que su cuenta no esta activa o esta baneado");
        }

        //Verifica que el usuario sea un usuario emprendedor
        if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
            throw new Exception("No se pudo eliminar la publicacion debido a que no es un usuario emprendedor");
        }

        //Valida que los campos ocultos solo contengan numeros
        if (!is_numeric($id_publicacion)) {
            throw new Exception("Los campos ocultos solo deben contener numeros");
        }

        //Verifica si la publicacion existe
        if (!verificarSiLaPublicacionExiste($conexion, $id_publicacion)) {
            throw new Exception("La publicacion ya fue eliminado previamente. Por favor actualiza la pantalla para ver los cambios");
        }

        //Verifica si el usuario hizo la publicacion
        if (!elUsuarioHizoLaPublicacion($conexion, $id_usuario, $id_publicacion)) {
            throw new Exception("Un usuario no puede eliminar las publicaciones que no halla hecho");
        }

        //Se elimina la publicacion de la cuenta del usuario
        bajaPublicacionInformacion($conexion, $id_publicacion);
        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Se elimino la publicacion correctamente';


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
