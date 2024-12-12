<?php
//Archivos de configuracion y funciones necesarias
include("../config/consultas_bd/conexion_bd.php");
include("../config/consultas_bd/consultas_producto.php");
include("../config/consultas_bd/consultas_usuario.php");
include("../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../config/consultas_bd/consultas_usuario_admin.php");
include("../config/funciones/funciones_token.php");
include("../config/funciones/funciones_productos.php");
include("../config/funciones/funciones_generales.php");
include("../config/funciones/funciones_session.php");
include("../config/config_define.php");


//Inicializacion de variables obtenidas de la solicitud POST
$campo_buscar = isset($_POST['campo_buscar']) ? $_POST['campo_buscar'] : '';
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$cant_registro = isset($_POST['cant_registro']) ? (int)$_POST['cant_registro'] : 10;
$categoria = isset($_POST['categoria']) ? (int)$_POST['categoria'] : null;
$estado = isset($_POST['estado']) ? (int)$_POST['estado'] : null;
$calificacion = isset($_POST['select_calificacion']) ? $_POST['select_calificacion'] : 'todos';

$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';

try {


    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario administrador
    if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
        throw new Exception("Debe iniciar sesion para poder ver la lista de productos");
    }

    //Se obtiene los datos de sesion
    $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
    $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario administrador es valido
    if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
        throw new Exception("No puede ver la lista de productos por que no es usuario administrador valido");
    }

    //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where = obtenerCondicionWhereBuscadorProductoEmprendedorParaAdmin($campo_buscar, $fecha, $categoria, $estado, $calificacion);

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $cant_registro);

    //Se obtiene lista de publicaciones que cumplen con las condiciones de busqueda
    $lista_productos = obtenerListaTodosProductos($conexion, $condicion_where, $condicion_limit);

    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($campo_buscar) || ($categoria != 0) || ($estado != 0) || !empty($fecha) || ($calificacion != "todos"));


    //Se obtiene la cantidad actual de productos segun las condiciones de busqueda
    $cantidad_productos = count($lista_productos);



    //Genera las cards HTML para los productos 
    $respuesta['card_productos'] = cargarCardsProductosEmprendedores($conexion, $lista_productos, $busqueda_activa);

    if ($cantidad_productos >= 1) {

        //Se obtiene la cantidad total de productos segun las condiciones de busqueda
        $cantTotalProducto  = cantTotalTodosProductos($conexion, $condicion_where);

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantTotalProducto, $cant_registro, $pagina_actual, "nextPageProducto");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantTotalProducto / $cant_registro);

        //Se obtiene la cantidad de segun que se va a mostrar en la interfaz del usuario
        $resultadosInicioFin = obtenerInicioFinResultados($totalPaginas, $cantTotalProducto, $pagina_actual, $cantidad_productos);

        //Se guarda en respuesta un mensaje indicando la cantidad de segun que se ve en la interfaz del usuario
        $respuesta['registro'] = "Mostrando {$resultadosInicioFin['inicio']} - {$resultadosInicioFin['fin']} de {$cantTotalProducto} productos";

        //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
        $respuesta['pagina'] = "Pagina " . $pagina_actual  . ' de ' .  $totalPaginas;
    }
} catch (Exception $e) {

    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}

