<?php

//Respuestas
function obtenerCondicionWhereBuscadorPreguntasRespuestasEmprendedor($campo_buscar_producto, $campo_buscar_usuario,$campo_fecha, $campo_estado, $campo_filtro_preguntas)
{
    $condicion_where = "";
    $condicion_where .= obtenerCondicionWhereBuscador(['pp.nombre_producto'], $campo_buscar_producto);
    $condicion_where .= obtenerCondicionWhereBuscador(['u.nombre_usuario'], $campo_buscar_usuario);
    $condicion_where .= obtenerCondicionWhereFecha('pr.fecha_pregunta', $campo_fecha);
    $condicion_where .= obtenerCondicionWhereIgual('pp.id_estado_producto', $campo_estado);

    if ($campo_filtro_preguntas !== "Todas") {
        if ($campo_filtro_preguntas === "NoRespondidas") {
            $condicion_where .= obtenerCondicionWhereIsNull('pr.respuesta');
        } else {
            if ($campo_filtro_preguntas === "Respondidas") {
                $condicion_where .= obtenerCondicionWhereIsNotNull('pr.respuesta');
            }
        }
    }
    return $condicion_where;

}

function cargarListaCardRespuestaProductoEmprendedor($lista_preguntas,$busqueda_activa)
{
    $datos = '';
    global $url_base;
    global $url_base_archivos;

    $fechaAnterior = null;
    if (count($lista_preguntas) > 0) {
        for ($i = 0; $i < count($lista_preguntas); $i++) {
            $fecha = date("d/m/Y", strtotime($lista_preguntas[$i]['fecha_pregunta']));
            if ($fechaAnterior != $fecha) {
                $fechaAnterior = $fecha;
                $datos .= "<h4>Preguntas recibidas el {$fecha}</h4>";
            }
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-9 mb-4'>";
            $datos .= "<div class='card h-100 mb-3'>";
      
                $datos .= '<div class="card-header">';
                    $datos .= '<div class="row div_preguntas_producto">';
                    
                        $datos .= '<div class="col-2 col-sm-2 col-md-1 col-lg-1">';
                            $datos .= '<img class="list_img_mis_preguntas" src="' . $url_base_archivos . '/uploads/' . $lista_preguntas[$i]['id_usuario_emprendedor'] . '/publicaciones_productos/' . $lista_preguntas[$i]['nombre_carpeta'] . '/' . $lista_preguntas[$i]['nombre_archivo'] . '">';
                        $datos .= '</div>';
                        
                        $datos .= '<div class="col-10 col-sm-10 col-md-4 col-lg-9">';
                            $datos .= '<p class="text-break">' . $lista_preguntas[$i]['nombre_producto'] . '</p>';
                        $datos .= '</div>';
                        $datos .= '<div class="col-12 col-sm-12 col-md-3 col-lg-2">';
                            $datos .= '<p><strong>Estado:</strong>' . $lista_preguntas[$i]['estado'] . '</p>';
                        $datos .= '</div>';
                    $datos .= '</div>';

                $datos .= '</div>';

                $datos .= '<div class="card-body">';
                    $datos .= '<div class="row">';
                        $datos .= '<div class="col-12 col-sm-12 col-md-12 col-lg-9">';
                            $datos .= '<p><strong>Nombre de usuario:</strong>' . $lista_preguntas[$i]['nombre_usuario'] . '</p>';

                            $fecha_pregunta=date("d/m/Y H:i:s", strtotime($lista_preguntas[$i]['fecha_pregunta']));
                            $datos .= "<li><strong>Pregunta:</strong>{$lista_preguntas[$i]['pregunta']}<span style='color: #8a8a8a;'>({$fecha_pregunta})</span></li>";
                                if (!is_null($lista_preguntas[$i]['respuesta'])) {
                                    $fecha_respuesta=date("d/m/Y H:i:s", strtotime($lista_preguntas[$i]['fecha_respuesta']));
                                    $datos .= "<ul>";
                                        $datos .= "<li><strong>Respuesta:</strong>{$lista_preguntas[$i]['respuesta']}<span style='color: #8a8a8a;'>({$fecha_respuesta})</span></li>";
                                    $datos .= "</ul>";
                                }
                            $datos .= "</li>";

                        $datos .= '</div>';
                        $datos .= '<div class="col-12 col-sm-12 col-md-12 col-lg-3 mt-1">';
                            if ($lista_preguntas[$i]['respuesta'] == null && $lista_preguntas[$i]['fecha_respuesta'] == null) {
                                $datos .= '<button type="button" class="btn btn-outline-success m-1" data-bs-toggle="modal" data-bs-target="#agregarModal" data-bs-fecha="' . date("d/m/Y H:i:s", strtotime($lista_preguntas[$i]['fecha_pregunta'])) . '" data-bs-nombre="' . $lista_preguntas[$i]['nombre_usuario'] . '" data-bs-id_producto="' . $lista_preguntas[$i]['id_producto'] . '" data-bs-id_pregunta="' . $lista_preguntas[$i]['id_pregunta_respuesta'] . '" data-bs-pregunta="' . $lista_preguntas[$i]['pregunta'] . '"><i class="fa-solid fa-circle-plus"></i> Responder</button>';
                            } else {
                                $datos .= '<button type="button" class="btn btn-outline-danger m-1" data-bs-toggle="modal" data-bs-target="#eliminaModal"  data-bs-id_producto="' . $lista_preguntas[$i]['id_producto'] . '" data-bs-id_pregunta="' . $lista_preguntas[$i]['id_pregunta_respuesta'] . '" data-bs-fecha="' . $lista_preguntas[$i]['fecha_respuesta'] . '" data-bs-respuesta="' . $lista_preguntas[$i]['respuesta'] . '"> <i class="fa-solid fa-trash"></i> Eliminar Respuesta</button>';
                            }
                            $token=hash_hmac('sha1', $lista_preguntas[$i]['id_producto'], KEY_TOKEN);
                            $datos .= "<a class='btn btn-outline-primary m-1' target='_blank' href='{$url_base}/paginas/detalles_producto/pagina_detalles_producto.php?id={$lista_preguntas[$i]['id_producto']}&token={$token}'><i class='fa-solid fa-magnifying-glass'></i>  Ver producto</a>";                    

                        $datos .= '</div>';
                    $datos .= '</div>';

                $datos .= '</div>';
                $datos .= '</div>';
            $datos .= '</div>';
        }
    } else {
        if ($busqueda_activa) {
            $mensaje = 'Sin resultados';
        } else {
            $mensaje = 'Por el momento no se recibio alguna pregunta';
        }
        $datos .= "<h3 class='text-center'>{$mensaje}</h3>";
    }
    return $datos;
}

