<!-- Modal para agregar una publicación -->
<div class="modal fade" id="modal_agregar_publicacion" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modal_agregar_publicacionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal_agregar_publicacionLabel">Crear publicación</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Formulario dentro del modal -->
            <form method="POST" enctype="multipart/form-data" id="formulario_agregar_publicacion">

                <!-- Body del modal -->
                <div class="modal-body">

                    <!-- Div para mostrar errores al hacer una nueva publicación -->
                    <div id="alert_notificacion_agregar_publicacion"></div>

                    <div class="col-12">
                        <p>Las publicaciones pueden ser modificadas y eliminadas en cualquier momento por el usuario.</p>
                    </div>


                    <!-- Div que contiene el campo descripcion y muestra la cantidad maxima de caracteres -->
                    <div class="col-12 form-floating mb-3">

                        <!-- Campo para la descripción de la publicación -->
                        <textarea style="height:150px;" class="form-control" name="txt_descripcion_agregar" placeholder="Descripción" id="txt_descripcion_agregar" minlength="1" maxlength="255" data-max="255" required></textarea>
                        <label for="txt_descripcion_agregar">Descripción</label>
                        <div class="col-12">
                            <span class="form-text">Máximo 255 caracteres.<span id="txaCountDescripAgregar">255 restantes</span></span>
                        </div>
                    </div>

                    <!-- Div para cargar archivos (imágenes/videos) -->
                    <div class="col-12 mb-3" id="div_grupo_archivo_agregar" style="display:none;">
                        <div class="formulario_grupo g-2">
                            <button type="button" class="boton_cerrar" id="boton_cerrar_grupo_archivo_agregar"><i class="fa-solid fa-x"></i></button>
                            <p style="padding-top: .75rem">Lista de imágenes y videos</p>
                            <div id="grupo_archivo_agregar" class="grupo_archivo">
                                <p class="archivo_div_text_agregar">Arrastre los archivos a esta zona o <label id="label_Archivo" for="input_archivos_agregar" class="form-label"><strong> haga clic aquí</strong></label></p>
                                <input type="file" class="input_archivos" name="input_archivos_agregar" id="input_archivos_agregar" multiple accept="image/jpeg, image/jpg, image/png,video/mp4,video/mkv,video/avi" />
                                <div class="formulario_grupo-input div_archivos" id="div_archivo_agregar_publicacion"></div>
                            </div>
                            <p id="mensaje_error_archivo_agregar" class="mensaje_error_archivo"></p>
                        </div>
                        <span class="form-text">Máximo 5 archivos (Imagen límite 10 MB, Video límite 100 MB)</span>
                    </div>

                    <!-- Div para agregar la ubicación en el mapa -->
                    <div id="grupo_map_agregar" class="grupo_map" style="display:none;">
                        <button id="boton_cerrar_grupo_maps_agregar" type="button" class="boton_cerrar"><i class="fa-solid fa-x"></i></button>
                        <p>Mi ubicación actual</p>
                        <div id="div_map_publicaciones_agregar" style="height: 300px;"></div>
                    </div>

                    <!-- Opciones para agregar archivos o ubicación -->
                    <div class="row">
                        <div class="col-5">
                            <p>Agregar a tu publicación</p>
                        </div>
                        <div class="col-6">
                            <button type="button" id="boton_grupo_archivo_agregar" class="boton_galeria" role="button" data-bs-toggle="button" aria-pressed="false"><i class="fa-solid fa-photo-film"></i></button>
                            <button type="button" id="boton_grupo_map_agregar" class="boton_map" role="button" data-bs-toggle="button" aria-pressed="false"><i class="fa-solid fa-map-location-dot"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Footer del modal -->
                <div class="modal-footer">

                    <!-- Botón para cerrar el modal -->
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>

                    <!-- Botón para enviar la publicación -->
                    <button type="submit" class="btn btn-outline-success">Publicar</button>
                </div>

            </form>
        </div>
    </div>
</div>


