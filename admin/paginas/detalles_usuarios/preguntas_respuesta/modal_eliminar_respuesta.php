<!--Modal para confirmar la eliminacion de la respuesta -->
<div class="modal fade" id="eliminarModalPreguntasRecibidas" tabindex="-1" aria-labelledby="eliminarModalPreguntasRecibidasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="eliminarModalPreguntasRecibidasLabel">Aviso</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!--Body del modal -->
            <div class="modal-body">

                <!--Div para mostrar errores en la eliminacion de la respuesta -->
                <div id="alert_eliminar_modal_respuesta"></div>
                <p>¿Estas seguro de querer eliminar la siguiente respuesta?</p>

                <!--Div para mostrar el contenido de la respuesta que se va a eliminar -->
                <div id="label_mensaje_eliminar_respuesta"></div>
            </div>

            <!--Footer del modal -->
            <div class="modal-footer">

                <!--Formulario dentro del modal-->
                <form method="POST" enctype="multipart/form-data" id="formulario_eliminar_respuesta">

                    <!--Campos ocultos para almacenar el ID de pregunta y el ID del producto -->
                    <input type="number" name="id_pregunta_recibida" id="id_pregunta_recibida" hidden required>
                    <input type="number" name="id_pregunta_producto" id="id_pregunta_producto" hidden required>

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
    var form_eliminar_respuesta = document.getElementById("formulario_eliminar_respuesta");

    // Obtener el modal de eliminacion
    const eliminarModalPreguntasRecibidas = document.getElementById('eliminarModalPreguntasRecibidas');


    //Agrega un evento cuando se muestra el modal
    eliminarModalPreguntasRecibidas.addEventListener('show.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        form_eliminar_respuesta.reset();

        //Elimina cualquier alerta previa en el modal
        var alert_eliminar_modal_respuesta = document.getElementById('alert_eliminar_modal_respuesta');
        alert_eliminar_modal_respuesta.innerHTML = '';

        //Elimina cualquier mensaje previa en el modal
        var label_mensaje_eliminar_respuesta = document.getElementById('label_mensaje_eliminar_respuesta');
        label_mensaje_eliminar_respuesta.innerHTML = '';


        //Obtener el boton que abrio el modal
        var button = event.relatedTarget;

        //Obtener atributos del boton
        var id_pregunta_recibida = button.getAttribute('data-bs-id_pregunta_recibida');
        var id_pregunta_producto = button.getAttribute('data-bs-id_pregunta_producto');
        var respuesta = button.getAttribute('data-bs-respuesta');
        var fecha = button.getAttribute('data-bs-fecha');

        //Mostrar la respuesta y la fecha en el modal
        label_mensaje_eliminar_respuesta.innerHTML = '<p class="text-break"><strong>Respuesta:</strong>' + respuesta +
            '<span style="color: #8a8a8a;">(' + fecha + ')</span></p>';

        //Asigna el ID de pregunta y el ID de producto al formulario en el modal 

        eliminarModalPreguntasRecibidas.querySelector('.modal-footer #id_pregunta_producto').value = id_pregunta_producto;
        eliminarModalPreguntasRecibidas.querySelector('.modal-footer #id_pregunta_recibida').value = id_pregunta_recibida;



    });
</script>