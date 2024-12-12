<!--Modal para confirmar la eliminacion de la cuenta del usuario-->
<div class="modal fade" id="modalEliminarCuenta" tabindex="-1" aria-labelledby="modalEliminarCuentaLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

    <div class="modal-dialog">
        <div class="modal-content">

            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalEliminarCuentaLabel">Aviso</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Formulario dentro del modal -->
            <form method="POST" id="formulario_eliminar_cuenta">

                <!-- Body del modal -->
                <div class="modal-body">

                    <!-- Div para mostrar errores al eliminar la cuenta del usuario-->
                    <div id="alert_eliminar_cuenta"></div>

                    <p>¿Estás seguro de querer eliminar permanentemente toda la información de esta cuenta? </p>
                    <p> Si el usuario es un emprendedor, esto incluye sus productos, publicaciones, preguntas hechas a otros productos, respuestas a preguntas de sus productos, y las relaciones de seguimiento. Si es un usuario común, se eliminarán las preguntas realizadas a productos y las relaciones de seguimiento. Esta acción no se puede deshacer.</p>

                 
                </div>

                <!--Footer del modal -->
                <div class="modal-footer">

                    <!-- Botón para cerrar el modal y cancelar la operacion -->
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="boton_cancelar_baja_cuenta">Cancelar</button>

                    <!-- Botón para desactivar la uenta -->
                    <button type="submit" class="btn btn-outline-danger" id="boton_baja_cuenta">Confirmar</button>
                </div>


            </form>

        </div>
    </div>
</div>

<script>
    // Obtener el formulario de eliminacion de la cuenta
    var formulario_eliminar_cuenta = document.getElementById("formulario_eliminar_cuenta");


    //Manejo del envio del formulario para eliminar la cuenta del usuario
    formulario_eliminar_cuenta.addEventListener('submit', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_eliminar_cuenta = document.getElementById("alert_eliminar_cuenta");
        alert_eliminar_cuenta.innerHTML = "";


        event.preventDefault();
        var boton_cancelar_baja_cuenta = document.getElementById("boton_cancelar_baja_cuenta");
        var boton_baja_cuenta = document.getElementById("boton_baja_cuenta");
        var campos_cambiar_estados = [boton_cancelar_baja_cuenta, boton_baja_cuenta];

        //Funcion para desactivar los elementos inputs que se utilizan 
        cambiarEstadoInputs(campos_cambiar_estados, true);


        // Envío del formulario usando fetch
        const formData = new FormData();


        fetch(`baja_usuario.php${window.location.search}`, { // Convierte la respuesta a JSON
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {

                if (datos.estado === 'success') {

                    //Muestra un mensaje en la interfaz del usuario
                    alert_eliminar_cuenta.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);


                    //Se redirige al usuario a la pagina de inicio despues que se acabe el tiempo
                    setTimeout(() => {
                        window.location.href = "../../index.php";
                    }, "1000");

                } else {
                    // Muestra un mensaje error de la solicitud ademas de cambiar al estado original los campos inputs
                    alert_eliminar_cuenta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    cambiarEstadoInputs(campos_cambiar_estados, false);

                }
            })
            .catch(e => {
                // Muestra un mensaje error de la solicitud ademas de cambiar al estado original los campos inputs
                alert_eliminar_cuenta.innerHTML = mensaje_alert_fijo("danger", e);
                cambiarEstadoInputs(campos_cambiar_estados, false);

            });


    });
</script>