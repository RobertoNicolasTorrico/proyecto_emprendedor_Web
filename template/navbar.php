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
                        <a class="btn btn-dark text-white" href="<?php echo ($url_base); ?>/index.php">Inicio</a>
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

                    <!--Enlace para iniciar sesion-->
                    <li class="nav-item">
                        <a class="btn btn-dark text-white" href="<?php echo ($url_base); ?>/paginas/iniciar_sesion/pagina_iniciar_sesion.php">Iniciar sesión</a>
                    </li>
                </ul>

            </div>
        </div>
    </div>
</nav>