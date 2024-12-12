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

                    <p>¿Esta seguro de querer desactivar la cuenta del usuario?</p>
                    <p>En caso que sea un usuario emprendedor todos los productos pasaran a un estado finalizado</p>
                </div>

                <!--Footer del modal -->
                <div class="modal-footer">

                    <!-- Botón para cerrar el modal y cancelar la operacion -->
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="boton_cancelar_cuenta">Cancelar</button>

                    <!-- Botón para desactivar la cuenta -->
                    <button type="submit" class="btn btn-outline-danger" id="boton_desactivar_cuenta">Confirmar</button>
                </div>

            </form>

        </div>
    </div>
</div>



<script>
    // Obtener el formulario de desactivacion
    var form_desactivar_cuenta = document.getElementById("formulario_desactivar_cuenta");

    //Manejo del envio del formulario para desactivar la cuenta del usuario
    form_desactivar_cuenta.addEventListener('submit', function(event) {


        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_desactivar_cuenta = document.getElementById("alert_desactivar_cuenta");
        alert_desactivar_cuenta.innerHTML = "";

        var boton_cancelar_cuenta = document.getElementById("boton_cancelar_cuenta");
        var boton_desactivar_cuenta = document.getElementById("boton_desactivar_cuenta");
        var campos_cambiar_estados = [boton_cancelar_cuenta, boton_desactivar_cuenta];


        //Funcion para desactivar los elementos inputs que se utilizan 
        cambiarEstadoInputs(campos_cambiar_estados, true);

        // Envío del formulario usando fetch
        const formData = new FormData();
        fetch(`desactivar_usuario.php${window.location.search}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convierte la respuesta a JSON
            .then(datos => {
                if (datos.estado === 'success') {
                    //Muestra un mensaje en la interfaz del usuario
                    alert_desactivar_cuenta.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                    //Se actualiza la pagina para actualizar los datos despues que se acabe el tiempo
                    setTimeout(() => {
                        window.location.reload();
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