<?php

//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/consultas_notificacion.php");
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../config/funciones/funciones_session.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_token.php");
include("../../config/config_define.php");

$mensaje_error = "";
$tipo_usuario = "";

try {
    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario
    $usuario_inicio_sesion = verificarEntradaDatosSession(['id_usuario', 'tipo_usuario']);
    if ($usuario_inicio_sesion) {

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION['id_usuario'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario es valido
        $usuario_valido = verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario);
        if ($usuario_valido) {

            if ($tipo_usuario == 2) {
                //Se obtiene los datos del emprendedor en caso que sea uno
                $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
                if (!empty($usuario_emprendedor)) {
                    //Las variables se utilizan para que el usuario emprendedor pueda ir a su perfil desde el navbar
                    $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];
                    $id_usuario_emprendedor_token = hash_hmac('sha1', $id_usuario_emprendedor, KEY_TOKEN);
                }
            }
        } else {
            //Si el usuario no es valido se destruye todos los elementos aunque existan y redirige al inicio de sesion 
            unset($_SESSION['id_usuario']);
            unset($_SESSION['tipo_usuario']);
            header("Location:../../paginas/iniciar_sesion/pagina_iniciar_sesion.php");
        }
    } else {
        //Si no se encuentran todos los datos necesarios de la sesion se destruye todos los elementos aunque existan o no y redirige al inicio de sesion 
        unset($_SESSION['id_usuario']);
        unset($_SESSION['tipo_usuario']);
        header("Location:../../paginas/iniciar_sesion/pagina_iniciar_sesion.php");
    }
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
    <title>Proyecto Emprendedor Notificaciones</title>

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
            //Si el tipo de usuario no es valido o no esta definido, destruye las variables de sesion y redirige al inicio de sesion 
            unset($_SESSION['id_usuario']);
            unset($_SESSION['tipo_usuario']);
            header("Location:../../paginas/iniciar_sesion/pagina_iniciar_sesion.php");
    }
    ?>


    <!--Separa el contenido principal de la pagina del pie de pagina.-->
    <main>
        <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
        <div class="container">
            <?php
            if (empty($mensaje_error)) { ?>

                <!--Titulo de la seccion-->
                <h1 class="text-center">Mis Notificaciones</h1>

                <div class="row align-items-center justify-content-center">

                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="row">

                            <!--Filtro por fecha que se recibio la notificacion-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" for="cant_dias">Mostrar:</span>
                                    <select class="form-select" name="cant_dias" id="cant_dias">
                                        <option value="0">Todas las fechas</option>
                                        <option value="5">Últimos 5 días</option>
                                        <option value="10">Últimos 10 días</option>
                                        <option value="20">Últimos 20 días</option>
                                        <option value="40">Últimos 40 días</option>
                                        <option value="60">Últimos 60 días</option>
                                    </select>
                                </div>
                            </div>


                            <!--Filtro por estado de la notificacion-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" for="num_estado">Estado:</span>
                                    <select class="form-select" name="num_estado" id="num_estado">
                                        <option value="todos" selected>Todos</option>
                                        <option value="1">Leidos</option>
                                        <option value="0">Sin leer</option>

                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-12 col-sm-10 col-md-8 col-lg-7">
                        <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                        <div id="alert_notificacion"></div>
                        <div class="overflow-auto m-2" id="div_nav_todas_notificaciones" style="max-height: 400px;">
                            <!--Contenedor con las cards que contiene las notificaciones-->
                            <div class="row" id="cards_notificaciones" style="margin-right:0;"></div>
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
    <script src="../../config/js/funciones.js"></script>
    <script src="../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <?php require("../../template/footer.php"); ?>
    <?php require("modal_eliminar_notificacion.php"); ?>


</body>

</html>


<script>
    var div_nav_todas_notificaciones = document.getElementById("div_nav_todas_notificaciones");
    var cards_notificaciones = document.getElementById("cards_notificaciones");


    // Inicializa la página actual de notificaciones y un elemento para cargar más notificaciones
    var pagina_todas_notificaciones = 1;
    var cargar_todas_notificaciones = true;
    var notificacion_no_leida;


    // Función para agregar eventos a los botones de notificaciones no leídas
    function agregarEventoBotones() {
        // Selecciona las notificaciones no leídas que no tienen el evento agregado
        notificacion_no_leida = document.querySelectorAll('.notificacion_no_leida:not(.evento-agregado)');
        notificacion_no_leida.forEach(card => {
            // Agrega el evento mouseove' para modificar la notificación
            card.addEventListener('mouseover', modificar_una_notificacion);
        });
    }


    // Carga inicial de datos de notificacion
    getDataTodasMisNotificaciones();

    // Evento de scroll para cargar más publicaciones cuando se llega al fondo del contenedor
    div_nav_todas_notificaciones.addEventListener('scroll', function() {
        // Verifica si el usuario ha llegado al fondo del contenedor
        if (div_nav_todas_notificaciones.scrollHeight - div_nav_todas_notificaciones.scrollTop === div_nav_todas_notificaciones.clientHeight && cargar_todas_notificaciones) {
            pagina_todas_notificaciones++; // Incrementa la página actual
            getDataTodasMisNotificaciones(); // Carga más notificaciones
        }
    });


    //Agrega un evento cada vez que se cambie el estado la pagina actual vuelve a 1 y se llama a la funcion de notificaciones
    document.getElementById("num_estado").addEventListener("change", function() {
        cards_notificaciones.innerHTML = ""; //Limpia el elemento cards de las notificaciones
        pagina_todas_notificaciones = 1; //Reinicia la pagina a 1
        getDataTodasMisNotificaciones(); //Llama a la funcion para obtener los datos de las notificaciones
    });

    //Agrega un evento cada vez que la cantidad de dias cambie la pagina actual vuelve a 1 y se llama a la funcion de notificaciones
    document.getElementById("cant_dias").addEventListener("change", function() {
        cards_notificaciones.innerHTML = ""; //Limpia el elemento cards de las notificaciones
        pagina_todas_notificaciones = 1; //Reinicia la pagina a 1
        getDataTodasMisNotificaciones(); //Llama a la funcion para obtener los datos de las notificaciones
    });


    //Función para obtener y cargar datos de las notificaciones
    function getDataTodasMisNotificaciones() {

        var estado = document.getElementById("num_estado");
        var pagina = pagina_todas_notificaciones;
        var cant_dias = document.getElementById("cant_dias");

        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('estado', estado.value);
        formData.append('cant_dias', cant_dias.value);
        formData.append('numero_pagina', pagina);
        fetch(`lista_notificaciones.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convierte la respuesta a JSON
            .then(datos => {
                if (datos.estado != "danger") {

                    // Agrega las nuevas notificaciones al contenedor
                    cards_notificaciones.innerHTML += datos.cards_notificaciones;
                    cargar_todas_notificaciones = datos.cargar_mas_notificaciones;
                    agregarEventoBotones(); // Agrega eventos a las nuevas notificaciones
                } else {
                    // Muestra un mensaje de error en el contenedor
                    div_nav_todas_notificaciones.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }

            })
            .catch(error => {
                // Muestra un mensaje error de la solicitud
                div_nav_todas_notificaciones.innerHTML = mensaje_alert_fijo("danger", error);

            });
    }


    // Función para modificar una notificación cuando se pasa el ratón sobre ella
    function modificar_una_notificacion(event) {
        var id_notificacion = this.getAttribute('data-id'); // Obtiene el ID de la notificación


        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('id_notificacion', id_notificacion);

        fetch(`modificar_notificacion.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convierte la respuesta a JSON
            .then(datos => {
                if (datos.estado != "danger") {
                    var div_circulo = document.getElementById("div_circulo_" + id_notificacion);
                    if (div_circulo) {
                        div_circulo.remove(); // Elimina el indicador de no leído
                        this.removeEventListener('mouseover', modificar_una_notificacion); // Elimina el evento 'mouseover'
                        this.classList.remove("notificacion_no_leida"); // Cambia la clase a leída
                        this.classList.add("notificacion_leida");

                        var navbar_notificacion = document.getElementById("navbar_notificacion");
                        navbar_notificacion.innerHTML = datos.notificacion_navbar; // Actualiza el contenido del navbar
                    }
                } else {
                    // Muestra un mensaje de error en el contenedor
                    div_nav_todas_notificaciones.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);
                }
            })
            .catch(error => {
                // Muestra un mensaje de error en caso de falla en la solicitud
                div_nav_todas_notificaciones.innerHTML = mensaje_alert_fijo("danger", error);
            });
    }
</script>