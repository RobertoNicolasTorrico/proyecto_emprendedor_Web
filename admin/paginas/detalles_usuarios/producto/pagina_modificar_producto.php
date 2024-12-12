<?php

//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/consultas_bd/consultas_notificacion.php");
include("../../../../config/consultas_bd/consultas_categoria.php");
include("../../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/config_define.php");


//Inicializacion de variables obtenidas de la URL
$id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : '';
$id_producto = isset($_GET['id_producto']) ? $_GET['id_producto'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';


global $url_base;
global $url_usuario_admin;
$mensaje_error = "";
$producto =  array();
$lista_imagenes = array();
$categorias_producto = array();
try {

  //Establecer la sesion
  session_start();

  // Establecer conexión con la base de datos
  $conexion = obtenerConexionBD();

  //Verifica que los datos de sesion sean de un usuario administrador y que sea un usuario valido
  verificarDatosSessionUsuarioAdministrador($conexion);


  //Se verifica que los datos recibidos de la URL sean validos
  verificarUrlTokenId($id_producto, $token);


  //Verifica si la cuenta del usuario sigue existe
  if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
    throw new Exception("La cuenta del usuario que publico el producto fue eliminada previamente. Por favor valla a otra pagina del sistema");
  }


  //Se obtiene los datos del emprendedor
  $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
  if (empty($usuario_emprendedor)) {
    throw new Exception("No se pudo obtener la informacion del usuario emprendedor. Por favor intente mas tarde");
  }

  //Las variables se utilizan para que el usuario emprendedor pueda ir a su perfil desde el navbar
  $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];
  $id_usuario_emprendedor_token = hash_hmac('sha1', $id_usuario_emprendedor, KEY_TOKEN);


  //Se obtiene las categorias disponibles 
  $categorias_producto = obtenerCategoriasProducto($conexion);

  //Se obtiene loss estados disponibles
  $estados_producto = obtenerEstadosProducto($conexion);



  //Se obtiene los datos del producto del emprendedor
  $producto = obtenerProductoDelUsuarioEmprendedor($conexion, $id_producto, $id_usuario_emprendedor);
  if (empty($producto)) {
    throw new Exception("No se puede modificar los productos que no le pertenecen al mismo usuario");
  }

  //Se obtiene los datos de las imagenes del producto
  $imagenes = obtenerListaImgProducto($conexion, $id_producto);
  if (empty($imagenes)) {
    throw new Exception("No se pudo obtener los datos necesarios para modificar el producto");
  }



  //Se agrega el nombre del archivo a la lista de imagenes
  foreach ($imagenes as $imagen) {
    array_push($lista_imagenes, $imagen['nombre_archivo']);
  }

  //Ubicacion de los detalles de usuario 
  $url_volver = $url_usuario_admin . "/paginas/detalles_usuarios/pagina_detalles_usuario.php?id=" . $id_usuario . "&token=" . hash_hmac('sha1', $id_usuario, KEY_TOKEN);


  //Ubicacion de las imagenes del producto
  $ruta_imagenes = $url_base . "/uploads/" . $producto['id_usuario_emprendedor'] . "/publicaciones_productos/" . $imagenes[0]['nombre_carpeta'];
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
  <title>Proyecto Emprendedor Admin Modificar Producto</title>

  <!--Enlace al archivo de estilos propios del proyecto-->
  <link href="../../../../config/css/estilos.css" rel="stylesheet">

  <!--Enlace al archivo de estilos de Bootstrap-->
  <link href="../../../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!--Enlace al archivo de estilos de FontAwesome para iconos-->
  <link href="../../../../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">


</head>

