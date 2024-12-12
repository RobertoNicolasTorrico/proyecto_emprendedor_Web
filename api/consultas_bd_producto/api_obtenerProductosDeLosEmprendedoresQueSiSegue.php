<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/consultas_bd/consultas_seguimiento.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_verificaciones.php");

// Campos esperados en la solicitud GET
$pagina_actual = isset($_GET['pagina_producto']) ? $_GET['pagina_producto'] : 1;


// Limite de productos 
$limite_publicacion_producto = 9;


// Campos esperados en la solicitud POST
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
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede mostrar la informacion de los productos debido a que su cuenta no esta activa o esta baneado");
        }

        //Valida que el campo pagina actual tengo un valor numerico
        if (!is_numeric($pagina_actual)) {
            throw new Exception("La pagina de publicacion debe ser un numero");
        }

        //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y el limite de publicaciones de productos
        $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_publicacion_producto);

        //Se obtiene lista de los ultimos productos disponibles 
        $lista_productos = obtenerProductosDeLosEmprendedoresQueSiSegue($conexion, $id_usuario, $condicion_limit);

        $lista_productos_imagenes = array();

        for ($i = 0; $i < count($lista_productos); $i++) {
            $id_producto = $lista_productos[$i]['id_publicacion_producto'];
            $lista_archivos = array();

            // Se obtiene la lista de imágenes del producto 
            $lista_imagenes = obtenerListaImgProducto($conexion, $id_producto);

            // Recorre la lista de imágenes y las agrega a $lista_archivos
            for ($j = 0; $j < count($lista_imagenes); $j++) {
                $lista_archivos[] = $lista_imagenes[$j];
            }

            // Agrega los detalles del producto y sus archivos $lista_productos_imagenes
            $lista_productos_imagenes[] = array(
                'detalles_producto' => $lista_productos[$i],
                'archivos' => $lista_archivos
            );
        }

        $respuesta['lista_productos'] = $lista_productos_imagenes;


        //Verifica si es necesario cargar mas elementos
        $cantidad_productos = count($lista_productos);
        $cargar_mas = cargarMasElementos($cantidad_productos, $limite_publicacion_producto);
        $respuesta['cargar_mas_productos'] = $cargar_mas;

        //Se obtiene la cantidad total de emprendedores que sigue el usuario
        $cant_seguimiento = cantTotalSeguimientoUsuario($conexion, $id_usuario);
        $respuesta['cant_seguimiento'] = $cant_seguimiento;


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
