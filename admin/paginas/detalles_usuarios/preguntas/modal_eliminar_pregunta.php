<!--Modal para confirmar la eliminacion de la pregunta -->
<div class="modal fade" id="eliminaModalPregunta" tabindex="-1" aria-labelledby="eliminaModalPreguntaLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="eliminaModalPreguntaLabel">Aviso</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!--Body del modal -->
            <div class="modal-body">

                <!--Div para mostrar errores en la eliminacion de la pregunta -->
                <div id="alert_eliminar_modal_pregunta"></div>
                <p>¿Estas seguro de querer eliminar la siguiente pregunta?</p>

                <!--Div para mostrar el contenido de la pregunta que se va a eliminar -->
                <div id="label_mensaje_eliminar_pregunta"></div>
            </div>

            <!--Footer del modal -->
            <div class="modal-footer">
                <!--Formulario dentro del modal-->
                <form method="POST" enctype="multipart/form-data" id="formulario_eliminar_mi_pregunta">

                    <!--Campos ocultos para almacenar el ID de pregunta -->
                    <input type="number" name="id_pregunta" id="id_pregunta" hidden required>

                    <!--Boton para cerrar el modal y cancelar la operacion -->
                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>

                    <!--Boton para confirmar la eliminacion -->
                    <button type="submit" class="btn btn-outline-danger">Confirmar</button>

                </form>

            </div>

        </div>
    </div>
</div>


<script>
    // Obtener el formulario de eliminación
    var form_eliminar_mi_pregunta = document.getElementById("formulario_eliminar_mi_pregunta");

    // Obtener el modal de eliminacion
    const eliminaModalPregunta = document.getElementById('eliminaModalPregunta');
    const ventanaModalEliminarMiPregunta = new bootstrap.Modal(eliminaModalPregunta);


    //Agrega un evento cuando se muestra el modal
    eliminaModalPregunta.addEventListener('show.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        form_eliminar_mi_pregunta.reset();

        //Elimina cualquier alerta previa en el modal
        var alert_eliminar_modal_pregunta = document.getElementById('alert_eliminar_modal_pregunta');
        alert_eliminar_modal_pregunta.innerHTML = '';

        //Elimina cualquier mensaje previa en el modal
        var label_mensaje_eliminar_pregunta = document.getElementById('label_mensaje_eliminar_pregunta');
        label_mensaje_eliminar_pregunta.innerHTML = '';


        //Obtener el boton que abrio el modal
        var button = event.relatedTarget;

        //Obtener atributos del boton
        var fecha = button.getAttribute('data-bs-fecha');
        var pregunta = button.getAttribute('data-bs-pregunta');
        var id_pregunta = button.getAttribute('data-bs-id_pregunta');


        //Mostrar la pregunta y la fecha en el modal
        label_mensaje_eliminar_pregunta.innerHTML = '<p class="text-break"><strong>Pregunta:</strong>' + pregunta +
            '<span style="color: #8a8a8a;">(' + fecha + ')</span></p>';


        //Asigna el ID de pregunta al formulario en el modal 
        eliminaModalPregunta.querySelector('.modal-footer #id_pregunta').value = id_pregunta;

    });


    //Manejo del envio del formulario para eliminar una pregunta
    eliminaModalPregunta.addEventListener("submit", (e) => {

        //Previene el envio por defecto del formulario
        e.preventDefault();
        //Elimina cualquier alerta previa 
        var alert_eliminar_modal_pregunta = document.getElementById("alert_eliminar_modal_pregunta");
        alert_eliminar_modal_pregunta.innerHTML = "";

        var id_pregunta = document.getElementById('id_pregunta');

        //Valida que el campo oculto solo contenga numeros
        if (!isNaN(id_pregunta)) {
            alert_eliminar_modal_pregunta.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
            return false;
        }

        // Envío del formulario usando fetch
        const formData = new FormData(form_eliminar_mi_pregunta);
        fetch(`preguntas/baja_pregunta.php${window.location.search}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado == "success") {

                    //En caso que la cantidad de preguntas que se ve en la interfaz sea igual 1 y la pagina actual es mayor a 1 
                    //Se va restar uno a la pagina actual debido a la eliminacion de la pregunta
                    if (cant_actual_registros_preguntas == 1 && pagina_actual_mis_preguntas > 1) {
                        pagina_actual_mis_preguntas = pagina_actual_mis_preguntas - 1;
                    }

                    // Actualiza los datos de las preguntas
                    getDataMisPreguntas();

                    //Muetra un mensaje en la interfaz del usuario
                    alert_notificacion_mis_perguntas.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                    //Cierra el modal 
                    ventanaModalEliminarMiPregunta.hide();

                } else {
                    if (datos.estado == "danger") {
                        //Muestra un mensaje de error en el Modal
                        alert_eliminar_modal_pregunta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    }
                }
            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_eliminar_modal_pregunta.innerHTML = mensaje_alert_fijo("danger", e);
            });
    });
</script>