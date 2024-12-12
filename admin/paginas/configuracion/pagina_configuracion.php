<?php
//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/config_define.php");

$mensaje_error = "";
$datos_usuario = array();
$tipo_usuario = 0;
try {

    //Establecer la sesion
    session_start();

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica que los datos de sesion sean de un usuario administrador y que sea un usuario valido
    verificarDatosSessionUsuarioAdministrador($conexion);

    //Se obtiene los datos del usuario administrador
    $datos_usuario = obtenerDatosUsuarioAdministrador($conexion, $_SESSION['id_usuario_administrador']);
} catch (Exception $e) {
    $mensaje_error = $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--Titulo de la página-->
    <title>Proyecto Emprendedor Admin Configuracion</title>

    <!--Enlace al archivo de estilos propios del proyecto-->
    <link href="../../../config/css/estilos.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Bootstrap-->
    <link href="../../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de FontAwesome para iconos-->
    <link href="../../../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">

</head>

<body>
    <!--Incluye el archivo de la barra de navegación para usuarios administrador.-->
    <?php include($url_navbar_usuario_admin);  ?>


    <!--Separa el contenido principal de la pagina del pie de pagina.-->
    <main>


        <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
        <div class="container mb-3">
            <?php if (empty($mensaje_error)) { ?>
                <h1 class="text-center">Configuraciones</h1>

                <!--Define una navegación por pestañas para alternar entre las vistas de datos personales y cambiar contraseña-->
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="datos_personales_tab" data-bs-toggle="tab" data-bs-target="#datos_personales" type="button" role="tab" aria-controls="datos_personales" aria-selected="true">Datos Personales</button>
                        <button class="nav-link" id="cambiar_contrasenia_tab" data-bs-toggle="tab" data-bs-target="#cambiar_contrasenia" type="button" role="tab" aria-controls="cambiar_contrasenia" aria-selected="false">Cambiar Contraseña</button>
                    </div>
                </nav>


                <!--Contenedor para el contenido de las pestañas.La pestaña de datos personales del usuario administrador esta activa por defecto.-->
                <div class="tab-content" id="nav-tabContent">


                    <!--Contenedor de los datos personales del usuario administrador.-->
                    <div class="tab-pane fade show active" id="datos_personales" role="tabpanel" aria-labelledby="datos_personales_tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12 col-sm-12 col-md-8 col-lg-6">

                                <!--Formulario para enviar los datos-->
                                <form id="formulario_modificar_datos_personales" method="POST">

                                    <!-- Card -->
                                    <div class="card">

                                        <!-- Body del Card -->
                                        <div class="card-body">
                                            <h5 class="text-center">Datos del usuario administrador</h5>

                                            <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                            <div id="alert_modificar_datos_personales"></div>

                                            <!-- Contenedor para mostrar los datos del usuario administrador  -->
                                            <div class="row g-2">

                                                <!-- Div que contiene el campo nombre de usuario y muestra la cantidad minima y maxima de caracteres permitidos -->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                                    <input type="text" class="form-control" name="txt_usuario" id="txt_usuario" placeholder="Nombre de usuario" minlength="5" maxlength="20" value="<?php echo ($datos_usuario['nombre_usuario']); ?>" required>
                                                    <label for="txt_usuario">Nombre de usuario</label>
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                        <span class="form-text">El nombre de usuario debe tener un minimo de 5 caracteres y un maximo de 20</span>
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


                                                <!-- Div que contiene el campo apellido y muestra la cantidad maxima de caracteres -->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-6 form-floating mb-3">
                                                    <input type="text" class="form-control" name="txt_apellidos" id="txt_apellidos" placeholder="Apellidos" minlength="1" maxlength="100" data-max="100" value="<?php echo ($datos_usuario['apellidos']); ?>" required>
                                                    <label for="txt_apellidos">Apellidos</label>
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                        <span class="form-text">Maximo 100 caracteres.<span id="txaCountApellidos">100 restantes</span></span>
                                                    </div>
                                                </div>

                                                <!-- Div que contiene el campo fecha de registro -->
                                                <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                                    <div class="input-group">
                                                        <span class="input-group-text">Fecha de registro:</span>
                                                        <input type="datetime-local" name="fecha_registro" id="fecha_registro" placeholder="Fecha registro" class="form-control" value="<?php echo ($datos_usuario['fecha']); ?>" disabled>
                                                    </div>
                                                </div>

                                                <!-- Div que contiene el campo email -->
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                                    <input type="email" class="form-control" name="txt_email" id="txt_email" placeholder="Email" value="<?php echo ($datos_usuario['email']); ?>" required minlength="1" maxlength="320" data-max="320">
                                                    <label for="txt_email">Email</label>
                                                </div>

                                                <input type="hidden" name="tipo_modificacion_usuario" id="tipo_modificacion_usuario" value="usuario" required>

                                            </div>
                                        </div>


                                        <!-- Footer del Card  -->
                                        <div class="card-footer">

                                            <!-- Botón para enviar los cambios hecho -->
                                            <button type="submit" class="btn btn-outline-success">Guardar cambios</button>
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

            <?php } else { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo ($mensaje_error) ?>
                </div>
            <?php } ?>


        </div>

    </main>
    <!-- Incluye el pie de pagina y varios scripts necesarios para el funcionamiento de la pagina.-->
    <script src="../../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../../config/js/funciones.js"></script>
    <?php require("../../../template/footer.php"); ?>
</body>

</html>

<script>
    // Se obtiene los formularios que se van a utilizar
    var form_modificar_datos_personales = document.getElementById("formulario_modificar_datos_personales");
    var form_modificar_datos_password = document.getElementById("formulario_modificar_datos_password");


    var datos_usuario = [<?php echo json_encode($datos_usuario); ?>];
    var js_datos_usuario = datos_usuario[0];



    /*Seccion parar modificar los datos personales del usuario administrador */


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

    //Manejo del envio del formulario para modificar los datos personales del usuario administrador
    form_modificar_datos_personales.addEventListener('submit', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();


        //Elimina cualquier alerta previa 
        var alert_modificar_datos_personales = document.getElementById("alert_modificar_datos_personales");
        alert_modificar_datos_personales.innerHTML = "";

        var txt_nombres = document.getElementById('txt_nombres');
        var txt_apellidos = document.getElementById('txt_apellidos');
        var txt_usuario = document.getElementById('txt_usuario');
        var txt_email = document.getElementById('txt_email');

        var tipo_modificacion_usuario = document.getElementById('tipo_modificacion_usuario');
        var campos_verificar = [txt_nombres, txt_apellidos, txt_usuario, txt_email];


        var js_nombre_usuario = js_datos_usuario['nombre_usuario'];
        var js_usuario_nombres = js_datos_usuario['nombres'];
        var js_usuario_apellidos = js_datos_usuario['apellidos'];
        var js_email = js_datos_usuario['email'];


        //Se compara los datos originales del usuario con los datos actuales para saber si hubo cambios o no
        if (txt_nombres.value == js_usuario_nombres && txt_apellidos.value == js_usuario_apellidos && txt_usuario == js_nombre_usuario && js_email == txt_email) {
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


        if (txt_usuario.value.length < 5 || txt_usuario.value.length > 20) {
            alert_modificar_datos_personales.innerHTML = mensaje_alert_fijo("danger", "El campo nombre de usuario debe tener entre 5 y 20 caracteres");
            return false;
        }


        //Valida que los campos no tengan espacios al inicio o al final de la cadena
        var lista_trim_input = listaInputEspacioBlancoIF(campos_verificar);
        if (lista_trim_input.length > 0) {
            alert_modificar_datos_personales.innerHTML = mensaje_alert_lista_fijo("danger", "No se permite que los campos tengan espacios en blanco al inicio o al final. Los siguientes campos no cumplen con esto:", lista_trim_input);
            return false;
        }


        //Verifica que los campos tengan una longitud valida
        var lista_length_input = listaInputLengthNoValidos(campos_verificar);
        if (lista_length_input.length > 0) {
            alert_modificar_datos_personales.innerHTML = mensaje_alert_lista_fijo("danger", "Los siguientes campos no cumplen con la longitud mínima de 1 carácter o el máximo de caracteres indicado:", lista_length_input);
            return false;
        }


        //Valida que el campo email sea valido
        if (!validarCampoEmail(txt_email)) {
            alert_modificar_datos_personales.innerHTML = mensaje_alert_fijo("danger", "Por favor ingrese un email con formato valido");
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('nombre_usuario', txt_usuario.value.trim());
        formData.append('nombres', txt_nombres.value.trim());
        formData.append('apellidos', txt_apellidos.value.trim());
        formData.append('email', txt_email.value.trim());
        formData.append('tipo_modificacion', tipo_modificacion_usuario.value.trim());

        fetch('modificar_datos_admin.php', {
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





    /*Seccion para modificar la contraseña del usuario administrador */


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


    //Manejo del envio del formulario para modificar la contraseña del usuario administrador
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
            alert_modificar_datos_password.innerHTML = mensaje_alert_fijo("danger", "Los campos contraseña y confirmar de contraseña no son iguales");
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('password_actual', txt_password_actual.value.trim());
        formData.append('password_nueva', txt_nueva_password.value.trim());
        formData.append('password_nueva_confirmacion', txt_confirmar_nueva_password.value.trim());
        formData.append('tipo_modificacion', tipo_modificacion_password.value.trim());
        fetch('modificar_datos_admin.php', {
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