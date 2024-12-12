<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../config/funciones/funciones_token.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_preguntas_respuestas.php");
include("../../config/funciones/funciones_verificaciones.php");


//Inicializacion de variables obtenidas de la solicitud GET
$campo_buscar_producto = isset($_GET['campo_buscar_producto']) ? $_GET['campo_buscar_producto'] : '';
$campo_buscar_usuario = isset($_GET['campo_buscar_usuario']) ? $_GET['campo_buscar_usuario'] : '';
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$pagina_actual = isset($_GET['pagina_actual']) ? (int)$_GET['pagina_actual'] : 1;
$cant_registro = isset($_GET['cant_registro']) ? (int)$_GET['cant_registro'] : 10;
$estado = isset($_GET['estado']) ? (int)$_GET['estado'] : null;
$filtro_preguntas = isset($_GET['filtro_preguntas']) ? $_GET['filtro_preguntas'] : 'Todas';



//Campos esperados en la solicitud GET
$campos_esperados = array('id_usuario', 'tipo_usuario');
$respuesta = [];


try {
    //Verifica si la solicitud es GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campos_esperados, $_GET);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Se obtiene los datos de la solicitud GET
        $id_usuario = $_GET['id_usuario'];
        $tipo_usuario = $_GET['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();


        //Verifica si el usuario administrador es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede mostrar la informacion debido a que su cuenta no esta activa o esta baneado");
        }


        //Verifica si es un usuario emprendedor
        if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
            throw new Exception("No se puede ver la informacion de las publicacion ya que no es un usuario emprendedor");
        }


        //Se obtiene los datos del usuario emprendedor por el id del usuario
        $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
        if (empty($usuario_emprendedor)) {
            throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
        }
        $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];



        //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
        $condicion_where = obtenerCondicionWhereBuscadorPreguntasRespuestasEmprendedor($campo_buscar_producto, $campo_buscar_usuario, $fecha, $estado, $filtro_preguntas);

        //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
        $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

        //Se obtiene lista de preguntas y respuestas que cumplen con las condiciones de busqueda
        $lista_preguntas = obtenerListaPreguntasRespuestaWhereLimit($conexion, $condicion_where, $condicion_limit, $id_usuario_emprendedor);


        //Determina si la busqueda esta activa
        $busqueda_activa = (!empty($campo_buscar_producto) || !empty($campo_buscar_usuario) || ($estado != 0) || ($filtro_preguntas != "Todas")  || !empty($fecha));
        $respuesta['busqueda_activa'] = $busqueda_activa;


        $fecha_anterior = null;

        foreach ($lista_preguntas as &$pregunta) {

            // Convierte la fecha de la pregunta actual a formato "d/m/Y"
            $fecha_actual = date("d/m/Y", strtotime($pregunta['fecha_pregunta']));

            // Compara la fecha actual con la fecha anterior
            if ($fecha_anterior != $fecha_actual) {
                // Si son diferentes actualiza fecha anterior y marca la pregunta con fecha diferente como true
                $fecha_anterior = $fecha_actual;
                $pregunta['fecha_diferente'] = true;
            } else {
                // Si son iguales marca la pregunta como false
                $pregunta['fecha_diferente'] = false;
            }
        }
        $respuesta['lista_preguntas'] = $lista_preguntas;

        $cantidad_preguntas = count($lista_preguntas);
        if ($cantidad_preguntas >= 1) {

            //Se obtiene la cantidad total de preguntas y respuesta segun las condiciones de busqueda
            $cantTotalPreguntas  = cantTotalListaPreguntasRespuestaWhere($conexion, $condicion_where, $id_usuario_emprendedor);
            $respuesta['cant_total_preguntas'] = $cantTotalPreguntas;


            //Calcula el total de paginas
            $totalPaginas = ceil($cantTotalPreguntas / $cant_registro);
            $respuesta['totalPaginas'] = $totalPaginas;


            //Se obtiene la cantidad de preguntas que se va a mostrar en la interfaz del usuario
            $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantTotalPreguntas, $pagina_actual, $cantidad_preguntas);

            //Se guarda en respuesta un mensaje indicando la cantidad de preguntas que se ve en la interfaz del usuario
            $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantTotalPreguntas} preguntas";

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
