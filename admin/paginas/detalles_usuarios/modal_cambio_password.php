<!--Modal para cambiar la contraseña  del cuenta-->
<div class="modal fade" id="modalCambioPassword" tabindex="-1" aria-labelledby="modalCambioPasswordLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">

            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalCambioPasswordLabel">Aviso</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Formulario dentro del modal -->
            <form method="POST" id="formulario_modificar_datos_password">

                <!-- Body del modal -->
                <div class="modal-body">

                    <!-- Div para mostrar errores al cambiar la contraseña del usuario -->
                    <div id="alert_modificar_datos_password"></div>
                    <p>¿Esta seguro de querer cambiar la contraseña del usuario?</p>

                    <input type="hidden" name="tipo_modificacion_password" id="tipo_modificacion_password" value="password" required>


                    <!-- Campo para la nueva contrasñea del usuario -->
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="txt_nueva_password" id="txt_nueva_password" placeholder="Nueva contraseña" minlength="6" maxlength="60" required>
                        <label for="txt_nueva_password">Nueva contraseña</label>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                            <span class="form-text">La contraseña debe tener como minimo 6 caracteres</span>
                        </div>
                    </div>



                    <!-- Campo para confirmar la nueva contrasñea del usuario -->
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="txt_confirmar_nueva_password" id="txt_confirmar_nueva_password" placeholder="Confirmar contraseña" minlength="6" maxlength="60" required>
                        <label for="txt_confirmar_nueva_password">Confirmar nueva contraseña</label>

                    </div>

                    <!-- Div que contiene el boton para ocultar y mostrar contraseña -->
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="check_nuevas_password">
                        <label for="check_nuevas_password" class="form-check-label">
                            Mostrar contraseña
                        </label>
                    </div>
                </div>

                <!--Footer del modal -->
                <div class="modal-footer">
                    <!-- Botón para cerrar el modal y cancelar la operacion -->
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>

                    <!-- Botón para desactivar la cuenta -->
                    <button type="submit" class="btn btn-outline-success">Confirmar</button>

                </div>
            </form>

        </div>
    </div>
</div>
<script>
    // Obtener el formulario de cambio de contraseña
    var form_modificar_datos_password = document.getElementById("formulario_modificar_datos_password");

    // Obtener el modal para cambiar la contraseña 
    const modalCambioPassword = document.getElementById('modalCambioPassword');


    //Agrega un evento cada vez que se haga un click en el checkbox cambiando el estado de la visibilidad de la contraseña
    document.getElementById("check_nuevas_password").addEventListener('click', function() {
        var check_nuevas_password = document.getElementById("check_nuevas_password");

        var txt_nueva_password = document.getElementById('txt_nueva_password');
        var txt_confirmar_nueva_password = document.getElementById('txt_confirmar_nueva_password');
        mostrar_ocultar_password(check_nuevas_password, txt_nueva_password);
        mostrar_ocultar_password(check_nuevas_password, txt_confirmar_nueva_password);
    });

    //Agrega un evento cuando se cierra el modal
    modalCambioPassword.addEventListener('hidden.bs.modal', event => {

        //Elimina cualquier alerta previa en el modal
        var alert_modificar_datos_password = document.getElementById('alert_modificar_datos_password');
        alert_modificar_datos_password.innerHTML = '';

        //Restable el checkbox para ocultar la contraseña
        txt_nueva_password.type = "password";
        txt_confirmar_nueva_password.type = "password";

        //Resetea el formulario para limpiar los campos
        form_modificar_datos_password.reset();

    });

    //Manejo del envio del formulario para modificar la contraseña del usuario
    form_modificar_datos_password.addEventListener('submit', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_modificar_datos_password = document.getElementById("alert_modificar_datos_password");
        alert_modificar_datos_password.innerHTML = "";

        var txt_nueva_password = document.getElementById('txt_nueva_password');
        var txt_confirmar_nueva_password = document.getElementById('txt_confirmar_nueva_password');


        var tipo_modificacion_password = document.getElementById('tipo_modificacion_password');
        const campos_verificar = [txt_nueva_password, txt_confirmar_nueva_password];


        //Valida que los campos no esten vacios
        if (validarCampoVacio([txt_nueva_password, txt_confirmar_nueva_password])) {
            alert_modificar_datos_password.innerHTML = mensaje_alert_fijo("danger", "Por favor complete todos los campos");
            return false;
        }


        //Verifica que los campos tengan una longitud valida
        var lista_length_input = listaInputLengthNoValidos(campos_verificar);
        if (lista_length_input.length > 0) {
            alert_modificar_datos_password.innerHTML = mensaje_alert_lista_fijo("danger", "Los siguientes campos no cumplen con la longitud mínima de 6 carácter o el máximo de caracteres indicado:", lista_length_input);
            return false;
        }


        //Valida que los campos no tengan espacios al inicio o al final de la cadena
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



        //Verifica que los campos nueva contraseña y la confirmacion de la nueva contraseña sean iguales
        if (!validarIgualdadPassword(txt_nueva_password, txt_confirmar_nueva_password)) {
            alert_modificar_datos_password.innerHTML = mensaje_alert_fijo("danger", "Los campos nueva contraseña y la confirmacion de nueva contraseña no son iguales");
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('password_nueva', txt_nueva_password.value.trim());
        formData.append('password_nueva_confirmacion', txt_confirmar_nueva_password.value.trim());
        formData.append('tipo_modificacion', tipo_modificacion_password.value.trim());
        fetch(`modificar_datos_usuario.php${window.location.search}`, {
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