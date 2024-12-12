<?php

//Archivos de configuracion y funciones necesarias
include("../config/consultas_bd/conexion_bd.php");
include("../config/funciones/funciones_session.php");
include("../config/consultas_bd/consultas_usuario_admin.php");
include("../config/consultas_bd/consultas_producto.php");
include("../config/consultas_bd/consultas_categoria.php");
include("../config/consultas_bd/consultas_usuario.php");
include("../config/config_define.php");


$mensaje_error = "";

try {

    //Establecer la sesion
    session_start();

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica que los datos de sesion sean de un usuario administrador y que sea un usuario valido
    verificarDatosSessionUsuarioAdministrador($conexion);

    //Se obtiene los datos de las categorias de productos
    $categorias_producto = obtenerCategoriasProducto($conexion);

    //Se obtiene los datos de los estados del producto
    $estados_producto = obtenerEstadosProducto($conexion);

    //Se obtiene los datos de los tipos usuarios
    $tipos_usuarios = obtenerTiposUsuarios($conexion);
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
    <title>Proyecto Emprendedor Inicio Administrador</title>

    <!--Enlace al archivo de estilos propios del proyecto-->
    <link href="../config/css/estilos.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Bootstrap-->
    <link href="../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de FontAwesome para iconos-->
    <link href="../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Fancybox-->
    <link href="../lib/fancyapps-5.0.33/dist/fancybox/fancybox.css" rel="stylesheet" />

    <!--Enlace al archivo de estilos para Leaflet para mapas-->
    <link href="../lib/leaflet-1.9.4/leaflet.css" rel="stylesheet" />

</head>


<body>

    <!--Incluye el archivo de la barra de navegación para usuarios administrador.-->
    <?php include($url_navbar_usuario_admin);  ?>


    <!--Separa el contenido principal de la pagina del pie de pagina.-->
    <main>

        <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
        <div class="container-fluid">

            <?php if (empty($mensaje_error)) { ?>


                <h1>Bienvenido usuario administrador</h1>
                <p>Aquí podra supervisar y gestionar los productos publicados, las publicaciones y las cuentas de los usuarios registrados</p>
      
                <!--Define una navegación por pestañas para alternar entre la vistas usuarios, productos y publicaciones.-->
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="lista_usuarios_tab" data-bs-toggle="tab" data-bs-target="#lista_usuarios" type="button" role="tab" aria-controls="lista_usuarios" aria-selected="true">Usuarios registrados</button>
                        <button class="nav-link" id="lista_productos_tab" data-bs-toggle="tab" data-bs-target="#lista_productos" type="button" role="tab" aria-controls="lista_productos" aria-selected="false">Productos publicados</button>
                        <button class="nav-link" id="lista_publicaciones_tab" data-bs-toggle="tab" data-bs-target="#lista_publicaciones" type="button" role="tab" aria-controls="lista_publicaciones" aria-selected="false">Ultimas publicaciones</button>


                    </div>
                </nav>


                <!--Contenedor para el contenido de las pestañas.La pestaña de usuarios esta activa por defecto.-->
                <div class="tab-content" id="nav-tabContent">

                    <!--Contenedor de usuarios-->
                    <div class="tab-pane fade show active" id="lista_usuarios" role="tabpanel" aria-labelledby="lista_usuarios_tab" tabindex="0">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="row">

                                    <!-- Contenedor para buscar un usuario y un boton para agregar un nuevo usuario -->
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                        <div class="row">

                                            <!--Campo de busqueda por el nombre usuario, nombre de emprendimiento o email -->
                                            <div class="col-12 col-sm-12 col-md-9 col-lg-6 mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                                    <input type="search" class="form-control" placeholder="Buscar por nombre de usuario/emprendimiento o email" aria-label="Buscar por nombre de usuario o email" name="campo_buscar_usuario" id="campo_buscar_usuario">
                                                </div>
                                            </div>

                                            <!--Boton para abrir el modal para registrar un nuevo usuario-->
                                            <div class="col-12 col-sm-12 col-md-3 col-lg-3 mb-3">
                                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                                                    <i class="fa-solid fa-circle-plus"></i> Crear cuenta de usuario
                                                </button>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Contenedor para buscar usuarios -->
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-3">
                                        <div class="row">

                                            <!--Buscar por fecha que se registro-->
                                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text">Fecha de registro:</span>
                                                    <input type="date" name="fecha_inicio_usuario" id="fecha_inicio_usuario" class="form-control">
                                                </div>
                                            </div>

                                            <!--Filtro por el estado del usuario-->
                                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text" for="estado_usuario">Estado de usuario:</span>
                                                    <select class="form-select" name="estado_usuario" id="estado_usuario">
                                                        <option value="todos" selected>Todos</option>
                                                        <option value="activado">Activado</option>
                                                        <option value="no_activado">No Activado</option>
                                                        <option value="baneado">Baneado</option>
                                                        <option value="no_baneado">No Baneado</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!--Filtro por el tipo de usuario-->
                                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text" for="num_tipo_usuario">Tipo de usuario:</span>
                                                    <select class="form-select" name="num_tipo_usuario" id="num_tipo_usuario">
                                                        <option value="0" selected>Todos</option>
                                                        <?php foreach ($tipos_usuarios as $estado) { ?>
                                                            <option value="<?php echo $estado['id_tipo_usuario']; ?>"><?php echo $estado['nombre_tipo_usuario']; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <!--Selector de cantidad de usuarios a mostrar-->
                                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text" for="cant_registro_usuario">Mostrar:</span>
                                                    <select class="form-select" name="cant_registro_usuario" id="cant_registro_usuario">
                                                        <option value="10">10</option>
                                                        <option value="30">30</option>
                                                        <option value="40">40</option>
                                                        <option value="50">50</option>

                                                    </select>
                                                </div>
                                            </div>


                                            <!--Filtro por calificacion del emprendedor-->
                                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text" for="calificacion_emprendedor">Calificacion del emprendedor:</span>
                                                    <select class="form-select" id="select_calificacion_emprendedor">
                                                        <option value="todos" selected>Todos</option>
                                                        <option value="sin_calificacion">Sin calificacion</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                <div id="alert_notificacion_usuario"></div>
                            </div>

                            <!--Contenedor con la tabla que contiene a los usuarios registrados-->
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="table table-responsive" id="tabla_usuarios"></div>
                            </div>


                            <!--Contenedor que contiene informacion sobre la paginacion y la cantidad de paginas disponibles-->
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="row">
                                    <div class="col-6 col-sm-6 col-md-6 col-lg-6">

                                        <!--Informacion sobre la cantidad de resultados que se ve en la interfaz-->
                                        <p id="lbl-totalResultadoUsuario"></p>

                                        <!--Informacion sobre las paginas disponibles -->
                                        <p id="lbl-totalPaginaUsuario"></p>
                                    </div>

                                    <!--Contenedor para la navegacion de la paginacion -->
                                    <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacionUsuario"></div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <!--Contenedor de productos-->
                    <div class="tab-pane fade" id="lista_productos" role="tabpanel" aria-labelledby="lista_productos_tab" tabindex="0">

                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                    <div class="row">

                                        <!--Campo de busqueda de productos por el nombre del producto o nombre del emprendedor-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-4 mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                                <input type="search" class="form-control" placeholder="Nombre del producto/emprendedor" aria-label="Buscar producto" name="campo_buscar_producto" id="campo_buscar_producto">
                                            </div>
                                        </div>

                                        <!--Buscar por fecha que se publico-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text">Fecha de publicacion:</span>
                                                <input type="date" name="fecha_inicio_producto" id="fecha_inicio_producto" class="form-control">
                                            </div>
                                        </div>

                                        <!--Filtro por categoria del producto-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text" for="num_categoria_producto">Categoria:</span>
                                                <select class="form-select" name="num_categoria_producto" id="num_categoria_producto">
                                                    <option value="0" selected>Todos</option>
                                                    <?php foreach ($categorias_producto as $categoria) { ?>
                                                        <option value="<?php echo $categoria['id_categoria_producto']; ?>"><?php echo $categoria['nombre_categoria']; ?></option>
                                                    <?php } ?>

                                                </select>
                                            </div>
                                        </div>


                                        <!--Filtro por estado del producto-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text" for="num_estado_producto">Estado:</span>
                                                <select class="form-select" name="num_estado_producto" id="num_estado_producto">
                                                    <option value="0" selected>Todos</option>
                                                    <?php foreach ($estados_producto as $estado) { ?>
                                                        <option value="<?php echo $estado['id_estado_producto']; ?>"><?php echo $estado['estado']; ?></option>
                                                    <?php } ?>

                                                </select>
                                            </div>
                                        </div>



                                        <!--Filtro por calificacion del producto-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text" for="cant_calificacion_producto">Calificacion:</span>
                                                <select class="form-select" id="select_calificacion_producto">
                                                    <option value="todos" selected>Todos</option>
                                                    <option value="sin_calificacion">Sin calificacion</option>

                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                </select>
                                            </div>
                                        </div>



                                        <!--Selector de cantidad de productos a mostrar-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text" for="cant_registro_producto">Mostrar:</span>
                                                <select class="form-select" name="cant_registro_producto" id="cant_registro_producto">
                                                    <option value="5">5</option>
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                <div id="alert_notificacion_producto"></div>
                            </div>



                            <!--Contenedor con las cards que contiene los producto-->
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="row justify-content-center" id="cards_productos"></div>
                            </div>

                            <!--Contenedor que contiene informacion sobre la paginacion y la cantidad de paginas disponibles-->
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="row">
                                    <div class="col-6 col-sm-6 col-md-6 col-lg-6">
                                        <!--Informacion sobre la cantidad de resultados que se ve en la interfaz-->
                                        <p id="lbl-totalResultadoProducto"></p>

                                        <!--Informacion sobre las paginas disponibles -->
                                        <p id="lbl-totalPaginaProducto"></p>
                                    </div>

                                    <!--Contenedor para la navegacion de la paginacion -->
                                    <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacionProducto"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!--Contenedor de las publicaciones-->
                    <div class="tab-pane fade" id="lista_publicaciones" role="tabpanel" aria-labelledby="lista_publicaciones_tab" tabindex="0">

                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="row">

                                    <!--Campo de busqueda de publicaciones por el nombre del emprendedor-->
                                    <div class="col-12 col-sm-12 col-md-9 col-lg-6 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                            <input type="search" class="form-control" placeholder="Buscar por nombre del emprendimiento" aria-label="Buscar por nombre emprendimiento" name="campo_buscar_publicacion" id="campo_buscar_publicacion">
                                        </div>
                                    </div>


                                    <!--Buscar por fecha que se publico-->
                                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text">Fecha de publicacion:</span>
                                            <input type="date" name="fecha_inicio_publicacion" id="fecha_inicio_publicacion" class="form-control">
                                        </div>
                                    </div>


                                    <!--Filtro por el contenido de la publicacion-->
                                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text" for="tipo_publicacion">Contenido:</span>
                                            <select class="form-select" name="tipo_publicacion" id="tipo_publicacion">
                                                <option value="todos" selected>Todos</option>
                                                <option value="solo_descrip">Solo Descripcion</option>
                                                <option value="fotos/videos">Con Fotos/Videos</option>
                                                <option value="ubicacion">Con Ubicacion</option>
                                                <option value="fotos/videos/ubicacion">Con Fotos/Videos/Ubicacion</option>

                                            </select>
                                        </div>
                                    </div>


                                    <!--Selector de cantidad de publicaciones a mostrar-->
                                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text" for="cant_registro_publicacion">Mostrar:</span>
                                            <select class="form-select" name="cant_registro_publicacion" id="cant_registro_publicacion">
                                                <option value="10">10</option>
                                                <option value="30">30</option>
                                                <option value="40">40</option>
                                                <option value="50">50</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                <div id="alert_notificacion_publicaciones"></div>
                            </div>


                            <!--Contenedor con las cards que contiene los producto-->
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="row justify-content-center" id="card_publicaciones"></div>
                            </div>


                            <!--Contenedor que contiene informacion sobre la paginacion y la cantidad de paginas disponibles-->
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="row">
                                    <div class="col-6 col-sm-6 col-md-6 col-lg-6">


                                        <!--Informacion sobre la cantidad de resultados que se ve en la interfaz-->
                                        <p id="lbl-totalResultadoPublicacion"></p>

                                        <!--Informacion sobre las paginas disponibles -->
                                        <p id="lbl-totalPaginaPublicacion"></p>
                                    </div>

                                    <!--Contenedor para la navegacion de la paginacion -->
                                    <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacionPublicacion"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            <?php } else {  ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo ($mensaje_error) ?>
                </div>
            <?php } ?>


        </div>

    </main>

    <!-- Incluye el pie de pagina y varios scripts necesarios para el funcionamiento de la pagina.-->
    <script src="../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../lib/leaflet-1.9.4/leaflet.js"></script>
    <script src="../config/js/funciones.js"></script>
    <script src="../lib/fancyapps-5.0.33/dist/fancybox/fancybox.umd.js"></script>
    <?php require("paginas/detalles_usuarios/publicacion/modal_mapa_publicacion.php");  ?>
    <?php require("modal_nuevo_usuario.php");  ?>
    <?php require("../template/footer.php"); ?>
</body>

</html>
<script>
    // Inicializa Fancybox con el selector de data-fancybox
    Fancybox.bind('[data-fancybox]', {
        // Deshabilita las miniaturas
        Thumbs: false,
        // Configuración de imágenes
        Images: {
            protected: true // Protege las imágenes contra clic derecho y arrastrar
        },
    });


    /*Seccion usuario */

    // Variable de control para la paginación de usuarios
    pagina_actual_usuario = 1;

    // Carga de datos de usuarios
    getDatosUsuarios();

    //Agrega un evento cada vez que la fecha cambie la pagina actual de usuario vuelve a 1 y se llama a la funcion de usuarios
    document.getElementById("fecha_inicio_usuario").addEventListener("change", function() {
        pagina_actual_usuario = 1; //Reinicia la pagina de usuarios a 1
        getDatosUsuarios(); //Llama a la funcion para obtener los datos del usuarios
    });


    //Agrega un evento cada vez que el campo buscar producto cambie la pagina actual de usuario vuelve a 1 y se llama a la funcion de usuarios
    document.getElementById("campo_buscar_usuario").addEventListener("input", function() {
        pagina_actual_usuario = 1; //Reinicia la pagina de usuarios a 1
        getDatosUsuarios(); //Llama a la funcion para obtener los datos del usuarios
    });

    //Agrega un evento cada vez que se cambie el estado del usuario la pagina actual de usuario vuelve a 1 y se llama a la funcion de usuarios 
    document.getElementById("estado_usuario").addEventListener("change", function() {
        pagina_actual_usuario = 1; //Reinicia la pagina de usuarios a 1
        getDatosUsuarios(); //Llama a la funcion para obtener los datos del usuarios
    });


    //Agrega un evento cada vez que se cambie el tipo de usuario la pagina actual de usuario vuelve a 1 y se llama a la funcion de usuarios 
    document.getElementById("num_tipo_usuario").addEventListener("change", function() {
        pagina_actual_usuario = 1; //Reinicia la pagina de usuarios a 1
        getDatosUsuarios(); //Llama a la funcion para obtener los datos del usuarios
    });



    //Agrega un evento cada vez que la cantidad de registro de usuarios cambie la pagina actual de usuarios vuelve a 1 y se llama a la funcion de usuarios
    document.getElementById("cant_registro_usuario").addEventListener("change", function() {
        pagina_actual_usuario = 1; //Reinicia la pagina de usuarios a 1
        getDatosUsuarios(); //Llama a la funcion para obtener los datos del usuarios
    });


    //Agrega un evento cada vez que la calificacion del emprendedor cambie la pagina actual de usuarios vuelve a 1 y se llama a la funcion de usuarios
    document.getElementById("select_calificacion_emprendedor").addEventListener("change", function() {
        pagina_actual_usuario = 1; //Reinicia la pagina de usuarios a 1
        getDatosUsuarios(); //Llama a la funcion para obtener los datos del usuarios
    });



    //Función para obtener y cargar los datos de la tabla de usuario
    function getDatosUsuarios() {


        //Elimina cualquier alerta previa 
        var alert_notificacion_usuario = document.getElementById("alert_notificacion_usuario");
        alert_notificacion_usuario.innerHTML = "";


        var campo_buscar_usuario = document.getElementById("campo_buscar_usuario");
        var fecha_inicio_usuario = document.getElementById("fecha_inicio_usuario");
        var estado_usuario = document.getElementById("estado_usuario");
        var num_tipo_usuario = document.getElementById("num_tipo_usuario");
        var cant_registro_usuario = document.getElementById("cant_registro_usuario");
        var select_calificacion_emprendedor = document.getElementById("select_calificacion_emprendedor");
        var lbl_totalResultadoUsuario = document.getElementById("lbl-totalResultadoUsuario");
        var lbl_totalPaginaUsuario = document.getElementById("lbl-totalPaginaUsuario");
        var nav_paginacionUsuario = document.getElementById("nav-paginacionUsuario");
        var tabla_usuarios = document.getElementById("tabla_usuarios");
        var pagina_usuario = pagina_actual_usuario;


        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('select_calificacion', select_calificacion_emprendedor.value);

        formData.append('campo_buscar_usuario', campo_buscar_usuario.value);
        formData.append('fecha_inicio_usuario', fecha_inicio_usuario.value);
        formData.append('estado_usuario', estado_usuario.value);
        formData.append('num_tipo_usuario', num_tipo_usuario.value);
        formData.append('cant_registro_usuario', cant_registro_usuario.value);
        formData.append('numero_pagina_usuario', pagina_usuario);

        fetch('lista_usuarios.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado !== 'danger') {
                    tabla_usuarios.innerHTML = datos.tabla_usuarios;
                    lbl_totalResultadoUsuario.innerHTML = datos.registro;
                    lbl_totalPaginaUsuario.innerHTML = datos.pagina;
                    nav_paginacionUsuario.innerHTML = datos.paginacion;
                } else {
                    alert_notificacion_usuario.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    tabla_usuarios.innerHTML = "";
                    lbl_totalResultadoUsuario.innerHTML = "";
                    lbl_totalPaginaUsuario.innerHTML = "";
                    nav_paginacionUsuario.innerHTML = "";
                }


            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_notificacion_usuario.innerHTML = mensaje_alert_fijo("danger", e);
            });
    }

    //Funcion para cambiar la pagina de usuarios y recargar los datos de la tabla de usuarios
    function nextPageUsuarios(pagina) {
        pagina_actual_usuario = pagina;
        getDatosUsuarios();
    }


    /*Seccion productos */


    // Variable de control para la paginación de productos
    pagina_actual_producto = 1;


    // Carga de datos de productos
    getDataProductos();


    //Agrega un evento cada vez que la fecha cambie la pagina actual de productos vuelve a 1 y se llama a la funcion de productos
    document.getElementById("fecha_inicio_producto").addEventListener("change", function() {
        pagina_actual_producto = 1; //Reinicia la pagina de productos a 1
        getDataProductos(); //Llama a la funcion para obtener los datos de los productos
    });


    //Agrega un evento cada vez que el campo buscar producto cambie la pagina actual de productos vuelve a 1 y se llama a la funcion de productos
    document.getElementById("campo_buscar_producto").addEventListener("input", function() {
        pagina_actual_producto = 1; //Reinicia la pagina de productos a 1
        getDataProductos(); //Llama a la funcion para obtener los datos de los productos
    });

    //Agrega un evento cada vez que se cambie el estado del producto la pagina actual de productos vuelve a 1 y se llama a la funcion de productos 
    document.getElementById("num_estado_producto").addEventListener("change", function() {
        pagina_actual_producto = 1; //Reinicia la pagina de productos a 1
        getDataProductos(); //Llama a la funcion para obtener los datos de los productos
    });


    //Agrega un evento cada vez que se cambie la categoria del productos la pagina actual de productos vuelve a 1 y se llama a la funcion de productos 
    document.getElementById("num_categoria_producto").addEventListener("change", function() {
        pagina_actual_producto = 1; //Reinicia la pagina de productos a 1
        getDataProductos(); //Llama a la funcion para obtener los datos de los productos
    });


    //Agrega un evento cada vez que la cantidad de registro de productos cambie la pagina actual de productos vuelve a 1 y se llama a la funcion de productos
    document.getElementById("cant_registro_producto").addEventListener("change", function() {
        pagina_actual_producto = 1; //Reinicia la pagina de productos a 1
        getDataProductos(); //Llama a la funcion para obtener los datos de los productos
    });


    //Agrega un evento cada vez que la calificacion del producto cambie la pagina actual de productos vuelve a 1 y se llama a la funcion de productos
    document.getElementById("select_calificacion_producto").addEventListener("change", function() {
        pagina_actual_producto = 1; //Reinicia la pagina de productos a 1
        getDataProductos(); //Llama a la funcion para obtener los datos de los productos
    });


    //Función para obtener y cargar los datos de los productos publicados
    function getDataProductos() {
        //Elimina cualquier alerta previa 
        var alert_notificacion_producto = document.getElementById("alert_notificacion_producto");
        alert_notificacion_producto.innerHTML = "";


        var campo_buscar = document.getElementById("campo_buscar_producto");
        var categoria = document.getElementById("num_categoria_producto");
        var estado = document.getElementById("num_estado_producto");
        var fecha_inicio = document.getElementById("fecha_inicio_producto");
        var cant_registro = document.getElementById("cant_registro_producto");
        var select_calificacion = document.getElementById("select_calificacion_producto");
        var cards_productos = document.getElementById("cards_productos");
        var pagina = pagina_actual_producto;

        var lbl_totalResultadoProducto = document.getElementById("lbl-totalResultadoProducto");
        var lbl_totalPaginaProducto = document.getElementById("lbl-totalPaginaProducto");
        var nav_paginacionProducto = document.getElementById("nav-paginacionProducto");

        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('select_calificacion', select_calificacion.value);
        formData.append('estado', estado.value);
        formData.append('categoria', categoria.value);
        formData.append('campo_buscar', campo_buscar.value);
        formData.append('cant_registro', cant_registro.value);
        formData.append('fecha', fecha_inicio.value);
        formData.append('numero_pagina', pagina);
        fetch(`lista_productos.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado !== 'danger') {
                    cards_productos.innerHTML = datos.card_productos;
                    lbl_totalResultadoProducto.innerHTML = datos.registro;
                    lbl_totalPaginaProducto.innerHTML = datos.pagina;
                    nav_paginacionProducto.innerHTML = datos.paginacion;
                } else {
                    alert_notificacion_producto.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    cards_productos.innerHTML = "";
                    lbl_totalResultadoProducto.innerHTML = "";
                    lbl_totalPaginaProducto.innerHTML = "";
                    nav_paginacionProducto.innerHTML = "";
                }

            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_notificacion_producto.innerHTML = mensaje_alert_fijo("danger", e);
            });
    }



    //Funcion para cambiar la pagina de productos y recargar los datos de la cards de productos
    function nextPageProducto(pagina) {
        pagina_actual_producto = pagina;
        getDataProductos();
    }


    /*Seccion publicaciones */


    // Variable de control para la paginación de publicaciones
    pagina_actual_publicacion = 1;


    // Carga de datos de publicaciones
    getDataPublicaciones();


    //Agrega un evento cada vez que la fecha cambie la pagina actual de publicaciones vuelve a 1 y se llama a la funcion de publicaciones
    document.getElementById("fecha_inicio_publicacion").addEventListener("change", function() {
        pagina_actual_publicacion = 1; //Reinicia la pagina de publicaciones a 1
        getDataPublicaciones(); //Llama a la funcion para obtener los datos de las publicaciones
    });


    //Agrega un evento cada vez que el campo buscar producto cambie la pagina actual de publicaciones vuelve a 1 y se llama a la funcion de publicaciones
    document.getElementById("campo_buscar_publicacion").addEventListener("input", function() {
        pagina_actual_publicacion = 1; //Reinicia la pagina de publicaciones a 1
        getDataPublicaciones(); //Llama a la funcion para obtener los datos de las publicaciones
    });

    //Agrega un evento cada vez que se cambie el tipo de contenido de las publicaciones a mostrar la pagina actual de publicaciones vuelve a 1 y se llama a la funcion de publicaciones 
    document.getElementById("tipo_publicacion").addEventListener("change", function() {
        pagina_actual_publicacion = 1; //Reinicia la pagina de productos a 1
        getDataPublicaciones(); //Llama a la funcion para obtener los datos de las publicaciones
    });


    //Agrega un evento cada vez que la cantidad de registro de publicaciones cambie la pagina actual de publicaciones vuelve a 1 y se llama a la funcion de publicaciones
    document.getElementById("cant_registro_publicacion").addEventListener("change", function() {
        pagina_actual_publicacion = 1; //Reinicia la pagina de publicaciones a 1
        getDataPublicaciones(); //Llama a la funcion para obtener los datos de las publicaciones
    });


    //Función para obtener y cargar los datos de las publicaciones
    function getDataPublicaciones() {

        var campo_buscar = document.getElementById("campo_buscar_publicacion");
        var fecha_inicio = document.getElementById("fecha_inicio_publicacion");
        var tipo_publicacion = document.getElementById("tipo_publicacion");
        var cant_registro = document.getElementById("cant_registro_publicacion");
        var alert_notificacion_publicaciones = document.getElementById("alert_notificacion_publicaciones");
        var card_publicaciones = document.getElementById("card_publicaciones");
        var pagina = pagina_actual_publicacion;
        var totalResultadoPublicacion = document.getElementById("lbl-totalResultadoPublicacion");
        var totalPaginaPublicacion = document.getElementById("lbl-totalPaginaPublicacion");
        var paginacionPublicacion = document.getElementById("nav-paginacionPublicacion");

        var formData = new FormData();

        formData.append('campo_buscar', campo_buscar.value);
        formData.append('fecha', fecha_inicio.value);
        formData.append('tipo_publicacion', tipo_publicacion.value);
        formData.append('cant_registro', cant_registro.value);
        formData.append('numero_pagina', pagina);
        fetch(`lista_publicaciones.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado !== 'danger') {
                    alert_notificacion_publicaciones.innerHTML = "";
                    card_publicaciones.innerHTML = datos.cards_publicaciones;
                    totalResultadoPublicacion.innerHTML = datos.registro;
                    totalPaginaPublicacion.innerHTML = datos.pagina;
                    paginacionPublicacion.innerHTML = datos.paginacion;
                } else {
                    alert_notificacion_publicaciones.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    card_publicaciones.innerHTML = "";
                    totalResultadoPublicacion.innerHTML = "";
                    totalPaginaPublicacion.innerHTML = "";
                    paginacionPublicacion.innerHTML = "";
                }

            }).catch(e => {
                console.error(e);
            });
    }



    //Funcion para cambiar la pagina de publicaciones y recargar los datos de la cards de publicaciones
    function nextPagePublicacion(pagina) {
        pagina_actual_publicacion = pagina;
        getDataPublicaciones();
    }
</script>