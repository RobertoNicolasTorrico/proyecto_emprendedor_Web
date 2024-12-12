<!-- Modal para modificar una pregunta -->
<div class="modal fade" id="modificarModalPregunta" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modificarModalPreguntaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modificarModalPreguntaLabel">Modificar Pregunta</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Formulario dentro del modal -->
            <form method="POST" enctype="multipart/form-data" id="formulario_modificar_pregunta" novalidate>

                <!-- Body del modal -->
                <div class="modal-body">

                    <!--Div para mostrar errores en la modificaciones de la pregunta -->
                    <div id="alert_notificacion_modificar_pregunta"></div>


                    <!--Campos oculto para almacenar el ID de la pregunta -->
                    <input type="number" name="id_pregunta_modificar" id="id_pregunta_modificar" hidden required>


                    <!-- Div que contiene el campo de la fecha que se hizo la pregunta-->
                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                        <div class="input-group">
                            <span class="input-group-text">Fecha que se hizo la pregunta:</span>
                            <input type="datetime-local" name="fecha_mi_pregunta" id="fecha_mi_pregunta" placeholder="Fecha registro" class="form-control" required>
                        </div>
                    </div>

                    <!-- Div que contiene el campo pregunta y muestra la cantidad maxima de caracteres permitidos-->
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="txt_pregunta_modificar" placeholder="Pregunta" id="txt_pregunta_modificar" minlength="1" maxlength="255" data-max="255" required></textarea>
                        <label for="txt_pregunta_modificar">Pregunta</label>
                        <span class="form-text">Maximo 255 caracteres.<span id="txaCountPreguntaModificar"> 255 restantes</span></span>
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
    var form_modificar_mi_pregunta = document.getElementById("formulario_modificar_pregunta");


    // Se obtiene el modal de agregar respuesta
    const modificarModalPregunta = document.getElementById('modificarModalPregunta');

    // Variables para almacenar los valores originales de la preguna 
    var bd_fecha_modificar_pregunta;
    var bd_txt_modificar_pregunta;


    //Agrega un evento para contar y mostrar caracteres restantes en el campo de pregunta
    document.getElementById('txt_pregunta_modificar').addEventListener('input', function() {
        contarMostrarCarecteresRestantes('txt_pregunta_modificar', 'txaCountPreguntaModificar');
    });



    //Agrega un evento cuando se cierra el modal
    modificarModalPregunta.addEventListener('hide.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        form_modificar_mi_pregunta.reset();

        //Elimina cualquier alerta previa en el modal
        var alert_notificacion_modificar_pregunta = document.getElementById('alert_notificacion_modificar_pregunta');
        alert_notificacion_modificar_pregunta.innerHTML = '';


        //Elimina el contenido de las variables que guardan la informacion de la pregunta
        bd_fecha_modificar_pregunta = '';
        bd_txt_modificar_pregunta = '';

        //Contar y mostrar caracteres restantes en el campo pregunta
        contarMostrarCarecteresRestantes('txt_pregunta_modificar', 'txaCountPreguntaModificar');
    });


    //Agrega un evento cuando se abre el modal
    modificarModalPregunta.addEventListener('show.bs.modal', event => {

        //Se obtiene el boton que abrio el modal
        var button = event.relatedTarget;

        //Se obtiene atributos del boton
        var fecha_bd_pregunta = button.getAttribute('data-bs-fecha');
        var bd_pregunta = button.getAttribute('data-bs-pregunta');
        var id_pregunta = button.getAttribute('data-bs-id_pregunta');


        //Se transfiere la fecha que se hizo la pregunta al campo fecha_mi_pregunta 
        document.getElementById("fecha_mi_pregunta").value = fecha_bd_pregunta;

        //Se transfiere el texto de la pregunta a modificar al campo txt_pregunta_modificar 
        document.getElementById("txt_pregunta_modificar").value = bd_pregunta;

        //Se transfiere el ID de la pregunta a modificar al campo id_pregunta_modificar 
        document.getElementById("id_pregunta_modificar").value = id_pregunta;

        //Se guarda los datos originales de pregunta en otras variables 
        bd_fecha_modificar_pregunta = fecha_bd_pregunta;
        bd_txt_modificar_pregunta = bd_pregunta;

        //Contar y mostrar caracteres restantes en el campo pregunta
        contarMostrarCarecteresRestantes('txt_pregunta_modificar', 'txaCountPreguntaModificar');
    });


    //Manejo del envio del formulario para hacer una modificacion a una pregunta
    form_modificar_mi_pregunta.addEventListener('submit', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_notificacion_modificar_pregunta = document.getElementById('alert_notificacion_modificar_pregunta');
        alert_notificacion_modificar_pregunta.innerHTML = "";

        var fecha_mi_pregunta = document.getElementById('fecha_mi_pregunta');
        var txt_pregunta_modificar = document.getElementById('txt_pregunta_modificar');
        var id_pregunta_modificar = document.getElementById('id_pregunta_modificar');
        var fecha_hora_formateada = devolverFechaDateTimeLocalInput(fecha_mi_pregunta);


        //Valida que el campo pregunta no este vacio
        if (validarCampoVacio([txt_pregunta_modificar,fecha_mi_pregunta])) {
            alert_notificacion_modificar_pregunta.innerHTML = mensaje_alert_fijo("danger", "Por favor complete todos los campos");
            return false;
        }

        //Se verifica que los valores ingresados en los campos inputs sea diferente al anterior
        if (txt_pregunta_modificar.value == bd_txt_modificar_pregunta && fecha_hora_formateada == bd_fecha_modificar_pregunta) {
            alert_notificacion_modificar_pregunta.innerHTML = mensaje_alert_fijo("info", "No hubo cambios en la pregunta");
            return false;
        }


        //Valida que el campo no tenga espacios al inicio o al final de la cadena
        if (txt_pregunta_modificar.value.trim() != txt_pregunta_modificar.value) {
            alert_notificacion_modificar_pregunta.innerHTML = mensaje_alert_fijo("danger", "La pregunta no puede tener espacios en blanco al inicio o al final");
            return false;
        }


        //Valida que la longitud del campo sea valida 
        if (!validarCantLengthInput(txt_pregunta_modificar)) {
            alert_notificacion_modificar_pregunta.innerHTML = mensaje_alert_fijo("danger", "El campo pregunta debe tener entre 1 y 255 caracteres");
            return false;
        }


        //Valida que el campo oculto solo contenga numeros
        if (!isNaN(id_pregunta_modificar)) {
            alert_notificacion_modificar_pregunta.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData(form_modificar_mi_pregunta);
        formData.append('fecha_modificada', fecha_hora_formateada);

        fetch(`preguntas/modificar_pregunta.php${window.location.search}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado === 'success') {

                    // Actualiza los datos de las preguntas
                    getDataMisPreguntas();

                    bd_fecha_modificar_pregunta = fecha_hora_formateada;
                    bd_txt_modificar_pregunta = txt_pregunta_modificar.value;

                    //Muestra un mensaje en el Modal del usuario
                    alert_notificacion_modificar_pregunta.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);
                } else {
                    //Muestra un mensaje en el Modal del usuario
                    alert_notificacion_modificar_pregunta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }
            })
            .catch(e => {

                // Muestra un mensaje error de la solicitud
                alert_notificacion_modificar_pregunta.innerHTML = mensaje_alert_fijo("danger", e);

            });
    });
</script>