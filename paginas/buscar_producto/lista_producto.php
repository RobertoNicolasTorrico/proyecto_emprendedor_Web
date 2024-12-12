<?php

//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_token.php");
include("../../config/funciones/funciones_session.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_productos.php");
include("../../config/config_define.php");


//Inicializacion de variables obtenidas de la solicitud POST
$campo_buscar_producto = isset($_POST['campo_buscar_producto']) ? $_POST['campo_buscar_producto'] : '';
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$num_ordenamiento = isset($_POST['num_ordenamiento']) ? (int)$_POST['num_ordenamiento'] : 1;
$precio_minimo = isset($_POST['precio_minimo']) ? (float)$_POST['precio_minimo'] : null;
$precio_maximo = isset($_POST['precio_maximo']) ? (float)$_POST['precio_maximo'] : null;
$categoria = isset($_POST['num_categoria']) ? (int)$_POST['num_categoria'] : '';
$calificacion = isset($_POST['calificacion']) ? $_POST['calificacion'] : null;


// Limite de productos por pagina
$limite_producto = 6;


$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';



try {


    //Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where =  obtenerCondicionWhereBuscadorProducto($campo_buscar_producto, $categoria, $calificacion, $precio_minimo, $precio_maximo);

    //Se obtiene la condicion ASC y DESC para la consulta en funcion del numero de ordenamiento 
    $condicion_ASC_DESC = obtenerCondicionASCDESCFiltroProducto($num_ordenamiento);

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_producto);

    //Se obtiene lista de publicaciones que cumplen con las condiciones de busqueda
    $lista_productos = obtenerListaBusquedaProductoWhereASCDESCLimit($conexion, $condicion_where, $condicion_ASC_DESC, $condicion_limit);

    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($campo_buscar_producto) || $calificacion !== "todos_calificacion" ||  !empty($categoria) || (!empty($precio_minimo) && !empty($precio_maximo)));


    //Se obtiene la cantidad de productos disponibles
    $cantidad_productos = count($lista_productos);

    //Se obtiene la cantidad total de productos segun las condiciones de busqueda
    $cantidad_total_productos  = cantTotalListaBusquedaProductoWhere($conexion, $condicion_where);


    //Genera las cards HTML para las productos 
    $respuesta["cards"] = obtenerListaCardsBuscadorProductos($conexion, $lista_productos, $cantidad_total_productos, $busqueda_activa);

    if ($cantidad_productos >= 1) {

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantidad_total_productos, $limite_producto, $pagina_actual,"nextPageProducto");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantidad_total_productos / $limite_producto);

        //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
        $respuesta['pagina'] = "Pagina " . $pagina_actual  . " de " .  $totalPaginas ;

    }
} catch (Exception $e) {

    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}