<script>
    // Se obtiene el formulario para agregar una publicacion
    const formulario_agregar_publicacion = document.getElementById("formulario_agregar_publicacion");


    //Se obtiene los elementos que utliza los archivos
    var boton_grupo_archivo_agregar = document.getElementById('boton_grupo_archivo_agregar');
    var imputArchivos_agregar = document.getElementById("input_archivos_agregar");
    var div_archivo_agregar_publicacion = document.getElementById('div_archivo_agregar_publicacion');
    var mensaje_error_archivo_agregar = document.getElementById("mensaje_error_archivo_agregar");
    var archivo_div_text_agregar = document.querySelector('.archivo_div_text_agregar');
    var grupo_archivo_agregar = document.getElementById("grupo_archivo_agregar");
    var div_grupo_archivo_agregar = document.getElementById('div_grupo_archivo_agregar');
    var boton_cerrar_grupo_archivo_agregar = document.getElementById('boton_cerrar_grupo_archivo_agregar');


    //Se obtiene los elementos que utliza maps
    var map_latitude_agregar = null;
    var map_longitude_agregar = null;
    var boton_grupo_map_agregar = document.getElementById("boton_grupo_map_agregar");
    var grupo_map_agregar = document.getElementById("grupo_map_agregar");
    var boton_cerrar_grupo_maps_agregar = document.getElementById('boton_cerrar_grupo_maps_agregar');
    var div_map_publicaciones_agregar = document.getElementById("div_map_publicaciones_agregar");


    //Cantidad minima y maxima de archivos permitidos
    const cant_min_agregar = 1;
    const cant_max_agregar = 5;

    var lista_archivos_agregar = [];

    // Se obtiene el modal de agregar una publicacion
    const modal_agregar_publicacion = document.getElementById('modal_agregar_publicacion');

    const alert_notificacion_agregar_publicacion = document.getElementById('alert_notificacion_agregar_publicacion');



    //Agrega un evento para contar y mostrar caracteres restantes en el campo de descripcion
    document.getElementById('txt_descripcion_agregar').addEventListener('input', function() {
        contarMostrarCarecteresRestantes('txt_descripcion_agregar', 'txaCountDescripAgregar');
    });


    //Agrega un evento cuando se cierra el modal
    modal_agregar_publicacion.addEventListener('hide.bs.modal', event => {

        //Resetea el formulario para limpiar los campos
        formulario_agregar_publicacion.reset();

        //Contar y mostrar caracteres restantes en el campo respuesta
        contarMostrarCarecteresRestantes('txt_descripcion_agregar', 'txaCountDescripAgregar');

        //Elimina cualquier alerta previa en el modal
        alert_notificacion_agregar_publicacion.innerHTML = "";

        //Llama las funciones para cerrar el div de archivos y mapa
        cerrar_ventana_archivo();
        cerrar_ventana_map();
    });


    // Abrir div de maps
    boton_grupo_map_agregar.addEventListener('click', function() {
        // Muestra el div de mapa y desactiva el botón para evitar múltiples clics
        grupo_map_agregar.style.display = "block";
        boton_grupo_map_agregar.disabled = true;

        // Verifica si la geolocalización es compatible con el navegador
        if (navigator.geolocation) {
            // Obtiene la posición actual del usuario y llama a las funciones de éxito o error
            navigator.geolocation.getCurrentPosition(successMaps, errorMaps);
        } else {
            // Muestra un mensaje de error si la geolocalización no es compatible
            div_map_publicaciones_agregar.removeAttribute('style');
            div_map_publicaciones_agregar.innerHTML = `<div class="grupo_map-error invalid-map-activo" id="error_map">
            <p>La geolocalización no es compatible con su navegador</p></div>`;
        }
    });

    //Agrega un evento para abrir div de archivos
    boton_grupo_archivo_agregar.addEventListener('click', function() {
        // Muestra el div de archivos y desactiva el botón para evitar múltiples clics
        div_grupo_archivo_agregar.style.display = "block";
        boton_grupo_archivo_agregar.disabled = true;
    });

    // Cerrar div de archivos
    boton_cerrar_grupo_archivo_agregar.addEventListener('click', function() {
        // Llama a la función para cerrar el div de archivos
        cerrar_ventana_archivo();
    });

    // Cerrar div de maps
    boton_cerrar_grupo_maps_agregar.addEventListener('click', function() {
        // Llama a la función para cerrar el div de maps
        cerrar_ventana_map();
    });

    // Función para cerrar el div de archivos
    function cerrar_ventana_archivo() {
        // Habilita el botón de agregar archivos y limpia el input de archivos
        boton_grupo_archivo_agregar.disabled = false;
        imputArchivos_agregar.value = null;
        lista_archivos_agregar = [];

        // Actualiza la vista de archivos
        div_archivo_agregar_publicacion.innerHTML = obtenerListaImagenesVideosInput(lista_archivos_agregar, "Agregar");

        // Limpia los mensajes de error y resetea el estado del botón
        mensaje_error_archivo_agregar.innerHTML = "";
        boton_grupo_archivo_agregar.classList.remove('active');
        div_grupo_archivo_agregar.style.display = "none";
        boton_grupo_archivo_agregar.ariaPressed = "false";
        
    }

    // Función para cerrar el div de maps
    function cerrar_ventana_map() {
        // Habilita el botón de agregar maps y oculta el div de maps
        boton_grupo_map_agregar.disabled = false;
        grupo_map_agregar.style.display = "none";

        // Resetea el estado del botón y las coordenadas del mapa
        boton_grupo_map_agregar.ariaPressed = "false";
        boton_grupo_map_agregar.classList.remove('active');
        map_latitude_agregar = null;
        map_longitude_agregar = null;
    }

    // Manejar el cambio en el input de archivos
    imputArchivos_agregar.addEventListener('change', (e) => {
        // Inicializa una lista para archivos válidos
        var archivos_validos = [];
        var archivos = imputArchivos_agregar.files;

        // Calcula el total de archivos incluyendo los ya agregados
        var cantidadTotal = archivos.length + lista_archivos_agregar.length;

        // Valida los archivos subidos
        archivos_validos = validarArchivoImagenVideoPublicacion(cantidadTotal, cant_min_agregar, cant_max_agregar, archivos, 'mensaje_error_archivo_agregar');
        if (archivos_validos.length > 0) {
            // Agrega archivos válidos a la lista y actualiza la vista
            lista_archivos_agregar.push(...archivos_validos);
            div_archivo_agregar_publicacion.innerHTML = obtenerListaImagenesVideosInput(lista_archivos_agregar, "Agregar");
        }
    });

    // Manejar el evento de soltar archivos en el div
    grupo_archivo_agregar.addEventListener('drop', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        grupo_archivo_agregar.classList.remove('dragover');

        // Obtiene los archivos arrastrados
        var archivos = event.dataTransfer.files;
        archivo_div_text_agregar.innerHTML = 'Arrastre los archivos a esta zona o <label id="label_Archivo" for="input_archivos_agregar" class="form-label"><strong> haga clic aquí</strong></label>';

        // Calcula el total de archivos incluyendo los ya agregados
        var cantidadTotal = archivos.length + lista_archivos_agregar.length;
        var archivos_validos = [];

        // Valida los archivos arrastrados
        archivos_validos = validarArchivoImagenVideoPublicacion(cantidadTotal, cant_min_agregar, cant_max_agregar, archivos, 'mensaje_error_archivo_agregar');
        if (archivos_validos.length > 0) {
            // Agrega archivos válidos a la lista y actualiza la vista
            lista_archivos_agregar.push(...archivos_validos);
            div_archivo_agregar_publicacion.innerHTML = obtenerListaImagenesVideosInput(lista_archivos_agregar, "Agregar");
        }
    });

    // Manejar el evento de arrastrar sobre el div
    grupo_archivo_agregar.addEventListener('dragover', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        grupo_archivo_agregar.classList.add('dragover');
        archivo_div_text_agregar.innerHTML = 'Suelta los archivos acá';
    });

    // Manejar el evento de salir del div arrastrando
    grupo_archivo_agregar.addEventListener('dragleave', function(event) {

        //Previene el envio por defecto del formulario
        event.preventDefault();

        grupo_archivo_agregar.classList.remove('dragover');
        archivo_div_text_agregar.innerHTML = 'Arrastra los archivos a esta zona o <label id="label_Archivo" for="input_archivos_agregar" class="form-label"><strong> haga clic aquí</strong></label>';
    });

    //Funcion para eliminar un archivo de la lista de archivos agregados
    function eliminarArchivoListaArchivoInputAgregar(num_archivo) {

        //Crea una URL temporal para el archivo a eliminar
        const url = URL.createObjectURL(lista_archivos_agregar[num_archivo]);

        //Elimina el archivo de la lista utilizando su indice
        lista_archivos_agregar.splice(num_archivo, 1);

        //Se revoca la URL temporal del archivo para liberar la memoria
        URL.revokeObjectURL(url);

        //Actualiza la vista de la lista de archivos 
        div_archivo_agregar_publicacion.innerHTML = obtenerListaImagenesVideosInput(lista_archivos_agregar, "Agregar");
    }

    //Funcion de exito para obtener la ubicacion
    function successMaps(position) {
        //Guarda la latitude y longitude obtenidas
        map_latitude_agregar = position.coords.latitude;
        map_longitude_agregar = position.coords.longitude;

        //Muestra la ubicacion en el mapa
        verUbicacionMapPublicacionesModal(map_latitude_agregar, map_longitude_agregar, 'div_map_publicaciones_agregar', "Mi ubicacion");
    }

    //Funcion de error al obtener la ubicacion
    function errorMaps(err) {
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
                mensaje = "No se ha podido obtener su posición en un tiempo razonable";
                break;
            default:
                mensaje = "Se ha producido un error desconocido al intentar obtener la posición actual";
                break;
        }
        //Muestra un mensaje de error si no se puede obtener la ubicacion
        div_map_publicaciones_agregar.removeAttribute('style');
        div_map_publicaciones_agregar.innerHTML = `<div class="grupo_map-error invalid-map-activo" id="error_map"><p>` + mensaje + `</p></div>`;
    }
</script>


