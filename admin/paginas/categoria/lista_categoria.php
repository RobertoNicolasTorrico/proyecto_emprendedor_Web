<?php
//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_categoria.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/funciones/funciones_generales.php");
include("../../../config/funciones/funciones_token.php");
include("../../../config/funciones/funciones_categoria.php");
include("../../../config/config_define.php");

//Inicializacion de variables obtenidas de la solitud POST
$cant_registro = isset($_POST['cant_registro']) ? (int)$_POST['cant_registro'] : 10;
$pagina_actual = isset($_POST['numero_pagina_categoria']) ? (int)$_POST['numero_pagina_categoria'] : 1;
$campo_buscar = isset($_POST['campo_buscar']) ? $_POST['campo_buscar'] : '';
$ordenar_por = isset($_POST['ordenar_por']) ? $_POST['ordenar_por'] : 'alfabetico_asc';


$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';

try {


    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario administrador
    if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
        throw new Exception("Debe iniciar sesion para poder ver la lista de categorias");
    }

    //Se obtiene los datos de sesion
    $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
    $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario administrador es valido
    if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
        throw new Exception("No puede ver la lista de categoria por que no es usuario administrador valido");
    }



    //Se obtener la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where = obtenerCondicionWhereBuscadorCategoria($campo_buscar);

    //Se obtener la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);


    //Se obtener la condicion ASC y DESC para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_ASCDESC = obtenerCondicionWhereBuscadorCategoriaASCDESC($ordenar_por);

    //Se obtener lista de categorias que cumplen con las condiciones de busqueda
    $lista_categoria = obtenerListaTodosCategoria($conexion, $condicion_where, $condicion_limit, $condicion_ASCDESC);

    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($campo_buscar));


    //Se obtiene la cantidad actual de categorias segun las condiciones de busqueda
    $cantidad_categoria = count($lista_categoria);
    $respuesta['cantidad_actual'] = $cantidad_categoria;


    //Genera las tabla HTML para las categorias 
    $respuesta['tabla'] = cargarTablaCategoria($lista_categoria, $busqueda_activa);

    if ($cantidad_categoria >= 1) {

        //Se obtiene la cantidad total de categorias segun las condiciones de busqueda
        $cantTotalCategorias  = cantTotalCategoriasWheCategoria($conexion, $condicion_where);

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantTotalCategorias, $cant_registro, $pagina_actual, "nextPageCategorias");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantTotalCategorias / $cant_registro);

        //Se obtiene la cantidad de categorias que se va a mostrar en la interfaz del usuario
        $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantTotalCategorias, $pagina_actual, $cantidad_categoria);

        //Se guarda en respuesta un mensaje indicando la cantidad de categorias que se ve en la interfaz del usuario
        $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantTotalCategorias} categorias";

        //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
        $respuesta['pagina'] = "Pagina " . $pagina_actual  . ' de ' .  $totalPaginas;
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}

function cargarTablaCategoria($lista_categoria, $busqueda_activa)
{

    $datos = '';

    $datos .= "<table class='table table-hover table-bordered'>";
    $datos .= "<thead>";
    $datos .= "<tr>";
    $datos .= "<th scope='col'>Categoria</th>";
    $datos .= "<th scope='col'>Cantidad de productos</th>";

    $datos .= "<th scope='col'>Accion</th>";
    $datos .= "</tr>";
    $datos .= "</thead>";

    $datos .= "<tbody>";
    if (count($lista_categoria) > 0) {
        for ($i = 0; $i < count($lista_categoria); $i++) {
            $datos .= "<tr>";

            $datos .= "<td>{$lista_categoria[$i]["nombre_categoria"]} </td>";
            $datos .= "<td>{$lista_categoria[$i]["cantidad_productos"]}</td>";
            $datos .= "<td>";

            $datos .= "<button type='button' class='btn btn-outline-warning m-1'  data-bs-id_categoria_producto='{$lista_categoria[$i]['id_categoria_producto']}'   data-bs-tipo_categoria='{$lista_categoria[$i]['nombre_categoria']}'   data-bs-toggle='modal' data-bs-target='#ModalModificarCategoria'>Modificar</button>";
            if ($lista_categoria[$i]["cantidad_productos"] == 0) {
                $datos .= "<button type='button' class='btn btn-outline-danger m-1' data-bs-toggle='modal' data-bs-target='#ModalEliminarCategoria' data-bs-id_categoria_producto='{$lista_categoria[$i]['id_categoria_producto']}' data-bs-tipo_categoria='{$lista_categoria[$i]['nombre_categoria']}'>Eliminar</button>";
            }
            $datos .= "</td>";
            $datos .= "</tr>";
        }
    } else {
        $datos .= "<tr>";
        if ($busqueda_activa) {
            $datos .= "<td colspan='3'>Sin resultados</td>";
        } else {
            $datos .= "<td colspan='3'>No hay categorias registradas</td>";
        }
        $datos .= "</tr>";
    }

    $datos .= '</tbody>';

    $datos .= '</table>';
    return $datos;
}


//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
