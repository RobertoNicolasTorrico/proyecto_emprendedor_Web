<?php

//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_session.php");
include("../../config/consultas_bd/consultas_usuario_admin.php");
include("../../config/config_define.php");
$tipos_usuarios = array();
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
    <title>Proyecto Emprendedor Registrarse</title>

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
        <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
        <div class="container mb-5">
            <?php if (empty($mensaje_error)) {    ?>
                <div class="row justify-content-center">
                    <div class="col-12 col-sm-12 col-md-8 col-lg-6">

                        <!-- Card -->
                        <div class="card">

                            <!-- Header del Card -->
                            <div class="card-header">
                                <h5 class="text-center">Crear cuenta</h5>
                            </div>

                            <!-- Body del Card -->
                            <div class="card-body">

                                <!--Formulario para registrar el usuario-->
                                <form id="formulario_registro_usuario">
                                    <div class="row g-2">
                                        <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                                        <div id="alert_registro_usuario"></div>

                                        <!-- Div que contiene un select para saber si el usuario quiere registrarse como un emprendedor -->
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-3">
                                            <label for="select_tipo_usuario">¿Quiere registrarse como emprendedor para publicar sus productos y hacer publicaciones?</label>
                                            <select class="form-select text-break" name="select_tipo_usuario" id="select_tipo_usuario" aria-label="Floating_label_select" required>
                                                <option value="1">No</option>
                                                <option value="2">Si</option>
                                            </select>
                                        </div>

                                        <!-- Div que contiene el campo nombre del emprendimiento y muestra la cantidad maxima de caracteres -->
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3" id="div_emprendedor" style="display: none;">
                                            <input type="text" class="form-control" name="txt_emprendimiento" id="txt_emprendimiento" placeholder="Nombre del Emprendimiento" minlength="1" maxlength="50">
                                            <label for="txt_emprendimiento">Nombre del emprendimiento</label>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <span class="form-text">El nombre del emprendimiento no puede ser cambiado y solo se permite un maximo de 50 caracteres</span>
                                            </div>
                                        </div>


                                        <!-- Div que contiene el campo nombre de usuario y muestra la cantidad minima y maxima de caracteres permitidos -->
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                            <input type="text" class="form-control" name="txt_usuario" id="txt_usuario" placeholder="Nombre de usuario" minlength="5" maxlength="20" required>
                                            <label for="txt_usuario">Nombre de usuario</label>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <span class="form-text">El nombre de usuario no puede ser cambiado y debe tener un minimo de 5 caracteres y un maximo de 20</span>
                                            </div>
                                        </div>


                                        <!-- Div que contiene el campo nombre y muestra la cantidad maxima de caracteres -->
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-6 form-floating ml-1">
                                            <input type="text" class="form-control" name="txt_nombres" id="txt_nombres" placeholder="Nombres" minlength="1" maxlength="100" data-max="100" required>
                                            <label for="txt_nombres">Nombres</label>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <span class="form-text">Maximo 100 caracteres.<span id="txaCountNombres">100 restantes</span></span>
                                            </div>
                                        </div>


                                        <!-- Div que contiene el campo apellido y muestra la cantidad maxima de caracteres -->
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-6 form-floating ml-1">
                                            <input type="text" class="form-control" name="txt_apellidos" id="txt_apellidos" placeholder="Apellidos" minlength="1" maxlength="100" data-max="100" required>
                                            <label for="txt_apellidos">Apellidos</label>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <span class="form-text">Maximo 100 caracteres.<span id="txaCounApellido">100 restantes</span></span>
                                            </div>
                                        </div>


                                        <!-- Div que contiene el campo email -->
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                                            <input type="email" class="form-control" name="txt_email" id="txt_email" placeholder="Email" minlength="2" maxlength="320" required>
                                            <label for="txt_email">Email</label>
                                        </div>


                                        <!-- Div que contiene el campo contraseña y muestra la cantidad minima de caracteres permitidos -->
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-7 form-floating mb-3">
                                            <input type="password" class="form-control" name="txt_password" id="txt_password" placeholder="Contraseña" minlength="6" maxlength="60" required>
                                            <label for="txt_password">Contraseña</label>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <span class="form-text">La contraseña debe tener como minimo 6 caracteres</span>
                                            </div>
                                        </div>


                                        <!-- Div que contiene el campo confirmar contraseña -->
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-5 form-floating mb-3">
                                            <input type="password" class="form-control" name="txt_confirmar_password" id="txt_confirmar_password" placeholder="Confirmar contraseña" minlength="6" maxlength="60" required>
                                            <label for="txt_confirmar_password">Confirmar contraseña</label>
                                        </div>


                                        <!-- Div que contiene un checkbox para mostrar y ocultar la contraseña-->
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-check mb-3">
                                            <input type="checkbox" class="form-check-input" id="check_password">
                                            <label for="check_password" class="form-check-label">
                                                Mostrar contraseña
                                            </label>
                                        </div>

                                        <!-- Div que contiene un boton para enviar la informacion-->
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 text-center">
                                            <button class="btn btn-outline-primary" id="boton_enviar_datos" type="submit">Crear cuenta</button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                            <!-- Div que contiene el boton para volver a la pagina donde puede iniciar sesion -->
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 text-center">
                                <p>¿Ya tenés una cuenta?<a href="<?php echo ($url_base) ?>/paginas/iniciar_sesion/pagina_iniciar_sesion.php">Inicia sesión</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } else {  ?>
                <!-- Muestra un mensaje de error si existe algún problema.-->
                <div class="alert alert-danger" role="alert">
                    <?php echo ($mensaje_error); ?>
                </div>
            <?php } ?>


        </div>
    </main>
    <!-- Incluye el pie de pagina y el script necesario para el funcionamiento de la pagina.-->
    <script src="../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../config/js/funciones.js"></script>
    <?php require("../../template/footer.php"); ?>
