<?php

function cargarListaCardPublicacionesInicio($conexion, $lista_publicaciones,$pagina_actual,$limite_publicacion,$cargar_mas)
{
    $datos = '';
    $mensaje="";
 
    global $url_base_archivos;
    global $url_foto_perfil_predeterminada;

    $cant_publicaciones = count($lista_publicaciones);
    if($cant_publicaciones> 0){

        for ($i=0; $i <$cant_publicaciones ; $i++) { 
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-11  mt-3 mb-3'>";

                $datos .= "<div class='card mb-3'>";
                    $datos .= "<div class='card-header'>";
                        $datos .= "<div class='d-flex align-items-center'>";
                            if(is_null($lista_publicaciones[$i]["foto_perfil_nombre"])){
                                $ruta_archivo=$url_foto_perfil_predeterminada;
                            }else{
                                $ruta_archivo=$url_base_archivos."/uploads/{$lista_publicaciones[$i]["id_usuario_emprendedor"]}/foto_perfil/{$lista_publicaciones[$i]["foto_perfil_nombre"]}";
                            }
                            $datos .= "<img class='mini_imagen_perfil' src='{$ruta_archivo}' alt='Foto de perfil'>";
                            $datos .= "<div>";
                                $token = hash_hmac('sha1',$lista_publicaciones[$i]["id_usuario_emprendedor"], KEY_TOKEN);
                                $datos .= "<h5 class='text-break mb-0'><a class='link-dark link-offset-2 link-underline link-underline-opacity-0 link-opacity-50-hover' href='{$url_base_archivos}/usuarios/usuario_emprendedor/seccion/perfil/pagina_perfil.php?id={$lista_publicaciones[$i]["id_usuario_emprendedor"]}&&token={$token}'>{$lista_publicaciones[$i]['nombre_emprendimiento']}</a></h5>";           
                                $fecha_publicacion=date('d/m/Y H:i:s', strtotime($lista_publicaciones[$i]['fecha_publicacion']));
                                $datos .= "<p class='mb-0 text-secondary'>{$fecha_publicacion}</p>";
                            $datos .= "</div>"; 
                        $datos .= "</div>";
                    $datos .= "</div>";
             
                    $datos .= "<div class='card-body'>";
                        $datos .= "<div class='row'>";
                            $lista_archivos = obtenerListaArchivosPublicaciones($conexion, $lista_publicaciones[$i]['id_publicacion_informacion']);
                            if(count($lista_archivos) > 0){
                                $ruta=$url_base_archivos."/uploads/{$lista_publicaciones[$i]["id_usuario_emprendedor"]}/publicaciones_informacion/{$lista_archivos[0]["nombre_carpeta"]}";
                                $ruta_nombre_archivo=$ruta."/".$lista_archivos[0]['nombre_archivo'];
                                $extension = pathinfo($lista_archivos[0]['nombre_archivo'],PATHINFO_EXTENSION);
                                $nombreCaruousel="carouselPublicaciones-{$lista_publicaciones[$i]["id_publicacion_informacion"]}";
                                $datos .= "<div class='col-12 col-sm-12 col-md-5 col-lg-4'>";
                                    $datos .= "<div id='$nombreCaruousel' class='carousel slide m-2' data-bs-touch='true'>";
                                        $datos .= "<div class='carousel-inner'>";
                                            $datos .= "<div class='carousel-item active'>";
                                                $datos .= crearElementoParaCarouselPublicaciones($nombreCaruousel,$ruta_nombre_archivo, $extension);
                                            $datos .= "</div>";
                                            for ($j=1; $j <count($lista_archivos) ; $j++) {
                                                $ruta_nombre_archivo=$ruta."/".$lista_archivos[$j]['nombre_archivo'];
                                                $extension = pathinfo($lista_archivos[$j]['nombre_archivo'],PATHINFO_EXTENSION); 
                                                $datos .= "<div class='carousel-item'>";
                                                    $datos .= crearElementoParaCarouselPublicaciones($nombreCaruousel,$ruta_nombre_archivo, $extension);
                                                $datos .= "</div>";
                                            }
                                        $datos .= "</div>";
                                        

                                        if(count($lista_archivos) > 1){
                                            $datos .= "<button class='carousel-control-prev-publicaciones' type='button' data-bs-target='#$nombreCaruousel' data-bs-slide='prev'>";
                                                $datos .= "<i class='fa-solid fa-chevron-left'></i>";
                                            $datos .= "</button>";
                                            $datos .= "<button class='carousel-control-next-publicaciones' type='button' data-bs-target='#$nombreCaruousel' data-bs-slide='next'>";
                                                $datos .= "<i class='fa-solid fa-chevron-right'></i>";
                                            $datos .= "</button>";
                                    
                                            $datos .= "<div class='indicators-publicaciones carousel-indicators'>";
                                                $datos .= "<button type='button' data-bs-target='#$nombreCaruousel' data-bs-slide-to='0' class='active' aria-current='true' aria-label='Slide 0'></button>";
                                                for ($k=1; $k <count($lista_archivos) ; $k++) {
                                                    $datos .= "<button type='button' data-bs-target='#$nombreCaruousel' data-bs-slide-to='{$k}' aria-label='Slide {$k}'></button>";

                                                }
                                            $datos .= "</div>";
                                        }
                                    $datos .= "</div>";
                                $datos .= "</div>";

                            }

                            $datos .= "<div class='col-7 col-sm col-md col-lg'>";
                                $datos .= "<p class='card-text text-break'>{$lista_publicaciones[$i]["descripcion"]}</p>";
                                if($lista_publicaciones[$i]['map_latitud'] != null && $lista_publicaciones[$i]['map_longitud'] != null){
                                    $datos .= '<button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modal_ver_map" data-bs-latitud="' . $lista_publicaciones[$i]['map_latitud'] . '" data-bs-longitud="' . $lista_publicaciones[$i]['map_longitud'] . '">';
                                        $datos .= '<i class="fa-solid fa-map-location-dot"></i> Ver ubicacion';
                                    $datos .= '</button>';
                                }
                            $datos .= "</div>";
                        $datos .= "</div>";
                    $datos .= "</div>";
                $datos .= "</div>";
            $datos .= "</div>";
        }
    }


    if(!$cargar_mas){
        if($cant_publicaciones >= 0 && $cant_publicaciones < $limite_publicacion){
            $mensaje="No hay mas publicaciones disponibles en este momento";
         }
    }


    if($pagina_actual == 1 && $cant_publicaciones == 0 ){
        $mensaje="No hay publicaciones disponibles por el momento";

    }

    if(!empty($mensaje)){
        $datos .="<div class='col-12 col-sm-12 col-md-12 col-lg-12 no-publicaciones'>";
        $datos .="<p class='text-center'>{$mensaje}</p>";
        $datos .="</div>";
        
    }
    
    return $datos;
}

