<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../../config/consultas_bd/consultas_notificacion.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/config_define.php");


$estados_producto = array();
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


    //Se obtiene los estados que puede tener un producto
    $estados_producto = obtenerEstadosProducto($conexion);


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
    <title>Proyecto Emprendedor Preguntas Recibidas</title>

    <!--Enlace al archivo de estilos propios del proyecto-->
    <link href="../../../../config/css/estilos.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Bootstrap-->
    <link href="../../../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de FontAwesome para iconos-->
    <link href="../../../../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">

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
                <h1 class="text-center">Preguntas recibidas</h1>

                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="row">

                            <!--Campo de busqueda por el nombre del producto-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="search" class="form-control" placeholder="Nombre del producto" aria-label="Buscar producto" name="campo_buscar" id="campo_buscar">
                                </div>
                            </div>

                            <!--Campo de busqueda por el nombre de usuario-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="search" class="form-control" placeholder="Nombre de usuario" aria-label="Buscar usuario" name="campo_buscar_usuario" id="campo_buscar_usuario">
                                </div>
                            </div>

                            <!--Filtro por estado del producto-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" for="num_estado">Estado del producto:</span>
                                    <select class="form-select" name="num_estado" id="num_estado">
                                        <option value="0" selected>Todos</option>
                                        <?php foreach ($estados_producto as $estado) { ?>
                                            <option value="<?php echo $estado['id_estado_producto']; ?>"><?php echo $estado['estado']; ?></option>
                                        <?php } ?>

                                    </select>
                                </div>
                            </div>

                            <!--Filtro por fecha que se recibio la pregunta-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text">Preguntas recibidas:</span>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control">
                                </div>
                            </div>

                            <!--Filtro por el estado de las preguntas-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" for="filtro_preguntas">Filtrar preguntas:</span>
                                    <select class="form-select" name="filtro_preguntas" id="filtro_preguntas">
                                        <option value="Todas">Todas</option>
                                        <option value="Respondidas">Respondidas</option>
                                        <option value="NoRespondidas">No Respondidas</option>

                                    </select>
                                </div>
                            </div>

                            <!--Selector de cantidad de preguntas y respuestas a mostrar-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" for="cant_registro">Mostrar:</span>
                                    <select class="form-select" name="cant_registro" id="cant_registro">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                        <div id="alert_notificacion_respuesta"></div>
                    </div>

                    <!--Contenedor con las cards que contiene el producto con las preguntas recibidas y con las respuestas hechas-->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="row justify-content-center" id="cards_producto"></div>
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
    <script src="../../../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <?php require("modal_eliminar_respuesta.php"); ?>
    <?php require("modal_agregar_respuesta.php"); ?>
    <?php require("../../../../template/footer.php"); ?>

</body>

</html>


