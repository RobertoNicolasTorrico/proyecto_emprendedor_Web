<!--Modal para confirmar la eliminacion del seguidor -->
<div class="modal fade" id="eliminarSeguidor" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="eliminarSeguidorLabel" aria-hidden="true">

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

                <!--Div para mostrar el nombre del usuario que se va a eliminar-->
                <div id="label_mensaje_eliminar"></div>
            </div>


            <!--Footer del modal -->
            <div class="modal-footer">

                <!--Formulario dentro del modal-->
                <form method="POST" enctype="multipart/form-data" id="formulario_eliminar_seguidor">

                    <!--Campos ocultos para almacenar el ID del usuario -->
                    <input type="number" name="id_usuario_eliminar" id="id_usuario_eliminar" hidden required>

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
    form_eliminar_seguidor = document.getElementById("formulario_eliminar_seguidor");

    // Obtener el modal de eliminacion
    const eliminarSeguidorModal = document.getElementById('eliminarSeguidor');
    const ventanaModalEliminarSeguidor = new bootstrap.Modal(eliminarSeguidorModal);


    //Agrega un evento cuando se muestra el modal
    eliminarSeguidorModal.addEventListener('show.bs.modal', event => {

        //Obtener el boton que abrio el modal
        var button = event.relatedTarget;

        //Obtener atributos del boton
        var nombre_usuario = button.getAttribute('data-bs-nombre_usuario');
        var id_seguidor = button.getAttribute('data-bs-id_seguidor');


        //Mostrar el nombre del usuario
        var label_mensaje_eliminar = document.getElementById('label_mensaje_eliminar');
        label_mensaje_eliminar.innerHTML = '<p class="text-break"><strong>Nombre de usuario:</strong>' + nombre_usuario + '</p>';

        //Asigna el ID del usuario en el formulario del modal 
        document.getElementById("id_usuario_eliminar").value = id_seguidor;

    });


    //Agrega un evento cuando se cierra el modal
    eliminarSeguidorModal.addEventListener('hide.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        form_eliminar_seguidor.reset();

        //Elimina cualquier alerta previa en el modal
        var alert_eliminar_modal_seguidor = document.getElementById('alert_eliminar_modal_seguidor');
        alert_eliminar_modal_seguidor.innerHTML = '';

        //Elimina cualquier mensaje previa en el modal
        var label_mensaje_eliminar = document.getElementById('label_mensaje_eliminar');
        label_mensaje_eliminar.innerHTML = '';

    });



    //Manejo del envio del formulario para eliminar al seguidor
    form_eliminar_seguidor.addEventListener("submit", (e) => {

        //Previene el envio por defecto del formulario
        e.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_eliminar_modal_seguidor = document.getElementById("alert_eliminar_modal_seguidor");
        alert_eliminar_modal_seguidor.innerHTML = "";

        var id_usuario_eliminar = document.getElementById('id_usuario_eliminar');

        //Valida que los campos ocultos solo contengan numeros
        if (!isNaN(id_usuario_eliminar)) {
            alert_eliminar_modal_seguidor.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData(form_eliminar_seguidor);
        fetch('baja_seguidor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado == "success") {

                    //Verifica si hubo una busqueda por parte del usuario
                    if (busqueda_activa_seguidores) {
                        //En caso que la busqueda esta activada se va a restar un seguidor de cantidad de la lista de los seguidores actuales
                        var cant_resultados_seguidores = document.getElementById("cant_resultados_seguidores");
                        cant_total_seguidores = cant_total_seguidores - 1;
                        cant_resultados_seguidores.innerHTML = cant_total_seguidores;
                    } else {
                        //En caso que la busqueda esta desactivada se va obtener el numero de seguidores actuales del usuario
                        var cant_actual_seguidores = document.getElementById("cant_actual_seguidores");
                        cant_actual_seguidores.innerHTML = datos.num_seguidores;

                    }

                    //Se elimina el contenedor que tiene el seguidor
                    var div_seguido = document.getElementById('div_seguidor_' + id_usuario_eliminar.value);
                    div_seguido.remove();

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
</script>