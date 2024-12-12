<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/consultas_bd/consultas_notificacion.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_session.php");
include("../../config/config_define.php");

//Limite de caracteres para la pregunta 
$pregunta_datos = [
    'cant_min' => 1,
    'cant_max' => 255,
];

// Campos esperados en la solicitud POST
$campo_esperados = array("id_producto", "txt_pregunta");

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
            throw new Exception("Debe haber iniciar sesion para poder hacer preguntas");
        }

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION['id_usuario'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();


        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede hacer una pregunta al producto debido a que su cuenta no esta activa o esta baneado");
        }


        //Se obtiene los datos de la solicitud POST
        $id_producto = $_POST['id_producto'];
        $pregunta = $_POST["txt_pregunta"];

        //Validar que los campos ocultos solo contengan numeros
        if (!is_numeric($id_producto)) {
            throw new Exception("El campo id Producto no recibio un parametro valido");
        }

        //Verifica que la pregunta no tenga espacios al inicio o al final de la cadena
        if (tieneEspaciosInicioFinCadena($pregunta)) {
            throw new Exception("La pregunta no puede tener espacios en blanco al inicio o al final");
        }

        //Verifica que la longitud de caracteres sea valida 
        if (verificarLongitudTexto($pregunta, $pregunta_datos['cant_min'], $pregunta_datos['cant_max'])) {
            throw new Exception("El campo pregunta debe tener entre 1 y 255 caracteres");
        }


        //Verifica si el usuario publico el producto
        if (elUsuarioLoPublico($conexion, $id_producto, $id_usuario)) {
            throw new Exception("Un usuario emprendedor no puede hacer preguntas aun producto que el halla publicado");
        }

        //Verifica que el producto este disponible para hacer preguntas
        if (!verificarSiElProductoEstaDisponible($conexion, $id_producto)) {
            throw new Exception("La publicacion del producto no se encuentra disponible asi que no se pueden hacer mas preguntas");
        }

        //Guarda la pregunta ademas se obtiene el id de pregunta
        $id_pegunta = altaPreguntaProducto($conexion, $pregunta, $id_usuario, $id_producto);


        //Se obtiene informacion sobre el usuario que publico el producto
        $producto = obtenerDatosProducto($conexion, $id_producto);
        $id_usuario_notificar = $producto['id_usuario'];

        //Se guarda una nueva notificacion para el usuario que publico su producto
        altaNotificacionPreguntaProducto($conexion, $id_usuario_notificar, $id_usuario, $id_pegunta, $id_producto);

        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'La pregunta sobre el producto se ha enviado con exito';
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