</body>

</html>

<script>
    // Se obtiene el formulario que se va a utilizar
    var form_registro_usuario = document.getElementById("formulario_registro_usuario");

    //Agrega un evento para contar y mostrar caracteres restantes en el campo nombres
    document.getElementById('txt_nombres').addEventListener('input', function() {
        contarMostrarCarecteresRestantes('txt_nombres', 'txaCountNombres');
    });

    //Agrega un evento para contar y mostrar caracteres restantes en el campo apellidos
    document.getElementById('txt_apellidos').addEventListener('input', function() {
        contarMostrarCarecteresRestantes('txt_apellidos', 'txaCounApellido');
    });


    //Agrega un evento cada vez que se haga un click en el checkbox cambiando el estado de la visibilidad de las contraseñas
    document.getElementById('check_password').addEventListener('click', function() {
        var txt_password = document.getElementById('txt_password');
        var txt_confirmar_password = document.getElementById('txt_confirmar_password');
        var check_password = document.getElementById('check_password')


        mostrar_ocultar_password(check_password, txt_password);
        mostrar_ocultar_password(check_password, txt_confirmar_password);

    });



    //Agrega un evento cada vez que se cambia el valor del select 
    document.getElementById('select_tipo_usuario').addEventListener('change', function() {
        var div_emprendedor = document.getElementById('div_emprendedor');
        var txt_emprendimiento = document.getElementById('txt_emprendimiento');

        //Se verifica que sea un usuario emprendedor en caso que asi sea va abrir el div para agregar el nombre del emprendimiento
        if (select_tipo_usuario.value == 2) {
            div_emprendedor.style.display = "block";
            txt_emprendimiento.setAttribute("required", "");
        } else {
            //Se oculta el campo nombre del emprendimiento y restablecer el valor el campo
            div_emprendedor.style.display = "none";
            txt_emprendimiento.value = null;
            txt_emprendimiento.removeAttribute("required");
        }
    });



    //Manejo del envio del formulario para registrar un nuevo usuario
    form_registro_usuario.addEventListener('submit', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_registro_usuario = document.getElementById('alert_registro_usuario');
        alert_registro_usuario.innerHTML = "";

        var select_tipo_usuario = document.getElementById('select_tipo_usuario');
        var txt_usuario = document.getElementById('txt_usuario');
        var txt_nombres = document.getElementById('txt_nombres');
        var txt_apellidos = document.getElementById('txt_apellidos');
        var txt_email = document.getElementById('txt_email');
        var emprendimiento = document.getElementById('txt_emprendimiento');
        var boton_enviar_datos = document.getElementById('boton_enviar_datos');
        var campos_verificar_txt = [txt_usuario, txt_nombres, txt_apellidos, txt_email, txt_password, txt_confirmar_password];
        const campos_cambiar_estados = [txt_usuario, txt_nombres, txt_apellidos, txt_email, txt_password, txt_confirmar_password, boton_enviar_datos, select_tipo_usuario, emprendimiento];


        //Se verifica que el valor del select sea un valor numerico
        if (isNaN(select_tipo_usuario.value.trim())) {
            alert_registro_usuario.innerHTML = mensaje_alert_fijo("danger", "El select de tipo de usuario no recibio un valor numerico valido");
            return false;
        }

        //En caso que sea un emprendedor se va agregar el campo nombre del emprendimiento a los valores a verificar
        if (select_tipo_usuario.value.trim() == 2) {
            campos_verificar_txt.push(emprendimiento);
        }


        //Valida que los campos no esten vacios
        if (validarCampoVacio(campos_verificar_txt) || validarCampoVacio([select_tipo_usuario])) {
            alert_registro_usuario.innerHTML = mensaje_alert_fijo("danger", "Por favor complete todos los campos");
            return false;
        }

        //Se verifica que el campo nombre de usuario no tenga espacios en su contenido
        if (txt_usuario.value.includes(' ')) {
            alert_registro_usuario.innerHTML = mensaje_alert_fijo("danger", "El nombre de usuario no puede tener espacios en blanco");
            return false;
        }


        if (txt_usuario.value.length < 5 || txt_usuario.value.length > 20) {
            alert_registro_usuario.innerHTML = mensaje_alert_fijo("danger", "El campo nombre de usuario debe tener entre 5 y 20 caracteres");
            return false;
        }


        //Valida que el campo password no tenga espacios al inicio o al final de la cadena
        if (tieneEspacioEnBlacoPassword(txt_password)) {
            alert_registro_usuario.innerHTML = mensaje_alert_fijo("danger", "El campo contraseña no puede tener espacios en blanco");
            return false;
        }



        //Valida que los campos no tengan espacios al inicio o al final de la cadena
        var lista_trim_input = listaInputEspacioBlancoIF(campos_verificar_txt);
        if (lista_trim_input.length > 0) {
            alert_registro_usuario.innerHTML = mensaje_alert_lista_fijo("danger", "No se permite que los campos tengan espacios en blanco al inicio o al final. Los siguientes campos no cumplen con esto:", lista_trim_input);
            return false;
        }


        //Verifica que los campos tengan una longitud valida
        var lista_length_input = listaInputLengthNoValidos(campos_verificar_txt);
        if (lista_length_input.length > 0) {
            alert_registro_usuario.innerHTML = mensaje_alert_lista_fijo("danger", "Los siguientes campos no cumplen con la longitud mínima de 1 carácter o el máximo de caracteres indicado:", lista_length_input);
            return false;
        }

        //Valida que el campo email sea valido
        if (!validarCampoEmail(txt_email)) {
            alert_registro_usuario.innerHTML = mensaje_alert_fijo("danger", "Por favor ingrese un email con formato valido");
            return false;
        }



        //Verifica que los campos  contraseña y la confirmarcion de la contraseña sean iguales
        if (!validarIgualdadPassword(txt_password, txt_confirmar_password)) {
            alert_registro_usuario.innerHTML = mensaje_alert_fijo("danger", "Los campos contraseña y confirmacion de contraseña no son iguales");
            return false;
        }

        //Funcion para desactivar los elementos inputs que se utilizan 
        cambiarEstadoInputs(campos_cambiar_estados, true);


        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('nombre_usuario', txt_usuario.value.trim());
        formData.append('nombres', txt_nombres.value.trim());
        formData.append('apellidos', txt_apellidos.value.trim());
        formData.append('email', txt_email.value.trim());
        formData.append('password', txt_password.value.trim());
        formData.append('confirmar_password', txt_confirmar_password.value.trim());
        formData.append('tipo_usuario', select_tipo_usuario.value.trim());
        if (select_tipo_usuario.value.trim() == 2) {
            formData.append('nombre_emprendimiento', emprendimiento.value.trim());
        }

        fetch('alta_usuario.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.lista.length > 0) {
                    alert_registro_usuario.innerHTML = mensaje_alert_lista_fijo(datos.estado, datos.mensaje, datos.lista);
                } else {
                    if (datos.estado == 'success') {

                        //Se verifica que el valor del select sea emprendedor
                        if (select_tipo_usuario.value == 2) {
                            //En caso que sea emprendedor se elimina todo el contenido del campo del emprendimiento y oculta el campo
                            txt_emprendimiento.value = null;
                            div_emprendedor.style.display = "none";
                            txt_emprendimiento.removeAttribute("required");
                        }

                        //Muestra un mensaje en la interfaz del usuario
                        alert_registro_usuario.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                        //Restable el checkbox para ocultar el campo contraseña y la nueva contraseña
                        txt_password.type = "password";
                        txt_confirmar_password.type = "password";

                        //Resetea el formulario para limpiar los campos
                        form_registro_usuario.reset();
                        contarMostrarCarecteresRestantes('txt_nombres', 'txaCountNombres');

                        contarMostrarCarecteresRestantes('txt_apellidos', 'txaCounApellido');


                    } else {
                        //Muestra un mensaje en la interfaz del usuario
                        alert_registro_usuario.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    }
                }
                //Funcion para activar los elementos inputs que se utilizan 
                cambiarEstadoInputs(campos_cambiar_estados, false);
            })
            .catch(e => {
                //Muestra un mensaje de error en el contenedor ademas de cambiar al estado original los campos inputs
                cambiarEstadoInputs(campos_cambiar_estados, false);
                alert_registro_usuario.innerHTML = mensaje_alert_fijo("danger", e);


            });
    });
</script>