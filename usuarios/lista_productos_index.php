<?php
//Archivos de configuracion y funciones necesarias
include("../config/consultas_bd/conexion_bd.php");
include("../config/consultas_bd/consultas_producto.php");
include("../config/consultas_bd/consultas_usuario.php");
include("../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../config/consultas_bd/consultas_seguimiento.php");
include("../config/funciones/funciones_token.php");
include("../config/funciones/funciones_generales.php");
include("../config/funciones/funciones_session.php");
include("../config/config_define.php");


// Se obtiene la pagina actual de productos
$pagina_actual = isset($_GET['pagina_producto']) ? $_GET['pagina_producto'] : 1;

// Limite de productos por pagina
$limite_publicacion_producto = 8;

$respuesta= [];

try {
    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario
    if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
        throw new Exception("Inicia sesion para poder ver la informacion de producto");
    }

    //Se obtiene los datos de sesion
    $id_usuario = $_SESSION["id_usuario"];
    $tipo_usuario = $_SESSION["tipo_usuario"];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario es valido
    if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
        throw new Exception("No se puede ver los datos de los productos debido a que su cuenta no esta activa o esta baneado");
    }

    //Valida que el campo pagina actual tengo un valor numerico
    if (!is_numeric($pagina_actual)) {
        throw new Exception("La pagina de publicacion debe ser un numero");
    }


    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y el limite de publicaciones de productos
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_publicacion_producto);

    //Se obtiene lista de los ultimos productos disponibles 
    $lista_productos = obtenerProductosDeLosEmprendedoresQueSiSegue($conexion, $id_usuario, $condicion_limit);

    //Verifica si es necesario cargar mas elementos
    $cantidad_productos = count($lista_productos);
    $cargar_mas = cargarMasElementos($cantidad_productos, $limite_publicacion_producto);
    $respuesta["cargar_mas_publicaciones_producto"] = $cargar_mas;

    //Genera las cards HTML para las publicaciones de productos y añadirlas a la respuesta
    $respuesta['cards'] = cargarListaCardProductosIndex($conexion, $lista_productos, $pagina_actual, $limite_publicacion_producto, $cargar_mas);
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}

