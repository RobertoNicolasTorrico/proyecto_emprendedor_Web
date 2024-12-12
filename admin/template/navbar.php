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
                        <a class="btn btn-dark text-white" href="<?php echo ($url_usuario_admin); ?>/index.php">Inicio</a>
                    </li>

                    <!--Enlace para a las categorias disponibles-->
                    <li class="nav-item">
                        <a class="btn btn-dark text-white" href="<?php echo ($url_usuario_admin); ?>/paginas/categoria/pagina_categoria.php">Categorias de productos</a>
                    </li>

                    <!--Enlace para ir a las configuraciones del usuario administrador-->
                    <li class="nav-item">
                        <a class="btn btn-dark text-white" href="<?php echo ($url_usuario_admin); ?>/paginas/configuracion/pagina_configuracion.php"><i class="fa-solid fa-gear"></i></a>
                    </li>


                    <!--Enlace para cerrar sesion-->
                    <li class="nav-item ">
                        <a class="btn btn-dark text-white" href="<?php echo ($url_usuario_admin); ?>/paginas/cerrar_sesion.php">Cerrar sesion</a>
                    </li>

                </ul>

            </div>
        </div>
    </div>
</nav>