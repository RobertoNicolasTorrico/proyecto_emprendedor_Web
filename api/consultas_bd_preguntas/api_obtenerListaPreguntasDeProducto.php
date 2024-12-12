<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_productos.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../config/consultas_bd/consultas_usuario.php");


// Campos esperados en la solicitud GET
$campo_esperados = array("id_producto");
$respuesta = [];
$preguntasUsuario = [];
$usuario_valido = false;
$elUsuarioLoPublico = false;
$producto_invalido = false;

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

        //Verifica que los valores id de usuario y el tipo de usuario no sea NULL
        if ($id_usuario  != null && $tipo_usuario != null) {

            //Se verifica que el usuario ingresado sea valido
            $usuario_valido = verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario);
            if ($usuario_valido) {
                //Se obtiene el valor si el usuario publico el producto
                $elUsuarioLoPublico = elUsuarioLoPublico($conexion, $id_producto, $id_usuario);
            }
        }

        //Se obtiene los datos del producto 
        $detalles_producto = obtenerDatosProducto($conexion, $id_producto);

        //Se verifica si el usuario no publico el producto
        if (!$elUsuarioLoPublico) {
            //Se obtiene el valor si el producto esta pausado o no
            $producto_invalido = $detalles_producto['id_estado_producto'] == 2 || !$detalles_producto['activado'] || $detalles_producto['baneado'];
        }
        $respuesta['producto_invalido'] = $producto_invalido;


        //Se obtiene el valor si el estado del producto esta finalizado ono
        $estado_producto_finalizado = verificarSiElProductoEstaFinalizado($conexion, $id_producto);
        $respuesta['estado_producto_finalizado'] = $estado_producto_finalizado;


        //Se verifica que el usuario ingresado sea valido
        if ($usuario_valido) {

            //Se verifica si el usuario publico el producto
            if ($elUsuarioLoPublico) {
                //Se obtiene todas las preguntas y respuesta del producto
                $preguntasGenerales = obtenerListaPreguntasRespuestaProducto($conexion, $id_producto);
            } else {
                //Se obtiene todas las preguntas y respuesta hechas por el usuario
                $preguntasUsuario = obtenerListaPreguntasRespuestaProductoUsuario($conexion, $id_producto, $id_usuario);

                //Se obtiene todas las preguntas y respuesta que no fueron hechas por el usuario
                $preguntasGenerales = obtenerListaPreguntasRespuestaProductoSinElUsuario($conexion, $id_producto, $id_usuario);
            }
        } else {
            //Se obtiene todas las preguntas y respuesta del producto
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
?>