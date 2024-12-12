function mostrar_ocultar_password(check_password, input_password) {
    if (check_password.checked) {
        input_password.type = "text";
    } else {
        input_password.type = "password";
    }
}

function mensaje_alert_dismissible(estado, mensaje) {
    alertPlaceholders = '<div class="alert alert-' + estado + ' alert-dismissible" role="alert">';
    alertPlaceholders += mensaje;
    alertPlaceholders += '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    alertPlaceholders += '</div>';
    return alertPlaceholders;
}

function mensaje_alert_fijo(estado, mensaje) {
    alertPlaceholders = '<div class="alert alert-' + estado + '" role="alert">';
    alertPlaceholders += mensaje;
    alertPlaceholders += '</div>';
    return alertPlaceholders;
}

function mensaje_alert_lista_dismissible(estado, mensaje, lista) {
    alertPlaceholders = '<div class="alert alert-' + estado + ' alert-dismissible" role="alert">';
    alertPlaceholders += mensaje;
    alertPlaceholders += '<ul>';
    for (let i = 0; i < lista.length; i++) {
        alertPlaceholders += '<li>' + lista[i] + '</li>';
    }
    alertPlaceholders += '</ul>';
    alertPlaceholders += '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    alertPlaceholders += '</div>';
    return alertPlaceholders;
}

function mensaje_alert_lista_fijo(estado, mensaje, lista) {
    alertPlaceholders = '<div class="alert alert-' + estado + '" role="alert">';
    alertPlaceholders += mensaje;
    alertPlaceholders += '<ul>';
    for (let i = 0; i < lista.length; i++) {
        alertPlaceholders += '<li>' + lista[i] + '</li>';
    }
    alertPlaceholders += '</ul>';
    alertPlaceholders += '</div>';
    return alertPlaceholders;
}

function contarMostrarCarecteresRestantes(id_input, id_contador) {
    var max_length = document.getElementById(id_input).getAttribute('data-max');
    var length_actual = document.getElementById(id_input).value.length;
    var valor_restante = max_length - length_actual;
    document.getElementById(id_contador).textContent = valor_restante + ' restantes';
}

function verMas(resumen, completo, estado) {
    if (estado) {
        completo.style.display = "block";
        resumen.style.display = "none";
    } else {
        completo.style.display = "none";
        resumen.style.display = "inline";
    }
}

function validarCampoVacio(campos) {
    for (let i = 0; i < campos.length; i++) {
        if (campos[i].value.trim() === '') {
            return true;
        }
    }
    return false;
}

function tieneEspacioEnBlacoPassword(password) {
    return password.value.includes(' ');
}

function validarIgualdadPassword(password1, password2) {
    return password1.value.trim() === password2.value.trim();
}

function tieneEspacioEnBlancoEmail(email) {
    return email.value.includes(' ');
}

function listaInputLengthNoValidos(campos) {
    var lista_input = [];
    for (let i = 0; i < campos.length; i++) {
        if (!validarCantLengthInput(campos[i])) {
            lista_input.push(campos[i].placeholder);
        }
    }
    return lista_input;
}

function listaInputEspaciosEnBlanco(inputs) {
    var lista_input = [];
    for (let i = 0; i < inputs.length; i++) {
        if (inputs[i].value.includes(' ')) {
            lista_input.push(inputs[i].placeholder);
        }
    }
    return lista_input;
}


function listaInputEspacioBlancoIF(inputs) {
    var lista_input = [];
    for (let i = 0; i < inputs.length; i++) {
        if (inputs[i].value.trim() != inputs[i].value) {
            lista_input.push(inputs[i].placeholder);
        }
    }
    return lista_input;
}

function listaInputNumNoPositivo(inputs) {
    var lista_input = [];
    for (let i = 0; i < inputs.length; i++) {
        if (!(inputs[i].value.trim() >= 0)) {
            lista_input.push(inputs[i].placeholder);
        }
    }
    return lista_input;
}

