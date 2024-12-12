<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/consultas_notificacion.php");
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/funciones/funciones_token.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_session.php");
include("../../config/config_define.php");


$tipo_usuario = "";
$mensaje_error = "";
try {
    //Establecer la sesion
    session_start();

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica los datos de sesion del usuario
    $usuario_inicio_sesion = verificarEntradaDatosSession(['id_usuario', 'tipo_usuario']);

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
    <title>Proyecto Emprendedor Búsqueda de emprendedores</title>

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
                <h1 class="text-center">Buscar emprendedores</h1>
                <div class="row" style="margin-right:0px">

                    <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                    <div id="alert_buscar_emprendedor"></div>

                    <!-- Filtro para dispositivos de escritorio -->
                    <div class="col-lg-3 d-none d-lg-block">


                        <!-- Contenedor que tiene un accordion para mostrar los filtro de busqueda para dispositivos escritorio-->
                        <div class="accordion" id="accordionPanelFiltroDesktop">


                            <!-- Contenedor que tiene el item del accordion que permite al usuario ordenar los resultados por fecha de registo del emprendedor usando botones de radio.  -->
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
                                            <label class="form-check-label" for="fecha_mas_recientes_desktop">Fecha de registro: más reciente</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="radio_ordenar_por_desktop" id="fecha_mas_antigua_desktop" value="2">
                                            <label class="form-check-label" for="fecha_mas_antigua_desktop">Fecha de registro: más antigua</label>
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

                        </div>
                        <!--Boton que permite restablecer los valores de busqueda -->
                        <button type="button" class="btn btn-outline-dark mt-3" id="restablecer_filtro_desktop">Restablecer</button>
                    </div>


                    <!-- Filtro para dispositivos de mobiles -->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-12 col-lg-8">
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="search" class="form-control" placeholder="Buscar por nombre del emprendimiento/usuario" aria-label="Buscar emprendedor" name="buscar_emprendedor" id="txt_buscar_emprendedor">
                                </div>
                            </div>
                            <!-- Botón y offcanvas para dispositivos móviles -->
                            <div class="col-12 col-sm-12 col-md-12 d-lg-none mt-2">

                                <!-- Botón del filtro para dispositivos móviles -->
                                <button class="btn btn-outline-dark mb-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltro" aria-controls="offcanvasFiltro">
                                Filtros
                                </button>

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

                                            <!-- Contenedor que tiene el item del accordion que permite al usuario ordenar los resultados por fecha de registo del emprendedor usando botones de radio.  -->
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
                                                            <label class="form-check-label" for="fecha_mas_recientes_mobile">Fecha de registro: más reciente</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_ordenar_por_mobile" id="fecha_mas_antigua_mobile" value="2">
                                                            <label class="form-check-label" for="fecha_mas_antigua_mobile">Fecha de registro: más antigua</label>
                                                        </div>

                                                        <!-- Resto de tus opciones de ordenar por para dispositivos móviles -->
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

                                        </div>
                                        <!--Boton que permite restablecer los valores de busqueda -->
                                        <button type="button" class="btn btn-outline-dark mt-3" id="restablecer_filtro_mobile">Restablecer</button>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="row" id="card_emprendedor"></div>
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
    var alert_buscar_emprendedor = document.getElementById("alert_buscar_emprendedor");
    const inicio_sesion = <?php echo json_encode($usuario_inicio_sesion); ?>;


    // Carga inicial de datos de los emprendedores
    getDataEmprendedores();


    //Funcion para que un usuario siga a un usuario emprendedor
    function seguirUsuario(event) {
        var perfil_Id = this.getAttribute('data-perfil-id');
        var perfil_Token = this.getAttribute('data-perfil-token');


        // Verifica si el usuario ha iniciado sesión
        if (inicio_sesion) {
            const formData = new FormData();
            fetch(`../../usuarios/seguidores_seguidos/alta_seguir_usuario.php?id=${perfil_Id}&token=${perfil_Token}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(datos => {
                    if (datos.estado !== 'danger') {
                        var div_boton_seguimiento = document.getElementById("div_boton_seguimiento-" + perfil_Id);
                        div_boton_seguimiento.innerHTML = "<button type='button' class='btn btn-outline-danger dejar-seguir-btn' data-perfil-token='" + perfil_Token + "' data-perfil-id='" + perfil_Id + "'>Dejar de seguir</button>";
                        agregarEventoBotonesSeguimiento();
                    } else {
                        //Muestra un mensaje de alert
                        alert('Error al seguir al emprendedor.');
                    }
                })
                .catch(error => {
                    // Muestra un mensaje error de la solicitud
                    alert(error);
                });
        } else {
            //Muestra un mensaje de alert
            alert('Debe iniciar sesión para seguir a un usuario.');
        }
    }

    function dejarSeguirUsuario(event) {
        // Tu código para dejar de seguir al usuario aquí
        var perfil_Id = this.getAttribute('data-perfil-id');
        var perfil_Token = this.getAttribute('data-perfil-token');

        // Verifica si el usuario ha iniciado sesión
        if (inicio_sesion) {
            const formData = new FormData();

            fetch(`../../usuarios/seguidores_seguidos/baja_seguir_usuario.php?id=${perfil_Id}&token=${perfil_Token}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(datos => {
                    if (datos.estado !== 'danger') {
                        var div_boton_seguimiento = document.getElementById("div_boton_seguimiento-" + perfil_Id);
                        div_boton_seguimiento.innerHTML = "<button type='button' class='btn btn-outline-success seguir-btn' data-perfil-token='" + perfil_Token + "' data-perfil-id='" + perfil_Id + "'>Seguir</button>";
                        agregarEventoBotonesSeguimiento();

                    } else {
                        //Muestra un mensaje de alert
                        alert('Error al dejar de seguir al emprendedor.');
                    }
                })
                .catch(error => {
                    //Muestra un mensaje de alert
                    alert(error);
                });
        } else {
            //Muestra un mensaje de alert
            alert('Debe iniciar sesión para dejar de seguir a un usuario.');
        }

    }



    //Función para obtener y cargar datos de emprendedores
    function getDataEmprendedores() {
        var campo_buscar_emprendedor = document.getElementById("txt_buscar_emprendedor");
        var pagina = pagina_actual;
        var ordenamiento;
        if (window.innerWidth < 992) {
            ordenamiento = parseInt(document.querySelector('input[name="radio_ordenar_por_mobile"]:checked').value);

        } else {
            ordenamiento = parseInt(document.querySelector('input[name="radio_ordenar_por_desktop"]:checked').value);
        }

        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('campo_buscar_emprendedor', campo_buscar_emprendedor.value);
        formData.append('numero_pagina', pagina);
        formData.append('num_ordenamiento', ordenamiento);
        formData.append('calificacion', calificacion);
        fetch('lista_emprendedor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {

                if (datos.estado != 'danger') {
                    document.getElementById('card_emprendedor').innerHTML = datos.cards;
                    document.getElementById("nav-paginacion").innerHTML = datos.paginacion;
                    document.getElementById("lbl-totalPagina").innerHTML = datos.pagina;
                    agregarEventoBotonesSeguimiento();
                } else {
                    // Muestra un mensaje de alerta si hubo un error
                    alert_buscar_emprendedor.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);
                }

            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_buscar_emprendedor.innerHTML = mensaje_alert_fijo("danger", e);
            });

    }


    //Funcion para restablecer los valores de busqueda del filtro del desktop a sus valores iniciales ademas de actualizar la lista de emprendedores
    function restablecer_desktop() {
        alert_buscar_emprendedor.innerHTML = "";
        document.getElementById('fecha_mas_recientes_desktop').checked = true;
        document.getElementById('todos_calificacion_desktop').checked = true;
        calificacion = "todos_calificacion";
        actualizarStarRating(0, estrellasDesktop);
        pagina_actual = 1;
        getDataEmprendedores();

    }


    //Funcion para restablecer los valores de busqueda del filtro del mobile a sus valores iniciales ademas de actualizar la lista de emprendedores
    function restablecer_mobile() {
        alert_filtro_offcanvas_mobile = document.getElementById("alert_filtro_offcanvas_mobile");
        alert_filtro_offcanvas_mobile.innerHTML = "";
        document.getElementById('fecha_mas_recientes_mobile').checked = true;
        document.getElementById('todos_calificacion_mobile').checked = true;
        calificacion = "todos_calificacion";
        actualizarStarRating(0, estrellasMobile);
        pagina_actual = 1;
        getDataEmprendedores();
    }



    // Evento clic para cada estrella en la versión de escritorio
    estrellasDesktop.forEach(function(star, index) {
        //Se llama a una la funcion para actualizar la calificacion y obtener una lista de emprendedores
        star.addEventListener("click", function() {
            document.getElementById('num_calificacion_desktop').checked = true;
            manejarClicEstrella(index, estrellasDesktop);
        });
    });

    // Evento clic para cada estrella en la versión móvil
    estrellasMobile.forEach(function(star, index) {
        //Se llama a una la funcion para actualizar la calificacion y obtener una lista de emprendedores
        star.addEventListener("click", function() {
            document.getElementById('num_calificacion_mobile').checked = true;
            manejarClicEstrella(index, estrellasMobile);
        });
    });



    //Agrega un evento cuando se detecta que se haga un click en el elemento de restablecer_filtro_desktop
    document.getElementById('restablecer_filtro_desktop').addEventListener('click', function() {
        //Se llama a la funcion para restablecer los valores de busqueda del filtro y actualiza la lista de emprendedores
        restablecer_desktop();
    });

    //Agrega un evento cuando se detecta que se haga un click en el elemento de restablecer_filtro_mobile
    document.getElementById('restablecer_filtro_mobile').addEventListener('click', function() {
        //Se llama a la funcion para restablecer los valores de busqueda del filtro y actualiza la lista de emprendedores
        restablecer_mobile();
    });


    //Agrega un evento listener a cada elemento dentro del componente radio version desktop
    document.getElementsByName("radio_ordenar_por_desktop").forEach(function(radio) {
        //Agrega un evento cada vez que el numero de ordenamiento cambie la pagina actual vuelve a 1 y se llama a la funcion de emprendedores
        radio.addEventListener('change', function() {
            pagina_actual = 1; //Reinicia la pagina a 1
            getDataEmprendedores(); //Llama a la funcion para obtener los datos del emprendedor

        });
    });

    //Agrega un evento listener a cada elemento dentro del componente radio version mobile
    document.getElementsByName("radio_ordenar_por_mobile").forEach(function(radio) {
        //Agrega un evento cada vez que el numero de ordenamiento cambie la pagina actual vuelve a 1 y se llama a la funcion de emprendedores
        radio.addEventListener('change', function() {
            pagina_actual = 1; //Reinicia la pagina a 1
            getDataEmprendedores(); //Llama a la funcion para obtener los datos del emprendedor

        });
    });


    //Agrega un evento listener a cada elemento dentro del componente radio_buscar_por_calificacion_desktop
    document.getElementsByName("radio_buscar_por_calificacion_desktop").forEach(function(radio) {
        //Agrega un evento cada vez que la calificacion del emprendedor cambie la pagina actual vuelve a 1 y se llama a la funcion de emprendedores
        radio.addEventListener('change', function() {
            if (radio.value != "num_calificacion") {
                calificacion = radio.value;
                actualizarStarRating(0, estrellasMobile); //LLama a la funcion para actualizar el numero de estrellas que se ve en la interfaz
                getDataEmprendedores(); //Llama a la funcion para obtener los datos del emprendedor
            } else {
                calificacion = 0;
                getDataEmprendedores(); //Llama a la funcion para obtener los datos del emprendedor
            }
        });
    });


    //Agrega un evento listener a cada elemento dentro del componente radio_buscar_por_calificacion_mobile
    document.getElementsByName("radio_buscar_por_calificacion_mobile").forEach(function(radio) {
        //Agrega un evento cada vez que la calificacion del emprendedor cambie la pagina actual vuelve a 1 y se llama a la funcion de emprendedores
        radio.addEventListener('change', function() {
            if (radio.value != "num_calificacion") {
                calificacion = radio.value;
                actualizarStarRating(0, estrellasMobile); //LLama a la funcion para actualizar el numero de estrellas que se ve en la interfaz
                getDataEmprendedores(); //Llama a la funcion para obtener los datos del emprendedor
            } else {
                calificacion = 0;
                getDataEmprendedores(); //Llama a la funcion para obtener los datos del emprendedor
            }
        });
    });



    //Agrega un evento cada vez que el campo buscar emprendedor cambie la pagina actual vuelve a 1 y se llama a la funcion de emprendedores
    document.getElementById('txt_buscar_emprendedor').addEventListener('input', function() {
        pagina_actual = 1; //Reinicia la pagina a 1
        getDataEmprendedores(); //Llama a la funcion para obtener los datos del emprendedor
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
        var calificacion_radio;
        if (tipo != tipoVista) {
            if (tipo === 'mobile') {
                alert_buscar_emprendedor.innerHTML = "";
                ordenamiento = document.querySelector('input[name="radio_ordenar_por_desktop"]:checked').value;
                document.querySelector(`input[name="radio_ordenar_por_mobile"][value="${ordenamiento}"]`).checked = true;

                calificacion_radio = document.querySelector('input[name="radio_buscar_por_calificacion_desktop"]:checked').value;
                document.querySelector(`input[name="radio_buscar_por_calificacion_mobile"][value="${calificacion_radio}"]`).checked = true;

                actualizarStarRating(calificacion, estrellasMobile);

            } else {
                var alert_filtro_offcanvas_mobile = document.getElementById("alert_filtro_offcanvas_mobile");
                alert_filtro_offcanvas_mobile.innerHTML = "";
                ordenamiento = document.querySelector('input[name="radio_ordenar_por_mobile"]:checked').value;
                document.querySelector(`input[name="radio_ordenar_por_desktop"][value="${ordenamiento}"]`).checked = true;

                calificacion_radio = document.querySelector('input[name="radio_buscar_por_calificacion_mobile"]:checked').value;
                document.querySelector(`input[name="radio_buscar_por_calificacion_desktop"][value="${calificacion_radio}"]`).checked = true;

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


    //Funcion actualiza la calificacion y marca o desmarcar las estrellas ademas de actualizar la lista de emprendedores
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
        getDataEmprendedores(); //Llama a la funcion para obtener los de los emprendedores
    }



    //Funcion para cambiar la pagina y recargar los datos
    function nextPageEmprendedor(pagina) {
        pagina_actual = pagina;
        getDataEmprendedores();
    }
</script>

