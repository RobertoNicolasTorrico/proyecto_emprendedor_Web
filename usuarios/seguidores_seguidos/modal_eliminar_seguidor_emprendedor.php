<!--Modal para confirmar la eliminacion del emprendedor -->
<div class="modal fade" id="eliminarSeguidorEmprendedor" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="eliminarSeguidorEmprendedorLabel" aria-hidden="true">

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

                <p>¿Estas seguro de querer dejar de seguir al siguiente emprendedor?</p>

                <!--Div para mostrar el nombre del emprendedor que va a dejar de seguir -->
                <div id="label_mensaje_eliminar_seguidor_emprendedor"></div>
            </div>

            <!--Footer del modal -->
            <div class="modal-footer">

                <!--Formulario dentro del modal-->
                <form method="POST" enctype="multipart/form-data" id="formulario_eliminar_seguidor_emprendedor">

                    <!--Campos ocultos para almacenar el ID del usuario y el token de la cuenta del emprendedor -->
                    <input type="text" name="token_emprendedor" id="token_emprendedor" hidden required>
                    <input type="number" name="id_usuario_eliminar_seguidor_emprendedor" id="id_usuario_eliminar_seguidor_emprendedor" hidden required>


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
    var form_eliminar_seguidor_emprendedor = document.getElementById("formulario_eliminar_seguidor_emprendedor");

    // Obtener el modal de eliminacion
    const eliminarSeguidorEmprendedor = document.getElementById('eliminarSeguidorEmprendedor');
    const ventanaModaleliminarSeguidorEmprendedor = new bootstrap.Modal(eliminarSeguidorEmprendedor);


    //Agrega un evento cuando se abre el modal
    eliminarSeguidorEmprendedor.addEventListener('show.bs.modal', event => {

        //Obtener el boton que abrio el modal
        var button = event.relatedTarget;

        //Obtener atributos del boton
        var nombre_emprendimiento = button.getAttribute('data-bs-nombre_emprendimiento');
        var id_usuario_emprendedor = button.getAttribute('data-bs-id_usuario_emprendedor');
        var token_emprendedor = button.getAttribute('data-perfil-token');


        //Mostrar el nombre del usuario
        var label_mensaje_eliminar_seguidor_emprendedor = document.getElementById('label_mensaje_eliminar_seguidor_emprendedor');
        label_mensaje_eliminar_seguidor_emprendedor.innerHTML = '<p class="text-break"><strong>Nombre del emprendedor:</strong>' + nombre_emprendimiento + '</p>';

        //Asigna el ID del usuario emprendedor y el token del usuario en el formulario del modal 
        eliminarSeguidorEmprendedor.querySelector('.modal-footer #id_usuario_eliminar_seguidor_emprendedor').value = id_usuario_emprendedor;
        eliminarSeguidorEmprendedor.querySelector('.modal-footer #token_emprendedor').value = token_emprendedor;

    });


    //Agrega un evento cuando se cierra el modal
    eliminarSeguidorEmprendedor.addEventListener('hide.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        form_eliminar_seguidor_emprendedor.reset();

        //Elimina cualquier alerta previa en el modal
        var alert_eliminar_modal_seguidor_emprendedor = document.getElementById('alert_eliminar_modal_seguidor_emprendedor');
        alert_eliminar_modal_seguidor_emprendedor.innerHTML = '';

        //Elimina cualquier mensaje previa en el modal
        var label_mensaje_eliminar_seguidor_emprendedor = document.getElementById('label_mensaje_eliminar_seguidor_emprendedor');
        label_mensaje_eliminar_seguidor_emprendedor.innerHTML = '';

    });




    //Manejo del envio del formulario para que el usuario deje de seguir al usuario emprendedor
    form_eliminar_seguidor_emprendedor.addEventListener("submit", (e) => {


        //Previene el envio por defecto del formulario
        e.preventDefault();


        //Elimina cualquier alerta previa 
        var alert_eliminar_modal_seguidor_emprendedor = document.getElementById("alert_eliminar_modal_seguidor_emprendedor");
        alert_eliminar_modal_seguidor_emprendedor.innerHTML = "";


        var id_usuario_eliminar_seguidor_emprendedor = document.getElementById('id_usuario_eliminar_seguidor_emprendedor');
        var token_emprendedor = document.getElementById('token_emprendedor');


        //Valida que los campos ocultos solo contengan numeros
        if (!isNaN(id_usuario_eliminar_seguidor_emprendedor)) {
            alert_eliminar_modal_seguidor_emprendedor.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData(form_eliminar_seguidor_emprendedor);
        fetch(`baja_seguir_usuario.php?id=${id_usuario_eliminar_seguidor_emprendedor.value}&token=${token_emprendedor.value}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado == "success") {

                    //Verifica si hubo una busqueda de emprendedores por parte del usuario
                    if (busqueda_activa_seguidos) {
                        //En caso que la busqueda esta activada se va a restar un seguidor de cantidad de la lista de los seguidores actuales
                        var cant_resultados_seguidos = document.getElementById("cant_resultados_seguidos");
                        cant_total_seguidos = cant_total_seguidos - 1;
                        cant_resultados_seguidos.innerHTML = cant_total_seguidos;

                    } else {
                        //En caso que la busqueda esta desactivada se va obtener el numero de seguidores actuales del usuario
                        var cant_actual_seguidos = document.getElementById("cant_actual_seguidos");
                        cant_actual_seguidos.innerHTML = datos.numero_seguidos_usuario;

                    }

                    //Se elimina el contenedor que tiene el seguidor
                    var div_seguido_emprendedor = document.getElementById('div_seguido_emprendedor_' + id_usuario_eliminar_seguidor_emprendedor.value);
                    div_seguido_emprendedor.remove();

                    //Cierra el modal 
                    ventanaModaleliminarSeguidorEmprendedor.hide();

                } else {
                    //Muestra un mensaje de error en el Modal
                    alert_eliminar_modal_seguidor_emprendedor.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);

                }
            }).catch(e => {

                // Muestra un mensaje error de la solicitud
                alert_eliminar_modal_seguidor_emprendedor.innerHTML = mensaje_alert_fijo("danger", e);
            });
    });
</script>