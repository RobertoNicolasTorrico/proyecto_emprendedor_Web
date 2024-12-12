<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../config/funciones/funciones_token.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_preguntas_respuestas.php");
include("../../config/funciones/funciones_verificaciones.php");

//Inicializacion de variables obtenidas de la solitud GET
$campo_buscar = isset($_GET['campo_buscar']) ? $_GET['campo_buscar'] : '';
$cant_registro = isset($_GET['cant_registro']) ? (int)$_GET['cant_registro'] : 5;
$pagina_actual = isset($_GET['numero_pagina']) ? (int)$_GET['numero_pagina'] : 1;
$cant_dias = isset($_GET['cant_dias']) ? (int)$_GET['cant_dias'] : null;
$estado = isset($_GET['estado']) ? (int)$_GET['estado'] : 0;

//Campos esperados en la solicitud GET
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


        //Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede mostrar la informacion debido a que su cuenta no esta activa o esta baneado");
        }


        //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
        $condicion_where = obtenerCondicionWhereBuscadorProductoPregunta($campo_buscar, $cant_dias, $estado);

        //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
        $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

        //Se obtiene lista de preguntas y respuestas que cumplen con las condiciones de busqueda
        $lista_pregunta = obtenerListaMisPreguntasWhereLimit($conexion, $condicion_where, $condicion_limit, $id_usuario);


        //Se obtiene la cantidad actual de preguntas y respuesta segun las condiciones de busqueda
        $cantidad_preguntas = count($lista_pregunta);
        $respuesta['cantidad_actual'] = $cantidad_preguntas;


        $productoActual = null;
        $lista_producto = [];

        // Recorre la lista de preguntas $lista_pregunta
        foreach ($lista_pregunta as $producto) {
            // Si el producto actual es diferente al producto en la iteración actual
            if ($productoActual != $producto['id_producto']) {
                // Actualiza $productoActual con el id del producto actual
                $productoActual = $producto['id_producto'];
                // Agrega los detalles del producto a la lista $lista_producto
                $lista_producto[] = [
                    'id_producto' => $producto['id_producto'],
                    'id_usuario_emprendedor' => $producto['id_usuario_emprendedor'],
                    'nombre_producto' => $producto['nombre_producto'],
                    'precio' => $producto['precio'],
                    'stock' => $producto['stock'],
                    'estado' => $producto['estado'],
                    'nombre_carpeta' => $producto['nombre_carpeta'],
                    'nombre_archivo' => $producto['nombre_archivo']
                ];
            }
        }


        $lista_productos_preguntas = array();

        // Recorre la lista de productos $lista_producto
        foreach ($lista_producto as $producto) {

            $misPreguntas = array();
            // Recorre la lista de preguntas $lista_pregunta
            foreach ($lista_pregunta as $pregunta) {
                // Si el id del producto en la pregunta coincide con el id del producto actual
                if ($producto['id_producto'] == $pregunta['id_producto']) {
                    // Agrega los detalles de la pregunta a la lista $misPreguntas
                    $misPreguntas[] = [
                        'id_pregunta_respuesta' => $pregunta['id_pregunta_respuesta'],
                        'pregunta' => $pregunta['pregunta'],
                        'fecha_pregunta' => $pregunta['fecha_pregunta'],
                        'respuesta' => $pregunta['respuesta'],
                        'fecha_respuesta' => $pregunta['fecha_respuesta'],
                        'id_usuario' => $pregunta['id_usuario']
                    ];
                }
            }
            // Agrega los detalles del producto y sus preguntas relacionadas a la lista productos preguntas
            $lista_productos_preguntas[] = array(
                'detalles_producto' => $producto,
                'preguntas_hechas' => $misPreguntas
            );
        }
        $respuesta['lista_pregunta'] = $lista_productos_preguntas;


        //Determina si la busqueda esta activa
        $busqueda_activa = (!empty($campo_buscar) ||  $estado != 0 ||  $cant_dias != 0);
        $respuesta['busqueda_activa'] = $busqueda_activa;

        if ($cantidad_preguntas >= 1) {

            //Se obtiene la cantidad total de preguntas y respuesta segun las condiciones de busqueda
            $cantTotalPreguntas  = cantTotalMisPreguntasProductoWhe($conexion, $condicion_where, $id_usuario);
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
