<?php
//Archivos de configuracion y funciones necesarias
require("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_notificacion.php");
include("../../config/consultas_bd/consultas_producto.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/funciones/funciones_token.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_session.php");
include("../../config/config_define.php");


$el_usuario_publico = false;
$mensaje_error = "";
$tipo_usuario = "";
$id_usuario = 0;
$imagenes_productos = array();
$producto = array();
$usuario_inicio_sesion = false;
$usuario_valido = false;
$ruta_carpeta = "";
global $url_base_archivos;
$estado = "danger";
$producto_disponible = true;

//Inicializacion de variables obtenidas de la URL
$id_producto = isset($_GET['id']) ? $_GET['id'] : '';
$id_producto_token = isset($_GET['token']) ? $_GET['token'] : '';

try {

  //Se verifica que los datos recibidos de la URL sean validos
  verificarUrlTokenId($id_producto, $id_producto_token);


  // Establecer conexión con la base de datos
  $conexion = obtenerConexionBD();

  //Se obtiene los datos del producto
  $producto = obtenerDatosProducto($conexion, $id_producto);

  //Se obtiene las imagenes del producto
  $imagenes_productos = obtenerListaImgProducto($conexion, $id_producto);

  //Verifica que se recibio todos los datos necesarios
  if (empty($producto) || empty($imagenes_productos)) {
    throw new Exception("No se pudo obtener toda la informacion del producto");
  }

  //Establecer la sesion
  session_start();

  //Verifica los datos de sesion del usuario
  $usuario_inicio_sesion = verificarEntradaDatosSession(['id_usuario', 'tipo_usuario']);


  //Las variables que se utilizan para que el usuario pueda ir a al perfil del emprendedor desde el producto
  $id_usuario_emprendedor_perfil = $producto['id_usuario_emprendedor'];
  $id_usuario_emprendedor_perfil_token = hash_hmac('sha1', $id_usuario_emprendedor_perfil, KEY_TOKEN);

  if ($usuario_inicio_sesion) {
    //Se obtiene los datos de sesion
    $id_usuario = $_SESSION['id_usuario'];
    $tipo_usuario = $_SESSION['tipo_usuario'];

    $usuario_valido = verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario);
    //Verifica si el usuario es valido
    if ($usuario_valido) {

      //Se obtiene el valor si el usuario publico el producto
      $el_usuario_publico = elUsuarioLoPublico($conexion, $id_producto, $id_usuario);

      //Se obtiene los datos del emprendedor 
      $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
      if (!empty($usuario_emprendedor)) {
        //Las variables que se utilizan para que el usuario emprendedor pueda ir a su perfil desde el navbar
        $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];
        $id_usuario_emprendedor_token = hash_hmac('sha1', $id_usuario_emprendedor, KEY_TOKEN);
      }
    }
  }

  //Verifica si el usuario publico el producto
  if (!$el_usuario_publico) {
    //Verifica que el producto no pueda ser visualizado en la interfaz  para eso debe cumplir los siguientes criterios para que no se muestren los datos del producto 
    //El estado del producto no debe estar pausado, la cuenta del usuario debe estar activada y no baneada
    if ($producto['id_estado_producto'] == 2 || !$producto['activado'] || $producto['baneado']) {
      $estado = "info";
      throw new Exception("La publicacion del producto no se encuentra disponible por el momento");
    }
  }


  //Ubicacion de las imagenes del producto
  $ruta_carpeta = " {$url_base_archivos}/uploads/{$producto['id_usuario_emprendedor']}/publicaciones_productos/{$imagenes_productos[0]['nombre_carpeta']}";
} catch (Exception $e) {
  // Capturar cualquier excepción y guardar el mensaje de error
  $mensaje_error = $e->getMessage();
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!--Titulo de la página-->
  <title>Proyecto Emprendedor Detalles del producto</title>

  <!--Enlace al archivo de estilos propios del proyecto-->
  <link href="../../config/css/estilos.css" rel="stylesheet">

  <!--Enlace al archivo de estilos de Bootstrap-->
  <link href="../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!--Enlace al archivo de estilos de FontAwesome para iconos-->
  <link href="../../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">

  <!--Enlace al archivo de estilos de Fancybox-->
  <link href="../../lib/fancyapps-5.0.33/dist/fancybox/fancybox.css" rel="stylesheet" />
  <link href="../../lib/fancyapps-5.0.33/dist/carousel/carousel.css" rel="stylesheet" />
  <link href="../../lib/fancyapps-5.0.33/dist/carousel/carousel.thumbs.css" rel="stylesheet" />


</head>

<body>
  <?php
  //Se obtiene el tipo de navbar que se va usar dependiendo del usuario
  switch ($tipo_usuario) {
    case 1:
      include($url_navbar_usuario_comun);
      break;
    case 2:
      include($url_navbar_usuario_emprendedor);
      break;
    default:
      include($url_navbar_general);
      break;
  }

  ?>

  <!--Separa el contenido principal de la pagina del pie de pagina.-->
  <main>

    <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
    <div class="container-fluid">

      <?php if (empty($mensaje_error)) {    ?>
        <h2 class="titulo text-break"><?php echo ($producto['nombre_producto']); ?></h2>
        <div class="row">
          <!--Carousel con las imagenes del producto-->
          <div class="col-12 col-sm-12 col-md-6 col-lg-6">
            <div class="f-carousel" id="carousel_producto">
              <?php for ($i = 0; $i < count($imagenes_productos); $i++) {
                $nombre_ruta_archivo = $ruta_carpeta . '/' . $imagenes_productos[$i]['nombre_archivo']; ?>
                <div class="f-carousel__slide" data-thumb-src="<?php echo ($nombre_ruta_archivo); ?>">
                  <a data-fancybox='galeria_producto' href='<?php echo ($nombre_ruta_archivo); ?>'>
                    <img class="galeria-img" src="<?php echo ($nombre_ruta_archivo); ?>" />
                  </a>
                </div>
              <?php } ?>
            </div>

            <?php if (count($imagenes_productos) == 1) { ?>

              <div class="f-thumbs f-carousel__thumbs is-classic is-ltr is-horizontal">
                <div class="f-thumbs__viewport">
                  <div class="f-thumbs__track" aria-live="polite" style="transform: matrix(1, 0, 0, 1, 225, 0);">
                    <div class="f-thumbs__slide is-nav-selected" data-index="0"><button class="f-thumbs__slide__button" tabindex="0" type="button" aria-label="Go to slide #1" data-carousel-index="0"><img class="f-thumbs__slide__img" alt="" src="<?php echo ($nombre_ruta_archivo); ?>"></button></div>
                  </div>
                </div>
              </div>
            <?php } ?>

          </div>

          <!--Div que contiene informacion del producto-->
          <div class="col-12 col-sm-12 col-md-6 col-lg-6">
            <h2 class="titulo">Detalles del producto</h2>
            <div class="row">

              <!--Div que contiene la descripcion del producto-->
              <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <p class="text-break"><strong>Descripcion:</strong><?php echo (nl2br($producto['descripcion'])); ?></p>
              </div>


              <!--Div que contiene el estado del producto-->
              <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <p class="text-break"><strong>Estado del producto:</strong> <?php echo ($producto['estado']); ?></p>
              </div>

              <!--Div que contiene la categoria del producto-->
              <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <p class="text-break"><strong>Categoria</strong>: <?php echo ($producto['nombre_categoria']); ?></p>
              </div>

              <!--Div que contiene el stock y el precio del producto-->
              <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <div class="row">

                  <!--Div que contiene el stock disponible del producto-->
                  <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                    <p class="text-break"><strong>Stock Disponible</strong>: <?php echo ($producto['stock']); ?></p>
                  </div>

                  <!--Div que contiene el precio del producto-->
                  <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                    <p class="text-break"><strong>Precio</strong>: $<?php echo (number_format($producto['precio'], 2, '.', ',')); ?></p>
                  </div>
                </div>
              </div>

              <!--Div que contiene la calificacion del producto-->
              <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <p class="text-break"><strong>Calificacion del producto:</strong>

                  <!--Verifica si el producto tiene una calificacion-->
                  <?php if (is_null($producto['calificacion'])) { ?>
                    El producto aun no tiene una calificacion
                    <?php } else {

                    //Agrega estrellas activas para la calificacion actual del producto
                    for ($k = 0; $k < $producto['calificacion']; $k++) { ?>
                      <i class="fas fa-star star-calificacion-activo"></i>
                      <?php  }

                    //Calcula cúantas estrellas faltan para llegar a la calificacion máxima posible. -->
                    $calificacionRestante = $calificacion_max_productos - $producto['calificacion'];
                    if ($calificacionRestante > 0) {
                      //Agregar estrellas vacias para las calificaciones restantes. -->

                      for ($l = 0; $l < $calificacionRestante; $l++) { ?>
                        <i class="fas fa-star star-calificacion"></i>
                  <?php    }
                    }
                  } ?>

                </p>

              </div>



              <!--Div que contiene el boton para redireccionar  al usuario al perfil del propietario del producto-->
              <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <a class="btn btn-outline-primary mb-3 mt-3" id="boton_perfil_emprendedor" href="<?php echo ($url_usuario_emprendedor); ?>/seccion/perfil/pagina_perfil.php?id=<?php echo ($id_usuario_emprendedor_perfil); ?>&token=<?php echo ($id_usuario_emprendedor_perfil_token); ?>"></a>
              </div>
            </div>
          </div>



          <!--Div que contiene un formulario para hacer preguntas a un producto  y un contenedor para mostrar las preguntas recibidas del producto -->
          <div class="col-12 col-sm-12 col-md-12 col-lg-12">
            <h2>Preguntas y respuestas</h2>
            <div class="row">
              <div class="col-12 col-sm-12 col-md-6 col-lg-8">

                <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                <div id="alert_notificacion_pregunta"></div>
                <?php if (!$el_usuario_publico) { ?>
                  <?php if ($producto['id_estado_producto'] == 1) { ?>
                    <p><b>Preguntale al vendedor</b></p>

                    <!--Formulario para enviar preguntas al producto-->
                    <form method="POST" enctype="multipart/form-data" id="form_agregar_pregunta">

                      <!--Div que contiene el campo pregunta -->
                      <div class="form-floating mb-3">
                        <textarea class="form-control" name="txt_pregunta" placeholder="Escriba tu pregunta" id="txt_pregunta" data-max="255" minlength="1" maxlength="255" required style="height: 103px;"></textarea>
                        <label for="txt_pregunta">Escriba su pregunta</label>
                        <span class="form-text">Maximo 255 caracteres.<span id="txaCountPregunta"> 255 restantes</span></span>

                      </div>

                      <button type="submit" class="btn btn-outline-primary">Preguntar</button>
                    </form>
                  <?php } ?>
                <?php } ?>
                <!--Div que contiene las preguntas recibidas del producto -->
                <div id="preguntas_producto"></div>
              </div>
            </div>
          </div>


        </div>

      <?php } else {  ?>
        <div class="alert alert-<?php echo ($estado); ?>" role="alert">
          <?php echo ($mensaje_error); ?>
        </div>
      <?php } ?>
    </div>

  </main>




  <!-- Incluye el pie de pagina, los Modals y varios scripts necesarios para el funcionamiento de la pagina.-->
  <script src="../..//lib/fancyapps-5.0.33/dist/carousel/carousel.umd.js"></script>
  <script src="../..//lib/fancyapps-5.0.33/dist/carousel/carousel.thumbs.umd.js"></script>
  <script src="../..//config/js/funciones.js"></script>
  <script src="../..//lib/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../..//lib/fancyapps-5.0.33/dist/fancybox/fancybox.umd.js"></script>


  <!--Verifica que el usuario emprendedor entro a su perfil-->
  <?php if ($el_usuario_publico) {
    require("../../usuarios/usuario_emprendedor/seccion/respuesta_pregunta/modal_eliminar_respuesta.php");
    require("../../usuarios/usuario_emprendedor/seccion/respuesta_pregunta/modal_agregar_respuesta.php");
  } ?>


  <?php require("../../template/footer.php"); ?>

</body>

</html>



<script>
  var js_producto = <?php echo json_encode(!empty($producto) ? true : false); ?>;
  var js_imagenes_productos = <?php echo json_encode(!empty($imagenes_productos) ? true : false); ?>;
  var js_producto_disponible = <?php echo json_encode($producto_disponible) ?>;
  var js_el_usuario_publico = <?php echo json_encode($el_usuario_publico) ?>;
  var js_usuario_inicio_sesion = <?php echo json_encode($usuario_inicio_sesion) ?>;


  if (js_producto && js_imagenes_productos && js_producto_disponible) {



    // Inicializa Fancybox con el selector de data-fancybox
    Fancybox.bind('[data-fancybox]', {
      // Deshabilita las miniaturas
      Thumbs: false,
      // Configuración de imágenes
      Images: {
        protected: true // Protege las imágenes contra clic derecho y arrastrar
      },
    });

    // Inicializa Carousel con el elemento de ID "carousel_producto"
    new Carousel(document.getElementById("carousel_producto"), {
      // Deshabilita los puntos de navegación
      Dots: false,
      // Configuración de miniaturas
      Thumbs: {
        type: "classic",
      },
    }, {
      Thumbs
    });

    // Carga los datos de las preguntas del producto
    getDataPreguntas();

    var alert_notificacion_pregunta = document.getElementById("alert_notificacion_pregunta");
    boton_perfil_emprendedor = document.getElementById('boton_perfil_emprendedor');

    if (js_el_usuario_publico) {
      //Inicializa los Modals para agregar y eliminar preguntas
      const ventanaModalAgregar = new bootstrap.Modal('#agregarModal');
      const ventanaModalEliminar = new bootstrap.Modal('#eliminaModal');

      boton_perfil_emprendedor.innerHTML = "Ver mi perfil";


      //Agrega un evento cuando se cierra el modal para eliminar una respuesta
      eliminaModal.addEventListener('hide.bs.modal', event => {

        // Obtener el modal para ver una lista de preguntas del producto
        const ventanaModalLista = new bootstrap.Modal('#preguntasRespuestaModal');

        //Abre el modal con las preguntas del producto
        ventanaModalLista.show();

      });

      //Agrega un evento cuando se cierra el modal para responder una pregunta
      agregarModal.addEventListener('hide.bs.modal', event => {

        // Obtener el modal para ver una lista de preguntas del producto
        const ventanaModalLista = new bootstrap.Modal('#preguntasRespuestaModal');

        //Abre el modal con las preguntas del producto
        ventanaModalLista.show();
      });


      //Manejo del envio del formulario para eliminar una respuesta
      form_eliminar_respuesta.addEventListener("submit", (e) => {

        //Previene el envio por defecto del formulario
        e.preventDefault();

        //Elimina cualquier alerta previa 
        alert_eliminar_modal = document.getElementById("alert_eliminar_modal");
        alert_eliminar_modal.innerHTML = "";

        var id_pregunta = form_eliminar_respuesta.querySelector('#id_pregunta');
        var id_producto = form_eliminar_respuesta.querySelector('#id_producto');

        //Valida que los campos ocultos solo contengan numeros
        if (!isNaN(id_pregunta) || !isNaN(id_producto)) {
          alert_eliminar_modal.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
          return false;
        }

        // Envío del formulario usando fetch
        const formData = new FormData(form_eliminar_respuesta);
        fetch('../../usuarios/usuario_emprendedor/seccion/respuesta_pregunta/baja_respuesta.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(datos => {
            if (datos.estado == "success") {

              // Actualiza los datos de las preguntas
              getDataPreguntasProducto(id_producto.value, datos.mensaje);

              //Cierra el modal 
              ventanaModalEliminar.hide();

            } else {
              if (datos.estado == "danger") {
                //Muestra un mensaje de error en el Modal
                alert_eliminar_modal.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
              }
            }
          }).catch(e => {
            // Muestra un mensaje error de la solicitud
            alert_eliminar_modal.innerHTML = mensaje_alert_fijo("danger", e);
          });

      });



      //Manejo del envio del formulario para agregar una respuesta a una pregunta recibida
      form_agregar_respuesta.addEventListener("submit", (event) => {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        alert_respuesta_modal = document.getElementById("alert_respuesta_modal");
        alert_respuesta_modal.innerHTML = "";

        var id_pregunta = form_agregar_respuesta.querySelector('#id_pregunta');
        var id_producto = form_agregar_respuesta.querySelector('#id_producto');


        //Valida que el campo respuesta no este vacio
        if (validarCampoVacio([respuesta_pregunta])) {
          alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", "Por favor complete el campo respuesta");
          return false;
        }

        //Valida que los campos ocultos solo contengan numeros
        if (!isNaN(id_pregunta) || !isNaN(id_producto)) {
          alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros");
          return false;
        }


        //Valida que el campo no tenga espacios al inicio o al final de la cadena
        if (respuesta_pregunta.value.trim() != respuesta_pregunta.value) {
          alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", "La respuesta no puede tener espacios en blanco al inicio o al final");
          return false;
        }

        //Valida que la longitud del campo sea valida 
        if (!validarCantLengthInput(respuesta_pregunta)) {
          alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", "El campo respuesta debe tener entre 1 y 255 caracteres");
          return false;
        }
        // Envío del formulario usando fetch
        const formData = new FormData(form_agregar_respuesta);
        fetch('../../usuarios/usuario_emprendedor/seccion/respuesta_pregunta/alta_respuesta.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(datos => {
            if (datos.estado == "success") {

              // Actualiza los datos de las preguntas
              getDataPreguntasProducto(id_producto.value, datos.mensaje);

              //Cierra el modal 
              ventanaModalAgregar.hide();
            } else {
              if (datos.estado == "danger") {
                //Muestra un mensaje de error en el Modal
                alert_respuesta_modal.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
              }
            }

          }).catch(e => {
            // Muestra un mensaje error de la solicitud
            alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", e);
          });
      });


    } else {

      boton_perfil_emprendedor.innerHTML = "Ver perfil del emprendedor";

      //Agrega un evento para contar y mostrar caracteres restantes en el campo de pregunta
      document.getElementById("txt_pregunta").addEventListener('input', function() {
        contarMostrarCarecteresRestantes('txt_pregunta', 'txaCountPregunta');
      });


      //Manejo del envio del formulario para enviar una pregunta al producto
      form_agregar_pregunta.addEventListener('submit', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();
        //Elimina cualquier alerta previa 
        var alert_notificacion_pregunta = document.getElementById("alert_notificacion_pregunta");
        alert_notificacion_pregunta.innerHTML = "";

        let js_id_producto = <?php echo json_encode($id_producto) ?>;
        var js_usuario_valido = <?php echo json_encode($usuario_valido) ?>;

        var txt_pregunta = document.getElementById("txt_pregunta");

        //Verifica que el usuario inicio sesion para hacer una pregunta
        if (!js_usuario_inicio_sesion) {
          alert_notificacion_pregunta.innerHTML = mensaje_alert_fijo("danger", "Debe iniciar sesion para poder hacer una pregunta.");
          return false;
        }

        //Verifica que el usuario es valido 
        if (!js_usuario_valido) {
          alert_notificacion_pregunta.innerHTML = mensaje_alert_fijo("danger", "El usuario debe ser valido para poder hacer preguntas");
          return false;
        }

        //Valida que el campo pregunta no este vacio
        if (validarCampoVacio([txt_pregunta])) {
          alert_notificacion_pregunta.innerHTML = mensaje_alert_fijo("danger", "Por favor complete el campo pregunta");
          return false;
        }


        //Valida que el campo pregunta no tenga espacios al inicio o al final de la cadena
        if (txt_pregunta.value.trim() != txt_pregunta.value) {
          alert_notificacion_pregunta.innerHTML = mensaje_alert_fijo("danger", "La pregunta no puede tener espacios en blanco al inicio o al final");
          return false;
        }

        //Valida que la longitud del campo sea valida 
        if (!validarCantLengthInput(txt_pregunta)) {
          alert_notificacion_pregunta.innerHTML = mensaje_alert_fijo("danger", "El campo pregunta debe tener entre 1 y 255 caracteres");
          return false;
        }


        // Envío del formulario usando fetch
        const formData = new FormData(form_agregar_pregunta);
        formData.append('id_producto', js_id_producto);
        fetch('../../usuarios/preguntas/alta_pregunta.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(datos => {
            if (datos.estado == "success") {

              //Muestra un mensaje en la interfaz del usuario
              alert_notificacion_pregunta.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);

              // Actualiza los datos de las preguntas
              getDataPreguntas();

              //Resetea el formulario para limpiar los campos
              form_agregar_pregunta.reset();

              //Contar y mostrar caracteres restantes en el campo pregunta
              contarMostrarCarecteresRestantes('txt_pregunta', 'txaCountPregunta');


            } else {
              if (datos.estado == "danger") {
                //Muestra un mensaje de error en el Modal
                alert_notificacion_pregunta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
              }
            }

          }).catch(e => {
            // Muestra un mensaje error de la solicitud
            alert_notificacion_pregunta.innerHTML = mensaje_alert_fijo("danger", e);
          });
      });

    }


  }


  //Función para obtener y cargar datos de las preguntas y respuesta del producto
  function getDataPreguntas() {
    preguntas_producto = document.getElementById("preguntas_producto");
    fetch(`lista_pregunta.php${window.location.search}`, {})
      .then(respuesta => respuesta.json())
      .then(datos => {
        if (datos.estado == "danger") {
          // Muestra un mensaje de alerta si hubo un error
          alert_notificacion_pregunta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
        } else {
          //Actualizar el contenedor de las preguntas y respuestas
          preguntas_producto.innerHTML = datos.pregunta;
        }
      }).catch(e => {
        // Muestra un mensaje error de la solicitud
        alert_notificacion_pregunta.innerHTML = mensaje_alert_fijo("danger", e);
      });
  }


  //Función para obtener y cargar los datos de preguntas y respuesta de un producto
  function getDataPreguntasProducto(id_producto, mensaje) {
    // Envío del formulario usando fetch
    const formData = new FormData();
    formData.append('id_producto', id_producto);
    fetch('../../usuarios/usuario_emprendedor/seccion/producto/lista_pregunta_producto.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(datos => {

        //Se obtiene el modal con las preguntas de un determinado producto
        var modalLista = document.getElementById('preguntasRespuestaModal');

        if (datos.estado == "success") {
          //Se actualiza el body del modal con la nueva lista  de preguntas y respuesta
          modalLista.querySelector('.modal-body').innerHTML = datos.lista;

          //Se obtiene alert del modal 
          alert_preguntas_respuesta = modalLista.querySelector('#alert_preguntas_respuesta');

          //Muestra un mensaje en la interfaz del usuario
          alert_preguntas_respuesta.innerHTML = mensaje_alert_dismissible(datos.estado, mensaje);

        } else {
          //Se obtiene alert del modal 
          alert_preguntas_respuesta = modalLista.querySelector('#alert_preguntas_respuesta');

          // Muestra un mensaje de alerta si hubo un error
          alert_preguntas_respuesta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
        }
      }).catch(e => {
        // Muestra un mensaje error de la solicitud
        alert_preguntas_respuesta.innerHTML = mensaje_alert_fijo("danger", e);
      });
  }
</script>