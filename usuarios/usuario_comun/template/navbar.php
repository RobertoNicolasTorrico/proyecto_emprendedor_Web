<nav class="navbar navbar-dark bg-dark navbar-expand-lg mb-3">
        <div class="container-fluid">

                <!--Logo del navbar-->
                <img class="navbar-brand" src="<?php echo ($url_logo) ?>" alt="logo" width="60" height="60">

                <!--Boton para desplegar el offcanvas-->
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#navbar_usuario_emprendedor" aria-controls="navbar_usuario_emprendedor">
                        <span class="navbar-toggler-icon"></span>
                </button>

                <!--offcanvas que contiene el menu de navegacion-->
                <div class=" offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="navbar_usuario_emprendedor" aria-labelledby="navbar_usuario_emprendedor">

                        <!--Header de offcanvas-->
                        <div class="offcanvas-header">
                                <h5 class="offcanvas-title" id="offcanvasDarkNavbarLabel">Menu</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>

                        <!--Body de offcanvas-->
                        <div class="offcanvas-body">
                                <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">

                                        <!--Enlace para ir al inicio  -->
                                        <li class="nav-item">
                                                <a class="btn btn-dark text-white" href="<?php echo ($url_base); ?>/usuarios/index.php">Inicio</a>
                                        </li>

                                        <!--Dropdown para buscar productos o emprendedores -->
                                        <li class="nav-item dropdown">
                                                <button class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Buscar</button>
                                                <ul class="dropdown-menu dropdown-menu-dark">
                                                        <!--Enlace para ir donde se puede buscar los productos de distintos emprendedores -->
                                                        <li>
                                                                <a class="dropdown-item" href="<?php echo ($url_base); ?>/paginas/buscar_producto/pagina_buscar_productos.php">Productos</a>
                                                        </li>

                                                        <!--Enlace para ir donde se puede buscar emprendedores -->
                                                        <li>
                                                                <a class="dropdown-item" href="<?php echo ($url_base); ?>/paginas/buscar_emprendedor/pagina_buscar_emprendedores.php">Emprendedores</a>
                                                        </li>
                                                </ul>
                                        </li>


                                        <!--Dropdown para ver las Preguntas hechas y Lista de Seguidos -->
                                        <li class="nav-item dropdown">
                                                <button class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Ver mis</button>
                                                <ul class="dropdown-menu dropdown-menu-dark">

                                                        <!--Enlace para ir donde estan las preguntas que hizo el usuario -->
                                                        <li class="nav-item">
                                                                <a class="dropdown-item" href="<?php echo ($url_usuario); ?>/preguntas/pagina_preguntas_hechas.php">Preguntas hechas</a>
                                                        </li>

                                                        <!--Enlace para ir donde estan las lista seguidos-->
                                                        <li class="nav-item">
                                                                <a class="dropdown-item" href="<?php echo ($url_usuario); ?>/seguidores_seguidos/pagina_seguidores_seguidos.php">Lista de Seguidos</a>
                                                        </li>
                                                </ul>
                                        </li>


                                        <!--Seccion de notificaciones -->
                                        <li class="nav-item dropdown" id="navbar_notificacion">
                                                <?php echo (navBarUltimasNotificaciones($conexion, $id_usuario)); ?>
                                        </li>


                                        <!--Enlace para ir a las configuraciones del usuario-->
                                        <li class="nav-item">
                                                <a class="btn btn-dark text-white" href="<?php echo ($url_usuario); ?>/configuracion/pagina_configuracion.php"><i class="fa-solid fa-gear"></i></a>
                                        </li>

                                        <!--Enlace para cerrar sesion-->
                                        <li class="nav-item ">
                                                <a class="btn btn-dark text-white" href="<?php echo ($url_usuario); ?>/cerrar_sesion.php">Cerrar sesion</a>
                                        </li>

                                </ul>

                        </div>
                </div>
        </div>
</nav>

<script>
        var boton_navbar_notificaciones = document.getElementById("boton_navbar_notificaciones");

        //Evento para el click del boton de notificaciones
        boton_navbar_notificaciones.addEventListener('click', function() {
                var array_id = [];
                var ariaExpanded = boton_navbar_notificaciones.getAttribute('aria-expanded');
                //Si el menu de notificaciones esta expandido
                if (ariaExpanded === 'true') {

                        //Selecciona todas las notificaciones no leidas
                        notificacion_no_leida = document.querySelectorAll('.notificacion_no_leida_navbar');

                        //Si la cantidad de notificaciones no leidas es mayor a 0
                        if (notificacion_no_leida.length > 0) {
                                notificacion_no_leida.forEach(card => {
                                        array_id.push(card.getAttribute('data-id'));
                                });
                                //LLama a la funcion para actualizar el estado de las notificaciones
                                modificar_notificaciones_navbar(array_id);
                        }
                }
        });

        //Función para actualizar el estado de las notificaciones
        function modificar_notificaciones_navbar(array_id_notificaciones) {
                alert_notificacion_navbar = document.getElementById("alert_notificacion_navbar");
                var notificacion;
                var js_url_usuario = <?php echo json_encode($url_usuario); ?> + "/notificaciones/modificar_notificaciones_navbar.php";

                //Sino hay notificaciones para actualizar termina la funcion 
                if (array_id_notificaciones.length == 0) {
                        return false;
                }
                const formData = new FormData();

                for (let id_notificacion of array_id_notificaciones) {
                        formData.append('id_notificaciones[]', id_notificacion);
                }
                //Realiza una solicitud POST para actualizar las notificaciones
                fetch(js_url_usuario, {
                                method: 'POST',
                                body: formData
                        })
                        .then(response => response.json())
                        .then(datos => {
                                if (datos.estado != "danger") {
                                        //Actualiza el contador de notificaciones
                                        cantidad_notificacion = document.getElementById("cantidad_notificacion");
                                        cantidad_notificacion.innerHTML = datos.cant_sin_leer_navbar;

                                        //Marca las notificaciones como leidas en la interfaz del usuario
                                        for (let i = 0; i < array_id_notificaciones.length; i++) {
                                                notificacion = document.getElementById("notificacion_" + array_id_notificaciones[i]);
                                                notificacion.classList.remove("notificacion_no_leida_navbar");
                                                notificacion.classList.add("notificacion_leida_navbar");
                                        }

                                } else {
                                        // Muestra un mensaje de alerta si hubo un error
                                        alert_notificacion_navbar.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);
                                }
                        })
                        .catch(error => {
                                // Maneja errores de la solicitud
                                console.error(error);
                        });
        }
</script>