<body>
  <!--Incluye el archivo de la barra de navegación para usuarios administrador.-->
  <?php include($url_navbar_usuario_admin);  ?>

  <!--Separa el contenido principal de la pagina del pie de pagina.-->
  <main>

    <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
    <div class="container mb-4">

      <?php if (empty($mensaje_error)) { ?>
        <div class="row justify-content-center">
          <div class="col-12 col-sm-12 col-md-9 col-lg-9">


            <!-- Card -->
            <div class="card">

              <!-- Header del Card -->
              <div class="card-header">
                <h5 class="text-center">Modificar datos del producto</h5>
              </div>


              <!-- Body del Card -->
              <div class="card-body">


                <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                <div id="alert_modificar_producto"></div>

                <!--Formulario para enviar los datos-->
                <form class="row" id="formulario_modificar_producto" enctype="multipart/form-data">
                  <div class="col-12 col-sm-12 col-md-12 col-lg-5">
                    <div class="row g-2">


                      <!-- Select para elegir el estado del producto -->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                        <select class="form-select" name="select_estado_producto" id="select_estado_producto" aria-label="Floating_label_select" required>
                          <option value=" " selected disabled>Elige una de las siguientes opciones</option>
                          <?php
                          foreach ($estados_producto as $estado) {
                            if ($producto['id_estado_producto'] == $estado['id_estado_producto']) { ?>
                              <option selected value="<?php echo $estado['id_estado_producto']; ?>"><?php echo $estado['estado']; ?></option>
                            <?php } else {  ?>
                              <option value="<?php echo $estado['id_estado_producto']; ?>"><?php echo $estado['estado']; ?></option>
                          <?php }
                          }
                          ?>
                        </select>
                        <label for="select_estado_producto">Estado de la publicacion de producto</label>
                      </div>

                      <!-- Div que contiene el campo nombre del producto y muestra la cantidad maxima de caracteres -->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3" id="grupo_txtNombre">
                        <input type="text" class="form-control" name="txt_nombre" id="txt_nombre" value="<?php echo ($producto['nombre_producto']); ?>" placeholder="Nombre del producto" minlength="1" maxlength="80" data-max="80" required>
                        <label for="txt_nombre">Nombre del producto</label>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                          <span class="form-text">Maximo 80 caracteres.<span id="txaCountNombre">80 restantes</span></span>
                        </div>
                      </div>

                      <!-- Div que contiene el campo descripcion de producto y muestra la cantidad maxima de caracteres -->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3" id="grupo_txtDescripcion">
                        <textarea class="form-control" name="txt_descripcion" placeholder="Descripcion del producto" id="txt_descripcion" data-max="1000" minlength="1" maxlength="1000" rows="10" cols="50" required style="height: 130px;"><?php echo ($producto['descripcion']); ?></textarea>
                        <label for="txt_descripcion">Descripcion del producto</label>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                          <span class="form-text">Maximo 1000 caracteres.<span id="txaCountDescripcion">1000 restantes</span></span>
                        </div>
                      </div>

                      <!-- Select para elegir que categoria tiene el producto -->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3">
                        <select class="form-select" name="select_categoria_producto" id="select_categoria_producto" aria-label="Floating_label_select" required>
                          <option value=" " selected disabled>Eliga una de las siguientes opciones</option>

                          <?php foreach ($categorias_producto as $categoria) {
                            if ($producto['id_categoria_producto'] == $categoria['id_categoria_producto']) { ?>
                              <option selected value="<?php echo $categoria['id_categoria_producto']; ?>"><?php echo $categoria['nombre_categoria']; ?></option>
                            <?php } else {  ?>
                              <option value="<?php echo $categoria['id_categoria_producto']; ?>"><?php echo $categoria['nombre_categoria']; ?></option>

                          <?php }
                          } ?>
                        </select>
                        <label for="select_categoria_producto">Seleccione que categoria de producto</label>
                      </div>



                      <!-- Div que contiene el campo el stock disponible del producto-->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-6 form-floating mb-3" id="grupo_txtStock">
                        <input type="number" class="form-control" name="txt_stock" id="txt_stock" value="<?php echo ($producto['stock']); ?>" placeholder="Stock" step="1" min="0" max="2147483647" required>
                        <label for="txt_stock">Stock</label>
                      </div>


                      <!-- Div que contiene el campo el precio disponible del producto-->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-6 form-floating mb-3" id="grupo_txtPrecio">
                        <input type="number" class="form-control" name="txt_precio" id="txt_precio" value="<?php echo ($producto['precio']); ?>" placeholder="Precio" step="0.01" min="0" required>
                        <label for="txt_precio">Precio</label>
                      </div>

                    </div>
                  </div>


                  <!-- Div para cargar imagenes -->
                  <div class="col-12 col-sm-12 col-md-12 col-lg-7 mb-3">
                    <div class="formulario_grupo g-2">
                      <p style="padding-top: .75rem">Imagenes para el producto</p>
                      <div id="grupo_archivo_modificar_producto" class="grupo_archivo">
                        <p class="archivo_div_text">Arratre los archivos a esta zona o <label id="label_Archivo" for="input_archivos" class="form-label"><strong> haga clic aqui</strong></label>(Limite 10 MB)</p>
                        <input type="file" class="input_archivos" name="input_archivos" id="input_archivos" multiple accept="image/jpeg, image/jpg, image/png" />
                        <div class="formulario_grupo-input div_archivos" id="div_archivos_modificar_producto"></div>
                      </div>
                      <p id="mensaje_error_archivo" class="mensaje_error_archivo"></p>
                      <span class="form-text">Adjunta al menos una imagen y hasta un maximo de cinco</span>

                    </div>


                    <!-- Div que contiene el campo fecha de publicacion-->
                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3 mt-3">
                      <div class="input-group">
                        <span class="input-group-text">Fecha de publicacion:</span>
                        <input type="datetime-local" name="fecha_publicacion" id="fecha_publicacion" placeholder="Fecha registro" class="form-control" value="<?php echo ($producto['fecha_publicacion']); ?>">
                      </div>
                    </div>


                    <!-- Div que contiene el campo fecha de modificaciones-->
                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                      <div class="input-group">
                        <span class="input-group-text">Fecha de modificacion:</span>
                        <input type="datetime-local" name="fecha_modificacion" id="fecha_modificacion" placeholder="Fecha modificacion" class="form-control" value="<?php echo ($producto['fecha_modificación']); ?>">
                      </div>
                    </div>


                    <!-- Div que contiene la calificacion del producto-->
                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                      <div class="input-group">
                        <span class="input-group-text" for="num_calificacion">Calificacion del producto:</span>
                        <select class="form-select" name="num_calificacion" id="num_calificacion">
                          <?php if (is_null(value: $producto['calificacion'])) { ?>
                            <option value="null" selected>Sin calificacion</option>
                            <?php for ($i = 0; $i <= 5; $i++) {  ?>
                              <option value="<?php echo ($i) ?>"><?php echo ($i) ?></option>

                            <?php  }   ?>

                          <?php  } else { ?>

                            <option value="null">Sin calificacion</option>
                            <?php for ($i = 0; $i <= 5; $i++) {  ?>
                              <?php if ($producto['calificacion'] == $i) { ?>
                                <option value="<?php echo ($i) ?>" selected><?php echo ($i) ?></option>

                              <?php  } else { ?>
                                <option value="<?php echo ($i) ?>"><?php echo ($i) ?></option>

                              <?php  }   ?>

                            <?php  }   ?>

                          <?php  }   ?>
                        </select>
                      </div>
                    </div>


                  </div>
                  <!-- Div que contiene dos botones uno para modificar el producto y otro para ir a la lista de productos el usuario -->
                  <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                    <button class="btn btn-outline-success" type="submit">Guardar Cambios</button>
                    <a class="btn btn-outline-danger" href="<?php echo ($url_volver) ?>">Cancelar </a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php } else {  ?>
        <div class="alert alert-danger" role="alert">
          <?php echo ($mensaje) ?>
        </div>
      <?php } ?>
    </div>
  </main>

  <!-- Incluye el pie de pagina, los Modals y varios scripts necesarios para el funcionamiento de la pagina.-->
  <script src="../../../../config/js/funciones.js"></script>
  <script src="../../../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
  <?php require("../../../../template/footer.php"); ?>



