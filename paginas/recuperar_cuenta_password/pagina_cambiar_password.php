<?php

//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_token.php");
include("../../config/config_define.php");


$token = isset($_GET['token']) ? $_GET['token'] : '';
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';

//Establecer la sesion
session_start();


//Elimina las variables de sesion especificas del usuario que son ID del usuario y el tipo de usuario.
unset($_SESSION['id_usuario']);
unset($_SESSION['tipo_usuario']);


$token_valido = false;
$mensaje_error = "";
try {

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();


    //Se verifica que la URL tenga el ID de un usuario y el token
    if (!empty($id_usuario) && !empty($token)) {
        //Se verifica que el usuario si halla pedido recuperar la contraseña
        $token_valido = validarTokenNuevaPassword($conexion, $token, $id_usuario);
        if (!$token_valido) {
            throw new Exception("El id o token no son validos");
        }
    } else {
        throw new Exception("El campo id o token esta vacio");
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
    <title>Proyecto emprendedor Cambiar Contraseña</title>

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

        <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
        <div class="container">
            <?php if (empty($mensaje_error)) { ?>
                <div class="row justify-content-center">
                    <div class="col-12 col-sm-12 col-md-8 col-lg-8">

                        <!--Formulario para enviar los datos-->
                        <form id="formulario_cambiar_password" method="POST">

                            <!-- Card -->
                            <div class="card">

                                <!-- Header del Card -->
                                <div class="card-header">
                                    <h5 class="text-center">Cambiar Contraseña</h5>
                                </div>


                                <!-- Body del Card -->
                                <div class="card-body">

                                    <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                    <div id="alert_cambiar_password"></div>
                                    <div class="row g-2">

                                        <!-- Div que contiene el campo nueva contraseña y muestra la cantidad minima de caracteres permitidos -->
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-6 form-floating mb-1">
                                            <input type="password" class="form-control" name="txt_nueva_password" id="txt_nueva_password" placeholder="Nueva contraseña" minlength="6" maxlength="60" required>
                                            <label for="txt_nueva_password">Nueva contraseña</label>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <span class="form-text">La contraseña debe tener como minimo 6 caracteres</span>
                                            </div>
                                        </div>


                                        <!-- Div que contiene el campo confirmar contraseña -->
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-6 form-floating mb-1">
                                            <input type="password" class="form-control" name="txt_confirmar_nueva_password" id="txt_confirmar_nueva_password" placeholder="Confirmar contraseña" minlength="6" maxlength="60" required>
                                            <label for="txt_confirmar_nueva_password">Confirmar nueva contraseña</label>

                                        </div>


                                        <!-- Div que contiene un checkbox para mostrar y ocultar la contraseña-->
                                        <div class="form-check mb-1">
                                            <input type="checkbox" class="form-check-input" id="check_nuevas_password">
                                            <label for="check_nuevas_password" class="form-check-label">
                                                Mostrar contraseña
                                            </label>
                                        </div>
                                    </div>

                                </div>

                                <!-- Footer del Card que contiene el boton para enviar los datos-->
                                <div class="card-footer text-center">
                                    <button class="btn btn-outline-success" id="boton_confirmar" type="submit">Confirmar</button>
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
    check_nuevas_password = document.getElementById("check_nuevas_password");

    // Se obtiene el formulario para cambiar la contraseña
    form_cambiar_password = document.getElementById("formulario_cambiar_password");


    //Verifica que los datos del checkbox y del formulario existan
    if (check_nuevas_password != null || form_cambiar_password != null) {


        //Agrega un evento cada vez que se haga un click en el checkbox cambiando el estado de la visibilidad de las contraseñas
        check_nuevas_password.addEventListener('click', function() {
            var txt_nueva_password = document.getElementById('txt_nueva_password');
            var txt_confirmar_nueva_password = document.getElementById('txt_confirmar_nueva_password');
            mostrar_ocultar_password(check_nuevas_password, txt_nueva_password);
            mostrar_ocultar_password(check_nuevas_password, txt_confirmar_nueva_password);

        });


        //Manejo del envio del formulario para recuperar la contraseña
        form_cambiar_password.addEventListener('submit', function(event) {

            //Previene el envio por defecto del formulario
            event.preventDefault();

            //Elimina cualquier alerta previa 
            var alert_cambiar_password = document.getElementById("alert_cambiar_password");
            alert_cambiar_password.innerHTML = "";

            var txt_nueva_password = document.getElementById('txt_nueva_password');
            var txt_confirmar_nueva_password = document.getElementById('txt_confirmar_nueva_password');
            var boton_confirmar = document.getElementById('boton_confirmar');
            var campos_verificar = [txt_nueva_password, txt_confirmar_nueva_password];
            var campos_cambiar_estados = [txt_nueva_password, txt_confirmar_nueva_password, boton_confirmar];



            //Valida que el campo no este vacio
            if (validarCampoVacio(campos_verificar)) {
                alert_cambiar_password.innerHTML = mensaje_alert_fijo("danger", "Por favor complete todos los campos");
                return false;
            }


            //Verifica que los campos tengan una longitud valida
            var lista_length_input = listaInputLengthNoValidos(campos_verificar);
            if (lista_length_input.length > 0) {
                alert_cambiar_password.innerHTML = mensaje_alert_lista_fijo("danger", "Los siguientes campos no cumplen con la longitud mínima de 6 carácteres o el máximo de caracteres indicado:", lista_length_input);
                return false;
            }

            //Valida que el campo password no tenga espacios en blanco
            if (tieneEspacioEnBlacoPassword(txt_nueva_password)) {
                alert_cambiar_password.innerHTML = mensaje_alert_fijo("danger", "El campo nueva contraseña no puede tener espacios en blanco");
                return false;
            }

            //Valida que el campo password no tenga espacios en blanco
            if (tieneEspacioEnBlacoPassword(txt_confirmar_nueva_password)) {
                alert_cambiar_password.innerHTML = mensaje_alert_fijo("danger", "El campo confirmar nueva contraseña no puede tener espacios en blanco");
                return false;
            }
            //Verifica que los campos contraseña y la confirmarcion de la contraseña sean iguales
            if (!validarIgualdadPassword(txt_nueva_password, txt_confirmar_nueva_password)) {
                alert_cambiar_password.innerHTML = mensaje_alert_fijo("danger", "Los campos nueva contraseña y la confirmacion de nueva contraseña no son iguales");
                return false;
            }

            //Funcion para desactivar los elementos inputs que se utilizan 
            cambiarEstadoInputs(campos_cambiar_estados, true);


            // Envío del formulario usando fetch
            const formData = new FormData();
            formData.append('password_nueva', txt_nueva_password.value.trim());
            formData.append('password_nueva_confirmacion', txt_confirmar_nueva_password.value.trim());

            fetch(`modificar_contrasenia.php${window.location.search}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(datos => {
                    if (datos.lista.length > 0) {
                        //Funcion para activar los elementos inputs que se utilizan 
                        cambiarEstadoInputs(campos_cambiar_estados, false);

                        //Muestra un mensaje en la interfaz del usuario
                        alert_cambiar_password.innerHTML = mensaje_alert_lista_fijo(datos.estado, datos.mensaje, datos.lista);
                    } else {
                        if (datos.estado === 'success') {


                            //Muestra un mensaje en la interfaz del usuario
                            alert_cambiar_password.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                            //Restable el checkbox para ocultar el campo contraseña y la nueva contraseña
                            txt_nueva_password.type = "password";
                            txt_confirmar_nueva_password.type = "password";

                            //Resetea el formulario para limpiar los campos
                            form_cambiar_password.reset();


                            //Se obtiene la direccion para redireccionar al usuario a la pagina de iniciar sesion
                            var url_base = <?php echo json_encode($url_base); ?>;
                            url_base = url_base + "/paginas/iniciar_sesion/pagina_iniciar_sesion.php";

                            //Se redirige al usuario a la pagina de iniciar sesion despues que acabe el tiempo
                            setTimeout(() => {
                                window.location.href = url_base;
                            }, "2000");

                        } else {
                            //Funcion para activar los elementos inputs que se utilizan 
                            cambiarEstadoInputs(campos_cambiar_estados, false);

                            //Muestra un mensaje en la interfaz del usuario
                            alert_cambiar_password.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                        }
                    }


                })
                .catch(e => {
                    //Muestra un mensaje de error en el contenedor ademas de cambiar al estado original los campos inputs
                    cambiarEstadoInputs(campos_cambiar_estados, false);
                    alert_cambiar_password.innerHTML = mensaje_alert_fijo("danger", e);
                });

        });
    }
</script>