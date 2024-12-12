<!--Modal para confirmar la eliminacion del seguidor del emprendedor -->
<div class="modal fade" id="eliminarSeguidor" tabindex="-1" aria-labelledby="eliminarSeguidorLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="eliminarSeguidorLabel">Aviso</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!--Body del modal -->
            <div class="modal-body">

                <!--Div para mostrar errores en la eliminacion del seguidor -->
                <div id="alert_eliminar_modal_seguidor"></div>
                <p>¿Estas seguro de querer eliminar al siguiente seguidor?</p>

                <!--Div para mostrar el nombre del seguidor que se va a eliminar de los seguidores de emprendedor -->
                <div id="label_mensaje_eliminar_seguidor"></div>
            </div>

            <!--Footer del modal -->
            <div class="modal-footer">

                <!--Formulario dentro del modal-->
                <form method="POST" enctype="multipart/form-data" id="formulario_eliminar_seguidor">

                    <!--Campos ocultos para almacenar el ID del usuario -->
                    <input type="number" name="id_usuario_eliminar_seguidor" id="id_usuario_eliminar_seguidor" hidden required>


                    <!--Boton para cerrar el modal y cancelar la operacion -->
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>

                    <!--Boton para confirmar la eliminacion -->
                    <button type="submit" class="btn btn-outline-danger">Confirmar</button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // Obtener el formulario de eliminación
    var form_eliminar_seguidor = document.getElementById("formulario_eliminar_seguidor");

    // Obtener el modal de eliminacion
    const eliminarSeguidorModal = document.getElementById('eliminarSeguidor');
    const ventanaModalEliminarSeguidor = new bootstrap.Modal(eliminarSeguidorModal);


    //Manejo del envio del formulario para eliminiar al seguidor
    form_eliminar_seguidor.addEventListener("submit", (e) => {

        var alert_notificacion_seguidor = document.getElementById("alert_notificacion_seguidor");


        //Previene el envio por defecto del formulario
        e.preventDefault();


        //Elimina cualquier alerta previa 
        var alert_eliminar_modal_seguidor = document.getElementById("alert_eliminar_modal_seguidor");
        alert_eliminar_modal_seguidor.innerHTML = "";


        var id_usuario_eliminar_seguidor = document.getElementById('id_usuario_eliminar_seguidor');


        //Valida que los campos ocultos solo contengan numeros
        if (!isNaN(id_usuario_eliminar_seguidor)) {
            alert_eliminar_modal_seguidor.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData(form_eliminar_seguidor);
        fetch(`seguidores_seguidos/baja_seguidor.php${window.location.search}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado == "success") {

                    //En caso que la cantidad de los seguidores que se ve en la interfaz sea igual 1 y la pagina actual es mayor a 1 
                    //Se va restar uno a la pagina actual debido a la eliminacion de la pregunta
                    if (cant_actual_registros_seguidor == 1 && pagina_actual_seguidor > 1) {
                        pagina_actual_seguidor = pagina_actual_seguidor - 1;
                    }
                    // Actualiza los datos de los seguidores
                    getDataTodasMisSeguidores();

                    //Muetra un mensaje en la interfaz del usuario
                    alert_notificacion_seguidor.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                    //Cierra el modal 
                    ventanaModalEliminarSeguidor.hide();


                } else {
                    //Muestra un mensaje de error en el Modal
                    alert_eliminar_modal_seguidor.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }
            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_eliminar_modal_seguidor.innerHTML = mensaje_alert_fijo("danger", e);
            });
    });


    //Agrega un evento cuando se abre el modal
    eliminarSeguidorModal.addEventListener('show.bs.modal', event => {

        //Obtener el boton que abrio el modal
        var button = event.relatedTarget;

        //Obtener atributos del boton
        var nombre_usuario = button.getAttribute('data-bs-nombre_usuario');
        var id_seguidor = button.getAttribute('data-bs-id_seguidor');

        //Mostrar el nombre del usuario
        var label_mensaje_eliminar_seguidor = document.getElementById('label_mensaje_eliminar_seguidor');
        label_mensaje_eliminar_seguidor.innerHTML = '<p class="text-break"><strong>Nombre de usuario:</strong>' + nombre_usuario + '</p>';

        //Asigna el ID del usuario que va dejar de seguir al usuario en el formulario del modal 
        document.getElementById("id_usuario_eliminar_seguidor").value = id_seguidor;

    });




    //Agrega un evento cuando se cierra el modal
    eliminarSeguidorModal.addEventListener('hide.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        form_eliminar_seguidor.reset();

        //Elimina cualquier alerta previa en el modal
        var alert_eliminar_modal_seguidor = document.getElementById('alert_eliminar_modal_seguidor');
        alert_eliminar_modal_seguidor.innerHTML = '';

        //Elimina cualquier mensaje previa en el modal
        var label_mensaje_eliminar_seguidor = document.getElementById('label_mensaje_eliminar_seguidor');
        label_mensaje_eliminar_seguidor.innerHTML = '';

    });
</script>