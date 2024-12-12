<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_session.php");
include("../../config/config_define.php");

$mensaje_error = "";
try {

    //Establecer la sesion
    session_start();

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    // Verificar datos de sesión del usuario y redirigidir a la pagina de inicio usuario si la sesion es valida
    verificarDatosSessionUsuario($conexion);
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
    <title>Proyecto Emprendedor Iniciar Sesion</title>

    <!--Enlace al archivo de estilos propios del proyecto-->
    <link href="../../config/css/estilos.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Bootstrap-->
    <link href="../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de FontAwesome para iconos-->
    <link href="../../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">


</head>

<body>
    <!--Incluye el archivo de la barra de navegación general.-->
    <?php include($url_navbar_general); ?>

    <!--Separa el contenido principal de la pagina del pie de pagina.-->
    <main>

        <div class="container">
            <?php if (empty($mensaje_error)) { ?>
                <div class="row justify-content-center">
                    <div class="col-12 col-sm-12 col-md-8 col-lg-6">

                        <!-- Card -->
                        <div class="card">

                            <!-- Header del Card -->
                            <div class="card-header">
                                <h5 class="text-center">Iniciar sesión</h5>
                            </div>

                            <!-- Body del Card -->
                            <div class="card-body">


                                <!--Formulario para inciar sesion-->
                                <form id="formulario_iniciar_sesion">

                                    <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                    <div id="alert_inicio_sesion"></div>


                                    <!-- Div que contiene el campo email -->
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" name="txt_email" id="txt_email" placeholder="Email" minlength="2" maxlength="320" required>
                                        <label for="txt_email">Email</label>
                                    </div>


                                    <!-- Div que contiene el campo contraseña -->
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" name="txt_password" id="txt_password" placeholder="Contraseña" minlength="6" maxlength="60" required>
                                        <label for="txt_password">Contraseña</label>
                                    </div>


                                    <!-- Div que contiene un checkbox para mostrar y ocultar la contraseña-->
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="check_password">
                                        <label class="form-check-label" for="check_password">
                                            Mostrar contraseña
                                        </label>
                                    </div>

                                    <!-- Div que contiene un boton para enviar la informacion-->
                                    <div class="text-center">
                                        <button class="btn btn-outline-dark" type="submit">Ingresar</button>
                                    </div>
                                </form>


                                <!-- Div que contiene el boton para ir a la pagina para registrarse -->
                                <div class="text-center">
                                    <p>¿No tenés cuenta? <a href="<?php echo ($url_base) ?>/paginas/registro/pagina_registrarse.php">Crear cuenta</a></p>
                                </div>


                                <!-- Div que contiene el boton para ir a la pagina para recuperar la contraseña-->
                                <div class="text-center">
                                    <a href="<?php echo ($url_base) ?>/paginas/recuperar_cuenta_password/pagina_enviar_email_recuperar.php">¿Olvidaste tu contraseña?</a>
                                </div>

                                <div class="text-center mt-2">
                                    <a href="<?php echo ($url_base) ?>/paginas/activar_cuenta/pagina_enviar_email_activacion.php">¿No recibiste tu correo de activación o tu cuenta está desactivada?</a>
                                </div>


                            </div>

                        </div>
                    </div>
                </div>

            <?php } else { ?>
                <!-- Muestra un mensaje de error si existe algún problema.-->
                <div class="alert alert-danger" role="alert">
                    <?php echo ($mensaje_error) ?>
                </div>
            <?php } ?>

        </div>

    </main>

    <!-- Incluye el pie de pagina y varios scripts necesarios para el funcionamiento de la pagina.-->
    <?php require("../../template/footer.php"); ?>

    <script src="../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../config/js/funciones.js"></script>

</body>

</html>

<script>
    // Se obtiene el formulario que se va a utilizar
    var form_Iniciar_Sesion = document.getElementById("formulario_iniciar_sesion");


    //Agrega un evento cada vez que se haga un click en el checkbox cambiando el estado de la visibilidad de la contraseña
    document.getElementById('check_password').addEventListener('click', function() {
        var password = document.getElementById('txt_password');
        mostrar_ocultar_password(document.getElementById('check_password'), password);
    });


    //Manejo del envio del formulario para iniciar sesion
    form_Iniciar_Sesion.addEventListener('submit', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_inicio_sesion = document.getElementById("alert_inicio_sesion");
        alert_inicio_sesion.innerHTML = "";


        var password = document.getElementById('txt_password');
        var email = document.getElementById('txt_email');
        var campos_verificar = [email, password];

        //Valida que los campos no esten vacios
        if (validarCampoVacio(campos_verificar)) {
            alert_inicio_sesion.innerHTML = mensaje_alert_fijo("danger", "Por favor complete todos los campos");
            return false;
        }


        //Valida que el campo email sea valido
        if (!validarCampoEmail(email)) {
            alert_inicio_sesion.innerHTML = mensaje_alert_fijo("danger", "Por favor ingrese un email con formato valido");
            return false;
        }


        //Valida que el campo password no tenga espacios en blaco en 
        if (tieneEspacioEnBlacoPassword(password)) {
            alert_inicio_sesion.innerHTML = mensaje_alert_fijo("danger", "La contraseña no puede tener espacios en blanco");
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData();

        formData.append('email', email.value);
        formData.append('password', password.value);
        fetch('iniciar_sesion.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())

            .then(datos => {

                if (datos.lista.length > 0) {
                    //Muestra un mensaje en la interfaz del usuario
                    alert_inicio_sesion.innerHTML = mensaje_alert_lista_fijo(datos.estado, datos.mensaje, datos.lista);
                } else {
                    if (datos.estado == 'danger') {
                        //Muestra un mensaje en la interfaz del usuario
                        alert_inicio_sesion.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    } else {
                        //Se verifica que en caso que se reciba la URL direccionada al usuario a la pagina de inicio usuario
                        if (typeof datos.url !== 'undefined') {
                            window.location.href = datos.url;
                        }
                    }
                }
            })
            .catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_inicio_sesion.innerHTML = mensaje_alert_fijo("danger", e);

            });

    });
</script>