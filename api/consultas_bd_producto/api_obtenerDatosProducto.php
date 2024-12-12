
<?php

//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_productos.php");
include("../../config/funciones/funciones_verificaciones.php");

//Campos esperados en la solicitud POST
$campo_esperados = array("id_producto");


$respuesta = [];
$preguntasUsuario = [];
$usuario_valido = false;
$elUsuarioLoPublico = false;

try {
    //Verifica si la solicitud es GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {


        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_GET);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Se obtiene los datos de la solicitud GET
        $id_producto =  $_GET['id_producto'];
        $id_usuario =  $_GET['id_usuario'];
        $tipo_usuario =  $_GET['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Se verifica que el id del usuario y el tipo de usuario no son null
        if ($id_usuario  != null && $tipo_usuario != null) {

            //Se obtiene el resultado si el usuario es valido o no
            $usuario_valido = verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario);
            if ($usuario_valido) {
                //Se verifica si el usuario publico el producto o no
                $elUsuarioLoPublico = elUsuarioLoPublico($conexion, $id_producto, $id_usuario);
            }
        }



        //Se obtiene los datos de la publicacion del producto por el id del producto 
        $detalles_producto = obtenerDatosProducto($conexion, $id_producto);
        if (empty($detalles_producto)) {
            throw new Exception("No se pudo obtener la informacion de la publicacion. por favor intente mas tarde");
        }

        //Se obtiene los datos de las imagenes de la publicacion del producto por id del producto
        $imagenes_productos = obtenerListaImgProducto($conexion, $id_producto);
        if (empty($imagenes_productos)) {
            throw new Exception("No se pudo obtener la informacion de la publicacion. por favor intente mas tarde");
        }

        //Verifica si el usuario emprendedor no publico el producto
        if (!$elUsuarioLoPublico) {
            //Se verifica que el estado del producto no debe estar pausado, la cuenta del usuario no debe estar a
            if ($detalles_producto['id_estado_producto'] == 2 || !$detalles_producto['activado'] || $detalles_producto['baneado']) {
                throw new Exception("La publicacion del producto no se encuentra disponible por el momento");
            }
        }
        $respuesta['archivos'] = $imagenes_productos;
        $respuesta['detalles_producto'] = $detalles_producto;


        //Se obtiene estado del producto
        $estado_producto_finalizado = verificarSiElProductoEstaFinalizado($conexion, $id_producto);
        $respuesta['estado_producto_finalizado'] = $estado_producto_finalizado;


        //Verifica si el usuario es valido
        if ($usuario_valido) {

            //Verifica si el usuario publico el producto
            if ($elUsuarioLoPublico) {

                //Se obtiene todas las preguntas para la interfaz del emprendedor que publico el producto
                $preguntasGenerales = obtenerListaPreguntasRespuestaProducto($conexion, $id_producto);
            } else {
                //Se obtiene las preguntas para la interfaz de usuario que inicio sesion
                $preguntasUsuario = obtenerListaPreguntasRespuestaProductoUsuario($conexion, $id_producto, $id_usuario);
                $preguntasGenerales = obtenerListaPreguntasRespuestaProductoSinElUsuario($conexion, $id_producto, $id_usuario);
            }
        } else {
            //Se obtiene todas las preguntas para la interfaz de usuario que no inicio sesion
            $preguntasGenerales = obtenerListaPreguntasRespuestaProducto($conexion, $id_producto);
        }

        $respuesta['preguntasUsuario'] = $preguntasUsuario;
        $respuesta['preguntasGenerales'] = $preguntasGenerales;
        $respuesta['el_usuario_publico'] = $elUsuarioLoPublico;
        $respuesta['usuario_valido'] = $usuario_valido;



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
