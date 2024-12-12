<?php


function obtenerCondicionWhereBuscadorProductoParaAdmin($campo_buscar, $campo_fecha, $campo_categoria, $campo_estado)
{
    $condicion_where = "";
    $condicion_where .= obtenerCondicionWhereBuscador(['pp.nombre_producto'], $campo_buscar);
    $condicion_where .= obtenerCondicionWhereFecha('pp.fecha_publicacion', $campo_fecha);
    $condicion_where .= obtenerCondicionWhereIgual('pp.id_categoria_producto', $campo_categoria);
    $condicion_where .= obtenerCondicionWhereIgual('pp.id_estado_producto', $campo_estado);

    return $condicion_where;
}


//Funcion utilizada para la pagina buscar productos en el perfil del usuario emprendedor
function obtenerCondicionASCDESCFiltroProducto($num_ordenamiento)
{
    $condicion_ASC_DESC = "";
    switch ($num_ordenamiento) {
        case 1:
            $condicion_ASC_DESC = "pp.fecha_publicacion DESC";
            break;
        case 2:
            $condicion_ASC_DESC = "pp.fecha_publicacion ASC";
            break;
        case 3:
            $condicion_ASC_DESC = "pp.precio DESC";
            break;
        case 4:
            $condicion_ASC_DESC = "pp.precio ASC;";
            break;
    }
    return $condicion_ASC_DESC;
}

//Funcion utilizada para la pagina buscar productos
function obtenerCondicionWhereBuscadorProducto($campo_buscar, $campo_categoria, $campo_calificacion, $campo_precio_minimo, $campo_precio_maximo)
{
    $condicion_where = "";
    $condicion_where .= obtenerCondicionWhereBuscador(['pp.nombre_producto'], $campo_buscar);
    $condicion_where .= obtenerCondicionWhereIgual('pp.id_categoria_producto', $campo_categoria);
    if ($campo_calificacion !== "todos_calificacion") {
        if ($campo_calificacion === "sin_calificacion") {
            $condicion_where .= obtenerCondicionWhereIsNull('pp.calificacion');
        } else {
            if (is_numeric($campo_calificacion)) {
                $condicion_where .= obtenerCondicionWhereIgual('pp.calificacion', $campo_calificacion);
            }
        }
    }
    $condicion_where .= obtenerCondicionWhereRangoPrecio('pp.precio', $campo_precio_minimo, $campo_precio_maximo);
    return $condicion_where;
}


//Funcion utilizada para la pagina buscar productos en el perfil del usuario emprendedor

function obtenerCondicionWhereBuscadorProductoPerfilUsuario($campo_buscar, $campo_categoria, $campo_calificacion, $campo_precio_minimo, $campo_precio_maximo, $campo_estado)
{
    $condicion_where = "";
    $condicion_where .= obtenerCondicionWhereBuscador(['pp.nombre_producto'], $campo_buscar);
    $condicion_where .= obtenerCondicionWhereIgual('pp.id_categoria_producto', $campo_categoria);
    if ($campo_calificacion === "sin_calificacion") {
        $condicion_where .= obtenerCondicionWhereIsNull('pp.calificacion');
    } else {
        if (is_numeric($campo_calificacion)) {
            $condicion_where .= obtenerCondicionWhereIgual('pp.calificacion', $campo_calificacion);
        }
    }
    $condicion_where .= obtenerCondicionWhereRangoPrecio('pp.precio', $campo_precio_minimo, $campo_precio_maximo);


    if ($campo_estado != "todos") {
        if ($campo_estado == "disponible") {
            $condicion_where .= obtenerCondicionWhereIgual('pp.id_estado_producto', "1");

        } else {
            if ($campo_estado == "finalizado") {
                $condicion_where .= obtenerCondicionWhereIgual('pp.id_estado_producto', "3");
            }
        }
    }

    return $condicion_where;
}


function obtenerCondicionWhereBuscadorProductoEmprendedor($campo_buscar, $campo_fecha, $campo_categoria, $campo_estado)
{
    $condicion_where = "";
    $condicion_where .= obtenerCondicionWhereBuscador(['pp.nombre_producto'], $campo_buscar);
    $condicion_where .= obtenerCondicionWhereFecha('pp.fecha_publicacion', $campo_fecha);
    $condicion_where .= obtenerCondicionWhereIgual('pp.id_categoria_producto', $campo_categoria);
    $condicion_where .= obtenerCondicionWhereIgual('pp.id_estado_producto', $campo_estado);

    return $condicion_where;
}



