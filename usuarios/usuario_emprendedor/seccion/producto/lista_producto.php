<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/funciones/funciones_productos.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/config_define.php");



//Inicializacion de variables obtenidas de la solicitud POST
$campo_buscar = isset($_POST['campo_buscar']) ? $_POST['campo_buscar'] : '';
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$cant_registro = isset($_POST['cant_registro']) ? (int)$_POST['cant_registro'] : 10;
$categoria = isset($_POST['categoria']) ? (int)$_POST['categoria'] : null;
$estado = isset($_POST['estado']) ? (int)$_POST['estado'] : null;


$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';



try {

    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario
    if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
        throw new Exception("Debe iniciar sesion para poder ver las publicaciones de productos");
    }

    //Se obtiene los datos de sesion
    $id_usuario = $_SESSION['id_usuario'];
    $tipo_usuario = $_SESSION['tipo_usuario'];

    //Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario es valido
    if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
        throw new Exception("No se puede mostrar la informacion de los productos debido a que su cuenta no esta activa o esta baneado");
    }

    //Verifica que el usuario sea un usuario emprendedor
    if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
        throw new Exception("No se puede ver la informacion de los productos ya que no es un usuario emprendedor");
    }


    //Se obtiene datos del usuario emprendedor por el id del usuario
    $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
    if (empty($usuario_emprendedor)) {
        throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
    }
    $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];



    //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where = obtenerCondicionWhereBuscadorProductoEmprendedor($campo_buscar, $fecha, $categoria, $estado);

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

    //Se obtiene lista de publicaciones que cumplen con las condiciones de busqueda
    $lista_productos = obtenerListaProductoWhereLimit($conexion, $condicion_where, $condicion_limit, $id_usuario_emprendedor);

    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($campo_buscar) || ($categoria != 0) || ($estado != 0) || !empty($fecha));

    //Genera las cards HTML para las publicaciones 
    $respuesta['card'] = listaCardProductosEmprendedor($conexion, $lista_productos, $busqueda_activa);


    //Se obtiene la cantidad actual de productos segun las condiciones de busqueda
    $cantidad_productos = count($lista_productos);

    //Se guarda la cantidad actual de productos
    $respuesta['cantidad_actual'] = $cantidad_productos;


    if ($cantidad_productos >= 1) {

        //Se obtiene la cantidad total de productos segun las condiciones de busqueda
        $cantTotalProducto  = cantTotalProductoWheUsuarioEmprendedor($conexion, $condicion_where, $id_usuario_emprendedor);

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantTotalProducto, $cant_registro, $pagina_actual, "nextPageProducto");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantTotalProducto / $cant_registro);

        //Se obtiene la cantidad de productos segun que se va a mostrar en la interfaz del usuario
        $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantTotalProducto, $pagina_actual, $cantidad_productos);

        //Se guarda en respuesta un mensaje indicando la cantidad de productos que se ve en la interfaz del usuario
        $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantTotalProducto} productos";

        //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
        $respuesta['pagina'] = "Pagina " . $pagina_actual  . ' de ' .  $totalPaginas;
    }
} catch (Exception $e) {

    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}