function cargarListaCardRespuestaProductoEmprendedorAdmin($lista_preguntas,$busqueda_activa)
{
    $datos = '';
    global $url_base;
    global $url_base_archivos;

    $fechaAnterior = null;
    if (count($lista_preguntas) > 0) {
        for ($i = 0; $i < count($lista_preguntas); $i++) {
            $fecha = date("d/m/Y", strtotime($lista_preguntas[$i]['fecha_pregunta']));
            if ($fechaAnterior != $fecha) {
                $fechaAnterior = $fecha;
                $datos .= "<h4>Preguntas recibidas el {$fecha}</h4>";
            }
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-9 mb-4'>";
            $datos .= "<div class='card h-100 mb-3'>";
      
                $datos .= '<div class="card-header">';
                    $datos .= '<div class="row div_preguntas_producto">';
                    
                        $datos .= '<div class="col-2 col-sm-2 col-md-1 col-lg-1">';
                            $datos .= '<img class="list_img_mis_preguntas" src="' . $url_base_archivos . '/uploads/' . $lista_preguntas[$i]['id_usuario_emprendedor'] . '/publicaciones_productos/' . $lista_preguntas[$i]['nombre_carpeta'] . '/' . $lista_preguntas[$i]['nombre_archivo'] . '">';
                        $datos .= '</div>';
                        
                        $datos .= '<div class="col-10 col-sm-10 col-md-4 col-lg-7">';
                            $datos .= '<p class="text-break">' . $lista_preguntas[$i]['nombre_producto'] . '</p>';
                        $datos .= '</div>';
                        $datos .= '<div class="col-4 col-sm-4 col-md-3 col-lg-2">';
                            $datos .= '<p><strong>Estado:</strong>' . $lista_preguntas[$i]['estado'] . '</p>';
                        $datos .= '</div>';
                    $datos .= '</div>';

                $datos .= '</div>';

                $datos .= '<div class="card-body">';
                    $datos .= '<div class="row">';

                    $datos .= '<div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-1">';
         
                    
                    $token_producto=hash_hmac('sha1', $lista_preguntas[$i]['id_producto'], KEY_TOKEN);
                    $datos .= "<a class='btn btn-outline-primary m-1' target='_blank' href='{$url_base}/paginas/detalles_producto/pagina_detalles_producto.php?id={$lista_preguntas[$i]["id_producto"]}&token={$token_producto}'><i class='fa-solid fa-magnifying-glass'></i> Vista general</a>";
                    $datos .= "<a class='btn btn-outline-primary m-1' target='_blank' href='{$url_base}/admin/paginas/detalles_usuarios/producto/pagina_detalles_producto.php?id={$lista_preguntas[$i]['id_producto']}&token={$token_producto}'><i class='fa-solid fa-magnifying-glass'></i> Vista Administrador</a>";
                    
                    
                    if ($lista_preguntas[$i]['respuesta'] != null && $lista_preguntas[$i]['fecha_respuesta'] != null) {
                        $datos .= '<button type="button" class="btn btn-outline-warning m-1" data-bs-fecha_respuesta="' . $lista_preguntas[$i]['fecha_respuesta'] . '" data-bs-respuesta="' . $lista_preguntas[$i]['respuesta'] . '"  data-bs-toggle="modal" data-bs-target="#modificarModalRespuesta" data-bs-id_respuesta="' . $lista_preguntas[$i]['id_pregunta_respuesta'] . '" ><i class="fa-regular fa-pen-to-square"></i> Modificar Respuesta</button>';
                        $datos .= '<button type="button" class="btn btn-outline-danger m-1" data-bs-toggle="modal" data-bs-target="#eliminarModalPreguntasRecibidas"  data-bs-id_pregunta_producto="' . $lista_preguntas[$i]['id_producto'] . '" data-bs-id_pregunta_recibida="' . $lista_preguntas[$i]['id_pregunta_respuesta'] . '" data-bs-fecha="' . $lista_preguntas[$i]['fecha_respuesta'] . '" data-bs-respuesta="' . $lista_preguntas[$i]['respuesta'] . '"> <i class="fa-solid fa-trash"></i> Eliminar Respuesta</button>';
                    }
                    
                    $datos .= '</div>';


                        $datos .= '<div class="col-12 col-sm-12 col-md-12 col-lg-12">';
                            $datos .= '<p><strong>Nombre de usuario:</strong>' . $lista_preguntas[$i]['nombre_usuario'] . '</p>';

                            $fecha_pregunta=date("d/m/Y H:i:s", strtotime($lista_preguntas[$i]['fecha_pregunta']));
                            $datos .= "<li><strong>Pregunta:</strong>{$lista_preguntas[$i]['pregunta']}<span style='color: #8a8a8a;'>({$fecha_pregunta})</span></li>";
                                if (!is_null($lista_preguntas[$i]['respuesta'])) {
                                    $fecha_respuesta=date("d/m/Y H:i:s", strtotime($lista_preguntas[$i]['fecha_respuesta']));
                                    $datos .= "<ul>";
                                        $datos .= "<li><strong>Respuesta:</strong>{$lista_preguntas[$i]['respuesta']}<span style='color: #8a8a8a;'>({$fecha_respuesta})</span></li>";
                                    $datos .= "</ul>";
                                }
                            $datos .= "</li>";

                        $datos .= '</div>';


                    $datos .= '</div>';

                $datos .= '</div>';
                $datos .= '</div>';
            $datos .= '</div>';
        }
    } else {
        if ($busqueda_activa) {
            $mensaje = 'Sin resultados';
        } else {
            $mensaje = 'Por el momento no se recibio alguna pregunta';
        }
        $datos .= "<h3 class='text-center'>{$mensaje}</h3>";
    }
    return $datos;
}

