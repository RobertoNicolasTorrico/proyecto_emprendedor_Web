<!--Modal para confirmar la eliminacion de la publicacion -->
<div class="modal fade" id="modal_eliminar_publicacion" tabindex="-1" aria-labelledby="modal_eliminar_publicacionLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal_eliminar_publicacionLabel">Aviso</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!--Body del modal -->
            <div class="modal-body">
                <!--Div para mostrar errores en la eliminacion de la publicacion -->
                <div id="alert_eliminar_modal"></div>
                <p>¿Estas seguro de querer eliminar la siguiente publicacion?</p>

                <!--Div para mostrar la descripcion de la publicacion que se va a eliminar -->
                <div id="label_mensaje_eliminar"></div>
            </div>

            <!--Footer del modal -->
            <div class="modal-footer">

                <!--Formulario dentro del modal-->
                <form method="POST" enctype="multipart/form-data" id="formulario_eliminar_publicacion">

                    <!--Campos ocultos para almacenar el ID de publicacion -->
                    <input type="number" name="id_publicacion_eliminar" id="id_publicacion_eliminar" hidden required>

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
    // Obtener el formulario de eliminación
    var formulario_eliminar_publicacion = document.getElementById("formulario_eliminar_publicacion");

    // Obtener el modal de eliminacion
    const eliminaPublicacionModal = document.getElementById('modal_eliminar_publicacion');
    const ventanaModalEliminar = new bootstrap.Modal(eliminaPublicacionModal);

    //Agrega un evento cuando se muestra el modal
    eliminaPublicacionModal.addEventListener('show.bs.modal', event => {

        //Obtener el boton que abrio el modal
        var button = event.relatedTarget;

        //Obtener atributos del boton
        var descripcion_bd = button.getAttribute('data-bs-descripcion');
        var fecha = button.getAttribute('data-bs-fecha');
        var id_publicacion = button.getAttribute('data-bs-id_publicacion');


        //Mostrar la descripcion de la publicacion y la fecha en el modal
        var label_mensaje_eliminar = document.getElementById('label_mensaje_eliminar');
        label_mensaje_eliminar.innerHTML = '<p class="text-break"><strong>Descripcion:</strong>' + descripcion_bd +
            '<span style="color: #8a8a8a;">(' + fecha + ')</span></p>';

        //Asigna el ID de publicacion en el formulario del modal 
        document.getElementById("id_publicacion_eliminar").value = id_publicacion;
    });


    //Agrega un evento cuando se cierra el modal
    eliminaPublicacionModal.addEventListener('hide.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        formulario_eliminar_publicacion.reset();

        //Elimina cualquier alerta previa en el modal
        var alert_eliminar_modal = document.getElementById('alert_eliminar_modal');
        alert_eliminar_modal.innerHTML = '';

        //Elimina cualquier mensaje previa en el modal
        var label_mensaje_eliminar = document.getElementById('label_mensaje_eliminar');
        label_mensaje_eliminar.innerHTML = '';

    });


    //Manejo del envio del formulario para eliminar una publicacion
    formulario_eliminar_publicacion.addEventListener("submit", (e) => {

        //Previene el envio por defecto del formulario
        e.preventDefault();

        //Elimina cualquier alerta previa 
        alert_eliminar_modal = document.getElementById("alert_eliminar_modal");
        alert_eliminar_modal.innerHTML = "";

        var id_publicacion_eliminar = document.getElementById('id_publicacion_eliminar');

        //Valida que los campos ocultos solo contengan numeros
        if (!isNaN(id_publicacion_eliminar)) {
            alert_eliminar_modal.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
            return false;
        }

        // Envío del formulario usando fetch
        const formData = new FormData(formulario_eliminar_publicacion);
        fetch('baja_publicacion.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado == "success") {

                    //En caso que la cantidad de publicaciones que se ve en la interfaz sea igual 1 y la pagina actual es mayor a 1 
                    //Se va restar uno a la pagina actual debido a la eliminacion de la publicacion
                    if (cant_actual_registros == 1 && pagina_actual > 1) {
                        pagina_actual = pagina_actual - 1;
                    }
                    // Actualiza los datos de las preguntas
                    getDataPublicacion();

                    //Muetra un mensaje en la interfaz del usuario
                    alert_notificacion_informacion.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                    //Cierra el modal 
                    ventanaModalEliminar.hide();

                } else {

                    //Muestra un mensaje de error en el Modal
                    alert_eliminar_modal.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);

                }
            }).catch(e => {

                // Muestra un mensaje error de la solicitud
                alert_eliminar_modal.innerHTML = mensaje_alert_fijo("danger", e);
            });
    });
</script>