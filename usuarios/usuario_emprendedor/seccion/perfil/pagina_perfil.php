<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_notificacion.php");
include("../../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_seguimiento.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/consultas_bd/consultas_categoria.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/config_define.php");


$usuario_emprendedor_perfil = array();
$usuario_emprendedor = array();
$categorias_producto = array();
$id_usuario_emprendedor;
$id_usuario_emprendedor_token;
$mensaje_error = "";
$estado = "danger";
$lo_sigue = false;
$es_perfil_del_usuario = false;
$usuario_valido = false;
$tipo_usuario = 0;

try {
    //Establecer la sesion
    session_start();


    //Inicializacion de variables obtenidas de la URL
    $id_usuario_emprendedor_perfil = isset($_GET['id']) ? $_GET['id'] : '';
    $id_usuario_emprendedor_perfil_token = isset($_GET['token']) ? $_GET['token'] : '';

    //Se verifica que los datos recibidos de la URL sean validos
    verificarUrlTokenId($id_usuario_emprendedor_perfil, $id_usuario_emprendedor_perfil_token);

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Se obtiene los datos de las categorias de los productos publicados
    $categorias_producto = obtenerCategoriasDeProductosPerfilEmprendedor($conexion, $id_usuario_emprendedor_perfil);

    //Se obtiene los datos del emprendedor 
    $usuario_emprendedor_perfil = obtenerDatosUsuarioEmprendedorPorIDUsuarioEmprendedor($conexion, $id_usuario_emprendedor_perfil);
    if (empty($usuario_emprendedor_perfil)) {
        throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
    }

    //Obtener cantidad de seguidores del usuario emprendedor
    $cant_total_seguidores = cantTotalSeguidoresUsuarioEmprendedor($conexion, $usuario_emprendedor_perfil['id_usuario']);

    //Obtener cantidad de emprendedores que sigue el propietario del perfil
    $cant_total_seguidos = cantTotalSeguimientoUsuario($conexion, $usuario_emprendedor_perfil['id_usuario']);


    //Verificar los datos de sesion del usuario
    $usuario_inicio_sesion = verificarEntradaDatosSession(['id_usuario', 'tipo_usuario']);
    if ($usuario_inicio_sesion) {

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION['id_usuario'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        $usuario_valido = verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario);

        //Verifica si el usuario es valido
        if ($usuario_valido) {

            //Se verifica que el usuario sea emprendedor
            if ($tipo_usuario == 2) {

                //Se obtiene la informacion  para saber si el usuario que ingreso a su perfil o no
                $es_perfil_del_usuario = esPerfilDelUsuario($conexion, $id_usuario, $id_usuario_emprendedor_perfil);

                //Se verifica que el usuario emprendedor que ingresa sea el perfil del usuario
                if (!$es_perfil_del_usuario) {

                    //Se obtiene los datos del emprendedor 
                    $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
                    if (empty($usuario_emprendedor)) {
                        throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
                    } else {
                        //Las variables se utilizan para que el usuario emprendedor pueda ir a su perfil desde el navbar
                        $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];
                        $id_usuario_emprendedor_token = hash_hmac('sha1', $id_usuario_emprendedor, KEY_TOKEN);
                    }
                }
            }

            //Se verifica que no sea el perfil del usuario emprendedor
            if (!$es_perfil_del_usuario) {
                //En caso que no sea el perfil del usuario se obtiene la informacion para saber si lo sigue o no
                $lo_sigue = verificarSiElUsuarioSigueAlEmprendedor($conexion, $id_usuario_emprendedor_perfil, $id_usuario);
            }
        }
    }

    //Se verifica que no sea el perfil del usuario emprendedor
    if (!$es_perfil_del_usuario) {

        //Verifica la cuenta del usuario este activa y no baneada
        if (!$usuario_emprendedor_perfil['activado'] || $usuario_emprendedor_perfil['baneado']) {
            $estado = "info";
            throw new Exception("La cuenta del usuario emprendedor no esta activa o esta baneado");
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
    <title>Proyecto Emprendedor Perfil</title>

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
        <?php if (empty($mensaje_error)) {    ?>
            <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
            <div class="container-fluid">


                <!--Titulo de la seccion-->
                <h1 class="text-center"><?php echo ($usuario_emprendedor_perfil['nombre_emprendimiento']); ?></h1>

                <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                <div id="alert_perfil_usuario_emprendedor"></div>

                <div class="row">
                    <div class="col-12 col-sm-12 col-md-4 col-lg-3 mb-4">

                        <!-- Card -->
                        <div class="card">
                            <!-- En caso que sea el perfil del usuario va aparecer un header diciendo que es su perfil -->
                            <?php if ($es_perfil_del_usuario) { ?>
                                <h5 class="card-header text-center">Mi perfil</h5>
                            <?php }  ?>


                            <!-- Body del Card -->
                            <div class="card-body">
                                <div class="row">

                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                        <?php if (is_null($usuario_emprendedor_perfil["foto_perfil_nombre"])) {
                                            $ruta_archivo = $url_foto_perfil_predeterminada;
                                        } else {
                                            $ruta_archivo = "{$url_base_archivos}/uploads/{$usuario_emprendedor_perfil["id_usuario_emprendedor"]}/foto_perfil/{$usuario_emprendedor_perfil["foto_perfil_nombre"]}";
                                        } ?>

                                        <img class="card-img-top imagen_perfil" src="<?php echo ($ruta_archivo); ?>" alt="Foto de perfil">

                                    </div>

                                    <!--Contenedor que incluye nombre de usuario,descripcion,seguidores, seguidos, productos disponibles, calificacion del emprendedor y una foto del perfil del emprendedor.-->
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">

                                        <div class="row">
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <p class="card-text text-break"><strong>Nombre de usuario:</strong><?php echo ($usuario_emprendedor_perfil['nombre_usuario']); ?></p>
                                            </div>

                                            <!--Verifica si el emprendedor tiene una foto de perfil. Si no hay foto de perfil, se usa una imagen predeterminada-->
                                            <?php if (is_null($usuario_emprendedor_perfil['descripcion'])) {
                                                $texto_descripcion = "Sin descripcion";
                                            } else {
                                                $texto_descripcion = $usuario_emprendedor_perfil["descripcion"];
                                            } ?>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <p class="card-text text-break"><strong>Descripción:</strong><?php echo ($texto_descripcion); ?></p>
                                            </div>


                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <p class="card-text text-break" id="txt_seguidores"><strong>Seguidores:</strong> <?php echo ($cant_total_seguidores); ?></p>
                                            </div>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <p class="card-text text-break" id="txt_seguidos"><strong>Seguidos:</strong> <?php echo ($cant_total_seguidos); ?></p>
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <p class="card-text text-break" id="txt_seguidos"><strong>Productos disponibles:</strong> <?php echo ($usuario_emprendedor_perfil['productos_disponibles']); ?></p>
                                            </div>



                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 text-center">
                                                <p class="card-text"><strong>Calificacion del emprendedor</strong></p>

                                                <!--Verifica si el emprendedor tiene una calificacion-->
                                                <div class="text-center">
                                                    <?php if (is_null($usuario_emprendedor_perfil['calificacion_emprendedor'])) { ?>
                                                        <p class='card-text text-break'>El emprendedor aun no tiene una calificacion</p>
                                                    <?php } else { ?>
                                                        <!--Agrega estrellas activas para la calificacion actual del emprendedor. -->
                                                        <?php for ($k = 0; $k < $usuario_emprendedor_perfil['calificacion_emprendedor']; $k++) { ?>
                                                            <i class="fas fa-star star-calificacion-activo"></i>
                                                        <?php  } ?>

                                                        <!--Calcula cúantas estrellas faltan para llegar a la calificacion máxima posible. -->
                                                        <?php $calificacionRestante = $calificacion_max_emprendedores - $usuario_emprendedor_perfil['calificacion_emprendedor']; ?>
                                                        <?php if ($calificacionRestante > 0) { ?>
                                                            <!--Agregar estrellas vacias para las calificaciones restantes. -->
                                                            <?php for ($l = 0; $l < $calificacionRestante; $l++) {  ?>
                                                                <i class="fas fa-star star-calificacion"></i>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- En caso que no sea el perfil del usuario va aparecer un footer con botones para seguir al usuario o dejar de seguir -->
                            <?php if (!$es_perfil_del_usuario) { ?>

                                <!-- Footer del Card -->
                                <div class="card-footer text-center">
                                    <div id="div_boton_seguimiento">
                                        <!-- Verifica que el usuario sigue al emprendedor-->
                                        <?php if ($lo_sigue) { ?>
                                            <button type="button" class="btn btn-outline-danger" id="button_dejar_seguir_usuario">Dejar de seguir</button>
                                        <?php  } else { ?>
                                            <button type="button" class="btn btn-outline-success" id="button_seguir_usuario">Seguir</button>
                                        <?php   } ?>
                                    </div>
                                </div>
                            <?php  } ?>

                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-8 col-lg-9 mb-4">
                        <!--Define una navegación por pestañas para alternar entre la vista de productos y la vista de publicaciones.-->
                        <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                <button class="nav-link active" id="nav_producto-tab" data-bs-toggle="tab" data-bs-target="#div_nav_producto" type="button" role="tab" aria-controls="div_nav_producto" aria-selected="true">Productos</button>
                                <button class="nav-link" id="nav_publicacion_tab" data-bs-toggle="tab" data-bs-target="#div_nav_publicacion" type="button" role="tab" aria-controls="div_nav_publicacion" aria-selected="false">Publicaciones</button>

                            </div>
                        </nav>
                        <!--Contenedor para el contenido de las pestañas.La pestaña de productos esta activa por defecto.-->
                        <div class="tab-content" id="nav-tabContent">

                            <!--Contenedor de los productos.-->
                            <div class="tab-pane fade show active" id="div_nav_producto" role="tabpanel" aria-labelledby="nav_producto-tab" tabindex="0">
                                <div class="row">

                                    <!--Campo de busqueda por el nombre del producto-->
                                    <div class="col-12 col-sm-8 col-md-8 col-lg-7 mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                            <input type="search" class="form-control" placeholder="Buscar por nombre de producto" aria-label="Buscar producto" name="buscar_producto" id="txt_buscar_producto">
                                        </div>
                                    </div>
                                    <!--Boton para desplegar el offcanvas-->
                                    <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-2">
                                        <button class="btn btn-outline-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltro" aria-controls="offcanvasFiltro">Filtros</button>
                                    </div>

                                    <!--Se verifica si el usuario emprendedor entro a su perfil en caso que asi sea se va ver el boton para hacer nuevos productos-->
                                    <?php if ($es_perfil_del_usuario) { ?>
                                        <!--Boton para redireccionar a otra pagina para agregar un nuevo producto-->
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 mt-1">
                                            <a href="../producto/pagina_agregar_producto.php" class="btn btn-outline-primary"><i class="fa-solid fa-circle-plus"></i> Publicar un nuevo producto</a>
                                        </div>
                                    <?php } ?>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                        <!--Contenedor con las cards que contiene los productos-->
                                        <div class="row" id="cards_productos"></div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                        <div class="row">
                                            <!--Informacion sobre las paginas disponibles -->
                                            <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="lbl-totalPagina"></div>

                                            <!--Contenedor para la navegacion de la paginacion -->
                                            <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacion"></div>

                                        </div>

                                    </div>
                                </div>

                                <!--offcanvas que contiene el menu de filtro de productos-->
                                <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasFiltro" aria-labelledby="offcanvasFiltroLabel">

                                    <!--Header de offcanvas-->
                                    <div class="offcanvas-header">
                                        <h5 class="offcanvas-title" id="offcanvasFiltroLabel">Filtros</h5>
                                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                    </div>

                                    <!--Body de offcanvas-->
                                    <div class="offcanvas-body">

                                        <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                        <div id="alert_filtro_offcanvas"></div>
                                        <!-- Contenedor que tiene un accordion para mostrar los filtro de busqueda  -->
                                        <div class="accordion" id="accordionPanelFiltro">

                                            <!-- Contenedor que tiene el item del accordion que permite al usuario ordenar los resultados por diferentes criterios usando botones de radio.  -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltroCollapseUno" aria-expanded="true" aria-controls="panelFiltroCollapseUno">
                                                        Ordenar por
                                                    </button>
                                                </h2>
                                                <div id="panelFiltroCollapseUno" class="accordion-collapse collapse show">
                                                    <div class="accordion-body">
                                                        <!-- Opciones de radio para ordenar -->
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_ordenar_por" id="fecha_mas_recientes" value="1" checked>
                                                            <label class="form-check-label" for="fecha_mas_recientes">Fecha de publicación: más recientes</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_ordenar_por" id="fecha_mas_antigua" value="2">
                                                            <label class="form-check-label" for="fecha_mas_antigua">Fecha de publicación: más antigua</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_ordenar_por" id="mayor_precio" value="3">
                                                            <label class="form-check-label" for="mayor_precio">Mayor precio</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_ordenar_por" id="menor_precio" value="4">
                                                            <label class="form-check-label" for="menor_precio">Menor Precio</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Contenedor que tiene el item del accordion que permite al usuario filtrar los resultados por un rango de precio especificando un mínimo y un máximo. -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltroCollapseDos" aria-expanded="false" aria-controls="panelFiltroCollapseDos">
                                                        Rango de precio
                                                    </button>
                                                </h2>
                                                <div id="panelFiltroCollapseDos" class="accordion-collapse collapse">
                                                    <div class="accordion-body">
                                                        <div class="input-group input-group-sm mb-2">
                                                            <input type="number" class="form-control" placeholder="Minimo" aria-label="Minimo" name="precio_minimo" id="precio_minimo" min="0" step="1">
                                                            <span class="input-group-text">-</span>
                                                            <input type="number" class="form-control" placeholder="Maximo" aria-label="Maximo" name="precio_maximo" id="precio_maximo" min="0" step="1">
                                                            <button type="button" class="btn btn-outline-secondary" id="buscar_rango_precio"><i class="fa-solid fa-chevron-right"></i></button>
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
                                                            <input class="form-check-input" type="radio" name="radio_buscar_por_calificacion" id="todos_calificacion" value="todos_calificacion" checked>
                                                            <label class="form-check-label" for="todos_calificacion">Todos</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_buscar_por_calificacion" id="sin_calificacion" value="sin_calificacion">
                                                            <label class="form-check-label" for="sin_calificacion">Sin calificacion</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="radio_buscar_por_calificacion" id="num_calificacion" value="num_calificacion">
                                                            <label class="form-check-label" for="num_calificacion">Por calificacion</label>
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


                                            <!-- Contenedor que tiene el item del accordion que permite al usuario filtrar los resultados por categoría utilizando un menú desplegable. -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltroCollapseCuatro" aria-expanded="false" aria-controls="panelFiltroCollapseCuatro">
                                                        Categoria
                                                    </button>
                                                </h2>
                                                <div id="panelFiltroCollapseCuatro" class="accordion-collapse collapse">
                                                    <div class="accordion-body">
                                                        <select class="form-select" id="categoria_select" name="categoria">
                                                            <option value="0" selected>Todos</option>
                                                            <?php foreach ($categorias_producto as $categoria) { ?>
                                                                <option value="<?php echo $categoria['id_categoria_producto']; ?>"><?php echo $categoria['nombre_categoria']; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <!-- Contenedor que tiene el item del accordion que permite al usuario filtrar los resultados por estado (disponible o finalizado). -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltroCollapseCinco" aria-expanded="false" aria-controls="panelFiltroCollapseCinco">
                                                        Estado
                                                    </button>
                                                </h2>
                                                <div id="panelFiltroCollapseCinco" class="accordion-collapse collapse">
                                                    <div class="accordion-body">
                                                        <select class="form-select" id="estado_select" name="estado">
                                                            <option value="todos" selected>Todos</option>
                                                            <option value="disponible">Disponible</option>
                                                            <option value="finalizado">Finalizado</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!--Boton que permite restablecer los valores de busqueda -->
                                        <button type="button" class="btn btn-outline-dark mt-3" id="restablecer_filtro">Restablecer</button>
                                    </div>
                                </div>
                            </div>

                            <!--Contenedor de las publicaciones.-->
                            <div class="tab-pane fade  overflow-auto" style="max-height: 600px;" id="div_nav_publicacion" role="tabpanel" aria-labelledby="nav_publicacion_tab" tabindex="0">

                                <!--Se verifica si el usuario emprendedor entro a su perfil en caso que asi sea se va ver el boton para hacer nuevoas publcaciones-->
                                <?php if ($es_perfil_del_usuario) { ?>
                                    <!--Boton para abrir el modal para agregar una nueva publicacion-->
                                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal_agregar_publicacion"><i class="fa-solid fa-circle-plus"></i> Nueva publicacion</button>
                                    </div>
                                <?php } ?>

                                <!--Contenedor con las cards que contiene las publicaciones-->
                                <div class="row" id="cards_publicaciones" style="margin-right:0"> </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        <?php } else {  ?>
            <div class="container">
                <div class="alert alert-<?php echo ($estado) ?>" role="alert">
                    <?php echo ($mensaje_error); ?>
                </div>
            </div>
        <?php } ?>

    </main>

    <!-- Incluye el pie de pagina y varios scripts necesarios para el funcionamiento de la pagina.-->
    <script src="../../../../lib/leaflet-1.9.4/leaflet.js"></script>
    <script src="../../../../config/js/funciones.js"></script>
    <script src="../../../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../../../lib/fancyapps-5.0.33/dist/fancybox/fancybox.umd.js"></script>
    <?php require("../publicacion/modal_mapa_publicacion.php"); ?>
    <?php require("../publicacion/modal_agregar_publicacion.php"); ?>
    <?php require("../../../../template/footer.php"); ?>

</body>

</html>

<script>
    const perfil_del_usuario = <?php echo json_encode($es_perfil_del_usuario); ?>;

    var calificacion = "";

    const estrellas = document.querySelectorAll(".star-filtro-mobile");
    var alert_filtro_offcanvas = document.getElementById("alert_filtro_offcanvas");



    // Elementos para la navegación 
    var div_nav_publicacion = document.getElementById("div_nav_publicacion");
    var div_nav_producto = document.getElementById("div_nav_producto");


    // Variables de control para la paginación de publicaciones
    var cargar_publicaciones = true;
    var pagina_publicacion = 1;

    // Variables de control para la paginación de producto
    var pagina_actual_producto = 1;


    // Carga inicial de datos de publicaciones y productos
    getDataPublicaciones(pagina_publicacion);
    getDataProductos();

    // Evento de scroll para cargar más publicaciones cuando se llega al fondo del contenedor
    div_nav_publicacion.addEventListener('scroll', function() {
        // Verifica si el usuario ha llegado al fondo del contenedor de producto
        if (div_nav_publicacion.scrollHeight - div_nav_publicacion.scrollTop === div_nav_publicacion.clientHeight && cargar_publicaciones) {
            pagina_publicacion++; // Incrementa la página actual de publicaciones
            getDataPublicaciones(pagina_publicacion); // Carga más publicaciones
        }
    });



    // Inicializa Fancybox con el selector de data-fancybox
    Fancybox.bind('[data-fancybox]', {
        // Deshabilita las miniaturas
        Thumbs: false,
        // Configuración de imágenes
        Images: {
            protected: true // Protege las imágenes contra clic derecho y arrastrar
        },
    });


    //Agrega un evento cuando una estrella se le hace click
    estrellas.forEach(function(star, index) {
        //Se llama a una la funcion para actualizar la calificacion y obtener una lista de producto
        star.addEventListener("click", function() {
            document.getElementById('num_calificacion').checked = true;
            manejarClicEstrella(index, estrellas);

        });
    });

    //Agrega un evento cuando se detecta que se haga un click en el elemento
    document.getElementById('restablecer_filtro').addEventListener('click', function() {
        //Se llama a la funcion para restablecer los valores de busqueda del filtro y actualiza la lista de productos
        restablecer_busqueda();
    });


    //Funcion para restablecer los valores de busqueda a sus valores iniciales ademas de actualizar la lista de productos
    function restablecer_busqueda() {
        alert_filtro_offcanvas.innerHTML = "";
        document.getElementById('fecha_mas_recientes').checked = true;
        document.getElementById('categoria_select').selectedIndex = 0;
        document.getElementById('estado_select').selectedIndex = 0;
        document.getElementById('precio_maximo').value = '';
        document.getElementById('precio_minimo').value = '';
        document.getElementById('todos_calificacion').checked = true;
        calificacion = "";
        actualizarStarRating(0, estrellas);
        pagina_actual_producto = 1;
        getDataProductos();
    }



    //Agrega un evento listener a cada elemento dentro del componente radio
    document.getElementsByName("radio_ordenar_por").forEach(function(radio) {
        //Agrega un evento cada vez que el numero de ordenamiento cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
        radio.addEventListener('change', function() {

            pagina_actual_producto = 1; //Reinicia la pagina a 1
            getDataProductos(); //Llama a la funcion para obtener datos de productos

        });
    });


    //Agrega un evento listener a cada elemento dentro del componente radio
    document.getElementsByName("radio_buscar_por_calificacion").forEach(function(radio) {
        //Agrega un evento cada vez que la calificacion del producto cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
        radio.addEventListener('change', function() {
            //Se verifica que el elemento de radio elegido sea diferente a num_calificacion
            if (radio.value != "num_calificacion") {
                calificacion = radio.value;
                actualizarStarRating(0, estrellas); //LLama a la funcion para actualizar el numero de estrellas que se ve en la interfaz
                getDataProductos(); //Llama a la funcion para obtener datos de productos

            } else {
                calificacion = 0;
                getDataProductos(); //Llama a la funcion para obtener datos de productos

            }
        });
    });

    //Agrega un evento cada vez que se cambie la categoria, la pagina actual vuelve a 1 y se llama a la funcion de productos
    document.getElementById("categoria_select").addEventListener("change", function() {

        pagina_actual_producto = 1; //Reinicia la pagina a 1
        getDataProductos(); //Llama a la funcion para obtener datos del producto

    });



    //Agrega un evento cada vez que se cambie el estado la pagina actual vuelve a 1 y se llama a la funcion de productos
    document.getElementById("estado_select").addEventListener("change", function() {
        pagina_actual_producto = 1; //Reinicia la pagina a 1
        getDataProductos(); //Llama a la funcion para obtener datos del producto

    });


    //Agrega un evento cuando se detecta que se haga un click en el elemento
    document.getElementById('buscar_rango_precio').addEventListener('click', function() {

        //Se obtiene los valores de precio minimo y maximo
        var precioMinimo = document.getElementById('precio_minimo');
        var precioMaximo = document.getElementById('precio_maximo');
        var campos_verificar_num = [precioMinimo, precioMaximo];

        //Elimina cualquier alerta previa 
        alert_filtro_offcanvas.innerHTML = "";
        if (validarCampoVacio(campos_verificar_num)) {
            alert_filtro_offcanvas.innerHTML = mensaje_alert_fijo("danger", "Por favor complete los dos campos de rango de precio");
            return false;
        }

        //Verifica que los campos precio maximo y minimo tengan valores numeros
        var lista_num_input = listaInputValorNoNumerico(campos_verificar_num);
        if (lista_num_input.length > 0) {
            alert_filtro_offcanvas.innerHTML = mensaje_alert_lista_fijo("danger", "Solo se permite ingresar valores numericos en los siguientes campos:", lista_num_input);
            return false;
        }

        //Verifica que los campos precio maximo y minimo tengan valores numeros positivos
        var lista_num_input = listaInputNumNoPositivo(campos_verificar_num);
        if (lista_num_input.length > 0) {
            alert_filtro_offcanvas.innerHTML = mensaje_alert_lista_fijo("danger", "Los valores ingresados en los siguientes campos no son numero positivos:", lista_num_input);
            return false;
        }

        //Verifica que el campo precio mininmo no sea mayor al campo precio maximo
        if (parseFloat(precioMinimo.value) > parseFloat(precioMaximo.value)) {
            alert_filtro_offcanvas.innerHTML = mensaje_alert_fijo("danger", "El rango minimo de precio debe ser menor o igual al valor maximo.");
            return false;
        }

        pagina_actual_producto = 1; //Reinicia la pagina a 1
        getDataProductos(); //Llama a la funcion para obtener datos del producto
    });


    //Agrega un evento cada vez que el campo buscar producto cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
    document.getElementById('txt_buscar_producto').addEventListener('input', function() {
        pagina_actual_producto = 1; //Reinicia la pagina a 1
        getDataProductos(); //Llama a la funcion para obtener datos del producto
    });



    //Funcion actualiza la apariencia de las estrellas segun la calificacion actual
    function actualizarStarRating(rating, estrellas) {
        estrellas.forEach(function(star, index) {
            if (index < rating) {
                star.classList.add("checked");
            } else {
                star.classList.remove("checked");
            }
        });
    }

    //Funcion actualiza la calificacion y marca o desmarcar las estrellas ademas de actualizar la lista de productos
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
        pagina_actual_producto = 1; //Reinicia la pagina a 1
        getDataProductos(); //Llama a la funcion para obtener datos del producto
    }


    //Función para obtener y cargar datos de productos
    function getDataProductos() {
        var campo_buscar_producto = document.getElementById("txt_buscar_producto");
        var pagina = pagina_actual_producto;

        var ordenamiento = parseInt(document.querySelector('input[name="radio_ordenar_por"]:checked').value);
        var precioMinimo = parseFloat(document.getElementById('precio_minimo').value);
        var precioMaximo = parseFloat(document.getElementById('precio_maximo').value);
        var categoria = parseInt(document.getElementById('categoria_select').value);
        var estado = document.getElementById('estado_select').value;

        // Envío del formulario usando fetch
        const formData = new FormData();

        formData.append('campo_buscar_producto', campo_buscar_producto.value);
        formData.append('numero_pagina', pagina);
        formData.append('num_ordenamiento', ordenamiento);
        formData.append('precio_minimo', precioMinimo);
        formData.append('precio_maximo', precioMaximo);
        formData.append('num_categoria', categoria);
        formData.append('estado', estado);
        formData.append('calificacion', calificacion);

        fetch(`lista_producto_perfil.php${window.location.search}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convierte la respuesta a JSON
            .then(datos => {

                if (datos.estado != "danger") {
                    document.getElementById('cards_productos').innerHTML = datos.cards;
                    document.getElementById("nav-paginacion").innerHTML = datos.paginacion;
                    document.getElementById("lbl-totalPagina").innerHTML = datos.pagina;
                } else {
                    // Muestra un mensaje de alerta si hubo un error
                    div_nav_producto.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }

            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                div_nav_producto.innerHTML = mensaje_alert_fijo("danger", e);
            });
    }




    //Función para obtener y cargar datos de publicaciones
    function getDataPublicaciones(pagina_publicacion) {
        fetch(`lista_publicaciones_perfil.php${window.location.search}&pagina=${pagina_publicacion}`, {})
            .then(respuesta => respuesta.json()) // Convierte la respuesta a JSON
            .then(datos => {
                if (datos.estado != "danger") {
                    // Añade nuevas cards de publicaciones al contenedor
                    cards_publicaciones.innerHTML += datos.cards_publicaciones;
                    // Actualiza la variable de control según si hay más publicaciones para cargar
                    cargar_publicaciones = datos.cargar_mas_publicaciones_informacion;
                } else {
                    // Muestra un mensaje de alerta si hubo un error
                    div_nav_publicacion.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }
            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                div_nav_publicacion.innerHTML = mensaje_alert_fijo("danger", e);
            });
    }


    var alert_perfil_usuario_emprendedor = document.getElementById("alert_perfil_usuario_emprendedor");
    const inicio_sesion = <?php echo json_encode($usuario_inicio_sesion); ?>;
    const sigue_el_perfil = <?php echo json_encode($lo_sigue); ?>;
    var button_seguir_usuario = document.getElementById("button_seguir_usuario");
    var button_dejar_seguir_usuario = document.getElementById("button_dejar_seguir_usuario");
    const div_boton_seguimiento = document.getElementById("div_boton_seguimiento");



    // Verifica si el perfil no es del usuario actual
    if (!perfil_del_usuario) {

        // Si el usuario no sigue al usuario emprendedor
        if (!sigue_el_perfil) {

            // Añade un event listener al botón de seguir usuario que llama a la función seguir_usuario() al hacer clic
            button_seguir_usuario.addEventListener('click', function() {
                seguir_usuario();
            });
        } else {

            // Si el usuario sigue el perfil, añade un event listener al botón de dejar de seguir usuario que llama a la función dejar_seguir_usuario() al hacer clic
            button_dejar_seguir_usuario.addEventListener('click', function() {
                dejar_seguir_usuario();
            });
        }
    } else {
        //En caso que sea el perfil del usuario se puede agregar publicaciones desde la pestaña de publicaciones

        //Manejo del envio del formulario para agregar una publicacion
        formulario_agregar_publicacion.addEventListener("submit", (e) => {

            //Previene el envio por defecto del formulario
            e.preventDefault();

            //Se obtiene el elemento donde estan las card de publicaciones
            var cards_publicaciones = document.getElementById("cards_publicaciones");


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
                    alert_notificacion_agregar_publicacion.innerHTML = mensaje_alert_fijo("danger", "No se pudo obtener los datos necesarios para guardar su ubicacion,Por favor cierre la ventana mapa para poder continuar");
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
            fetch('../publicacion/alta_publicacion.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json()) // Convierte la respuesta a JSON
                .then(datos => {
                    if (datos.estado === 'success') {

                        //Limpia las cards de publicaciones
                        cards_publicaciones.innerHTML = "";
                        pagina_publicacion = 1;

                        // Actualiza los datos de publicacion
                        getDataPublicaciones(pagina_publicacion);

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

    }

    //Funcion para que un usuario deje de seguir a un usuario emprendedor
    function dejar_seguir_usuario() {

        // Verifica si el usuario ha iniciado sesión
        if (inicio_sesion) {

            // Envío del formulario usando fetch
            var formData = new FormData();

            fetch(`../../../seguidores_seguidos/baja_seguir_usuario.php${window.location.search}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json()) // Convierte la respuesta a JSON
                .then(datos => {
                    if (datos.estado != 'danger') {
                        // Actualiza el número de seguidores y seguidos
                        actualizar_num_seguidores(datos.num_seguidores, datos.num_seguidos);

                        // Cambia el botón a "Seguir"
                        div_boton_seguimiento.innerHTML = '<button type="button" class="btn btn-outline-success" id="button_seguir_usuario">Seguir</button>';
                        button_seguir_usuario = document.getElementById("button_seguir_usuario");

                        // Añade un event listener al nuevo botón "Seguir"
                        button_seguir_usuario.addEventListener('click', function() {
                            seguir_usuario();
                        });

                    } else {
                        // Muestra un mensaje de alerta si hubo un error
                        alert_perfil_usuario_emprendedor.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);
                    }

                })
                .catch(error => {
                    // Muestra un mensaje error de la solicitud
                    alert_perfil_usuario_emprendedor.innerHTML = mensaje_alert_fijo("danger", error);
                });
        } else {
            //Muestra un mensaje de alert
            alert("Debe iniciar sesion para poder seguir a un usuario");
        }
    }


    //Funcion para que un usuario siga a un usuario emprendedor
    function seguir_usuario() {

        // Verifica si el usuario ha iniciado sesión
        if (inicio_sesion) {

            // Envío del formulario usando fetch
            var formData = new FormData();
            fetch(`../../../seguidores_seguidos/alta_seguir_usuario.php${window.location.search}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json()) // Convierte la respuesta a JSON
                .then(datos => {
                    if (datos.estado != 'danger') {


                        // Actualiza el número de seguidores y seguidos
                        actualizar_num_seguidores(datos.num_seguidores, datos.num_seguidos);

                        // Cambia el botón a "Dejar de seguir"
                        div_boton_seguimiento.innerHTML = '<button type="button" class="btn btn-outline-danger" id="button_dejar_seguir_usuario">Dejar de seguir</button>';
                        button_dejar_seguir_usuario = document.getElementById("button_dejar_seguir_usuario");

                        // Añade un event listener al nuevo botón "Dejar de seguir"
                        button_dejar_seguir_usuario.addEventListener('click', function() {
                            dejar_seguir_usuario();
                        });

                    } else {
                        alert_perfil_usuario_emprendedor.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);
                    }

                })
                .catch(error => {
                    // Muestra un mensaje error de la solicitud
                    alert_perfil_usuario_emprendedor.innerHTML = mensaje_alert_fijo("danger", error);
                });
        } else {
            //Muestra un mensaje de alert
            alert("Debe iniciar sesion para poder seguir a un usuario");
        }
    }


    //Funciones para actualizar el numero de seguidores y seguidos en el perfil del emprendedor
    function actualizar_num_seguidores(num_seguidores, num_seguidos) {
        txt_seguidores = document.getElementById('txt_seguidores');
        txt_seguidos = document.getElementById('txt_seguidos');
        txt_seguidores.innerHTML = "<strong>Seguidores:</strong> " + num_seguidores;
        txt_seguidos.innerHTML = "<strong>Seguidos:</strong> " + num_seguidos
    }



    //Funcion para cambiar la pagina y recargar los datos
    function nextPageProducto(pagina) {
        pagina_actual_producto = pagina;
        getDataProductos();
    }
</script>