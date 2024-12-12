<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/consultas_notificacion.php");
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../config/funciones/funciones_session.php");
include("../../config/funciones/funciones_token.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/config_define.php");


$mensaje_error = "";

$estados_producto = array();
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

        //Se obtiene los estados que puede tener un producto
        $estados_producto = obtenerEstadosProducto($conexion);

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
    <title>Proyecto Emprendedor Mis Preguntas</title>

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
        <div class="container-fluid">
            <?php
            if (empty($mensaje_error)) { ?>

                <!--Titulo de la seccion-->
                <h1 class="text-center">Mis preguntas</h1>

                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="row">

                            <!--Campo de busqueda por el nombre del producto-->
                            <div class="col-12 col-sm-auto col-md-auto col-lg-3 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="search" class="form-control" placeholder="Nombre del producto" aria-label=">Nombre del producto" name="campo_buscar" id="campo_buscar">
                                </div>
                            </div>

                            <!--Filtro por estado del producto-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" for="num_estado">Estado de la publicacion:</span>
                                    <select class="form-select" name="num_estado" id="num_estado">
                                        <option value="0" selected>Todos</option>
                                        <?php foreach ($estados_producto as $estado) { ?>
                                            <option value="<?php echo $estado['id_estado_producto']; ?>"><?php echo $estado['estado']; ?></option>
                                        <?php } ?>

                                    </select>
                                </div>
                            </div>


                            <!--Filtro por fecha que se hizo la pregunta-->
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

                            <!--Selector de cantidad de preguntas a mostrar-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" for="cant_registro">Cantidad de preguntas:</span>
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
                        <div id="alert_notificacion_pregunta"></div>
                    </div>


                    <!--Contenedor con las cards que contiene las preguntas hechas-->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div id="card_pregunta"></div>
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
    <script src="../../config/js/funciones.js"></script>
    <script src="../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <?php require("modal_eliminar_pregunta.php"); ?>
    <?php require("modal_agregar_pregunta.php"); ?>
    <?php require("../../template/footer.php"); ?>


</body>

</html>

<script>
    //Inicializa la pagina actual y la cantidad actual de preguntas que se ve en la interfaz 
    var pagina_actual = 1;
    var cant_actual_registros;
    var alert_notificacion_pregunta = document.getElementById('alert_notificacion_pregunta');

    //Llama a la funcion para obtener datos de las preguntas hechas
    getDataMisPreguntas();



    //Agrega un evento cada vez que el campo buscar producto cambie la pagina actual vuelve a 1 y se llama a la funcion de preguntas hechas
    document.getElementById("campo_buscar").addEventListener("input", function() {

        pagina_actual = 1; //Reinicia la pagina a 1
        getDataMisPreguntas(); //Llama a la funcion para obtener los datos de las preguntas hechas

    });


    //Agrega un evento cada vez que se cambie el estado la pagina actual vuelve a 1 y se llama a la funcion de preguntas hechas
    document.getElementById("num_estado").addEventListener("change", function() {

        pagina_actual = 1; //Reinicia la pagina a 1
        getDataMisPreguntas(); //Llama a la funcion para obtener los datos de las preguntas hechas

    });


    //Agrega un evento cada vez que la cantidad de registro cambie la pagina actual vuelve a 1 y se llama a la funcion de preguntas hechas
    document.getElementById("cant_registro").addEventListener("change", function() {

        pagina_actual = 1; //Reinicia la pagina a 1
        getDataMisPreguntas(); //Llama a la funcion para obtener los datos de las preguntas hechas

    });

    //Agrega un evento cada vez que la cantidad de dias cambie la pagina actual vuelve a 1 y se llama a la funcion de preguntas hechas
    document.getElementById("cant_dias").addEventListener("change", function() {

        pagina_actual = 1; //Reinicia la pagina a 1
        getDataMisPreguntas(); //Llama a la funcion para obtener los datos de las preguntas hechas

    });



    //Función para obtener y cargar datos preguntas hechas
    function getDataMisPreguntas() {

        //Elimina cualquier alerta previa 
        alert_notificacion_pregunta.innerHTML = "";

        var campo_buscar = document.getElementById("campo_buscar");
        var cant_registro = document.getElementById("cant_registro");
        var estado = document.getElementById("num_estado");
        var cant_dias = document.getElementById("cant_dias");
        var cards_pregunta = document.getElementById("card_pregunta");
        var pagina = pagina_actual;

        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('campo_buscar', campo_buscar.value);
        formData.append('cant_registro', cant_registro.value);
        formData.append('estado', estado.value);
        formData.append('cant_dias', cant_dias.value);
        formData.append('numero_pagina', pagina);

        fetch('lista_preguntas.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convierte la respuesta a JSON
            .then(datos => {
                if (datos.estado !== 'danger') {
                    cards_pregunta.innerHTML = datos.datos;
                    cant_actual_registros = datos.cantidad_actual;
                    document.getElementById("lbl-totalResultado").innerHTML = datos.registro;
                    document.getElementById("lbl-totalPagina").innerHTML = datos.pagina;
                    document.getElementById("nav-paginacion").innerHTML = datos.paginacion;
                } else {
                    // Muestra un mensaje de alerta si hubo un error
                    alert_notificacion_pregunta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }
            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_notificacion_pregunta.innerHTML = mensaje_alert_fijo("danger", e);
            });
    }


    //Funcion para cambiar la pagina y recargar los datos
    function nextPagePregunta(pagina) {
        pagina_actual = pagina;
        getDataMisPreguntas();
    }
</script>