//Preguntas

function obtenerCondicionWhereBuscadorProductoPregunta($campo_buscar, $cant_dias, $campo_estado)
{
    $condicion_where = "";
    $condicion_where .= obtenerCondicionWhereBuscador(['pp.nombre_producto'], $campo_buscar);
    $condicion_where .= obtenerCondicionWhereCantDias('pr.fecha_pregunta', $cant_dias);
    $condicion_where .= obtenerCondicionWhereIgual('pp.id_estado_producto', $campo_estado);
    return $condicion_where;
}


function cargarListaCardMisPreguntas($preguntas, $busqueda_activa)
{
    global $url_base_archivos;
    global $url_base;

    $datos = '';
    if (count($preguntas) > 0) {
        $productoAnterior = null;
        for ($i = 0; $i < count($preguntas); $i++) {
            if ($productoAnterior != $preguntas[$i]['id_producto']) {
                if ($productoAnterior !== null) {
                    $datos .= '</div>';
                    $datos .= '</div>';
                    $datos .= '</div>';
                    $datos .= '</div>';
                }

                $datos .= '<div class="card mb-4">';
                $datos .= '<div class="card-header">';
                $datos .= '<div class="row div_preguntas_producto">';
                $datos .= '<div class="col-2 col-sm-2 col-md-1 col-lg-1">';
                $datos .= '<img class="list_img_mis_preguntas" src="' . $url_base_archivos . '/uploads/' . $preguntas[$i]['id_usuario_emprendedor'] . '/publicaciones_productos/' . $preguntas[$i]['nombre_carpeta'] . '/' . $preguntas[$i]['nombre_archivo'] . '">';
                $datos .= '</div>';

                $datos .= '<div class="col-10 col-sm-10 col-md-4 col-lg-6">';
                $datos .= '<p class="text-break">' . $preguntas[$i]['nombre_producto'] . '</p>';
                $datos .= '</div>';

                $datos .= '<div class="col-12 col-sm-4 col-md-2 col-lg-1">';
                $datos .= '<p class="text-break"><strong>Stock:</strong>' . $preguntas[$i]['stock'] . '</p>';
                $datos .= '</div>';
                $decimales_precio = ($preguntas[$i]['precio'] == floor($preguntas[$i]['precio'])) ? 0 : 2;
                $precio = number_format($preguntas[$i]['precio'], $decimales_precio,  ',', '.');
                $datos .= '<div class="col-12 col-sm-4 col-md-2 col-lg-2">';
                $datos .= '<p class="text-break"><strong>Precio:</strong>$' . $precio . '</p>';
                $datos .= '</div>';

                $datos .= '<div class="col-12 col-sm-4 col-md-3 col-lg-2">';
                $datos .= '<p><strong>Estado:</strong>' . $preguntas[$i]['estado'] . '</p>';
                $datos .= '</div>';
                $datos .= '</div>';
                $datos .= '</div>';

                $datos .= '<div class="card-body">';
                if ($preguntas[$i]['estado'] === 'Disponible') {
                    $datos .= '<div class="pb-3">';
                    $datos .= '<button type="button" class="btn btn-outline-success" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#agregarModal" data-bs-id_producto="' . $preguntas[$i]['id_producto'] . '" style="margin-right: 10px;"><i class="fa-solid fa-plus"></i> Hacer otra pregunta</button>';
                    $token=hash_hmac('sha1', $preguntas[$i]['id_producto'], KEY_TOKEN);
                    $datos .= "<a class='btn btn-outline-primary' href='{$url_base}/paginas/detalles_producto/pagina_detalles_producto.php?id={$preguntas[$i]['id_producto']}&token={$token}'><i class='fa-solid fa-magnifying-glass'></i>  Ver producto</a>";                    
                    $datos .= '</div>';
                }

                $datos .= '<div class="accordion" id="accordionPanels-' . $productoAnterior . '">';
            }

            if ($preguntas[$i]['respuesta']  != null && $preguntas[$i]['fecha_respuesta']  != null) {
                $datos .= '<div class="accordion-item">';
                $datos .= '<h2 class="accordion-header">';
                $datos .= '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-' . $i . '" aria-expanded="true" aria-controls="panelsStayOpen-' . $i . '">';
                $datos .= '<p class="text-break m-0">' . $preguntas[$i]['pregunta'] . '<span style="color: #8a8a8a;">(' . date("d/m/Y H:i:s", strtotime($preguntas[$i]['fecha_pregunta'])) . ')</span></p>';
                $datos .= '</button>';
                $datos .= '</h2>';
                $datos .= '<div id="panelsStayOpen-' . $i . '" class="accordion-collapse collapse">';
                $datos .= '<div class="accordion-body">';
                $datos .= '<ul class="m-0">';
                $datos .= '<li>' . $preguntas[$i]['respuesta'] . '<span style="color: #8a8a8a;">(' . date("d/m/Y H:i:s", strtotime($preguntas[$i]['fecha_respuesta'])) . ')</span></li>';
                $datos .= '</ul>';
                $datos .= '</div>';
                $datos .= '</div>';
                $datos .= '</div>';
            } else {
                $datos .= '<div class="card accordion-item">';
                $datos .= '<div class="card-body" style="background-color: rgba(0, 0, 0, .04);">';
                $datos .= '<div class="row">';
                $datos .= '<div class="col-12 col-sm-12 col-md-10 col-lg-10">';
                $datos .= '<p class="card-text  m-0">' . $preguntas[$i]['pregunta'] . '<span style="color: #8a8a8a;">(' . date("d/m/Y H:i:s", strtotime($preguntas[$i]['fecha_pregunta'])) . ')</span></p>';
                $datos .= '</div>';
                $datos .= '<div class="col-auto col-sm-auto col-md-2 col-lg-2">';
                $datos .= '<button type="button" class="btn btn-outline-danger" data-bs-fecha="' . date("d/m/Y H:i:s", strtotime($preguntas[$i]['fecha_pregunta'])) . '" data-bs-pregunta="' . $preguntas[$i]['pregunta'] . '"  data-bs-toggle="modal" data-bs-target="#eliminaModal" data-bs-id_pregunta="' . $preguntas[$i]['id_pregunta_respuesta'] . '" > <i class="fa-solid fa-trash"></i> Eliminar Pregunta</button>';
                $datos .= '</div>';
                $datos .= '</div>';
                $datos .= '</div>';
                $datos .= '</div>';
            }
            $productoAnterior = $preguntas[$i]['id_producto'];
        }

        if ($productoAnterior !== null) {
            $datos .= '</div>';
            $datos .= '</div>';
            $datos .= '</div>';
            $datos .= '</div>';
        }
    } else {
        if ($busqueda_activa) {
            $mensaje = 'Sin resultados';
        } else {
            $mensaje = 'Por el momento no hiciste alguna pregunta';
        }

        $datos .= "<h3 class='text-center'>{$mensaje}</h3>";
    }
    
    return $datos;
}

