<!--Modal para confirmar la eliminacion de una respuesta -->
<div class="modal fade" id="eliminaModal" tabindex="-1" aria-labelledby="eliminaModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="eliminaModalLabel">Aviso</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!--Body del modal -->
            <div class="modal-body">
                <!--Div para mostrar errores en la eliminacion de la respuesta -->
                <div id="alert_eliminar_modal"></div>

                <p>¿Estas seguro de querer eliminar la siguiente respuesta?</p>

                <!--Div para mostrar el contenido de la respuesta que se va a eliminar -->
                <div id="label_mensaje_eliminar"></div>

            </div>

            <!--Footer del modal -->
            <div class="modal-footer">

                <!--Formulario dentro del modal-->
                <form method="POST" enctype="multipart/form-data" id="formulario_eliminar_respuesta">

                    <!--Campos ocultos para almacenar el ID de pregunta y el ID del producto -->
                    <input type="number" name="id_pregunta" id="id_pregunta" hidden required>
                    <input type="number" name="id_producto" id="id_producto" hidden required>

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
    const eliminaModal = document.getElementById('eliminaModal');

    //Agrega un evento cuando se muestra el modal
    eliminaModal.addEventListener('show.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        form_eliminar_respuesta.reset();

        //Elimina cualquier alerta previa en el modal
        var alert_eliminar_modal = document.getElementById('alert_eliminar_modal');
        alert_eliminar_modal.innerHTML = '';

        //Elimina cualquier mensaje previa en el modal
        var label_mensaje_eliminar = document.getElementById('label_mensaje_eliminar');
        label_mensaje_eliminar.innerHTML = '';


        //Obtener el boton que abrio el modal
        var button = event.relatedTarget;

        //Obtener atributos del boton
      
        var respuesta = button.getAttribute('data-bs-respuesta');

        var fecha = button.getAttribute('data-bs-fecha');
        var id_pregunta = button.getAttribute('data-bs-id_pregunta');
        var id_producto = button.getAttribute('data-bs-id_producto');

        //Mostrar la respuesta y la fecha en el modal
        label_mensaje_eliminar.innerHTML = '<p class="text-break"><strong>Respuesta:</strong>' + respuesta +
            '<span style="color: #8a8a8a;">(' + fecha + ')</span></p>';

        //Asigna el ID de pregunta y el ID de producto al formulario en el modal 
        eliminaModal.querySelector('.modal-footer #id_pregunta').value = id_pregunta;
        eliminaModal.querySelector('.modal-footer #id_producto').value = id_producto;
    });


</script>