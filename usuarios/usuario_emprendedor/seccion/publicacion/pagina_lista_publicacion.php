<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../../config/consultas_bd/consultas_notificacion.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/config_define.php");

$mensaje_error = "";
try {

    //Establecer la sesion
    session_start();

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica que los datos de sesion sean un usuario emprendedor y que sea un usuario valido
    verificarDatosSessionUsuarioEmprendedor($conexion);

    //Se obtiene ID del usuario
    $id_usuario = $_SESSION['id_usuario'];


    //Se obtiene los datos del emprendedor en caso que sea uno
    $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
    if (empty($usuario_emprendedor)) {
        throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
    }

    //Las variables se utilizan para que el usuario emprendedor pueda ir a su perfil desde el navbar
    $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];
    $id_usuario_emprendedor_token = hash_hmac('sha1', $id_usuario_emprendedor, KEY_TOKEN);
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
    <title>Proyecto Emprendedor Mis Publicaciones</title>

    <!--Enlace al archivo de estilos propios del proyecto-->
    <link href="../../../../config/css/estilos.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Bootstrap-->
    <link href="../../../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de FontAwesome para iconos-->
    <link href="../../../../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Fancybox-->
    <link href="../../../../lib/fancyapps-5.0.33/dist/fancybox/fancybox.css" rel="stylesheet" />

    <!--Enlace al archivo de estilos para Leaflet para mapas-->
    <link href="../../../../lib/leaflet-1.9.4/leaflet.css" rel="stylesheet" />

</head>

