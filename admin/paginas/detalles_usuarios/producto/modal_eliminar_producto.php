<!--Modal para confirmar la eliminacion de la publicacion -->
<div class="modal fade" id="ModaleliminarProducto" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ModaleliminarProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">


            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="ModaleliminarProductoLabel">Aviso</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!--Body del modal -->
            <div class="modal-body">

                <!--Div para mostrar errores en la eliminacion de la publicacion -->
                <div id="alert_eliminar_modal_producto"></div>
                <p>¿Estas seguro de querer eliminar la siguiente publicacion del producto?</p>


                <!--Div para mostrar el nombre del producto que se va a eliminar -->
                <div id="label_mensaje_eliminar_producto"></div>

            </div>

            <!--Footer del modal -->
            <div class="modal-footer">

                <!--Formulario dentro del modal-->
                <form method="POST" enctype="multipart/form-data" id="formulario_eliminar_producto">

                    <!--Campos ocultos para almacenar el ID de publicacion -->
                    <input type="number" name="id_producto_eliminar" id="id_producto_eliminar" hidden required>

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
    var form_eliminar_producto = document.getElementById("formulario_eliminar_producto");

    // Obtener el modal de eliminacion
    const eliminaProductoModal = document.getElementById('ModaleliminarProducto');
    const ventanaModalEliminarProducto = new bootstrap.Modal(eliminaProductoModal);

    //Agrega un evento cuando se muestra el modal
    eliminaProductoModal.addEventListener('show.bs.modal', event => {

        //Obtener el boton que abrio el modal
        var button = event.relatedTarget;

        //Obtener atributos del boton
        var nombre_producto = button.getAttribute('data-bs-nombre_producto');
        var id_producto = button.getAttribute('data-bs-id_producto');


        //Mostrar el nombre del producto
        var label_mensaje_eliminar_producto = document.getElementById('label_mensaje_eliminar_producto');
        label_mensaje_eliminar_producto.innerHTML = '<p class="text-break"><strong>Nombre del producto:</strong>' + nombre_producto + '</p>';

        //Asigna el ID de publicacion en el formulario del modal 
        document.getElementById("id_producto_eliminar").value = id_producto;

    });

    //Agrega un evento cuando se cierra el modal
    eliminaProductoModal.addEventListener('hide.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        form_eliminar_producto.reset();

        //Elimina cualquier alerta previa en el modal
        var alert_eliminar_modal_producto = document.getElementById('alert_eliminar_modal_producto');
        alert_eliminar_modal_producto.innerHTML = '';

        //Elimina cualquier mensaje previa en el modal
        var label_mensaje_eliminar_producto = document.getElementById('label_mensaje_eliminar_producto');
        label_mensaje_eliminar_producto.innerHTML = '';

    });



    //Manejo del envio del formulario para eliminar una publicacion
    form_eliminar_producto.addEventListener("submit", (e) => {


        //Previene el envio por defecto del formulario
        e.preventDefault();

        //Elimina cualquier alerta previa 
        alert_eliminar_modal_producto = document.getElementById("alert_eliminar_modal_producto");
        alert_eliminar_modal_producto.innerHTML = "";

        var id_producto_eliminar = document.getElementById('id_producto_eliminar');

        //Valida que los campos ocultos solo contengan numeros
        if (!isNaN(id_producto_eliminar)) {
            alert_eliminar_modal_producto.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
            return false;
        }

        // Envío del formulario usando fetch
        const formData = new FormData(form_eliminar_producto);

        fetch(`producto/baja_producto.php${window.location.search}`, {

                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {

                if (datos.estado == "success") {

                    //En caso que la cantidad de publicaciones que se ve en la interfaz sea igual 1 y la pagina actual es mayor a 1 
                    //Se va restar uno a la pagina actual debido a la eliminacion de la publicacion
                    if (cant_actual_registros_productos == 1 && pagina_actual_producto > 1) {
                        pagina_actual_producto = pagina_actual_producto - 1;
                    }

                    // Actualiza los datos de las preguntas
                    getDataProductos();

                    //Muetra un mensaje en la interfaz del usuario
                    alert_notificacion_producto.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                    //Cierra el modal 
                    ventanaModalEliminarProducto.hide();

                } else {

                    //Muestra un mensaje de error en el Modal
                    alert_eliminar_modal_producto.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }

            }).catch(e => {

                // Muestra un mensaje error de la solicitud
                alert_eliminar_modal_producto.innerHTML = mensaje_alert_fijo("danger", e);
            });

    });
</script>