function listaInputNumFueraRango(inputs) {
    var lista_input = [];
    for (let i = 0; i < inputs.length; i++) {
        if (!(inputs[i].value.trim() >= inputs[i].min && inputs[i].value.trim() <= inputs[i].max)) {
            lista_input.push(inputs[i].placeholder);
        }
    }
    return lista_input;
}

function listaInputValorNoNumerico(inputs) {
    var lista_input = [];
    for (let i = 0; i < inputs.length; i++) {
        if (isNaN(inputs[i].value.trim())) {
            lista_input.push(inputs[i].placeholder);
        }
    }
    return lista_input;
}

function validarCantLengthInput(campo) {
    return campo.value.trim().length >= campo.getAttribute('minlength') && campo.value.trim().length <= campo.getAttribute('maxlength');
}

function validarCampoEmail(campo) {

    var expresion_regular = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (expresion_regular.test(campo.value)) {
        return true;
    }
    return false;
}

function validarCampoTiposUsuario(campo, tipos_usuarios) {
    var campo_select = campo.value.trim();
    if (!isNaN(campo_select)) {
        for (let i = 0; i < tipos_usuarios.length; i++) {
            if (campo_select == tipos_usuarios[i]['id_tipo_usuario']) {
                return true;
            }
        }
    }
    return false;
}

function validarCampoCategoriaProducto(campo, categoria_producto) {
    var campo_select = campo.value.trim();
    if (!isNaN(campo_select)) {
        for (let i = 0; i < categoria_producto.length; i++) {
            if (campo_select == categoria_producto[i]['id_categoria_producto']) {
                return true;
            }
        }
    }
    return false;
}

