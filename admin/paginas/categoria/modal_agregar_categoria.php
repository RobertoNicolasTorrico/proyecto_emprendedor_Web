<!--Modal para agregar una categoria -->
<div class="modal fade" id="ModalAgregarCategoria" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ModalAgregarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="ModalAgregarCategoriaLabel">Agregar una nueva Categoria</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!--Formulario dentro del modal-->
            <form method="POST" enctype="multipart/form-data" id="formulario_agregar_categoria">

                <!--Body del modal -->
                <div class="modal-body">


                    <!--Div para mostrar errores al responder la pregunta -->
                    <div id="alert_notificacion_agregar_categoria"></div>

                    <!--Campo para ingresar la categoria-->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                        <input type="text" class="form-control" name="txt_nueva_tipo_categoria" id="txt_nueva_tipo_categoria" placeholder="Tipo de categoria" minlength="1" maxlength="40" data-max="40" required>
                        <label for="txt_nueva_tipo_categoria">Categoria</label>
                        <!--Mostrar la cantidad de caracteres permitidos y restantes-->
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                            <span class="form-text">Maximo 40 caracteres.<span id="txaCountNuevaCategoria">40 restantes</span></span>
                        </div>
                    </div>
                </div>

                <!--Footer del modal -->
                <div class="modal-footer">
                    <!--Boton para cerrar el modal y cancelar la operacion -->
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>

                    <!--Boton para enviar la categoria-->
                    <button type="submit" class="btn btn-outline-success">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    // Se obtiene el formulario para agregar una nueva categoria
    var form_agregar_categoria = document.getElementById("formulario_agregar_categoria");


    // Se obtiene el modal de agregar una nueva categori
    const ModalAgregarCategoria = document.getElementById('ModalAgregarCategoria');

    //Agrega un evento cuando se cierra el modal
    ModalAgregarCategoria.addEventListener('hide.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        form_agregar_categoria.reset();

        //Contar y mostrar caracteres restantes en el campo categoria
        contarMostrarCarecteresRestantes('txt_nueva_tipo_categoria', 'txaCountNuevaCategoria');


        //Elimina cualquier alerta previa en el modal
        var alert_notificacion_agregar_categoria = document.getElementById('alert_notificacion_agregar_categoria');
        alert_notificacion_agregar_categoria.innerHTML = "";

    });




    //Manejo del envio del formulario para hacer una nueva categoria
    form_agregar_categoria.addEventListener('submit', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();


        //Elimina cualquier alerta previa 
        var alert_notificacion_agregar_categoria = document.getElementById('alert_notificacion_agregar_categoria');
        alert_notificacion_agregar_categoria.innerHTML = "";

        var txt_nueva_tipo_categoria = document.getElementById('txt_nueva_tipo_categoria');


        //Valida que el campo categoria no este vacio
        if (validarCampoVacio([txt_nueva_tipo_categoria])) {
            alert_notificacion_agregar_categoria.innerHTML = mensaje_alert_fijo("danger", "Por favor complete el campo categoria");
            return false;
        }


        //Valida que el campo no tenga espacios al inicio o al final de la cadena
        if (txt_nueva_tipo_categoria.value.trim() != txt_nueva_tipo_categoria.value) {
            alert_notificacion_agregar_categoria.innerHTML = mensaje_alert_fijo("danger", "La categoria no puede tener espacios en blanco al inicio o al final");
            return false;
        }


        //Valida que la longitud del campo sea valida 
        if (!validarCantLengthInput(txt_nueva_tipo_categoria)) {
            alert_notificacion_agregar_categoria.innerHTML = mensaje_alert_fijo("danger", "El campo categoria debe tener entre 1 y 40 caracteres");
            return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData(form_agregar_categoria);

        fetch(`alta_categoria.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado === 'success') {

                    //Muestra un mensaje en la interfaz del usuario
                    alert_notificacion_agregar_categoria.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

                    // Actualiza los datos de las categorias
                    getDatosCategorias();

                    //Resetea el formulario para limpiar los campos
                    form_agregar_categoria.reset();

                    //Contar y mostrar caracteres restantes en el campo categoria
                    contarMostrarCarecteresRestantes('txt_nueva_tipo_categoria', 'txaCountNuevaCategoria');

                } else {
                    //Muestra un mensaje de error en el Modal
                    alert_notificacion_agregar_categoria.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }
            })
            .catch(e => {

                // Muestra un mensaje error de la solicitud
                alert_notificacion_agregar_categoria.innerHTML = mensaje_alert_fijo("danger", e);
            });
    });


    //Agrega un evento para contar y mostrar caracteres restantes en el campo de respuesta
    document.getElementById('txt_nueva_tipo_categoria').addEventListener('input', function() {
        contarMostrarCarecteresRestantes('txt_nueva_tipo_categoria', 'txaCountNuevaCategoria');
    });
</script>