<?php
include("../../../../config/consultas_bd/consultas_notificacion.php");
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/config_define.php");

global $url_base;
$imagenes_productos = array();
$producto = array();
$estado = "danger";
$mensaje_error = "";
$ruta_carpeta = "";
$id_usuario = "";
$token_usuario = "";


//Inicializacion de variables obtenidas de la URL
$id_producto = isset($_GET['id']) ? $_GET['id'] : '';
$id_producto_token = isset($_GET['token']) ? $_GET['token'] : '';
try {

  //Establecer la sesion
  session_start();

  // Establecer conexión con la base de datos
  $conexion = obtenerConexionBD();

  //Verifica que los datos de sesion sean de un usuario administrador y que sea un usuario valido
  verificarDatosSessionUsuarioAdministrador($conexion);


  //Se verifica que los datos recibidos de la URL sean validos
  verificarUrlTokenId($id_producto, $id_producto_token);

  //Se obtiene los datos  del producto
  $producto = obtenerDatosProducto($conexion, $id_producto);

  //Se obtiene los datos de las imagenes del producto
  $imagenes_productos = obtenerListaImgProducto($conexion, $id_producto);

  //Verifica que se recibio todos los datos necesarios
  if (empty($producto) || empty($imagenes_productos)) {
    throw new Exception("No se pudo obtener toda la informacion del producto debido a que fue eliminado o la cuenta del usuario fue eliminada");
  }


  //Obtener Id del usuario y convertir token la id del usuario
  $id_usuario = $producto['id_usuario'];
  $token_usuario = hash_hmac('sha1', $id_usuario, KEY_TOKEN);


  //Ubicacion de las imagenes del producto
  $ruta_carpeta = "{$url_base}/uploads/{$producto['id_usuario_emprendedor']}/publicaciones_productos/{$imagenes_productos[0]['nombre_carpeta']}";
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
  <title>Proyecto Emprendedor Admin Detalles Producto</title>

  <!--Enlace al archivo de estilos propios del proyecto-->
  <link href="../../../../config/css/estilos.css" rel="stylesheet">

  <!--Enlace al archivo de estilos de Bootstrap-->
  <link href="../../../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!--Enlace al archivo de estilos de FontAwesome para iconos-->
  <link href="../../../../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">

  <!--Enlace al archivo de estilos de Fancybox-->
  <link href="../../../../lib/fancyapps-5.0.33/dist/fancybox/fancybox.css" rel="stylesheet" />
  <link href="../../../../lib/fancyapps-5.0.33/dist/carousel/carousel.css" rel="stylesheet" />
  <link href="../../../../lib/fancyapps-5.0.33/dist/carousel/carousel.thumbs.css" rel="stylesheet" />

</head>

<body>
  <!--Incluye el archivo de la barra de navegación para usuarios administrador.-->
  <?php include($url_navbar_usuario_admin);  ?>

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
                <p class="text-break"><strong>Descripcion:</strong> <?php echo (nl2br($producto['descripcion'])); ?> </p>
              </div>

              <!--Div que contiene la fecha de publicacion producto-->
              <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <p class="text-break"><strong>Fecha de publicacion</strong>: <?php echo ($producto['fecha_publicacion']); ?></p>
              </div>


              <?php if (!empty($producto['fecha_modificación'])) { ?>

                <!--Div que contiene la fecha de modificacion producto-->
                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                  <p class="text-break"><strong>Fecha de modificacion</strong>: <?php echo ($producto['fecha_modificación']); ?></p>
                </div>

              <?php } ?>

              <!--Div que contiene el estado del producto-->
              <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <p class="text-break"><strong>Estado de la publicacion</strong>: <?php echo ($producto['estado']); ?></p>
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




              <div class="col-12 col-sm-12 col-md-12 col-lg-12">

                <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                <div id="alert_notificacion_pregunta"></div>

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
  <script src="../../../../lib/fancyapps-5.0.33/dist/carousel/carousel.umd.js"></script>
  <script src="../../../../lib/fancyapps-5.0.33/dist/carousel/carousel.thumbs.umd.js"></script>
  <script src="../../../../config/js/funciones.js"></script>
  <script src="../../../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../../../../lib/fancyapps-5.0.33/dist/fancybox/fancybox.umd.js"></script>
  <?php require("../preguntas_respuesta/modal_eliminar_respuesta.php"); ?>
  <?php require("../../../../template/footer.php"); ?>

</body>

</html>



<script>

  var js_producto = <?php echo json_encode(!empty($producto) ? true : false); ?> ;
  var js_imagenes_productos = <?php echo json_encode(!empty($imagenes_productos) ? true : false); ?> ;


  if (js_producto && js_imagenes_productos) {

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
    const ventanaModalEliminarPreguntasRecibidas = new bootstrap.Modal(eliminarModalPreguntasRecibidas);

    //Agrega un evento cuando se cierra el modal para eliminar una respuesta
    eliminarModalPreguntasRecibidas.addEventListener('hide.bs.modal', event => {

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
      var alert_eliminar_modal_respuesta = document.getElementById("alert_eliminar_modal_respuesta");
      alert_eliminar_modal_respuesta.innerHTML = "";


      var id_pregunta = form_eliminar_respuesta.querySelector('#id_pregunta_recibida');
      var id_producto = form_eliminar_respuesta.querySelector('#id_pregunta_producto');


      //Valida que los campos ocultos solo contengan numeros
      if (!isNaN(id_pregunta) || !isNaN(id_producto)) {
        alert_eliminar_modal_respuesta.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
        return false;
      }

      // Envío del formulario usando fetch
      const formData = new FormData(form_eliminar_respuesta);

      var js_id_usuario = <?php echo json_encode($id_usuario); ?>;
      var js_token_usuario = <?php echo json_encode($token_usuario); ?>;

      fetch(`../preguntas_respuesta/baja_respuesta.php?id=${js_id_usuario}&token=${js_token_usuario}`, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(datos => {
          if (datos.estado == "success") {

            // Actualiza los datos de las preguntas y respuesta dentro del modal
            getDataPreguntasProducto(id_producto.value, datos.mensaje);

            //Cierra el modal 
            ventanaModalEliminarPreguntasRecibidas.hide();

          } else {
            if (datos.estado == "danger") {
              //Muestra un mensaje de error en el Modal
              alert_eliminar_modal_respuesta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
            }
          }

        }).catch(e => {
          // Muestra un mensaje error de la solicitud
          alert_eliminar_modal_respuesta.innerHTML = mensaje_alert_fijo("danger", e);
        });

    });

  }

  //Función para obtener y cargar datos de las preguntas y respuesta del producto
  function getDataPreguntas() {
    preguntas_producto = document.getElementById("preguntas_producto");
    fetch(`lista_pregunta.php${window.location.search}`, {})
      .then(respuesta => respuesta.json())
      .then(datos => {
        if (datos.estado == "danger") {
          // Muestra un mensaje de alerta si hubo un error
          alert_preguntas_respuesta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
        } else {
          preguntas_producto.innerHTML = datos.pregunta;
        }
      }).catch(e => {
        // Muestra un mensaje error de la solicitud
        alert_preguntas_respuesta.innerHTML = mensaje_alert_fijo("danger", e);
      });
  }

  //Función para obtener y cargar los datos de preguntas y respuesta de un producto
  function getDataPreguntasProducto(id_producto, mensaje) {
    // Envío del formulario usando fetch
    const formData = new FormData();
    formData.append('id_producto', id_producto);
    fetch(`lista_pregunta_producto.php${window.location.search}`, {
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