function cargarListaCardProductosIndex($conexion, $lista_productos, $pagina_actual, $limite_producto, $cargar_mas)
{
    //Calificacion maxima para un producto
    global $calificacion_max_productos;
    
    //URL base del sitio
    global $url_base;

    $token = "";
    $mensaje = "";
    $datos =  "";
    $cant_producto = count($lista_productos);

 
    //Verifica si hay productos en la lista
    if ($cant_producto > 0) {
        //Si es la primera pagina se agrega un titulo para la seccion de productos 
        if ($pagina_actual == 1) {
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12 mb-2'>";
                $datos .= "<h4 class='mb-0 text-center'>Ultimos productos publicados</h4>";
            $datos .= "</div>";
        }

        for ($i = 0; $i < $cant_producto; $i++) {
            //Se obtiene las imagenes que tiene el producto
            $listaImagenes = obtenerListaImgProducto($conexion, $lista_productos[$i]['id_publicacion_producto']);
            if (empty($listaImagenes)) {
                throw new Exception("Error:No se pudo obtener todas las imagenes de los productos");
            }

            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-10 mb-3 mt-2'>";

                $datos .= "<div class='card h-100'>";  //Card Inicio

                    //El header de la card contiene la imagen del perfil del emprendedor, el nombre del empredimiento y la fecha de publicacion del producto.
                    
                    //Card Header Inicio
                    $datos .= "<div class='card-header'>"; 
                        $datos .= "<div class='d-flex align-items-center'>";
                            //Verifica si el emprendedor tiene una foto de perfil. Si no hay foto de perfil, se usa una imagen predeterminada
                            if (is_null($lista_productos[$i]["foto_perfil_nombre"])) {
                                $ruta_archivo = "{$url_base}/img/foto_perfil/foto_de_perfil_predeterminado.jpg";
                            } else {
                                $ruta_archivo = "{$url_base}/uploads/{$lista_productos[$i]["id_usuario_emprendedor"]}/foto_perfil/{$lista_productos[$i]["foto_perfil_nombre"]}";
                            }
                            $datos .= "<img class='mini_imagen_perfil' src='{$ruta_archivo}' alt='Foto de perfil'>";
                            $datos .= "<div>";
                                $token = hash_hmac('sha1', $lista_productos[$i]["id_usuario_emprendedor"], KEY_TOKEN);
                                $datos .= "<h5 class='text-break mb-0'><a class='link-dark link-offset-2 link-underline link-underline-opacity-0 link-opacity-50-hover' href='{$url_base}/usuarios/usuario_emprendedor/seccion/perfil/pagina_perfil.php?id={$lista_productos[$i]["id_usuario_emprendedor"]}&&token={$token}'>{$lista_productos[$i]['nombre_emprendimiento']}</a></h5>";
                                $fecha_publicacion = date('d/m/Y H:i:s', strtotime($lista_productos[$i]['fecha_publicacion']));
                                $datos .= "<p class='mb-0 text-secondary'>{$fecha_publicacion}</p>";
                            $datos .= "</div>";
                        $datos .= "</div>";
                    $datos .= "</div>";
                    //Card Header Fin

                    // El body de la card incluye informacion y un carrusel de imagenes del producto.-->
                   
                    //Card Body Inicio
                    $datos .= "<div class='card-body'>"; 
                        $datos .= "<div class='row'>";
                            //Imagenes del producto inicio
                            $datos .= "<div class='col-12 col-sm-12 col-md-4 col-lg-4'>";
                                $datos .= "<div id='carouselProductos-{$lista_productos[$i]["id_publicacion_producto"]}' class='carousel slide'>";
                                    $datos .= "<div class='carousel-inner'>";
                                        $datos .= "<div class='carousel-item active'>";
                                            $datos .= "<img src='{$url_base}/uploads/{$lista_productos[$i]['id_usuario_emprendedor']}/publicaciones_productos/{$listaImagenes[0]['nombre_carpeta']}/{$listaImagenes[0]['nombre_archivo']}' class='galeria-img-publicacion-producto d-block w-100 card-img-top' alt='imagen-0'>";
                                        $datos .= "</div>";
                                        for ($j = 1; $j < count($listaImagenes); $j++) {
                                            $datos .= "<div class='carousel-item'>";
                                                $datos .= "<img src='{$url_base}/uploads/{$lista_productos[$i]["id_usuario_emprendedor"]}/publicaciones_productos/{$listaImagenes[$j]["nombre_carpeta"]}/{$listaImagenes[$j]["nombre_archivo"]}' class='galeria-img-publicacion-producto d-block w-100 card-img-top' alt='imagen-{$i}'>";
                                            $datos .= "</div>";
                                        }
                                    $datos .= "</div>";

                                    //Botones de control del carrusel para nevegar entre las imagenes del producto. Se muestran solo si hay mas de una imagen.
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
                            //Imagenes del producto Fin

                            //Datos del producto Inicio
                            $token = hash_hmac('sha1', $lista_productos[$i]['id_publicacion_producto'], KEY_TOKEN);
                            $datos .= "<div class='col-12 col-sm-12 col-md-8 col-lg-8'>";
                                $datos .= "<h5 class='card-title text-center'>";
                                    $datos .= "<a class='link-dark link-offset-2 link-underline link-underline-opacity-0 link-opacity-50-hover' href='{$url_base}/paginas/detalles_producto/pagina_detalles_producto.php?id={$lista_productos[$i]['id_publicacion_producto']}&token={$token}'>{$lista_productos[$i]['nombre_producto']}</a>";
                                $datos .= "</h5>";
                                $datos .= "<div class='row'>";
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
                $datos .= "</div>";
                //Card Fin
            $datos .= "</div>";
        }
    }


    //Verifica si no hay mas productos disponibles para cargar
    if (!$cargar_mas) {
        if ($cant_producto >= 0 && $cant_producto < $limite_producto) {
            $mensaje = "No hay mas publicaciones disponibles en este momento";
        }
    }
    //Verifica si no hay productos disponibles para cargar
    if ($pagina_actual == 1 && $cant_producto == 0) {
        $mensaje = "No hay publicaciones disponibles por el momento";
    }

    if (!empty($mensaje)) {
        $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12 no-publicaciones'>";
            $datos .= "<p class='text-center'>{$mensaje}</p>";
        $datos .= "</div>";
    }

    return $datos;
}


//Devolver la respuesta en formato JSON
echo json_encode($respuesta);