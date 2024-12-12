<!-- Modal para modificar una respuesta -->
<div class="modal fade" id="modificarModalRespuesta" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modificarModalRespuestaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modificarModalRespuestaLabel">Modificar Respuesta</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Formulario dentro del modal -->
            <form method="POST" enctype="multipart/form-data" id="formulario_modificar_respuesta" novalidate>

                <!-- Body del modal -->
                <div class="modal-body">

                    <!--Div para mostrar errores en la modificaciones de la respuesta -->
                    <div id="alert_notificacion_modificar_respuesta"></div>


                    <!--Campos oculto para almacenar el ID de la pregunta_respuesta -->
                    <input type="number" name="id_respuesta_modificar" id="id_respuesta_modificar" hidden required>


                    <!-- Div que contiene el campo de la fecha cuando se respondio la pregunta-->
                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                        <div class="input-group">
                            <span class="input-group-text">Fecha que respondio:</span>
                            <input type="datetime-local" name="fecha_mi_respuesta" id="fecha_mi_respuesta" placeholder="Fecha registro" class="form-control" required>
                        </div>
                    </div>

                    <!-- Div que contiene el campo respuesta y muestra la cantidad maxima de caracteres permitidos-->
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="txt_respuesta_modificar" placeholder="Respuesta" id="txt_respuesta_modificar" minlength="1" maxlength="255" data-max="255" required></textarea>
                        <label for="txt_respuesta_modificar">Respuesta</label>
                        <span class="form-text">Maximo 255 caracteres.<span id="txaCountRespuestaModificar"> 255 restantes</span></span>
                    </div>
                </div>

                <!-- Footer del modal -->
                <div class="modal-footer">

                    <!-- Botón para cerrar el modal y cancelar la operacion -->
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>

                    <!-- Botón para enviar la publicación -->
                    <button type="submit" class="btn btn-outline-success">Guardar cambios</button>
                </div>
            </form>


        </div>
    </div>
