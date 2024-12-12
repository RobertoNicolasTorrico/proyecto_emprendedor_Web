<!--Modal para agregar una respuesta -->
<div class="modal fade" id="agregarModal" tabindex="-1" aria-labelledby="agregarModalLabel" aria-hidden="false" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!--Header del modal -->
      <div class="modal-header">
        <h5 class="modal-title" id="agregarModalLabel">Responder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!--Formulario dentro del modal-->
      <form method="POST" enctype="multipart/form-data" id="formulario_agregar_respuesta">
        <!--Body del modal -->
        <div class="modal-body">

          <!--Div para mostrar errores al responder la pregunta -->
          <div id="alert_respuesta_modal"></div>

          <!--Div para mostrar el contenido de la pregunta que va a responder -->
          <div id="label_mensaje_agregar"></div>

          <!--Campos ocultos para almacenar el ID de pregunta y el ID del producto -->
          <input type="number" name="id_pregunta" id="id_pregunta" hidden required>
          <input type="number" name="id_producto" id="id_producto" hidden required>

          <!--Campo para ingresar la respuesta-->
          <label for="respuesta_pregunta" class="col-form-label"><strong>Respuesta</strong></label>
          <textarea type="text" class="form-control" name="respuesta_pregunta" id="respuesta_pregunta" placeholder="Respuesta" minlength="1" maxlength="255" data-max="255" required></textarea>

          <!--Mostrar la cantidad de caracteres permitidos y restantes-->
          <p>Maximo 255 caracteres.<span id="txaCountRespuesta">255 restantes</span></p>

        </div>

        <!--Footer del modal -->
        <div class="modal-footer">
          <!--Boton para cerrar el modal y cancelar la operacion -->
          <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>

          <!--Boton para enviar la respuesta-->
          <button type="submit" class="btn btn-outline-success">Enviar respuesta</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Se obtiene el formulario para agregar una respuesta
  var form_agregar_respuesta = document.getElementById("formulario_agregar_respuesta");

  // Se obtiene el modal de agregar respuesta
  const agregarModal = document.getElementById('agregarModal');

  //Agrega un evento cuando se muestra el modal
  agregarModal.addEventListener('show.bs.modal', event => {


    //Resetea el formulario para limpiar los campos
    form_agregar_respuesta.reset();

    //Contar y mostrar caracteres restantes en el campo respuesta
    contarMostrarCarecteresRestantes('respuesta_pregunta', 'txaCountRespuesta');


    //Elimina cualquier alerta previa en el modal
    var alert_respuesta_modal = document.getElementById('alert_respuesta_modal');
    alert_respuesta_modal.innerHTML = '';

    //Elimina cualquier mensaje previa en el modal
    var label_mensaje_agregar = document.getElementById('label_mensaje_agregar');
    label_mensaje_agregar.innerHTML = '';

    //Se obtiene el boton que abrio el modal
    var button = event.relatedTarget;

    //Se obtiene atributos del boton
    var pregunta = button.getAttribute('data-bs-pregunta');
    var fecha = button.getAttribute('data-bs-fecha');
    var nombre = button.getAttribute('data-bs-nombre');
    var id_pregunta = button.getAttribute('data-bs-id_pregunta');
    var id_producto = button.getAttribute('data-bs-id_producto');

    //Mostrar el nombre de usuario, pregunta y la fecha en el modal
    document.getElementById("label_mensaje_agregar").innerHTML = '<p class="text-break"><strong>Nombre de usuario:</strong>' + nombre +
      '</p> <p class="text-break"><strong>Pregunta:</strong>' + pregunta +
      '<span style="color: #8a8a8a;">(' + fecha + ')</span></p>';

    //Asigna el ID de pregunta y el ID de producto al formulario en el modal 
    agregarModal.querySelector('.modal-body #id_pregunta').value = id_pregunta;
    agregarModal.querySelector('.modal-body #id_producto').value = id_producto;
  });




  //Agrega un evento para contar y mostrar caracteres restantes en el campo de respuesta
  document.getElementById('respuesta_pregunta').addEventListener('input', function() {
    contarMostrarCarecteresRestantes('respuesta_pregunta', 'txaCountRespuesta');
  });
</script>