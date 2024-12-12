<!--Modal para confirmar la eliminacion de la categoria -->
<div class="modal fade" id="ModalEliminarCategoria" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ModalEliminarCategoriaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <!--Header del modal -->
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="ModalEliminarCategoriaLabel">Aviso</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!--Body del modal -->
      <div class="modal-body">

        <!--Div para mostrar errores en la eliminacion de la categoria -->
        <div id="alert_eliminar_modal_categoria"></div>
        <p>¿Estas seguro de querer eliminar la siguiente categoria?</p>

        <!--Div para mostrar la categoria que se va a eliminar -->
        <div id="label_mensaje_eliminar_categoria"></div>
      </div>



      <!--Footer del modal -->
      <div class="modal-footer">
        <!--Formulario dentro del modal-->
        <form method="POST" enctype="multipart/form-data" id="formulario_eliminar_categoria">

          <!--Campos ocultos para almacenar el ID de la categoria -->
          <input type="number" name="id_categoria_eliminar" id="id_categoria_eliminar" hidden required>

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
  var form_eliminar_categoria = document.getElementById("formulario_eliminar_categoria");

  // Obtener el modal de eliminacion
  const ModalEliminarCategoria = document.getElementById('ModalEliminarCategoria');
  const ventanaModalEliminarCategoria = new bootstrap.Modal(ModalEliminarCategoria);



  //Manejo del envio del formulario para eliminar una pregunta
  form_eliminar_categoria.addEventListener("submit", (e) => {

    //Previene el envio por defecto del formulario
    e.preventDefault();

    //Elimina cualquier alerta previa 
    var alert_eliminar_modal_categoria = document.getElementById("alert_eliminar_modal_categoria");
    alert_eliminar_modal_categoria.innerHTML = "";


    var id_categoria_eliminar = document.getElementById('id_categoria_eliminar');

    //Valida que el campo oculto solo contenga numeros
    if (!isNaN(id_categoria_eliminar)) {
      alert_eliminar_modal_categoria.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
      return false;
    }

    // Envío del formulario usando fetch
    const formData = new FormData(form_eliminar_categoria);
    fetch(`baja_categoria.php`, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(datos => {

        if (datos.estado == "success") {

          //En caso que la cantidad de categorias que se ve en la interfaz sea igual 1 y la pagina actual es mayor a 1 
          //Se va restar uno a la pagina actual debido a la eliminacion de la pregunta
          if (cant_actual_registros == 1 && pagina_actual_categoria > 1) {
            pagina_actual_categoria = pagina_actual_categoria - 1;
          }

          // Actualiza los datos de las preguntas
          getDatosCategorias();

          //Muetra un mensaje en la interfaz del usuario
          alert_notificacion_categoria.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

          //Cierra el modal 
          ventanaModalEliminarCategoria.hide();

        } else {
          if (datos.estado == "danger") {
            //Muestra un mensaje de error en el Modal
            alert_eliminar_modal_categoria.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
          }
        }

      }).catch(e => {

        // Muestra un mensaje error de la solicitud
        alert_eliminar_modal_categoria.innerHTML = mensaje_alert_fijo("danger", e);
      });
  });


  //Agrega un evento cuando se muestra el modal
  ModalEliminarCategoria.addEventListener('show.bs.modal', event => {

    //Obtener el boton que abrio el modal
    var button = event.relatedTarget;

    //Obtener atributos del boton
    var id_categoria = button.getAttribute('data-bs-id_categoria_producto');
    var tipo_categoria = button.getAttribute('data-bs-tipo_categoria');

    //Mostrar la categoria
    var label_mensaje_eliminar_categoria = document.getElementById('label_mensaje_eliminar_categoria');
    label_mensaje_eliminar_categoria.innerHTML = '<p class="text-break"><strong>Categoria:</strong>' + tipo_categoria + '</p>';

    //Asigna el ID de categoria al formulario en el modal 
    ModalEliminarCategoria.querySelector('.modal-footer #id_categoria_eliminar').value = id_categoria;

  });



  //Agrega un evento cuando se cierra el modal
  ModalEliminarCategoria.addEventListener('hide.bs.modal', event => {

    //Resetea el formulario para limpiar los campos
    form_eliminar_categoria.reset();

    //Elimina cualquier alerta previa en el modal
    var alert_eliminar_modal_categoria = document.getElementById("alert_eliminar_modal_categoria");
    alert_eliminar_modal_categoria.innerHTML = '';

    //Elimina cualquier mensaje previa en el modal
    var label_mensaje_eliminar_categoria = document.getElementById('label_mensaje_eliminar_categoria');
    label_mensaje_eliminar_categoria.innerHTML = '';

  });
</script>