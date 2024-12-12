<?php

//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/consultas_notificacion.php");
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_categoria.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/funciones/funciones_token.php");
include("../../config/funciones/funciones_session.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/config_define.php");

$categorias_producto = array();
$mensaje_error = "";
$tipo_usuario = "";
try {

    //Establecer la sesion
    session_start();

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();


    //Se obtiene los datos de las categorias de los productos publicados
    $categorias_producto = obtenerCategoriasProducto($conexion);


    //Verifica los datos de sesion del usuario
    $usuario_inicio_sesion = verificarEntradaDatosSession(['id_usuario', 'tipo_usuario']);

    //Verifica si el usuario es valido
    if ($usuario_inicio_sesion) {

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION['id_usuario'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        $usuario_valido = verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario);
        //Verifica si el usuario es valido
        if ($usuario_valido) {
            //Se obtiene los datos del emprendedor 
            $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
            if (!empty($usuario_emprendedor)) {
                //Las variables se utilizan para que el usuario emprendedor pueda ir a su perfil desde el navbar
                $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];
                $id_usuario_emprendedor_token = hash_hmac('sha1', $id_usuario_emprendedor, KEY_TOKEN);
            }
        }
    }
} catch (Exception $e) {
    $mensaje_error = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--Titulo de la página-->
    <title>Proyecto Emprendedor Búsqueda de productos</title>

    <!--Enlace al archivo de estilos propios del proyecto-->
    <link href="../../config/css/estilos.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Bootstrap-->
    <link href="../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de FontAwesome para iconos-->
    <link href="../../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">

</head>

<body>
    <?php
    //Se obtiene el tipo de navbar que se va usar dependiendo del usuario
    switch ($tipo_usuario) {
        case 1:
            include($url_navbar_usuario_comun);
            break;
        case 2:
            include($url_navbar_usuario_emprendedor);
            break;
        default:
            include($url_navbar_general);
            break;
    }

    ?>
    <!--Separa el contenido principal de la pagina del pie de pagina.-->
    <main>
        <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
        <div class="container-fluid">
            <?php if (empty($mensaje_error)) {    ?>

                <!--Titulo de la seccion-->
                <h1 class="text-center">Buscar productos</h1>
                <div class="row">

                    <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                    <div id="alert_buscar_productos"></div>


                    <!-- Filtro para dispositivos de escritorio -->
                    <div class="col-lg-3 d-none d-lg-block mb-4">

                        <!-- Contenedor que tiene un accordion para mostrar los filtro de busqueda para dispositivos escritorio-->
                        <div class="accordion" id="accordionPanelFiltroDesktop">


                            <!-- Contenedor que tiene el item del accordion que permite al usuario ordenar los resultados por fecha de publicacion o por precio del producto usando botones de radio.  -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltroCollapseUno" aria-expanded="true" aria-controls="panelFiltroCollapseUno">
                                        Ordenar por
                                    </button>
                                </h2>
                                <div id="panelFiltroCollapseUno" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="radio_ordenar_por_desktop" id="fecha_mas_recientes_desktop" value="1" checked>
                                            <label class="form-check-label" for="fecha_mas_recientes_desktop">Fecha de publicación: más reciente</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="radio_ordenar_por_desktop" id="fecha_mas_antigua_desktop" value="2">
                                            <label class="form-check-label" for="fecha_mas_antigua_desktop">Fecha de publicación: más antigua</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="radio_ordenar_por_desktop" id="mayor_precio_desktop" value="3">
                                            <label class="form-check-label" for="mayor_precio_desktop">Mayor precio</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="radio_ordenar_por_desktop" id="menor_precio_desktop" value="4">
                                            <label class="form-check-label" for="menor_precio_desktop">Menor precio</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenedor que tiene el item del accordion que permite al usuario filtrar los resultado por rango del precio. -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltroCollapseDos" aria-expanded="false" aria-controls="panelFiltroCollapseDos">
                                        Rango de precio
                                    </button>
                                </h2>
                                <div id="panelFiltroCollapseDos" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="input-group input-group-sm mb-2">
                                            <input type="number" class="form-control" placeholder="Minimo" aria-label="Minimo" name="precio_minimo_desktop" id="precio_minimo_desktop" min="0" step="1">
                                            <span class="input-group-text">-</span>
                                            <input type="number" class="form-control" placeholder="Maximo" aria-label="Maximo" name="precio_maximo_desktop" id="precio_maximo_desktop" min="0" step="1">
                                            <button type="button" class="btn btn-outline-secondary" id="buscar_rango_precio_desktop"><i class="fa-solid fa-chevron-right"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenedor que tiene el item del accordion que permite al usuario filtrar los resultados por calificación, incluyendo la opción de seleccionar "Todos", "Sin calificación" o una calificación específica. -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltroCollapseTres" aria-expanded="false" aria-controls="panelFiltroCollapseTres">
                                        Calificacion
                                    </button>
                                </h2>
                                <div id="panelFiltroCollapseTres" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="radio_buscar_por_calificacion_desktop" id="todos_calificacion_desktop" value="todos_calificacion" checked>
                                            <label class="form-check-label" for="todos_calificacion_desktop">Todos</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="radio_buscar_por_calificacion_desktop" id="sin_calificacion_desktop" value="sin_calificacion">
                                            <label class="form-check-label" for="sin_calificacion_desktop">Sin calificacion</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="radio_buscar_por_calificacion_desktop" id="num_calificacion_desktop" value="num_calificacion">
                                            <label class="form-check-label" for="num_calificacion_desktop">Por calificacion</label>
                                        </div>
                                        <div class="rating">
                                            <i class="fa-solid fa-star star-filtro-desktop"></i>
                                            <i class="fa-solid fa-star star-filtro-desktop"></i>
                                            <i class="fa-solid fa-star star-filtro-desktop"></i>
                                            <i class="fa-solid fa-star star-filtro-desktop"></i>
                                            <i class="fa-solid fa-star star-filtro-desktop"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Contenedor que tiene el item del accordion que permite al usuario filtrar los resultados por categoria del productos. -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltroCollapseCuatro" aria-expanded="false" aria-controls="panelFiltroCollapseCuatro">
                                        Categoria
                                    </button>
                                </h2>
                                <div id="panelFiltroCollapseCuatro" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <select class="form-select" id="categoria_select_desktop" name="categoria_desktop">
                                            <option value="0" selected>Todos</option>
                                            <?php foreach ($categorias_producto as $categoria) { ?>
                                                <option value="<?php echo $categoria['id_categoria_producto']; ?>"><?php echo $categoria['nombre_categoria']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>


                        </div>
                        <!--Boton que permite restablecer los valores de busqueda -->
                        <button type="button" class="btn btn-outline-dark mt-3" id="restablecer_filtro_desktop">Restablecer</button>
                    </div>


                    <!-- Filtro para dispositivos de mobiles -->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-12 col-lg-8">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="search" class="form-control" placeholder="Buscar producto" aria-label="Buscar producto" name="buscar_producto" id="txt_buscar_producto">
                                </div>
                            </div>
                            <!-- Botón y offcanvas para dispositivos móviles -->
                            <div class="col-12 col-sm-12 col-md-12 d-lg-none mt-2">

                                <!-- Botón del filtro para dispositivos móviles -->
                                <button class="btn btn-outline-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltro" aria-controls="offcanvasFiltro">Filtros</button>

                                <!-- Offcanvas para el filtro en dispositivos móviles -->
                                <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasFiltro" aria-labelledby="offcanvasFiltroLabel">

                                    <!--Header de offcanvas-->
                                    <div class="offcanvas-header">
                                        <h5 class="offcanvas-title" id="offcanvasFiltroLabel">Filtro</h5>
                                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                    </div>

                                    <!--Body de offcanvas-->
                                    <div class="offcanvas-body">

                                        <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                        <div id="alert_filtro_offcanvas_mobile"></div>


                                        <!-- Contenedor que tiene un accordion para mostrar los filtro de busqueda para dispositivos móviles -->
                                        <div class="accordion" id="accordionPanelFiltroMobile">


                                            <!-- Contenedor que tiene el item del accordion que permite al usuario ordenar los resultados por fecha de publicacion o por precio del producto usando botones de radio.  -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltroCollapseUnoMobile" aria-expanded="true" aria-controls="panelFiltroCollapseUnoMobile">
                                                        Ordenar por
                                                    </button>
                                                </h2>
                                                <div id="panelFiltroCollapseUnoMobile" class="accordion-collapse collapse show">
                                                    <div class="accordion-body">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_ordenar_por_mobile" id="fecha_mas_recientes_mobile" value="1" checked>
                                                            <label class="form-check-label" for="fecha_mas_recientes_mobile">Fecha de publicación: más reciente</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_ordenar_por_mobile" id="fecha_mas_antigua_mobile" value="2">
                                                            <label class="form-check-label" for="fecha_mas_antigua_mobile">Fecha de publicación: más antigua</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_ordenar_por_mobile" id="mayor_precio_mobile" value="3">
                                                            <label class="form-check-label" for="mayor_precio_mobile">Mayor precio</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_ordenar_por_mobile" id="menor_precio_mobile" value="4">
                                                            <label class="form-check-label" for="menor_precio_mobile">Menor precio</label>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>


                                            <!-- Contenedor que tiene el item del accordion que permite al usuario filtrar los resultado por rango del precio. -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltroCollapseDos" aria-expanded="false" aria-controls="panelFiltroCollapseDos">
                                                        Rango de precio
                                                    </button>
                                                </h2>
                                                <div id="panelFiltroCollapseDos" class="accordion-collapse collapse">
                                                    <div class="accordion-body">
                                                        <div class="input-group input-group-sm mb-2">
                                                            <input type="number" class="form-control" placeholder="Minimo" aria-label="Minimo" name="precio_minimo_mobile" id="precio_minimo_mobile" min="0" step="1">
                                                            <span class="input-group-text">-</span>
                                                            <input type="number" class="form-control" placeholder="Maximo" aria-label="Maximo" name="precio_maximo_mobile" id="precio_maximo_mobile" min="0" step="1">
                                                            <button type="button" class="btn btn-outline-secondary" id="buscar_rango_precio_mobile"><i class="fa-solid fa-chevron-right"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Contenedor que tiene el item del accordion que permite al usuario filtrar los resultados por calificación, incluyendo la opción de seleccionar "Todos", "Sin calificación" o una calificación específica. -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltroCollapseTres" aria-expanded="false" aria-controls="panelFiltroCollapseTres">
                                                        Calificacion
                                                    </button>
                                                </h2>
                                                <div id="panelFiltroCollapseTres" class="accordion-collapse collapse">
                                                    <div class="accordion-body">

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_buscar_por_calificacion_mobile" id="todos_calificacion_mobile" value="todos_calificacion" checked>
                                                            <label class="form-check-label" for="todos_calificacion_mobile">Todos</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_buscar_por_calificacion_mobile" id="sin_calificacion_mobile" value="sin_calificacion">
                                                            <label class="form-check-label" for="sin_calificacion_mobile">Sin calificacion</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_buscar_por_calificacion_mobile" id="num_calificacion_mobile" value="num_calificacion">
                                                            <label class="form-check-label" for="num_calificacion_mobile">Por calificacion</label>
                                                        </div>
                                                        <div class="rating">
                                                            <i class="fa-solid fa-star star-filtro-mobile"></i>
                                                            <i class="fa-solid fa-star star-filtro-mobile"></i>
                                                            <i class="fa-solid fa-star star-filtro-mobile"></i>
                                                            <i class="fa-solid fa-star star-filtro-mobile"></i>
                                                            <i class="fa-solid fa-star star-filtro-mobile"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <!-- Contenedor que tiene el item del accordion que permite al usuario filtrar los resultados por categoria del productos. -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltroCollapseCuatro" aria-expanded="false" aria-controls="panelFiltroCollapseCuatro">
                                                        Categoria
                                                    </button>
                                                </h2>
                                                <div id="panelFiltroCollapseCuatro" class="accordion-collapse collapse">
                                                    <div class="accordion-body">
                                                        <select class="form-select" id="categoria_select_mobile" name="categoria_mobile">
                                                            <option value="0" selected>Todos</option>
                                                            <?php foreach ($categorias_producto as $categoria) { ?>
                                                                <option value="<?php echo $categoria['id_categoria_producto']; ?>"><?php echo $categoria['nombre_categoria']; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!--Boton que permite restablecer los valores de busqueda -->
                                        <button type="button" class="btn btn-outline-dark mt-3" id="restablecer_filtro_mobile">Restablecer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--Contenedor con las cards que contiene los prouctos-->
                        <div class="row align-items-center justify-content-center" id="card_producto"></div>
                    </div>


                    <!--Contenedor que contiene informacion sobre la paginacion y la cantidad de paginas disponibles-->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="row">
                            <!--Informacion sobre las paginas disponibles -->
                            <div class="col-6 col-sm-6 col-md-6 col-lg-6">
                                <p id="lbl-totalPagina"></p>
                            </div>

                            <!--Contenedor para la navegacion de la paginacion -->
                            <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacion"> </div>

                        </div>

                    </div>
                </div>



            <?php } else {  ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo ($mensaje_error); ?>
                </div>
            <?php } ?>

        </div>
    </main>


    <!-- Incluye el pie de pagina y varios scripts necesarios para el funcionamiento de la pagina.-->
    <script src="../../config/js/funciones.js"></script>
    <script src="../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <?php require("../../template/footer.php"); ?>

</body>

</html>




<script>
    var pagina_actual = 1;
    var calificacion = "todos_calificacion";
    var tipoVista = "";
    const estrellasDesktop = document.querySelectorAll(".star-filtro-desktop");
    const estrellasMobile = document.querySelectorAll(".star-filtro-mobile");
    var alert_buscar_productos = document.getElementById("alert_buscar_productos");


    // Carga inicial de datos de los productos
    getDataProductos();

    //Funcion para restablecer los valores de busqueda del filtro del desktop a sus valores iniciales ademas de actualizar la lista de productos
    function restablecer_desktop() {
        alert_buscar_productos.innerHTML = "";
        document.getElementById('fecha_mas_recientes_desktop').checked = true;
        document.getElementById('categoria_select_desktop').selectedIndex = 0;
        document.getElementById('precio_minimo_desktop').value = '';
        document.getElementById('precio_maximo_desktop').value = '';
        document.getElementById('todos_calificacion_desktop').checked = true;
        calificacion = "todos_calificacion";
        actualizarStarRating(0, estrellasDesktop);
        pagina_actual = 1;
        getDataProductos();
    }


    //Funcion para restablecer los valores de busqueda del filtro del mobile a sus valores iniciales ademas de actualizar la lista de productos
    function restablecer_mobile() {
        alert_filtro_offcanvas_mobile = document.getElementById("alert_filtro_offcanvas_mobile");
        alert_filtro_offcanvas_mobile.innerHTML = "";
        document.getElementById('fecha_mas_recientes_mobile').checked = true;
        document.getElementById('categoria_select_mobile').selectedIndex = 0;
        document.getElementById('precio_minimo_mobile').value = '';
        document.getElementById('precio_maximo_mobile').value = '';
        document.getElementById('todos_calificacion_mobile').checked = true;
        calificacion = "todos_calificacion";
        actualizarStarRating(0, estrellasMobile);
        pagina_actual = 1;
        getDataProductos();
    }


    // Evento clic para cada estrella en la versión de escritorio
    estrellasDesktop.forEach(function(star, index) {
        //Se llama a una la funcion para actualizar la calificacion y obtener una lista de productos
        star.addEventListener("click", function() {
            document.getElementById('num_calificacion_desktop').checked = true;
            manejarClicEstrella(index, estrellasDesktop);
        });
    });

    // Evento clic para cada estrella en la versión móvil
    estrellasMobile.forEach(function(star, index) {
        //Se llama a una la funcion para actualizar la calificacion y obtener una lista de productos
        star.addEventListener("click", function() {
            document.getElementById('num_calificacion_mobile').checked = true;
            manejarClicEstrella(index, estrellasMobile);
        });
    });



    //Agrega un evento cuando se detecta que se haga un click en el elemento de restablecer_filtro_desktop
    document.getElementById('restablecer_filtro_desktop').addEventListener('click', function() {
        //Se llama a la funcion para restablecer los valores de busqueda del filtro y actualiza la lista de productos
        restablecer_desktop();
    });

    //Agrega un evento cuando se detecta que se haga un click en el elemento de restablecer_filtro_mobile
    document.getElementById('restablecer_filtro_mobile').addEventListener('click', function() {
        //Se llama a la funcion para restablecer los valores de busqueda del filtro y actualiza la lista de productos
        restablecer_mobile();
    });

    //Agrega un evento listener a cada elemento dentro del componente radio version desktop
    document.getElementsByName("radio_ordenar_por_desktop").forEach(function(radio) {
        //Agrega un evento cada vez que el numero de ordenamiento cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
        radio.addEventListener('change', function() {
            pagina_actual = 1; //Reinicia la pagina a 1
            getDataProductos(); //Llama a la funcion para obtener los datos de los productos

        });
    });

    //Agrega un evento listener a cada elemento dentro del componente radio version mobile
    document.getElementsByName("radio_ordenar_por_mobile").forEach(function(radio) {
        //Agrega un evento cada vez que el numero de ordenamiento cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
        radio.addEventListener('change', function() {
            pagina_actual = 1; //Reinicia la pagina a 1
            getDataProductos(); //Llama a la funcion para obtener los datos de los productos

        });
    });


    //Agrega un evento listener a cada elemento dentro del componente radio_buscar_por_calificacion_desktop
    document.getElementsByName("radio_buscar_por_calificacion_desktop").forEach(function(radio) {
        //Agrega un evento cada vez que la calificacion del producto cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
        radio.addEventListener('change', function() {
            if (radio.value != "num_calificacion") {
                calificacion = radio.value;
                actualizarStarRating(0, estrellasMobile); //LLama a la funcion para actualizar el numero de estrellas que se ve en la interfaz
                getDataProductos(); //Llama a la funcion para obtener los datos de los productos
            } else {
                calificacion = 0;
                getDataProductos(); //Llama a la funcion para obtener los datos de los productos
            }
        });
    });


    //Agrega un evento listener a cada elemento dentro del componente radio_buscar_por_calificacion_mobile
    document.getElementsByName("radio_buscar_por_calificacion_mobile").forEach(function(radio) {
        //Agrega un evento cada vez que la calificacion del producto cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
        radio.addEventListener('change', function() {
            if (radio.value != "num_calificacion") {
                calificacion = radio.value;
                actualizarStarRating(0, estrellasMobile); //LLama a la funcion para actualizar el numero de estrellas que se ve en la interfaz
                getDataProductos(); //Llama a la funcion para obtener los datos de los productos
            } else {
                calificacion = 0;
                getDataProductos(); //Llama a la funcion para obtener los datos de los productos
            }
        });
    });


    //Agrega un evento cada vez que el campo buscar producto cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
    document.getElementById('txt_buscar_producto').addEventListener('input', function() {
        pagina_actual = 1; //Reinicia la pagina a 1
        getDataProductos(); //Llama a la funcion para obtener los datos de los productos
    });


    //Agrega un evento cada vez que la categoria del producto cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
    document.getElementById("categoria_select_mobile").addEventListener("change", function() {
        pagina_actual = 1; //Reinicia la pagina a 1
        getDataProductos(); //Llama a la funcion para obtener los datos de los productos
    });


    //Agrega un evento cada vez que la categoria del producto cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
    document.getElementById("categoria_select_desktop").addEventListener("change", function() {
        pagina_actual = 1; //Reinicia la pagina a 1
        getDataProductos(); //Llama a la funcion para obtener los datos de los productos
    });


    //Agrega un evento cuando se detecta que se haga un click en el elemento de buscar_rango_precio_desktop
    document.getElementById('buscar_rango_precio_desktop').addEventListener('click', function() {
        precioMinimoDesktop = document.getElementById('precio_minimo_desktop');
        precioMaximoDesktop = document.getElementById('precio_maximo_desktop');
        var campos_verificar_num = [precioMinimoDesktop, precioMaximoDesktop];


        //Elimina cualquier alerta previa 
        alert_buscar_productos.innerHTML = "";

        //Valida que el campo respuesta no este vacio
        if (validarCampoVacio(campos_verificar_num)) {
            alert_buscar_productos.innerHTML = mensaje_alert_fijo("danger", "Por favor complete los dos campos de rango de precio");
            return false;
        }

        //Verifica que los campos precio minimo y maximo tengan valores numeros
        var lista_num_input = listaInputValorNoNumerico(campos_verificar_num);
        if (lista_num_input.length > 0) {
            alert_buscar_productos.innerHTML = mensaje_alert_lista_fijo("danger", "Solo se permite ingresar valores numericos en los siguientes campos:", lista_num_input);
            return false;
        }

        //Verifica que los campos precio minimo y maximo tengan valores numeros positivos
        var lista_num_input = listaInputNumNoPositivo(campos_verificar_num);
        if (lista_num_input.length > 0) {
            alert_buscar_productos.innerHTML = mensaje_alert_lista_fijo("danger", "Los valores ingresados en los siguientes campos no son numero positivos:", lista_num_input);
            return false;
        }

        //Verifica que el campo precio minimo no sea mayor a precio maximo
        if (parseFloat(precioMinimoDesktop.value) > parseFloat(precioMaximoDesktop.value)) {
            alert_buscar_productos.innerHTML = mensaje_alert_fijo("danger", "El rango minimo de precio debe ser menor o igual al valor maximo.");
            return false;
        }
        pagina_actual = 1; //Reinicia la pagina a 1
        getDataProductos(); //Llama a la funcion para obtener los productos
    });

    //Agrega un evento cuando se detecta que se haga un click en el elemento de buscar_rango_precio_mobile
    document.getElementById('buscar_rango_precio_mobile').addEventListener('click', function() {
        precioMinimoMobile = document.getElementById('precio_minimo_mobile');
        precioMaximoMobile = document.getElementById('precio_maximo_mobile');
        var campos_verificar_num = [precioMinimoMobile, precioMaximoMobile];

        //Elimina cualquier alerta previa 
        alert_filtro_offcanvas_mobile = document.getElementById("alert_filtro_offcanvas_mobile");
        alert_filtro_offcanvas_mobile.innerHTML = "";


        //Valida que el campo respuesta no este vacio
        if (validarCampoVacio(campos_verificar_num)) {
            alert_filtro_offcanvas_mobile.innerHTML = mensaje_alert_fijo("danger", "Por favor complete los dos campos de rango de precio");
            return false;
        }


        //Verifica que los campos precio minimo y maximo tengan valores numeros
        var lista_num_input = listaInputValorNoNumerico(campos_verificar_num);
        if (lista_num_input.length > 0) {
            alert_filtro_offcanvas_mobile.innerHTML = mensaje_alert_lista_fijo("danger", "Solo se permite ingresar valores numericos en los siguientes campos:", lista_num_input);
            return false;
        }


        //Verifica que los campos precio minimo y maximo tengan valores numeros positivos
        var lista_num_input = listaInputNumNoPositivo(campos_verificar_num);
        if (lista_num_input.length > 0) {
            alert_filtro_offcanvas_mobile.innerHTML = mensaje_alert_lista_fijo("danger", "Los valores ingresados en los siguientes campos no son numero positivos:", lista_num_input);
            return false;
        }

        //Verifica que el campo precio minimo no sea mayor a precio maximo
        if (parseFloat(precioMinimoMobile.value) > parseFloat(precioMaximoMobile.value)) {
            alert_filtro_offcanvas_mobile.innerHTML = mensaje_alert_fijo("danger", "El rango minimo de precio debe ser menor o igual al valor maximo.");
            return false;
        }

        pagina_actual = 1; //Reinicia la pagina a 1
        getDataProductos(); //Llama a la funcion para obtener los productos
    });

    // Evento para detectar el cambio en el tamaño de la ventana y cambiar los datos del filtro según sea necesario
    window.addEventListener('resize', function() {
        if (window.innerWidth < 992) {
            guardarValoresFiltros('mobile');
        } else {
            // Cambiar a vista de escritorio
            guardarValoresFiltros('desktop');
        }
    });

    //Funcion para guardar los valores del filtro de busqueda de los dos modos  
    function guardarValoresFiltros(tipo) {
        var ordenamiento;
        var precioMinimo;
        var precioMaximo;
        var categoria;
        var calificacion_radio;
        if (tipo != tipoVista) {
            if (tipo === 'mobile') {
                alert_buscar_productos.innerHTML = "";
                ordenamiento = document.querySelector('input[name="radio_ordenar_por_desktop"]:checked').value;
                precioMinimo = document.getElementById('precio_minimo_desktop').value;
                precioMaximo = document.getElementById('precio_maximo_desktop').value;
                categoria = document.getElementById('categoria_select_desktop').value;
                calificacion_radio = document.querySelector('input[name="radio_buscar_por_calificacion_desktop"]:checked').value;

                document.querySelector(`input[name="radio_buscar_por_calificacion_mobile"][value="${calificacion_radio}"]`).checked = true;
                document.querySelector(`input[name="radio_ordenar_por_mobile"][value="${ordenamiento}"]`).checked = true;
                document.getElementById('precio_minimo_mobile').value = precioMinimo;
                document.getElementById('precio_maximo_mobile').value = precioMaximo;
                document.getElementById('categoria_select_mobile').selectedIndex = categoria;

                actualizarStarRating(calificacion, estrellasMobile);
            } else {
                var alert_filtro_offcanvas_mobile = document.getElementById("alert_filtro_offcanvas_mobile");
                alert_filtro_offcanvas_mobile.innerHTML = "";
                ordenamiento = document.querySelector('input[name="radio_ordenar_por_mobile"]:checked').value;
                precioMinimo = document.getElementById('precio_minimo_mobile').value;
                precioMaximo = document.getElementById('precio_maximo_mobile').value;
                categoria = document.getElementById('categoria_select_mobile').value;
                calificacion_radio = document.querySelector('input[name="radio_buscar_por_calificacion_mobile"]:checked').value;

                document.querySelector(`input[name="radio_buscar_por_calificacion_desktop"][value="${calificacion_radio}"]`).checked = true;
                document.querySelector(`input[name="radio_ordenar_por_desktop"][value="${ordenamiento}"]`).checked = true;
                document.getElementById('precio_minimo_desktop').value = precioMinimo;
                document.getElementById('precio_maximo_desktop').value = precioMaximo;
                document.getElementById('categoria_select_desktop').selectedIndex = categoria;

                actualizarStarRating(calificacion, estrellasDesktop);
            }
        }
        tipoVista = tipo;
    }


    // Función para actualizar la interfaz de usuario con la calificación seleccionada
    function actualizarStarRating(rating, estrellas) {
        estrellas.forEach(function(star, index) {
            if (index < rating) {
                star.classList.add("checked");
            } else {
                star.classList.remove("checked");
            }
        });
    }


    //Funcion actualiza la calificacion y marca o desmarcar las estrellas ademas de actualizar la lista de productos
    function manejarClicEstrella(index, estrellas) {
        const rating = index + 1;
        if (rating === calificacion) {
            calificacion = 0;
            estrellas.forEach(function(star) {
                star.classList.remove("checked");
            });
        } else {
            calificacion = rating;
            actualizarStarRating(calificacion, estrellas)
        }

        pagina_actual = 1; //Reinicia la pagina a 1
        getDataProductos(); //Llama a la funcion para obtener los productos
    }


    //Función para obtener y cargar datos de productos
    function getDataProductos() {
        var campo_buscar_producto = document.getElementById("txt_buscar_producto");
        alert_buscar_productos.innerHTML = "";
        var pagina = pagina_actual;
        var ordenamiento;
        var precioMinimo;
        var precioMaximo;
        var categoria;
        if (window.innerWidth < 992) {
            ordenamiento = parseInt(document.querySelector('input[name="radio_ordenar_por_mobile"]:checked').value);
            precioMinimo = parseFloat(document.getElementById('precio_minimo_mobile').value);
            precioMaximo = parseFloat(document.getElementById('precio_maximo_mobile').value);
            categoria = parseInt(document.getElementById('categoria_select_mobile').value);
        } else {
            ordenamiento = parseInt(document.querySelector('input[name="radio_ordenar_por_desktop"]:checked').value);
            precioMinimo = parseFloat(document.getElementById('precio_minimo_desktop').value);
            precioMaximo = parseFloat(document.getElementById('precio_maximo_desktop').value);
            categoria = parseInt(document.getElementById('categoria_select_desktop').value);
        }


        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('campo_buscar_producto', campo_buscar_producto.value);
        formData.append('numero_pagina', pagina);
        formData.append('num_ordenamiento', ordenamiento);
        formData.append('precio_minimo', precioMinimo);
        formData.append('precio_maximo', precioMaximo);
        formData.append('num_categoria', categoria);
        formData.append('calificacion', calificacion);
        fetch('lista_producto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado != 'danger') {
                    document.getElementById('card_producto').innerHTML = datos.cards;
                    document.getElementById("nav-paginacion").innerHTML = datos.paginacion;
                    document.getElementById("lbl-totalPagina").innerHTML = datos.pagina;
                } else {
                    // Muestra un mensaje de alerta si hubo un error
                    alert_buscar_productos.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);
                }
            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_buscar_productos.innerHTML = mensaje_alert_fijo("danger", e);
            });

    }


    //Funcion para cambiar la pagina y recargar los datos
    function nextPageProducto(pagina) {
        pagina_actual = pagina;
        getDataProductos();
    }
</script>