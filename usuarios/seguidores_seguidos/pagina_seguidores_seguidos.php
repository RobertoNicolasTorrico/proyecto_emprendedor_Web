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

$tipo_usuario = "";
$es_usuario_emprendedor = false;

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
                    //Variable que se utiliza para verificar que sea un usuario emprendedor que inicio sesion
                    $es_usuario_emprendedor = true;
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
    <title>Proyecto Emprendedor Seguidores</title>

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
        <?php if (empty($mensaje_error)) {    ?>
            <div class="row align-items-center justify-content-center" style="--bs-gutter-x: 0;">


                <div class="col-12 col-sm-10 col-md-8 col-lg-6">
                    <!--Define una navegación por pestañas para alternar entre la vista de seguidoes y la vista de seguidos en caso que sea un emprendedor.-->
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <button class="nav-link active" id="nav_seguidos_tab" data-bs-toggle="tab" data-bs-target="#div_nav_seguidos" type="button" role="tab" aria-controls="div_nav_seguidos" aria-selected="true">Seguidos</button>
                            <!--Verifica que sea un emprendedor que inicio sesion para mostrar a los usuarios que lo siguen-->
                            <?php if ($es_usuario_emprendedor) { ?>
                                <button class="nav-link" id="nav_seguidores_tab" data-bs-toggle="tab" data-bs-target="#div_nav_seguidores" type="button" role="tab" aria-controls="div_nav_seguidores" aria-selected="false">Seguidores</button>
                            <?php } ?>
                        </div>
                    </nav>

                    <!--Contenedor para el contenido de las pestañas.La pestaña de seguidos esta activa por defecto.-->
                    <div class="tab-content" id="nav-tabContent">

                        <!--Contenedor de los seguidos.-->
                        <div class="tab-pane fade show active overflow-y-auto" style="max-height: 600px;" id="div_nav_seguidos" role="tabpanel" aria-labelledby="nav_seguidos_tab" tabindex="0">

                            <h1 class='text-center'>Lista de emprendedores que seguis</h1>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="search" class="form-control" placeholder="Buscar por nombre del emprendimiento/usuario" aria-label="Buscar emprendedor" name="buscar_emprendedor" id="txt_buscar_emprendedor">
                            </div>
                            <div class="row justify-content-center" id="cards_seguidos" style="margin-right:0"></div>


                        </div>

                        <!--Contenedor de los seguidores.-->
                        <!--Se verifica que un usuario emprendedor inicio para mostrar la pestaña-->
                        <?php if ($es_usuario_emprendedor) { ?>
                            <div class="tab-pane fade overflow-y-auto" style="max-height: 600px;" id="div_nav_seguidores" role="tabpanel" aria-labelledby="nav_seguidores_tab" tabindex="0">
                                <h1 class='text-center'>Lista de usuarios que te siguen</h1>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="search" class="form-control" placeholder="Buscar por nombre de usuario" aria-label="Buscar seguidor" name="buscar_seguidor" id="txt_buscar_seguidor">
                                </div>

                                <div class="row justify-content-center" id="cards_seguidores" style="margin-right:0"></div>

                            </div>

                        <?php } ?>


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


    <!--Contenedor de los productos.-->
    <script src="../../config/js/funciones.js"></script>
    <script src="../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <?php if ($es_usuario_emprendedor) {
        require("modal_eliminar_seguidor.php");
    } ?>
    <?php require("modal_eliminar_seguidor_emprendedor.php"); ?>
    <?php require("../../template/footer.php"); ?>

</body>