function obtenerCondicionWhereBuscadorPublicacion($campo_fecha)
{
    $condicion_where = "";
    $condicion_where .= obtenerCondicionWhereFecha('pp.fecha_publicacion', $campo_fecha);
    return $condicion_where;
}

function cargarListaPublicacionesEmprendedor($conexion, $lista_publicaciones,$busqueda_activa)
{


    $ruta="";
    $datos = '';
    $archivos_url=array();
    global $url_base_archivos;

    $cant_publicaciones = count($lista_publicaciones);
    if($cant_publicaciones> 0){

        for ($i=0; $i <$cant_publicaciones ; $i++) { 
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-8  mt-3 mb-3'>";

                $datos .= "<div class='card mb-3'>";
                    $datos .= "<div class='card-body'>";
                        $datos .= "<div class='row'>";
                      
                            $lista_archivos = obtenerListaArchivosPublicaciones($conexion, $lista_publicaciones[$i]['id_publicacion_informacion']);
                            if(count($lista_archivos) > 0){
                                
                                $ruta=$url_base_archivos."/uploads/{$lista_publicaciones[$i]["id_usuario_emprendedor"]}/publicaciones_informacion/{$lista_archivos[0]["nombre_carpeta"]}";
                                $ruta_nombre_archivo=$ruta."/".$lista_archivos[0]['nombre_archivo'];
                                $extension = pathinfo($lista_archivos[0]['nombre_archivo'],PATHINFO_EXTENSION);
                                array_push($archivos_url,$lista_archivos[0]["nombre_archivo"]);

                                $nombreCaruousel="carouselPublicaciones-{$lista_publicaciones[$i]["id_publicacion_informacion"]}";
                                $datos .= "<div class='col-12 col-sm-12 col-md-5 col-lg-4'>";
                                    $datos .= "<div id='$nombreCaruousel' class='carousel slide m-2' data-bs-touch='true'>";
                                        $datos .= "<div class='carousel-inner'>";
                                            $datos .= "<div class='carousel-item active'>";
                                                $datos .= crearElementoParaCarouselPublicaciones($nombreCaruousel,$ruta_nombre_archivo, $extension);
                                            $datos .= "</div>";
                                            for ($j=1; $j <count($lista_archivos) ; $j++) {
                                                $ruta_nombre_archivo=$ruta."/".$lista_archivos[$j]['nombre_archivo'];
                                                array_push($archivos_url,$lista_archivos[$j]["nombre_archivo"]);
                                                $extension = pathinfo($lista_archivos[$j]['nombre_archivo'],PATHINFO_EXTENSION); 
                                                $datos .= "<div class='carousel-item'>";
                                                    $datos .= crearElementoParaCarouselPublicaciones($nombreCaruousel,$ruta_nombre_archivo, $extension);
                                                $datos .= "</div>";
                                            }
                                        $datos .= "</div>";
                                        

                                        if(count($lista_archivos) > 1){
                                            $datos .= "<button class='carousel-control-prev-publicaciones' type='button' data-bs-target='#$nombreCaruousel' data-bs-slide='prev'>";
                                                $datos .= "<i class='fa-solid fa-chevron-left'></i>";
                                            $datos .= "</button>";
                                            $datos .= "<button class='carousel-control-next-publicaciones' type='button' data-bs-target='#$nombreCaruousel' data-bs-slide='next'>";
                                                $datos .= "<i class='fa-solid fa-chevron-right'></i>";
                                            $datos .= "</button>";
                                    
                                            $datos .= "<div class='indicators-publicaciones carousel-indicators'>";
                                                $datos .= "<button type='button' data-bs-target='#$nombreCaruousel' data-bs-slide-to='0' class='active' aria-current='true' aria-label='Slide 0'></button>";
                                                for ($k=1; $k <count($lista_archivos) ; $k++) {
                                                    $datos .= "<button type='button' data-bs-target='#$nombreCaruousel' data-bs-slide-to='{$k}' aria-label='Slide {$k}'></button>";

                                                }
                                            $datos .= "</div>";
                                        }
                                    $datos .= "</div>";
                                $datos .= "</div>";

                            }

                            $datos .= "<div class='col-7 col-sm col-md col-lg'>";
                            $fecha_publicacion=date('d/m/Y H:i:s', timestamp: strtotime($lista_publicaciones[$i]['fecha_publicacion']));
                            $datos .= "<p class='card-text'><strong>Fecha de publicacion:</strong>{$fecha_publicacion}</p>";
                                $datos .= "<p class='card-text text-break'><strong>Descripcion:</strong>{$lista_publicaciones[$i]["descripcion"]}</p>";
                                if($lista_publicaciones[$i]['map_latitud'] != null && $lista_publicaciones[$i]['map_longitud'] != null){
                                    $datos .= '<button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modal_ver_map" data-bs-latitud="' . $lista_publicaciones[$i]['map_latitud'] . '" data-bs-longitud="' . $lista_publicaciones[$i]['map_longitud'] . '">';
                                        $datos .= '<i class="fa-solid fa-map-location-dot"></i> Ver ubicacion';
                                    $datos .= '</button>';
                                }
                            $datos .= "</div>";
                        $datos .= "</div>";
                    $datos .= "</div>";
                    $datos .= "<div class='card-footer'>";

                        $archivos_html=htmlspecialchars(json_encode($archivos_url));
                        $datos .= "<button type='button' class='btn btn-outline-warning m-1' data-bs-toggle='modal' data-bs-fecha='{$lista_publicaciones[$i]["fecha_publicacion"]}'  data-bs-lista-imagenes='{$archivos_html}'  data-bs-latitud='{$lista_publicaciones[$i]["map_latitud"]}' data-bs-longitud='{$lista_publicaciones[$i]["map_longitud"]}' data-bs-descripcion='{$lista_publicaciones[$i]["descripcion"]}' data-bs-ruta='{$ruta}' data-bs-id_publicacion='{$lista_publicaciones[$i]["id_publicacion_informacion"]}' data-bs-target='#modal_modificar_publicacion'><i class='fa-regular fa-pen-to-square'></i> Modificar</button>"; 
                        $datos .= "<button type='button' class='btn btn-outline-danger m-1' data-bs-toggle='modal' data-bs-target='#modal_eliminar_publicacion' data-bs-fecha='{$lista_publicaciones[$i]["fecha_publicacion"]}'  data-bs-descripcion='{$lista_publicaciones[$i]["descripcion"]}' data-bs-id_publicacion='{$lista_publicaciones[$i]["id_publicacion_informacion"]}'> <i class='fa-solid fa-trash'></i> Eliminar</button>";  
                        $archivos_url=array();
                    $datos .= "</div>";

                $datos .= "</div>";
            $datos .= "</div>";
        }
    }else{
        $datos .= '<div class="col-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">';
        if($busqueda_activa){
            $datos .= '<h3>Sin resultados</h3>';
        }else{
            $datos .= '<h3>La lista de publicaciones esta vacia</h3>';
        }
        $datos .= '</div>';
    }






    return $datos;
}


