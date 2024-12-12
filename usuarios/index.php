<?php
//Archivos de configuracion y funciones necesarias
include("../config/consultas_bd/conexion_bd.php");
include("../config/consultas_bd/consultas_producto.php");
include("../config/consultas_bd/consultas_usuario.php");
include("../config/consultas_bd/consultas_seguimiento.php");
include("../config/consultas_bd/consultas_notificacion.php");
include("../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../config/funciones/funciones_generales.php");
include("../config/funciones/funciones_session.php");
include("../config/funciones/funciones_token.php");
include("../config/config_define.php");

$mensaje_error = "";
$cant_seguimiento = 0;
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
                        //Se obtiene la cantidad total de emprendedores que sigue el usuario
                        $cant_seguimiento = cantTotalSeguimientoUsuario($conexion, $id_usuario);

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
                        header("Location:../paginas/iniciar_sesion/pagina_iniciar_sesion.php");
                }
        } else {
                //Si no se encuentran todos los datos necesarios de la sesion se destruye todos los elementos aunque existan o no y redirige al inicio de sesion 
                unset($_SESSION['id_usuario']);
                unset($_SESSION['tipo_usuario']);
                header("Location:../paginas/iniciar_sesion/pagina_iniciar_sesion.php");
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
        <title>Proyecto Emprendedor Inicio Usuario</title>

        <!--Enlace al archivo de estilos propios del proyecto-->
        <link href="../config/css/estilos.css" rel="stylesheet">

        <!--Enlace al archivo de estilos de Bootstrap-->
        <link href="../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <!--Enlace al archivo de estilos de FontAwesome para iconos-->
        <link href="../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">

        <!--Enlace al archivo de estilos de Fancybox-->
        <link href="../lib/fancyapps-5.0.33/dist/fancybox/fancybox.css" rel="stylesheet" />

        <!--Enlace al archivo de estilos para Leaflet para mapas-->
        <link href="../lib/leaflet-1.9.4/leaflet.css" rel="stylesheet" />

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
                        header("Location:../paginas/iniciar_sesion/pagina_iniciar_sesion.php");
        }

        ?>



        <!--Separa el contenido principal de la pagina del pie de pagina.-->
        <main>
                <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
                <div class="container">

                        <?php if (empty($mensaje_error)) {    ?>
                                <!--Define una navegación por pestañas para alternar entre la vista de productos y la vista de publicaciones.-->
                                <nav>
                                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                                <button class="nav-link active" id="nav_producto-tab" data-bs-toggle="tab" data-bs-target="#div_nav_producto" type="button" role="tab" aria-controls="div_nav_producto" aria-selected="true">Productos</button>
                                                <button class="nav-link" id="nav_publicacion_tab" data-bs-toggle="tab" data-bs-target="#div_nav_publicacion" type="button" role="tab" aria-controls="div_nav_publicacion" aria-selected="false">Publicaciones</button>
                                        </div>
                                </nav>

                                <!--Contenedor para el contenido de las pestañas.La pestaña de productos esta activa por defecto.-->
                                <div class="tab-content" id="nav-tabContent">
                                        <!--Contenedor de los productos-->
                                        <div class="tab-pane fade overflow-auto show active" style="max-height: 400px;" id="div_nav_producto" role="tabpanel" aria-labelledby="nav_producto-tab" tabindex="0">
                                                <div class="row align-items-center justify-content-center" id="cards_productos" style="margin-right:0;"></div>
                                        </div>

                                        <!--Contenedor de las publicaciones-->
                                        <div class="tab-pane fade  overflow-auto" style="max-height: 400px;" id="div_nav_publicacion" role="tabpanel" aria-labelledby="nav_publicacion_tab" tabindex="0">
                                                <div class="row align-items-center justify-content-center" id="cards_publicaciones" style="margin-right:0;"></div>
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
        <?php require("usuario_emprendedor/seccion/publicacion/modal_mapa_publicacion.php"); ?>
        <?php require("../template/footer.php"); ?>

        <script src="../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="../lib/leaflet-1.9.4/leaflet.js"></script>
        <script src="../config/js/funciones.js"></script>
        <script src="../lib/fancyapps-5.0.33/dist/fancybox/fancybox.umd.js"></script>


</body>

</html>
<script>
        // Inicializa Fancybox con el selector de data-fancybox
        Fancybox.bind('[data-fancybox]', {
                // Deshabilita las miniaturas
                Thumbs: false,
                // Configuración de imágenes
                Images: {
                        protected: true // Protege las imágenes contra clic derecho y arrastrar
                },
        });


        // Elementos para la navegación y las cards de publicaciones
        var div_nav_publicacion = document.getElementById("div_nav_publicacion");
        var cards_publicaciones = document.getElementById("cards_publicaciones");
        // Variables de control para la paginación de publicaciones
        var cargar_publicaciones_informacion = true;
        var pagina_publicacion_informacion = 1;


        // Elementos para la navegación y las cards de productos
        var div_nav_producto = document.getElementById("div_nav_producto");
        var cards_productos = document.getElementById("cards_productos");
        // Variables de control para la paginación de productos
        var cargar_publicaciones_producto = true;
        var pagina_publicacion_producto = 1;


        const js_cant_seguimiento = <?php echo json_encode($cant_seguimiento);  ?>;

        //Verifica si el usuario sigue a algun emprendedor
        if (js_cant_seguimiento > 0) {
                // Carga inicial de datos de publicaciones y productos
                getDataProductos(pagina_publicacion_producto);
                getDataPublicaciones(pagina_publicacion_informacion);


                // Evento de scroll para cargar más productos cuando se llega al fondo del contenedor
                div_nav_producto.addEventListener('scroll', function() {
                        // Verifica si el usuario ha llegado al fondo del contenedor de producto
                        if (div_nav_producto.scrollHeight - div_nav_producto.scrollTop === div_nav_producto.clientHeight && cargar_publicaciones_producto) {
                                pagina_publicacion_producto++; // Incrementa la página actual de productos
                                getDataProductos(pagina_publicacion_producto); // Carga más productos
                        }
                });



                // Evento de scroll para cargar más publicaciones cuando se llega al fondo del contenedor
                div_nav_publicacion.addEventListener('scroll', function() {
                        // Verifica si el usuario ha llegado al fondo del contenedor de publicaciones
                        if (div_nav_publicacion.scrollHeight - div_nav_publicacion.scrollTop === div_nav_publicacion.clientHeight && cargar_publicaciones_informacion) {
                                pagina_publicacion_informacion++; // Incrementa la página actual de publicaciones
                                getDataPublicaciones(pagina_publicacion_informacion); // Carga más publicaciones
                        }
                });

        } else {
                cards_productos.innerHTML = "<p class='text-center'>¡Empieza a seguir a tus emprendedores favoritos para ver sus últimos productos! </p>";
                cards_publicaciones.innerHTML = "<p class='text-center'>¡Empieza a seguir a tus emprendedores favoritos para ver sus últimas publicaciones!</p>";
        }



        //Función para obtener y cargar datos de productos
        function getDataProductos(pagina_publicacion_producto) {
                fetch(`lista_productos_index.php?pagina_producto=${pagina_publicacion_producto}`, {})
                        .then(respuesta => respuesta.json()) // Convierte la respuesta a JSON
                        .then(datos => {
                                if (datos.estado != "danger") {
                                        // Añade nuevas cards de productos al contenedor
                                        cards_productos.innerHTML += datos.cards;
                                        // Actualiza la variable de control según si hay más productos para cargar
                                        cargar_publicaciones_producto = datos.cargar_mas_publicaciones_producto;
                                } else {
                                        // Muestra un mensaje de alerta si hubo un error
                                        div_nav_producto.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                                }
                        }).catch(e => {
                                // Maneja errores de la solicitud
                                div_nav_producto.innerHTML = mensaje_alert_fijo("danger", e);
                        });
        }



        //Función para obtener y cargar datos de publicaciones
        function getDataPublicaciones(pagina_publicacion_informacion) {
                fetch(`lista_publicaciones_index.php?pagina_publicacion=${pagina_publicacion_informacion}`, {})
                        .then(respuesta => respuesta.json()) // Convierte la respuesta a JSON
                        .then(datos => {
                                if (datos.estado != "danger") {
                                        // Añade nuevas cards de publicaciones al contenedor
                                        cards_publicaciones.innerHTML += datos.cards;
                                        // Actualiza la variable de control según si hay más publicaciones para cargar
                                        cargar_publicaciones_informacion = datos.cargar_mas_publicaciones_informacion;
                                } else {
                                        // Muestra un mensaje de alerta si hubo un error
                                        div_nav_publicacion.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                                }
                        }).catch(e => {
                                // Muestra un mensaje error de la solicitud
                                div_nav_publicacion.innerHTML = mensaje_alert_fijo("danger", e);
                        });
        }
</script>