function obtenerListaCardProductosPerfilEmprendedor($conexion, $lista_productos, $cantidad_total_producto,$busqueda_activa)
{
    $token = "";
    $calificacionMaxima = 5;
    $datos = '';
    global $url_base_archivos;
    global $url_base;
    global $calificacion_max_productos;

    if (count($lista_productos) > 0) {
        $mensaje="";
        if ($busqueda_activa) {
            $mensaje="Resultados: {$cantidad_total_producto}";

        }else{
            $mensaje="Productos publicados: {$cantidad_total_producto}";
        }
        $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12 m-2'>";
            $datos .= "<h4 class='mb-0'>$mensaje</h4>";
        $datos .= '</div>';
        for ($i = 0; $i < count($lista_productos); $i++) {

            //Se obtiene las imagenes que tiene el producto
            $listaImagenes = obtenerListaImgProducto($conexion, $lista_productos[$i]['id_publicacion_producto']);
            if (empty($listaImagenes)) {
                throw new Exception("Error:No se pudo obtener todas las imagenes de los productos");
            }
            $datos .= "<div class='col-12 col-sm-6 col-md-6 col-lg-4 mb-3'>";
            $datos .= "<div class='card h-100'>";
            $datos .= "<div class='card-header'>";
            $datos .= "<div class='d-flex align-items-center'>";

            if (is_null($lista_productos[$i]["foto_perfil_nombre"])) {
                $ruta_archivo = "{$url_base_archivos}/img/foto_perfil/foto_de_perfil_predeterminado.jpg";
            } else {
                $ruta_archivo = "{$url_base_archivos}/uploads/{$lista_productos[$i]["id_usuario_emprendedor"]}/foto_perfil/{$lista_productos[$i]["foto_perfil_nombre"]}";
            } 

                $datos .= "<img class='mini_imagen_perfil' src='{$ruta_archivo}' alt='Foto de perfil'>";
                $datos .= "<div>";
                $token = hash_hmac('sha1',$lista_productos[$i]["id_usuario_emprendedor"], KEY_TOKEN);

                $datos .= "<h5 class='text-break mb-0'><a class='link-dark link-offset-2 link-underline link-underline-opacity-0 link-opacity-50-hover' href='{$url_base}/usuarios/usuario_emprendedor/seccion/perfil/pagina_perfil.php?id={$lista_productos[$i]["id_usuario_emprendedor"]}&&token={$token}'>{$lista_productos[$i]['nombre_emprendimiento']}</a></h5>";                                           
                $fecha_publicacion=date('d/m/Y H:i:s', strtotime($lista_productos[$i]['fecha_publicacion']));
                    $datos .= "<p class='mb-0 text-secondary'>{$fecha_publicacion}</p>";
                $datos .= "</div>";
            $datos .= "</div>";
        $datos .= "</div>";
            $datos .= "<div class='card-body'>";
            $datos .= "<div id='carousel-{$i}' class='carousel slide'>";
            $datos .= "<div class='carousel-inner'>";
            $datos .= "<div class='carousel-item active'>";
            $datos .= "<img src='{$url_base_archivos}/uploads/{$lista_productos[$i]["id_usuario_emprendedor"]}/publicaciones_productos/{$listaImagenes[0]["nombre_carpeta"]}/{$listaImagenes[0]["nombre_archivo"]}' class='galeria-img-publicacion-producto d-block w-100 card-img-top' alt='imagen-0'>";
            $datos .= "</div>";
            for ($j = 1; $j < count($listaImagenes); $j++) {
                $datos .= "<div class='carousel-item'>";
                $datos .= "<img src='{$url_base_archivos}/uploads/{$lista_productos[$i]["id_usuario_emprendedor"]}/publicaciones_productos/{$listaImagenes[$j]["nombre_carpeta"]}/{$listaImagenes[$j]["nombre_archivo"]}' class='galeria-img-publicacion-producto d-block w-100 card-img-top' alt='imagen-{$i}'>";
                $datos .= "</div>";
            }
            $datos .= "</div>";
            if (count($listaImagenes) > 1) {
                $datos .= "<button class='carousel-control-prev-publicaciones-producto' type='button' data-bs-target='#carousel-{$i}' data-bs-slide='prev'>";
                $datos .= "<i class='fa-solid fa-chevron-left'></i>";
                $datos .= "</button>";
                $datos .= "<button class='carousel-control-next-publicaciones-producto' type='button' data-bs-target='#carousel-{$i}' data-bs-slide='next'>";
                $datos .= "<i class='fa-solid fa-chevron-right'></i>";
                $datos .= "</button>";
            }
            $datos .= "</div>";

            $token = hash_hmac('sha1', $lista_productos[$i]["id_publicacion_producto"], KEY_TOKEN);

            $datos .= "<h5 class='card-title text-center'>";
            $datos .= "<a class='link-dark link-offset-2 link-underline link-underline-opacity-0 link-opacity-50-hover' href='{$url_base_archivos}/paginas/detalles_producto/pagina_detalles_producto.php?id={$lista_productos[$i]['id_publicacion_producto']}&token={$token}'>{$lista_productos[$i]['nombre_producto']}</a>";
            $datos .= "</h5>";
            $datos .= "<div class='row'>";


            $decimales_precio = ($lista_productos[$i]['precio'] == floor($lista_productos[$i]['precio'])) ? 0 : 2;
            $precio = number_format($lista_productos[$i]['precio'], $decimales_precio,  ',', '.');

            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";

                $datos .= "<p class='card-text'><strong>Estado:</strong>{$lista_productos[$i]["estado"]}</p>";
            $datos .= "</div>";


            $datos .= "<div class='col-12 col-sm-auto col-md-auto col-lg-auto'>";

            $datos .= "<p class='card-text'><strong>Precio:</strong>$" . "{$precio}</p>";
            $datos .= "</div>";
            $datos .= "<div class='col-12 col-sm-auto col-md-auto col-lg-auto'>";
            $datos .= "<p class='card-text'><strong>Stock:</strong>{$lista_productos[$i]["stock"]}</p>";
            $datos .= "</div>";

            $datos .= "<div class='col-12 col-sm-auto col-md-auto col-lg-auto'>";
            $datos .= "<p class='card-text'><strong>Categoria:</strong>{$lista_productos[$i]["nombre_categoria"]}</p>";
            $datos .= "</div>";
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12 text-center'>";
            $datos .= "<p class='card-text'><strong>Calificacion del producto</strong></p>";
            $datos .= "</div>";

            $datos .= "<div class='text-center'>";
            if (is_null($lista_productos[$i]['calificacion'])) {
                $datos .= "<p class='card-text'>Este producto aun no tiene una calificacion</p>";
            } else {
                for ($k = 0; $k < $lista_productos[$i]['calificacion']; $k++) {
                    $datos .= "<i class='fas fa-star star-calificacion-activo'></i>";
                }
                $calificacionRestante = $calificacion_max_productos - $lista_productos[$i]['calificacion'];
                if ($calificacionRestante > 0) {
                    for ($l = 0; $l < $calificacionRestante; $l++) {
                        $datos .= "<i class='fas fa-star star-calificacion'></i>";
                    }
                }
            }

            $datos .= "</div>";

            $datos .= "</div>";
            $datos .= "</div>";

            $datos .= "</div>";
            $datos .= "</div>";
        }
    } else {
        if ($busqueda_activa) {
            $mensaje = 'Sin resultados';
        } else {
            $mensaje = 'No hay productos disponibles por el momento';
        }

        $datos .= '<div class="col-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">';
        $datos .= "<p class='text-center'>{$mensaje}</p>";
        $datos .= '</div>';
    }

    return $datos;
}