</div>
<script>
    // Se obtiene el formulario para modificar la pregunta
    var formulario_modificar_respuesta = document.getElementById("formulario_modificar_respuesta");

    // Se obtiene el modal de agregar respuesta
    const modificarModalRespuesta = document.getElementById('modificarModalRespuesta');

    // Variables para almacenar los valores originales de la preguna 
    var bd_fecha_modificar_respuesta;
    var bd_txt_modificar_respuesta;


    //Agrega un evento para contar y mostrar caracteres restantes en el campo de respuesta

    document.getElementById('txt_respuesta_modificar').addEventListener('input', function() {
        contarMostrarCarecteresRestantes('txt_respuesta_modificar', 'txaCountRespuestaModificar');
    });





    //Agrega un evento cuando se cierra el modal
    modificarModalRespuesta.addEventListener('hide.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        formulario_modificar_respuesta.reset();

        //Elimina cualquier alerta previa en el modal
        var alert_notificacion_modificar_respuesta = document.getElementById('alert_notificacion_modificar_respuesta');
        alert_notificacion_modificar_respuesta.innerHTML = '';


        //Elimina el contenido de las variables que guardan la informacion de la pregunta
        bd_fecha_modificar_respuesta = '';
        bd_txt_modificar_respuesta = '';

        //Contar y mostrar caracteres restantes en el campo respuesta
        contarMostrarCarecteresRestantes('txt_respuesta_modificar', 'txaCountRespuestaModificar');
    });


    //Agrega un evento cuando se abre el modal
    modificarModalRespuesta.addEventListener('show.bs.modal', event => {

        //Se obtiene el boton que abrio el modal
        var button = event.relatedTarget;

        //Se obtiene atributos del boton
        var fecha_bd_respuesta = button.getAttribute('data-bs-fecha_respuesta');
        var id_respuesta = button.getAttribute('data-bs-id_respuesta');
        var bd_respuesta = button.getAttribute('data-bs-respuesta');


        //Se transfiere la fecha que se hizo la respuesta al campo fecha_mi_respuesta 
        document.getElementById("fecha_mi_respuesta").value = fecha_bd_respuesta;

        //Se transfiere el texto de la respuesta a modificar al campo txt_respuesta_modificar 
        document.getElementById("txt_respuesta_modificar").value = bd_respuesta;

        //Se transfiere el ID de la respuesta a modificar al campo id_respuesta_modificar 
        document.getElementById("id_respuesta_modificar").value = id_respuesta;

        //Se guarda los datos originales de respuesta en otras variables 
        bd_fecha_modificar_respuesta = fecha_bd_respuesta;
        bd_txt_modificar_respuesta = bd_respuesta;

        //Contar y mostrar caracteres restantes en el campo respuesta
        contarMostrarCarecteresRestantes('txt_respuesta_modificar', 'txaCountRespuestaModificar');

    });

    //Manejo del envio del formulario para hacer una modificacion de respuesta
    formulario_modificar_respuesta.addEventListener('submit', function(event) {


        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_notificacion_modificar_respuesta = document.getElementById('alert_notificacion_modificar_respuesta');
        alert_notificacion_modificar_respuesta.innerHTML = '';


        var fecha_mi_respuesta = document.getElementById('fecha_mi_respuesta');
        var txt_respuesta_modificar = document.getElementById('txt_respuesta_modificar');
        var id_respuesta_modificar = document.getElementById('id_respuesta_modificar');
        var fecha_hora_formateada = devolverFechaDateTimeLocalInput(fecha_mi_respuesta);



        //Valida que el campo respuesta no este vacio
        if (validarCampoVacio([txt_respuesta_modificar,fecha_mi_respuesta])) {
            alert_notificacion_modificar_respuesta.innerHTML = mensaje_alert_fijo("danger", "Por favor complete todos los campos");
            return false;
        }

        //Se verifica que los valores ingresados en los campos inputs sea diferente al anterior
        if (txt_respuesta_modificar.value == bd_txt_modificar_respuesta && fecha_hora_formateada == bd_fecha_modificar_respuesta) {
            alert_notificacion_modificar_respuesta.innerHTML = mensaje_alert_fijo("info", "No hubo cambios en la respuesta");
            return false;
        }


        //Valida que el campo no tenga espacios al inicio o al final de la cadena
        if (txt_respuesta_modificar.value.trim() != txt_respuesta_modificar.value) {
            alert_notificacion_modificar_respuesta.innerHTML = mensaje_alert_fijo("danger", "La respuesta no puede tener espacios en blanco al inicio o al final");
            return false;
        }


        //Valida que la longitud del campo sea valida 
        if (!validarCantLengthInput(txt_respuesta_modificar)) {
            alert_notificacion_modificar_respuesta.innerHTML = mensaje_alert_fijo("danger", "El campo respuesta debe tener entre 1 y 255 caracteres");
            return false;
        }


        //Valida que el campo oculto solo contenga numeros
        if (!isNaN(id_respuesta_modificar)) {
            alert_notificacion_modificar_respuesta.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData(formulario_modificar_respuesta);
        formData.append('fecha_modificada', fecha_hora_formateada);

        fetch(`preguntas_respuesta/modificar_respuesta.php${window.location.search}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado === 'success') {

                    // Actualiza los datos de las preguntas recibidas
                    getDataPreguntasRecibidas();


                    bd_fecha_modificar_respuesta = fecha_hora_formateada;
                    bd_txt_modificar_respuesta =txt_respuesta_modificar.value;


                    //Muestra un mensaje en el Modal del usuario
                    alert_notificacion_modificar_respuesta.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);
                } else {

                    //Muestra un mensaje en el Modal del usuario
                    alert_notificacion_modificar_respuesta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }
            })
            .catch(e => {

                // Muestra un mensaje error de la solicitud
                alert_notificacion_modificar_respuesta.innerHTML = mensaje_alert_fijo("danger", e);
            });

    });
</script>