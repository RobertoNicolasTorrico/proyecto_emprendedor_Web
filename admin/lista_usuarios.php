<?php

//Archivos de configuracion y funciones necesarias
include("../config/consultas_bd/conexion_bd.php");
include("../config/consultas_bd/consultas_usuario.php");
include("../config/consultas_bd/consultas_usuario_admin.php");
include("../config/funciones/funciones_generales.php");
include("../config/funciones/funciones_token.php");
include("../config/funciones/funciones_session.php");
include("../config/funciones/funciones_usuario.php");
include("../config/config_define.php");


//Inicializacion de variables obtenidas de la solicitud POST
$campo_buscar = isset($_POST['campo_buscar_usuario']) ? $_POST['campo_buscar_usuario'] : '';
$fecha = isset($_POST['fecha_inicio_usuario']) ? $_POST['fecha_inicio_usuario'] : '';
$pagina_actual = isset($_POST['numero_pagina_usuario']) ? (int)$_POST['numero_pagina_usuario'] : 1;
$cant_registro = isset($_POST['cant_registro_usuario']) ? (int)$_POST['cant_registro_usuario'] : 10;

$tipo_usuario = isset($_POST['num_tipo_usuario']) ? (int)$_POST['num_tipo_usuario'] : 0;
$estado_usuario = isset($_POST['estado_usuario']) ? $_POST['estado_usuario'] : "todos";
$calificacion = isset($_POST['select_calificacion']) ? $_POST['select_calificacion'] : "todos";


$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';

try {

    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario administrador
    if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
        throw new Exception("Debe iniciar sesion para poder ver la lista de usuarios");
    }

    //Se obtiene los datos de sesion
    $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
    $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario administrador es valido
    if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
        throw new Exception("No puede ver la lista de usuarios por que no es usuario administrador valido");
    }


    //Se obtener la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where = obtenerCondicionWhereBuscadorUsuario($campo_buscar, $fecha, $tipo_usuario, $estado_usuario, $calificacion);

    //Se obtener la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

    //Se obtener lista de usuarios que cumplen con las condiciones de busqueda
    $lista_usuarios = obtenerTodosDatosDeUsuarios($conexion, $condicion_where, $condicion_limit);

    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($campo_buscar) || !empty($fecha)  || ($tipo_usuario != 0) || ($estado_usuario != 'todos')  || ($calificacion != 'todos'));


    //Se obtiene la cantidad actual de usuarios segun las condiciones de busqueda
    $cantidad_usuarios = count($lista_usuarios);


    //Genera la tabla HTML para los usuarios
    $respuesta['tabla_usuarios'] = cargarTablaListaUsuario($lista_usuarios, $busqueda_activa);

    if ($cantidad_usuarios >= 1) {

        //Se obtiene la cantidad total de usuarios segun las condiciones de busqueda
        $cantTotalUsuarios  = cantTotalUsuariosWheUsuario($conexion, $condicion_where);

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantTotalUsuarios, $cant_registro, $pagina_actual, "nextPageUsuarios");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantTotalUsuarios / $cant_registro);

        //Se obtiene la cantidad de usuarios que se va a mostrar en la interfaz del usuario
        $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantTotalUsuarios, $pagina_actual, $cantidad_usuarios);

        //Se guarda en respuesta un mensaje indicando la cantidad de usuarios que se ve en la interfaz del usuario
        $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantTotalUsuarios} usuarios";

        //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
        $respuesta['pagina'] = "Pagina {$pagina_actual} de {$totalPaginas}";
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}

function cargarTablaListaUsuario($lista_usuarios, $busqueda_activa)
{

    global $url_base;
    $datos = '';

    $datos .= "<table class='table table-hover table-bordered'>";
    $datos .= "<thead>";
    $datos .= "<tr>";
    $datos .= "<th scope='col'>Fecha de registro</th>";
    $datos .= "<th scope='col'>Nombre de Usuario</th>";
    $datos .= "<th scope='col'>Nombres</th>";
    $datos .= "<th scope='col'>Apellidos</th>";
    $datos .= "<th scope='col'>Email</th>";
    $datos .= "<th scope='col'>Activado</th>";
    $datos .= "<th scope='col'>Baneado</th>";
    $datos .= "<th scope='col'>Nombre de Empredimiento</th>";
    $datos .= "<th scope='col'>Calificacion del emprendedor</th>";

    $datos .= "<th scope='col'>Accion</th>";
    $datos .= "</tr>";
    $datos .= "</thead>";

    $datos .= "<tbody>";
    if (count($lista_usuarios) > 0) {
        for ($i = 0; $i < count($lista_usuarios); $i++) {
            $datos .= "<tr>";
            $fecha = date("d/m/Y H:i:s", strtotime($lista_usuarios[$i]['fecha']));

            $datos .= "<td>{$fecha} </td>";
            $datos .= "<td>{$lista_usuarios[$i]["nombre_usuario"]} </td>";
            $datos .= "<td>{$lista_usuarios[$i]["nombres"]}</td>";
            $datos .= "<td>{$lista_usuarios[$i]["apellidos"]} </td>";
            $datos .= "<td>{$lista_usuarios[$i]["email"]}</td>";

            $datos .= "<td>{$lista_usuarios[$i]["esta_activado"]}</td>";
            $datos .= "<td>{$lista_usuarios[$i]["esta_baneado"]} </td>";
            if (!is_null($lista_usuarios[$i]["nombre_emprendimiento"])) {
                $datos .= "<td>{$lista_usuarios[$i]["nombre_emprendimiento"]}</td>";
                if (is_null($lista_usuarios[$i]['calificacion_emprendedor'])) {
                    $datos .= "<td>El emprendedor aun no tiene una calificacion</td>";
                } else {
                    $datos .= "<td>{$lista_usuarios[$i]["calificacion_emprendedor"]}</td>";
                }
            } else {
                $datos .= "<td class='text-black-50 bg-white'>No es un emprendedor</span></td>";
                $datos .= "<td class='text-black-50 bg-white'>No es un emprendedor</span></td>";
            }

            $datos .= "<td>";
            $token = hash_hmac('sha1', $lista_usuarios[$i]['id_usuario'], KEY_TOKEN);
            $datos .= "<a class='btn btn-outline-success' href='{$url_base}/admin/paginas/detalles_usuarios/pagina_detalles_usuario.php?id={$lista_usuarios[$i]['id_usuario']}&token={$token}'>Ver todos los detalles</a>";
            $datos .= "</td>";
            $datos .= "</tr>";
        }
    } else {
        $datos .= "<tr>";
        if ($busqueda_activa) {
            $datos .= "<td colspan='10'>Sin resultados</td>";
        } else {
            $datos .= "<td colspan='10'>No hay usuarios registrados</td>";
        }
        $datos .= "</tr>";
    }

    $datos .= '</tbody>';

    $datos .= '</table>';
    return $datos;
}


//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
