<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/consultas_preguntas_respuesta.php");
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_notificacion.php");
include("../../../../config/consultas_bd/consultas_categoria.php");
include("../../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/config_define.php");


$categorias_producto = array();
$estados_producto = array();
$mensaje_error = "";
try {

    //Establecer la sesion
    session_start();

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica que los datos de sesion sean un usuario emprendedor y que sea un usuario valido
    verificarDatosSessionUsuarioEmprendedor($conexion);

    //Se obtiene ID del usuario
    $id_usuario = $_SESSION['id_usuario'];


    //Se obtiene los datos del emprendedor en caso que sea uno
    $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
    if (empty($usuario_emprendedor)) {
        throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
    }

    //Las variables se utilizan para que el usuario emprendedor pueda ir a su perfil desde el navbar
    $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];
    $id_usuario_emprendedor_token = hash_hmac('sha1', $id_usuario_emprendedor, KEY_TOKEN);


    //Se obtiene los datos de las categorias de los productos publicados del emprendedor
    $categorias_producto = obtenerCategoriasDeProductosEmprendedor($conexion, $id_usuario);

    //Se obtiene los datos de los estados que puede tener un produto
    $estados_producto = obtenerEstadosProducto($conexion);
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
    <title>Proyecto Emprendedor Mis Productos</title>

    <!--Enlace al archivo de estilos propios del proyecto-->
    <link href="../../../../config/css/estilos.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Bootstrap-->
    <link href="../../../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de FontAwesome para iconos-->
    <link href="../../../../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Fancybox-->
    <link href="../../../../lib/fancyapps-5.0.33/dist/fancybox/fancybox.css" rel="stylesheet" />



</head>


<body>

    <!--Incluye el archivo de la barra de navegación para usuarios emprendedores.-->
    <?php include($url_navbar_usuario_emprendedor); ?>

    <!--Separa el contenido principal de la pagina del pie de pagina.-->
    <main>

        <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
        <div class="container-fluid">
            <?php if (empty($mensaje_error)) { ?>

                <!--Titulo de la seccion-->
                <h1 class="text-center">Mis productos publicados</h1>

                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">

                        <div class="row">

                            <!--Boton para redireccionar a otra pagina para agregar un nuevo producto-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <a href="pagina_agregar_producto.php" class="btn btn-outline-success"><i class="fa-solid fa-circle-plus"></i> Publicar un nuevo producto</a>
                            </div>

                            <!--Selector de cantidad de productos a mostrar-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" for="cant_registro">Mostrar:</span>
                                    <select class="form-select" name="cant_registro" id="cant_registro">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">

                                <div class="row">


                                    <!--Campo de busqueda por el nombre del producto-->
                                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                            <input type="search" class="form-control" placeholder="Nombre del producto" aria-label="Buscar producto" name="campo_buscar" id="campo_buscar">
                                        </div>
                                    </div>


                                    <!--Buscar por fecha que se publico-->
                                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text">Fecha de publicacion:</span>
                                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control">
                                        </div>
                                    </div>


                                    <!--Filtro por categoria del producto-->
                                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text" for="num_categoria">Categorias de mis productos:</span>
                                            <select class="form-select" name="num_categoria" id="num_categoria">
                                                <option value="0" selected>Todos</option>
                                                <?php foreach ($categorias_producto as $categoria) { ?>
                                                    <option value="<?php echo $categoria['id_categoria_producto']; ?>"><?php echo $categoria['nombre_categoria']; ?></option>
                                                <?php } ?>

                                            </select>
                                        </div>
                                    </div>


                                    <!--Filtro por estado del producto-->
                                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text" for="num_estado">Estado:</span>
                                            <select class="form-select" name="num_estado" id="num_estado">
                                                <option value="0" selected>Todos</option>
                                                <?php foreach ($estados_producto as $estado) { ?>
                                                    <option value="<?php echo $estado['id_estado_producto']; ?>"><?php echo $estado['estado']; ?></option>
                                                <?php } ?>

                                            </select>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>

                        <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                        <div id="alert_notificacion_producto"></div>
                    </div>

                    <!--Contenedor con las cards que contiene los producto-->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="row justify-content-center" id="card_producto"></div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">

                        <div class="row">
                            <div class="col-6 col-sm-6 col-md-6 col-lg-6">

                                <!--Informacion sobre la cantidad de resultados que se ve en la interfaz-->
                                <p id="lbl-totalResultado"></p>

                                <!--Informacion sobre las paginas disponibles -->
                                <p id="lbl-totalPagina"></p>

                            </div>

                            <!--Contenedor para la navegacion de la paginacion -->
                            <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacion"></div>
                        </div>

                    </div>
                </div>
            <?php } else {  ?>
                <!-- Muestra un mensaje de error si existe algún problema.-->
                <div class="alert alert-danger" role="alert">
                    <?php echo ($mensaje_error) ?>
                </div>
            <?php } ?>
        </div>
    </main>


    <!-- Incluye el pie de pagina, los Modals y varios scripts necesarios para el funcionamiento de la pagina.-->
    <script src="../../../../config/js/funciones.js"></script>
    <script src="../../../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../../../lib/fancyapps-5.0.33/dist/fancybox/fancybox.umd.js"></script>
    <?php require("modal_eliminar_producto.php"); ?>
    <?php require("../respuesta_pregunta/modal_eliminar_respuesta.php"); ?>
    <?php require("../respuesta_pregunta/modal_agregar_respuesta.php"); ?>
    <?php require("../../../../template/footer.php"); ?>

