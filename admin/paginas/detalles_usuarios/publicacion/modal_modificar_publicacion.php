<!-- Modal para modificar la publicación -->
<div class="modal fade" id="modal_modificar_publicacion" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modal_modificar_publicacionLabel" aria-hidden="true">

    <div class="modal-dialog">
        <div class="modal-content">


            <!-- Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal_modificar_publicacionLabel">Modificar publicacion</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Formulario dentro del modal -->
            <form method="POST" enctype="multipart/form-data" id="formulario_modificar_publicacion" novalidate>


                <!-- Body del modal -->
                <div class="modal-body">

                    <!-- Div para mostrar errores al modificar la publicación -->
                    <div id="alert_notificacion_modificar_publicacion"></div>

                    <!--Campos ocultos para almacenar el ID de publicacion -->
                    <input type="number" name="id_publicacion_modificar" id="id_publicacion_modificar" hidden required>


                    <!-- Div que contiene el campo de fecha de publicaciones-->
                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                        <div class="input-group">
                            <span class="input-group-text">Fecha de publicacion:</span>
                            <input type="datetime-local" name="fecha_modificar_publicacion" id="fecha_modificar_publicacion" placeholder="Fecha de Publicacion" class="form-control" required>
                        </div>
                    </div>


                    <!-- Div que contiene el campo descripcion y muestra la cantidad maxima de caracteres permitidos-->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-floating mb-3" id="grupo_txtDescripcion">
                        <textarea class="form-control" name="txt_descripcion_modificar" placeholder="Descripcion" id="txt_descripcion_modificar" data-max="255" minlength="1" maxlength="255" style="height: 100px" required></textarea>
                        <label for="txt_descripcion_modificar">Descripcion</label>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                            <span class="form-text">Maximo 255 caracteres.<span id="txaCountDescripModificar"></span></span>
                        </div>
                    </div>


                    <!-- Div para cargar archivos (imágenes/videos) -->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-3" id="div_grupo_archivo_modificar" style="display:none;">
                        <div class="formulario_grupo g-2">
                            <button type="button" id="boton_cerrar_grupo_archivo_modificar" class="boton_cerrar"><i class="fa-solid fa-x"></i></button>
                            <p style="padding-top: .75rem">Lista de imagenes de videos</p>
                            <div id="grupo_archivo_modificar" class="grupo_archivo">
                                <p class="archivo_div_text_modificar">Arratre los archivos a esta zona o <label id="label_Archivo" for="input_archivos_modificar" class="form-label"><strong> haga clic aqui</strong></label></p>
                                <input type="file" class="input_archivos" name="input_archivos_modificar" id="input_archivos_modificar" multiple accept="image/jpeg, image/jpg, image/png,video/mp4,video/mkv, video/avi" />
                                <div class="formulario_grupo-input div_archivos" id="div_archivo_modificacion"></div>
                            </div>
                            <p id="mensaje_error_archivo_modificar" class="mensaje_error_archivo"></p>
                        </div>
                        <span class="form-text">Maximo 5 archivos(Imagen limite 10 MB,Video limite 100 MB)</span>
                    </div>


                    <!-- Div para agregar la ubicación en el mapa y un boton para actualizar la ubicacion de la publicacion-->
                    <div id="div_grupo_map_modificar" class="grupo_map" style="display:none;">
                        <button id="boton_cerrar_grupo_maps_modificar" type="button" class="boton_cerrar"><i class="fa-solid fa-x"></i></button>
                        <p>Mi ubicacion actual</p>
                        <div class="grupo_map-error invalid-map-activo" id="error_map" style="display:none;"></div>
                        <div id="div_map_publicaciones_modificar" style="height: 300px"></div>
                        <button id="boton_maps_actualizar_ubicacion" type="button" class="btn btn-outline-primary m-2" style="display:none;">Actualizar Ubicacion</button>
                    </div>


                    <!-- Opciones para agregar archivos o ubicación -->
                    <div class="row">
                        <div class="col-5">
                            <p>Agregar a la publicación</p>
                        </div>
                        <div class="col-6">
                            <button type="button" id="boton_grupo_archivo_modificar" class="boton_galeria" role="button" data-bs-toggle="button" aria-pressed="false"><i class="fa-solid fa-photo-film"></i></button>
                            <button type="button" id="boton_grupo_map_modificar" class="boton_map" role="button" data-bs-toggle="button" aria-pressed="false"><i class="fa-solid fa-map-location-dot"></i></button>
                        </div>
                    </div>

                </div>

                <!-- Footer del modal -->
                <div class="modal-footer">

                    <!-- Botón para cerrar el modal y cancelar la operacion -->
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>

                    <!-- Botón para modificar la publicación -->
                    <button type="submit" class="btn btn-outline-success">Guardar cambios</button>
                </div>


            </form>
        </div>
    </div>
