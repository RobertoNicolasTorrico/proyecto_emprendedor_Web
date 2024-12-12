<!--Modal para confirmar la eliminacion de la notificacion -->
<div class="modal fade" id="eliminaModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="eliminaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="eliminaModalLabel">Aviso</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!--Body del modal -->
            <div class="modal-body">

                <!--Div para mostrar errores en la eliminacion de la notificacion -->
                <div id="alert_eliminar_modal"></div>
                <p>¿Estas seguro de querer eliminar la siguiente notificacion?</p>

                <!--Div para mostrar detalles sobre la notificacion a eliminar -->
                <div id="label_mensaje_eliminar"></div>
            </div>

            <!--Footer del modal -->
            <div class="modal-footer">

                <!--Formulario dentro del modal-->
                <form method="POST" enctype="multipart/form-data" id="formulario_eliminar">

                    <!--Campos ocultos para almacenar el ID de la notificacion -->
                    <input type="number" name="id_notificacion" id="id_notificacion" hidden required>

                    <!--Boton para cerrar el modal y cancelar la operacion -->
                    <button type="button" class="btn btn-outline-primary " data-bs-dismiss="modal">Cancelar</button>

                    <!--Boton para confirmar la eliminacion -->
                    <button type="submit" class="btn btn-outline-danger">Confirmar</button>

                </form>

            </div>

        </div>
    </div>
</div>

<script>
    var alert_notificacion = document.getElementById("alert_notificacion");

    // Obtener el formulario de eliminación
    var form_eliminar = document.getElementById("formulario_eliminar");

    // Obtener el modal de eliminacion
    const eliminaModal = document.getElementById('eliminaModal');
    const ventanaModalEliminar = new bootstrap.Modal(eliminaModal);

    //Agrega un evento cuando se muestra el modal
    eliminaModal.addEventListener('show.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        form_eliminar.reset();

        //Elimina cualquier alerta previa en el modal
        var alert_eliminar_modal = document.getElementById('alert_eliminar_modal');
        alert_eliminar_modal.innerHTML = '';

        //Elimina cualquier mensaje previa en el modal
        var label_mensaje_eliminar = document.getElementById('label_mensaje_eliminar');
        label_mensaje_eliminar.innerHTML = '';

        //Obtener el boton que abrio el modal
        var button = event.relatedTarget;

        //Obtener atributos del boton
        var id_notificacion = button.getAttribute('data-bs-id_notificacion');
        var mensaje_notificacion = button.getAttribute('data-bs_mensaje');


        //Mostrar la pregunta y la fecha en el modal
        var label_mensaje_eliminar = document.getElementById('label_mensaje_eliminar');
        label_mensaje_eliminar.innerHTML = mensaje_notificacion;


        //Asigna el ID de pregunta al formulario en el modal 
        eliminaModal.querySelector('.modal-footer #id_notificacion').value = id_notificacion;
    });


    //Manejo del envio del formulario para eliminar una notificacion
    form_eliminar.addEventListener("submit", (e) => {

        //Previene el envio por defecto del formulario
        e.preventDefault();
        //Elimina cualquier alerta previa 
        var alert_eliminar_modal = document.getElementById("alert_eliminar_modal");
        alert_eliminar_modal.innerHTML = "";
        var id_notificacion = document.getElementById('id_notificacion');


        //Valida que los campos ocultos solo contengan numeros
        if (!isNaN(id_notificacion)) {
            alert_eliminar_modal.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData(form_eliminar);
        fetch('baja_notificacion.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado == "success") {

                    //Elimina la card seleccionado
                    var div_notificacion = document.getElementById("div_notificaciones-" + id_notificacion.value);
                    div_notificacion.remove();
                    alert_notificacion.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                    //Cierra el modal 
                    ventanaModalEliminar.hide();

                } else {
                    if (datos.estado == "danger") {

                        //Muestra un mensaje de error en el Modal
                        alert_eliminar_modal.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    }
                }
            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_eliminar_modal.innerHTML = mensaje_alert_fijo("danger", e);
            });

    });
</script>