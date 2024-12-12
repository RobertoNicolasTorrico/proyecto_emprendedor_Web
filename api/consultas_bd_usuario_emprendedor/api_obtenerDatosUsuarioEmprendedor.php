<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_verificaciones.php");


//Campos esperados en la solicitud POST
$campo_esperados = array("id_usuario", "tipo_usuario");
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
        $id_usuario = $_GET['id_usuario'];
        $tipo_usuario = $_GET['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();


        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario,  $tipo_usuario)) {
            throw new Exception("No se pudo obtener los datos del usuario emprendedor debido a que su cuenta no esta activa o esta baneado");
        }
        //Se obtiene los datos del emprendedor
        $respuesta['datosUsuarioEmprendedor'] = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);

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
