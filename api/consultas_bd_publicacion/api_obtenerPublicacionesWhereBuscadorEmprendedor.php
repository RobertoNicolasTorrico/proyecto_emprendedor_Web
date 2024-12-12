<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_publicaciones.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_session.php");
include("../../config/funciones/funciones_publicaciones.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/config_define.php");


//Inicializacion de variables obtenidas de la solicitud GET
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$pagina_actual = isset($_GET['pagina_actual']) ? (int)$_GET['pagina_actual'] : 1;
$cant_registro = isset($_GET['cant_registro']) ? (int)$_GET['cant_registro'] : 5;


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


        //Se obtiene los datos de la solicitud GET
        $id_usuario = $_GET['id_usuario'];
        $tipo_usuario = $_GET['tipo_usuario'];


        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede mostrar la informacion de los productos debido a que su cuenta no esta activa o esta baneado");
        }

        //Verifica que el usuario sea un usuario emprendedor
        if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
            throw new Exception("No se puede ver la informacion de los productos ya que no es un usuario emprendedor");
        }


        //Se obtiene datos del usuario emprendedor por el id del usuario
        $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
        if (empty($usuario_emprendedor)) {
            throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
        }
        $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];


        //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
        $condicion_where = obtenerCondicionWhereBuscadorPublicacion($fecha);

        //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
        $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

        //Se obtiene lista de publicaciones que cumplen con las condiciones de busqueda
        $lista_publicacion = obtenerListaPublicacionWhereLimit($conexion, $condicion_where, $condicion_limit, $id_usuario_emprendedor);

        //Determina si la busqueda esta activa
        $busqueda_activa = (!empty($fecha));
        $respuesta['busqueda_activa'] = $busqueda_activa;


        $lista_publicaciones_archivo = array();
        for ($i = 0; $i < count($lista_publicacion); $i++) {
            $id_publicacion = $lista_publicacion[$i]['id_publicacion_informacion'];

            $lista_archivos = array();
            // Se obtiene la lista de archivos de la publicacion 
            $lista_archivos_publicacion = obtenerListaArchivosPublicaciones($conexion, $id_publicacion);

            // Recorre la lista de archivos y las agrega a $lista_archivos_publicacion
            for ($j = 0; $j < count($lista_archivos_publicacion); $j++) {
                $lista_archivos[] = $lista_archivos_publicacion[$j];
            }

            // Agrega los detalles de la publicacion y sus archivos $lista_publicaciones_archivo
            $lista_publicaciones_archivo[] = array(
                'detalles_publicaciones' => $lista_publicacion[$i],
                'archivos' => $lista_archivos
            );
        }
        $respuesta['lista_publicaciones'] = $lista_publicaciones_archivo;


        //Se obtiene la cantidad de publicaciones disponibles
        $cantidad_publicacion = count($lista_publicacion);
        $respuesta['cantidad_actual'] = $cantidad_publicacion;


        if ($cantidad_publicacion >= 1) {

            //Se obtiene la cantidad total de publicaciones segun las condiciones de busqueda
            $cantTotalPublicacion  = cantTotalPublicacionesWheUsuarioEmprendedor($conexion, $condicion_where, $id_usuario_emprendedor);
            $respuesta['cantidad_total_publicacion'] = $cantTotalPublicacion;

            //Calcula el total de paginas
            $totalPaginas = ceil($cantTotalPublicacion / $cant_registro);
            $respuesta['totalPaginas'] = $totalPaginas;

            //Se obtiene la cantidad de publicaciones segun que se va a mostrar en la interfaz del usuario
            $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantTotalPublicacion, $pagina_actual, $cantidad_publicacion);

            //Se guarda en respuesta un mensaje indicando la cantidad de publicaciones que se ve en la interfaz del usuario
            $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantTotalPublicaciones} publicaciones";

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