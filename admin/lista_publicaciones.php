<?php
//Archivos de configuracion y funciones necesarias
include("../config/consultas_bd/consultas_publicaciones.php");
include("../config/consultas_bd/consultas_usuario_admin.php");
include("../config/consultas_bd/conexion_bd.php");
include("../config/funciones/funciones_token.php");
include("../config/funciones/funciones_generales.php");
include("../config/funciones/funciones_session.php");
include("../config/funciones/funciones_publicaciones.php");
include("../config/config_define.php");


//Inicializacion de variables obtenidas de la solicitud POST
$campo_buscar = isset($_POST['campo_buscar']) ? $_POST['campo_buscar'] : '';
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$tipo_publicacion = isset($_POST['tipo_publicacion']) ? $_POST['tipo_publicacion'] : 'todos';
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$cant_registro = isset($_POST['cant_registro']) ? (int)$_POST['cant_registro'] : 10;

$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';


try {

    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario administrador
    if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
        throw new Exception("Debe iniciar sesion para poder ver la lista de publicaciones");
    }

    //Se obtiene los datos de sesion
    $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
    $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario administrador es valido
    if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
        throw new Exception("No puede ver la lista de publicaciones por que no es usuario administrador valido");

    }


    //Obtener la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where = obtenerCondicionWhereBuscadorPublicacionAdmin($campo_buscar, $fecha, $tipo_publicacion);

    //Obtener la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

    //Obtener lista de publicaciones que cumplen con las condiciones de busqueda
    $lista_publicaciones = obtenerListaPublicacionWhereLimitAdmin($conexion, $condicion_where, $condicion_limit);

    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($fecha) || !empty($campo_buscar) || ($tipo_publicacion != 'todos') );

    //Se obtiene la cantidad actual de publicaciones segun las condiciones de busqueda
    $cantidad_publicaciones = count($lista_publicaciones);


    //Genera las cards HTML para las publicaciones 
    $respuesta['cards_publicaciones'] = cargarCardPublicacionesAdmin($conexion, $lista_publicaciones,$busqueda_activa);
    if ($cantidad_publicaciones >= 1) {

        //Se obtiene la cantidad total de publicaciones segun las condiciones de busqueda
        $cantTotalPublicaciones  = cantTotalTodasPublicacion($conexion, $condicion_where);

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantTotalPublicaciones, $cant_registro, $pagina_actual, "nextPagePublicacion");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantTotalPublicaciones / $cant_registro);

        //Se obtiene la cantidad de publicaciones que se va a mostrar en la interfaz del usuario
        $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantTotalPublicaciones, $pagina_actual, $cantidad_publicaciones);

        //Se guarda en respuesta un mensaje indicando la cantidad de publicaciones que se ve en la interfaz del usuario
        $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantTotalPublicaciones} publicaciones";

        //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
        $respuesta['pagina'] = "Pagina " . $pagina_actual  . ' de ' .  $totalPaginas;

    }

} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}


