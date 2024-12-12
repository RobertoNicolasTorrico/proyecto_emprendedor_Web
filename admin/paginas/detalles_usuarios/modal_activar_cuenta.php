<!--Modal para confirmar la activacion de la cuenta-->
<div class="modal fade" id="modalActivarCuenta" tabindex="-1" aria-labelledby="modalActivarCuentaLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

    <div class="modal-dialog">
        <div class="modal-content">

            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalActivarCuentaLabel">Aviso</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Formulario dentro del modal -->
            <form method="POST" id="formulario_activar_cuenta">

                <!-- Body del modal -->
                <div class="modal-body">

                    <!-- Div para mostrar errores al activar la cuenta del usuario -->
                    <div id="alert_activar_cuenta"></div>


                    <p>¿Esta seguro de querer activar la cuenta del usuario?</p>
                    <p>En caso que sea un usuario emprendedor todos los productos seguiran en estado finalizado</p>
                </div>


                <!--Footer del modal -->
                <div class="modal-footer">

                    <!-- Botón para cerrar el modal y cancelar la operacion -->
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="boton_cancelar_cuenta_activar">Cancelar</button>

                    <!-- Botón para activar la cuenta -->
                    <button type="submit" class="btn btn-outline-danger" id="boton_activar_cuenta">Confirmar</button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
    // Obtener el formulario de desactivacion
    var form_activar_cuenta = document.getElementById("formulario_activar_cuenta");


    //Manejo del envio del formulario para desactivar la cuenta del usuario
    form_activar_cuenta.addEventListener('submit', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_activar_cuenta = document.getElementById("alert_activar_cuenta");
        alert_activar_cuenta.innerHTML = "";


        var boton_cancelar_cuenta_activar = document.getElementById("boton_cancelar_cuenta_activar");
        var boton_activar_cuenta = document.getElementById("boton_activar_cuenta");
        var campos_cambiar_estados = [boton_cancelar_cuenta_activar, boton_activar_cuenta];


        //Funcion para desactivar los elementos inputs que se utilizan 
        cambiarEstadoInputs(campos_cambiar_estados, true);

        // Envío del formulario usando fetch
        const formData = new FormData();
        fetch(`activar_usuario.php${window.location.search}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convierte la respuesta a JSON
            .then(datos => {
                if (datos.estado === 'success') {

                    //Muestra un mensaje en la interfaz del usuario
                    alert_activar_cuenta.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);


                    //Se actualiza la pagina para actualizar los datos despues que se acabe el tiempo
                    setTimeout(() => {
                        window.location.reload();
                    }, "1000");

                } else {
                    //Muestra un mensaje de error en el Modal ademas de cambiar al estado original los campos inputs
                    alert_activar_cuenta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    cambiarEstadoInputs(campos_cambiar_estados, false);
                }
            })
            .catch(e => {
                // Muestra un mensaje error de la solicitud ademas de cambiar al estado original los campos inputs
                alert_activar_cuenta.innerHTML = mensaje_alert_fijo("danger", e);
                cambiarEstadoInputs(campos_cambiar_estados, false);

            });


    });
</script>