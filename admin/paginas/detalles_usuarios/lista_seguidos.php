<?php
//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_seguimiento.php");
include("../../../config/consultas_bd/consultas_usuario.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/funciones/funciones_generales.php");
include("../../../config/funciones/funciones_seguidos_seguidores.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/funciones/funciones_token.php");
include("../../../config/config_define.php");


$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';

//Inicializacion de variables obtenidas de la solicitud POST
$campo_buscar_seguidos = isset($_POST['campo_buscar_seguidos']) ? $_POST['campo_buscar_seguidos'] : '';
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
    $condicion_where =  obtenerCondicionWhereBuscadorEmprendedorParaAdmin($campo_buscar_seguidos, $fecha);

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

    //Se obtiene lista de preguntas y respuestas que cumplen con las condiciones de busqueda
    $lista_seguidos = obtenerListaEmprendedoresSeguidos($conexion, $id_usuario, $condicion_limit, $condicion_where);

    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($campo_buscar_seguidos) || !empty($fecha));

    //Genera la tabla HTML para los usuario que siguen al emprendedor
    $respuesta["tabla"] = cargarTablaSeguidosParaAdmin($lista_seguidos, $busqueda_activa);


    //Se obtiene la cantidad actual seguidores
    $cantidad_seguidos = count($lista_seguidos);
    $respuesta['cantidad_actual'] = $cantidad_seguidos;
    if ($cantidad_seguidos >= 1) {

        //Se obtiene la cantidad total de seguidos segun las condiciones de busqueda
        $cantTotalSeguidos = cantTotalSeguidosWheUsuarioEmprendedor($conexion, $id_usuario, $condicion_where);

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantTotalSeguidos, $cant_registro, $pagina_actual, "nextPageSeguidoresEmprendedores");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantTotalSeguidos / $cant_registro);

        //Se obtiene la cantidad de seguidos que se va a mostrar en la interfaz del usuario
        $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantTotalSeguidos, $pagina_actual, $cantidad_seguidos);

        //Se guarda en respuesta un mensaje indicando la cantidad de seguidos que se ve en la interfaz del usuario
        $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantTotalSeguidos} emprendedores";

        //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
        $respuesta['pagina'] = "Pagina {$pagina_actual} de {$totalPaginas}";
    }
} catch (Exception $e) {
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}

function cargarTablaSeguidosParaAdmin($lista_seguidos, $busqueda_activa)
{
    $datos = "";

    global $url_base;
    $datos = "";
    $datos .= "<table class='table table-striped table-bordered'>";
    $datos .= "<thead>";
    $datos .= "<tr>";
    $datos .= "<th scope='col'>Foto perfil</th>";
    $datos .= "<th scope='col'>Nombre del empredimiento</th>";
    $datos .= "<th scope='col'>Nombre de usuario</th>";
    $datos .= "<th scope='col'>Fecha que empezo a seguirlo</th>";

    $datos .= "<th scope='col'>Acciones</th>";
    $datos .= "</tr>";
    $datos .= "</thead>";
    $datos .= "<tbody>";



    if (count($lista_seguidos) > 0) {
        for ($i = 0; $i < count($lista_seguidos); $i++) {
            $datos .= "<tr>";
            $fecha = date("d/m/Y H:i:s", strtotime($lista_seguidos[$i]['fecha_seguimiento']));
            $datos .= "<td class='text-break text-center'>";

            if (is_null($lista_seguidos[$i]["foto_perfil_nombre"])) {
                $ruta_archivo = "{$url_base}/img/foto_perfil/foto_de_perfil_predeterminado.jpg";
            } else {
                $ruta_archivo = "{$url_base}/uploads/{$lista_seguidos[$i]["id_usuario_emprendedor"]}/foto_perfil/{$lista_seguidos[$i]["foto_perfil_nombre"]}";
            }

            $datos .= "<a data-fancybox='galeria-{$lista_seguidos[$i]["id_usuario"]}' href='{$ruta_archivo}'>";
            $datos .= "<img class='tabla_imagen_perfil' src='{$ruta_archivo}' alt='Foto de perfil'>";
            $datos .= "</a>";

            $datos .= " </td>";
            $datos .= "<td class='text-break'>{$lista_seguidos[$i]['nombre_emprendimiento']}</td>";

            $datos .= "<td class='text-break'>{$lista_seguidos[$i]['nombre_usuario']}</td>";
            $datos .= "<td class='text-break'>{$fecha}</td>";

            $datos .= "<td>";
            $token_emprendedor = hash_hmac('sha1', $lista_seguidos[$i]['id_usuario_emprendedor'], KEY_TOKEN);
            $datos .= '<a class="btn btn-outline-success m-1"  target="_blank"  href="' . $url_base . '/usuarios/usuario_emprendedor/seccion/perfil/pagina_perfil.php?id=' . $lista_seguidos[$i]['id_usuario_emprendedor'] . '&token=' . $token_emprendedor . '"><i class="fa-solid fa-user"></i> Perfil del emprendedor</a>';
            $token_usuario = hash_hmac('sha1', $lista_seguidos[$i]['id_usuario'], KEY_TOKEN);

            $datos .= '<a class="btn btn-outline-success m-1"  target="_blank"  href="' . $url_base . '/admin/paginas/detalles_usuarios/pagina_detalles_usuario.php?id=' . $lista_seguidos[$i]['id_usuario'] . '&token=' . $token_usuario . '"><i class="fa-solid fa-user"></i> Detalles del usuario</a>';
            $datos .= "<button type='button' class='btn btn-outline-danger m-1'  data-bs-toggle='modal' data-bs-target='#eliminarSeguidorEmprendedor' data-bs-nombre_emprendedor='{$lista_seguidos[$i]["nombre_emprendimiento"]}' data-bs-id_usuario_emprendedor='{$lista_seguidos[$i]["id_usuario_emprendedor"]}'><i class='fa-solid fa-trash'></i> Eliminar seguidor</button>";

            $datos .= "</td>";

            $datos .= "</tr>";
        }
    } else {

        $datos .= "<tr>";
        if ($busqueda_activa) {
            $datos .= "<td colspan='5'>Sin resultados</td>";
        } else {
            $datos .= "<td colspan='5'>Por el momento el usuario no sigue a emprendedores</td>";
        }
        $datos .= "</tr>";
    }

    $datos .= "</tbody>";

    $datos .= "</table>";


    return $datos;
}



echo json_encode($respuesta);
