<?php

function crearNuevaCarpeta($ruta)
{
    if (!file_exists($ruta)) {
        if (!mkdir($ruta, 0777, true)) {
            throw new Exception("No se pudo crear la carpeta");
        }
    }
}

function eliminarCarpetaConArchivos($ruta)
{
    if (is_dir($ruta)) {

        $archivos_en_ruta = glob($ruta . "/*");
        foreach ($archivos_en_ruta as $archivo) {
            if (is_file($archivo)) {
                unlink($archivo);
            }
        }
        rmdir($ruta);
    }
}

function eliminarCarpetaRecursiva($carpeta) {
    if (is_dir($carpeta)) {
        $elementos = glob($carpeta . '/*');
        foreach ($elementos as $elemento) {
            if (is_dir($elemento)) {
                eliminarCarpetaRecursiva($elemento);
            } else {
                unlink($elemento);
            }
        }
        rmdir($carpeta);
    }
}


function obtenerCondicionWhereBuscador($columnas, $campo_buscar)
{
    // Validar que se hayan pasado columnas y un término de búsqueda
    if (empty($columnas) || $campo_buscar === null || trim($campo_buscar) === "") {
        $condicion_where = "";
    }else{
        $condicion_where = " AND (";
        foreach ($columnas as $columna) {
            $condicion_where .= $columna . " LIKE '%" . addslashes($campo_buscar) . "%' OR ";
        }
        $condicion_where = substr($condicion_where, 0, -4) . ")";
    }

    return $condicion_where;
}



function obtenerCondicionWhereFecha($nombre_columna, $campo_fecha)
{
    $condicion_where = "";
    if ($campo_fecha != null) {
        $condicion_where .= " AND ( DATE(" . $nombre_columna . ") = '" . $campo_fecha . "') ";
    }
    return $condicion_where;
}


function obtenerCondicionWhereCantDias($nombre_columna, $cant_dias)
{
    $condicion_where = "";
    if ($cant_dias != null) {
        $condicion_where .= " AND (" . $nombre_columna . " >= DATE_SUB(CURDATE(),INTERVAL " . $cant_dias  . " DAY) )";
    }
    return $condicion_where;
}

function obtenerCondicionWhereIgual($nombre_columna, $campo)
{
    $condicion_where = "";
    if ($campo != null) {
        $condicion_where .= " AND (" . $nombre_columna . " = '" . $campo . "') ";
    }
    return $condicion_where;
}

function obtenerCondicionWhereIsNull($nombre_columna)
{
    $condicion_where = " AND (" . $nombre_columna . " IS NULL ) ";
    return $condicion_where;
}

function obtenerCondicionWhereIsNotNull($nombre_columna)
{
    $condicion_where = " AND (" . $nombre_columna . " IS NOT NULL ) ";
    return $condicion_where;
}

function obtenerCondicionLimitBuscador($pagina_actual, $cant_registro)
{
    $condicion_limit = "";
    $inicio = ($pagina_actual - 1) * $cant_registro;
    $condicion_limit = "LIMIT $inicio , $cant_registro;";
    return $condicion_limit;
}

function obtenerCondicionWhereRangoPrecio($nombre_columna,  $campo_precio_minimo, $campo_precio_maximo)
{
    $condicion_where = "";
    if ($campo_precio_minimo != null && $campo_precio_maximo != null) {
        $condicion_where .= " AND (" . $nombre_columna . " BETWEEN " . $campo_precio_minimo . " AND " . $campo_precio_maximo . ")";
    }

    return $condicion_where;
}


function calcularRango($pagina, $totalPaginas)
{
    $rango = [
        'inicio' => max(1, $pagina - 4),
        'fin' => min($pagina + 4, $totalPaginas)
    ];

    if ($rango['fin'] - $rango['inicio'] < 8) {
        if ($pagina < 5) {
            $rango['fin'] = min($totalPaginas, 9);
        } else {
            $rango['inicio'] = max(1, $totalPaginas - 8);
        }
    }

    return $rango;
}


