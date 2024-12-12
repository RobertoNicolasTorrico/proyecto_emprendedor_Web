<?php

//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/consultas_notificacion.php");
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_usuario.php");
include("../../../config/consultas_bd/consultas_producto.php");
include("../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/consultas_bd/consultas_categoria.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/funciones/funciones_generales.php");
include("../../../config/funciones/funciones_token.php");
include("../../../config/config_define.php");

$mensaje_error = "";
$datos_usuario = array();
$tipo_usuario = 0;

//Inicializacion de variables obtenidas de la URL
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';
$id_usuario_token = isset($_GET['token']) ? $_GET['token'] : '';

try {
    //Se verifica que los datos recibidos de la URL sean validos
    verificarUrlTokenId($id_usuario, $id_usuario_token);

    //Establecer la sesion
    session_start();

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica que los datos de sesion sean de un usuario administrador y que sea un usuario valido
    verificarDatosSessionUsuarioAdministrador($conexion);


    if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
        throw new Exception("La cuenta del usuario fue eliminada previamente. Por favor regrese a la página anterior");
    }

    //Se obtiene los datos del usuario
    $datos_usuario = obtenerDatosUsuarioEmprendedorYUsuarioComun($conexion, $id_usuario);

    if (empty($datos_usuario)) {
        throw new Exception("No se pudo obtener la informacion del usuario. Por favor actualiza la pagina");
    }

    //Se obtiene los datos de las categorias de los productos publicados del emprendedor
    $categorias_producto = obtenerCategoriasDeProductosEmprendedor($conexion, $id_usuario);

    //Se obtiene los datos de los estados que puede tener un produto
    $estados_producto = obtenerEstadosProducto($conexion);
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
    <title>Proyecto Emprendedor Admin datos de usuarios</title>

    <!--Enlace al archivo de estilos propios del proyecto-->
    <link href="../../../config/css/estilos.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Bootstrap-->
    <link href="../../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de FontAwesome para iconos-->
    <link href="../../../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Fancybox-->
    <link href="../../../lib/fancyapps-5.0.33/dist/fancybox/fancybox.css" rel="stylesheet" />

    <!--Enlace al archivo de estilos para Leaflet para mapas-->
    <link href="../../../lib/leaflet-1.9.4/leaflet.css" rel="stylesheet" />
</head>