function validarCampoSelectCalificacion(num_calificacion) {
    if (num_calificacion.value.trim() != "null") {
        if (!isNaN(num_calificacion.value.trim())) {
            for (let i = 0; i <= 5; i++) {
                if (num_calificacion.value.trim() == i) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function cambiarEstadoInputs(campos_input, estado) {
    campos_input.forEach(elemento => {
        elemento.disabled = estado;
    });
}

function validadCantidadArchivos(cantidad, cant_min, cant_max) {
    return cantidad >= cant_min && cantidad <= cant_max;
}

function validarExtensionImagen(archivo) {
    var extensions = /(.jpg|.jpeg|.png)$/;
    return extensions.exec(archivo.type);
}

function validarTamanioImagen(archivo) {
    const limit = 10240;
    var tamanio = Math.round(archivo.size / 1024);

    return tamanio <= limit;
}

function obtenerListaImagenesVideosBD(lista_archivos_bd, ruta) {

    var extensions_img_valida = ["jpg", "jpeg", "png"];
    var extensions_video_valida = ["mp4", "mkv", "avi"];
    var datos = "";
    var extension_archivo = "";
    for (let i = 0; i < lista_archivos_bd.length; i++) {
        extension_archivo = obtenerExtension(lista_archivos_bd[i]);
        extension_archivo = extension_archivo.toLowerCase();
        if (extensions_img_valida.includes(extension_archivo)) {
            datos += `<div class="image">
            <img src="${ruta}/${lista_archivos_bd[i]}" alt="archivo-${i}">
            <span onclick="eliminarArchivoListaArchivoBD(${i})" ><i class="fa-regular fa-trash-can"></i></span>
        </div>`;
        } else {
            if (extensions_video_valida.includes(extension_archivo)) {
                datos += `<div class="video">
                <video controls>"
                    Su navegador no soporta la etiqueta de vídeo.
                    <source src="${ruta}/${lista_archivos_bd[i]}">
                </video>
                <span onclick="eliminarArchivoListaArchivoBD(${i})"><i class="fa-regular fa-trash-can"></i></span>
            </div>`;
            }
        }
    }
    return datos;
}

function validarExtensionVideo(archivo) {
    var extensions = /(.mp4|.mkv|.avi)$/;

    return extensions.exec(archivo.type);
}

function validarTamanioVideo(archivo) {
    const limit = 102400;
    var tamanio = Math.round(archivo.size / 1024);

    return tamanio <= limit;
}

function obtenerExtension(nombreArchivo) {
    return nombreArchivo.slice((nombreArchivo.lastIndexOf(".") - 1 >>> 0) + 2);
}

function devolverFechaDateTimeLocalInput(fecha_input_Local) {
    var fecha_hora_formateada = "";
    if (fecha_input_Local.value.length > 0) {
        var parte_fecha = fecha_input_Local.value.split('T');
        var fecha_formateada = parte_fecha[0];
        var hora_formateada = parte_fecha[1];
        if (hora_formateada.length == 5) {
            hora_formateada = hora_formateada + ':00';
        }
        fecha_hora_formateada = fecha_formateada + ' ' + hora_formateada;
    }
    return fecha_hora_formateada;

}

function validarArchivoImagenVideoPublicacion(cantidadTotal, cant_min, cant_max, archivo, id_mensaje_error) {
    var archivos_validos = [];
    var mensaje_error = document.getElementById(id_mensaje_error);

    if (!validadCantidadArchivos(cantidadTotal, cant_min, cant_max)) {
        mensaje_error.innerHTML = "Solo se permite agregar " + cant_max + " archivos";
        return archivos_validos;
    }
    for (var i = 0; i < archivo.length; i++) {
        if (validarExtensionImagen(archivo[i])) {

            if (validarTamanioImagen(archivo[i])) {
                archivos_validos.push(archivo[i]);
            } else {
                mensaje_error.innerHTML = "La imagen " + archivo[i].name + " excede el tamaño maximo permitido de 10MB";
                return archivos_validos;
            }
        } else {
            if (validarExtensionVideo(archivo[i])) {

                if (validarTamanioVideo(archivo[i])) {
                    archivos_validos.push(archivo[i]);
                } else {
                    mensaje_error.innerHTML = "El video " + archivo[i].name + " excede el tamaño maximo permitido de 100MB";

                    return archivos_validos;
                }
            } else {
                mensaje_error.innerHTML = "El formato del archivo " + archivo[i].name + " no es valido. Formatos permitidos: JPEG, JPG, PNG, MP4, MKV, AVI";

                return archivos_validos;
            }
        }
    }
    return archivos_validos;
}

function obtenerListaImagenesVideosInput(archivos_fotos_video, id) {
    var extensionsVideo = /(.avi|.mp4|.mkv)$/;
    let div = "";
    archivos_fotos_video.forEach((archivo, i) => {
        const url = URL.createObjectURL(archivo);
        if (extensionsVideo.exec(archivo.type)) {
            div += `<div class="video">
                            <video controls>"
                                Su navegador no soporta la etiqueta de vídeo.
                                <source src="${url}">
                            </video>
                            <span onclick="eliminarArchivoListaArchivoInput${id}(${i})"><i class="fa-regular fa-trash-can"></i></span>
                        </div>`;
        } else {
            div += `<div class="image">
                        <img src="${url}" alt="image-${i}">
                        <span onclick="eliminarArchivoListaArchivoInput${id}(${i})" ><i class="fa-regular fa-trash-can"></i></span>
                    </div>`;
        }
    })
    return div;
}

function obtenerListaImagenesBD(lista_archivos_bd) {
    let images = "";
    lista_archivos_bd.forEach((image, i) => {
        images += `<div class="image">
                    <img src="${ruta}/${image}" alt="image-${i}">
                    <span onclick="eliminarImagenBD(${i})"><i class="fa-regular fa-trash-can"></i></span>
                  </div>`;
    })
    return images;
}

//Estas dos funciones son solo cuando se agregan imagenes desde input pero en los casos que no esta la variable lista_archivos_bd
function obtenerListaImagenesInput(lista_archivos) {
    let imagen = "";
    lista_archivos.forEach((image, i) => {
        const url = URL.createObjectURL(image);
        imagen += `<div class="image">
                    <img src="${url}" alt="image-${i}">
                    <span onclick="eliminarImagenListaArchivoInput(${i})" ><i class="fa-regular fa-trash-can"></i></span>
                  </div>`;
    })
    return imagen;
}

function eliminaArchivoInput(i) {
    const url = URL.createObjectURL(lista_archivos[i]);
    lista_archivos.splice(i, 1);
    URL.revokeObjectURL(url);
}

function eliminarImagenBD(num_img) {
    lista_archivos_bd.splice(num_img, 1);
    let lista_imagenes_bd = obtenerListaImagenesBD(lista_archivos_bd);
    let lista_imagenes = obtenerListaImagenesInput(lista_archivos);
    div_archivos_modificar_producto.innerHTML = lista_imagenes_bd + lista_imagenes;
}

function validarArchivoImagenProducto(cantidadTotal, cant_min, cant_max, archivo) {
    let archivos_validos = [];
    mensaje_error_archivo.innerHTML = "";
    if (!validadCantidadArchivos(cantidadTotal, cant_min, cant_max)) {
        mensaje_error_archivo.innerHTML = "Solo se permite subir " + cant_max + " imagenes";
        return archivos_validos;
    }
    for (let i = 0; i < archivo.length; i++) {
        if (!validarExtensionImagen(archivo[i])) {
            mensaje_error_archivo.innerHTML = "El formato del archivo " + archivo[i].name + " no es valido. Formatos permitidos: JPEG, JPG y PNG";

            return archivos_validos;
        }
        if (!validarTamanioImagen(archivo[i])) {
            mensaje_error_archivo.innerHTML = "La imagen " + archivo[i].name + " excede el tamaño maximo permitido de 10MB";


            return archivos_validos;
        }
        archivos_validos.push(archivo[i]);
    }
    return archivos_validos;
}

var modal_map_ubicacion_publicacion = {};


function verUbicacionMapPublicacionesModal(latitud, longitud, id_elemento, texto) {
    const zoom = 17;

    // Verifica si ya existe un mapa para el contenedor
    const mapaExistente = modal_map_ubicacion_publicacion[id_elemento];
    if (mapaExistente) {
        // Actualiza la vista del mapa y el marcador si es necesario
        if (texto.trim() != '') {
            mapaExistente.eachLayer(function (layer) {
                if (layer instanceof L.Marker) {
                    layer.setLatLng([latitud, longitud]).setPopupContent(texto).openPopup();
                }
            });

        }
        mapaExistente.setView([latitud, longitud], zoom);

    } else {
        // Crea un nuevo mapa si no existe uno para el contenedor
        const nuevoMapa = L.map(id_elemento).setView([latitud, longitud], zoom);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(nuevoMapa);
        modal_map_ubicacion_publicacion[id_elemento] = nuevoMapa;

        // Agrega marcador si es necesario
        if (texto.trim() != '') {
            L.marker([latitud, longitud]).addTo(nuevoMapa).bindPopup(texto).openPopup();
        }
    }

    // Ajusta el tamaño del mapa en el contenedor
    modal_map_ubicacion_publicacion[id_elemento].invalidateSize();

}


function validarCampoNumericoEntero(campo) {

    const limite = 2147483647;
    return campo > limite;
}

function agregarEventoBotonesSeguimiento() {
    // Obtener botones que se están actualizando
    var botonesSeguir = document.querySelectorAll('.seguir-btn:not(.evento-agregado)');
    var botonesDejarSeguir = document.querySelectorAll('.dejar-seguir-btn:not(.evento-agregado)');

    // Agregar eventos solo a los botones que se están actualizando
    botonesSeguir.forEach(button => {
        button.removeEventListener('click', seguirUsuario);
        button.addEventListener('click', seguirUsuario);
        button.classList.add('evento-agregado');
    });

    botonesDejarSeguir.forEach(button => {
        button.removeEventListener('click', dejarSeguirUsuario);
        button.addEventListener('click', dejarSeguirUsuario);
        button.classList.add('evento-agregado');
    });
}

