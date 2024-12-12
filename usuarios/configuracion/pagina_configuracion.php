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
$datos_usuario = array();
$tipo_usuario = 0;


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

            //Se obtiene los datos del usuario
            $datos_usuario = obtenerDatosUsuarioEmprendedorYUsuarioComun($conexion, $id_usuario);

            //Se verifica que sea un usuario emprendedor
            if ($datos_usuario['id_tipo_usuario'] == 2) {
                //Las variables se utilizan para que el usuario emprendedor pueda ir a su perfil desde el navbar
                $id_usuario_emprendedor = $datos_usuario['id_usuario_emprendedor'];
                $id_usuario_emprendedor_token = hash_hmac('sha1', $id_usuario_emprendedor, KEY_TOKEN);
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
    <title>Proyecto Emprendedor Configuracion</title>

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
            <?php if (empty($mensaje_error)) { ?>
                <!--Titulo de la seccion-->
                <h1 class="text-center">Configuraciones</h1>

                <!--Define una navegación por pestañas para alternar entre las vistas de datos personales,datos del emprendimiento,cambiar email y cambiar contraseña.-->
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">

                        <button class="nav-link active" id="datos_personales_tab" data-bs-toggle="tab" data-bs-target="#datos_personales" type="button" role="tab" aria-controls="datos_personales" aria-selected="true">Datos Personales</button>

                        <!--Se verifica que el tipo de usuario sea un emprendedor-->
                        <?php if ($datos_usuario['id_tipo_usuario'] == 2) { ?>
                            <button class="nav-link" id="datos_emprendimiento_tab" data-bs-toggle="tab" data-bs-target="#datos_emprendimiento" type="button" role="tab" aria-controls="datos_emprendimiento" aria-selected="false">Datos del Emprendimiento</button>
                        <?php  } ?>

                        <button class="nav-link" id="cambiar_email_tab" data-bs-toggle="tab" data-bs-target="#cambiar_email" type="button" role="tab" aria-controls="cambiar_email" aria-selected="false">Cambiar Email</button>

                        <button class="nav-link" id="cambiar_contrasenia_tab" data-bs-toggle="tab" data-bs-target="#cambiar_contrasenia" type="button" role="tab" aria-controls="cambiar_contrasenia" aria-selected="false">Cambiar Contraseña</button>
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
                                            <h5 class="text-center">Datos del usuario</h5>

                                            <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                            <div id="alert_modificar_datos_personales"></div>


                                            <!-- Contenedor para mostrar los datos del usuario -->
                                            <div class="row g-2">

                                                <!-- Div que contiene el campo nombre de usuario(No puede ser modificado) -->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                                    <!-- Campo para el nombre de usuario -->
                                                    <input type="text" class="form-control" name="txt_usuario" id="txt_usuario" placeholder="Nombre de usuario" value="<?php echo ($datos_usuario['nombre_usuario']); ?>" disabled>
                                                    <label for="txt_usuario">Nombre de usuario</label>
                                                </div>

                                                <!-- Div que contiene el campo nombres y muestra la cantidad maxima de caracteres-->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-6 form-floating mb-3">
                                                    <!-- Campo para nombres -->
                                                    <input type="text" class="form-control" name="txt_nombres" id="txt_nombres" placeholder="Nombres" minlength="1" maxlength="100" data-max="100" value="<?php echo ($datos_usuario['nombres']); ?>" required>
                                                    <label for="txt_nombres">Nombres</label>
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                        <span class="form-text">Maximo 100 caracteres.<span id="txaCountNombres">100 restantes</span></span>
                                                    </div>
                                                </div>

                                                <!-- Div que contiene el campo apellidos y muestra la cantidad maxima de caracteres-->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-6 form-floating mb-3">
                                                    <!-- Campo para apellidos -->
                                                    <input type="text" class="form-control" name="txt_apellidos" id="txt_apellidos" placeholder="Apellidos" minlength="1" maxlength="100" data-max="100" value="<?php echo ($datos_usuario['apellidos']); ?>" required>
                                                    <label for="txt_apellidos">Apellidos</label>
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                        <span class="form-text">Maximo 100 caracteres.<span id="txaCountApellidos">100 restantes</span></span>
                                                    </div>
                                                </div>

                                                <!-- Div que contiene el campo fecha de registro(No puede ser modificado) -->
                                                <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                    <div class="input-group">
                                                        <span class="input-group-text">Fecha de registro:</span>
                                                        <input type="datetime-local" name="fecha_registro" id="fecha_registro" placeholder="Fecha registro" class="form-control" value="<?php echo ($datos_usuario['fecha']); ?>" disabled>
                                                    </div>
                                                </div>

                                                <!-- Div que contiene el campo email -->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                                    <!-- Campo para el email -->
                                                    <input type="email" class="form-control" name="txt_email" id="txt_email" placeholder="Email" minlength="2" maxlength="320" value="<?php echo ($datos_usuario['email']); ?>" disabled>
                                                    <label for="txt_email">Email</label>
                                                </div>

                                                <input type="hidden" name="tipo_modificacion_usuario" id="tipo_modificacion_usuario" value="usuario" required>

                                            </div>
                                        </div>

                                        <!-- Footer del Card  -->
                                        <div class="card-footer">

                                            <!-- Botón para abrir el modal desactivar la cuenta del usuario -->
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDesactivarCuenta">Desactivar Cuenta</button>

                                            <!-- Botón para enviar los cambios hecho -->
                                            <button type="submit" class="btn btn-outline-success">Guardar cambios</button>
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

                                                <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                                <div id="alert_modificar_datos_emprendedores"></div>

                                                <div class="row">
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                                                        <div class="row g-2">

                                                            <!-- Div que contiene el campo del nombre del emprendimiento(No puede ser modificado) -->
                                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                                                <input type="text" class="form-control" name="txt_emprendimiento" id="txt_emprendimiento" placeholder="Nombre del emprendimiento" value="<?php echo ($datos_usuario['nombre_emprendimiento']); ?>" disabled>
                                                                <label for="txt_emprendimiento">Nombre del emprendimiento</label>
                                                            </div>

                                                            <!-- Div que contiene la calificacion del emprendedor(No puede ser modificado) -->
                                                            <div class="input-group mb-3">
                                                                <span class="input-group-text" for="num_calificacion">Calificacion del emprendedor:</span>
                                                                <select class="form-select" name="num_calificacion" id="num_calificacion" disabled>
                                                                    <?php if (is_null($datos_usuario['calificacion_emprendedor'])) { ?>
                                                                        <option value="null" selected>Sin calificacion</option>

                                                                    <?php  } else { ?>
                                                                        <?php for ($i = 0; $i <= 5; $i++) {  ?>
                                                                            <?php if ($datos_usuario['calificacion_emprendedor'] == $i) { ?>
                                                                                <option value="<?php echo ($i) ?>" selected><?php echo ($i) ?></option>
                                                                    <?php  }
                                                                        }
                                                                    }   ?>
                                                                </select>
                                                            </div>




                                                            <!-- Div que contiene el campo descripcion del emprendimiento y muestra la cantidad maxima de caracteres-->
                                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                                                <!-- Campo para descripcion del emprendimiento -->
                                                                <textarea class="form-control" name="txt_descripcion" placeholder="Descripcion del Emprendimiento" id="txt_descripcion" minlength="1" maxlength="150" data-max="150" rows="10" cols="50" required style="height: 130px;"><?php echo ($datos_usuario['descripcion']); ?></textarea>
                                                                <label for="txt_descripcion">Descripcion del emprendimiento</label>
                                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                                    <span class="form-text">Maximo 150 caracteres.<span id="txaCountDescrip">150 restantes</span></span>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>


                                                    <!-- Div que contiene la foto del perfil del emprendimiento-->
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                                                        <h6 class="text-center">Foto de perfil</h6>
                                                        <div class="row g-2 d-flex justify-content-center">
                                                            <div class="col-7 col-sm-6 col-md-5 col-lg-7">
                                                                <?php if (is_null($datos_usuario["foto_perfil_nombre"])) {
                                                                    $ruta_archivo = $url_foto_perfil_predeterminada;
                                                                } else {
                                                                    $ruta_archivo = $url_base_archivos . "/uploads/{$datos_usuario["id_usuario_emprendedor"]}/foto_perfil/{$datos_usuario["foto_perfil_nombre"]}";
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
                                            </div>

                                        </div>
                                    </form>

                                </div>
                            </div>

                        </div>
                    <?php  } ?>

                    <!--Contenedor del cambio de email.-->
                    <div class="tab-pane fade" id="cambiar_email" role="tabpanel" aria-labelledby="cambiar_email_tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12 col-sm-12 col-md-8 col-lg-6">



                                <!--Formulario para enviar los datos-->
                                <form id="formulario_modificar_datos_email" method="POST">
                                    <div class="card">

                                        <!-- Body del Card -->
                                        <div class="card-body">
                                            <h5 class="text-center">Cambiar email</h5>

                                            <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                            <div id="alert_modificar_datos_email"></div>
                                            <div class="row g-2">

                                                <p>Para hacer un cambio de E-mail se le enviara un correo electrónico al nuevo E-mail ingresado para verificar la validez y tu acceso al mismo.</p>
                                                <!-- Div que contiene el nuevo email para cambiar-->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                                    <input type="email" class="form-control" name="txt_nuevo_email" id="txt_nuevo_email" placeholder="Email" minlength="2" maxlength="320" required>
                                                    <label for="txt_nuevo_email">Nuevo Email</label>
                                                </div>
                                                <!-- Div que contiene el campo de la contraseña para confirmar el cambio de email-->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                                    <input type="password" class="form-control" name="txt_confirmar_password_email" id="txt_confirmar_password_email" placeholder="Confirmar contraseña" minlength="6" maxlength="60" required>
                                                    <label for="txt_confirmar_password_email">Contraseña para confirmar el cambio de email</label>
                                                    <div class=" form-check mb-3">
                                                        <input type="checkbox" class="form-check-input" id="check_password_email">
                                                        <label for="check_password_email" class="form-check-label">
                                                            Mostrar contraseña
                                                        </label>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="tipo_modificacion_email" id="tipo_modificacion_email" value="email" required>
                                            </div>
                                        </div>
                                        <!-- Footer del Card  -->
                                        <div class="card-footer">
                                            <!-- Botón para enviar los cambios hechos -->
                                            <button class="btn btn-outline-success" id="boton_cambiar_email" type="submit">Confirmar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!--Contenedor del cambio de contraseña.-->
                    <div class="tab-pane fade" id="cambiar_contrasenia" role="tabpanel" aria-labelledby="cambiar_contrasenia_tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12 col-sm-12 col-md-8 col-lg-7">

                                <!--Formulario para enviar los datos-->
                                <form id="formulario_modificar_datos_password" method="POST">

                                    <!--Card -->
                                    <div class="card">

                                        <!-- Body del Card -->
                                        <div class="card-body">
                                            <h5 class="text-center">Cambiar Contraseña</h5>

                                            <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                            <div id="alert_modificar_datos_password"></div>
                                            <div class="row g-2">

                                                <!-- Div que contiene el campo contraseña actual y checkbox para mostrar y ocultar la contraseña  -->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                                    <input type="password" class="form-control" name="txt_password_actual" id="txt_password_actual" placeholder="Contraseña actual" minlength="6" maxlength="60" required>
                                                    <label for="txt_password_actual">Contraseña actual</label>
                                                    <div class="form-check mb-3">
                                                        <input type="checkbox" class="form-check-input" id="check_password_actual">
                                                        <label for="check_password_actual" class="form-check-label">
                                                            Mostrar contraseña
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Div que contiene el campo nueva contraseña -->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-7 form-floating mb-3">
                                                    <input type="password" class="form-control" name="txt_nueva_password" id="txt_nueva_password" placeholder="Nueva contraseña" minlength="6" maxlength="60" required>
                                                    <label for="txt_nueva_password">Nueva contraseña</label>
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                        <span class="form-text">La nueva contraseña debe tener como minimo 6 caracteres</span>
                                                    </div>
                                                </div>


                                                <!-- Div que contiene el campo confirmar nueva contraseña -->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-5 form-floating mb-3">
                                                    <input type="password" class="form-control" name="txt_confirmar_nueva_password" id="txt_confirmar_nueva_password" placeholder="Confirmar nueva contraseña" minlength="6" maxlength="60" required>
                                                    <label for="txt_confirmar_nueva_password">Confirmar nueva contraseña</label>

                                                </div>

                                                <!-- Div que contiene un checkbox para mostrar y ocultar la nueva contraseña y la confirmacion de la nueva contraseña-->
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" class="form-check-input" id="check_nuevas_password">
                                                    <label for="check_nuevas_password" class="form-check-label">
                                                        Mostrar contraseña
                                                    </label>
                                                </div>
                                                <input type="hidden" name="tipo_modificacion_password" id="tipo_modificacion_password" value="password" required>

                                            </div>

                                        </div>
                                        <!-- Footer del Card  -->
                                        <div class="card-footer">

                                            <!-- Botón para enviar los cambios hechos -->
                                            <button class="btn btn-outline-success" type="submit">Confirmar</button>
                                        </div>
                                    </div>
                                </form>
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

    <!-- Incluye el pie de pagina y el script necesario para el funcionamiento de la pagina.-->
    <script src="../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../config/js/funciones.js"></script>
    <?php require("modal_desactivar_cuenta.php"); ?>
    <?php require("../../template/footer.php"); ?>


</body>

</html>

<script>
    // Se obtiene los formularios que se van a utilizar
    var form_modificar_datos_personales = document.getElementById("formulario_modificar_datos_personales");
    var form_modificar_datos_emprendedores = document.getElementById("formulario_modificar_datos_emprendedores");
    var form_modificar_datos_email = document.getElementById("formulario_modificar_datos_email");
    var form_modificar_datos_password = document.getElementById("formulario_modificar_datos_password");


    /*Seccion parar modificar los datos personales del usuario */


    //Funcion para mostrar la cantidad restantes de caracteres en el campo nombres
    contarMostrarCarecteresRestantes('txt_nombres', 'txaCountNombres');

    //Funcion para mostrar la cantidad restantes de caracteres en el campo apellidos
    contarMostrarCarecteresRestantes('txt_apellidos', 'txaCountApellidos');

    //Agrega un evento para contar y mostrar caracteres restantes en el campo nombres
    document.getElementById('txt_nombres').addEventListener('input', function() {
        contarMostrarCarecteresRestantes('txt_nombres', 'txaCountNombres');
    });

    //Agrega un evento para contar y mostrar caracteres restantes en el campo apellidos
    document.getElementById('txt_apellidos').addEventListener('input', function() {
        contarMostrarCarecteresRestantes('txt_apellidos', 'txaCountApellidos');
    });


    var datos_usuario = [<?php echo json_encode($datos_usuario); ?>];
    var js_datos_usuario = datos_usuario[0];


    //Manejo del envio del formulario para modificar los datos personales del usuario
    form_modificar_datos_personales.addEventListener('submit', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();


        //Elimina cualquier alerta previa 
        var alert_modificar_datos_personales = document.getElementById("alert_modificar_datos_personales");
        alert_modificar_datos_personales.innerHTML = "";

        var txt_nombres = document.getElementById('txt_nombres');
        var txt_apellidos = document.getElementById('txt_apellidos');
        var tipo_modificacion_usuario = document.getElementById('tipo_modificacion_usuario');
        var campos_verificar = [txt_nombres, txt_apellidos];
        var js_usuario_nombres = js_datos_usuario['nombres'];
        var js_usuario_apellidos = js_datos_usuario['apellidos'];

        //Se compara los datos originales del usuario con los datos actuales para saber si hubo cambios o no
        if (txt_nombres.value == js_usuario_nombres && txt_apellidos.value == js_usuario_apellidos) {
            alert_modificar_datos_personales.innerHTML = mensaje_alert_dismissible("info", "No hubo cambios en los datos personales del usuario");
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

        //Valida que los campos no tengan espacios al inicio o al final de la cadena
        var lista_trim_input = listaInputEspacioBlancoIF(campos_verificar);
        if (lista_trim_input.length > 0) {
            alert_modificar_datos_personales.innerHTML = mensaje_alert_lista_fijo("danger", "No se permite que los campos tengan espacios en blanco al inicio o al final. Los siguientes campos no cumplen con esto:", lista_trim_input);
            return false;
        }



        //Verifica que los campos nombres y apellidos tengan una longitud valida
        var lista_length_input = listaInputLengthNoValidos(campos_verificar);
        if (lista_length_input.length > 0) {
            alert_modificar_datos_personales.innerHTML = mensaje_alert_lista_fijo("danger", "Los siguientes campos no cumplen con la longitud mínima de 1 carácter o el máximo de caracteres indicado:", lista_length_input);
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData();

        formData.append('nombres', txt_nombres.value.trim());
        formData.append('apellidos', txt_apellidos.value.trim());
        formData.append('tipo_modificacion', tipo_modificacion_usuario.value.trim());

        fetch('modificar_datos_usuario.php', {
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
                        js_datos_usuario['nombres'] = txt_nombres.value.trim();
                        js_datos_usuario['apellidos'] = txt_apellidos.value.trim();

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




    /*Seccion parar modificar los datos del emprendimiento del usuario emprendedor*/



    var js_tipo_usuario = <?php echo json_encode($datos_usuario['id_tipo_usuario']) ?>;
    if (js_tipo_usuario === 2) {

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
                        alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo('danger', "La imagen " + input_foto_perfil.files[0].name + " excede el tamaño maximo permitido de 10MB");
                        input_foto_perfil.value = "";
                    }
                } else {
                    //Muestra un mensaje en la interfaz del usuario
                    alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo('danger', "El formato del archivo " + input_foto_perfil.files[0].name + " no es valido. Formatos permitidos: JPEG, JPG y PNG");
                    input_foto_perfil.value = "";
                }
            }
        });


        //Funcion para mostrar la cantidad restantes de caracteres en el campo descripcion
        contarMostrarCarecteresRestantes('txt_descripcion', 'txaCountDescrip');

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


            var txt_descripcion = document.getElementById('txt_descripcion');
            var input_foto_perfil = document.getElementById('input_foto_perfil');

            var tipo_modificacion_emprendedor = document.getElementById('tipo_modificacion_emprendedor');

            var js_usuario_descripcion = js_datos_usuario['descripcion'];


            //Se compara los datos originales del emprendedor con los datos actuales para saber si hubo un cambio o no
            if (((js_usuario_descripcion === null && txt_descripcion.value === '') || (js_usuario_descripcion === txt_descripcion.value)) && input_foto_perfil.value == '') {
                alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_dismissible("info", "No hubo cambios en los datos del emprendimiento");
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


            //Se verifica que la descripcion el emprendimiento no este vacio
            if (txt_descripcion.value !== '') {

                //Valida que los campos no tengan espacios al inicio o al final de la cadena
                var lista_trim_input = listaInputEspacioBlancoIF([txt_descripcion]);
                if (lista_trim_input.length > 0) {
                    alert_modificar_datos_emprendedores.innerHTML = mensaje_alert_fijo("danger", "El campo descripcion no puede tener espacios en blanco al inicio o al final");
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
            formData.append('descripcion', txt_descripcion.value.trim());
            if (input_foto_perfil.value !== '') {
                formData.append('file', input_foto_perfil.files[0]);
            }
            formData.append('tipo_modificacion', tipo_modificacion_emprendedor.value.trim());

            fetch('modificar_datos_usuario.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(datos => {
                    if (datos.estado === 'success') {

                        //Elimina cualquier elemento input 
                        input_foto_perfil.value = "";


                        //El sistema actualiza la informacion del emprendimiento
                        js_datos_usuario['descripcion'] = txt_descripcion.value.trim();


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

    }


    /*Seccion para modificar el email del usuario */


    //Agrega un evento cada vez que se haga un click en el checkbox cambiando el estado de la visibilidad de la contraseña
    document.getElementById("check_password_email").addEventListener('click', function() {
        var check_password_email = document.getElementById("check_password_email");
        var txt_confirmar_password_email = document.getElementById('txt_confirmar_password_email');
        mostrar_ocultar_password(check_password_email, txt_confirmar_password_email);
    });



    //Manejo del envio del formulario para el email del usuario
    form_modificar_datos_email.addEventListener('submit', function(event) {


        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_modificar_datos_email = document.getElementById("alert_modificar_datos_email");
        alert_modificar_datos_email.innerHTML = "";

        var txt_nuevo_email = document.getElementById('txt_nuevo_email');
        var txt_confirmar_password_email = document.getElementById('txt_confirmar_password_email');

        var tipo_modificacion_email = document.getElementById('tipo_modificacion_email');
        var boton_cambiar_email = document.getElementById('boton_cambiar_email');
        var campos_cambiar_estados = [txt_nuevo_email, txt_confirmar_password_email, boton_cambiar_email];

        js_usuario_email = <?php echo json_encode($datos_usuario['email']); ?>;

        //Se compara el email original del usuario con el nuevo ingresado
        if (js_usuario_email == txt_nuevo_email.value) {
            alert_modificar_datos_email.innerHTML = mensaje_alert_dismissible("info", "El email ingresado es el mismo con el que estas registrado");
            return false;
        }


        //Valida que los campos no esten vacios
        if (validarCampoVacio([txt_nuevo_email, txt_confirmar_password_email])) {
            alert_modificar_datos_email.innerHTML = mensaje_alert_fijo("danger", "Por favor complete todos los campos");
            return false;
        }


        //Valida que el campo email sea valido
        if (!validarCampoEmail(txt_nuevo_email)) {
            alert_modificar_datos_email.innerHTML = mensaje_alert_fijo("danger", "Por favor ingrese un email con formato valido");
            return false;
        }



        //Verifica que el tipo de modificacion que se va hacer es de email
        if (tipo_modificacion_email.value != "email") {
            alert_modificar_datos_email.innerHTML = mensaje_alert_fijo('danger', "No se puede modificar el valor del tipo de modificacion");
            return false;
        }

        //Valida que el campo password no tenga espacios al inicio o al final de la cadena
        if (tieneEspacioEnBlacoPassword(txt_confirmar_password_email)) {
            alert_modificar_datos_email.innerHTML = mensaje_alert_fijo("danger", "La contraseña no puede tener espacios en blanco");
            return false;
        }

        //Valida que el campo email sea valido
        if (!validarCampoEmail(txt_nuevo_email)) {
            alert_modificar_datos_email.innerHTML = mensaje_alert_fijo("danger", "Por favor ingrese un email valido");
            return false;
        }


        //Funcion para desactivar los elementos inputs que se utilizan 
        cambiarEstadoInputs(campos_cambiar_estados, true);


        // Envío del formulario usando fetch
        const formData = new FormData();

        formData.append('email', txt_nuevo_email.value.trim());
        formData.append('password', txt_confirmar_password_email.value.trim());
        formData.append('tipo_modificacion', tipo_modificacion_email.value.trim());
        fetch('modificar_datos_usuario.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.lista.length > 0) {
                    alert_modificar_datos_email.innerHTML = mensaje_alert_lista_fijo(datos.estado, datos.mensaje, datos.lista);
                } else {
                    if (datos.estado === 'success') {

                        //Muestra un mensaje en la interfaz del usuario
                        alert_modificar_datos_email.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                        //Restable el checkbox para ocultar la contraseña
                        txt_confirmar_password_email.type = "password";

                        //Resetea el formulario para limpiar los campos
                        form_modificar_datos_email.reset();
                    } else {
                        //Muestra un mensaje en la interfaz del usuario
                        alert_modificar_datos_email.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    }
                }
                //Funcion para activar los elementos inputs que se utilizan 
                cambiarEstadoInputs(campos_cambiar_estados, false);
            })
            .catch(e => {
                //Muestra un mensaje de error en el contenedor ademas de cambiar al estado original los campos inputs
                cambiarEstadoInputs(campos_cambiar_estados, false);
                alert_modificar_datos_email.innerHTML = mensaje_alert_fijo("danger", e);
            });

    });


    /*Seccion para modificar la contraseña del usuario */


    //Agrega un evento cada vez que se haga un click en el checkbox cambiando el estado de la visibilidad de la contraseña
    document.getElementById("check_password_actual").addEventListener('click', function() {
        var check_password_actual = document.getElementById("check_password_actual");
        var txt_password_actual = document.getElementById('txt_password_actual');
        mostrar_ocultar_password(check_password_actual, txt_password_actual);

    });

    //Agrega un evento cada vez que se haga un click en el checkbox cambiando el estado de la visibilidad de la contraseña
    document.getElementById("check_nuevas_password").addEventListener('click', function() {
        var check_nuevas_password = document.getElementById("check_nuevas_password");
        var txt_nueva_password = document.getElementById('txt_nueva_password');
        var txt_confirmar_nueva_password = document.getElementById('txt_confirmar_nueva_password');
        mostrar_ocultar_password(check_nuevas_password, txt_nueva_password);
        mostrar_ocultar_password(check_nuevas_password, txt_confirmar_nueva_password);

    });


    //Manejo del envio del formulario para modificar la contraseña del usuario
    form_modificar_datos_password.addEventListener('submit', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_modificar_datos_password = document.getElementById("alert_modificar_datos_password");
        alert_modificar_datos_password.innerHTML = "";

        var txt_password_actual = document.getElementById('txt_password_actual');
        var txt_nueva_password = document.getElementById('txt_nueva_password');
        var txt_confirmar_nueva_password = document.getElementById('txt_confirmar_nueva_password');

        var tipo_modificacion_password = document.getElementById('tipo_modificacion_password');
        const campos_verificar = [txt_password_actual, txt_nueva_password, txt_confirmar_nueva_password];


        //Valida que los campos no esten vacios
        if (validarCampoVacio([txt_password_actual, txt_nueva_password, txt_confirmar_nueva_password])) {
            alert_modificar_datos_password.innerHTML = mensaje_alert_fijo("danger", "Por favor complete todos los campos");
            return false;
        }

        //Verifica que los campos tengan una longitud valida
        var lista_length_input = listaInputLengthNoValidos(campos_verificar);
        if (lista_length_input.length > 0) {
            alert_modificar_datos_password.innerHTML = mensaje_alert_lista_fijo("danger", "Los siguientes campos no cumplen con la longitud mínima de 6 carácteres o el máximo de caracteres indicado:", lista_length_input);
            return false;
        }


        //Valida que los campos no tengan espacios en blanco
        var lista_trim_input = listaInputEspaciosEnBlanco(campos_verificar);
        if (lista_trim_input.length > 0) {
            alert_modificar_datos_password.innerHTML = mensaje_alert_lista_fijo("danger", "No se permite que los campos tengan espacios en blanco. Los siguientes campos no cumplen con esto:", lista_trim_input);
            return false;
        }


        //Verifica que el tipo de modificacion que se va hacer es de password
        if (tipo_modificacion_password.value != "password") {
            alert_modificar_datos_password.innerHTML = mensaje_alert_fijo('danger', "No se puede modificar el valor del tipo de modificacion");
            return false;
        }

        //Verifica que los campos nueva contraseña y la confirmarcion de la nueva contraseña sean iguales
        if (!validarIgualdadPassword(txt_nueva_password, txt_confirmar_nueva_password)) {
            alert_modificar_datos_password.innerHTML = mensaje_alert_fijo("danger", "Los campos nueva contraseña y confirmacion nueva contraseña no son iguales");
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('password_actual', txt_password_actual.value.trim());
        formData.append('password_nueva', txt_nueva_password.value.trim());
        formData.append('password_nueva_confirmacion', txt_confirmar_nueva_password.value.trim());
        formData.append('tipo_modificacion', tipo_modificacion_password.value.trim());
        fetch('modificar_datos_usuario.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.lista.length > 0) {
                    //Muestra un mensaje en la interfaz del usuario
                    alert_modificar_datos_password.innerHTML = mensaje_alert_lista_fijo(datos.estado, datos.mensaje, datos.lista);
                } else {
                    if (datos.estado === 'success') {
                        //Muestra un mensaje en la interfaz del usuario
                        alert_modificar_datos_password.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                        //Restable los checkboxs de contraseña para ocultar los campos contraseñas
                        txt_password_actual.type = "password";
                        txt_nueva_password.type = "password";
                        txt_confirmar_nueva_password.type = "password";

                        //Resetea el formulario para limpiar los campos
                        form_modificar_datos_password.reset();
                    } else {

                        //Muestra un mensaje en la interfaz del usuario
                        alert_modificar_datos_password.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    }
                }
            })


            .catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_modificar_datos_password.innerHTML = mensaje_alert_fijo("danger", e);
            });


    });
</script>