</body>

</html>



<script>
  //Inicializa la variables que almacenan los datos de la categoria, productos y la ruta donde se guardan las imagenes
  const js_categorias_producto = <?php echo json_encode($categorias_producto); ?>;
  const js_producto = <?php echo json_encode($producto); ?>;
  const ruta = <?php echo json_encode($ruta_imagenes); ?>;

  //Lista de archivos que ya existen en la base de datos
  var lista_archivos_bd = <?php echo json_encode($lista_imagenes); ?>;

  //Lista de nuevos archivos a agregar
  var lista_archivos = [];


  //Cantidad minima y maxima de archivos permitidos
  const cant_min = 1;
  const cant_max = 5;


  if (js_categorias_producto.length > 0 && lista_archivos_bd.length > 0 && Object.keys(js_producto).length > 0) {


    //Se obtiene los elementos que utliza los archivos
    var div_archivos_modificar_producto = document.getElementById('div_archivos_modificar_producto');
    var grupo_archivo_modificar_producto = document.getElementById("grupo_archivo_modificar_producto");
    var imputArchivos = document.getElementById("input_archivos");
    var archivo_div_text = document.querySelector('.archivo_div_text');
    var alert_modificar_producto = document.getElementById('alert_modificar_producto');
    var mensaje_error_archivo = document.getElementById("mensaje_error_archivo");

    // Se obtiene el formulario para modificar el producto
    var form_modificar_producto = document.getElementById("formulario_modificar_producto");

    //Actualiza la vista de la lista de archivos con las imagenes de que tenian guardado 
    div_archivos_modificar_producto.innerHTML = obtenerListaImagenesBD(lista_archivos_bd, ruta);



    //Funcion para mostrar la cantidad restantes de caracteres en el campo de nombre del producto
    contarMostrarCarecteresRestantes('txt_nombre', 'txaCountNombre');

    //Funcion para mostrar la cantidad restantes de caracteres en el campo descripcion del producto
    contarMostrarCarecteresRestantes('txt_descripcion', 'txaCountDescripcion');

    //Agrega un evento para contar y mostrar caracteres restantes en el campo de nombre del producto
    document.getElementById('txt_nombre').addEventListener('input', function() {
      contarMostrarCarecteresRestantes('txt_nombre', 'txaCountNombre');
    });

    //Agrega un evento para contar y mostrar caracteres restantes en el campo de descripcion
    document.getElementById('txt_descripcion').addEventListener('input', function() {
      contarMostrarCarecteresRestantes('txt_descripcion', 'txaCountDescripcion');
    });


    // Manejar el cambio en el input de archivos
    imputArchivos.addEventListener('change', function() {
      var archivos_validos = [];

      //Se obtiene los archivos agregados
      var archivos = imputArchivos.files;

      //Se suma la cantidad de archivos que tiene originalmente la publicacion, los nuevos archivos subidos por el usuario y los que va agregar
      var cantidadTotal = archivos.length + lista_archivos.length + lista_archivos_bd.length;

      //Verifica que la imagen sea valido
      archivos_validos = validarArchivoImagenProducto(cantidadTotal, cant_min, cant_max, archivos);

      if (archivos_validos.length > 0) {

        //Se agrega los archivos validos para mostrar  
        lista_archivos.push(...archivos_validos);

        //Actualiza la vista de la lista de imagenes 
        var lista_imagenes_bd = obtenerListaImagenesBD(lista_archivos_bd, ruta);
        var lista_imagenes = obtenerListaImagenesInput(lista_archivos);
        div_archivos_modificar_producto.innerHTML = lista_imagenes_bd + lista_imagenes;
      }

    });


    // Manejar el evento de soltar archivos en el div
    grupo_archivo_modificar_producto.addEventListener('drop', function(event) {

      //Previene el envio por defecto del formulario
      event.preventDefault();
      grupo_archivo_modificar_producto.classList.remove('dragover');
      var archivos_validos = [];

      //Se obtiene los archivos agregados
      var archivos = event.dataTransfer.files;

      archivo_div_text.innerHTML = 'Arratre los archivos a esta zona o <label id="label_Archivo" for="input_archivos" class="form-label"><strong> haga clic aqui</strong></label>(Limite 10 MB)';

      //Se suma la cantidad de archivos que tiene originalmente la publicacion, los nuevos archivos subidos por el usuario y los que va agregar
      var cantidadTotal = archivos.length + lista_archivos.length + lista_archivos_bd.length;

      // Valida los archivos arrastrados
      archivos_validos = validarArchivoImagenProducto(cantidadTotal, cant_min, cant_max, archivos);
      if (archivos_validos.length > 0) {

        //Se agrega los archivos validos para mostrar  
        lista_archivos.push(...archivos_validos);

        //Actualiza la vista de la lista de archivos 
        var lista_imagenes_bd = obtenerListaImagenesBD(lista_archivos_bd, ruta);
        var lista_imagenes = obtenerListaImagenesInput(lista_archivos);
        div_archivos_modificar_producto.innerHTML = lista_imagenes_bd + lista_imagenes;
      }
    });



    grupo_archivo_modificar_producto.addEventListener('dragover', function(event) {
      event.preventDefault();
      grupo_archivo_modificar_producto.classList.add('dragover');
      archivo_div_text.innerHTML = 'Suelta los archivos aca(Limite 10 MB)';
    });

    // Manejar el evento de arrastrar sobre el div
    grupo_archivo_modificar_producto.addEventListener('dragleave', function(event) {

      //Previene el envio por defecto del formulario
      event.preventDefault();
      grupo_archivo_modificar_producto.classList.remove('dragover');
      archivo_div_text.innerHTML = 'Arratre los archivos a esta zona o <label id="label_Archivo" for="input_archivos" class="form-label"><strong> haga clic aqui</strong></label>(Limite 10 MB)';
    });


    //Manejo del envio del formulario para modificar el producto
    form_modificar_producto.addEventListener('submit', function(event) {

      //Previene el envio por defecto del formulario
      event.preventDefault();

      //Elimina cualquier alerta previa 
      mensaje_error_archivo.innerHTML = "";

      var txt_nombre = document.getElementById('txt_nombre');
      var txt_descripcion = document.getElementById('txt_descripcion');
      var select_categoria_producto = document.getElementById('select_categoria_producto');
      var select_estado_producto = document.getElementById('select_estado_producto');
      var fecha_modificacion = document.getElementById('fecha_modificacion');
      var fecha_publicacion = document.getElementById('fecha_publicacion');
      var select_calificacion = document.getElementById('num_calificacion');

      var txt_stock = document.getElementById('txt_stock');
      var txt_precio = document.getElementById('txt_precio');
      var campos_verificar = [txt_nombre, txt_descripcion];
      var campos_verificar_num = [txt_stock, txt_precio];
      var cantidadTotalArchivos = lista_archivos.length + lista_archivos_bd.length;


      var fecha_hora_formateada_publicacion = devolverFechaDateTimeLocalInput(fecha_publicacion);
      var fecha_hora_formateada_modificacion = devolverFechaDateTimeLocalInput(fecha_modificacion);


      //Se compara los datos originales del producto con los datos actuales para saber si hubo un cambio o no
      if (compararDatosOriginalesConFormulario()) {
        alert_modificar_producto.innerHTML = mensaje_alert_fijo("info", "No hubo cambios en los datos del producto");
        return false;
      }


      //Valida que los campos no esten vacios
      if (validarCampoVacio(campos_verificar) || validarCampoVacio(campos_verificar_num) || validarCampoVacio([select_categoria_producto, select_estado_producto]) || cantidadTotalArchivos < cant_min) {
        alert_modificar_producto.innerHTML = mensaje_alert_fijo("danger", "Por favor complete todos los campos");
        return false;
      }

      //Verifica que el campo fecha de publicacion no este vacio
      if (fecha_publicacion.value.length == 0) {
        alert_modificar_producto.innerHTML = mensaje_alert_fijo("danger", "El campo fecha de publicacion no puede estar vacio");
        return false;
      }


      //Verifica que el campo fecha de modificacion si esta vacio o no
      if (fecha_modificacion.value.length > 0) {
        const fechainputPublicacion = new Date(fecha_publicacion.value);
        const fechainputModificacion = new Date(fecha_modificacion.value);
        if (fechainputPublicacion > fechainputModificacion) {
          alert_modificar_producto.innerHTML = mensaje_alert_fijo("danger", "La fecha de publicacion no puede ser posterior a la fecha de modificacion");
          return false;
        }
      }

      //Valida que los campos no tengan espacios al inicio o al final de la cadena
      var lista_trim_input = listaInputEspacioBlancoIF(campos_verificar);
      if (lista_trim_input.length > 0) {
        alert_modificar_producto.innerHTML = mensaje_alert_lista_fijo("danger", "No se permite que los campos tengan espacios en blanco al inicio o al final. Los siguientes campos no cumplen con esto:", lista_trim_input);
        return false;
      }

      //Verifica que el campo select de la calificacion tenga un valor valido
      if (!validarCampoSelectCalificacion(select_calificacion)) {
        alert_modificar_producto.innerHTML = mensaje_alert_fijo("danger", "Los valores en el select fueron modificados");
        return false;
      }

      //Verifica que la cantidad de archivos en la publicacion del producto sea valido
      if (!validadCantidadArchivos(cantidadTotalArchivos, cant_min, cant_max)) {
        alert_modificar_producto.innerHTML = mensaje_alert_fijo("danger", "La cantidad de imagenes no cumplen con los requisitos.Debe ser al menos " + cant_min + " imagen " + "y como máximo " + cant_max + " imagenes");
        return false;
      }

      //Verifica que el valor del stock sea uno valido 
      if (validarCampoNumericoEntero(txt_stock.value)) {
        alert_modificar_producto.innerHTML = mensaje_alert_fijo("danger", "El stock maximo que puede tener un producto es 2147483647");
        return false;
      }

      //Verifica que los campos stock y precio del producto tengan valores numeros
      var lista_num_input = listaInputValorNoNumerico(campos_verificar_num);
      if (lista_num_input.length > 0) {
        alert_modificar_producto.innerHTML = mensaje_alert_lista_fijo("danger", "Solo se permite ingresar valores numericos en los siguientes campos:", lista_num_input);
        return false;
      }


      //Verifica que los campos stock y precio del producto tengan valores numeros positivos
      var lista_num_input_rango = listaInputNumNoPositivo(campos_verificar_num);
      if (lista_num_input_rango.length > 0) {
        alert_modificar_producto.innerHTML = mensaje_alert_lista_fijo("danger", "Solo se permite ingresar valores numericos positivos los siguientes campos no cumplen eso: ", lista_num_input_rango);
        return false;
      }

      //Verifica que los campos stock y precio del producto tengan una longitud valida
      var lista_length_input = listaInputLengthNoValidos(campos_verificar);
      if (lista_length_input.length > 0) {
        alert_modificar_producto.innerHTML = mensaje_alert_lista_fijo("danger", "Los siguientes campos no cumplen con la longitud mínima de 1 carácter o el máximo de caracteres indicado:", lista_length_input);
        return false;
      }


      //Verifica que el valor de categoria elegido no halla sido modificado por el usuario
      if (!validarCampoCategoriaProducto(select_categoria_producto, js_categorias_producto)) {
        alert_modificar_producto.innerHTML = mensaje_alert_fijo("danger", "Los valores en el select fueron modificados");
        return false;
      }


      // Envío del formulario usando fetch
      var formData = new FormData();
      formData.append('fecha_publicacion', fecha_hora_formateada_publicacion);
      formData.append('fecha_modificada', fecha_hora_formateada_modificacion);
      formData.append('select_calificacion', select_calificacion.value.trim());
      formData.append('nombre_producto', txt_nombre.value.trim());
      formData.append('descripcion', txt_descripcion.value.trim());
      formData.append('categoria_producto', select_categoria_producto.value.trim());
      formData.append('stock', txt_stock.value.trim());
      formData.append('precio', txt_precio.value.trim());
      formData.append('estado_producto', select_estado_producto.value.trim());
      for (var file of lista_archivos) {
        formData.append('files[]', file);
      }

      for (var nombre_file of lista_archivos_bd) {
        formData.append('nombres_files_bd[]', nombre_file);
      }

      fetch(`modificar_producto.php${window.location.search}`, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(datos => {
          if (datos.lista.length > 0) {
            alert_modificar_producto.innerHTML = mensaje_alert_lista_fijo(datos.estado, datos.mensaje, datos.lista);
          } else {
            if (datos.estado === 'success') {
              //Muestra un mensaje en la interfaz del usuario
              alert_modificar_producto.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);


              js_producto['fecha_publicacion'] = fecha_hora_formateada_publicacion;
              js_producto['fecha_modificación'] = fecha_hora_formateada_modificacion;
              js_producto['calificacion'] = select_calificacion.value;
              js_producto['nombre_producto'] = txt_nombre.value;
              js_producto['descripcion'] = txt_descripcion.value;
              js_producto['id_categoria_producto'] = select_categoria_producto.value;
              js_producto['id_estado_producto'] = select_estado_producto.value;
              js_producto['stock'] = txt_stock.value;
              js_producto['precio'] = txt_precio.value;


            } else {
              //Muestra un mensaje en la interfaz del usuario
              alert_modificar_producto.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
            }

          }
        })
        .catch(e => {
          // Muestra un mensaje error de la solicitud
          alert_modificar_producto.innerHTML = mensaje_alert_fijo("danger", e);
        });

    });




    //Funcion para eliminar un archivo de la lista de archivos agregados
    function eliminarImagenListaArchivoInput(num_img) {
      //Se llama a una funcion para eliminar un archivo del input

      eliminaArchivoInput(num_img);

      //Actualiza la vista de la lista de archivos 
      var lista_imagenes_bd = obtenerListaImagenesBD(lista_archivos_bd, ruta);
      var lista_imagenes = obtenerListaImagenesInput(lista_archivos);
      div_archivos_modificar_producto.innerHTML = lista_imagenes_bd + lista_imagenes;

    }


    //Funcion para comparar los datos originales del producto con los nuevo datos ingresados
    function compararDatosOriginalesConFormulario() {
      var fecha_hora_formateada_publicacion = devolverFechaDateTimeLocalInput(fecha_publicacion);
      var fecha_hora_formateada_modificacion = devolverFechaDateTimeLocalInput(fecha_modificacion);
      var select_calificacion = document.getElementById('num_calificacion');


      //Comprobacion de la fecha de publicacion
      const fechaIgualPublicacion = (fecha_hora_formateada_publicacion === js_producto['fecha_publicacion']);

      //Comprobacion de la fecha de modificacion
      const fechaIgualModificacion = ((fecha_hora_formateada_modificacion === js_producto['fecha_modificación']) || (fecha_hora_formateada_modificacion == '' && js_producto['fecha_modificación'] == null));

      const calificacion = (js_producto['calificacion'] == select_calificacion.value || (js_producto['calificacion'] == null && select_calificacion.value == 'null'));
      // Comprobación de nombre
      const nombreIgual = txt_nombre.value == js_producto['nombre_producto'];

      // Comprobación de descripción
      const descripcionIgual = txt_descripcion.value == js_producto['descripcion'];

      // Comprobación de categoría
      const categoriaIgual = select_categoria_producto.value == js_producto['id_categoria_producto'];

      // Comprobación de estado
      const estadoIgual = select_estado_producto.value == js_producto['id_estado_producto'];

      // Comprobación de stock
      const stockIgual = txt_stock.value == js_producto['stock'];

      // Comprobación de precio
      const precioIgual = txt_precio.value == js_producto['precio'];

      // Comprobación de archivos
      const archivosIguales = JSON.stringify(lista_archivos_bd) == JSON.stringify(<?php echo json_encode($lista_imagenes); ?>);

      const nuevosArchivos = lista_archivos.length == 0;
      // Comprobación general
      return nombreIgual && nuevosArchivos && calificacion && descripcionIgual && categoriaIgual && estadoIgual && stockIgual && precioIgual && archivosIguales && fechaIgualPublicacion && fechaIgualModificacion;
    }


  }
</script>