function obtenerListaCardsBuscadorProductos($conexion, $lista_productos, $cantidad_total_producto, $busqueda_activa)
{

    $id_usuario = "";
    $tipo_usuario = "";
    $datos = '';


   //Calificacion maxima para un productos
   global $calificacion_max_productos;
    
   //URL base de archivos del sitio
   global $url_base_archivos;

   //URL de la imagen de emprendedores en que caso que no tengan imagen del perfil
   global $url_foto_perfil_predeterminada;


    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario
    if (verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION["id_usuario"];
        $tipo_usuario = $_SESSION["tipo_usuario"];

    }

    if (count($lista_productos) > 0) {
        if ($busqueda_activa) {
            $datos .= '<div class="col-12 col-sm-12 col-md-12 col-lg-12 mt-2">';
            $datos .= '<h4 class="mb-0">Resultados:' . $cantidad_total_producto . '</h4>';
            $datos .= '</div>';
        }
        for ($i = 0; $i < count($lista_productos); $i++) {
       
            $listaImagenes = obtenerListaImgProducto($conexion, $lista_productos[$i]['id_publicacion_producto']);
            if (empty($listaImagenes)) {
                throw new Exception("Error:No se pudo obtener todas las imagenes de los productos");
            }

            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-10 mb-3 mt-2'>";

                //Card Inicio
                $datos .= "<div class='card h-100'>";

                    //Card Header Inicio
                    $datos .= "<div class='card-header'>";

                        //Div Inicio que contiene la imagen del perfil del emprendedor, nombre del emprendimiento y la fecha de publicacion
                        $datos .= "<div class='d-flex align-items-center'>";
                            //Se verifica que el emprendedor tiene una imagen de perfil en caso que no se agrega una imagen predeterminada del sistema
                            if (is_null($lista_productos[$i]["foto_perfil_nombre"])) {
                                $ruta_archivo = $url_foto_perfil_predeterminada;
                            } else {
                                $ruta_archivo = "{$url_base_archivos}/uploads/{$lista_productos[$i]["id_usuario_emprendedor"]}/foto_perfil/{$lista_productos[$i]["foto_perfil_nombre"]}";
                            } 

                            $datos .= "<img class='mini_imagen_perfil' src='{$ruta_archivo}' alt='Foto de perfil'>";
                            $datos .= "<div>";
                                $token = hash_hmac('sha1',$lista_productos[$i]["id_usuario_emprendedor"], KEY_TOKEN);
                                 $datos .= "<h5 class='text-break mb-0'><a class='link-dark link-offset-2 link-underline link-underline-opacity-0 link-opacity-50-hover' href='{$url_base_archivos}/usuarios/usuario_emprendedor/seccion/perfil/pagina_perfil.php?id={$lista_productos[$i]["id_usuario_emprendedor"]}&token={$token}'>{$lista_productos[$i]['nombre_emprendimiento']}</a></h5>";                                           
                                $fecha_publicacion=date('d/m/Y H:i:s', strtotime($lista_productos[$i]['fecha_publicacion']));
                                $datos .= "<p class='mb-0 text-secondary'>{$fecha_publicacion}</p>";
                            $datos .= "</div>";
                        $datos .= "</div>";
                    $datos .= "</div>";
                    //Card Header Fin

                    //Card Body Inicio
                    $datos .= "<div class='card-body'>";
                        $datos .= "<div class='row'>";

                            //Imagenes del producto inicio
                            $datos .= "<div class='col-12 col-sm-12 col-md-4 col-lg-4'>";
                                $datos .= "<div id='carousel-{$i}' class='carousel slide'>";
                                    $datos .= "<div class='carousel-inner'>";
                                        $datos .= "<div class='carousel-item active'>";
                                            $datos .= "<img src='{$url_base_archivos}/uploads/{$lista_productos[$i]['id_usuario_emprendedor']}/publicaciones_productos/{$listaImagenes[0]['nombre_carpeta']}/{$listaImagenes[0]['nombre_archivo']}' class='galeria-img-publicacion-producto d-block w-100 card-img-top' alt='imagen-0'>";
                                        $datos .= "</div>";
                                        for ($j = 1; $j < count($listaImagenes); $j++) {
                                            $datos .= "<div class='carousel-item'>";
                                            $datos .= "<img src='{$url_base_archivos}/uploads/{$lista_productos[$i]["id_usuario_emprendedor"]}/publicaciones_productos/{$listaImagenes[$j]["nombre_carpeta"]}/{$listaImagenes[$j]["nombre_archivo"]}' class='galeria-img-publicacion-producto d-block w-100 card-img-top' alt='imagen-{$i}'>";
                                            $datos .= "</div>";
                                        }
                                    $datos .= "</div>";

                                    //Botones de control del carrusel para nevegar entre las imagenes del producto. Se muestran solo si hay mas de una imagen.
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
                            //Imagenes del producto Fin

                            //Datos del producto Inicio
                            $token=hash_hmac('sha1', $lista_productos[$i]['id_publicacion_producto'], KEY_TOKEN);
                            $datos .= "<div class='col-12 col-sm-12 col-md-8 col-lg-8'>";
                                $datos .= "<h5 class='card-title text-center'>";
                                    $datos .= "<a class='link-dark link-offset-2 link-underline link-underline-opacity-0 link-opacity-50-hover' href='../detalles_producto/pagina_detalles_producto.php?id={$lista_productos[$i]['id_publicacion_producto']}&token={$token}'>{$lista_productos[$i]['nombre_producto']}</a>";
                                $datos .= "</h5>";
                                $datos .= "<div class='row'>";
                            
            
                                    $decimales_precio = ($lista_productos[$i]['precio'] == floor($lista_productos[$i]['precio'])) ? 0 : 2;
                                    $precio_producto=number_format($lista_productos[$i]['precio'], $decimales_precio,  ',', '.');
                                    $datos .= "<div class='col-6 col-sm-6 col-md-6 col-lg-6'>";
                                        $datos .= "<p class='card-text'><strong>Precio:</strong>$ {$precio_producto}</p>";
                                     $datos .= "</div>";
                              
                                    $datos .= "<div class='col-6 col-sm-6 col-md-6 col-lg-6'>";
                                        $datos .= "<p class='card-text'><strong>Stock:</strong>{$lista_productos[$i]['stock']}</p>";
                                    $datos .= "</div>";
                                    $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
                                        $datos .= "<p class='card-text'><strong>Categoria:</strong>{$lista_productos[$i]['nombre_categoria']}</p>";
                                    $datos .= "</div>";



                                      //Verifica si el producto tiene una calificacion
                                    $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12 text-center'>";
                                      $datos .= "<p class='card-text m-0'><strong>Calificacion del producto</strong></p>";
                                      $datos .= "<div>";
                                          if (is_null($lista_productos[$i]['calificacion'])) {
                                              $datos .= "<p class='card-text'>Este producto aun no tiene una calificacion</p>";
                                          } else {
                                              //Agrega estrellas activas para la calificacion actual del producto.
                                              for ($k = 0; $k < $lista_productos[$i]['calificacion']; $k++) {
                                                  $datos .= "<i class='fas fa-star star-calificacion-activo'></i>";
                                              }

                                              //Calcula cúantas estrellas faltan para llegar a la calificacion máxima posible. -->
                                              $calificacionRestante = $calificacion_max_productos - $lista_productos[$i]['calificacion'];
                                              if ($calificacionRestante > 0) {

                                                  //Agregar estrellas vacias para las calificaciones restantes. -->
                                                  for ($l = 0; $l < $calificacionRestante; $l++) {
                                                      $datos .= "<i class='fas fa-star star-calificacion'></i>";
                                                  }
                                              }
                                          }
                                      $datos .= "</div>";
                                    $datos .= "</div>";
                                
                                $datos .= "</div>";
                            $datos .= "</div>";
                            //Datos del producto Fin

                        $datos .= "</div>";
                    
                    $datos .= "</div>";
                    //Card Body Fin

                    //Verifica si el emprendedor publico el producto
                    if ($id_usuario ==$lista_productos[$i]['id_usuario'] && $tipo_usuario == 2 ) {
                        $datos .= "<div class='card-footer text-body-secondary'>";
                        $datos .= "Es uno de tus productos";
                        $datos .= "</div>";

                    }



                $datos .= "</div>";
                //Card Fin

            $datos .= "</div>";
        }
    } else {

        if ($busqueda_activa) {
            $mensaje = 'Sin resultados';
        } else {
            $mensaje = 'No hay productos disponibles por el momento';
        }

        $datos .= '<div class="col-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">';
        $datos .= "<h2 class='text-center'>{$mensaje}</h2>";
        $datos .= '</div>';
    }

    return $datos;
}




//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