<script>
    //Inicializa la pagina actual y los modals para agregar y eliminar respuesta
    var pagina_actual = 1;
    const alert_notificacion_respuesta = document.getElementById('alert_notificacion_respuesta');
    const ventanaModalEliminar = new bootstrap.Modal('#eliminaModal');
    const ventanaModalAgregar = new bootstrap.Modal('#agregarModal');

    //Llama a la funcion para obtener datos de productos con las preguntas recibidas
    getDataProductosPreguntas();


    //Agrega un evento cada vez que la fecha cambie la pagina actual vuelve a 1 y se llama a la funcion de productos con las preguntas recibidas
    document.getElementById("fecha_inicio").addEventListener("change", function() {

        pagina_actual = 1; //Reinicia la pagina a 1
        getDataProductosPreguntas(); //Llama a la funcion para obtener datos de productos con las preguntas recibidas

    });

    //Agrega un evento cada vez que el campo buscar usuario cambie la pagina actual vuelve a 1 y se llama a la funcion de productos con las preguntas recibidas
    document.getElementById("campo_buscar_usuario").addEventListener("input", function() {

        pagina_actual = 1; //Reinicia la pagina a 1
        getDataProductosPreguntas(); //Llama a la funcion para obtener datos de productos con las preguntas recibidas

    });

    //Agrega un evento cada vez que el campo buscar producto cambie la pagina actual vuelve a 1 y se llama a la funcion de productos con las preguntas recibidas
    document.getElementById("campo_buscar").addEventListener("input", function() {
        pagina_actual = 1; //Reinicia la pagina a 1

        getDataProductosPreguntas(); //Llama a la funcion para obtener datos de productos con las preguntas recibidas

    });

    //Agrega un evento cada vez que se cambie el estado la pagina actual vuelve a 1 y se llama a la funcion de productos con las preguntas recibidas
    document.getElementById("num_estado").addEventListener("change", function() {

        pagina_actual = 1; //Reinicia la pagina a 1
        getDataProductosPreguntas(); //Llama a la funcion para obtener datos de productos con las preguntas recibidas

    });

    //Agrega un evento cada vez que la cantidad de registro cambie la pagina actual vuelve a 1 y se llama a la funcion de productos con las preguntas recibidas
    document.getElementById("cant_registro").addEventListener("change", function() {

        pagina_actual = 1; //Reinicia la pagina a 1
        getDataProductosPreguntas(); //Llama a la funcion para obtener datos de productos con las preguntas recibidas

    });

    //Agrega un evento cada vez ue se cambie el filtro de pregunta cambie la pagina actual vuelve a 1 y se llama a la funcion de productos con las preguntas recibidas
    document.getElementById("filtro_preguntas").addEventListener("change", function() {

        pagina_actual = 1; //Reinicia la pagina a 1
        getDataProductosPreguntas(); //Llama a la funcion para obtener datos de productos con las preguntas recibidas

    });



    //Manejo del envio del formulario para agregar una respuesta a una pregunta recibida
    form_agregar_respuesta.addEventListener("submit", (e) => {

        //Previene el envio por defecto del formulario
        e.preventDefault();

        //Elimina cualquier alerta previa 
        alert_respuesta_modal = document.getElementById("alert_respuesta_modal");
        alert_respuesta_modal.innerHTML = "";

        var respuesta_pregunta = document.getElementById('respuesta_pregunta');
        var id_pregunta = document.getElementById('id_pregunta');
        var id_producto = document.getElementById('id_producto');


        //Valida que el campo respuesta no este vacio
        if (validarCampoVacio([respuesta_pregunta])) {
            alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", "Por favor complete el campo respuesta");
            return false;
        }

        //Valida que los campos ocultos solo contengan numeros
        if (!isNaN(id_pregunta) || !isNaN(id_producto)) {
            alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros");
            return false;
        }

        //Valida que el campo no tenga espacios al inicio o al final de la cadena
        if (respuesta_pregunta.value.trim() != respuesta_pregunta.value) {
            alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", "La respuesta no puede tener espacios en el inicio o al final");
            return false;
        }

        //Valida que la longitud del campo sea valida 
        if (!validarCantLengthInput(respuesta_pregunta)) {
            alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", "El campo respuesta debe tener entre 1 y 255 caracteres");
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData(form_agregar_respuesta);
        fetch('alta_respuesta.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())// Convierte la respuesta a JSON
            .then(datos => {
                if (datos.estado == "success") {

                    // Actualiza los datos de las preguntas
                    getDataProductosPreguntas();

                    //Muestra un mensaje en la interfaz del usuario
                    alert_notificacion_respuesta.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                    //Cierra el modal 
                    ventanaModalAgregar.hide();
                } else {
                    if (datos.estado == "danger") {
                        //Muestra un mensaje de error en el Modal
                        alert_respuesta_modal.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    }
                }
            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", e);
            });
    });


    //Manejo del envio del formulario para eliminar una respuesta
    form_eliminar_respuesta.addEventListener("submit", (e) => {
        //Previene el envio por defecto del formulario
        e.preventDefault();

        //Elimina cualquier alerta previa 
        alert_eliminar_modal = document.getElementById("alert_eliminar_modal");
        alert_eliminar_modal.innerHTML = "";

        var id_pregunta = document.getElementById('id_pregunta');
        var id_producto = document.getElementById('id_producto');

        //Valida que los campos ocultos solo contengan numeros
        if (!isNaN(id_pregunta) || !isNaN(id_producto)) {
            alert_eliminar_modal.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
            return false;
        }

        // Envío del formulario usando fetch
        const formData = new FormData(form_eliminar_respuesta);
        fetch('baja_respuesta.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado == "success") {

                    // Actualiza los datos de las preguntas
                    getDataProductosPreguntas();

                    //Muetra un mensaje en la interfaz del usuario
                    alert_notificacion_respuesta.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                    //Cierra el modal 
                    ventanaModalEliminar.hide();

                } else {
                    if (datos.estado == "danger") {
                        //Muestra un mensaje de error en el Modal
                        alert_eliminar_modal.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    }
                }

            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_eliminar_modal.innerHTML = mensaje_alert_fijo("danger", e);
            });

    });




    //Función para obtener y cargar datos de productos con las preguntas recibidas 
    function getDataProductosPreguntas() {
        var campo_buscar_producto = document.getElementById("campo_buscar");
        var campo_buscar_usuario = document.getElementById("campo_buscar_usuario");
        var fecha_inicio = document.getElementById("fecha_inicio");
        var estado = document.getElementById("num_estado");
        var filtro_preguntas = document.getElementById("filtro_preguntas");
        var cant_registro = document.getElementById("cant_registro");
        var cards_producto = document.getElementById("cards_producto");
        var pagina = pagina_actual;

        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('estado', estado.value);
        formData.append('filtro_preguntas', filtro_preguntas.value);
        formData.append('campo_buscar_producto', campo_buscar_producto.value);
        formData.append('fecha', fecha_inicio.value);
        formData.append('campo_buscar_usuario', campo_buscar_usuario.value);
        formData.append('cant_registro', cant_registro.value);
        formData.append('numero_pagina', pagina);

        fetch('lista_respuesta.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convierte la respuesta a JSON
            .then(datos => {
                if (datos.estado !== 'danger') {
                    cards_producto.innerHTML = datos.cards;
                    document.getElementById("lbl-totalResultado").innerHTML = datos.registro;
                    document.getElementById("lbl-totalPagina").innerHTML = datos.pagina;
                    document.getElementById("nav-paginacion").innerHTML = datos.paginacion;
                } else {
                    // Muestra un mensaje de alerta si hubo un error
                    alert_notificacion_respuesta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }

            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_notificacion_respuesta.innerHTML = mensaje_alert_fijo("danger", e);
            });

    }

    //Funcion para cambiar la pagina y recargar los datos
    function nextPagePreguntasRespuesta(pagina) {
        pagina_actual = pagina;
        getDataProductosPreguntas();
    }
</script>