</div>
<script>
    //Lista de archivos que ya existen en la base de datos
    var lista_archivos_modificar_bd = [];

    //Copia de la lista de archivos en la base de datos
    var copia_lista_archivos_modificar_bd = [];

    //Lista de nuevos archivos a agregar
    var lista_archivos_modificar = [];

    //Nuevos de datos para la publicacion
    var latitud = null;
    var longitud = null;
    var descripcion = null;


    //Datos ya existente en la publicacion 
    var latitud_bd = null;
    var longitud_bd = null;
    var descripcion_bd = null;
    var fecha_bd_publicacion = null;


    var fecha_modificar_publicacion = document.getElementById('fecha_modificar_publicacion');
    var txt_descripcion_modificar = document.getElementById('txt_descripcion_modificar');


    const modal_modificar_publicacion = document.getElementById('modal_modificar_publicacion');

    // Se obtiene el formulario para modificar la publicacion
    var formulario_modificar_publicacion = document.getElementById("formulario_modificar_publicacion");


    //Se obtiene los elementos que utliza los archivos
    var boton_cerrar_grupo_archivo_modificar = document.getElementById("boton_cerrar_grupo_archivo_modificar");
    var input_archivos_modificar = document.getElementById("input_archivos_modificar");
    var boton_cerrar_grupo_maps_modificar = document.getElementById('boton_cerrar_grupo_maps_modificar');
    var div_archivo_modificacion = document.getElementById('div_archivo_modificacion');
    var div_grupo_archivo_modificar = document.getElementById('div_grupo_archivo_modificar');
    var grupo_archivo_modificar = document.getElementById("grupo_archivo_modificar");
    var mensaje_error_archivo_modificar = document.getElementById('mensaje_error_archivo_modificar');


    //Se obtiene los elementos que utliza maps
    var div_grupo_map_modificar = document.getElementById('div_grupo_map_modificar');
    var boton_maps_actualizar_ubicacion = document.getElementById('boton_maps_actualizar_ubicacion');
    var div_map_publicaciones_modificar = document.getElementById("div_map_publicaciones_modificar");
    var div_error_maps = document.getElementById('error_map');
    var archivo_div_text_modificar = document.querySelector('.archivo_div_text_modificar');


    //Variables para modificar las publicaciones del usuario
    var ruta;
    const cant_min = 1;
    const cant_max = 5;



    //Funciona para comparar los datos originales con los datos actuales de la publicacion
    function compararDatosOriginalesConFormularioModificar() {

        var fecha_hora_formateada = devolverFechaDateTimeLocalInput(fecha_modificar_publicacion);

        // Comprobación de la fecha
        const fechaIgual = (fecha_bd_publicacion === fecha_hora_formateada);

        // Comprobación de la descripcion
        const descripcionIgual = (descripcion_bd === txt_descripcion_modificar.value);

        // Comprobación de coordenadas
        const coordenadasIguales = (latitud_bd === latitud && longitud_bd === longitud);

        // Comprobación de archivos
        const archivosIguales = (lista_archivos_modificar_bd.length === lista_archivos_modificar.length && lista_archivos_modificar.length === 0 && lista_archivos_modificar_bd.every(valor => lista_archivos_modificar.includes(valor)));

        // Comprobación general
        return descripcionIgual && coordenadasIguales && archivosIguales && fechaIgual;
    }

    //Agrega un evento para contar y mostrar caracteres restantes en el campo de descripcion
    document.getElementById('txt_descripcion_modificar').addEventListener('input', function() {
        contarMostrarCarecteresRestantes('txt_descripcion_modificar', 'txaCountDescripModificar');
    });


    // Manejar el cambio en el input de archivos
    input_archivos_modificar.addEventListener('change', function() {

        //Elimina cualquier alerta previa 
        mensaje_error_archivo_modificar.innerHTML = '';

        var archivos_validos = [];

        //Se obtiene los archivos agregados
        var archivos = input_archivos_modificar.files;

        //Se suma la cantidad de archivos que tiene originalmente la publicacion, los nuevos archivos subidos por el usuario y los que va agregar
        var cantidadTotal = archivos.length + lista_archivos_modificar.length + copia_lista_archivos_modificar_bd.length;

        //Verifica que el archivo(imagen/video) sea valido
        archivos_validos = validarArchivoImagenVideoPublicacion(cantidadTotal, cant_min, cant_max, archivos, 'mensaje_error_archivo_modificar');

        if (archivos_validos.length > 0) {

            //Se agrega los archivos validos para mostrar  
            lista_archivos_modificar.push(...archivos_validos);

            //Actualiza la vista de la lista de archivos 
            var lista_Video_Imagenes_bd = obtenerListaImagenesVideosBD(copia_lista_archivos_modificar_bd, ruta);
            var lista_Video_Imagenes = obtenerListaImagenesVideosInput(lista_archivos_modificar, "Modificar");
            div_archivo_modificacion.innerHTML = lista_Video_Imagenes_bd + lista_Video_Imagenes;
        }
    });

    // Manejar el evento de soltar archivos en el div
    grupo_archivo_modificar.addEventListener('drop', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        mensaje_error_archivo_modificar.innerHTML = '';

        grupo_archivo_modificar.classList.remove('dragover');

        //Se obtiene los archivos agregados
        var archivos = event.dataTransfer.files;
        var archivos_validos = [];
        //Se suma la cantidad de archivos que tiene originalmente la publicacion, los nuevos archivos subidos por el usuario y los que va agregar
        var cantidadTotal = archivos.length + lista_archivos_modificar.length + copia_lista_archivos_modificar_bd.length;
        archivo_div_text_modificar.innerHTML = 'Arratre los archivos a esta zona o <label id="label_Archivo" for="input_archivos_modificar" class="form-label"><strong> haga clic aqui</strong></label>';

        //Verifica que el archivo(imagen/video) sea valido
        archivos_validos = validarArchivoImagenVideoPublicacion(cantidadTotal, cant_min, cant_max, archivos, 'mensaje_error_archivo_modificar');
        if (archivos_validos.length > 0) {

            //Se agrega los archivos validos para mostrar  
            lista_archivos_modificar.push(...archivos_validos);

            //Actualiza la vista de la lista de archivos 
            var lista_Video_Imagenes_bd = obtenerListaImagenesVideosBD(copia_lista_archivos_modificar_bd, ruta);
            var lista_Video_Imagenes = obtenerListaImagenesVideosInput(lista_archivos_modificar, "Modificar");
            div_archivo_modificacion.innerHTML = lista_Video_Imagenes_bd + lista_Video_Imagenes;
        }

    });

    // Manejar el evento de arrastrar sobre el div
    grupo_archivo_modificar.addEventListener('dragover', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        grupo_archivo_modificar.classList.add('dragover');
        archivo_div_text_modificar.innerHTML = 'Suelta los archivos aca';
    });


    // Manejar el evento de salir del div arrastrando
    grupo_archivo_modificar.addEventListener('dragleave', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();
        grupo_archivo_modificar.classList.remove('dragover');

        // Obtiene los archivos arrastrados
        archivo_div_text_modificar.innerHTML = 'Arratre los archivos a esta zona o <label id="label_Archivo" for="input_archivos_modificar" class="form-label"><strong> haga clic aqui</strong></label>';
    });


    //Agrega un evento cuando se muestra el modal
    modal_modificar_publicacion.addEventListener('shown.bs.modal', event => {

        //Se obtiene el boton que abrio el modal
        var button = event.relatedTarget;

        //Se obtiene atributos del boton
        var id_publicacion_modificar = button.getAttribute('data-bs-id_publicacion');
        lista_archivos_modificar_bd = JSON.parse(button.getAttribute('data-bs-lista-imagenes'));
        descripcion_bd = button.getAttribute('data-bs-descripcion');
        ruta = button.getAttribute('data-bs-ruta');
        latitud_bd = (button.getAttribute('data-bs-latitud').length > 0) ? button.getAttribute('data-bs-latitud') : null;
        longitud_bd = (button.getAttribute('data-bs-longitud').length > 0) ? button.getAttribute('data-bs-longitud') : null;
        fecha_bd_publicacion = button.getAttribute('data-bs-fecha');

        fecha_modificar_publicacion.value = fecha_bd_publicacion;

        document.getElementById("id_publicacion_modificar").value = id_publicacion_modificar;
        txt_descripcion_modificar.value = descripcion_bd;

        //Contar y mostrar caracteres restantes en el campo descripcion
        contarMostrarCarecteresRestantes('txt_descripcion_modificar', 'txaCountDescripModificar');

        //Muestra archivos(imagenes/videos) si la publicacion tienes archivos
        if (lista_archivos_modificar_bd.length > 0) {
            abrir_ventana_archivo_modificar();
        }

        //Muestra la ubicacion actual de la publicacion
        if (latitud_bd != null && longitud_bd != null) {
            abrir_ventana_map_modificar();
        }

    });


    //Agrega un evento cuando se cierra el modal
    modal_modificar_publicacion.addEventListener('hide.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        formulario_modificar_publicacion.reset();
        descripcion_bd = null;
        fecha_bd_publicacion = null

        //Elimina cualquier alerta previa en el modal
        alert_notificacion_modificar_publicacion.innerHTML = '';

        //Llama las funciones para cerrar el div de archivos y mapa
        cerrar_ventana_archivo_modificar();
        cerrar_ventana_map_modificar();
    });


    //Funcion para eliminar un archivo de la lista de archivos de la base de datos
    function eliminarArchivoListaArchivoBD(num_archivo) {

        //Elimina cualquier alerta previa 
        mensaje_error_archivo_modificar.innerHTML = '';

        //Elimina el archivo de la lista utilizando su indice
        copia_lista_archivos_modificar_bd.splice(num_archivo, 1);

        //Actualiza la vista de la lista de archivos de la base de datos
        var lista_Video_Imagenes_bd = obtenerListaImagenesVideosBD(copia_lista_archivos_modificar_bd, ruta);
        var lista_Video_Imagenes = obtenerListaImagenesVideosInput(lista_archivos_modificar, "Modificar");
        div_archivo_modificacion.innerHTML = lista_Video_Imagenes_bd + lista_Video_Imagenes;

    }

    //Funcion para eliminar un archivo de la lista de archivos agregados
    function eliminarArchivoListaArchivoInputModificar(num_archivo) {

        //Elimina cualquier alerta previa 
        mensaje_error_archivo_modificar.innerHTML = '';

        //Crea una URL temporal para el archivo a eliminar
        const url = URL.createObjectURL(lista_archivos_modificar[num_archivo]);

        //Elimina el archivo de la lista utilizando su indice
        lista_archivos_modificar.splice(num_archivo, 1);

        //Se revoca la URL temporal del archivo para liberar la memoria
        URL.revokeObjectURL(url);


        //Actualiza la vista de la lista de archivos 
        var lista_Video_Imagenes_bd = obtenerListaImagenesVideosBD(copia_lista_archivos_modificar_bd, ruta);
        var lista_Video_Imagenes = obtenerListaImagenesVideosInput(lista_archivos_modificar, "Modificar");
        div_archivo_modificacion.innerHTML = lista_Video_Imagenes_bd + lista_Video_Imagenes;
    }



    //Funcion utilizada para abrir el div de archivos
    function abrir_ventana_archivo_modificar() {

        // Deshabilita el botón de agregar archivos
        boton_grupo_archivo_modificar.disabled = true;

        // Limpia los mensajes de error y resetea el estado del botón
        div_grupo_archivo_modificar.style.display = "block";
        boton_grupo_archivo_modificar.ariaPressed = "true";
        boton_grupo_archivo_modificar.classList.add("active");

        //En caso que tenga archivos guardados en la base de datos se agregan al div de archivos
        if (lista_archivos_modificar_bd.length > 0) {
            //Actualiza la vista de la lista de archivos de la base de datos
            copia_lista_archivos_modificar_bd = lista_archivos_modificar_bd.slice();
            div_archivo_modificacion.innerHTML = obtenerListaImagenesVideosBD(copia_lista_archivos_modificar_bd, ruta);
        } else {
            //Actualiza la vista de la lista de archivos de las nuevas imagenes y con la lista de archivos de la base de datos
            var lista_Video_Imagenes_bd = obtenerListaImagenesVideosBD(copia_lista_archivos_modificar_bd, ruta);
            var lista_Video_Imagenes = obtenerListaImagenesVideosInput(lista_archivos_modificar);
            div_archivo_modificacion.innerHTML = lista_Video_Imagenes_bd + lista_Video_Imagenes;
        }
    }


    //Funcion utilizada para abrir el div de maps
    function abrir_ventana_map_modificar() {

        // Deshabilita el botón de agregar archivos
        boton_grupo_map_modificar.disabled = true;

        // Limpia los mensajes de error y resetea el estado del botón
        div_grupo_map_modificar.style.display = "block";
        boton_grupo_map_modificar.ariaPressed = "true";
        boton_grupo_map_modificar.classList.add("active");
        div_error_maps.innerHTML = ``;
        div_error_maps.style.display = "none";
        div_map_publicaciones_modificar.style.display = "block";

        //Verifica que la publicacion original tenga latitud y longitud guardado
        if (latitud_bd != null && longitud_bd != null) {

            //Guarda la latitude y longitude de la publicacion original
            latitud = latitud_bd;
            longitud = longitud_bd;

            //Muestra la ubicacion en el mapa
            verUbicacionMapPublicacionesModal(latitud_bd, longitud_bd, 'div_map_publicaciones_modificar', "Ubicacion actual de la publicacion");

            boton_maps_actualizar_ubicacion.style.display = "block";
        } else {
            // Verifica si la geolocalización es compatible con el navegador
            if (navigator.geolocation) {

                // Obtiene la posición actual del usuario y llama a las funciones de éxito o error
                navigator.geolocation.getCurrentPosition(successMapsModificar, errorMapsModificar);
            } else {
                //LLama una funcion que muestra un mensaje de error si no se puede obtener la ubicacion
                mensajeErrorMaps("La geolocalización no es compatible con su navegador");
            }
        }
    }

    //Funcion de exito para obtener la ubicacion
    function successMapsModificar(position) {

        //Guarda la latitude y longitude obtenidas
        latitud = position.coords.latitude;
        longitud = position.coords.longitude;

        //Muestra la ubicacion en el mapa
        verUbicacionMapPublicacionesModal(latitud, longitud, 'div_map_publicaciones_modificar', "Mi ubicacion actual");
    }


    //Muestra un mensaje de error si no se puede obtener la ubicacion ademas de eliminar la informacion de la latitud y longitud 
    function mensajeErrorMaps(mensaje) {
        div_error_maps.innerHTML = '<p>' + mensaje + '</p>';
        div_error_maps.style.display = "block";
        div_map_publicaciones_modificar.style.display = "none";

        latitud = null;
        longitud = null;
    }




    //Funcion de error al obtener la ubicacion
    function errorMapsModificar(err) {
        var mensaje = "";

        //Determina el mensaje de error segun el tipo de error
        switch (err.code) {
            case err.PERMISSION_DENIED:
                mensaje = "Debe permitir el acceso a su posición para que la aplicación pueda funcionar";
                break;
            case err.POSITION_UNAVAILABLE:
                mensaje = "La información sobre su posición actual no está disponible";
                break;
            case err.TIMEOUT:
                mensaje = "No he podido obtener su posición en un tiempo razonable";
                break;
            default:
                mensaje = "Se ha producido un error desconocido al intentar obtener la posición actual";
                break;
        }

        //LLama una funcion que muestra un mensaje de error si no se puede obtener la ubicacion
        mensajeErrorMaps(mensaje);
    }


    // Función para cerrar el div de archivos
    function cerrar_ventana_archivo_modificar() {
        // Habilita el botón de agregar archivos y limpia el input de archivos
        boton_grupo_archivo_modificar.disabled = false;
        copia_lista_archivos_modificar_bd = [];
        lista_archivos_modificar = [];

        // Limpia los mensajes de error y resetea el estado del botón
        mensaje_error_archivo_modificar.innerHTML = '';
        boton_grupo_archivo_modificar.classList.remove('active');
        boton_grupo_archivo_modificar.ariaPressed = "false";
        div_grupo_archivo_modificar.style.display = "none";

    }

    // Función para cerrar el div de maps
    function cerrar_ventana_map_modificar() {
        // Habilita el botón de agregar maps y oculta el div de maps
        boton_grupo_map_modificar.disabled = false;
        div_grupo_map_modificar.style.display = "none";

        // Resetea el estado del botón y las coordenadas del mapa
        boton_grupo_map_modificar.classList.remove('active');
        boton_grupo_map_modificar.ariaPressed = "false";
        boton_maps_actualizar_ubicacion.style.display = "none";
        latitud = null;
        longitud = null;
    }

    //Cerrar div de archivos
    boton_cerrar_grupo_archivo_modificar.addEventListener('click', function() {
        // Llama a la función para cerrar el div de archivos
        cerrar_ventana_archivo_modificar();
    });


    //Cerrar div de maps
    boton_cerrar_grupo_maps_modificar.addEventListener('click', function() {

        // Llama a la función para cerrar el div de mapas
        cerrar_ventana_map_modificar();
    });


    //Agrega un evento para abrir div de archivos
    boton_grupo_archivo_modificar.addEventListener('click', function() {
        // Llama a la función para abrir el div de maps
        abrir_ventana_archivo_modificar();
    });

    //Agrega un evento para abrir div de maps
    boton_grupo_map_modificar.addEventListener('click', function() {
        // Llama a la función para abrir el div de maps
        abrir_ventana_map_modificar();
    });

    //Agrega un evento para actualizar la ubicacion de la publicacion
    boton_maps_actualizar_ubicacion.addEventListener('click', function() {

        // Verifica si la geolocalización es compatible con el navegador
        if (navigator.geolocation) {

            // Obtiene la posición actual del usuario y llama a las funciones de éxito o error
            navigator.geolocation.getCurrentPosition(successMapsModificar, errorMapsModificar);
        } else {

            // Muestra un mensaje de error si la geolocalización no es compatible
            mensajeErrorMaps("La geolocalización no es compatible con su navegador");
        }
    });




    //Manejo del envio del formulario para modificar la publicacion
    formulario_modificar_publicacion.addEventListener('submit', function(event) {


        //Previene el envio por defecto del formulario
        event.preventDefault();

        //Elimina cualquier alerta previa 
        mensaje_error_archivo_modificar.innerHTML = '';
        alert_notificacion_modificar_publicacion.innerHTML = '';


        var id_publicacion_modificar = document.getElementById('id_publicacion_modificar');
        var fecha_hora_formateada_publicacion = devolverFechaDateTimeLocalInput(fecha_modificar_publicacion);

        //Valida que el campo descripcion no este vacio
        if (validarCampoVacio([txt_descripcion_modificar])) {
            alert_notificacion_modificar_publicacion.innerHTML = mensaje_alert_fijo("danger", "Por favor complete el campo descripcion");
            return false;
        }
        console.log(fecha_hora_formateada_publicacion)

        //Verifica que el campo fecha de publicacion no este vacio
        if (fecha_hora_formateada_publicacion.length == 0) {
            alert_notificacion_modificar_publicacion.innerHTML = mensaje_alert_fijo("danger", "El campo fecha de publicacion no puede estar vacio");
            return false;
        }


        //Valida que el campo no tenga espacios al inicio o al final de la cadena
        if (txt_descripcion_modificar.value.trim() != txt_descripcion_modificar.value) {
            alert_notificacion_modificar_publicacion.innerHTML = mensaje_alert_fijo("danger", "La descripcion no puede tener espacios en blanco al inicio o al final");
            return false;
        }


        //Valida que la longitud del campo sea valida 
        if (!validarCantLengthInput(txt_descripcion_modificar)) {
            alert_notificacion_modificar_publicacion.innerHTML = mensaje_alert_fijo("danger", "El campo descripcion debe tener entre 1 y 255 caracteres");
            return false;
        }


        //Valida que los campos ocultos solo contengan numeros
        if (!isNaN(id_publicacion_modificar)) {
            alert_eliminar_modal.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
            return false;
        }


        //Verifica que si el div de mapa este activo 
        if (boton_grupo_map_modificar.disabled) {

            //En caso que este activo y los valores de latitud o longitud sea NULL mostraria un mensaje de error
            if (longitud == null || latitud == null) {
                alert_notificacion_modificar_publicacion.innerHTML = mensaje_alert_fijo("danger", "No se pudo obtener los datos necesarios para actualizar su ubicacion. Por favor cierre la ventana mapa para poder continuar");
                return false;
            }
        }


        //Verifica que si el div de archivos este activo 
        if (boton_grupo_archivo_modificar.disabled) {

            //Se suma la cantidad total de archivos eliminados y los nuevos archivos verificando que la cantidad sea valido
            cantidad_total = lista_archivos_modificar.length + copia_lista_archivos_modificar_bd.length

            //Verifica que la cantidad de archivos en la publicacion sea valido
            if (!validadCantidadArchivos(cantidad_total, cant_min, cant_max)) {
                alert_notificacion_modificar_publicacion.innerHTML = mensaje_alert_fijo("danger", "La cantidad de archivos no cumplen con los requisitos.Debe ser al menos " + cant_min + " archivo " + "y como máximo " + cant_max + " archivos");
                return false;
            }
        }



        //Se llama a una funcion para comparar cambios con los datos originales de la publicacion
        if (compararDatosOriginalesConFormularioModificar()) {
            alert_notificacion_modificar_publicacion.innerHTML = mensaje_alert_fijo("info", "No hubo cambios en la publicacion");
            return false;
        }

        // Envío del formulario usando fetch
        const formData = new FormData(formulario_modificar_publicacion);
        formData.append('fecha_modificada', fecha_hora_formateada_publicacion);
        formData.append('latitud', latitud);
        formData.append('longitud', longitud);

        for (var file of lista_archivos_modificar) {
            formData.append('files[]', file);
        }
        for (var nombre_file of copia_lista_archivos_modificar_bd) {
            formData.append('nombres_files_bd[]', nombre_file);
        }


        fetch(`publicacion/modificar_publicacion.php${window.location.search}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado === 'success') {

                    descripcion_bd = txt_descripcion_modificar.value;
                    fecha_bd_publicacion = fecha_hora_formateada_publicacion;

                      // Actualiza los datos de publicacion
                      getDataPublicacion();


                    //Muestra un mensaje en la interfaz del usuario
                    alert_notificacion_modificar_publicacion.innerHTML = mensaje_alert_dismissible(datos.estado, datos.mensaje);
                } else {

                    //Muestra un mensaje en la interfaz del usuario
                    alert_notificacion_modificar_publicacion.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }
            })
            .catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_notificacion_modificar_publicacion.innerHTML = mensaje_alert_fijo("danger", e);
            });

    });
</script>