function obtenerCondicionWhereBuscadorPublicacionAdmin($campo_buscar, $campo_fecha, $select_tipo_publicacion)
{
    $condicion_where = "";
    if (!empty($campo_buscar) || !empty($campo_fecha)  || $select_tipo_publicacion != "todos") {
        $condicion_where .= "WHERE";
    }

    $condicion_where_buscador = obtenerCondicionWhereBuscador(['up.nombre_emprendimiento'], $campo_buscar);
    if (!empty($condicion_where_buscador)) {
        $condicion_where .= substr($condicion_where_buscador, 4);
    }

    $condicion_where_fecha = obtenerCondicionWhereFecha('p.fecha_publicacion', $campo_fecha);
    if (empty($condicion_where_buscador)) {
        $condicion_where .= substr($condicion_where_fecha, 4);
    } else {
        $condicion_where .=  $condicion_where_fecha;
    }

    $condicion_where_tipo_publicacion = "";
    switch ($select_tipo_publicacion) {
        case "solo_descrip":

            $condicion_where_tipo_publicacion .= obtenerCondicionWhereIsNull('ap.id_archivo_publicacion');
            $condicion_where_tipo_publicacion .= obtenerCondicionWhereIsNull('p.map_latitud');
            $condicion_where_tipo_publicacion .= obtenerCondicionWhereIsNull('p.map_longitud');
            break;
        case "fotos/videos":
            $condicion_where_tipo_publicacion .= obtenerCondicionWhereIsNotNull('ap.id_archivo_publicacion');
            $condicion_where_tipo_publicacion .= obtenerCondicionWhereIsNull('p.map_latitud');
            $condicion_where_tipo_publicacion .= obtenerCondicionWhereIsNull('p.map_longitud');

            break;
        case "ubicacion":
            $condicion_where_tipo_publicacion .= obtenerCondicionWhereIsNull('ap.id_archivo_publicacion');
            $condicion_where_tipo_publicacion .= obtenerCondicionWhereIsNotNull('p.map_latitud');
            $condicion_where_tipo_publicacion .= obtenerCondicionWhereIsNotNull('p.map_longitud');

            break;
        case "fotos/videos/ubicacion":
            $condicion_where_tipo_publicacion .= obtenerCondicionWhereIsNotNull('ap.id_archivo_publicacion');
            $condicion_where_tipo_publicacion .= obtenerCondicionWhereIsNotNull('p.map_latitud');
            $condicion_where_tipo_publicacion .= obtenerCondicionWhereIsNotNull('p.map_longitud');

            break;
        default:
            $condicion_where_tipo_publicacion = "";
            break;
    }

    if (empty($condicion_where_buscador) && empty($condicion_where_fecha)) {
        $condicion_where .= substr($condicion_where_tipo_publicacion, 4);
    } else {
        $condicion_where .=  $condicion_where_tipo_publicacion;
    }
    return $condicion_where;
}

