<?php

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
    <title>Proyecto Emprendedor Activar Cuenta</title>

    <!--Enlace al archivo de estilos propios del proyecto-->
    <link href="../../config/css/estilos.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Bootstrap-->
    <link href="../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

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

                        <!--Formulario para enviar los datos-->
                        <form id="formulario_recuperar_cuenta" method="POST">


                            <!-- Card -->
                            <div class="card">

                                <!-- Header del Card -->
                                <div class="card-header">
                                    <h5 class="text-center">Activar Cuenta</h5>
                                </div>

                                <!-- Body del Card -->
                                <div class="card-body">

                                    <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                    <div id="alert_recuperar_cuenta"></div>
                                    <p>Por favor, ingrese el correo electrónico con el que está registrado para enviarle los pasos necesarios para activar su cuenta.</p>

                                    <!-- Div que contiene el campo email -->
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" name="txt_email" id="txt_email" placeholder="Email" minlength="2" maxlength="320" required>
                                        <label for="txt_email">Email</label>
                                    </div>


                                    <!-- Div que contiene un boton para enviar la informacion y el otro para direccionarse a la pagina de inicio sesion-->
                                    <div class="text-center">
                                        <a class="btn btn-outline-primary" id="boton_cancelar" href="../iniciar_sesion/pagina_iniciar_sesion.php">Cancelar</a>
                                        <button class="btn btn-outline-success" id="boton_enviar" type="submit">Enviar</button>
                                    </div>

                                </div>
                            </div>
                        </form>
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
    <?php require("../../template/footer.php"); ?>
    <script src="../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../config/js/funciones.js"></script>
</body>

</html>

<script>
    // Se obtiene el formulario para activar la cuenta del usuario
    var form_recuperar_cuenta = document.getElementById("formulario_recuperar_cuenta");

    //Manejo del envio del formulario para enviar el email de activacion
    form_recuperar_cuenta.addEventListener('submit', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_recuperar_cuenta = document.getElementById("alert_recuperar_cuenta");
        alert_recuperar_cuenta.innerHTML = "";


        var email = document.getElementById('txt_email');
        var boton_enviar = document.getElementById('boton_enviar');
        var boton_cancelar = document.getElementById('boton_cancelar');
        var campos_cambiar_estados = [email, boton_enviar, boton_cancelar];

        //Valida que el campo no este vacio
        if (validarCampoVacio([email])) {
            alert_recuperar_cuenta.innerHTML = mensaje_alert_fijo("danger", "Por favor complete el campo email");
            return false;
        }


        //Verifica que el campo de email tenga una longitud valida
        if (!validarCantLengthInput(email)) {
            alert_recuperar_cuenta.innerHTML = mensaje_alert_fijo("danger", "El campo email no cumple con la longitud mínima de 1 carácter o el máximo caracteres indicado");
            return false;
        }


        //Valida que el campo email sea valido
        if (!validarCampoEmail(email)) {
            alert_recuperar_cuenta.innerHTML = mensaje_alert_fijo("danger", "Por favor ingrese un email con formato valido");
            return false;
        }


        //Funcion para desactivar los elementos inputs que se utilizan 
        cambiarEstadoInputs(campos_cambiar_estados, true);

        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('email', email.value.trim());

        fetch('enviar_email_activar_cuenta.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())

            .then(datos => {
                if (datos.estado == 'success') {
                    //Muestra un mensaje en la interfaz del usuario
                    alert_recuperar_cuenta.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                    //Resetea el formulario para limpiar los campos
                    form_recuperar_cuenta.reset();
                } else {

                    //Muestra un mensaje en la interfaz del usuario
                    alert_recuperar_cuenta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);

                }

                //Funcion para activar los elementos inputs que se utilizan 
                cambiarEstadoInputs(campos_cambiar_estados, false);
            })
            .catch(e => {

                //Muestra un mensaje de error en el contenedor ademas de cambiar al estado original los campos inputs
                cambiarEstadoInputs(campos_cambiar_estados, false);
                alert_recuperar_cuenta.innerHTML = mensaje_alert_fijo("danger", e);
            });

    });
</script>