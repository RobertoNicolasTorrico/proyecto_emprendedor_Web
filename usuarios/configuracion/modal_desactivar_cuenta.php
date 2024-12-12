<!--Modal para confirmar la desactivacion de la cuenta-->
<div class="modal fade" id="modalDesactivarCuenta" tabindex="-1" aria-labelledby="modalDesactivarCuentaLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">

            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalDesactivarCuentaLabel">Aviso</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>


            <!-- Formulario dentro del modal -->
            <form method="POST" id="formulario_desactivar_cuenta">

                <!-- Body del modal -->
                <div class="modal-body">

                    <!-- Div para mostrar errores al desactivar la cuenta -->
                    <div id="alert_desactivar_cuenta"></div>

                    <p>¿Estás seguro de querer desactivar su cuenta? Tenga en cuenta que si es un usuario emprendedor, todos sus productos pasarán al estado finalizado y no se veran sus publicaciones.</p>
                    <p>Para volver a reactivar su cuenta, vaya a la página de Activar Cuenta y siga los pasos que aparecen para volver a reactivar su cuenta</p>


                    <!-- Div que contiene el campo contraseña y un boton para mostrar o ocultar el contenido del campo contraseña -->
                    <div class="form-floating mb-3">

                        <!-- Campo para confirmar la contrasñea del usuario -->
                        <input type="password" class="form-control" name="txt_password_desactivar" id="txt_password_desactivar" placeholder="Confirmar contraseña" minlength="6" maxlength="60" required>
                        <label for="txt_password_desactivar">Contraseña</label>

                        <!-- Div que contiene el boton para ocultar y mostrar contraseña -->
                        <div class=" form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="check_password_desactivar_cuenta">
                            <label for="check_password_desactivar_cuenta" class="form-check-label">
                                Mostrar contraseña
                            </label>
                        </div>

                    </div>
                </div>

                <!--Footer del modal -->
                <div class="modal-footer">

                    <!-- Botón para cerrar el modal y cancelar la operacion -->
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="boton_cancelar_cuenta">Cancelar</button>

                    <!-- Botón para desactivar la uenta -->
                    <button type="submit" class="btn btn-outline-danger" id="boton_desactivar_cuenta">Confirmar</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    // Obtener el formulario de desactivacion
    var form_desactivar_cuenta = document.getElementById("formulario_desactivar_cuenta");

    // Obtener el modal de deasctivar la cuenta del modal
    const modalDesactivarCuenta = document.getElementById('modalDesactivarCuenta');
    const ventanaModalEliminar = new bootstrap.Modal(modalDesactivarCuenta);


    var txt_password_desactivar = document.getElementById("txt_password_desactivar");
    var check_password_desactivar_cuenta = document.getElementById('check_password_desactivar_cuenta');


    //Agrega un evento cuando se cierra el modal
    modalDesactivarCuenta.addEventListener('hidden.bs.modal', event => {

        //Elimina cualquier alerta previa en el modal
        var alert_desactivar_cuenta = document.getElementById('alert_desactivar_cuenta');
        alert_desactivar_cuenta.innerHTML = '';

        //Restable el checkbox para ocultar la contraseña
        txt_password_desactivar.type = "password";

        //Resetea el formulario para limpiar los campos
        form_desactivar_cuenta.reset();

    });


    //Agrega un evento cada vez que se haga un click en el checkbox cambiando el estado de la visibilidad de la contraseña
    check_password_desactivar_cuenta.addEventListener('click', function() {
        mostrar_ocultar_password(check_password_desactivar_cuenta, txt_password_desactivar);
    });


    //Manejo del envio del formulario para desactivar la cuenta del usuario
    form_desactivar_cuenta.addEventListener('submit', function(event) {


        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_desactivar_cuenta = document.getElementById('alert_desactivar_cuenta');
        alert_desactivar_cuenta.innerHTML = "";


        var boton_cancelar_cuenta = document.getElementById("boton_cancelar_cuenta");
        var boton_desactivar_cuenta = document.getElementById("boton_desactivar_cuenta");
        var txt_password_desactivar = document.getElementById("txt_password_desactivar");


        var campos_cambiar_estados = [boton_cancelar_cuenta, boton_desactivar_cuenta, txt_password_desactivar];

        //Valida que los campos no esten vacios
        if (validarCampoVacio([txt_password_desactivar])) {
            alert_desactivar_cuenta.innerHTML = mensaje_alert_fijo("danger", "Por favor complete el campo contraseña");
            return false;
        }

        //Valida que el campo password no tenga espacios en blanco
        if (tieneEspacioEnBlacoPassword(txt_password_desactivar)) {
            alert_desactivar_cuenta.innerHTML = mensaje_alert_fijo("danger", "La contraseña no puede tener espacios en blanco");
            return false;
        }

        //Funcion para desactivar los elementos inputs que se utilizan 
        cambiarEstadoInputs(campos_cambiar_estados, true);

        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('password', txt_password_desactivar.value.trim());

        fetch(`desactivar_usuario.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convierte la respuesta a JSON
            .then(datos => {

                if (datos.estado === 'success') {
                    //Muestra un mensaje en la interfaz del usuario
                    alert_desactivar_cuenta.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                    //Se obtiene la direccion para redireccionar al usuario a la pagina de iniciar sesion
                    var url_base = <?php echo json_encode($url_base); ?>;
                    url_base = url_base + "/paginas/iniciar_sesion/pagina_iniciar_sesion.php";

                    //Se redirige al usuario a la pagina de iniciar sesion despues que se acabe el tiempo
                    setTimeout(() => {
                        window.location.href = url_base;
                    }, "1000");

                } else {
                    //Muestra un mensaje de error en el Modal ademas de cambiar al estado original los campos inputs
                    alert_desactivar_cuenta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    cambiarEstadoInputs(campos_cambiar_estados, false);

                }

            })
            .catch(e => {
                // Muestra un mensaje error de la solicitud ademas de cambiar al estado original los campos inputs
                alert_desactivar_cuenta.innerHTML = mensaje_alert_fijo("danger", e);
                cambiarEstadoInputs(campos_cambiar_estados, false);

            });


    });
</script>