<?php
//Archivos de configuracion y funciones necesarias
include("config/consultas_bd/conexion_bd.php");
include("config/consultas_bd/consultas_usuario.php");
include("config/consultas_bd/consultas_producto.php");
include("config/consultas_bd/consultas_publicaciones.php");
include("config/consultas_bd/consultas_usuario_emprendedor.php");
include("config/funciones/funciones_session.php");
include("config/funciones/funciones_token.php");
include("config/funciones/funciones_generales.php");
include("config/config_define.php");

$mensaje_error = "";
$lista_productos = array();
$lista_emprendedores = array();

//calificacion maximma para emprendedores y productos 
global $calificacion_max_productos;
global $calificacion_max_emprendedores;

try {
    //Establecer la sesion
    session_start();

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    // Verificar datos de sesión del usuario y redirigidir a la pagina de inicio usuario si la sesion es valida
    verificarDatosSessionUsuario($conexion);

    // Limitar la cantidad de productos y emprendedores a mostrar
    $condicion_limit_producto = "LIMIT 10";
    $condicion_limit_emprendedores = "LIMIT 10";

    // Se obtiene unalista de productos disponibles para la página de inicio
    $lista_productos = obtenerListaProductosDisponiblesParaIndex($conexion, $condicion_limit_producto);

    // Se obtiene una lista de emprendedores activos para la página de inicio
    $lista_emprendedores = obtenerListaEmprendedoresActivosParaIndex($conexion, $condicion_limit_emprendedores);
} catch (Exception $e) {
    // Capturar cualquier excepción y guardar el mensaje de error
    $mensaje_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--Titulo de la página-->
    <title>Proyecto emprendedor Inicio</title>

    <!--Enlace al archivo de estilos propios del proyecto-->
    <link href="config/css/estilos.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Swiper-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!--Enlace al archivo de estilos de Bootstrap-->
    <link href="lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de FontAwesome para iconos-->
    <link href="lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Fancybox-->
    <link href="lib/fancyapps-5.0.33/dist/fancybox/fancybox.css" rel="stylesheet" />

    <!--Enlace al archivo de estilos para Leaflet para mapas-->
    <link href="lib/leaflet-1.9.4/leaflet.css" rel="stylesheet" />


</head>


<body>
    <!--Incluye el archivo de la barra de navegación general.-->
    <?php include($url_navbar_general); ?>

    <!--Separa el contenido principal de la pagina del pie de pagina.-->
    <main>
        <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
        <div class="container">

            <?php if (empty($mensaje_error)) {    ?>

                <!--Muestra un mensaje de bienvenida y una breve descripción de la página si no hay mensajes de error.-->
                <div class="mb-4">
                    <h1 class="text-center">Bienvenidos</h1>
                    <p class="fw-light text-center fs-5">En esta pagina los emprendedores tienen la oportunidad de llevar sus productos al siguiente nivel. Aquí podran:</p>

                    <ul class="list-group">
                        <li class="list-group-item"><strong>Publicar Productos e Información:</strong> Los emprendedores pueden compartir sus productos y cualquier información relevante para que más personas conozcan lo que ofrecen.</li>
                        <li class="list-group-item"><strong>Responder Preguntas:</strong> Los emprendedores pueden responder las preguntas recibidas de sus productos.</li>
                        <li class="list-group-item"><strong>Conectar con Usuarios:</strong> Los usuarios pueden seguir a sus emprendedores favoritos y mantenerse al día con sus últimas publicaciones.</li>
                        <li class="list-group-item"><strong>Hacer Preguntas:</strong> Permite que los usuarios hagan preguntas sobre los productos que les interesen.</li>
                        <li class="list-group-item"><strong>Recibir Notificaciones:</strong> Todos los usuarios reciben notificaciones cuando sus preguntas son respondidas ademas que los emprendedores reciben notificaciones sobre nuevos seguidores o cuando uno de sus productos reciba una nueva pregunta.</li>
                    </ul>
                </div>





                <!--Contenedor con un encabezado que muetra las últimas publicaciones de diferentes emprendedores.-->
                <div>
                    <h5>Ultimas publicaciones de diferentes emprendedores</h5>
                    <div class="overflow-y-auto mb-5 border border-secondary-subtle" style="max-height: 450px;" id="div_nav_publicacion">
                        <div class="row align-items-center justify-content-center" id="cards_publicaciones" style="margin-right:0;"></div>
                    </div>
                </div>

                <!--Define una navegación por pestañas para alternar entre la vista de productos y la vista de emprendedores.-->
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="nav_producto-tab" data-bs-toggle="tab" data-bs-target="#div_nav_producto" type="button" role="tab" aria-controls="div_nav_producto" aria-selected="true">Productos</button>
                        <button class="nav-link" id="nav_emprendedor_tab" data-bs-toggle="tab" data-bs-target="#div_nav_emprendedor" type="button" role="tab" aria-controls="div_nav_emprendedor" aria-selected="false">Emprendedores</button>
                    </div>
                </nav>

                <!--Contenedor para el contenido de las pestañas.La pestaña de productos esta activa por defecto.-->
                <div class="tab-content" id="nav-tabContent">


                    <!--Contenedor de los productos.-->
                    <div class="tab-pane fade overflow-auto show active" id="div_nav_producto" role="tabpanel" aria-labelledby="nav_producto-tab" tabindex="0">
                        <!--Muestra productos de los emprendedores si la lista de productos tiene 1 o mas elementos. Utiliza Swiper para crear un Carrusel de productos-->
                        <?php if (count($lista_productos) >= 1) { ?>
                            <h5>Algunos productos de los emprendedores registrados</h5>
                            <div class="swiper swiperProductos">
                                <div class="swiper-wrapper">

                                    <!--Cada producto se representa como una card dentro de un swiper-slide.-->
                                    <?php for ($i = 0; $i < count($lista_productos); $i++) {    ?>
                                        <?php $listaImagenes = obtenerListaImgProducto($conexion, $lista_productos[$i]['id_publicacion_producto']); ?>

                                        <div class="swiper-slide">
                                            <div class="card h-100" style="box-shadow: 6px 6px 10px 1px rgba(22, 22, 26, 0.18);">
                                                <!--El header de la card contiene la imagen del perfil del emprendedor, el nombre del empredimiento y la fecha de publicacion del producto.-->
                                                <div class='card-header'>
                                                    <div class='d-flex align-items-center'>

                                                        <!--Verifica si el emprendedor tiene una foto de perfil. Si no hay foto de perfil, se usa una imagen predeterminada-->
                                                        <?php if (is_null($lista_productos[$i]["foto_perfil_nombre"])) {
                                                            $ruta_archivo = $url_foto_perfil_predeterminada;
                                                        } else {
                                                            $ruta_archivo = "{$url_base_archivos}/uploads/{$lista_productos[$i]["id_usuario_emprendedor"]}/foto_perfil/{$lista_productos[$i]["foto_perfil_nombre"]}";
                                                        } ?>

                                                        <img class='mini_imagen_perfil' src=<?php echo ($ruta_archivo); ?> alt='Foto de perfil'>
                                                        <div>
                                                            <?php $token = hash_hmac('sha1', $lista_productos[$i]["id_usuario_emprendedor"], KEY_TOKEN);  ?>

                                                            <h5 class='text-break mb-0'>
                                                                <a class='link-dark link-offset-2 link-underline link-underline-opacity-0 link-opacity-50-hover' href='<?php echo ($url_base) ?>/usuarios/usuario_emprendedor/seccion/perfil/pagina_perfil.php?id=<?php echo ($lista_productos[$i]["id_usuario_emprendedor"]) ?>&token=<?php echo ($token) ?>'><?php echo ($lista_productos[$i]['nombre_emprendimiento']) ?></a>
                                                            </h5>
                                                            <?php $fecha_publicacion = date('d/m/Y H:m:s', strtotime($lista_productos[$i]['fecha_publicacion'])); ?>
                                                            <p class='mb-0 text-secondary'><?php echo ($fecha_publicacion) ?></p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!--El body de la card incluye informacion y un carrusel de imagenes del producto.-->
                                                <div class="card-body">

                                                    <!--Imagenes del producto.-->
                                                    <div id="carousel-<?php echo ($i) ?>" class="carousel slide">
                                                        <div class="carousel-inner">
                                                            <div class="carousel-item active">
                                                                <img src="<?php echo ($url_base_archivos) ?>/uploads/<?php echo ($lista_productos[$i]['id_usuario_emprendedor']) ?>/publicaciones_productos/<?php echo ($listaImagenes[0]['nombre_carpeta']) ?>/<?php echo ($listaImagenes[0]['nombre_archivo']) ?>" class="galeria-img-publicacion-producto d-block w-100 card-img-top" alt="imagen-0">
                                                            </div>
                                                            <?php for ($j = 1; $j < count($listaImagenes); $j++) {  ?>
                                                                <div class="carousel-item ">
                                                                    <img src="<?php echo ($url_base_archivos) ?>/uploads/<?php echo ($lista_productos[$i]['id_usuario_emprendedor']) ?>/publicaciones_productos/<?php echo ($listaImagenes[$j]['nombre_carpeta']) ?>/<?php echo ($listaImagenes[$j]['nombre_archivo']) ?>" class="galeria-img-publicacion-producto d-block w-100 card-img-top" alt="imagen-<?php echo ($i) ?>">
                                                                </div>
                                                            <?php  } ?>
                                                        </div>

                                                        <!--Botones de control del carrusel para nevegar entre las imagenes del producto. Se muestran solo si hay mas de una imagen.-->
                                                        <?php if (count($listaImagenes) > 1) { ?>
                                                            <button class="carousel-control-prev-publicaciones-producto" type="button" data-bs-target="#carousel-<?php echo ($i) ?>" data-bs-slide="prev">
                                                                <i class="fa-solid fa-chevron-left"></i>
                                                            </button>
                                                            <button class="carousel-control-next-publicaciones-producto" type="button" data-bs-target="#carousel-<?php echo ($i) ?>" data-bs-slide="next">
                                                                <i class="fa-solid fa-chevron-right"></i>
                                                            </button>
                                                        <?php } ?>
                                                    </div>

                                                    <?php $id_producto = $lista_productos[$i]['id_publicacion_producto']; ?>
                                                    <?php $id_producto_token = hash_hmac('sha1', $id_producto, KEY_TOKEN);  ?>

                                                    <!--Datos del producto.-->

                                                    <h5 class="card-title text-center">
                                                        <a class='link-dark link-offset-2 link-underline link-underline-opacity-0 link-opacity-50-hover' href="paginas/detalles_producto/pagina_detalles_producto.php?id=<?php echo ($id_producto); ?>&token=<?php echo ($id_producto_token); ?>"><?php echo ($lista_productos[$i]['nombre_producto']) ?></a>
                                                    </h5>
                                                    <div class="row">
                                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                            <p class="card-text"><strong>Categoria:</strong><?php echo ($lista_productos[$i]['nombre_categoria']) ?></p>
                                                        </div>
                                                        <div class="col-6 col-sm-6 col-md-6 col-lg-6">
                                                            <p class="card-text"><strong>Precio:</strong>$<?php echo ($lista_productos[$i]['precio']) ?></p>
                                                        </div>
                                                        <div class="col-6 col-sm-6 col-md-6 col-lg-6">
                                                            <p class="card-text"><strong>Stock:</strong><?php echo ($lista_productos[$i]['stock']) ?></p>
                                                        </div>

                                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 text-center">
                                                            <p class="card-text"><strong>Calificacion del producto</strong></p>
                                                        </div>

                                                        <!--Verifica si el producto tiene una calificacion-->
                                                        <div class="text-center">
                                                            <?php if (is_null($lista_productos[$i]['calificacion'])) { ?>
                                                                <p class="card-text">Este producto aun no tiene una calificacion</p>
                                                            <?php } else { ?>
                                                                <!--Agrega estrellas activas para la calificacion actual del producto. -->
                                                                <?php for ($k = 0; $k < $lista_productos[$i]['calificacion']; $k++) { ?>
                                                                    <i class="fas fa-star star-calificacion-activo"></i>
                                                                <?php  } ?>

                                                                <!--Calcula cúantas estrellas faltan para llegar a la calificacion máxima posible. -->
                                                                <?php $calificacionRestante = $calificacion_max_productos - $lista_productos[$i]['calificacion']; ?>
                                                                <?php if ($calificacionRestante > 0) { ?>
                                                                    <!--Agregar estrellas vacias para las calificaciones restantes. -->
                                                                    <?php for ($l = 0; $l < $calificacionRestante; $l++) {  ?>
                                                                        <i class="fas fa-star star-calificacion"></i>
                                                                    <?php } ?>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    <?php   }  ?>

                                </div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                                <div class="swiper-pagination"></div>

                            </div>
                        <?php  } else { ?>
                            <h5 class="text-center">No hay publicaciones de productos disponible</h5>
                        <?php  } ?>


                    </div>

                    <!--Contenedor de los emprendedores.-->
                    <div class="tab-pane fade  overflow-auto" id="div_nav_emprendedor" role="tabpanel" aria-labelledby="nav_emprendedor_tab" tabindex="0">

                        <!--Muestra a emprendedores si la lista de emprendores tiene 1 o mas elementos.Utiliza Swiper para crear un Carrusel de emprendedores.-->
                        <?php if (count($lista_emprendedores) > 0) { ?>
                            <div class="swiper swiperEmprendedores">
                                <div class="swiper-wrapper">
                                    <!--Cada emprendedor se representa como una card dentro de un swiper-slide.-->
                                    <?php for ($i = 0; $i < count($lista_emprendedores); $i++) {  ?>
                                        <div class="swiper-slide">
                                            <div class="card h-100">

                                                <!--El header de la card contiene el nombre del empredimiento.-->
                                                <div class="card-header">
                                                    <p class="card-text text-center text-break"><strong><?php echo ($lista_emprendedores[$i]["nombre_emprendimiento"]); ?></strong></p>
                                                </div>

                                                <!--El body de la card incluye nombre de usuario, productos disponibles, calificacion del emprendedor y una foto del perfil del emprendedor.-->
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                            <!--Verifica si el emprendedor tiene una foto de perfil. Si no hay foto de perfil, se usa una imagen predeterminada-->
                                                            <?php if (is_null($lista_emprendedores[$i]["foto_perfil_nombre"])) {
                                                                $ruta_archivo = $url_foto_perfil_predeterminada;
                                                            } else {
                                                                $ruta_archivo = "{$url_base_archivos}/uploads/{$lista_emprendedores[$i]["id_usuario_emprendedor"]}/foto_perfil/{$lista_emprendedores[$i]["foto_perfil_nombre"]}";
                                                            } ?>
                                                            <img class="card-img-top imagen_perfil" src=" <?php echo ($ruta_archivo); ?>" alt="Foto de perfil">
                                                        </div>
                                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                            <div class="row">

                                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                                    <p class="card-text text-break"><strong>Nombre de usuario:</strong><?php echo ($lista_emprendedores[$i]["nombre_usuario"]); ?></p>
                                                                </div>
                                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                                    <p class="card-text text-break"><strong>Productos disponibles:</strong><?php echo ($lista_emprendedores[$i]["cant_productos_publicados"]); ?></p>
                                                                </div>
                                                                <div class='col-12 col-sm-12 col-md-12 col-lg-12 text-center'>
                                                                    <p class='card-text'><strong>Calificacion del emprendedor</strong></p>
                                                                </div>

                                                                <!--Verifica si el emprendedor tiene una calificacion-->
                                                                <div class="text-center">
                                                                    <?php if (is_null($lista_emprendedores[$i]['calificacion_emprendedor'])) { ?>
                                                                        <p class='card-text text-break'>El emprendedor aun no tiene una calificacion</p>
                                                                    <?php } else { ?>
                                                                        <!--Agrega estrellas activas para la calificacion actual del emprendedor. -->
                                                                        <?php for ($k = 0; $k < $lista_emprendedores[$i]['calificacion_emprendedor']; $k++) { ?>
                                                                            <i class="fas fa-star star-calificacion-activo"></i>
                                                                        <?php  } ?>

                                                                        <!--Calcula cúantas estrellas faltan para llegar a la calificacion máxima posible. -->
                                                                        <?php $calificacionRestante = $calificacion_max_emprendedores - $lista_emprendedores[$i]['calificacion_emprendedor']; ?>
                                                                        <?php if ($calificacionRestante > 0) { ?>
                                                                            <!--Agregar estrellas vacias para las calificaciones restantes. -->
                                                                            <?php for ($l = 0; $l < $calificacionRestante; $l++) {  ?>
                                                                                <i class="fas fa-star star-calificacion"></i>
                                                                            <?php } ?>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <!--El footer de la card contiene un boton para ver el perfil del emprendedor, utlizando el ID del usuario y un token que se obtiene del id del usuario.-->
                                                <div class="card-footer">
                                                    <div class="row  justify-content-center">
                                                        <?php $id_usuario = $lista_emprendedores[$i]['id_usuario_emprendedor'];  ?>
                                                        <?php $id_usuario_token = hash_hmac('sha1', $lista_emprendedores[$i]['id_usuario_emprendedor'], KEY_TOKEN); ?>
                                                        <div class="col-auto">
                                                            <a class="btn btn-outline-primary" href="usuarios/usuario_emprendedor/seccion/perfil/pagina_perfil.php?id=<?php echo ($id_usuario) ?>&token=<?php echo ($id_usuario_token) ?>">Ver perfil</a>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                    <?php }  ?>

                                </div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                                <div class="swiper-pagination"></div>
                            </div>
                        <?php  } else { ?>
                            <h5 class="text-center">Aun no hay emprendedores registrados </h5>
                        <?php  } ?>
                    </div>
                </div>

            <?php } else {  ?>
                <!-- Muestra un mensaje de error si existe algún problema.-->
                <div class="alert alert-danger" role="alert">
                    <?php echo ($mensaje_error); ?>
                </div>
            <?php } ?>
        </div>
    </main>

    <!-- Incluye el pie de pagina y varios scripts necesarios para el funcionamiento de la pagina.-->
    <?php require("template/footer.php"); ?>
    <?php require("usuarios/usuario_emprendedor/seccion/publicacion/modal_mapa_publicacion.php"); ?>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="config/js/funciones.js"></script>
    <script src="lib/leaflet-1.9.4/leaflet.js"></script>
    <script src="lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="lib/fancyapps-5.0.33/dist/fancybox/fancybox.umd.js"></script>
</body>

</html>
<script>
    // Inicializa un nuevo Swiper con la clase "swiperEmprendedores"
    var swiperEmprendedores = new Swiper(".swiperEmprendedores", {

        // Número de diapositivas visibles al mismo tiempo en el contenedor del slider
        slidesPerView: 'auto',

        // Distancia entre diapositivas en píxeles
        spaceBetween: 20,

        // Cambia el cursor al cursor de agarre cuando se pasa sobre el slider
        grabCursor: true,

        // Configuración de la paginación
        pagination: {
            el: ".swiper-pagination", // Selector del elemento de paginación
            clickable: true, // Permite hacer clic en los puntos de paginación para navegar
        },

        // Configura diferentes parámetros según el tamaño de la pantalla (puntos de interrupción)
        breakpoints: {
            //Diapositivas visibles en pantallas >= 576px
            576: {
                slidesPerView: 2,
            },
            //Diapositivas visibles en pantallas >= 768px
            768: {
                slidesPerView: 2,
            },
            //Diapositivas visibles en pantallas >= 992px
            992: {
                slidesPerView: 4,
            },
            //Diapositivas visibles en pantallas >= 1200px
            1200: {
                slidesPerView: 3,
            },
        },

        // Configuración de la navegación (botones siguiente y anterior)
        navigation: {
            nextEl: ".swiper-button-next", // Selector del botón de siguiente
            prevEl: ".swiper-button-prev", // Selector del botón de anterior
        },

    });

    // Inicializa un nuevo Swiper con la clase "swiperProductos"
    var swiperProductos = new Swiper(".swiperProductos", {

        // Número de diapositivas visibles al mismo tiempo en el contenedor del slider
        slidesPerView: 'auto',

        // Distancia entre diapositivas en píxeles
        spaceBetween: 20,

        // Cambia el cursor al cursor de agarre cuando se pasa sobre el slider
        grabCursor: true,

        // Configuración de la paginación
        pagination: {
            el: ".swiper-pagination", // Selector del elemento de paginación
            clickable: true, // Permite hacer clic en los puntos de paginación para navegar
        },

        // Configura diferentes parámetros según el tamaño de la pantalla (puntos de interrupción)
        breakpoints: {
            //Diapositivas visibles en pantallas >= 576px
            576: {
                slidesPerView: 2,
            },
            //Diapositivas visibles en pantallas >= 768px
            768: {
                slidesPerView: 2,
            },
            //Diapositivas visibles en pantallas >= 992px
            992: {
                slidesPerView: 3,
            },
            //Diapositivas visibles en pantallas >= 1200px
            1200: {
                slidesPerView: 3,
            },
        },

    });



    // Inicializa Fancybox con el selector de data-fancybox
    Fancybox.bind('[data-fancybox]', {
        // Deshabilita las miniaturas
        Thumbs: false,
        // Configuración de imágenes
        Images: {
            protected: true // Protege las imágenes contra clic derecho y arrastrar
        },
    });


    // Elementos para la navegación y las cards de publicaciones
    var div_nav_publicacion = document.getElementById("div_nav_publicacion");
    var cards_publicaciones = document.getElementById("cards_publicaciones");

    // Variables de control para la paginación
    var cargar_publicaciones_informacion = true;
    var pagina_publicacion_informacion = 1;




    // Carga inicial de datos de publicaciones
    getDataPublicaciones(pagina_publicacion_informacion);

    // Evento de scroll para cargar más publicaciones cuando se llega al fondo del contenedor
    div_nav_publicacion.addEventListener('scroll', function() {
        // Verifica si el usuario ha llegado al fondo del contenedor
        if (div_nav_publicacion.scrollHeight - div_nav_publicacion.scrollTop === div_nav_publicacion.clientHeight && cargar_publicaciones_informacion) {
            pagina_publicacion_informacion++; // Incrementa la página actual
            getDataPublicaciones(pagina_publicacion_informacion); // Carga más publicaciones
        }
    });

    //Función para obtener y cargar datos de publicaciones
    function getDataPublicaciones(pagina_publicacion_informacion) {
        fetch(`lista_publicaciones_index.php?pagina_publicacion=${pagina_publicacion_informacion}`, {})
            .then(respuesta => respuesta.json()) // Convierte la respuesta a JSON
            .then(datos => {
                if (datos.estado != "danger") {
                    // Añade nuevas cards de publicaciones al contenedor
                    cards_publicaciones.innerHTML += datos.cards;
                    // Actualiza la variable de control según si hay más publicaciones para cargar
                    cargar_publicaciones_informacion = datos.cargar_mas_publicaciones_informacion;
                } else {
                    // Muestra un mensaje de alerta si hubo un error
                    div_nav_publicacion.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }
            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                div_nav_publicacion.innerHTML = mensaje_alert_fijo("danger", e);
            });
    }
</script>