function obtenerCondicionWhereBuscadorProductoEmprendedorParaAdmin($campo_buscar, $campo_fecha, $campo_categoria, $campo_estado, $campo_calificacion)
{
    $condicion_where = "";
    if (!empty($campo_buscar) || !empty($campo_fecha) || !empty($campo_categoria) || !empty($campo_estado) || $campo_calificacion !="todos") {
        $condicion_where .= "WHERE";
    }

    $condicion_where_buscador = obtenerCondicionWhereBuscador(['pp.nombre_producto', 'up.nombre_emprendimiento'], $campo_buscar);
    if (!empty($condicion_where_buscador)) {
        $condicion_where .= substr($condicion_where_buscador, 4);
    }
    $condicion_where_fecha = obtenerCondicionWhereFecha('pp.fecha_publicacion', $campo_fecha);
    if (empty($condicion_where_buscador)) {
        $condicion_where .= substr($condicion_where_fecha, 4);
    } else {
        $condicion_where .=  $condicion_where_fecha;
    }


    $condicion_where_categoria = obtenerCondicionWhereIgual('pp.id_categoria_producto', $campo_categoria);
    if (empty($condicion_where_buscador) && empty($condicion_where_fecha) && empty($condicion_where_tipo_usuario)) {
        $condicion_where .= substr($condicion_where_categoria, 4);
    } else {
        $condicion_where .=  $condicion_where_categoria;
    }


    $condicion_where_estado = obtenerCondicionWhereIgual('pp.id_estado_producto', $campo_estado);

    if (empty($condicion_where_buscador) && empty($condicion_where_fecha) && empty($condicion_where_tipo_usuario) && empty($condicion_where_categoria)) {
        $condicion_where .= substr($condicion_where_estado, 4);
    } else {
        $condicion_where .=  $condicion_where_estado;
    }

    $condicion_where_calificacion = "";
    if ($campo_calificacion === "sin_calificacion") {
        $condicion_where_calificacion = obtenerCondicionWhereIsNull('pp.calificacion');
    } else {
        if (is_numeric($campo_calificacion)) {
            $condicion_where_calificacion = obtenerCondicionWhereIgual('pp.calificacion', $campo_calificacion);
        }
    }

    if (empty($condicion_where_estado) && empty($condicion_where_buscador) && empty($condicion_where_fecha) && empty($condicion_where_tipo_usuario) && empty($condicion_where_categoria)) {
        $condicion_where .= substr($condicion_where_calificacion, 4);
    } else {
        $condicion_where .=  $condicion_where_calificacion;
    }

    return $condicion_where;
}