function cargarCardPublicacionesAdmin($conexion, $lista_publicaciones,$busqueda_activa)
{
    $datos = '';
    global $url_base;

    if (count($lista_publicaciones) > 0) {
        for ($i = 0; $i < count($lista_publicaciones); $i++) {
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-10'>";
                $datos .= "<div class='card mb-3'>";
                    $datos .= "<div class='card-header'>";
                        $datos .= "<div class='d-flex align-items-center'>";
                            if (is_null($lista_publicaciones[$i]["foto_perfil_nombre"])) {
                                $ruta_archivo = "../img/foto_perfil/foto_de_perfil_predeterminado.jpg";
                            } else {
                                $ruta_archivo = "../uploads/{$lista_publicaciones[$i]["id_usuario_emprendedor"]}/foto_perfil/{$lista_publicaciones[$i]["foto_perfil_nombre"]}";
                            }
                            $datos .= "<img class='mini_imagen_perfil' src='{$ruta_archivo}' alt='Foto de perfil'>";
                            $datos .= "<div>";
                                $token = hash_hmac('sha1', $lista_publicaciones[$i]["id_usuario_emprendedor"], KEY_TOKEN);
                                $datos .= "<h5 class='text-break mb-0'>{$lista_publicaciones[$i]['nombre_emprendimiento']}</h5>";
                                $fecha_publicacion = date('d/m/Y H:i:s', strtotime($lista_publicaciones[$i]['fecha_publicacion']));
                                $datos .= "<p class='mb-0 text-secondary'>{$fecha_publicacion}</p>";
                            $datos .= "</div>";
                        $datos .= "</div>";
                    $datos .= "</div>";

                    $datos .= "<div class='card-body'>";
                        $datos .= "<div class='row'>";
                            $lista_archivos = obtenerListaArchivosPublicaciones($conexion, $lista_publicaciones[$i]['id_publicacion_informacion']);
                            if (count($lista_archivos) > 0) {
                                $ruta = "../uploads/{$lista_publicaciones[$i]["id_usuario_emprendedor"]}/publicaciones_informacion/{$lista_archivos[0]["nombre_carpeta"]}";
                                $ruta_nombre_archivo = $ruta . "/" . $lista_archivos[0]['nombre_archivo'];
                                $extension = pathinfo($lista_archivos[0]['nombre_archivo'], PATHINFO_EXTENSION);
                                $nombreCaruousel = "carouselPublicaciones-{$lista_publicaciones[$i]["id_publicacion_informacion"]}";
                                $datos .= "<div class='col-12 col-sm-12 col-md-5 col-lg-4'>";
                                    $datos .= "<div id='$nombreCaruousel' class='carousel slide m-2' data-bs-touch='true'>";
                                        $datos .= "<div class='carousel-inner'>";
                                            $datos .= "<div class='carousel-item active'>";
                                                $datos .= crearElementoParaCarouselPublicaciones($nombreCaruousel, $ruta_nombre_archivo, $extension);
                                            $datos .= "</div>";
                                            for ($j = 1; $j < count($lista_archivos); $j++) {
                                                $ruta_nombre_archivo = $ruta . "/" . $lista_archivos[$j]['nombre_archivo'];
                                                $extension = pathinfo($lista_archivos[$j]['nombre_archivo'], PATHINFO_EXTENSION);
                                                $datos .= "<div class='carousel-item'>";
                                                    $datos .= crearElementoParaCarouselPublicaciones($nombreCaruousel, $ruta_nombre_archivo, $extension);
                                                $datos .= "</div>";
                                            }
                                        $datos .= "</div>";

                                        if (count($lista_archivos) > 1) {
                                            $datos .= "<button class='carousel-control-prev-publicaciones' type='button' data-bs-target='#$nombreCaruousel' data-bs-slide='prev'>";
                                                $datos .= "<i class='fa-solid fa-chevron-left'></i>";
                                            $datos .= "</button>";
                                            $datos .= "<button class='carousel-control-next-publicaciones' type='button' data-bs-target='#$nombreCaruousel' data-bs-slide='next'>";
                                                $datos .= "<i class='fa-solid fa-chevron-right'></i>";
                                            $datos .= "</button>";

                                            $datos .= "<div class='indicators-publicaciones carousel-indicators'>";
                                                $datos .= "<button type='button' data-bs-target='#$nombreCaruousel' data-bs-slide-to='0' class='active' aria-current='true' aria-label='Slide 0'></button>";
                                                for ($k = 1; $k < count($lista_archivos); $k++) {
                                                    $datos .= "<button type='button' data-bs-target='#$nombreCaruousel' data-bs-slide-to='{$k}' aria-label='Slide {$k}'></button>";
                                                }
                                            $datos .= "</div>";
                                        }
                                    $datos .= "</div>";
                                $datos .= "</div>";
                            }

                            $datos .= "<div class='col-7 col-sm col-md col-lg'>";
                                $datos .= "<p class='card-text text-break'>{$lista_publicaciones[$i]["descripcion"]}</p>";
                                if ($lista_publicaciones[$i]['map_latitud'] != null && $lista_publicaciones[$i]['map_longitud'] != null) {
                                    $datos .= '<button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modal_ver_map" data-bs-latitud="' . $lista_publicaciones[$i]['map_latitud'] . '" data-bs-longitud="' . $lista_publicaciones[$i]['map_longitud'] . '">';
                                        $datos .= '<i class="fa-solid fa-map-location-dot"></i> Ver ubicacion';
                                    $datos .= '</button>';
                                }
                            $datos .= "</div>";
                        $datos .= "</div>";
                    $datos .= "</div>";
                    $datos .= "<div class='card-footer'>";


                        $token_emprendedor = hash_hmac('sha1', $lista_publicaciones[$i]["id_usuario_emprendedor"], KEY_TOKEN);
                        $datos .= "<a class='btn btn-outline-success m-1' target='_blank' href='{$url_base}/usuarios/usuario_emprendedor/seccion/perfil/pagina_perfil.php?id={$lista_publicaciones[$i]["id_usuario_emprendedor"]}&token={$token_emprendedor}'>Perfil del emprendedor</a>";
            
    
                        $token_usuario = hash_hmac('sha1', $lista_publicaciones[$i]['id_usuario'], KEY_TOKEN);
                        $datos .= "<a class='btn btn-outline-success m-1' target='_blank' href='{$url_base}/admin/paginas/detalles_usuarios/pagina_detalles_usuario.php?id={$lista_publicaciones[$i]['id_usuario']}&token={$token_usuario}'>Datos del usuario</a>";
        
        
                    $datos .= "</div>";
        
                $datos .= "</div>";
            $datos .= "</div>";
        }
    } else {

        $datos .= '<div class="col-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">';
        if ($busqueda_activa) {
            $datos .= '<h2>Sin resultados</h2>';
        } else {
            $datos .= '<h2>No hay publicaciones registradas</h2>';
        }
        $datos .= '</div>';

    }
    return $datos;
}


//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
