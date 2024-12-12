<?php
//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/consultas_bd/consultas_seguimiento.php");
include("../../../config/consultas_bd/consultas_usuario.php");
include("../../../config/funciones/funciones_generales.php");
include("../../../config/funciones/funciones_seguidos_seguidores.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/funciones/funciones_token.php");
include("../../../config/config_define.php");

$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';

//Inicializacion de variables obtenidas de la solicitud POST
$campo_buscar_seguidor = isset($_POST['campo_buscar_seguidor']) ? $_POST['campo_buscar_seguidor'] : '';
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$cant_registro = isset($_POST['cant_registro']) ? (int)$_POST['cant_registro'] : 5;


//Inicializacion de variables obtenidas de la URL
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';
$id_usuario_token = isset($_GET['token']) ? $_GET['token'] : '';
try {


    //Se verifica que los datos recibidos de la URL sean validos
    verificarUrlTokenId($id_usuario, $id_usuario_token);

    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario administrador
    if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
        throw new Exception("Debe iniciar sesion para poder ver la lista de seguidores del usuario");
    }

    //Se obtiene los datos de sesion
    $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
    $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario administrador es valido
    if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
        throw new Exception("No se puede ver la lista de seguidores de un usuario por que no es usuario administrador valido");
    }

     //Verifica si la cuenta del usuario sigue disponible
     if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
        throw new Exception("Esta cuenta fue eliminada previamente. Por favor regrese a la página anterior");
    }

    //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where =  obtenerCondicionWhereBuscarSeguidorAdmin($campo_buscar_seguidor, $fecha);

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

    //Se obtiene lista de preguntas y respuestas que cumplen con las condiciones de busqueda
    $lista_seguidores = obtenerListasSeguidoresDeEmprendedores($conexion, $id_usuario, $condicion_limit, $condicion_where);

    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($campo_buscar_seguidor) || !empty($fecha));


    //Genera la tabla HTML para los seguidores del usuario
    $respuesta["tabla"] = cargarTablaSeguidoresParaAdmin($lista_seguidores, $busqueda_activa);

    //Se obtiene la cantidad actual seguidores
    $cantidad_seguidores = count($lista_seguidores);
    $respuesta['cantidad_actual']=$cantidad_seguidores;


    if ($cantidad_seguidores >= 1) {

        //Se obtiene la cantidad total de seguidores segun las condiciones de busqueda
        $cantTotalSeguidos =  cantTotalSeguidoresWheUsuarioEmprendedor($conexion, $id_usuario, $condicion_where);

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantTotalSeguidos, $cant_registro, $pagina_actual, "nextPageSeguidores");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantTotalSeguidos / $cant_registro);

        //Se obtiene la cantidad de seguidores que se va a mostrar en la interfaz del usuario
        $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantTotalSeguidos, $pagina_actual, $cantidad_seguidores);

        //Se guarda en respuesta un mensaje indicando la cantidad de seguidores que se ve en la interfaz del usuario
        $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantTotalSeguidos} seguidores";

        //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
        $respuesta['pagina'] = "Pagina {$pagina_actual}  de  {$totalPaginas}";
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}




function cargarTablaSeguidoresParaAdmin($lista_seguidores, $busqueda_activa)
{

    global $url_base;

    $datos = "";
    $datos .= "<table class='table table-striped table-bordered'>";
    $datos .= "<thead>";
    $datos .= "<tr>";
    $datos .= "<th scope='col'>Nombre de usuario</th>";
    $datos .= "<th scope='col'>Nombre completo</th>";
    $datos .= "<th scope='col'>Fecha que empezo a seguirlo</th>";
    $datos .= "<th scope='col'>Acciones</th>";
    $datos .= "</tr>";
    $datos .= "</thead>";
    $datos .= "<tbody>";



    if (count($lista_seguidores) > 0) {
        for ($i = 0; $i < count($lista_seguidores); $i++) {
            $datos .= "<tr>";
     

            $datos .= "<td class='text-break'>{$lista_seguidores[$i]['nombre_usuario']}</td>";
            $datos .= "<td class='text-break'>{$lista_seguidores[$i]['nombre_completo']}</td>";

            $fecha = date("d/m/Y H:i:s", strtotime($lista_seguidores[$i]['fecha_seguimiento']));
            $datos .= "<td class='text-break'>{$fecha}</td>";


            $datos .= "<td>";
            $token = hash_hmac('sha1', $lista_seguidores[$i]['id_usuario_seguidor'], KEY_TOKEN);
            $datos .= '<a class="btn btn-outline-success m-1"  target="_blank"  href="' . $url_base . '/admin/paginas/detalles_usuarios/pagina_detalles_usuario.php?id=' . $lista_seguidores[$i]['id_usuario_seguidor'] . '&token=' . $token . '"><i class="fa-solid fa-user"></i> Detalles del usuario</a>';
            $datos .= "<button type='button' class='btn btn-outline-danger'  data-bs-toggle='modal' data-bs-target='#eliminarSeguidor' data-bs-nombre_usuario='{$lista_seguidores[$i]["nombre_usuario"]}' data-bs-id_seguidor='{$lista_seguidores[$i]["id_usuario"]}'><i class='fa-solid fa-trash'></i> Eliminar seguidor</button>";

            $datos .= "</td>";
            $datos .= "</tr>";
        }
    } else {

        $datos .= "<tr>";
        if ($busqueda_activa) {
            $datos .= "<td colspan='4'>Sin resultados</td>";
        } else {
            $datos .= "<td colspan='4'>No hay usuarios que lo sigan</td>";
        }
        $datos .= "</tr>";
    }

    $datos .= "</tbody>";

    $datos .= "</table>";

    return $datos;
}


echo json_encode($respuesta);