</body>

</html>


<script>
    //Inicializa la variables que almacenan los datos de la categoria y estados de productos
    var js_categorias_producto = <?php echo json_encode($categorias_producto); ?>;
    var js_estados_producto = <?php echo json_encode($estados_producto); ?>;
    var cant_actual_registros;


    //Se verifica que se halla recibo los estados de los productos 
    if (js_estados_producto.length > 0) {

        //Inicializa la pagina actual y las notificaciones
        var pagina_actual = 1;
        const alert_notificacion_producto = document.getElementById('alert_notificacion_producto');
        const alert_notificacion_respuesta = document.getElementById('alert_notificacion_respuesta');

        // Obtener el modal para responder una pregunta
        const agregarModal = document.getElementById('agregarModal');
        const ventanaModalAgregar = new bootstrap.Modal('#agregarModal');

        // Obtener el modal para eliminar una respuesta
        const eliminaModal = document.getElementById('eliminaModal');
        const ventanaModalEliminar = new bootstrap.Modal('#eliminaModal');

        // Carga de datos de productos
        getData();


        //Agrega un evento cuando se cierra el modal para eliminar una respuesta
        eliminaModal.addEventListener('hide.bs.modal', event => {

            //Se obtiene el id del producto del formulario
            var id_producto = eliminaModal.querySelector('.modal-footer #id_producto').value;

            // Obtener el modal para ver una lista de preguntas del producto
            const ventanaModalLista = new bootstrap.Modal('#preguntasModal-' + id_producto);

            //Abre el modal con las preguntas del producto
            ventanaModalLista.show();

        });

        //Agrega un evento cuando se cierra el modal para responder una pregunta
        agregarModal.addEventListener('hide.bs.modal', event => {

            //Se obtiene el id del producto del formulario
            var id_producto = agregarModal.querySelector('.modal-body #id_producto').value;

            // Obtener el modal para ver una lista de preguntas del producto
            const ventanaModalLista = new bootstrap.Modal('#preguntasModal-' + id_producto);

            //Abre el modal con las preguntas del producto
            ventanaModalLista.show();
        });

        //Manejo del envio del formulario para agregar una respuesta a una pregunta recibida
        form_agregar_respuesta.addEventListener("submit", (e) => {

            //Previene el envio por defecto del formulario
            e.preventDefault();

            //Elimina cualquier alerta previa 
            alert_respuesta_modal = document.getElementById("alert_respuesta_modal");
            alert_respuesta_modal.innerHTML = "";


            var respuesta_pregunta = document.getElementById('respuesta_pregunta');
            var id_pregunta = form_agregar_respuesta.querySelector('#id_pregunta');
            var id_producto = form_agregar_respuesta.querySelector('#id_producto');


            //Valida que el campo respuesta no este vacio
            if (validarCampoVacio([respuesta_pregunta])) {
                alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", "Por favor complete el campo respuesta.");
                return false;
            }


            //Valida que los campos ocultos solo contengan numeros
            if (!isNaN(id_pregunta) || !isNaN(id_producto)) {
                alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
                return false;
            }


            //Valida que el campo no tenga espacios al inicio o al final de la cadena
            if (respuesta_pregunta.value.trim() != respuesta_pregunta.value) {
                alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", "La respuesta no puede tener espacios en el inicio o al final");
                return false;
            }


            //Valida que la longitud del campo sea valida 
            if (!validarCantLengthInput(respuesta_pregunta)) {
                alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", "El campo respuesta debe tener entre 1 y 255 caracteres");
                return false;
            }

            // Envío del formulario usando fetch
            const formData = new FormData(form_agregar_respuesta);
            fetch('../respuesta_pregunta/alta_respuesta.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json()) // Convierte la respuesta a JSON
                .then(datos => {
                    if (datos.estado == "success") {

                        // LLama una funcion para obtener una lista de preguntas actualizada del producto
                        getDataPreguntasProducto(id_producto.value, datos.mensaje);

                        //Cierra el modal 
                        ventanaModalAgregar.hide();

                    } else {
                        if (datos.estado == "danger") {
                            // Muestra un mensaje de alerta si hubo un error
                            alert_respuesta_modal.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                        }
                    }
                }).catch(e => {
                    // Muestra un mensaje error de la solicitud
                    alert_respuesta_modal.innerHTML = mensaje_alert_fijo("danger", e);
                });
        });


        //Manejo del envio del formulario para eliminar una respuesta
        form_eliminar_respuesta.addEventListener("submit", (e) => {

            //Previene el envio por defecto del formulario
            e.preventDefault();


            //Elimina cualquier alerta previa 
            var alert_eliminar_modal_respuesta = document.getElementById("alert_eliminar_modal");
            alert_eliminar_modal_respuesta.innerHTML = "";

            var id_pregunta = form_eliminar_respuesta.querySelector('#id_pregunta');
            var id_producto = form_eliminar_respuesta.querySelector('#id_producto');


            //Valida que los campos ocultos solo contengan numeros
            if (!isNaN(id_pregunta) || !isNaN(id_producto)) {
                alert_eliminar_modal_respuesta.innerHTML = mensaje_alert_fijo("danger", "Los campos ocultos deben contener solo numeros.");
                return false;
            }

            // Envío del formulario usando fetch
            const formData = new FormData(form_eliminar_respuesta);
            fetch('../respuesta_pregunta/baja_respuesta.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json()) // Convierte la respuesta a JSON
                .then(datos => {
                    if (datos.estado == "success") {

                        // LLama una funcion para obtener una lista de preguntas actualizada del producto
                        getDataPreguntasProducto(id_producto.value, datos.mensaje);


                        //Cierra el modal 
                        ventanaModalEliminar.hide();

                    } else {
                        if (datos.estado == "danger") {
                            // Muestra un mensaje de alerta si hubo un error
                            alert_eliminar_modal_respuesta.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                      
                        }
                    }

                }).catch(e => {
                    // Muestra un mensaje error de la solicitud
                    alert_eliminar_modal_respuesta.innerHTML = mensaje_alert_fijo("danger", e);
                });


        });


        
        //Agrega un evento cada vez que la fecha cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
        document.getElementById("fecha_inicio").addEventListener("change", function() {
            pagina_actual = 1; //Reinicia la pagina a 1
            getData(); //Llama a la funcion para obtener los datos de productos
        });


        //Agrega un evento cada vez que el campo buscar producto cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
        document.getElementById("campo_buscar").addEventListener("input", function() {
            pagina_actual = 1; //Reinicia la pagina a 1
            getData(); //Llama a la funcion para obtener los datos de productos
        });


        //Agrega un evento cada vez que se cambie el estado la pagina actual vuelve a 1 y se llama a la funcion de productos 
        document.getElementById("num_estado").addEventListener("change", function() {
            pagina_actual = 1; //Reinicia la pagina a 1
            getData(); //Llama a la funcion para obtener los datos de productos
        });

        //Agrega un evento cada vez que se cambie la categoria la pagina actual vuelve a 1 y se llama a la funcion de productos 
        document.getElementById("num_categoria").addEventListener("change", function() {
            pagina_actual = 1; //Reinicia la pagina a 1
            getData(); //Llama a la funcion para obtener los datos de productos
        });


        //Agrega un evento cada vez que la cantidad de registro cambie la pagina actual vuelve a 1 y se llama a la funcion de productos
        document.getElementById("cant_registro").addEventListener("change", function() {
            pagina_actual = 1; //Reinicia la pagina a 1
            getData(); //Llama a la funcion para obtener los datos de productos
        });


        // Inicializa Fancybox con el selector de data-fancybox
        Fancybox.bind('[data-fancybox]', {
            // Deshabilita las miniaturas
            Thumbs: false,
            // Configuración de imágenes
            Images: {
                protected: true // Protege las imágenes contra clic derecho y arrastrar
            },
        });

    }


    //Función para obtener y cargar datos de productos
    function getData() {
        var campo_buscar = document.getElementById("campo_buscar");
        var categoria = document.getElementById("num_categoria");
        var estado = document.getElementById("num_estado");
        var fecha_inicio = document.getElementById("fecha_inicio");
        var cant_registro = document.getElementById("cant_registro");
        var card_producto = document.getElementById("card_producto");
        var pagina = pagina_actual;

        // Envío del formulario usando fetch
        const formData = new FormData();

        formData.append('estado', estado.value);
        formData.append('categoria', num_categoria.value);
        formData.append('campo_buscar', campo_buscar.value);
        formData.append('cant_registro', cant_registro.value);
        formData.append('fecha', fecha_inicio.value);
        formData.append('numero_pagina', pagina);
        fetch('lista_producto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convierte la respuesta a JSON
            .then(datos => {
                if (datos.estado !== 'danger') {
                    card_producto.innerHTML = datos.card;
                    cant_actual_registros = datos.cantidad_actual;
                    document.getElementById("lbl-totalResultado").innerHTML = datos.registro;
                    document.getElementById("lbl-totalPagina").innerHTML = datos.pagina;
                    document.getElementById("nav-paginacion").innerHTML = datos.paginacion;
                } else {
                    // Muestra un mensaje de alerta si hubo un error
                    alert_notificacion_producto.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                }

            }).catch(e => {
                // Muestra un mensaje error de la solicitud
                alert_notificacion_producto.innerHTML = mensaje_alert_fijo("danger", e);
            });
    }


    //Funcion para cambiar la pagina y recargar los datos
    function nextPageProducto(pagina) {
        pagina_actual = pagina;
        getData();
    }

    //Función para obtener y cargar los datos de preguntas y respuesta de un producto
    function getDataPreguntasProducto(id_producto, mensaje) {

        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('id_producto', id_producto);
        fetch('lista_pregunta_producto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                //Se obtiene el modal con las preguntas de un determinado producto
                var modalLista = document.getElementById('preguntasModal-' + id_producto);

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