function cargarListaCardPreguntasEmprendedorParaAdmin($preguntas, $busqueda_activa)
{
    global $url_base_archivos;
    global $url_base;

    $datos = '';
    if (count($preguntas) > 0) {
        $productoAnterior = null;
        for ($i = 0; $i < count($preguntas); $i++) {
            if ($productoAnterior != $preguntas[$i]['id_producto']) {
                if ($productoAnterior !== null) {
                    $datos .= '</div>';
                    $datos .= '</div>';
                    $datos .= '</div>';
                    $datos .= '</div>';
                }

                $datos .= '<div class="card mb-4">';
                $datos .= '<div class="card-header">';
                $datos .= '<div class="row div_preguntas_producto">';
                $datos .= '<div class="col-2 col-sm-2 col-md-1 col-lg-1">';
                $datos .= '<img class="list_img_mis_preguntas" src="' . $url_base_archivos . '/uploads/' . $preguntas[$i]['id_usuario_emprendedor'] . '/publicaciones_productos/' . $preguntas[$i]['nombre_carpeta'] . '/' . $preguntas[$i]['nombre_archivo'] . '">';
                $datos .= '</div>';

                $datos .= '<div class="col-10 col-sm-10 col-md-4 col-lg-6">';
                $datos .= '<p class="text-break">' . $preguntas[$i]['nombre_producto'] . '</p>';
                $datos .= '</div>';

                $datos .= '<div class="col-4 col-sm-4 col-md-2 col-lg-1">';
                $datos .= '<p class="text-break"><strong>Stock:</strong>' . $preguntas[$i]['stock'] . '</p>';
                $datos .= '</div>';
                $decimales_precio = ($preguntas[$i]['precio'] == floor($preguntas[$i]['precio'])) ? 0 : 2;
                $precio = number_format($preguntas[$i]['precio'], $decimales_precio,  ',', '.');
                $datos .= '<div class="col-4 col-sm-4 col-md-2 col-lg-2">';
                $datos .= '<p class="text-break"><strong>Precio:</strong>$' . $precio . '</p>';
                $datos .= '</div>';

                $datos .= '<div class="col-4 col-sm-4 col-md-3 col-lg-2">';
                $datos .= '<p><strong>Estado:</strong>' . $preguntas[$i]['estado'] . '</p>';
                $datos .= '</div>';
                $datos .= '</div>';
                $datos .= '</div>';

                $datos .= '<div class="card-body">';
         
                $datos .= '<div class="pb-3">';
                $token_producto=hash_hmac('sha1', $preguntas[$i]['id_producto'], KEY_TOKEN);

                if ($preguntas[$i]['estado'] !== 'Pausado') {
                    $datos .= "<a class='btn btn-outline-primary m-1' target='_blank' href='{$url_base}/paginas/detalles_producto/pagina_detalles_producto.php?id={$preguntas[$i]["id_producto"]}&token={$token_producto}'><i class='fa-solid fa-magnifying-glass'></i> Vista general</a>";
                }
              
                $datos .= "<a class='btn btn-outline-primary m-1' target='_blank' href='{$url_base}/admin/paginas/detalles_usuarios/producto/pagina_detalles_producto.php?id={$preguntas[$i]['id_producto']}&token={$token_producto}'><i class='fa-solid fa-magnifying-glass'></i> Vista Administrador</a>";
                    $datos .= '</div>';
                
                $datos .= '<div class="accordion" id="accordionPanels-' . $productoAnterior . '">';
            }

            if ($preguntas[$i]['respuesta']  != null && $preguntas[$i]['fecha_respuesta']  != null) {
                $datos .= '<div class="accordion-item">';
                $datos .= '<h2 class="accordion-header">';

                    $datos .= '<div class="col-12 col-sm-12 col-md-12 col-lg-12">';
                        $datos .= '<div class="row">';

                            $datos .= '<div class="col-auto col-sm-auto col-md-9 col-lg-10">';
                                $datos .= '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-' . $i . '" aria-expanded="true" aria-controls="panelsStayOpen-' . $i . '">';
                                    $datos .= '<p class="text-break m-0">' . $preguntas[$i]['pregunta'] . '<span style="color: #8a8a8a;">(' . date("d/m/Y H:i:s", strtotime($preguntas[$i]['fecha_pregunta'])) . ')</span></p>';
                                $datos .= '</button>';
                            $datos .= '</div>';


                            $datos .= '<div class="col-auto col-sm-auto col-md-3 col-lg-2">';
                                $datos .= '<button type="button" class="btn btn-outline-warning" data-bs-fecha="' . $preguntas[$i]['fecha_pregunta'] . '" data-bs-pregunta="' . $preguntas[$i]['pregunta'] . '"  data-bs-toggle="modal" data-bs-target="#modificarModalPregunta" data-bs-id_pregunta="' . $preguntas[$i]['id_pregunta_respuesta'] . '" ><i class="fa-regular fa-pen-to-square"></i> Modificar Pregunta</button>';
                            $datos .= '</div>';
                        $datos .= '</div>';
                    $datos .= '</div>';

                $datos .= '</h2>';
                $datos .= '<div id="panelsStayOpen-' . $i . '" class="accordion-collapse collapse">';
                $datos .= '<div class="accordion-body">';


                $datos .= '<ul class="m-0">';
                    $datos .= '<li>' . $preguntas[$i]['respuesta'] . '<span style="color: #8a8a8a;">(' . date("d/m/Y H:i:s", strtotime($preguntas[$i]['fecha_respuesta'])) . ')</span></li>';
                $datos .= '</ul>';


                $datos .= '</div>';
      
                $datos .= '</div>';
                $datos .= '</div>';
            } else {
                $datos .= '<div class="card accordion-item">';
                $datos .= '<div class="card-body" style="background-color: rgba(0, 0, 0, .04);">';
                $datos .= '<div class="row">';
                $datos .= '<div class="col-12 col-sm-12 col-md-10 col-lg-10">';
                $datos .= '<p class="card-text  m-0">' . $preguntas[$i]['pregunta'] . '<span style="color: #8a8a8a;">(' . date("d/m/Y H:i:s", strtotime($preguntas[$i]['fecha_pregunta'])) . ')</span></p>';
                $datos .= '</div>';
                
                $datos .= '<div class="col-auto col-sm-auto col-md-2 col-lg-2">';
                
                $datos .= '<button type="button" class="btn btn-outline-danger m-1" data-bs-fecha="' . date("d/m/Y H:i:s", strtotime($preguntas[$i]['fecha_pregunta'])) . '" data-bs-pregunta="' . $preguntas[$i]['pregunta'] . '"  data-bs-toggle="modal" data-bs-target="#eliminaModalPregunta" data-bs-id_pregunta="' . $preguntas[$i]['id_pregunta_respuesta'] . '" > <i class="fa-solid fa-trash"></i> Eliminar Pregunta</button>';

                $datos .= '<button type="button" class="btn btn-outline-warning" data-bs-fecha="' . $preguntas[$i]['fecha_pregunta'] . '" data-bs-pregunta="' . $preguntas[$i]['pregunta'] . '"  data-bs-toggle="modal" data-bs-target="#modificarModalPregunta" data-bs-id_pregunta="' . $preguntas[$i]['id_pregunta_respuesta'] . '" ><i class="fa-regular fa-pen-to-square"></i> Modificar Pregunta</button>';
                $datos .= '</div>';
                $datos .= '</div>';
                $datos .= '</div>';
                $datos .= '</div>';
            }
            $productoAnterior = $preguntas[$i]['id_producto'];
        }

        if ($productoAnterior !== null) {
            $datos .= '</div>';
            $datos .= '</div>';
            $datos .= '</div>';
            $datos .= '</div>';
        }
    } else {
        if ($busqueda_activa) {
            $mensaje = 'Sin resultados';
        } else {
            $mensaje = 'Por el momento no hizo alguna pregunta';
        }

        $datos .= "<h3 class='text-center'>{$mensaje}</h3>";
    }
    return $datos;
}