function listaCardProductosEmprendedor($conexion, $lista_productos, $busqueda_activa)
{
    $datos = '';
    global $url_base_archivos;
    global $calificacion_max_productos;

    $datos = '';
    if (count($lista_productos) > 0) {
        for ($i = 0; $i < count($lista_productos); $i++) {

            $listaImagenes = obtenerListaImgProducto($conexion, $lista_productos[$i]['id_publicacion_producto']);
            if (empty($listaImagenes)) {
                throw new Exception("Error:No se pudo obtener todas las imagenes de los productos");
            }

            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-10 mb-3 mt-2'>";
            $datos .= "<div class='card h-100'>";
            $datos .= "<div class='card-body'>";
            $datos .= "<div class='row'>";
            // Inicio de Carousel del producto
            $datos .= "<div class='col-12 col-sm-12 col-md-4 col-lg-4'>";
            $datos .= "<div id='carousel-{$i}' class='carousel slide'>";
            $datos .= "<div class='carousel-inner'>";
            $datos .= "<div class='carousel-item active'>";
            $datos .= "<a data-fancybox='gallery-{$i}' href='{$url_base_archivos}/uploads/{$lista_productos[$i]['id_usuario_emprendedor']}/publicaciones_productos/{$listaImagenes[0]['nombre_carpeta']}/{$listaImagenes[0]['nombre_archivo']}'>";
            $datos .= "<img class='galeria-img-publicacion d-block w-100' src='{$url_base_archivos}/uploads/{$lista_productos[$i]['id_usuario_emprendedor']}/publicaciones_productos/{$listaImagenes[0]['nombre_carpeta']}/{$listaImagenes[0]['nombre_archivo']}'>";
            $datos .= "</a>";
            $datos .= "</div>";

            for ($j = 1; $j < count($listaImagenes); $j++) {
                $datos .= "<div class='carousel-item'>";
                $datos .= "<a data-fancybox='gallery-{$i}' href='{$url_base_archivos}/uploads/{$lista_productos[$i]["id_usuario_emprendedor"]}/publicaciones_productos/{$listaImagenes[$j]["nombre_carpeta"]}/{$listaImagenes[$j]["nombre_archivo"]}'>";
                $datos .= "<img class='galeria-img-publicacion d-block w-100' src='{$url_base_archivos}/uploads/{$lista_productos[$i]["id_usuario_emprendedor"]}/publicaciones_productos/{$listaImagenes[$j]["nombre_carpeta"]}/{$listaImagenes[$j]["nombre_archivo"]}'>";
                $datos .= "</a>";
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
            $datos .= "</div>";
            //Fin del Carousel del productos
            $datos .= "<div class='col-12 col-sm-12 col-md-8 col-lg-8'>";
            $datos .= "<h5 class='card-title text-center'>{$lista_productos[$i]['nombre_producto']}</h5>";
            $datos .= "<div class='row'>";
            $datos .= "<div class='col-6 col-sm-6 col-md-6 col-lg-6'>";
            $fecha_publicacion = date('d/m/Y H:i:s', strtotime($lista_productos[$i]['fecha_publicacion']));
            $datos .= "<p class='card-text'><strong>Fecha de publicacion:</strong>{$fecha_publicacion}</p>";
            $datos .= "</div>";
            if ($lista_productos[$i]['fecha_modificación'] != null) {
                $fecha_modificacion = date('d/m/Y H:i:s', strtotime($lista_productos[$i]['fecha_modificación']));
                $datos .= "<div class='col-6 col-sm-6 col-md-6 col-lg-6'>";
                $datos .= "<p class='card-text'><strong>Ultima modificacion:</strong>{$fecha_modificacion}</p>";
                $datos .= "</div>";
            }
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
            if (strlen($lista_productos[$i]['descripcion']) <= 150) {
                $descripcion =  nl2br($lista_productos[$i]['descripcion']);
                $datos .= "<p class='card-text'><strong>Descripcion:</strong>{$descripcion}</p>";
            } else {
                $descripcion_resumen = nl2br(substr($lista_productos[$i]['descripcion'], 0, 150));
                $datos .= "<p id='text_resumen_{$i}' class='card-text'><strong>Descripcion:</strong>{$descripcion_resumen}<button class='btn btn-link' onclick='verMas(text_resumen_{$i},text_completo_{$i},true)' >... [leer más]</button></p>";
                $descripcion_completa = nl2br($lista_productos[$i]['descripcion']);
                $datos .= "<p id='text_completo_{$i}' class='card-text' style='display:none;'><strong>Descripcion:</strong>{$descripcion_completa}<button class='btn btn-link' onclick='verMas(text_resumen_{$i},text_completo_$i,false);'>... [leer menos]</button></p>";
            }
            $datos .= "</div>";


            $decimales_precio = ($lista_productos[$i]['precio'] == floor($lista_productos[$i]['precio'])) ? 0 : 2;
            $precio_producto = number_format($lista_productos[$i]['precio'], $decimales_precio,  ',', '.');
            $datos .= "<div class='col-6 col-sm-6 col-md-6 col-lg-6'>";
            $datos .= "<p class='card-text'><strong>Precio:</strong>$ {$precio_producto}</p>";
            $datos .= "</div>";

            $datos .= "<div class='col-6 col-sm-6 col-md-6 col-lg-6'>";
            $datos .= "<p class='card-text'><strong>Stock:</strong>{$lista_productos[$i]['stock']}</p>";
            $datos .= "</div>";
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
            $datos .= "<p class='card-text'><strong>Categoria:</strong>{$lista_productos[$i]['nombre_categoria']}</p>";
            $datos .= "</div>";
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
            $datos .= "<p class='card-text'><strong>Estado:</strong>{$lista_productos[$i]['estado']}</p>";
            $datos .= "</div>";

            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12 text-center'>";
            $datos .= "<p class='card-text m-0'><strong>Calificacion del producto</strong></p>";
            $datos .= "<div>";
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

            $datos .= "</div>";
            $datos .= "<div class='card-footer'>";
            $datos .= '<a class="btn btn-outline-success m-1" href="' . $url_base_archivos . '/paginas/detalles_producto/pagina_detalles_producto.php?id=' . $lista_productos[$i]['id_publicacion_producto'] . '&token=' . hash_hmac('sha1', $lista_productos[$i]['id_publicacion_producto'], KEY_TOKEN) . '">Vista general</a>';
            $datos .= '<a class="btn btn-outline-warning m-1" href="pagina_modificar_producto.php?id=' . $lista_productos[$i]['id_publicacion_producto'] . '&token=' . hash_hmac('sha1', $lista_productos[$i]['id_publicacion_producto'], KEY_TOKEN) . '">Modificar</a>';
            $preguntasGenerales = obtenerListaPreguntasRespuestaProducto($conexion, $lista_productos[$i]['id_publicacion_producto']);
            if (count($preguntasGenerales) > 0) {
                $datos .= '<button type="button" class="btn btn-outline-primary m-1" data-bs-dismiss="modal" data-bs-id_producto="' . $lista_productos[$i]['id_publicacion_producto'] . '" data-bs-toggle="modal" data-bs-target="#preguntasModal-' . $lista_productos[$i]['id_publicacion_producto'] . '">Preguntas</button>';
                $datos .= obtenerModalListaPreguntasRespuesta($preguntasGenerales, $lista_productos[$i]['id_publicacion_producto']);
            } else {
                $datos .= "<button type='button' class='btn btn-outline-danger m-1' data-bs-dismiss='modal' data-bs-id_producto='{$lista_productos[$i]['id_publicacion_producto']}'  data-bs-nombre_producto='{$lista_productos[$i]['nombre_producto']}' data-bs-toggle='modal' data-bs-target='#ModaleliminarProducto'>Eliminar</button>";
            }
            $datos .= "</div>";
            $datos .= "</div>";
            $datos .= "</div>";
        }
    } else {
        $datos .= '<div class="col-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">';
        if ($busqueda_activa) {
            $datos .= '<h3>Sin resultados</h3>';
        } else {
            $datos .= '<h3>La lista de productos esta vacia</h3>';

        }
        $datos .= '</div>';
    }


    return  $datos;
}


function obtenerModalListaPreguntasRespuesta($preguntas, $id)
{
    $datos = '';
    $datos .= '<div class="modal fade" id="preguntasModal-' . $id . '" tabindex="-1" aria-labelledby="preguntasModal-' . $id . 'Label" aria-hidden="true">';
    $datos .= '<div class="modal-dialog modal-dialog-scrollable modal-lg">';
    $datos .= '<div class="modal-content">';
    $datos .= '<div class="modal-header">';
    $datos .= '<h1 class="modal-title fs-5" id="preguntasModal-' . $id . 'Label">Lista de preguntas recibidas del producto</h1>';
    $datos .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
    $datos .= '</div>';
    $datos .= '<div class="modal-body">';
    $datos .= '<div id="alert_preguntas_respuesta"></div>';

    for ($i = 0; $i < count($preguntas); ++$i) {

        $datos .= "<div class='card mb-4'>";
        $datos .= "<div class='card-body'>";
        $datos .= "<p class='card-text text-break'><strong>Nombre de usuario:</strong>{$preguntas[$i]["nombre_usuario"]}</p>";
        $fecha_pregunta = date('d/m/Y H:i:s', strtotime($preguntas[$i]['fecha_pregunta']));
        $datos .= "<p class='card-text text-break'><strong>Pregunta:</strong>{$preguntas[$i]["pregunta"]}<span style='color: #8a8a8a;'>({$fecha_pregunta})</span></p>";

        if ($preguntas[$i]["respuesta"] != null && $preguntas[$i]["fecha_respuesta"]  != null) {
            $fecha_respuesta = date('d/m/Y H:i:s', strtotime($preguntas[$i]['fecha_respuesta']));
            $datos .= "<p class='card-text text-break'><strong>Respuesta:</strong>{$preguntas[$i]["respuesta"]}<span style='color: #8a8a8a;'>({$fecha_respuesta})</span></p>";
        }
        $datos .= "</div>";
        $datos .= "<div class='card-footer'>";
        $fecha = date('d/m/Y H:i:s', strtotime($preguntas[$i]['fecha_pregunta']));
        if ($preguntas[$i]["respuesta"] != null && $preguntas[$i]["fecha_respuesta"]  != null) {
            $datos .= "<button type='button' class='btn btn-outline-danger' data-bs-dismiss='modal' data-bs-toggle='modal'  data-bs-target='#eliminaModal'  data-bs-id_producto='{$preguntas[$i]["id_producto"]}' data-bs-id_pregunta='{$preguntas[$i]["id_pregunta_respuesta"]}' data-bs-fecha='{$fecha}' data-bs-respuesta='{$preguntas[$i]["respuesta"]}'> <i class='fa-solid fa-trash'></i> Eliminar Respuesta</button>";
        } else {
            $datos .= "<button type='button' class='btn btn-outline-success' data-bs-dismiss='modal' data-bs-toggle='modal' data-bs-target='#agregarModal' data-bs-fecha='{$fecha}' data-bs-nombre='{$preguntas[$i]["nombre_usuario"]}'data-bs-id_producto='{$preguntas[$i]["id_producto"]}' data-bs-id_pregunta='{$preguntas[$i]["id_pregunta_respuesta"]}' data-bs-pregunta='{$preguntas[$i]["pregunta"]}'><i class='fa-solid fa-circle-plus'></i> Agregar Respuesta</button>";
        }
        $datos .= "</div>";
        $datos .= "</div> ";
    }
    $datos .= '</div>';
    $datos .= '</div>';
    $datos .= '</div>';
    $datos .= '</div>';

    return  $datos;
}

//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