<body>
    <!--Incluye el archivo de la barra de navegación para usuarios administrador.-->
    <?php include($url_navbar_usuario_admin);  ?>

    <!--Separa el contenido principal de la pagina del pie de pagina.-->
    <main>

        <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
        <div class="container-fluid mb-5">

            <?php if (empty($mensaje_error)) { ?>
                <h1 class="text-center">Informacion de la cuenta del usuario</h1>

                <!--Define una navegación por pestañas para alternar entre las vistas de datos personales, datos del emprendimiento, lista de productos, lista de publicaciones, emprendedores que sigue,los usuarios que lo siguen, preguntas hechas y preguntas recibidas-->
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="datos_personales_tab" data-bs-toggle="tab" data-bs-target="#datos_personales" type="button" role="tab" aria-controls="datos_personales" aria-selected="true">Datos Personales</button>

                        <!--Se verifica que el tipo de usuario sea un emprendedor-->
                        <?php if ($datos_usuario['id_tipo_usuario'] == 2) { ?>
                            <button class="nav-link" id="datos_emprendimiento_tab" data-bs-toggle="tab" data-bs-target="#datos_emprendimiento" type="button" role="tab" aria-controls="datos_emprendimiento" aria-selected="false">Datos del Emprendimiento</button>
                            <button class="nav-link" id="datos_emprendimiento_productos_tab" data-bs-toggle="tab" data-bs-target="#datos_emprendimiento_productos" type="button" role="tab" aria-controls="datos_emprendimiento_productos" aria-selected="false">Productos</button>
                            <button class="nav-link" id="datos_emprendimiento_publicaciones_tab" data-bs-toggle="tab" data-bs-target="#datos_emprendimiento_publicaciones" type="button" role="tab" aria-controls="datos_emprendimiento_publicaciones" aria-selected="false">Publicaciones</button>
                            <button class="nav-link" id="datos_seguidores_tab" data-bs-toggle="tab" data-bs-target="#datos_seguidores" type="button" role="tab" aria-controls="datos_seguidores" aria-selected="false">Seguidores</button>

                        <?php  } ?>

                        <button class="nav-link " id="datos_seguidos_tab" data-bs-toggle="tab" data-bs-target="#datos_seguidos" type="button" role="tab" aria-controls="datos_seguidos" aria-selected="false">Seguidos</button>
                        <button class="nav-link" id="preguntas_hechas_tab" data-bs-toggle="tab" data-bs-target="#preguntas_hechas" type="button" role="tab" aria-controls="preguntas_hechas" aria-selected="false">Preguntas hechas</button>


                        <!--Se verifica que el tipo de usuario sea un emprendedor-->
                        <?php if ($datos_usuario['id_tipo_usuario'] == 2) { ?>
                            <button class="nav-link" id="datos_emprendimiento_preguntas_recibidas_tab" data-bs-toggle="tab" data-bs-target="#preguntas_recibidas" type="button" role="tab" aria-controls="preguntas_recibidas" aria-selected="false">Preguntas recibidas</button>
                        <?php  } ?>

                    </div>
                </nav>

                <!--Contenedor para el contenido de las pestañas.La pestaña de datos personales esta activa por defecto.-->

                <div class="tab-content" id="nav-tabContent">

                    <!--Contenedor de los datos personales del usuario.-->
                    <div class="tab-pane fade show active" id="datos_personales" role="tabpanel" aria-labelledby="datos_personales_tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12 col-sm-12 col-md-8 col-lg-6">

                                <!--Formulario para enviar los datos-->
                                <form id="formulario_modificar_datos_personales" method="POST">

                                    <!-- Card -->
                                    <div class="card">

                                        <!-- Body del Card -->
                                        <div class="card-body">

                                            <h5 class="text-center">Datos personales del usuario</h5>

                                            <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                            <div id="alert_modificar_datos_personales"></div>


                                            <!-- Contenedor para mostrar los datos del usuario -->
                                            <div class="row g-2">

                                                <!-- Div que contiene el campo nombre de usuario -->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                                    <input type="text" class="form-control" name="txt_usuario" id="txt_usuario" placeholder="Nombre de usuario" minlength="5" maxlength="20" data-max="20" value="<?php echo ($datos_usuario['nombre_usuario']); ?>" required>
                                                    <label for="txt_usuario">Nombre de usuario</label>
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                        <span class="form-text">Minimo 5 y Maximo 20 caracteres.<span id="txaCountNombreUsuario">20 restantes</span></span>
                                                    </div>
                                                </div>


                                                <!-- Div que contiene el campo nombres y muestra la cantidad maxima de caracteres-->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-6 form-floating mb-3">
                                                    <input type="text" class="form-control" name="txt_nombres" id="txt_nombres" placeholder="Nombres" minlength="1" maxlength="100" data-max="100" value="<?php echo ($datos_usuario['nombres']); ?>" required>
                                                    <label for="txt_nombres">Nombres</label>
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                        <span class="form-text">Maximo 100 caracteres.<span id="txaCountNombres">100 restantes</span></span>
                                                    </div>
                                                </div>

                                                <!-- Div que contiene el campo apellidos y muestra la cantidad maxima de caracteres-->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-6 form-floating mb-3">
                                                    <input type="text" class="form-control" name="txt_apellidos" id="txt_apellidos" placeholder="Apellidos" minlength="1" maxlength="100" data-max="100" value="<?php echo ($datos_usuario['apellidos']); ?>" required>
                                                    <label for="txt_apellidos">Apellidos</label>
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                        <span class="form-text">Maximo 100 caracteres.<span id="txaCountApellidos">100 restantes</span></span>
                                                    </div>
                                                </div>



                                                <!-- Div que contiene el select para mostrar el tipo de usuario -->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-6 mb-3">
                                                    <div class="input-group">
                                                        <span class="input-group-text" for="tipo_usuario">Tipo de Usuario:</span>
                                                        <select class="form-select" name="tipo_usuario" id="tipo_usuario" disabled required>

                                                            <?php if ($datos_usuario['id_tipo_usuario'] == 1) { ?>
                                                                <option value="Comun" selected>Común</option>
                                                            <?php  } else {  ?>
                                                                <option value="Emprendedor" selected>Emprendedor</option>
                                                            <?php  }  ?>

                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Div que contiene el email -->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-7 form-floating mb-3">
                                                    <input type="email" class="form-control" name="txt_email" id="txt_email" placeholder="Email" minlength="2" maxlength="320" data-max="320" value="<?php echo ($datos_usuario['email']); ?>" required>
                                                    <label for="txt_email">Email</label>
                                                </div>


                                                <!-- Div que contiene el campo fecha de registro -->
                                                <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                    <div class="input-group">
                                                        <span class="input-group-text">Fecha de registro:</span>
                                                        <input type="datetime-local" name="fecha_registro" id="fecha_registro" placeholder="Fecha registro" class="form-control" value="<?php echo ($datos_usuario['fecha']); ?>" required>
                                                    </div>
                                                </div>


                                                <!-- Div que contiene  selects para mostrar el estado de la cuenta -->
                                                <div class="col-auto col-sm-auto col-md-auto col-lg-12 mb-3">
                                                    <div class="row">
                                                        <!-- Div que contiene el select para mostrar si esta baneado el usurio -->
                                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                            <div class="input-group">
                                                                <span class="input-group-text" for="estado_baneado">Estado Baneado:</span>
                                                                <select class="form-select" name="estado_baneado" id="estado_baneado" required>

                                                                    <?php if ($datos_usuario['baneado']) { ?>
                                                                        <option value="1" selected>Si</option>
                                                                        <option value="0">No</option>

                                                                    <?php  } else {  ?>
                                                                        <option value="0" selected>No</option>
                                                                        <option value="1">Si</option>
                                                                    <?php  }  ?>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <!-- Div que contiene el select para mostrar si la cuenta del usuario esta activa -->
                                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                            <div class="input-group">
                                                                <span class="input-group-text" for="estado_activo">Estado Activo:</span>
                                                                <select class="form-select" name="estado_activo" id="estado_activo" disabled>

                                                                    <?php if ($datos_usuario['activado']) { ?>
                                                                        <option value="1" selected>Si</option>
                                                                    <?php  } else {  ?>
                                                                        <option value="0" selected>No</option>
                                                                    <?php  }  ?>
                                                                </select>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="tipo_modificacion_usuario" id="tipo_modificacion_usuario" value="usuario" required>

                                        </div>


                                        <!-- Footer del Card  -->
                                        <div class="card-footer">

                                            <!-- Botón para abrir el modal para eliminar la cuenta del usuario -->
                                            <button type="button" class="btn btn-outline-danger mb-1" data-bs-toggle="modal" data-bs-target="#modalEliminarCuenta">Eliminar cuenta</button>

                                            <!-- Botón para enviar los cambios hecho -->
                                            <button class="btn btn-outline-success mb-1" type="submit">Guardar cambios</button>


                                            <!-- Botón para abrir el modal cambiar la contraseña del usuario -->
                                            <button type="button" class="btn btn-outline-secondary mb-1" data-bs-toggle="modal" data-bs-id="<?php echo ($datos_usuario['id_usuario']); ?>" data-bs-target="#modalCambioPassword">Cambiar contraseña</button>

                                            <!--Se verifica si la cuenta del usuario esta activada-->
                                            <?php if ($datos_usuario['activado']) { ?>
                                                <!-- Botón para abrir el modal desactivar la cuenta del usuario -->
                                                <button type="button" class="btn btn-outline-danger mb-1" data-bs-toggle="modal" data-bs-target="#modalDesactivarCuenta">Desactivar Cuenta</button>

                                            <?php  } else { ?>

                                                <!-- Botón para abrir el modal activar la cuenta del usuario -->
                                                <button type="button" class="btn btn-outline-success mb-1" data-bs-toggle="modal" data-bs-target="#modalActivarCuenta">Activar Cuenta</button>
                                            <?php  } ?>

                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>


                    <!--Se verifica que el tipo de usuario sea un emprendedor-->
                    <?php if ($datos_usuario['id_tipo_usuario'] == 2) { ?>


                        <!--Contenedor de los datos del emprendimiento.-->
                        <div class="tab-pane fade" id="datos_emprendimiento" role="tabpanel" aria-labelledby="datos_emprendimiento_tab" tabindex="0">
                            <div class="row justify-content-center">
                                <div class="col-12 col-sm-12 col-md-8 col-lg-10">

                                    <!--Formulario para enviar los datos-->
                                    <form id="formulario_modificar_datos_emprendedores" method="POST" enctype="multipart/form-data" novalidate>

                                        <!-- Card -->
                                        <div class="card">

                                            <!-- Body del Card -->
                                            <div class="card-body">
                                                <h5 class="text-center">Datos del Emprendimiento</h5>
                                                <div id="alert_modificar_datos_emprendedores"></div>

                                                <div class="row">
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                                                        <div class="row g-2">


                                                            <!-- Div que contiene el campo del nombre del emprendimiento y muestra la cantidad maxima de caracteres permitidos-->
                                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                                                <input type="text" class="form-control" name="txt_emprendimiento" id="txt_emprendimiento" minlength="1" maxlength="50" data-max="50" placeholder="Nombre del Empredimiento" value="<?php echo ($datos_usuario['nombre_emprendimiento']); ?>" required>
                                                                <label for="txt_emprendimiento">Nombre del emprendimiento</label>
                                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                                    <span class="form-text">Maximo 50 caracteres.<span id="txaCountEmpredmiento">50 restantes</span></span>
                                                                </div>
                                                            </div>


                                                            <!-- Div que contiene la calificacion del emprendedor y muestra la cantidad maxima de caracteres permitidos-->
                                                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                                <div class="input-group">
                                                                    <span class="input-group-text" for="num_calificacion">Calificacion del emprendedor:</span>
                                                                    <select class="form-select" name="num_calificacion" id="num_calificacion">
                                                                        <?php if (is_null($datos_usuario['calificacion_emprendedor'])) { ?>
                                                                            <option value="null" selected>Sin calificacion</option>
                                                                            <?php for ($i = 0; $i <= 5; $i++) {  ?>
                                                                                <option value="<?php echo ($i) ?>"><?php echo ($i) ?></option>

                                                                            <?php  }   ?>

                                                                        <?php  } else { ?>

                                                                            <option value="null">Sin calificacion</option>
                                                                            <?php for ($i = 0; $i <= 5; $i++) {  ?>
                                                                                <?php if ($datos_usuario['calificacion_emprendedor'] == $i) { ?>
                                                                                    <option value="<?php echo ($i) ?>" selected><?php echo ($i) ?></option>

                                                                                <?php  } else { ?>
                                                                                    <option value="<?php echo ($i) ?>"><?php echo ($i) ?></option>

                                                                                <?php  }   ?>

                                                                            <?php  }   ?>

                                                                        <?php  }   ?>
                                                                    </select>
                                                                </div>
                                                            </div>


                                                            <!-- Div que contiene el campo descripcion del emprendimiento y muestra la cantidad maxima de caracteres-->
                                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                                                <textarea class="form-control" name="txt_descripcion" placeholder="Descripcion del emprendimiento" id="txt_descripcion" minlength="1" maxlength="150" data-max="150" rows="10" cols="50" required style="height: 130px;"><?php echo ($datos_usuario['descripcion']); ?></textarea>
                                                                <label for="txt_descripcion">Descripcion del emprendimiento</label>
                                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                                    <span class="form-text">Maximo 150 caracteres.<span id="txaCountDescrip">150 restantes</span></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Div que contiene la foto del perfil del emprendimiento-->
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                                                        <h6 class="text-center">Foto del perfil</h6>
                                                        <div class="row g-2 d-flex justify-content-center">
                                                            <div class="col-7 col-sm-6 col-md-5 col-lg-7">
                                                                <?php if (is_null($datos_usuario["foto_perfil_nombre"])) {
                                                                    $ruta_archivo = $url_base . "/img/foto_perfil/foto_de_perfil_predeterminado.jpg";
                                                                } else {
                                                                    $ruta_archivo = $url_base . "/uploads/{$datos_usuario["id_usuario_emprendedor"]}/foto_perfil/{$datos_usuario["foto_perfil_nombre"]}";
                                                                } ?>

                                                                <img class="card-img-top imagen_perfil" id="foto_perfil_emprendedor" src="<?php echo ($ruta_archivo); ?>" alt="Foto de perfil">

                                                            </div>
                                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                                <div class="input-group mb-3">
                                                                    <input type="file" class="form-control" id="input_foto_perfil" accept="image/jpeg, image/jpg, image/png">
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="tipo_modificacion_emprendedor" id="tipo_modificacion_emprendedor" value="emprendedor" required>

                                                </div>


                                            </div>

                                            <!-- Footer del Card  -->
                                            <div class="card-footer">

                                                <!-- Botón para enviar los cambios hechos -->
                                                <button class="btn btn-outline-success" type="submit">Guardar cambios</button>

                                                <!-- Direcciona al administrador al perfil del emprendedor-->
                                                <?php $token_emprendedor = hash_hmac('sha1', $datos_usuario['id_usuario_emprendedor'], KEY_TOKEN);  ?>
                                                <a class="btn btn-outline-success m-1" target="_blank" href="<?php echo ($url_base) ?>/usuarios/usuario_emprendedor/seccion/perfil/pagina_perfil.php?id=<?php echo ($datos_usuario['id_usuario_emprendedor']); ?>&token=<?php echo ($token_emprendedor); ?>"><i class="fa-solid fa-user"></i> Perfil del emprendedor</a>
                                            </div>

                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>


                        <!--Contenedor de los datos de los productos del emprendedor.-->
                        <div class="tab-pane fade" id="datos_emprendimiento_productos" role="tabpanel" aria-labelledby="datos_emprendimiento_productos_tab" tabindex="0">

                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">

                                        <div class="row">

                                            <!--Campo de busqueda por el nombre del producto-->
                                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                                    <input type="search" class="form-control" placeholder="Nombre del producto" aria-label="Buscar producto" name="campo_buscar_producto" id="campo_buscar_producto">
                                                </div>
                                            </div>


                                            <!--Buscar por fecha que se publico-->
                                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text">Fecha de publicacion:</span>
                                                    <input type="date" name="fecha_inicio_producto" id="fecha_inicio_producto" class="form-control">
                                                </div>
                                            </div>

                                            <!--Filtro por categoria del producto-->
                                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text" for="num_categoria_producto">Categoria:</span>
                                                    <select class="form-select" name="num_categoria_producto" id="num_categoria_producto">
                                                        <option value="0" selected>Todos</option>
                                                        <?php foreach ($categorias_producto as $categoria) { ?>
                                                            <option value="<?php echo $categoria['id_categoria_producto']; ?>"><?php echo $categoria['nombre_categoria']; ?></option>
                                                        <?php } ?>

                                                    </select>
                                                </div>
                                            </div>


                                            <!--Filtro por estado del producto-->
                                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text" for="num_estado_producto">Estado:</span>
                                                    <select class="form-select" name="num_estado_producto" id="num_estado_producto">
                                                        <option value="0" selected>Todos</option>
                                                        <?php foreach ($estados_producto as $estado) { ?>
                                                            <option value="<?php echo $estado['id_estado_producto']; ?>"><?php echo $estado['estado']; ?></option>
                                                        <?php } ?>

                                                    </select>
                                                </div>
                                            </div>

                                            <!--Selector de cantidad de productos a mostrar-->
                                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text" for="cant_registro_producto">Mostrar:</span>
                                                    <select class="form-select" name="cant_registro_producto" id="cant_registro_producto">
                                                        <option value="5">5</option>
                                                        <option value="10">10</option>
                                                        <option value="25">25</option>
                                                        <option value="50">50</option>
                                                        <option value="100">100</option>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                    <div id="alert_notificacion_producto"></div>
                                </div>

                                <!--Contenedor con las cards que contiene los producto-->
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="row justify-content-center" id="card_productos"></div>
                                </div>

                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">

                                    <div class="row">
                                        <div class="col-6 col-sm-6 col-md-6 col-lg-6">

                                            <!--Informacion sobre la cantidad de resultados que se ve en la interfaz-->
                                            <p id="lbl-totalResultadoProducto"></p>

                                            <!--Informacion sobre las paginas disponibles -->
                                            <p id="lbl-totalPaginaProducto"></p>

                                        </div>

                                        <!--Contenedor para la navegacion de la paginacion -->
                                        <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacionProducto"></div>
                                    </div>

                                </div>

                            </div>
                        </div>


                        <!--Contenedor de los datos de las publicaciones del emprendedor.-->
                        <div class="tab-pane fade" id="datos_emprendimiento_publicaciones" role="tabpanel" aria-labelledby="datos_emprendimiento_publicaciones_tab" tabindex="0">
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">

                                    <div class="row">
                                        <!--Buscar por fecha que se hizo la publicacion-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text">Buscar por fecha de publicacion:</span>
                                                <input type="date" name="fecha_inicio_publicacion" id="fecha_inicio_publicacion" class="form-control">
                                            </div>
                                        </div>

                                        <!--Selector de cantidad de publicaciones a mostrar-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text" for="cant_registro">Mostrar:</span>
                                                <select class="form-select" name="cant_registro_publicacion" id="cant_registro_publicacion">
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
                                    <div id="alert_notificacion_informacion"></div>
                                </div>

                                <!--Contenedor con las cards que contiene las publicaciones-->
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="row justify-content-center" id="cards_publicaciones"></div>
                                </div>

                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="row">
                                        <div class="col-6 col-sm-6 col-md-6 col-lg-6">

                                            <!--Informacion sobre la cantidad de resultados que se ve en la interfaz-->
                                            <p id="lbl-totalResultadoPublicacion"></p>

                                            <!--Informacion sobre las paginas disponibles -->
                                            <p id="lbl-totalPaginaPublicacion"></p>

                                        </div>

                                        <!--Contenedor para la navegacion de la paginacion -->
                                        <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacionPublicacion"></div>
                                    </div>
                                </div>


                            </div>
                        </div>

                        <!--Contenedor de los datos de los seguidores del emprendedor.-->
                        <div class="tab-pane fade" id="datos_seguidores" role="tabpanel" aria-labelledby="datos_seguidores_tab" tabindex="0">
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class=row>


                                        <!--Campo de busqueda por el nombre de usuario o nombre completo-->
                                        <div class="col-12 col-sm-12 col-md-10 col-lg-6 mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                                <input type="search" class="form-control" placeholder="Buscar por nombre de usuario/nombre completo" aria-label="Buscar seguidor" name="buscar_seguidor" id="txt_buscar_seguidor">
                                            </div>
                                        </div>

                                        <!--Buscar por fecha que lo empezo a seguir-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text">Fecha desde que lo sigue:</span>
                                                <input type="date" name="fecha_inicio_seguidor" id="fecha_inicio_seguidor" class="form-control">
                                            </div>
                                        </div>

                                        <!--Selector de cantidad de seguidores a mostrar-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text" for="cant_registro_seguidor">Mostrar:</span>
                                                <select class="form-select" name="cant_registro_seguidor" id="cant_registro_seguidor">
                                                    <option value="5">5</option>
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                        <div id="alert_notificacion_seguidor"></div>
                                    </div>
                                </div>


                                <!--Contenedor con la tabla que contiene a los seguidores-->
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="table-responsive" id="tabla_seguidor"></div>
                                </div>

                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="row">
                                        <div class="col-6 col-sm-6 col-md-6 col-lg-6">

                                            <!--Informacion sobre la cantidad de resultados que se ve en la interfaz-->
                                            <p id="lbl-totalResultadoSeguidor"></p>

                                            <!--Informacion sobre las paginas disponibles -->
                                            <p id="lbl-totalPaginaSeguidor"></p>

                                        </div>

                                        <!--Contenedor para la navegacion de la paginacion -->
                                        <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacionSeguidor"></div>
                                    </div>
                                </div>



                            </div>
                        </div>

                        <!--Contenedor de la preguntas recibidas de los productos del emprendedor.-->
                        <div class="tab-pane fade" id="preguntas_recibidas" role="tabpanel" aria-labelledby="datos_emprendimiento_preguntas_recibidas_tab" tabindex="0">
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="row">

                                        <!--Campo de busqueda por el nombre del producto-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                                <input type="search" class="form-control" placeholder="Nombre del producto" aria-label="Buscar producto" name="campo_buscar_preguntas_recibidas_pro" id="campo_buscar_preguntas_recibidas_pro">
                                            </div>
                                        </div>

                                        <!--Campo de busqueda por el nombre de usuario-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                                <input type="search" class="form-control" placeholder="Nombre de usuario" aria-label="Buscar usuario" name="campo_buscar_preguntas_recibidas_usu" id="campo_buscar_preguntas_recibidas_usu">
                                            </div>
                                        </div>


                                        <!--Filtro por estado del producto-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text" for="num_estado_preguntas_recibidas">Estado del producto:</span>
                                                <select class="form-select" name="num_estado_preguntas_recibidas" id="num_estado_preguntas_recibidas">
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
                                                <input type="date" name="fecha_inicio_preguntas_recibidas" id="fecha_inicio_preguntas_recibidas" class="form-control">
                                            </div>
                                        </div>


                                        <!--Filtro por el estado de las preguntas-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text" for="filtro_preguntas_recibidas">Filtrar preguntas:</span>
                                                <select class="form-select" name="filtro_preguntas_recibidas" id="filtro_preguntas_recibidas">
                                                    <option value="Todas">Todas</option>
                                                    <option value="Respondidas">Respondidas</option>
                                                    <option value="NoRespondidas">No Respondidas</option>

                                                </select>
                                            </div>
                                        </div>

                                        <!--Selector de cantidad de preguntas a mostrar-->
                                        <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text" for="cant_registro_preguntas_recibidas">Mostrar:</span>
                                                <select class="form-select" name="cant_registro_preguntas_recibidas" id="cant_registro_preguntas_recibidas">
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
                                    <div class="row justify-content-center" id="card_preguntas_recibidas"></div>
                                </div>

                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="row">
                                        <div class="col-6 col-sm-6 col-md-6 col-lg-6">
                                            <!--Informacion sobre la cantidad de resultados que se ve en la interfaz-->
                                            <p id="lbl-totalResultadoPreguntasRecibidas"></p>

                                            <!--Informacion sobre las paginas disponibles -->
                                            <p id="lbl-totalPaginaPreguntasRecibidas"></p>
                                        </div>

                                        <!--Contenedor para la navegacion de la paginacion -->
                                        <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacionPreguntasRecibidas"></div>
                                    </div>
                                </div>


                            </div>
                        </div>


                    <?php  } ?>

                    <!--Contenedor de los datos de los emprendedores que sigue el usuario.-->
                    <div class="tab-pane fade" id="datos_seguidos" role="tabpanel" aria-labelledby="datos_seguidos_tab" tabindex="0">

                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class=row>


                                    <!--Campo de busqueda por el nombre del emprendimiento/usuario-->
                                    <div class="col-12 col-sm-12 col-md-10 col-lg-6 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                            <input type="search" class="form-control" placeholder="Buscar por nombre del emprendimiento/usuario" aria-label="Buscar emprendedor" name="buscar_seguidos" id="txt_buscar_seguidos">
                                        </div>
                                    </div>


                                    <!--Buscar por fecha que lo empezo a seguir-->
                                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text">Fecha desde que lo sigue:</span>
                                            <input type="date" name="fecha_inicio_seguidos" id="fecha_inicio_seguidos" class="form-control">
                                        </div>
                                    </div>

                                    <!--Selector de cantidad de emprendedores a mostrar-->
                                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text" for="cant_registro_seguidos">Mostrar:</span>
                                            <select class="form-select" name="cant_registro_seguidos" id="cant_registro_seguidos">
                                                <option value="5">5</option>
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                    <div id="alert_notificacion_seguidos"></div>
                                </div>
                            </div>


                            <!--Contenedor con las cards que contiene el producto con las preguntas recibidas y con las respuestas hechas-->
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="table-responsive" id="tabla_seguidos"></div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="row">
                                    <div class="col-6 col-sm-6 col-md-6 col-lg-6">
                                        <!--Informacion sobre la cantidad de resultados que se ve en la interfaz-->
                                        <p id="lbl-totalResultadoSeguidos"></p>

                                        <!--Informacion sobre las paginas disponibles -->
                                        <p id="lbl-totalPaginaSeguidos"></p>
                                    </div>

                                    <!--Contenedor para la navegacion de la paginacion -->
                                    <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacionSeguidos"></div>
                                </div>
                            </div>

                        </div>
                    </div>


                    <!--Contenedor de la preguntas hechas a los producto de los emprendedor.-->
                    <div class="tab-pane fade" id="preguntas_hechas" role="tabpanel" aria-labelledby="preguntas_hechas_tab" tabindex="0">

                        <div class="row justify-content-center">
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="row">

                                    <!--Campo de busqueda por el nombre del producto-->
                                    <div class="col-12 col-sm-auto col-md-auto col-lg-3 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                            <input type="search" class="form-control" placeholder="Nombre del producto" aria-label=">Nombre del producto" name="campo_buscar_mis_perguntas" id="campo_buscar_mis_perguntas">
                                        </div>
                                    </div>


                                    <!--Filtro por estado del producto-->
                                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text" for="num_estado_mis_perguntas">Estado de la publicacion:</span>
                                            <select class="form-select" name="num_estado_mis_perguntas" id="num_estado_mis_perguntas">
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
                                            <span class="input-group-text" for="cant_dias_mis_perguntas">Mostrar:</span>
                                            <select class="form-select" name="cant_dias_mis_perguntas" id="cant_dias_mis_perguntas">
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
                                            <span class="input-group-text" for="cant_registro_mis_perguntas">Cantidad de preguntas:</span>
                                            <select class="form-select" name="cant_registro_mis_perguntas" id="cant_registro_mis_perguntas">
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
                                <div id="alert_notificacion_mis_perguntas"></div>
                            </div>
                            <!--Contenedor con las cards que contiene las preguntas hechas-->
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div id="tabla_mis_perguntas"></div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="row">
                                    <div class="col-6 col-sm-6 col-md-6 col-lg-6">
                                        <!--Informacion sobre la cantidad de resultados que se ve en la interfaz-->
                                        <p id="lbl-totalResultadoMisPreguntas"></p>

                                        <!--Informacion sobre las paginas disponibles -->
                                        <p id="lbl-totalPaginaMisPreguntas"></p>
                                    </div>

                                    <!--Contenedor para la navegacion de la paginacion -->
                                    <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacionMisPreguntas"></div>
                                </div>
                            </div>

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
    <script src="../../../config/js/funciones.js"></script>
    <script src="../../../lib/leaflet-1.9.4/leaflet.js"></script>
    <script src="../../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../../lib/fancyapps-5.0.33/dist/fancybox/fancybox.umd.js"></script>
    <!--Verifica que no halla ningun mensaje de errro-->
    <?php if (empty($mensaje_error)) {

        require("modal_cambio_password.php");
        require("preguntas/modal_modificar_pregunta.php");
        require("preguntas/modal_eliminar_pregunta.php");
        require("seguidores_seguidos/modal_eliminar_seguidor_emprendedor.php");
        require("modal_baja_usuario.php");

        //Verifica que la cuenta del usuario esta activada
        if ($datos_usuario['activado']) {
            require("modal_desactivar_cuenta.php");
        } else {
            require("modal_activar_cuenta.php");
        }

        //Verifica que la cuenta del usuario sea un emprendedor
        if ($datos_usuario['id_tipo_usuario'] == 2) {
            require("publicacion/modal_eliminar_publicacion.php");
            require("publicacion/modal_mapa_publicacion.php");
            require("publicacion/modal_modificar_publicacion.php");
            require("seguidores_seguidos/modal_eliminar_seguidor.php");
            require("producto/modal_eliminar_producto.php");
            require("preguntas_respuesta/modal_eliminar_respuesta.php");
            require("preguntas_respuesta/modal_modificar_respuesta.php");
        }
    } ?>
    <?php require("../../../template/footer.php"); ?>

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

    var datos_usuario = [<?php echo json_encode($datos_usuario); ?>];
    var js_datos_usuario = datos_usuario[0];

    if (js_datos_usuario.length = !0) {

        /*Seccion de los datos personales del usuario */


        // Se obtiene el formulario para modificar los datos personales
        var form_modificar_datos_personales = document.getElementById("formulario_modificar_datos_personales");


        //Funcion para mostrar la cantidad restantes de caracteres en el campo nombre de usuario
        contarMostrarCarecteresRestantes('txt_usuario', 'txaCountNombreUsuario');

        //Funcion para mostrar la cantidad restantes de caracteres en el campo nombres
        contarMostrarCarecteresRestantes('txt_nombres', 'txaCountNombres');

        //Funcion para mostrar la cantidad restantes de caracteres en el campo apellidos
        contarMostrarCarecteresRestantes('txt_apellidos', 'txaCountApellidos');

        //Agrega un evento para contar y mostrar caracteres restantes en el campo nombre de usuarios
        document.getElementById('txt_usuario').addEventListener('input', function() {
            contarMostrarCarecteresRestantes('txt_usuario', 'txaCountNombreUsuario');
        });

        //Agrega un evento para contar y mostrar caracteres restantes en el campo nombres
        document.getElementById('txt_nombres').addEventListener('input', function() {
            contarMostrarCarecteresRestantes('txt_nombres', 'txaCountNombres');
        });

        //Agrega un evento para contar y mostrar caracteres restantes en el campo apellidos
        document.getElementById('txt_apellidos').addEventListener('input', function() {
            contarMostrarCarecteresRestantes('txt_apellidos', 'txaCountApellidos');
        });


        //Manejo del envio del formulario para modificar los datos personales del usuario
        form_modificar_datos_personales.addEventListener('submit', function(event) {

            //Previene el envio por defecto del formulario
            event.preventDefault();

            //Elimina cualquier alerta previa 
            var alert_modificar_datos_personales = document.getElementById("alert_modificar_datos_personales");
            alert_modificar_datos_personales.innerHTML = "";


            var txt_usuario = document.getElementById('txt_usuario');
            var txt_nombres = document.getElementById('txt_nombres');
            var txt_apellidos = document.getElementById('txt_apellidos');
            var txt_email = document.getElementById('txt_email');
            var fecha_registro = document.getElementById('fecha_registro');
            var estado_activo = document.getElementById('estado_activo');

            var tipo_modificacion_usuario = document.getElementById('tipo_modificacion_usuario');
            var campos_verificar = [txt_usuario, txt_nombres, txt_apellidos, txt_email, fecha_registro, estado_activo];
            var campos_verificar_txt = [txt_usuario, txt_nombres, txt_apellidos, txt_email];

            var js_nombre_usuario = js_datos_usuario['nombre_usuario'];
            var js_usuario_nombres = js_datos_usuario['nombres'];
            var js_usuario_apellidos = js_datos_usuario['apellidos'];
            var js_email = js_datos_usuario['email'];
            var js_fecha_registro = js_datos_usuario['fecha'];
            var js_baneado = js_datos_usuario['baneado'];

            var fecha_hora_formateada = devolverFechaDateTimeLocalInput(fecha_registro);


            //Se compara los datos originales del usuario con los datos actuales para saber si hubo cambios o no
            if (txt_usuario.value == js_nombre_usuario && txt_nombres.value == js_usuario_nombres && txt_apellidos.value == js_usuario_apellidos && txt_email.value == js_email && fecha_hora_formateada == js_fecha_registro && estado_baneado.value == js_baneado) {
                alert_modificar_datos_personales.innerHTML = mensaje_alert_dismissible("info", "No hubo cambios en los datos del usuario");
                return false;
            }


            //Valida que los campos no esten vacios
            if (validarCampoVacio(campos_verificar)) {
                alert_modificar_datos_personales.innerHTML = mensaje_alert_fijo("danger", "Por favor complete todos los campos");
                return false;
            }


            //Verifica que el tipo de modificacion que se va hacer sea usuario
            if (tipo_modificacion_usuario.value != "usuario") {
                alert_modificar_datos_personales.innerHTML = mensaje_alert_fijo('danger', "No se puede modificar el valor del tipo de modificacion");
                return false;
            }


            //Se verifica que el campo nombre de usuario no tenga espacios en su contenido
            if (txt_usuario.value.includes(' ')) {
                alert_modificar_datos_personales.innerHTML = mensaje_alert_fijo("danger", "El nombre de usuario no puede tener espacios en blanco");
                return false;
            }

            //Se verifica que el campo nombre de usuario no tenga menos de 5 caracteres y que no tenga mas 20 de caracteres
            if ((txt_usuario.value.length < 5) || (txt_usuario.value.length > 20)) {
                alert_modificar_datos_personales.innerHTML = mensaje_alert_fijo("danger", "El campo nombre de usuario debe tener entre 5 y 20 caracteres");
                return false;
            }


            //Valida que los campos no tengan espacios al inicio o al final de la cadena
            var lista_trim_input = listaInputEspacioBlancoIF(campos_verificar_txt);
            if (lista_trim_input.length > 0) {
                alert_modificar_datos_personales.innerHTML = mensaje_alert_lista_fijo("danger", "No se permite que los campos tengan espacios en blanco al inicio o al final. Los siguientes campos no cumplen con esto:", lista_trim_input);
                return false;
            }

            //Verifica que los campos nombres y apellidos tengan una longitud valida
            var lista_length_input = listaInputLengthNoValidos(campos_verificar_txt);
            if (lista_length_input.length > 0) {
                alert_modificar_datos_personales.innerHTML = mensaje_alert_lista_fijo("danger", "Los siguientes campos no cumplen con la longitud mínima de 1 carácter o el máximo de caracteres indicado:", lista_length_input);
                return false;
            }

            //Se verifica que el valor del select sea un valor numerico
            if (isNaN(estado_baneado.value.trim())) {
                alert_modificar_datos_personales.innerHTML = mensaje_alert_fijo("danger", "El select del estado baneado no recibio un valor numerico valido");
                return false;
            }


            //Valida que el campo email sea valido
            if (!validarCampoEmail(txt_email)) {
                alert_modificar_datos_personales.innerHTML = mensaje_alert_fijo("danger", "Por favor ingrese un email con formato valido");
                return false;
            }

            const formData = new FormData();
            formData.append('nombre_usuario', txt_usuario.value.trim());
            formData.append('nombres', txt_nombres.value.trim());
            formData.append('apellidos', txt_apellidos.value.trim());
            formData.append('email', txt_email.value.trim());
            formData.append('fecha_registro', fecha_hora_formateada);
            formData.append('estado_baneado', estado_baneado.value.trim());
            formData.append('tipo_modificacion', tipo_modificacion_usuario.value.trim());

            fetch(`modificar_datos_usuario.php${window.location.search}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(datos => {

                    if (datos.lista.length > 0) {

                        //Muestra un mensaje de error con una lista de campos que no cumplen con lo establecido
                        alert_modificar_datos_personales.innerHTML = mensaje_alert_lista_fijo(datos.estado, datos.mensaje, datos.lista);
                    } else {
                        if (datos.estado === 'success') {

                            js_datos_usuario['nombre_usuario'] = txt_usuario.value.trim();
                            js_datos_usuario['nombres'] = txt_nombres.value.trim();
                            js_datos_usuario['apellidos'] = txt_apellidos.value.trim();
                            js_datos_usuario['email'] = txt_email.value.trim();
                            js_datos_usuario['fecha'] = fecha_hora_formateada;
                            js_datos_usuario['baneado'] = estado_baneado.value.trim();

                            //Muestra un mensaje en la interfaz del usuario
                            alert_modificar_datos_personales.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);
                        } else {

                            //Muestra un mensaje en la interfaz del usuario
                            alert_modificar_datos_personales.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                        }
                    }

                })
                .catch(e => {
                    // Muestra un mensaje error de la solicitud
                    alert_modificar_datos_personales.innerHTML = mensaje_alert_fijo("danger", e);
                });


        });



        /*Seccion del usuario que muestra a los emprendedores que sigue  */

        //Inicializa la pagina actual y la cantidad actual de emprendedores que sigue
        var pagina_actual_seguidos = 1;
        var cant_actual_seguidos;


        // Carga los datos de los emprendedores que sigue 
        getDataTodosEmprendedorSiguiendo();


        //Agrega un evento cada vez que el campo buscar emprendedor cambie la pagina actual vuelve a 1 y se llama a la funcion de los emprendedores que sigue
        document.getElementById('txt_buscar_seguidos').addEventListener('input', function() {
            pagina_actual_seguidos = 1; //Reinicia la pagina a 1
            getDataTodosEmprendedorSiguiendo(); //Llama a la funcion para obtener los datos de los emprendedores que sigue
        });

        //Agrega un evento cada vez que la fecha cambie la pagina actual vuelve a 1 y se llama a la funcion de los emprendedores que sigue
        document.getElementById("fecha_inicio_seguidos").addEventListener("change", function() {
            pagina_actual_seguidos = 1; //Reinicia la pagina a 1
            getDataTodosEmprendedorSiguiendo(); //Llama a la funcion para obtener los datos de los emprendedores que sigue
        });


        //Agrega un evento cada vez que la cantidad de registro cambie la pagina actual vuelve a 1 y se llama a la funcion de los emprendedores que sigue
        document.getElementById("cant_registro_seguidos").addEventListener("change", function() {
            pagina_actual_seguidos = 1; //Reinicia la pagina a 1
            getDataTodosEmprendedorSiguiendo(); //Llama a la funcion para obtener los datos de los emprendedores que sigue
        });



        //Función para obtener y cargar datos de los emprendedores que sigue
        function getDataTodosEmprendedorSiguiendo() {

            //Elimina cualquier alerta previa 
            var alert_notificacion_seguidos = document.getElementById("alert_notificacion_seguidos");
            alert_notificacion_seguidos.innerHTML = "";

            var txt_buscar_seguidos = document.getElementById("txt_buscar_seguidos");
            var fecha_inicio_seguidos = document.getElementById("fecha_inicio_seguidos");
            var cant_registro_seguidos = document.getElementById("cant_registro_seguidos");
            var tabla_seguidos = document.getElementById("tabla_seguidos");
            var pagina = pagina_actual_seguidos;

            // Envío del formulario usando fetch
            const formData = new FormData();

            formData.append('fecha', fecha_inicio_seguidos.value);
            formData.append('cant_registro', cant_registro_seguidos.value);
            formData.append('numero_pagina', pagina);
            formData.append('campo_buscar_seguidos', txt_buscar_seguidos.value);
            fetch(`lista_seguidos.php${window.location.search}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json()) // Convierte la respuesta a JSON
                .then(datos => {
                    if (datos.estado !== 'danger') {
                        cant_actual_seguidos = datos.cantidad_actual
                        tabla_seguidos.innerHTML = datos.tabla;
                        document.getElementById("lbl-totalResultadoSeguidos").innerHTML = datos.registro;
                        document.getElementById("lbl-totalPaginaSeguidos").innerHTML = datos.pagina;
                        document.getElementById("nav-paginacionSeguidos").innerHTML = datos.paginacion;

                    } else {
                        // Muestra un mensaje de alerta si hubo un error
                        alert_notificacion_seguidos.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    }
                })
                .catch(error => {
                    // Muestra un mensaje error de la solicitud
                    alert_notificacion_seguidos.innerHTML = mensaje_alert_fijo("danger", error);
                });
        }


        //Funcion para cambiar la pagina y recargar los datos
        function nextPageSeguidoresEmprendedores(pagina) {
            pagina_actual_seguidos = pagina;
            getDataTodosEmprendedorSiguiendo();
        }




        /*Seccion del usuario que muestra las preguntas hechas por el mismo usuario  */



        //Inicializa la pagina actual y la cantidad actual de preguntas hechas
        var pagina_actual_mis_preguntas = 1;
        var cant_actual_registros_preguntas;

        // Carga los datos de las preguntas hechas por el usuarios
        getDataMisPreguntas();


        //Agrega un evento cada vez que el campo buscar producto cambie la pagina actual vuelve a 1 y se llama a la funcion de los emprendedores que sigue
        document.getElementById("campo_buscar_mis_perguntas").addEventListener("input", function() {
            pagina_actual_mis_preguntas = 1; //Reinicia la pagina a 1
            getDataMisPreguntas(); //Llama a la funcion para obtener los datos de las preguntas hechas por el usuario
        });


        //Agrega un evento cada vez que se cambie el estado la pagina actual vuelve a 1 y se llama a la funcion de las preguntas hechas
        document.getElementById("num_estado_mis_perguntas").addEventListener("change", function() {
            pagina_actual_mis_preguntas = 1; //Reinicia la pagina a 1
            getDataMisPreguntas(); //Llama a la funcion para obtener los datos de las preguntas hechas por el usuario
        });


        //Agrega un evento cada vez que la cantidad de registro cambie la pagina actual vuelve a 1 y se llama a la funcion de las preguntas hechas
        document.getElementById("cant_registro_mis_perguntas").addEventListener("change", function() {
            pagina_actual_mis_preguntas = 1; //Reinicia la pagina a 1
            getDataMisPreguntas(); //Llama a la funcion para obtener los datos de las preguntas hechas por el usuario
        });


        //Agrega un evento cada vez que la cantidad de dias cambie la pagina actual vuelve a 1 y se llama a la funcion de las preguntas hechas
        document.getElementById("cant_dias_mis_perguntas").addEventListener("change", function() {
            pagina_actual_mis_preguntas = 1; //Reinicia la pagina a 1
            getDataMisPreguntas(); //Llama a la funcion para obtener los datos de las preguntas hechas por el usuario
        });


        //Función para obtener y cargar datos de las preguntas hechas
        function getDataMisPreguntas() {


            //Elimina cualquier alerta previa 
            var alert_notificacion_mis_perguntas = document.getElementById("alert_notificacion_mis_perguntas");
            alert_notificacion_mis_perguntas.innerHTML = "";

            var campo_buscar = document.getElementById("campo_buscar_mis_perguntas");
            var cant_registro = document.getElementById("cant_registro_mis_perguntas");
            var estado = document.getElementById("num_estado_mis_perguntas");
            var cant_dias = document.getElementById("cant_dias_mis_perguntas");
            var tabla_pregunta = document.getElementById("tabla_mis_perguntas");
            var pagina = pagina_actual_mis_preguntas;

            // Envío del formulario usando fetch
            const formData = new FormData();
            formData.append('campo_buscar', campo_buscar.value);
            formData.append('cant_registro', cant_registro.value);
            formData.append('estado', estado.value);
            formData.append('cant_dias', cant_dias.value);
            formData.append('numero_pagina', pagina);

            fetch(`lista_preguntas.php${window.location.search}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json()) // Convierte la respuesta a JSON
                .then(datos => {
                    if (datos.estado != "danger") {

                        cant_actual_registros_preguntas = datos.cantidad_actual;
                        tabla_pregunta.innerHTML = datos.cards_pregunas;
                        document.getElementById("lbl-totalResultadoMisPreguntas").innerHTML = datos.registro;
                        document.getElementById("lbl-totalPaginaMisPreguntas").innerHTML = datos.pagina;
                        document.getElementById("nav-paginacionMisPreguntas").innerHTML = datos.paginacion;
                    } else {
                        // Muestra un mensaje de alerta si hubo un error
                        alert_notificacion_mis_perguntas.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);

                    }
                }).catch(e => {
                    // Muestra un mensaje error de la solicitud
                    alert_notificacion_mis_perguntas.innerHTML = mensaje_alert_fijo("danger", e);
                });
        }


        //Funcion para cambiar la pagina y recargar los datos
        function nextPagePregunta(pagina) {
            pagina_actual_mis_preguntas = pagina;
            getDataMisPreguntas();
        }


        //Se verifica que el usuario sea un usuario emprendedor
        if (js_datos_usuario['id_tipo_usuario'] === 2) {


            /*Seccion parar modificar los datos del emprendimiento del usuario emprendedor*/


            //Manejo del envio del formulario para modificar los datos del empredimiento del usuario
            var form_modificar_datos_emprendedores = document.getElementById("formulario_modificar_datos_emprendedores");


            var input_foto_perfil = document.getElementById('input_foto_perfil');


            //Agrega un evento cada vez que se haga un cambio en el input de la imagen
            input_foto_perfil.addEventListener('change', function() {
                var foto_perfil_emprendedor = document.getElementById('foto_perfil_emprendedor');
                if (input_foto_perfil.value !== '') {
                    if (validarExtensionImagen(input_foto_perfil.files[0])) {
                        if (validarTamanioImagen(input_foto_perfil.files[0])) {
                            const url = URL.createObjectURL(input_foto_perfil.files[0]);
                            foto_perfil_emprendedor.src = url;
                        } else {

                            //Muestra un mensaje en la interfaz del usuario
                            alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo('danger', "La imagen: " + input_foto_perfil.files[0].name + "  excede el tamaño maximo permitido de 10MB");
                            input_foto_perfil.value = "";
                        }
                    } else {
                        //Muestra un mensaje en la interfaz del usuario
                        alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo('danger', "El formato del archivo " + input_foto_perfil.files[0].name + " no es valido. Formatos permitidos: JPEG, JPG y PNG");
                        input_foto_perfil.value = "";
                    }
                }
            });



            //Funcion para mostrar la cantidad restantes de caracteres en el campo nombre del empredimiento
            contarMostrarCarecteresRestantes('txt_emprendimiento', 'txaCountEmpredmiento');

            //Funcion para mostrar la cantidad restantes de caracteres en el campo descripcion
            contarMostrarCarecteresRestantes('txt_descripcion', 'txaCountDescrip');


            //Agrega un evento para contar y mostrar caracteres restantes en el campo nombre del empredimiento
            document.getElementById('txt_emprendimiento').addEventListener('input', function() {
                contarMostrarCarecteresRestantes('txt_emprendimiento', 'txaCountEmpredmiento');
            });


            //Agrega un evento para contar y mostrar caracteres restantes en el campo de descripcion
            document.getElementById('txt_descripcion').addEventListener('input', function() {
                contarMostrarCarecteresRestantes('txt_descripcion', 'txaCountDescrip');
            });


            //Manejo del envio del formulario para modificar los datos del emprendimiento
            form_modificar_datos_emprendedores.addEventListener('submit', function(event) {


                //Previene el envio por defecto del formulario
                event.preventDefault();

                //Elimina cualquier alerta previa 
                var alert_modificar_datos_emprendedores = document.getElementById("alert_modificar_datos_emprendedores");
                alert_modificar_datos_emprendedores.innerHTML = "";

                var txt_emprendimiento = document.getElementById('txt_emprendimiento');
                var txt_descripcion = document.getElementById('txt_descripcion');
                var select_calificacion = document.getElementById('num_calificacion');
                var input_foto_perfil = document.getElementById('input_foto_perfil');
                var tipo_modificacion_emprendedor = document.getElementById('tipo_modificacion_emprendedor');


                var js_usuario_nombre_emprendimiento = js_datos_usuario['nombre_emprendimiento'];
                var js_usuario_descripcion = js_datos_usuario['descripcion'];
                var js_calificacion_usuario = js_datos_usuario['calificacion_emprendedor'];

                //Se compara los datos originales del emprendedor con los datos actuales para saber si hubo un cambio o no
                if ((js_calificacion_usuario == select_calificacion.value || (js_calificacion_usuario == null && select_calificacion.value == 'null')) && (js_usuario_nombre_emprendimiento == txt_emprendimiento.value) && ((js_usuario_descripcion === null && txt_descripcion.value === '') || (js_usuario_descripcion === txt_descripcion.value)) && input_foto_perfil.value == '') {
                    alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_dismissible("info", "No hubo cambios en los datos del empredimiento");
                    return false;
                }

                //Valida que los campos no esten vacios
                if (validarCampoVacio([txt_emprendimiento])) {
                    alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo("danger", "El campo nombre del emprendimiento no puede estar vacio");
                    return false;
                }




                //Verifica que el campo select de la calificacion sea valido
                if (!validarCampoSelectCalificacion(select_calificacion)) {
                    alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo("danger", "Los valores en el select fueron modificados");
                    return false;
                }



                //Verifica que el tipo de modificacion que se va hacer es de emprendedor
                if (tipo_modificacion_emprendedor.value != "emprendedor") {
                    alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo('danger', "No se puede modificar el valor del tipo de modificacion");
                    return false;
                }

                if (input_foto_perfil.value !== '') {

                    //Verifica que la extension del archivo sea valido
                    if (validarExtensionImagen(input_foto_perfil.files[0])) {

                        //Verifica el tamaño de la imagenes
                        if (!validarTamanioImagen(input_foto_perfil.files[0])) {
                            alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo('danger', "La imagen:" + input_foto_perfil.files[0].name + " excede el tamaño maximo permitido de 10MB");
                            return false;
                        }
                    } else {
                        alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo('danger', "El formato del archivo " + input_foto_perfil.files[0].name + " no es valido. Formatos permitidos: JPEG, JPG y PNG");
                        return false;
                    }
                }


                //Se verifica que el nombre del emprendimiento no sea diferente 
                if (txt_emprendimiento.value !== js_usuario_nombre_emprendimiento) {

                    //Valida que el  campo nombre del emprendimiento  no tengan espacios al inicio o al final de la cadena
                    var lista_trim_input = listaInputEspacioBlancoIF([txt_emprendimiento]);
                    if (lista_trim_input.length > 0) {
                        alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo("danger", "El nombre del emprendimiento no puede tener espacios en blanco al inicio o al final");
                        return false;
                    }


                    //Verifica que el campo nombre del emprendimiento tenga una longitud valida
                    if (!validarCantLengthInput(txt_emprendimiento)) {
                        alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo("danger", "El campo nombre del emprendimiento debe tener entre 1 y 50 caracteres");
                        return false;
                    }
                }




                //Se verifica que la descripcion el emprendimiento no este vacio
                if (txt_descripcion.value !== '') {

                    //Valida que los campos no tengan espacios al inicio o al final de la cadena
                    var lista_trim_input = listaInputEspacioBlancoIF([txt_descripcion]);
                    if (lista_trim_input.length > 0) {
                        alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo("danger", "El campo descripcion no debe tener espacios en blanco al inicio o al final");
                        return false;
                    }


                    //Verifica que el campo descripcion tenga una longitud valida
                    if (!validarCantLengthInput(txt_descripcion)) {
                        alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo("danger", "El campo descripcion debe tener entre 1 y 150 caracteres");
                        return false;
                    }
                }

                // Envío del formulario usando fetch
                const formData = new FormData();
                formData.append('nombre_emprendimiento', txt_emprendimiento.value.trim());
                formData.append('descripcion', txt_descripcion.value.trim());
                formData.append('select_calificacion', select_calificacion.value.trim());
                if (input_foto_perfil.value !== '') {
                    formData.append('file', input_foto_perfil.files[0]);
                }
                formData.append('tipo_modificacion', tipo_modificacion_emprendedor.value.trim());

                fetch(`modificar_datos_usuario.php${window.location.search}`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(datos => {
                        if (datos.estado === 'success') {

                            //Elimina cualquier elemento input 
                            input_foto_perfil.value = "";


                            //El sistema actualiza la informacion del emprendimiento
                            js_datos_usuario['nombre_emprendimiento'] = txt_emprendimiento.value.trim();
                            js_datos_usuario['descripcion'] = txt_descripcion.value.trim();
                            js_datos_usuario['calificacion_emprendedor'] = select_calificacion.value.trim();


                            //Muestra un mensaje en la interfaz del usuario
                            alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);
                        } else {

                            //Muestra un mensaje en la interfaz del usuario
                            alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                        }

                    })
                    .catch(e => {
                        // Muestra un mensaje error de la solicitud
                        alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo("danger", e);
                    });

            });



            /*Seccion del usuario que muestra los productos del emprendedor   */

            //Inicializa la pagina actual y la cantidad actual de productos
            var pagina_actual_producto = 1;
            var cant_actual_registros_productos;

            // Carga los datos de los productos publicados
            getDataProductos();


            //Agrega un evento cada vez que la fecha cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
            document.getElementById("fecha_inicio_producto").addEventListener("change", function() {
                pagina_actual_producto = 1; //Reinicia la pagina a 1
                getDataProductos(); //Llama a la funcion para obtener los datos de productos
            });


            //Agrega un evento cada vez que el campo buscar producto cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
            document.getElementById("campo_buscar_producto").addEventListener("input", function() {
                pagina_actual_producto = 1; //Reinicia la pagina a 1
                getDataProductos(); //Llama a la funcion para obtener los datos de productos
            });


            //Agrega un evento cada vez que se cambie el estado la pagina actual vuelve a 1 y se llama a la funcion de productos 
            document.getElementById("num_estado_producto").addEventListener("change", function() {
                pagina_actual_producto = 1; //Reinicia la pagina a 1
                getDataProductos(); //Llama a la funcion para obtener los datos de productos
            });

            //Agrega un evento cada vez que se cambie la categoria la pagina actual vuelve a 1 y se llama a la funcion de productos 
            document.getElementById("num_categoria_producto").addEventListener("change", function() {
                pagina_actual_producto = 1; //Reinicia la pagina a 1
                getDataProductos(); //Llama a la funcion para obtener los datos de productos
            });


            //Agrega un evento cada vez que la cantidad de registro cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
            document.getElementById("cant_registro_producto").addEventListener("change", function() {
                pagina_actual_producto = 1; //Reinicia la pagina a 1
                getDataProductos(); //Llama a la funcion para obtener los datos de productos
            });





            //Función para obtener y cargar datos de productos
            function getDataProductos() {


                //Elimina cualquier alerta previa 
                var alert_notificacion_producto = document.getElementById("alert_notificacion_producto");
                alert_notificacion_producto.innerHTML = "";

                var campo_buscar = document.getElementById("campo_buscar_producto");
                var categoria = document.getElementById("num_categoria_producto");
                var estado = document.getElementById("num_estado_producto");
                var fecha_inicio = document.getElementById("fecha_inicio_producto");
                var cant_registro = document.getElementById("cant_registro_producto");
                var card_productos = document.getElementById("card_productos");
                var pagina = pagina_actual_producto;


                // Envío del formulario usando fetch
                const formData = new FormData();
                formData.append('estado', estado.value);
                formData.append('categoria', categoria.value);
                formData.append('campo_buscar', campo_buscar.value);
                formData.append('cant_registro', cant_registro.value);
                formData.append('fecha', fecha_inicio.value);
                formData.append('numero_pagina', pagina);
                fetch(`lista_producto.php${window.location.search}`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json()) // Convierte la respuesta a JSON
                    .then(datos => {
                        if (datos.estado !== 'danger') {
                            card_productos.innerHTML = datos.card_productos;
                            cant_actual_registros_productos = datos.cantidad_actual;

                            document.getElementById("lbl-totalResultadoProducto").innerHTML = datos.registro;
                            document.getElementById("lbl-totalPaginaProducto").innerHTML = datos.pagina;
                            document.getElementById("nav-paginacionProducto").innerHTML = datos.paginacion;
                        } else {
                            // Muestra un mensaje error de la solicitud
                            alert_notificacion_producto.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                        }

                    }).catch(e => {
                        // Muestra un mensaje error de la solicitud
                        alert_notificacion_producto.innerHTML = mensaje_alert_fijo("danger", e);
                    });
            }


            //Funcion para cambiar la pagina y recargar los datos
            function nextPageProducto(pagina) {
                pagina_actual_producto = pagina;
                getDataProductos();
            }



            /*Seccion del usuario que muestra las publicaciones del emprendedor   */

            //Inicializa la pagina actual y la cantidad actual de publicaciones
            var pagina_actual_publicacion = 1;
            var cant_actual_registros_publicacion;

            // Carga los datos de las publicaciones
            getDataPublicacion();



            //Agrega un evento cada vez que la fecha cambie la pagina actual vuelve a 1 y se llama a la funcion de publicaciones
            document.getElementById("fecha_inicio_publicacion").addEventListener("change", function() {

                pagina_actual_publicacion = 1; //Reinicia la pagina a 1
                getDataPublicacion(); //Llama a la funcion para obtener datos de publicaciones

            });

            //Agrega un evento cada vez que la cantidad de registro cambie la pagina actual vuelve a 1 y se llama a la funcion de publicaciones
            document.getElementById("cant_registro_publicacion").addEventListener("change", function() {

                pagina_actual_publicacion = 1; //Reinicia la pagina a 1
                getDataPublicacion(); //Llama a la funcion para obtener datos de publicaciones

            });



            //Función para obtener y cargar datos de publicaciones
            function getDataPublicacion() {

                //Elimina cualquier alerta previa 
                var alert_notificacion_informacion = document.getElementById("alert_notificacion_informacion");
                alert_notificacion_informacion.innerHTML = "";

                var fecha_inicio = document.getElementById("fecha_inicio_publicacion");
                var cant_registro = document.getElementById("cant_registro_publicacion");
                var cards_publicaciones = document.getElementById("cards_publicaciones");
                var pagina = pagina_actual_publicacion;

                // Envío del formulario usando fetch
                const formData = new FormData(); // Convierte la respuesta a JSON

                formData.append('cant_registro', cant_registro.value);
                formData.append('fecha', fecha_inicio.value);
                formData.append('numero_pagina', pagina);
                fetch(`lista_publicacion.php${window.location.search}`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(datos => {
                        if (datos.estado !== 'danger') {
                            cards_publicaciones.innerHTML = datos.card_publicaciones;
                            cant_actual_registros_publicacion = datos.cantidad_actual;
                            document.getElementById("lbl-totalResultadoPublicacion").innerHTML = datos.registro;
                            document.getElementById("lbl-totalPaginaPublicacion").innerHTML = datos.pagina;
                            document.getElementById("nav-paginacionPublicacion").innerHTML = datos.paginacion;
                        } else {

                            // Muestra un mensaje de alerta si hubo un error
                            alert_notificacion_informacion.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                        }
                    }).catch(e => {
                        // Muestra un mensaje error de la solicitud
                        alert_notificacion_informacion.innerHTML = mensaje_alert_fijo("danger", e);
                    });
            }


            //Funcion para cambiar la pagina y recargar los datos
            function nextPagePublicaciones(pagina) {
                pagina_actual_publicacion = pagina;
                getDataPublicacion();
            }





            /*Seccion del emprendedor que muestra a los usuarios que lo siguen   */




            //Inicializa la pagina actual y la cantidad actual de seguidos
            var pagina_actual_seguidor = 1;
            var cant_actual_registros_seguidor;

            // Carga los datos de los usuarios que siguen al emprendedor
            getDataTodasMisSeguidores();


            //Agrega un evento cada vez que el campo buscar producto cambie la pagina actual vuelve a 1 y se llama a la funcion de los usuarios que siguen al emprendedor
            document.getElementById("txt_buscar_seguidor").addEventListener("input", function() {
                pagina_actual_seguidor = 1; //Reinicia la pagina a 1
                getDataTodasMisSeguidores(); //Llama a la funcion para obtener los datos de los usuarios que siguen al emprendedor
            });

            //Agrega un evento cada vez que la fecha cambie la pagina actual vuelve a 1 y se llama a la funcion de los usuarios que siguen al emprendedor
            document.getElementById("fecha_inicio_seguidor").addEventListener("change", function() {
                pagina_actual_seguidor = 1; //Reinicia la pagina a 1
                getDataTodasMisSeguidores(); //Llama a la funcion para obtener los datos de los usuarios que siguen al emprendedor
            });


            //Agrega un evento cada vez que la cantidad de registro cambie la pagina actual vuelve a 1 y se llama a la funcion de los usuarios que siguen al emprendedor
            document.getElementById("cant_registro_seguidor").addEventListener("change", function() {
                pagina_actual_seguidor = 1; //Reinicia la pagina a 1
                getDataTodasMisSeguidores(); //Llama a la funcion para obtener los datos de los usuarios que siguen al emprendedor
            });



            //Función para obtener y cargar datos de los seguidores del usuario emprendedor
            function getDataTodasMisSeguidores() {

                //Elimina cualquier alerta previa 
                var alert_notificacion_seguidor = document.getElementById("alert_notificacion_seguidor");
                alert_notificacion_seguidor.innerHTML = "";


                var txt_buscar_seguidor = document.getElementById("txt_buscar_seguidor");
                var fecha_inicio_seguidor = document.getElementById("fecha_inicio_seguidor");
                var cant_registro_seguidor = document.getElementById("cant_registro_seguidor");
                var tabla_seguidor = document.getElementById("tabla_seguidor");
                var pagina = pagina_actual_seguidor;

                // Envío del formulario usando fetch
                const formData = new FormData();
                formData.append('fecha', fecha_inicio_seguidor.value);
                formData.append('cant_registro', cant_registro_seguidor.value);
                formData.append('numero_pagina', pagina);
                formData.append('campo_buscar_seguidor', txt_buscar_seguidor.value);
                fetch(`lista_seguidores.php${window.location.search}`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json()) // Convierte la respuesta a JSON
                    .then(datos => {
                        if (datos.estado !== 'danger') {
                            cant_actual_registros_seguidor = datos.cantidad_actual;
                            tabla_seguidor.innerHTML = datos.tabla;
                            document.getElementById("lbl-totalResultadoSeguidor").innerHTML = datos.registro;
                            document.getElementById("lbl-totalPaginaSeguidor").innerHTML = datos.pagina;
                            document.getElementById("nav-paginacionSeguidor").innerHTML = datos.paginacion;

                        } else {
                            // Muestra un mensaje de alerta si hubo un error
                            alert_notificacion_seguidor.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        // Muestra un mensaje error de la solicitud
                        alert_notificacion_seguidor.innerHTML = mensaje_alert_fijo("danger", error);
                    });
            }

            //Funcion para cambiar la pagina y recargar los datos
            function nextPageSeguidores(pagina) {
                pagina_actual_seguidor = pagina;
                getDataTodasMisSeguidores();
            }





            /*Seccion del usuario que muestra las preguntas recibidas de los productos   */


            //Inicializa la pagina actual y la cantidad actual de seguidos
            var pagina_actual_preguntas_recibidas = 1;

            // Carga los datos de las preguntas recibidas del emprendedor
            getDataPreguntasRecibidas();



            //Agrega un evento cada vez que la fecha cambie la pagina actual vuelve a 1 y se llama a la funcion de productos con las preguntas recibidas
            document.getElementById("fecha_inicio_preguntas_recibidas").addEventListener("change", function() {

                pagina_actual_preguntas_recibidas = 1; //Reinicia la pagina a 1
                getDataPreguntasRecibidas(); //Llama a la funcion para obtener datos de productos con las preguntas recibidas

            });

            //Agrega un evento cada vez que el campo buscar usuario cambie la pagina actual vuelve a 1 y se llama a la funcion de productos con las preguntas recibidas
            document.getElementById("campo_buscar_preguntas_recibidas_usu").addEventListener("input", function() {

                pagina_actual_preguntas_recibidas = 1; //Reinicia la pagina a 1
                getDataPreguntasRecibidas(); //Llama a la funcion para obtener datos de productos con las preguntas recibidas

            });



            //Agrega un evento cada vez que el campo buscar producto cambie la pagina actual vuelve a 1 y se llama a la funcion de productos con las preguntas recibidas
            document.getElementById("campo_buscar_preguntas_recibidas_pro").addEventListener("input", function() {
                pagina_actual_preguntas_recibidas = 1; //Reinicia la pagina a 1
                getDataPreguntasRecibidas(); //Llama a la funcion para obtener datos de productos con las preguntas recibidas

            });

            //Agrega un evento cada vez que se cambie el estado la pagina actual vuelve a 1 y se llama a la funcion de productos con las preguntas recibidas
            document.getElementById("num_estado_preguntas_recibidas").addEventListener("change", function() {

                pagina_actual_preguntas_recibidas = 1; //Reinicia la pagina a 1
                getDataPreguntasRecibidas(); //Llama a la funcion para obtener datos de productos con las preguntas recibidas

            });

            //Agrega un evento cada vez que la cantidad de registro cambie la pagina actual vuelve a 1 y se llama a la funcion de productos con las preguntas recibidas
            document.getElementById("cant_registro_preguntas_recibidas").addEventListener("change", function() {

                pagina_actual_preguntas_recibidas = 1; //Reinicia la pagina a 1
                getDataPreguntasRecibidas(); //Llama a la funcion para obtener datos de productos con las preguntas recibidas

            });

            //Agrega un evento cada vez ue se cambie el filtro de pregunta cambie la pagina actual vuelve a 1 y se llama a la funcion de productos con las preguntas recibidas
            document.getElementById("filtro_preguntas_recibidas").addEventListener("change", function() {

                pagina_actual_preguntas_recibidas = 1; //Reinicia la pagina a 1
                getDataPreguntasRecibidas(); //Llama a la funcion para obtener datos de productos con las preguntas recibidas

            });





            const ventanaModalEliminarPreguntasRecibidas = new bootstrap.Modal(eliminarModalPreguntasRecibidas);


            //Manejo del envio del formulario para eliminar una respuesta
            form_eliminar_respuesta.addEventListener("submit", (e) => {

                //Previene el envio por defecto del formulario
                e.preventDefault();

                //Elimina cualquier alerta previa 
                alert_eliminar_modal_respuesta = document.getElementById("alert_eliminar_modal_respuesta");
                alert_eliminar_modal_respuesta.innerHTML = "";


                var id_pregunta_recibida = document.getElementById('id_pregunta_recibida');
                var id_pregunta_producto = document.getElementById('id_pregunta_producto');


                //Valida que los campos ocultos solo contengan numeros
                if (!isNaN(id_pregunta_recibida) || !isNaN(id_pregunta_producto)) {
                    alert_eliminar_modal_respuesta.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
                    return false;
                }

                // Envío del formulario usando fetch
                const formData = new FormData(form_eliminar_respuesta);
                fetch(`preguntas_respuesta/baja_respuesta.php${window.location.search}`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json()) // Convierte la respuesta a JSON
                    .then(datos => {
                        if (datos.estado == "success") {

                            // Actualiza los datos de las preguntas y respuesta
                            getDataPreguntasRecibidas();

                            //Muetra un mensaje en la interfaz del usuario
                            alert_notificacion_respuesta.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                            //Cierra el modal 
                            ventanaModalEliminarPreguntasRecibidas.hide();


                        } else {
                            if (datos.estado == "danger") {

                                //Muestra un mensaje de error en el Modal
                                alert_eliminar_modal_respuesta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                            }
                        }

                    }).catch(e => {
                        // Muestra un mensaje error de la solicitud
                        alert_eliminar_modal.innerHTML = mensaje_alert_fijo("danger", e);
                    });

            });



            //Función para obtener y cargar datos de productos con las preguntas recibidas 
            function getDataPreguntasRecibidas() {

                //Elimina cualquier alerta previa 
                alert_notificacion_respuesta = document.getElementById("alert_notificacion_respuesta");
                alert_notificacion_respuesta.innerHTML = "";


                var campo_buscar_producto = document.getElementById("campo_buscar_preguntas_recibidas_pro");
                var campo_buscar_usuario = document.getElementById("campo_buscar_preguntas_recibidas_usu");
                var fecha_inicio = document.getElementById("fecha_inicio_preguntas_recibidas");
                var estado = document.getElementById("num_estado_preguntas_recibidas");
                var filtro_preguntas = document.getElementById("filtro_preguntas_recibidas");
                var cant_registro = document.getElementById("cant_registro_preguntas_recibidas");
                var card_preguntas_recibidas = document.getElementById("card_preguntas_recibidas");
                var pagina = pagina_actual_preguntas_recibidas;


                // Envío del formulario usando fetch
                const formData = new FormData();


                formData.append('estado', estado.value);
                formData.append('filtro_preguntas', filtro_preguntas.value);
                formData.append('campo_buscar_producto', campo_buscar_producto.value);
                formData.append('fecha', fecha_inicio.value);
                formData.append('campo_buscar_usuario', campo_buscar_usuario.value);
                formData.append('cant_registro', cant_registro.value);
                formData.append('numero_pagina', pagina);
                fetch(`lista_respuesta.php${window.location.search}`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json()) // Convierte la respuesta a JSON
                    .then(datos => {
                        if (datos.estado !== 'danger') {
                            card_preguntas_recibidas.innerHTML = datos.cards_respuesta;
                            document.getElementById("lbl-totalResultadoPreguntasRecibidas").innerHTML = datos.registro;
                            document.getElementById("lbl-totalPaginaPreguntasRecibidas").innerHTML = datos.pagina;
                            document.getElementById("nav-paginacionPreguntasRecibidas").innerHTML = datos.paginacion;
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
            function nextPagePreguntasRecibidas(pagina) {
                pagina_actual_preguntas_recibidas = pagina;
                getDataPreguntasRecibidas();
            }


        }
    }
</script>

