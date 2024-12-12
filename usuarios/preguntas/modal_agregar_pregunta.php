<!--Modal para agregar otra pregunta -->
<div class="modal fade" id="agregarModal" tabindex="-1" aria-labelledby="agregarModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <!--Header del modal -->
      <div class="modal-header">
        <h5 class="modal-title" id="agregarModalLabel">Hacer otra pregunta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!--Formulario dentro del modal-->
      <form method="POST" enctype="multipart/form-data" id="formulario_agregar">

        <!--Body del modal -->
        <div class="modal-body">

          <!--Div para mostrar errores al responder la pregunta -->
          <div id="alert_pregunta_modal"></div>
          <!--Campos oculto para almacenar el ID del producto -->
          <input type="number" name="id_producto" id="id_producto" hidden required>

          <div class="form-floating mb-3">
            <!--Campo para ingresar la pregunta-->
            <textarea class="form-control" name="txt_pregunta" placeholder="Escriba su pregunta" id="txt_pregunta" minlength="1" maxlength="255" data-max="255" style="height: 116px;" required></textarea>
            <label for="txt_pregunta">Escriba su pregunta</label>

            <!--Mostrar la cantidad de caracteres permitidos y restantes-->
            <span class="form-text">Maximo 255 caracteres.<span id="txaCountPregunta"> 255 restantes</span></span>
          </div>
        </div>


        <!--Footer del modal -->
        <div class="modal-footer">
          <!--Boton para cerrar el modal y cancelar la operacion -->
          <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>

          <!--Boton para enviar la pregunta-->
          <button type="submit" class="btn btn-outline-success">Enviar pregunta</button>
        </div>


      </form>
    </div>
  </div>
</div>
<script>
  // Se obtiene el formulario para agregar otra pregunta
  var form_pregunta = document.getElementById("formulario_agregar");


  // Se obtiene el modal de agregar respuesta
  const agregarModal = document.getElementById('agregarModal');
  const ventanaModalAgregar = new bootstrap.Modal(agregarModal);

  //Agrega un evento cuando se muestra el modal
  agregarModal.addEventListener('show.bs.modal', event => {

    //Resetea el formulario para limpiar los campos
    form_pregunta.reset();

    //Contar y mostrar caracteres restantes en el campo pregunta
    contarMostrarCarecteresRestantes('txt_pregunta', 'txaCountPregunta');

    //Elimina cualquier alerta previa en el modal
    var alert_pregunta_modal = document.getElementById('alert_pregunta_modal');
    alert_pregunta_modal.innerHTML = '';

    //Se obtiene el boton que abrio el modal
    var button = event.relatedTarget;

    //Se obtiene atributos del boton
    var id_producto = button.getAttribute('data-bs-id_producto');

    //Asigna el ID de producto al formulario en el modal 
    agregarModal.querySelector('.modal-body #id_producto').value = id_producto;
  });

  
  //Agrega un evento para contar y mostrar caracteres restantes en el campo de pregunta
  document.getElementById("txt_pregunta").addEventListener('input', function() {
    contarMostrarCarecteresRestantes('txt_pregunta', 'txaCountPregunta');
  });



  //Manejo del envio del formulario para hacer otra pregunta al producto
  form_pregunta.addEventListener("submit", (e) => {

    //Previene el envio por defecto del formulario
    e.preventDefault();


    //Elimina cualquier alerta previa 
    var alert_pregunta_modal = document.getElementById("alert_pregunta_modal");
    alert_pregunta_modal.innerHTML = "";

    var pregunta_producto = document.getElementById('txt_pregunta');
    var id_producto = form_pregunta.querySelector('#id_producto');

    //Valida que el campo pregunta no este vacio
    if (validarCampoVacio([pregunta_producto])) {
      alert_pregunta_modal.innerHTML = mensaje_alert_fijo("danger", "Por favor complete el campo pregunta.");
      return false;
    }

    //Valida que los campos ocultos solo contengan numeros
    if (!isNaN(id_producto)) {
      alert_pregunta_modal.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
      return false;
    }

    //Valida que el campo pregunta no tenga espacios al inicio o al final de la cadena
    if (pregunta_producto.value.trim() != pregunta_producto.value) {
      alert_pregunta_modal.innerHTML = mensaje_alert_fijo("danger", "La pregunta no puede tener espacios en blanco al inicio o al final");
      return false;
    }

    //Valida que la longitud del campo sea valida 
    if (!validarCantLengthInput(pregunta_producto)) {
      alert_pregunta_modal.innerHTML = mensaje_alert_fijo("danger", "El campo pregunta debe tener entre 1 y 255 caracteres");
      return false;
    }

    // Envío del formulario usando fetch
    const formData = new FormData(form_pregunta);
    fetch('alta_pregunta.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(datos => {
        if (datos.estado == "success") {

            // Actualiza los datos de las preguntas
            getDataMisPreguntas();

          //Muestra un mensaje en la interfaz del usuario
          alert_notificacion_pregunta.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);


          //Cierra el modal 
          ventanaModalAgregar.hide();
        } else {
          if (datos.estado == "danger") {
            //Muestra un mensaje de error en el Modal
            alert_pregunta_modal.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
          }
        }
      }).catch(e => {
        // Muestra un mensaje error de la solicitud
        alert_pregunta_modal.innerHTML = mensaje_alert_fijo("danger", e);
      });
  });
</script>