function generarPaginacion($totalRegistro, $limit, $pagina, $nombre_funcion)
{
    $paginacion = '<nav aria-label="Page navigation">';
    $paginacion .= '<ul class="pagination">';

    if ($totalRegistro > 0) {
        $totalPaginas = ceil($totalRegistro / $limit);
        $rango = calcularRango($pagina, $totalPaginas);


        if ($rango['inicio'] > 1) {
            // Agregar puntos suspensivos antes de las primeras 10 páginas
            $paginacion .= generarEnlacePagina($pagina, 1, $nombre_funcion);
            $paginacion .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        for ($i = $rango['inicio']; $i <= $rango['fin']; $i++) {
            $paginacion .= generarElementoPagina($i, $pagina, $nombre_funcion);
        }

        if ($rango['fin'] < $totalPaginas) {
            // Agregar puntos suspensivos después de las primeras 10 páginas
            $paginacion .= '<li class="page-item disabled"><span class="page-link">...</span></li>';

            // Agregar enlace para ir a la última página
            $paginacion .= generarEnlacePagina($pagina, $totalPaginas, $nombre_funcion);
        }
    }

    $paginacion .= '</ul>';
    $paginacion .= '</nav>';

    return $paginacion;
}

function generarEnlacePagina($paginaActual, $numeroPagina, $nombre_funcion)
{
    $activeClass = ($paginaActual == $numeroPagina) ? ' active' : '';
    return '<li class="page-item' . $activeClass . '"><a class="page-link" href="#" onclick="' . $nombre_funcion . '(' . $numeroPagina . ')">' . $numeroPagina . '</a></li>';
}

function generarElementoPagina($numeroPagina, $paginaActual, $nombre_funcion)
{
    $activeClass = ($numeroPagina == $paginaActual) ? ' active' : '';
    $elemento = '<li class="page-item' . $activeClass . '">';
    $elemento .= '<a class="page-link" href="#" onclick="' . $nombre_funcion . '(' . $numeroPagina . ')">' . $numeroPagina . '</a>';
    $elemento .= '</li>';
    return $elemento;
}


function crearElementoParaCarouselPublicaciones($nombreCaruousel, $ruta_nombre_archivo, $extension)
{
    $datos = "";
    $extensions_img = array("jpg", "jpeg", "png");
    $extensions_video = array("mp4", "mkv", "avi");

    if (in_array($extension, $extensions_img)) {
        $datos .= "<a data-fancybox='gallery-{$nombreCaruousel}' href='{$ruta_nombre_archivo}'>";
        $datos .= "<img class='galeria-img-publicacion d-block w-100' src='{$ruta_nombre_archivo}'>";
        $datos .= "</a>";
    } else {
        if (in_array($extension, $extensions_video)) {
            $datos .= "<a data-fancybox='gallery-{$nombreCaruousel}' href='{$ruta_nombre_archivo}'>";
            $datos .= "<video class='galeria-img-publicacion d-block w-100' controls>";
            $datos .= "<source src='{$ruta_nombre_archivo}' type='video/mp4'>";
            $datos .= "Tu navegador no soporta el video.";
            $datos .= "</video>";
            $datos .= "</a>";
        }
    }
    return $datos;
}

function obtenerTiempoTranscurrido($fecha)
{
    $fecha_actual = time(); // Fecha y hora actual en segundos desde el epoch
    $marcar_tiempo = strtotime($fecha); // Convertir la fecha de notificación a marcar_tiempo
    // Calcular la diferencia en segundos
    $diferencia = $fecha_actual - $marcar_tiempo;


    if ($diferencia < 60) { // Menos de un minuto
        return "Hace unos segundos";
    } else
        if ($diferencia < 3600) { // Menos de una hora
        $minutos = floor($diferencia / 60);
        if ($minutos == 1) {
            return "Hace 1 minuto";
        } else {
            return "Hace " . $minutos . " minutos";
        }
    } else
        // Calcular el tiempo transcurrido en días, semanas o meses
        if ($diferencia < 86400) { // Menos de un día
            $horas = floor($diferencia / 3600);
            if ($horas == 1) {
                return "Hace 1 hora";
            } else {
                return "Hace " . $horas . " horas";
            }
        } else
            if ($diferencia < 604800) { // Menos de una semana
            $dias = floor($diferencia / 86400);
            if ($dias == 1) {
                return "Hace 1 día";
            } else {
                return "Hace " . $dias . " días";
            }
        } else 
        if ($diferencia < 2592000) { // Menos de un mes
            $semanas = floor($diferencia / 604800);
            if ($semanas == 1) {
                return "Hace 1 semana";
            } else {
                return "Hace " . $semanas . " semanas";
            }
        } else { // Más de un mes
            $meses = floor($diferencia / 2592000);
            if ($meses == 1) {
                return "Hace 1 mes";
            } else {
                return "Hace " . $meses . " meses";
            }
        }
}

function obtenerInicioFinResultados($totalPaginas, $cantTotalResultados, $pagina_actual, $cantidad_actual_resultados)
{
    $datos = [];
    if ($pagina_actual == $totalPaginas) {
        $datos['inicio'] =  $cantTotalResultados - $cantidad_actual_resultados + 1;
        $datos['fin'] = $cantTotalResultados;
    } else {
        $datos['inicio'] = ($pagina_actual - 1) * $cantidad_actual_resultados + 1;
        $datos['fin'] = min($pagina_actual * $cantidad_actual_resultados, $cantTotalResultados);
    }
    return $datos;
}



function navBarUltimasNotificaciones($conexion, $id_usuario)
{
    global $url_usuario;
    $datos = "";
    $notificacion_navbar = obtenerListaUltimasNotificaciones($conexion, $id_usuario);
    $cantidad_notificacion_total = obtenerCantidadNotificacionSinLeer($conexion, $id_usuario);
    if ($cantidad_notificacion_total > 99) {
        $txt_cantidad_notificacion = "+99";
    } else {
        $txt_cantidad_notificacion = $cantidad_notificacion_total;
    }

    $datos .= "<button class='btn btn-dark dropdown-toggle  dropdown-no-arrow' data-bs-toggle='dropdown' aria-expanded='false' id='boton_navbar_notificaciones'>";
    $datos .= "<i class='fa-regular fa-bell'></i>";

    $datos .= "<span class='position-relative'>";
    $datos .= "<span class='position-absolute top-0 start-100 translate-middle badge rounded-pill badge text-bg-light' id='cantidad_notificacion'>{$txt_cantidad_notificacion}</span>";
    $datos .= "</span>";

    $datos .= "</button>";

    $datos .= "<ul class='dropdown-menu dropdown-menu-dark dropdown-menu-end overflow-auto' style='max-height: 300px; max-width=200px'>";

    $datos .= "<li>";
    $datos .= "<h6 class='dropdown-header'>Ultimas notificaciones</h6>";
    $datos .= "</li>";
    $datos .= "<li id='alert_notificacion_navbar' style='margin: 10px;'></li>";

    for ($i = 0; $i < count($notificacion_navbar); $i++) {
        $tiempoTranscurrido = obtenerTiempoTranscurrido($notificacion_navbar[$i]['fecha_notificacion']);
        $leida = ($notificacion_navbar[$i]["leido"] != 0) ? true : false;
        if ($leida) {
            $clase = "notificacion_leida_navbar";
        } else {
            $clase = "notificacion_no_leida_navbar";
        }
        $datos .= "<li class='dropdown-item {$clase}' id='notificacion_{$notificacion_navbar[$i]['id_notificacion']}' data-id='{$notificacion_navbar[$i]['id_notificacion']}'>";

        switch ($notificacion_navbar[$i]['tipo_notificacion']) {
            case 'Seguimiento':
                $datos .= "<strong>{$notificacion_navbar[$i]['nombre_usuario']}</strong> comenzó a seguirte.<span class='text-secondary'> {$tiempoTranscurrido}</span>";
                break;
            case 'Pregunta':
                $datos .= "Recibiste una nueva pregunta sobre tu producto <strong>{$notificacion_navbar[$i]['nombre_producto']}</strong> del usuario <strong>{$notificacion_navbar[$i]['nombre_usuario']}</strong>. <span class='text-secondary'>{$tiempoTranscurrido}</span>";
                break;
            case 'Respuesta':
                $datos .= " <strong>{$notificacion_navbar[$i]['nombre_usuario']}</strong> respondio la pregunta que hiciste al producto <strong>{$notificacion_navbar[$i]['nombre_producto']}</strong>. <span class='text-secondary'>{$tiempoTranscurrido}</span>";
                break;

            default:
                break;
        }

        $datos .= "</li>";
    }

    if (count($notificacion_navbar) == 0) {
        $datos .= "<li class='dropdown-item'>Sin notificaciones por el momento</li>";
    }
    $datos .= "<li><hr class='dropdown-divider'></li>";
    $datos .= "<li class='text-center'>";
    $datos .= "<a class='dropdown-item' href='{$url_usuario}/notificaciones/pagina_notificaciones.php'>Ver todas las notificacion</a>";
    $datos .= "</li>";
    $datos .= "</ul>";
    return $datos;
}


function cargarMasElementos($cantidad_actual,$limite){

    $cargar_mas=false;
    if($cantidad_actual > 0 ){
        if( $cantidad_actual >= $limite){
            $cargar_mas =true;
        }
    }
    return $cargar_mas;
}

