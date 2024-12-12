<!--Modal para confirmar la eliminacion del seguidor del emprendedor -->

<div class="modal fade" id="eliminarSeguidorEmprendedor" tabindex="-1" aria-labelledby="eliminarSeguidorEmprendedorLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">


            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="eliminarSeguidorEmprendedorLabel">Aviso</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!--Body del modal -->
            <div class="modal-body">

                <!--Div para mostrar errores en la eliminacion del seguidor -->
                <div id="alert_eliminar_modal_seguidor_emprendedor"></div>
                <p>¿Estas seguro de querer hacer que el usuario deje de seguir al siguiente usuario emprendedor?</p>


                <!--Div para mostrar el nombre del usuario que se va a eliminar de los seguidores de emprendedor -->
                <div id="label_mensaje_eliminar_seguidor_emprendedor"></div>
            </div>

            <!--Footer del modal -->
            <div class="modal-footer">

                <!--Formulario dentro del modal-->
                <form method="POST" enctype="multipart/form-data" id="formulario_eliminar_seguidor_emprendedor">

                    <!--Campos ocultos para almacenar el ID del usuario del emprendedor -->
                    <input type="number" name="id_usuario_eliminar_seguidor_emprendedor" id="id_usuario_eliminar_seguidor_emprendedor" hidden required>


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
    form_eliminar_seguidor_emprendedor = document.getElementById("formulario_eliminar_seguidor_emprendedor");

    // Obtener el modal de eliminacion
    const eliminarSeguidorEmprendedor = document.getElementById('eliminarSeguidorEmprendedor');
    const ventanaModaleliminarSeguidorEmprendedor = new bootstrap.Modal(eliminarSeguidorEmprendedor);


    //Manejo del envio del formulario para dejar de seguir al usuario emprendedor
    form_eliminar_seguidor_emprendedor.addEventListener("submit", (e) => {

        var alert_notificacion_seguidos = document.getElementById("alert_notificacion_seguidos");

        //Previene el envio por defecto del formulario
        e.preventDefault();


        //Elimina cualquier alerta previa 
        var alert_eliminar_modal_seguidor_emprendedor = document.getElementById("alert_eliminar_modal_seguidor_emprendedor");
        alert_eliminar_modal_seguidor_emprendedor.innerHTML = "";


        var id_usuario_eliminar_seguidor_emprendedor = document.getElementById('id_usuario_eliminar_seguidor_emprendedor');

        //Valida que los campos ocultos solo contengan 
        if (!isNaN(id_usuario_eliminar_seguidor_emprendedor)) {
            alert_eliminar_modal_seguidor_emprendedor.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
            return false;
        }

        // Envío del formulario usando fetch
        const formData = new FormData(form_eliminar_seguidor_emprendedor);
        fetch(`seguidores_seguidos/baja_seguir_usuario_emprendedor.php${window.location.search}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado == "success") {



                    //En caso que la cantidad de emprendedores que se ve en la interfaz sea igual 1 y la pagina actual es mayor a 1 
                    //Se va restar uno a la pagina actual debido a la eliminacion del seguimiento del emprendedor
                    if (cant_actual_seguidos == 1 && pagina_actual_seguidos > 1) {
                        pagina_actual_seguidos = pagina_actual_seguidos - 1;
                    }


                    // Actualiza los datos de los emprendedores
                    getDataTodosEmprendedorSiguiendo();

                    //Muetra un mensaje en la interfaz del usuario
                    alert_notificacion_seguidos.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                    //Cierra el modal 
                    ventanaModaleliminarSeguidorEmprendedor.hide();

                } else {

                    //Muestra un mensaje de error en el Modal
                    alert_eliminar_modal_seguidor_emprendedor.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }
            }).catch(e => {
                // Muestra un mensaje error en el Modal de la solicitud
                alert_eliminar_modal_seguidor_emprendedor.innerHTML = mensaje_alert_fijo("danger", e);


            });
    });


    //Agrega un evento cuando se abre el modal
    eliminarSeguidorEmprendedor.addEventListener('show.bs.modal', event => {

        //Obtener el boton que abrio el modal
        var button = event.relatedTarget;

        //Obtener atributos del boton
        var nombre_emprendedor = button.getAttribute('data-bs-nombre_emprendedor');
        var id_usuario_emprendedor = button.getAttribute('data-bs-id_usuario_emprendedor');


        //Mostrar el nombre del usuario
        var label_mensaje_eliminar_seguidor_emprendedor = document.getElementById('label_mensaje_eliminar_seguidor_emprendedor');
        label_mensaje_eliminar_seguidor_emprendedor.innerHTML = '<p class="text-break"><strong>Nombre del emprendedor:</strong>' + nombre_emprendedor + '</p>';

        //Asigna el ID del usuario emprendedor que va a dejar de seguir al emprendedor en el formulario del modal 

        eliminarSeguidorEmprendedor.querySelector('.modal-footer #id_usuario_eliminar_seguidor_emprendedor').value = id_usuario_emprendedor;

    });


    //Agrega un evento cuando se cierra el modal
    eliminarSeguidorEmprendedor.addEventListener('hide.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        form_eliminar_seguidor_emprendedor.reset();

        //Elimina cualquier alerta previa en el modal


        var alert_eliminar_modal_seguidor_emprendedor = document.getElementById("alert_eliminar_modal_seguidor_emprendedor");
        alert_eliminar_modal_seguidor_emprendedor.innerHTML = "";


        //Elimina cualquier mensaje previa en el modal
        var label_mensaje_eliminar_seguidor_emprendedor = document.getElementById('label_mensaje_eliminar_seguidor_emprendedor');
        label_mensaje_eliminar_seguidor_emprendedor.innerHTML = '';

    });
</script>