</html>
<script>
    const es_usuario_emprendedor = <?php echo json_encode($es_usuario_emprendedor); ?>;


    // Elementos para la navegación y las cards de seguidos
    var div_nav_seguidos = document.getElementById("div_nav_seguidos");
    var cards_seguidos = document.getElementById("cards_seguidos");
    // Variables de control para la paginación de seguidos
    var pagina_todas_cards_seguidos = 1;
    var cargar_cards_seguidos = true;

    //Variable de control de busqueda de seguidos
    var busqueda_activa_seguidos = false;

    var cant_total_seguidos = 0;


    // Carga inicial de datos de seguidos
    getDataTodasMisSeguidos();

    if (es_usuario_emprendedor) {


        // Elementos para la navegación y las cards de seguidores
        var div_nav_seguidores = document.getElementById("div_nav_seguidores");
        var cards_seguidores = document.getElementById("cards_seguidores");

        // Variables de control para la paginación de seguidores
        var pagina_todas_cards_seguidores = 1;
        var cargar_cards_seguidores = true;

        //Variable de control de busqueda de seguidores
        var busqueda_activa_seguidores = false;
        var cant_total_seguidores = 0;

        // Carga inicial de datos de seguidores
        getDataTodasMiSeguidores();


        // Evento de scroll para cargar más seguidores cuando se llega al fondo del contenedor
        div_nav_seguidores.addEventListener('scroll', function() {
            // Verifica si el usuario ha llegado al fondo del contenedor de seguidores
            if (div_nav_seguidores.scrollHeight - div_nav_seguidores.scrollTop === div_nav_seguidores.clientHeight && cargar_cards_seguidores) {
                pagina_todas_cards_seguidores++; // Incrementa la página actual de seguidores
                getDataTodasMiSeguidores(); // Carga más seguidores
            }
        });

        //Agrega un evento cada vez que el campo buscar seguidor cambie la pagina actual vuelve a 1 y se llama a la funcion de seguidores
        document.getElementById('txt_buscar_seguidor').addEventListener('input', function() {
            cards_seguidores.innerHTML = ""; //Limpia el elemento cards de las seguidores
            pagina_todas_cards_seguidores = 1; //Reinicia la pagina de seguidores a 1
            getDataTodasMiSeguidores(); //Llama a la funcion para obtener los datos de seguidores
        });

    }


    //Función para obtener y cargar datos de seguidores
    function getDataTodasMiSeguidores() {

        var pagina = pagina_todas_cards_seguidores;
        var txt_buscar_seguidor = document.getElementById("txt_buscar_seguidor");

        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('numero_pagina', pagina);
        formData.append('campo_buscar_seguidor', txt_buscar_seguidor.value);

        fetch(`lista_seguidores.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convierte la respuesta a JSON
            .then(datos => {
                if (datos.estado != "danger") {
                    //Si la pagina actual es igual 1 se va a restablacer la cantidad de seguidores que se ve en la interfaz del emprendedor
                    if (pagina == 1) {
                        cant_total_seguidores = datos.cant_seguidores;
                    }
                    //Se verifica si el usuario esta buscando aun seguidor
                    if (datos.busqueda_activa) {
                        //Se restablece el contenedor donde estan las cards de los seguidores
                        cards_seguidores.innerHTML = datos.cards_seguidores;
                    } else {
                        // Añade nuevas cards de seguidores al contenedor
                        cards_seguidores.innerHTML += datos.cards_seguidores;
                    }
                    busqueda_activa_seguidores = datos.busqueda_activa;
                    // Actualiza la variable de control según si hay más seguidores para cargar
                    cargar_cards_seguidores = datos.cargar_mas_seguidores;
                } else {
                    // Muestra un mensaje de error en el contenedor
                    div_nav_seguidores.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }
            })
            .catch(error => {
                // Maneja errores de la solicitud
                div_nav_seguidores.innerHTML = mensaje_alert_fijo("danger", error);
            });

    }


    //Agrega un evento cada vez que el campo buscar emprendedor cambie la pagina actual vuelve a 1 y se llama a la funcion de seguidos
    document.getElementById('txt_buscar_emprendedor').addEventListener('input', function() {
        cards_seguidos.innerHTML = ""; //Limpia el elemento cards de las seguidos
        pagina_todas_cards_seguidos = 1; //Reinicia la pagina de seguidos a 1
        getDataTodasMisSeguidos(); //Llama a la funcion para obtener los datos de seguidos
    });


    // Evento de scroll para cargar más seguidos cuando se llega al fondo del contenedor
    div_nav_seguidos.addEventListener('scroll', function() {

        // Verifica si el usuario ha llegado al fondo del contenedor de seguidos
        if (div_nav_seguidos.scrollHeight - div_nav_seguidos.scrollTop === div_nav_seguidos.clientHeight && cargar_cards_seguidos) {
            pagina_todas_cards_seguidos++; // Incrementa la página actual de seguidos
            getDataTodasMisSeguidos(); // Carga más seguidos
        }
    });



    function getDataTodasMisSeguidos() {

        var campo_buscar_emprendedor = document.getElementById("txt_buscar_emprendedor");
        var pagina = pagina_todas_cards_seguidos;

        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('numero_pagina', pagina);
        formData.append('campo_buscar_emprendedor', campo_buscar_emprendedor.value);
        fetch(`lista_seguidos.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convierte la respuesta a JSON
            .then(datos => {
                if (datos.estado != "danger") {

                    //Si la pagina actual es igual 1 se va a restablacer la cantidad de seguidos que se ve en la interfaz del usuario
                    if (pagina == 1) {
                        cant_total_seguidos = datos.cant_seguidos;
                    }

                    //Se verifica si el usuario esta buscando aun emprendedor
                    if (datos.busqueda_activa) {

                        //Se restablece el contenedor donde estan las cards de los seguidos
                        cards_seguidos.innerHTML = datos.cards_seguidos;
                    } else {

                        // Añade nuevas cards de seguidos al contenedor
                        cards_seguidos.innerHTML += datos.cards_seguidos;
                    }
                    busqueda_activa_seguidos = datos.busqueda_activa;

                    // Actualiza la variable de control según si hay más seguidos para cargar
                    cargar_cards_seguidos = datos.cargar_mas_seguidos;
                } else {
                    // Muestra un mensaje de error en el contenedor
                    div_nav_seguidos.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }

            })
            .catch(error => {
                // Maneja errores de la solicitud
                div_nav_seguidos.innerHTML = mensaje_alert_fijo("danger", error);
            });
    }
</script>