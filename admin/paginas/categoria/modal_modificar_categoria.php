<!-- Modal para modificar la categoria -->
<div class="modal fade" id="ModalModificarCategoria" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ModalModificarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="ModalModificarCategoriaLabel">Modificar Categoria</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>


            <!-- Formulario dentro del modal -->
            <form method="POST" enctype="multipart/form-data" id="formulario_modificar_categoria">

                <!-- Body del modal -->
                <div class="modal-body">

                    <!-- Div para mostrar errores al modificar de la categoria -->
                    <div id="alert_notificacion_modificar_categoria"></div>

                    <!--Campos ocultos para almacenar el ID de categoria -->
                    <input type="number" name="id_categoria" id="id_categoria" hidden required>


                    <!-- Div que contiene el campo categoria y muestra la cantidad maxima de caracteres permitidos -->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                        <input type="text" class="form-control" name="txt_tipo_categoria" id="txt_tipo_categoria" placeholder="Tipo de categoria" minlength="1" maxlength="40" data-max="40" required>
                        <label for="txt_tipo_categoria">Categoria</label>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                            <span class="form-text">Maximo 40 caracteres.<span id="txaCountCategoria">40 restantes</span></span>
                        </div>
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
    // Se obtiene el formulario para modificar la categoria
    var form_modificar_categoria = document.getElementById("formulario_modificar_categoria");


    // Se obtiene el modal para modificar la categoria
    const modificarModalCategoria = document.getElementById('ModalModificarCategoria');


    //Agrega un evento para contar y mostrar caracteres restantes en el campo de categoria
    document.getElementById('txt_tipo_categoria').addEventListener('input', function() {
        contarMostrarCarecteresRestantes('txt_tipo_categoria', 'txaCountCategoria');
    });


    //Manejo del envio del formulario para hacer una modificacion a una categoria
    form_modificar_categoria.addEventListener('submit', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        var alert_notificacion_modificar_categoria = document.getElementById('alert_notificacion_modificar_categoria');
        alert_notificacion_modificar_categoria.innerHTML = "";

        var txt_tipo_categoria = document.getElementById('txt_tipo_categoria');
        var id_categoria = document.getElementById('id_categoria');


        //Valida que el campo categoria no este vacio
        if (validarCampoVacio([txt_tipo_categoria])) {
            alert_notificacion_modificar_categoria.innerHTML = mensaje_alert_fijo("danger", "Por favor complete el campo categoria");
            return false;
        }

        //Se verifica que el valor ingresado en el campo input sea diferente al anterior
        if (txt_tipo_categoria.value == bd_txt_tipo_categoria) {
            alert_notificacion_modificar_categoria.innerHTML = mensaje_alert_fijo("info", "No hubo cambios en el nombre de la categoria");
            return false;
        }


        //Valida que el campo no tenga espacios al inicio o al final de la cadena
        if (txt_tipo_categoria.value.trim() != txt_tipo_categoria.value) {
            alert_notificacion_modificar_categoria.innerHTML = mensaje_alert_fijo("danger", "La categoria no puede tener espacios en blanco al inicio o al final");
            return false;
        }


        //Valida que la longitud del campo sea valida 
        if (!validarCantLengthInput(txt_tipo_categoria)) {
            alert_notificacion_modificar_categoria.innerHTML = mensaje_alert_fijo("danger", "El campo categoria debe tener entre 1 y 40 caracteres");
            return false;
        }


        //Valida que el campo oculto solo contenga numeros
        if (!isNaN(id_categoria)) {
            alert_notificacion_modificar_categoria.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
            return false;
        }

        // Envío del formulario usando fetch
        const formData = new FormData(form_modificar_categoria);

        fetch(`modificar_categoria.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado === 'success') {

                    // Actualiza los datos de las categorias
                    getDatosCategorias();

                    //Muestra un mensaje en el Modal del usuario
                    alert_notificacion_modificar_categoria.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);
                } else {

                    //Muestra un mensaje en el Modal del usuario
                    alert_notificacion_modificar_categoria.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }
            })
            .catch(e => {

                // Muestra un mensaje error de la solicitud
                alert_notificacion_modificar_categoria.innerHTML = mensaje_alert_fijo("danger", e);
            });
    });



    //Agrega un evento cuando se muestra el modal
    modificarModalCategoria.addEventListener('show.bs.modal', event => {
        //Se obtiene el boton que abrio el modal
        var button = event.relatedTarget;

        //Se obtiene atributos del boton
        var bd_tipo_categoria = button.getAttribute('data-bs-tipo_categoria');
        var id_categoria_producto = button.getAttribute('data-bs-id_categoria_producto');


        //Se transfiere el texto de la categoria a modificar al campo txt_tipo_categoria 
        document.getElementById("txt_tipo_categoria").value = bd_tipo_categoria;

        //Se transfiere el ID de la categoria a modificar al campo id_categoria 
        document.getElementById("id_categoria").value = id_categoria_producto;


        //Se transfiere el texto de la categoria a modificar al campo bd_txt_tipo_categoria para comprobar si hubo cambios o no 
        bd_txt_tipo_categoria = bd_tipo_categoria;

        //Contar y mostrar caracteres restantes en el campo categoria
        contarMostrarCarecteresRestantes('txt_tipo_categoria', 'txaCountCategoria');


    });



    //Agrega un evento cuando se cierra el modal
    modificarModalCategoria.addEventListener('hide.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        form_modificar_categoria.reset();

        //Elimina cualquier alerta previa en el modal
        var alert_notificacion_modificar_categoria = document.getElementById("alert_notificacion_modificar_categoria");
        alert_notificacion_modificar_categoria.innerHTML = '';

        //Elimina el contenido de la variable bd_txt_tipo_categoria 
        bd_txt_tipo_categoria = "";

        //Contar y mostrar caracteres restantes en el campo categoria
        contarMostrarCarecteresRestantes('txt_tipo_categoria', 'txaCountCategoria');

    });
</script>