function cargarCardsProductosEmprendedores($conexion, $lista_productos, $busqueda_activa)
{
    $calificacionMaxima = 5;
    global $url_base;
    $datos = '';


    if (count($lista_productos) > 0) {
        for ($i = 0; $i < count($lista_productos); $i++) {

            $listaImagenes = obtenerListaImgProducto($conexion, $lista_productos[$i]['id_publicacion_producto']);
            if (empty($listaImagenes)) {
                throw new Exception("Error:No se pudo obtener todas las imagenes de los productos");
            }

            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-8 mb-3 mt-2'>";
            $datos .= "<div class='card h-100'>";
            $datos .= "<div class='card-header'>";
            $datos .= "<div class='d-flex align-items-center'>";

            if (is_null($lista_productos[$i]["foto_perfil_nombre"])) {
                $ruta_archivo = "../img/foto_perfil/foto_de_perfil_predeterminado.jpg";
            } else {
                $ruta_archivo = "../uploads/{$lista_productos[$i]["id_usuario_emprendedor"]}/foto_perfil/{$lista_productos[$i]["foto_perfil_nombre"]}";
            }

            $datos .= "<img class='mini_imagen_perfil' src='{$ruta_archivo}' alt='Foto de perfil'>";
            $datos .= "<div>";

            $datos .= "<h5 class='text-break mb-0'>{$lista_productos[$i]['nombre_emprendimiento']}</h5>";
            $fecha_publicacion = date('d/m/Y H:i:s', strtotime($lista_productos[$i]['fecha_publicacion']));
            $datos .= "<p class='mb-0 text-secondary'>{$fecha_publicacion}</p>";
            $datos .= "</div>";
            $datos .= "</div>";
            $datos .= "</div>";

            $datos .= "<div class='card-body'>";
            $datos .= "<div class='row'>";
            $datos .= "<div class='col-12 col-sm-12 col-md-4 col-lg-4'>";
            $datos .= "<div id='carouselProductos-{$lista_productos[$i]["id_publicacion_producto"]}' class='carousel slide'>";
            $datos .= "<div class='carousel-inner'>";
            $datos .= "<div class='carousel-item active'>";
            $datos .= '<a  data-fancybox="gallery-' . $lista_productos[$i]['id_publicacion_producto'] . '"   href="' . $url_base . '/uploads/' . $lista_productos[$i]['id_usuario_emprendedor'] . '/publicaciones_productos/' . $listaImagenes[0]['nombre_carpeta'] . '/' . $listaImagenes[0]['nombre_archivo'] . '">';
            $datos .= "<img src='{$url_base}/uploads/{$lista_productos[$i]['id_usuario_emprendedor']}/publicaciones_productos/{$listaImagenes[0]['nombre_carpeta']}/{$listaImagenes[0]['nombre_archivo']}' class='img-productos-admin d-block w-100 ' alt='imagen-0'>";
            $datos .= "</a>";

            $datos .= "</div>";
            for ($j = 1; $j < count($listaImagenes); $j++) {
                $datos .= "<div class='carousel-item'>";
                $datos .= '<a  data-fancybox="gallery-' . $lista_productos[$i]['id_publicacion_producto'] . '"   href="' . $url_base . '/uploads/' . $lista_productos[$i]['id_usuario_emprendedor'] . '/publicaciones_productos/' . $listaImagenes[$j]['nombre_carpeta'] . '/' . $listaImagenes[$j]['nombre_archivo'] . '">';

                $datos .= "<img src='{$url_base}/uploads/{$lista_productos[$i]["id_usuario_emprendedor"]}/publicaciones_productos/{$listaImagenes[$j]["nombre_carpeta"]}/{$listaImagenes[$j]["nombre_archivo"]}' class='img-productos-admin d-block w-100' alt='imagen-{$i}'>";
                $datos .= "</a>";
                $datos .= "</div>";
            }
            $datos .= "</div>";

            if (count($listaImagenes) > 1) {
                $datos .= "<button class='carousel-control-prev-publicaciones-producto' type='button' data-bs-target='#carouselProductos-{$lista_productos[$i]["id_publicacion_producto"]}' data-bs-slide='prev'>";
                $datos .= "<i class='fa-solid fa-chevron-left'></i>";
                $datos .= "</button>";
                $datos .= "<button class='carousel-control-next-publicaciones-producto' type='button' data-bs-target='#carouselProductos-{$lista_productos[$i]["id_publicacion_producto"]}' data-bs-slide='next'>";
                $datos .= "<i class='fa-solid fa-chevron-right'></i>";
                $datos .= "</button>";
            }

            $datos .= "</div>";
            $datos .= "</div>";
            $datos .= "<div class='col-12 col-sm-12 col-md-8 col-lg-8'>";
            $datos .= "<h5 class='card-title text-center'>{$lista_productos[$i]['nombre_producto']}</h5>";
            $datos .= "<div class='row'>";


            $decimales_precio = ($lista_productos[$i]['precio'] == floor($lista_productos[$i]['precio'])) ? 0 : 2;
            $precio_producto = number_format($lista_productos[$i]['precio'], $decimales_precio,  ',', '.');
            $datos .= "<div class='col-6 col-sm-6 col-md-6 col-lg-6'>";
            $datos .= "<p class='card-text'><strong>Precio:</strong>$ {$precio_producto}</p>";
            $datos .= "</div>";

            $datos .= "<div class='col-6 col-sm-6 col-md-6 col-lg-6'>";
            $datos .= "<p class='card-text'><strong>Stock:</strong>{$lista_productos[$i]['stock']}</p>";
            $datos .= "</div>";
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-6'>";
            $datos .= "<p class='card-text'><strong>Categoria:</strong>{$lista_productos[$i]['nombre_categoria']}</p>";
            $datos .= "</div>";


            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-6'>";
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
                $calificacionRestante = $calificacionMaxima - $lista_productos[$i]['calificacion'];
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


            $token_emprendedor = hash_hmac('sha1', $lista_productos[$i]["id_usuario_emprendedor"], KEY_TOKEN);
            $datos .= "<a class='btn btn-outline-success m-1' target='_blank' href='{$url_base}/usuarios/usuario_emprendedor/seccion/perfil/pagina_perfil.php?id={$lista_productos[$i]["id_usuario_emprendedor"]}&token={$token_emprendedor}'>Perfil del emprendedor</a>";

            if ($lista_productos[$i]['id_estado_producto'] != 2) {

                $token_producto = hash_hmac('sha1', $lista_productos[$i]['id_publicacion_producto'], KEY_TOKEN);
                $datos .= "<a class='btn btn-outline-success m-1' target='_blank' href='{$url_base}/paginas/detalles_producto/pagina_detalles_producto.php?id={$lista_productos[$i]['id_publicacion_producto']}&token={$token_producto}'>Vista general</a>";

            }
            $token_usuario = hash_hmac('sha1', $lista_productos[$i]['id_usuario'], KEY_TOKEN);
            $datos .= "<a class='btn btn-outline-success m-1' target='_blank' href='{$url_base}/admin/paginas/detalles_usuarios/pagina_detalles_usuario.php?id={$lista_productos[$i]['id_usuario']}&token={$token_usuario}'>Datos del usuario</a>";

            $token_producto_admin = hash_hmac('sha1', $lista_productos[$i]['id_publicacion_producto'], KEY_TOKEN);
            $datos .= "<a class='btn btn-outline-success m-1' target='_blank' href='{$url_base}/admin/paginas/detalles_usuarios/producto/pagina_detalles_producto.php?id={$lista_productos[$i]['id_publicacion_producto']}&token={$token_producto_admin}'>Vista administrador</a>";

            $preguntasGenerales = obtenerListaPreguntasRespuestaProducto($conexion, $lista_productos[$i]['id_publicacion_producto']);
            if (count($preguntasGenerales) > 0) {
                $datos .= "<button type='button' class='btn btn-outline-success m-1' data-bs-dismiss='modal' data-bs-id_producto='{$lista_productos[$i]['id_publicacion_producto']}' data-bs-toggle='modal' data-bs-target='#preguntasModal-{$lista_productos[$i]['id_publicacion_producto']}'>Preguntas y respuestas</button>";
                $datos .= obtenerModalListaPreguntasRespuestaProducto($preguntasGenerales, $lista_productos[$i]['id_publicacion_producto']);
            }

            $datos .= "</div>";

            $datos .= "</div>";

            $datos .= "</div>";
            $datos .= "</div>";
        }
    } else {
        $datos .= '<div class="col-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">';
        if ($busqueda_activa) {
            $datos .= '<h2>Sin resultados</h2>';
        } else {
            $datos .= '<h2>No hay productos registrados</h2>';
        }
        $datos .= '</div>';
    }

    return $datos;
}


function obtenerModalListaPreguntasRespuestaProducto($preguntas, $id)
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