<body>
    <!--Incluye el archivo de la barra de navegación para usuarios emprendedores.-->
    <?php include($url_navbar_usuario_emprendedor); ?>

    <!--Separa el contenido principal de la pagina del pie de pagina.-->
    <main>
        <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
        <div class="container-fluid">
            <?php if (empty($mensaje_error)) { ?>
                <!--Titulo de la seccion-->
                <h1 class="text-center">Mis publicaciones</h1>
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">

                        <div class="row">


                            <!--Boton para abrir el modal para agregar una nueva publicacion-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modal_agregar_publicacion"><i class="fa-solid fa-circle-plus"></i> Nueva publicacion</button>
                            </div>

                            <!--Buscar por fecha que se hizo la publicacion-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text">Buscar por fecha de publicacion:</span>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control">
                                </div>
                            </div>

                            <!--Selector de cantidad de publicaciones a mostrar-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" for="cant_registro">Mostrar:</span>
                                    <select class="form-select" name="cant_registro" id="cant_registro">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                        <div id="alert_notificacion_informacion"></div>
                    </div>

                    <!--Contenedor con las cards que contiene las publicaciones-->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="row justify-content-center" id="card_publicacion"></div>
                    </div>

                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="row">
                            <div class="col-6 col-sm-6 col-md-6 col-lg-6">

                                <!--Informacion sobre la cantidad de resultados que se ve en la interfaz-->
                                <p id="lbl-totalResultado"></p>

                                <!--Informacion sobre las paginas disponibles -->
                                <p id="lbl-totalPagina"></p>

                            </div>

                            <!--Contenedor para la navegacion de la paginacion -->
                            <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacion"></div>
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



    <!-- Incluye el pie de pagina, los Modals y varios scripts necesarios para el funcionamiento de la pagina.-->
    <script src="../../../../config/js/funciones.js"></script>
    <script src="../../../../lib/leaflet-1.9.4/leaflet.js"></script>
    <script src="../../../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../../../lib/fancyapps-5.0.33/dist/fancybox/fancybox.umd.js"></script>
    <?php require("modal_agregar_publicacion.php"); ?>
    <?php require("modal_eliminar_publicacion.php"); ?>
    <?php require("modal_mapa_publicacion.php"); ?>
    <?php require("modal_modificar_publicacion.php"); ?>

    <?php require("../../../../template/footer.php"); ?>

</body>


</html>


<script>
    //Inicializa la pagina actual y la cantidad actual de publicaciones que se ve en la interfaz 
    var cant_actual_registros;
    var pagina_actual = 1;
    var alert_notificacion_informacion = document.getElementById("alert_notificacion_informacion");

    //Llama a la funcion para obtener datos de las publicaciones
    getDataPublicacion();



    // Inicializa Fancybox con el selector de data-fancybox
    Fancybox.bind('[data-fancybox]', {
        // Deshabilita las miniaturas
        Thumbs: false,
        // Configuración de imágenes
        Images: {
            protected: true // Protege las imágenes contra clic derecho y arrastrar
        },
    });



    //Agrega un evento cada vez que la fecha cambie la pagina actual vuelve a 1 y se llama a la funcion de publicaciones
    document.getElementById("fecha_inicio").addEventListener("change", function() {

        pagina_actual = 1; //Reinicia la pagina a 1
        getDataPublicacion(); //Llama a la funcion para obtener datos de publicaciones

    });

    //Agrega un evento cada vez que la cantidad de registro cambie la pagina actual vuelve a 1 y se llama a la funcion de publicaciones
    document.getElementById("cant_registro").addEventListener("change", function() {

        pagina_actual = 1; //Reinicia la pagina a 1
        getDataPublicacion(); //Llama a la funcion para obtener datos de publicaciones

    });



    //Manejo del envio del formulario para agregar una publicacion
    formulario_agregar_publicacion.addEventListener("submit", (e) => {

        //Previene el envio por defecto del formulario
        e.preventDefault();


        //Elimina cualquier alerta previa 
        mensaje_error_archivo_agregar.innerHTML = "";
        alert_notificacion_agregar_publicacion.innerHTML = "";


        var txt_descripcion_agregar = document.getElementById('txt_descripcion_agregar');

        //Valida que el campo descripcion no este vacio
        if (validarCampoVacio([txt_descripcion_agregar])) {
            alert_notificacion_agregar_publicacion.innerHTML = mensaje_alert_fijo("danger", "Por favor complete el campo descripcion");
            return false;
        }


        //Valida que el campo no tenga espacios al inicio o al final de la cadena
        var lista_trim_input = listaInputEspacioBlancoIF([txt_descripcion_agregar]);
        if (lista_trim_input.length > 0) {
            alert_notificacion_agregar_publicacion.innerHTML = mensaje_alert_fijo("danger", "La descripcion no puede tener espacios en blanco al inicio o al final");
            return false;
        }

        //Valida que la longitud del campo sea valida 
        if (!validarCantLengthInput(txt_descripcion_agregar)) {
            alert_notificacion_agregar_publicacion.innerHTML = mensaje_alert_fijo("danger", "El campo descripcion debe tener entre 1 y 255 caracteres");
            return false;
        }

        //Verifica que si el div de mapa este activo 
        if (boton_grupo_map_agregar.disabled) {
            //En caso que este activo y los valores de latitud o longitud sea NULL mostraria un mensaje de error
            if (map_latitude_agregar == null || map_longitude_agregar == null) {
                alert_notificacion_agregar_publicacion.innerHTML = mensaje_alert_fijo("danger", "No se pudo obtener los datos necesarios para guardar su ubicacion. Por favor cierre la ventana mapa para poder continuar");
                return false;
            }
        }

        //Verifica que si el div de archivos este activo 
        if (boton_grupo_archivo_agregar.disabled) {

            //Verifica que la cantidad de archivos en la publicacion sea valido
            if (!validadCantidadArchivos(lista_archivos_agregar.length, cant_min_agregar, cant_max_agregar)) {
                alert_notificacion_agregar_publicacion.innerHTML = mensaje_alert_fijo("danger", "La cantidad de archivos no cumplen con los requisitos.Debe ser al menos " + cant_min_agregar + " archivo " + "y como máximo " + cant_max_agregar + " archivos");
                return false;
            }
        }

        // Envío del formulario usando fetch
        var formData = new FormData();
        formData.append('descripcion', txt_descripcion_agregar.value.trim());
        formData.append('map_latitude', map_latitude_agregar);
        formData.append('map_longitude', map_longitude_agregar);
        for (var file of lista_archivos_agregar) {
            formData.append('files[]', file);
        }
        fetch('alta_publicacion.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado === 'success') {

                    // Actualiza los datos de publicacion
                    getDataPublicacion();


                    //Se restable la lista de archivos
                    lista_archivos_agregar = [];

                    //Muestra un mensaje en la interfaz del usuario
                    alert_notificacion_agregar_publicacion.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);


                    //Resetea el formulario para limpiar los campos
                    formulario_agregar_publicacion.reset();

                    //Contar y mostrar caracteres restantes en el campo respuesta
                    contarMostrarCarecteresRestantes('txt_descripcion_agregar', 'txaCountDescripAgregar');


                    //Llama las funciones para cerrar el div de archivos y mapa
                    cerrar_ventana_archivo();
                    cerrar_ventana_map();


                } else {
                    if (datos.estado === 'danger') {
                        //Muestra un mensaje de error en el Modal
                        alert_notificacion_agregar_publicacion.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    }
                }
            })
            .catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_notificacion_agregar_publicacion.innerHTML = mensaje_alert_fijo("danger", e);
            });
    });


    //Función para obtener y cargar datos de publicaciones
    function getDataPublicacion() {

        //Elimina cualquier alerta previa 
        alert_notificacion_informacion.innerHTML = '';

        var fecha_inicio = document.getElementById("fecha_inicio");
        var cant_registro = document.getElementById("cant_registro");
        var card_publicacion = document.getElementById("card_publicacion");
        var pagina = pagina_actual;

        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('cant_registro', cant_registro.value);
        formData.append('fecha', fecha_inicio.value);
        formData.append('numero_pagina', pagina);
        fetch('lista_publicacion.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convierte la respuesta a JSON
            .then(datos => {
                if (datos.estado !== 'danger') {
                    card_publicacion.innerHTML = datos.card;
                    cant_actual_registros = datos.cantidad_actual;
                    document.getElementById("lbl-totalResultado").innerHTML = datos.registro;
                    document.getElementById("lbl-totalPagina").innerHTML = datos.pagina;
                    document.getElementById("nav-paginacion").innerHTML = datos.paginacion;
                } else {
                    // Muestra un mensaje de alerta si hubo un error
                    alert_notificacion_informacion.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }


            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_notificacion_informacion.innerHTML = mensaje_alert_fijo("danger", e);
            });
    }



    //Funcion para cambiar la pagina y recargar los datos
    function nextPagePublicacion(pagina) {
        pagina_actual = pagina;
        getDataPublicacion();
    }
</script>
