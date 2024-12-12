<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_productos.php");

//Inicializacion de variables obtenidas de la solicitud GET
$campo_buscar = isset($_GET['campo_buscar']) ? $_GET['campo_buscar'] : null;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;
$pagina_actual = isset($_GET['pagina_actual']) ? (int)$_GET['pagina_actual'] : 1;
$cant_registro = isset($_GET['cant_registro']) ? (int)$_GET['cant_registro'] : 10;
$categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$estado = isset($_GET['estado']) ? (int)$_GET['estado'] : null;

// Campos esperados en la solicitud GET
$campo_esperados = array("id_usuario", "tipo_usuario");

try {
    //Verifica si la solicitud es GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {


        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_GET);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }


        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Obtener los datos de la solicitud GET
        $id_usuario = $_GET['id_usuario'];
        $tipo_usuario = $_GET['tipo_usuario'];

        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede mostrar la informacion de los productos debido a que su cuenta no esta activa o esta baneado");
        }
        //Verifica que el usuario sea un usuario emprendedor
        if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
            throw new Exception("No se puede ver los detalles de los productos debido que no es un usuario emprendedor");
        }


        //Se obtiene datos del usuario emprendedor por el id del usuario
        $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
        if (empty($usuario_emprendedor)) {
            throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
        }
        $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];

        //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
        $condicion_where = obtenerCondicionWhereBuscadorProductoEmprendedor($campo_buscar, $fecha, $categoria, $estado);

        //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
        $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

        //Se obtiene lista de publicaciones que cumplen con las condiciones de busqueda
        $lista_productos = obtenerListaProductoWhereLimit($conexion, $condicion_where, $condicion_limit, $id_usuario_emprendedor);

        $lista_productos_imagenes = array();
        for ($i = 0; $i < count($lista_productos); $i++) {
            $id_producto = $lista_productos[$i]['id_publicacion_producto'];
            $lista_archivos = array();

            // Se obtiene la lista de imágenes del producto 
            $lista_imagenes = obtenerListaImgProducto($conexion, $id_producto);

            // Se obtiene la lista de preguntas del producto 
            $lista_preguntas = obtenerListaPreguntasRespuestaProducto($conexion, $id_producto);

            // Recorre la lista de imágenes y las agrega a $lista_archivos
            for ($j = 0; $j < count($lista_imagenes); $j++) {
                $lista_archivos[] = $lista_imagenes[$j];
            }

            // Agrega los detalles del producto, sus archivos y sus preguntas a $lista_productos_imagenes
            $lista_productos_imagenes[] = array(
                'detalles_producto' => $lista_productos[$i],
                'archivos' => $lista_archivos,
                'lista_preguntas' => $lista_preguntas

            );
        }
        $respuesta['lista_productos'] = $lista_productos_imagenes;


        //Se obtiene la cantidad actual de productos segun las condiciones de busqueda
        $cantidad_productos = count($lista_productos);
        //Se guarda la cantidad actual de productos
        $respuesta['cantidad_actual'] = $cantidad_productos;

        //Determina si la busqueda esta activa
        $busqueda_activa = (!empty($campo_buscar) || ($categoria != 0) || ($estado != 0) || !empty($fecha));
        $respuesta['busqueda_activa'] = $busqueda_activa;



        if ($cantidad_productos >= 1) {

            //Se obtiene la cantidad total de publicaciones segun las condiciones de busqueda
            $cantidad_total_productos  = cantTotalProductoWheUsuarioEmprendedor($conexion, $condicion_where, $id_usuario_emprendedor);
            $respuesta['cantidad_total_productos'] = $cantidad_total_productos;

            //Calcula el total de paginas
            $totalPaginas = ceil($cantidad_total_productos / $cant_registro);
            $respuesta['totalPaginas'] = $totalPaginas;

            //Se guarda en respuesta un mensaje indicando la cantidad de productos que se ve en la interfaz del usuario
            $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantidad_total_productos, $pagina_actual, $cantidad_productos);
            $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantidad_total_productos} productos";

            //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
            $respuesta['pagina'] = "Pagina {$pagina_actual} de {$totalPaginas}";
        }


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
